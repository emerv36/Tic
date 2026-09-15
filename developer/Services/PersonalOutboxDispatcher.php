<?php
require_once(__DIR__ . '/../Config/PDOconn.php');
require_once(__DIR__ . '/../Config/sige_config.php');

class PersonalOutboxDispatcher {
    private function conexion() {
        $pdo = new PDO(connstring, user, pass, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ));
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("SET time_zone = '-05:00'");
        return $pdo;
    }

    public function procesarPendientes($limite = 25) {
        $limite = max(1, min(100, (int) $limite));
        $pdo = $this->conexion();
        
        // Expirar leases (5 minutos) y marcar como FALLIDA si superó intentos máximos
        $maxAttempts = defined('SIGE_OUTBOX_MAX_ATTEMPTS') ? (int) SIGE_OUTBOX_MAX_ATTEMPTS : 12;
        $stmtExpirar = $pdo->prepare(
            "UPDATE sige_personal_outbox
             SET estado = IF(intentos >= :maxAttempts, 'FALLIDA', 'REINTENTO'),
                 proximo_intento_en = NOW(),
                 ultimo_error = IF(intentos >= :maxAttempts, 'Límite de reintentos alcanzado (Lease ENVIANDO expirado)', 'Lease ENVIANDO expirado')
             WHERE estado = 'ENVIANDO'
               AND actualizado_en < DATE_SUB(NOW(), INTERVAL 5 MINUTE)"
        );
        $stmtExpirar->execute(array(':maxAttempts' => $maxAttempts));
        
        $procesadas = 0;
        while ($procesadas < $limite) {
            $orden = $this->reclamarSiguiente($pdo);
            if (!$orden) {
                break;
            }
            $this->enviarYFinalizar($pdo, $orden);
            $procesadas++;
        }
        return $procesadas;
    }

    private function reclamarSiguiente(PDO $pdo) {
        $pdo->beginTransaction();
        try {
            // Orden estricto por persona: no debe existir un comando anterior para la misma persona con UUID válido que no esté resuelto
            $stmt = $pdo->query(
                "SELECT o.* FROM sige_personal_outbox o
                 WHERE o.estado IN ('PENDIENTE','REINTENTO')
                   AND o.proximo_intento_en <= NOW()
                   AND NOT EXISTS (
                       SELECT 1 FROM sige_personal_outbox prev
                       WHERE o.persona_uuid IS NOT NULL
                         AND o.persona_uuid != ''
                         AND prev.persona_uuid = o.persona_uuid
                         AND prev.id < o.id
                         AND prev.estado NOT IN ('ENVIADA', 'FALLIDA', 'CONFLICTO_OBSOLETO')
                   )
                 ORDER BY o.id ASC LIMIT 1 FOR UPDATE"
            );
            $orden = $stmt->fetch();
            if (!$orden) {
                $pdo->commit();
                return null;
            }
            
            $update = $pdo->prepare(
                "UPDATE sige_personal_outbox
                 SET estado = 'ENVIANDO', intentos = intentos + 1, actualizado_en = CURRENT_TIMESTAMP
                 WHERE id = :id"
            );
            $update->execute(array(':id' => $orden['id']));
            $pdo->commit();
            
            $orden['intentos'] = (int) $orden['intentos'] + 1;
            return $orden;
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }
    }

    private function enviarYFinalizar(PDO $pdo, array $orden) {
        try {
            $resultado = $this->postJson($orden['payload'], $orden['comando'] ?? null);
            $http = (int) $resultado['http_code'];
            $ack = $this->validarAck($resultado['respuesta'], $orden['id_operacion']);
            
            if ($http >= 200 && $http < 300 && $ack['valido']) {
                $stmt = $pdo->prepare(
                    "UPDATE sige_personal_outbox
                     SET estado = 'ENVIADA', ultimo_http_code = :http, ultimo_error = NULL,
                         enviado_en = CURRENT_TIMESTAMP, actualizado_en = CURRENT_TIMESTAMP
                     WHERE id = :id"
                );
                $stmt->execute(array(':http' => $http, ':id' => $orden['id']));
                return;
            }

            $codigo = $ack['codigo'];
            $terminal = in_array($http, array(400, 404, 409, 422), true)
                || ($http >= 200 && $http < 300 && $ack['status'] === 'RECHAZADO');
            
            $estado = $terminal ? 'FALLIDA' : 'REINTENTO';
            if ($codigo === 'CONFLICTO_OBSOLETO') {
                $estado = 'CONFLICTO_OBSOLETO';
            }
            
            $maxAttempts = defined('SIGE_OUTBOX_MAX_ATTEMPTS') ? (int) SIGE_OUTBOX_MAX_ATTEMPTS : 12;
            $intentos = (int) $orden['intentos'];
            if (!$terminal && $intentos >= $maxAttempts) {
                $estado = 'FALLIDA';
            }
            
            $minutos = $this->minutosReintento($intentos);
            $proximo = date('Y-m-d H:i:s', time() + ($minutos * 60));
            
            $error = $ack['mensaje'] !== '' ? $ack['mensaje'] : (string) $resultado['respuesta'];
            $error = substr($error, 0, 2000);
            
            $stmt = $pdo->prepare(
                "UPDATE sige_personal_outbox
                 SET estado = :estado, ultimo_http_code = :http, ultimo_error = :error,
                     proximo_intento_en = :proximo, actualizado_en = CURRENT_TIMESTAMP
                 WHERE id = :id"
            );
            $stmt->bindValue(':estado', $estado);
            $stmt->bindValue(':http', $http, PDO::PARAM_INT);
            $stmt->bindValue(':error', $error);
            $stmt->bindValue(':proximo', $proximo);
            $stmt->bindValue(':id', $orden['id'], PDO::PARAM_INT);
            $stmt->execute();
        } catch (Throwable $ex) {
            $maxAttempts = defined('SIGE_OUTBOX_MAX_ATTEMPTS') ? (int) SIGE_OUTBOX_MAX_ATTEMPTS : 12;
            $intentos = (int) ($orden['intentos'] ?? 1);
            $estado = ($intentos >= $maxAttempts) ? 'FALLIDA' : 'REINTENTO';
            $error = 'Excepción al enviar outbox: ' . substr($ex->getMessage(), 0, 1900);
            $minutos = $this->minutosReintento($intentos);
            $proximo = date('Y-m-d H:i:s', time() + ($minutos * 60));

            $stmt = $pdo->prepare(
                "UPDATE sige_personal_outbox
                 SET estado = :estado, ultimo_error = :error,
                     proximo_intento_en = :proximo, actualizado_en = CURRENT_TIMESTAMP
                 WHERE id = :id"
            );
            $stmt->bindValue(':estado', $estado);
            $stmt->bindValue(':error', $error);
            $stmt->bindValue(':proximo', $proximo);
            $stmt->bindValue(':id', $orden['id'], PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    private function validarAck($respuesta, $idOperacion) {
        $respuestaStr = (string) $respuesta;
        // Strip UTF-8 BOM if present
        if (substr($respuestaStr, 0, 3) === "\xEF\xBB\xBF") {
            $respuestaStr = substr($respuestaStr, 3);
        }
        $body = json_decode($respuestaStr, true);
        if (!is_array($body)) {
            return array('valido' => false, 'status' => '', 'codigo' => '', 'mensaje' => 'ACK SIGE no es JSON válido.');
        }
        $status = strtoupper(trim((string) ($body['status'] ?? '')));
        $recibido = strtolower(trim((string) ($body['id_operacion'] ?? '')));
        
        if ($recibido === '' || !hash_equals(strtolower($idOperacion), $recibido)) {
            // Si es un error y tiene mensaje, usamos el mensaje real
            if (isset($body['error']) || isset($body['mensaje']) || isset($body['message'])) {
                $status = $status === '' ? 'RECHAZADO' : $status;
                $codigo = (string) ($body['codigo'] ?? $body['error'] ?? '');
                $mensaje = (string) ($body['mensaje'] ?? $body['message'] ?? 'Error desconocido');
                return array('valido' => false, 'status' => $status, 'codigo' => $codigo, 'mensaje' => $mensaje);
            }
            return array('valido' => false, 'status' => $status, 'codigo' => (string) ($body['codigo'] ?? ''), 'mensaje' => 'ACK SIGE no corresponde a id_operacion.');
        }
        
        return array(
            'valido' => in_array($status, array('PROCESADO', 'ACEPTADO'), true),
            'status' => $status,
            'codigo' => (string) ($body['codigo'] ?? $body['status'] ?? ''),
            'mensaje' => (string) ($body['mensaje'] ?? '')
        );
    }

    private function minutosReintento($intentos) {
        if ($intentos <= 1) return 1;
        if ($intentos === 2) return 5;
        if ($intentos === 3) return 15;
        return 30;
    }

    protected function resolverUrl($comando) {
        $comando = strtoupper(trim((string) $comando));
        if ($comando === 'CARNET') return SIGE_PERSONAL_CARNET_COMMAND_URL;
        if (in_array($comando, array('VINCULO_CREAR', 'VINCULO_ACTUALIZAR'), true)) return SIGE_PERSONAL_LINK_COMMAND_URL;
        return SIGE_PERSONAL_COMMAND_URL;
    }

    protected function postJson($payload, $comando = null) {
        if (!defined('SIGE_API_KEY') || trim((string) SIGE_API_KEY) === '') {
            return array('http_code' => 0, 'respuesta' => 'Integración outbound no configurada.');
        }
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $this->resolverUrl($comando),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => SIGE_WEBHOOK_TIMEOUT,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Bearer ' . SIGE_API_KEY,
                'Accept: application/json'
            ),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ));
        $respuesta = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($respuesta === false) {
            return array('http_code' => 0, 'respuesta' => 'Error de transporte: ' . $error);
        }
        return array('http_code' => $http, 'respuesta' => $respuesta);
    }
}
