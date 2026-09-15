<?php
require_once(__DIR__ . '/../Config/PDOconn.php');
require_once(__DIR__ . '/../Config/sige_config.php');

class SigeReconciliationWorker {
    private $pdo;
    private $ejecucionId;

    public function __construct() {
        $this->pdo = new PDO(connstring, user, pass, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ));
        $this->pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        $this->pdo->exec("SET time_zone = '-05:00'");
    }

    public function ejecutar() {
        if (trim((string) SIGE_API_KEY) === '') {
            throw new RuntimeException('Integración outbound SIGE no configurada.');
        }
        if (!$this->obtenerLock()) {
            return array('status' => 'OMITIDA', 'motivo' => 'YA_EN_EJECUCION');
        }
        $this->ejecucionId = $this->crearEjecucion();
        $resumen = array(
            'status' => 'COMPLETADA', 'ejecucion_id' => $this->ejecucionId,
            'lotes' => 0, 'consultados' => 0, 'actualizados' => 0,
            'conflictos' => 0, 'intentos_http' => 0, 'ultimo_http_code' => null
        );
        try {
            $cursor = 0;
            while (true) {
                $lote = $this->cargarLote($cursor);
                if (!$lote) break;
                $cursor = (int) end($lote)['id'];
                $resumen['lotes']++;
                $resumen['consultados'] += count($lote);
                $http = $this->enviarConBackoff($lote);
                $resumen['intentos_http'] += $http['intentos'];
                $resumen['ultimo_http_code'] = $http['http_code'];
                $procesado = $this->procesarRespuesta($lote, $http);
                $resumen['actualizados'] += $procesado['actualizados'];
                $resumen['conflictos'] += $procesado['conflictos'];
            }
            $this->finalizarEjecucion($resumen, null);
            return $resumen;
        } catch (Throwable $ex) {
            $resumen['status'] = 'ERROR';
            $this->finalizarEjecucion($resumen, $ex->getMessage());
            throw $ex;
        } finally {
            $this->liberarLock();
        }
    }

    private function cargarLote($cursor) {
        $stmt = $this->pdo->prepare(
            "SELECT id, id_inscripcion, numero_documento, version_estado
             FROM sige_carnet_proyeccion
             WHERE es_actual = 1 AND id > :cursor
             ORDER BY id ASC LIMIT 500"
        );
        $stmt->bindValue(':cursor', (int) $cursor, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function enviarConBackoff(array $lote) {
        $estudiantes = array();
        foreach ($lote as $fila) {
            $estudiantes[] = array(
                'numero_documento' => (string) $fila['numero_documento'],
                'version_estado' => (int) $fila['version_estado']
            );
        }
        $payload = json_encode(array('estudiantes' => $estudiantes), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) throw new RuntimeException('No fue posible serializar el lote de reconciliación.');
        $esperas = array(0, 1, 5, 15);
        $ultimo = null;
        foreach ($esperas as $indice => $segundos) {
            if ($segundos > 0) sleep($segundos);
            $ultimo = $this->postJson($payload);
            $ultimo['intentos'] = $indice + 1;
            if ($ultimo['http_code'] >= 200 && $ultimo['http_code'] < 300) return $ultimo;
            if (in_array($ultimo['http_code'], array(400, 401, 403, 404, 409, 422), true)) break;
        }
        $detalle = 'HTTP ' . (int) $ultimo['http_code'] . ': ' . substr((string) $ultimo['respuesta'], 0, 700);
        $this->registrarConflicto(null, 'RESPUESTA_INCOMPATIBLE', null, null, $detalle, $ultimo['respuesta']);
        throw new RuntimeException('Reconciliación SIGE agotó el backoff: HTTP ' . (int) $ultimo['http_code'] . '.');
    }

    private function postJson($payload) {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => SIGE_CARNET_RECONCILIATION_URL,
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

    private function procesarRespuesta(array $lote, array $http) {
        $body = json_decode((string) $http['respuesta'], true);
        if (!is_array($body) || ($body['status'] ?? '') !== 'PROCESADO' || !isset($body['estudiantes']) || !is_array($body['estudiantes'])) {
            $this->registrarConflicto(null, 'RESPUESTA_INCOMPATIBLE', null, null, 'JSON o status incompatible.', $http['respuesta']);
            throw new RuntimeException('Respuesta de reconciliación incompatible con DOC-650.');
        }
        $solicitados = array();
        foreach ($lote as $fila) $solicitados[(string) $fila['numero_documento']] = $fila;
        $vistos = array();
        $actualizados = 0;
        $conflictos = 0;
        foreach ($body['estudiantes'] as $item) {
            if (!is_array($item)) {
                $this->registrarConflicto(null, 'RESPUESTA_INCOMPATIBLE', null, null, 'Elemento de estudiantes no es objeto.', $http['respuesta']);
                $conflictos++;
                continue;
            }
            $documento = trim((string) ($item['numero_documento'] ?? ''));
            if ($documento === '' || !isset($solicitados[$documento]) || isset($vistos[$documento])) {
                $this->registrarConflicto($documento ?: null, 'RESPUESTA_INCOMPATIBLE', null, null, 'Documento inesperado o repetido.', $http['respuesta']);
                $conflictos++;
                continue;
            }
            $vistos[$documento] = true;
            if (!array_key_exists('requiere_reactivacion', $item) || !is_bool($item['requiere_reactivacion'])) {
                $this->registrarConflicto($documento, 'RESPUESTA_INCOMPATIBLE', (int) $solicitados[$documento]['version_estado'], null, 'Falta requiere_reactivacion booleano.', json_encode($item));
                $conflictos++;
                continue;
            }
            $versionSige = filter_var($item['version_estado'] ?? null, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0)));
            $versionTic = (int) $solicitados[$documento]['version_estado'];
            if ($versionSige === false) {
                $this->registrarConflicto($documento, 'RESPUESTA_INCOMPATIBLE', $versionTic, null, 'version_estado inválida.', json_encode($item));
                $conflictos++;
                continue;
            }
            $versionSige = (int) $versionSige;
            if ($versionSige < $versionTic) {
                $this->registrarConflicto($documento, 'SIGE_VERSION_MENOR', $versionTic, $versionSige, 'Se conserva la proyección TIC; nunca se decrementa.', json_encode($item));
                $conflictos++;
                continue;
            }
            if ($versionSige === $versionTic) continue;
            if (!isset($item['carnet']) || !is_array($item['carnet'])) {
                $this->registrarConflicto($documento, 'RESPUESTA_INCOMPATIBLE', $versionTic, $versionSige, 'Versión mayor sin carnet autoritativo.', json_encode($item));
                $conflictos++;
                continue;
            }
            if ($this->aplicarVersionMayor($solicitados[$documento], $item)) $actualizados++;
        }

        $noEncontrados = isset($body['no_encontrados']) && is_array($body['no_encontrados']) ? $body['no_encontrados'] : array();
        foreach ($noEncontrados as $documento) {
            $documento = trim((string) $documento);
            if (isset($solicitados[$documento]) && !isset($vistos[$documento])) {
                $this->registrarConflicto($documento, 'NO_ENCONTRADO_SIGE', (int) $solicitados[$documento]['version_estado'], null, 'SIGE no encontró el estudiante.', null);
                $vistos[$documento] = true;
                $conflictos++;
            }
        }
        foreach ($solicitados as $documento => $fila) {
            if (!isset($vistos[$documento])) {
                $this->registrarConflicto($documento, 'RESPUESTA_INCOMPATIBLE', (int) $fila['version_estado'], null, 'SIGE omitió el documento solicitado.', $http['respuesta']);
                $conflictos++;
            }
        }
        return array('actualizados' => $actualizados, 'conflictos' => $conflictos);
    }

    private function aplicarVersionMayor(array $tic, array $sige) {
        $carnet = $sige['carnet'];
        $carnetId = filter_var($carnet['sige_carnet_id'] ?? null, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
        $version = (int) $sige['version_estado'];
        $estado = strtoupper(trim((string) ($carnet['estado'] ?? '')));
        if ($carnetId === false || !in_array($estado, array('ACTIVO', 'INACTIVO'), true)) {
            $this->registrarConflicto($tic['numero_documento'], 'RESPUESTA_INCOMPATIBLE', (int) $tic['version_estado'], $version, 'Carnet autoritativo inválido.', json_encode($sige));
            return false;
        }
        $this->pdo->beginTransaction();
        try {
            $actualStmt = $this->pdo->prepare(
                "SELECT * FROM sige_carnet_proyeccion
                 WHERE numero_documento = :documento AND es_actual = 1
                 ORDER BY version_estado DESC, id DESC LIMIT 1 FOR UPDATE"
            );
            $actualStmt->execute(array(':documento' => $tic['numero_documento']));
            $actual = $actualStmt->fetch();
            if (!$actual || (int) $actual['version_estado'] >= $version) {
                $this->pdo->commit();
                return false;
            }
            $payload = json_encode($sige, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $eventoId = $this->uuidV4();
            $insertEvento = $this->pdo->prepare(
                "INSERT INTO sige_carnet_evento
                 (id_evento, tipo_evento, version_estado, origen, emergency_id,
                  id_inscripcion, numero_documento, sige_carnet_id, uid_rfid,
                  ocurrido_en, recibido_en, payload, payload_hash, procesado)
                 VALUES
                 (:evento, 'RECONCILIACION_SIGE', :version, 'SIGE_RECONCILIACION', NULL,
                  :id_inscripcion, :documento, :carnet_id, :uid,
                  NOW(), NOW(), :payload, :hash, 1)"
            );
            $insertEvento->execute(array(
                ':evento' => $eventoId, ':version' => $version,
                ':id_inscripcion' => $actual['id_inscripcion'], ':documento' => $tic['numero_documento'],
                ':carnet_id' => $carnetId, ':uid' => $carnet['uid_rfid'] ?? null,
                ':payload' => $payload, ':hash' => hash('sha256', $payload)
            ));
            $desmarcar = $this->pdo->prepare(
                "UPDATE sige_carnet_proyeccion SET es_actual = 0
                 WHERE numero_documento = :documento AND sige_carnet_id <> :carnet_id AND es_actual = 1"
            );
            $desmarcar->execute(array(':documento' => $tic['numero_documento'], ':carnet_id' => $carnetId));
            $upsert = $this->pdo->prepare(
                "INSERT INTO sige_carnet_proyeccion
                 (sige_carnet_id, id_inscripcion, numero_documento, uid_rfid,
                  estado_operativo, motivo_inactivacion, fecha_emision, vigencia_hasta,
                  es_actual, ultimo_evento_id, version_estado, version, requiere_reactivacion, actualizado_en)
                 VALUES
                 (:carnet_id, :id_inscripcion, :documento, :uid,
                  :estado, :motivo, :fecha_emision, :vigencia_hasta,
                  1, :evento, :version_estado, :version_compat, :requiere, NOW())
                 ON DUPLICATE KEY UPDATE
                    id_inscripcion = VALUES(id_inscripcion), numero_documento = VALUES(numero_documento),
                    uid_rfid = VALUES(uid_rfid), estado_operativo = VALUES(estado_operativo),
                    motivo_inactivacion = VALUES(motivo_inactivacion), fecha_emision = VALUES(fecha_emision),
                    vigencia_hasta = VALUES(vigencia_hasta), es_actual = 1,
                    ultimo_evento_id = VALUES(ultimo_evento_id), version_estado = VALUES(version_estado),
                    version = VALUES(version_estado), requiere_reactivacion = VALUES(requiere_reactivacion),
                    actualizado_en = VALUES(actualizado_en)"
            );
            $upsert->execute(array(
                ':carnet_id' => $carnetId, ':id_inscripcion' => $actual['id_inscripcion'],
                ':documento' => $tic['numero_documento'], ':uid' => $carnet['uid_rfid'] ?? null,
                ':estado' => $estado, ':motivo' => strtoupper(trim((string) ($carnet['motivo'] ?? 'NO_APLICA'))),
                ':fecha_emision' => $this->fechaOpcional($carnet['fecha_emision'] ?? null),
                ':vigencia_hasta' => $this->fechaOpcional($carnet['vigencia_hasta'] ?? null),
                ':evento' => $eventoId, ':version_estado' => $version,
                ':version_compat' => $version,
                ':requiere' => filter_var($sige['requiere_reactivacion'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0
            ));
            $this->pdo->commit();
            return true;
        } catch (Throwable $ex) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $ex;
        }
    }

    private function registrarConflicto($documento, $tipo, $versionTic, $versionSige, $detalle, $respuesta) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sige_reconciliacion_conflicto
             (ejecucion_id, numero_documento, tipo, version_tic, version_sige,
              detalle, respuesta_hash, detectado_en, ultima_deteccion_en, resuelto)
             VALUES (:ejecucion, :documento, :tipo, :tic, :sige, :detalle, :hash, NOW(), NOW(), 0)"
        );
        $stmt->execute(array(
            ':ejecucion' => $this->ejecucionId, ':documento' => $documento,
            ':tipo' => $tipo, ':tic' => $versionTic, ':sige' => $versionSige,
            ':detalle' => substr((string) $detalle, 0, 1000),
            ':hash' => $respuesta === null ? null : hash('sha256', (string) $respuesta)
        ));
    }

    private function crearEjecucion() {
        $this->pdo->exec("INSERT INTO sige_reconciliacion_ejecucion (estado, iniciado_en) VALUES ('EN_PROCESO', NOW())");
        return (int) $this->pdo->lastInsertId();
    }

    private function finalizarEjecucion(array $resumen, $error) {
        $stmt = $this->pdo->prepare(
            "UPDATE sige_reconciliacion_ejecucion
             SET estado = :estado, lotes = :lotes, consultados = :consultados,
                 actualizados = :actualizados, conflictos = :conflictos,
                 intentos_http = :intentos, ultimo_http_code = :http,
                 ultimo_error = :error, finalizado_en = NOW()
             WHERE id = :id"
        );
        $stmt->execute(array(
            ':estado' => $resumen['status'], ':lotes' => $resumen['lotes'],
            ':consultados' => $resumen['consultados'], ':actualizados' => $resumen['actualizados'],
            ':conflictos' => $resumen['conflictos'], ':intentos' => $resumen['intentos_http'],
            ':http' => $resumen['ultimo_http_code'], ':error' => $error === null ? null : substr($error, 0, 2000),
            ':id' => $this->ejecucionId
        ));
    }

    private function obtenerLock() {
        $stmt = $this->pdo->query("SELECT GET_LOCK('tic_sige_reconciliacion', 0)");
        return (int) $stmt->fetchColumn() === 1;
    }

    private function liberarLock() {
        try { $this->pdo->query("SELECT RELEASE_LOCK('tic_sige_reconciliacion')"); } catch (Throwable $ignored) {}
    }

    private function fechaOpcional($valor) {
        if ($valor === null || $valor === '') return null;
        foreach (array('Y-m-d', 'Y-m-d H:i:s') as $formato) {
            $fecha = DateTimeImmutable::createFromFormat('!' . $formato, $valor);
            $errores = DateTimeImmutable::getLastErrors();
            if ($fecha !== false
                && ($errores === false || ($errores['warning_count'] === 0 && $errores['error_count'] === 0))
                && $fecha->format($formato) === $valor) {
                return $fecha->format('Y-m-d');
            }
        }
        throw new RuntimeException('Fecha autoritativa inválida en reconciliación.');
    }

    private function uuidV4() {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
    }
}
