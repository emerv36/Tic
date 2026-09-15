<?php
require_once(__DIR__ . '/../Config/PDOconn.php');
require_once(__DIR__ . '/../Config/sige_config.php');

class SigeOrdenDispatcher {
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
        $pdo->exec(
            "UPDATE sige_orden_outbox
             SET estado = 'REINTENTO', proximo_intento_en = NOW(),
                 ultimo_error = 'Lease ENVIANDO expirado', actualizado_en = NOW()
             WHERE estado = 'ENVIANDO'
               AND actualizado_en < DATE_SUB(NOW(), INTERVAL 10 MINUTE)"
        );
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
            $stmt = $pdo->query(
                "SELECT * FROM sige_orden_outbox
                 WHERE estado IN ('PENDIENTE','REINTENTO')
                   AND proximo_intento_en <= NOW()
                 ORDER BY id ASC LIMIT 1 FOR UPDATE"
            );
            $orden = $stmt->fetch();
            if (!$orden) {
                $pdo->commit();
                return null;
            }
            $update = $pdo->prepare(
                "UPDATE sige_orden_outbox
                 SET estado = 'ENVIANDO', intentos = intentos + 1, actualizado_en = NOW()
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
        $resultado = $this->postJson($orden['payload']);
        $http = (int) $resultado['http_code'];
        $ack = $this->validarAck($resultado['respuesta'], $orden['id_operacion']);
        if ($http >= 200 && $http < 300 && $ack['valido']) {
            $stmt = $pdo->prepare(
                "UPDATE sige_orden_outbox
                 SET estado = 'ENVIADA', ultimo_http_code = :http, ultimo_error = NULL,
                     enviado_en = NOW(), actualizado_en = NOW()
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
        $minutos = $this->minutosReintento((int) $orden['intentos']);
        $proximo = date('Y-m-d H:i:s', time() + ($minutos * 60));
        $error = $ack['mensaje'] !== '' ? $ack['mensaje'] : (string) $resultado['respuesta'];
        $error = substr($error, 0, 2000);
        $stmt = $pdo->prepare(
            "UPDATE sige_orden_outbox
             SET estado = :estado, ultimo_http_code = :http, ultimo_error = :error,
                 proximo_intento_en = :proximo,
                 actualizado_en = NOW()
             WHERE id = :id"
        );
        $stmt->bindValue(':estado', $estado);
        $stmt->bindValue(':http', $http, PDO::PARAM_INT);
        $stmt->bindValue(':error', $error);
        $stmt->bindValue(':proximo', $proximo);
        $stmt->bindValue(':id', $orden['id'], PDO::PARAM_INT);
        $stmt->execute();
    }

    private function validarAck($respuesta, $idOperacion) {
        $body = json_decode((string) $respuesta, true);
        if (!is_array($body)) {
            return array('valido' => false, 'status' => '', 'codigo' => '', 'mensaje' => 'ACK SIGE no es JSON válido.');
        }
        $status = strtoupper(trim((string) ($body['status'] ?? '')));
        $recibido = strtolower(trim((string) ($body['id_operacion'] ?? '')));
        if ($recibido === '' || !hash_equals(strtolower($idOperacion), $recibido)) {
            return array('valido' => false, 'status' => $status, 'codigo' => (string) ($body['codigo'] ?? ''), 'mensaje' => 'ACK SIGE no corresponde a id_operacion.');
        }
        return array(
            'valido' => $status === 'PROCESADO',
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

    private function postJson($payload) {
        if (!defined('SIGE_API_KEY') || trim((string) SIGE_API_KEY) === '') {
            return array('http_code' => 0, 'respuesta' => 'Integración outbound no configurada.');
        }
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => SIGE_CARNET_COMMAND_URL,
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
