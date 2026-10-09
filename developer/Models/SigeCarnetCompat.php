<?php
require_once(__DIR__ . '/../Config/PDOconn.php');

if (!class_exists('SigeEventoDuplicadoConflictivo')) {
    class SigeEventoDuplicadoConflictivo extends RuntimeException {}
}
if (!class_exists('SigeEventoInvalido')) {
    class SigeEventoInvalido extends InvalidArgumentException {}
}

class SigeCarnetCompat {
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

    public function registrarEventoYProyectar(array $evento) {
        $normalizado = $this->normalizarEvento($evento);
        $payload = json_encode($evento, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            throw new SigeEventoInvalido('No fue posible serializar el evento.');
        }
        $hash = hash('sha256', $payload);
        $pdo = $this->conexion();
        $pdo->beginTransaction();
        try {
            $existente = $pdo->prepare(
                "SELECT payload_hash, version_estado FROM sige_carnet_evento WHERE id_evento = :id FOR UPDATE"
            );
            $existente->execute(array(':id' => $normalizado['id_evento']));
            $filaExistente = $existente->fetch();
            if ($filaExistente) {
                $pdo->rollBack();
                if (!hash_equals($filaExistente['payload_hash'], $hash)) {
                    throw new SigeEventoDuplicadoConflictivo('El id_evento ya existe con un payload diferente.');
                }
                return array(
                    'status' => 'DUPLICADO',
                    'duplicado' => true,
                    'id_evento' => $normalizado['id_evento'],
                    'id_inscripcion' => null,
                    'version_estado' => (int) $filaExistente['version_estado']
                );
            }

            $idInscripcion = $this->resolverInscripcion(
                $pdo,
                $normalizado['tic_id_inscripcion'],
                $normalizado['numero_documento']
            );
            $insertEvento = $pdo->prepare(
                "INSERT INTO sige_carnet_evento
                 (id_evento, tipo_evento, version_estado, origen, emergency_id,
                  id_inscripcion, numero_documento, sige_carnet_id, uid_rfid,
                  ocurrido_en, payload, payload_hash, procesado)
                 VALUES
                 (:id_evento, :tipo, :version_estado, :origen, :emergency_id,
                  :id_inscripcion, :documento, :carnet_id, :uid,
                  :ocurrido_en, :payload, :payload_hash, 0)"
            );
            $insertEvento->execute(array(
                ':id_evento' => $normalizado['id_evento'],
                ':tipo' => $normalizado['tipo'],
                ':version_estado' => $normalizado['version_estado'],
                ':origen' => $normalizado['origen'],
                ':emergency_id' => $normalizado['emergency_id'],
                ':id_inscripcion' => $idInscripcion,
                ':documento' => $normalizado['numero_documento'],
                ':carnet_id' => $normalizado['sige_carnet_id'],
                ':uid' => $normalizado['uid_rfid'],
                ':ocurrido_en' => $normalizado['ocurrido_en'],
                ':payload' => $payload,
                ':payload_hash' => $hash
            ));

            $actual = $this->bloquearProyeccionActual($pdo, $idInscripcion, $normalizado['numero_documento']);
            if ($actual && $normalizado['version_estado'] <= (int) $actual['version_estado']) {
                $this->marcarEvento($pdo, $normalizado['id_evento'], 'EVENTO_OBSOLETO_IGNORADO');
                $pdo->commit();
                return array(
                    'status' => 'OBSOLETO_IGNORADO',
                    'duplicado' => false,
                    'id_evento' => $normalizado['id_evento'],
                    'id_inscripcion' => $idInscripcion,
                    'version_estado' => $normalizado['version_estado']
                );
            }

            if ($idInscripcion !== null) {
                $desmarcar = $pdo->prepare(
                    "UPDATE sige_carnet_proyeccion SET es_actual = 0
                     WHERE id_inscripcion = :id AND sige_carnet_id <> :carnet_id AND es_actual = 1"
                );
                $desmarcar->execute(array(':id' => $idInscripcion, ':carnet_id' => $normalizado['sige_carnet_id']));
            }
            $upsert = $pdo->prepare(
                "INSERT INTO sige_carnet_proyeccion
                 (sige_carnet_id, id_inscripcion, numero_documento, uid_rfid,
                  estado_operativo, motivo_inactivacion, requiere_reactivacion, fecha_emision, vigencia_hasta,
                  es_actual, ultimo_evento_id, version_estado, version, actualizado_en)
                 VALUES
                 (:carnet_id, :id_inscripcion, :documento, :uid,
                  :estado, :motivo, :requiere_reactivacion, :fecha_emision, :vigencia_hasta,
                  1, :evento_id, :version_estado, :version_legacy, :actualizado_en)
                 ON DUPLICATE KEY UPDATE
                    id_inscripcion = VALUES(id_inscripcion),
                    numero_documento = VALUES(numero_documento),
                    uid_rfid = VALUES(uid_rfid),
                    estado_operativo = VALUES(estado_operativo),
                    motivo_inactivacion = VALUES(motivo_inactivacion),
                    requiere_reactivacion = VALUES(requiere_reactivacion),
                    fecha_emision = VALUES(fecha_emision),
                    vigencia_hasta = VALUES(vigencia_hasta),
                    es_actual = 1,
                    ultimo_evento_id = VALUES(ultimo_evento_id),
                    version_estado = VALUES(version_estado),
                    version = VALUES(version_estado),
                    actualizado_en = VALUES(actualizado_en)"
            );
            $upsert->execute(array(
                ':carnet_id' => $normalizado['sige_carnet_id'],
                ':id_inscripcion' => $idInscripcion,
                ':documento' => $normalizado['numero_documento'],
                ':uid' => $normalizado['uid_rfid'],
                ':estado' => $normalizado['estado'],
                ':motivo' => $normalizado['motivo'],
                ':requiere_reactivacion' => $normalizado['requiere_reactivacion'],
                ':fecha_emision' => $normalizado['fecha_emision'],
                ':vigencia_hasta' => $normalizado['vigencia_hasta'],
                ':evento_id' => $normalizado['id_evento'],
                ':version_estado' => $normalizado['version_estado'],
                ':version_legacy' => $normalizado['version_estado'],
                ':actualizado_en' => $normalizado['ocurrido_en']
            ));
            $this->marcarEvento($pdo, $normalizado['id_evento'], null);
            $pdo->commit();
            return array(
                'status' => 'PROCESADO',
                'duplicado' => false,
                'id_evento' => $normalizado['id_evento'],
                'id_inscripcion' => $idInscripcion,
                'version_estado' => $normalizado['version_estado']
            );
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $ex;
        }
    }

    public function conciliarProyeccionSiAusente(PDO $pdo, $idInscripcion, $documento) {
        $carnet = $this->bloquearProyeccionActual($pdo, $idInscripcion, $documento);
        if ($carnet) {
            return array('carnet' => $carnet, 'verificado' => true, 'es_nuevo' => false);
        }
        if (!defined('SIGE_API_KEY') || trim((string) SIGE_API_KEY) === '') {
            return array('carnet' => null, 'verificado' => false, 'es_nuevo' => false);
        }

        try {
            $resultado = $this->consultarEstudianteSige($documento);
            if (!empty($resultado['encontrado']) && ($resultado['status'] ?? '') === 'ENCONTRADO' && isset($resultado['data']['carnet'])) {
                $this->aplicarProyeccionSige($pdo, $idInscripcion, $documento, $resultado['data']);
                $carnet = $this->bloquearProyeccionActual($pdo, $idInscripcion, $documento);
                return array('carnet' => $carnet, 'verificado' => true, 'es_nuevo' => false);
            } elseif (!empty($resultado['encontrado']) && ($resultado['status'] ?? '') === 'SIN_CARNET') {
                return array('carnet' => null, 'verificado' => true, 'es_nuevo' => true);
            } elseif (($resultado['status'] ?? '') === 'NO_ENCONTRADO') {
                return array('carnet' => null, 'verificado' => true, 'es_nuevo' => true);
            }
        } catch (Throwable $e) {
            error_log('Conciliación individual SIGE falló: ' . $e->getMessage());
        }
        return array('carnet' => null, 'verificado' => false, 'es_nuevo' => false);
    }

    private function consultarEstudianteSige($documento) {
        $documento = trim((string) $documento);
        if ($documento === '' || !defined('SIGE_CARNET_RECONCILIATION_URL') || !defined('SIGE_API_KEY')) {
            return array('encontrado' => false, 'status' => 'NO_CONFIGURADO');
        }
        $payload = json_encode(array(
            'estudiantes' => array(
                array('numero_documento' => $documento, 'version_estado' => 0)
            )
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => SIGE_CARNET_RECONCILIATION_URL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => defined('SIGE_WEBHOOK_TIMEOUT') ? SIGE_WEBHOOK_TIMEOUT : 10,
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
        curl_close($ch);

        if ($respuesta === false || $http < 200 || $http >= 300) {
            return array('encontrado' => false, 'status' => 'ERROR_HTTP', 'http_code' => $http);
        }
        $body = json_decode((string) $respuesta, true);
        if (!is_array($body) || ($body['status'] ?? '') !== 'PROCESADO') {
            return array('encontrado' => false, 'status' => 'RESPUESTA_INVALIDA');
        }
        if (isset($body['no_encontrados']) && is_array($body['no_encontrados']) && in_array($documento, $body['no_encontrados'], true)) {
            return array('encontrado' => false, 'status' => 'NO_ENCONTRADO', 'es_ausente' => true);
        }
        if (isset($body['estudiantes']) && is_array($body['estudiantes'])) {
            foreach ($body['estudiantes'] as $item) {
                if (isset($item['numero_documento']) && (string)$item['numero_documento'] === $documento) {
                    if (array_key_exists('carnet', $item) && $item['carnet'] !== null) {
                        return array('encontrado' => true, 'status' => 'ENCONTRADO', 'data' => $item);
                    } else {
                        return array('encontrado' => true, 'status' => 'SIN_CARNET', 'data' => $item);
                    }
                }
            }
        }
        return array('encontrado' => false, 'status' => 'NO_ENCONTRADO', 'es_ausente' => true);
    }

    private function aplicarProyeccionSige(PDO $pdo, $idInscripcion, $documento, array $sige) {
        $carnet = $sige['carnet'];
        $carnetId = filter_var($carnet['sige_carnet_id'] ?? null, FILTER_VALIDATE_INT);
        $version = (int) ($sige['version_estado'] ?? 0);
        $estado = strtoupper(trim((string) ($carnet['estado'] ?? '')));
        if ($carnetId === false || !in_array($estado, array('ACTIVO', 'INACTIVO'), true) || $version < 1) {
            return;
        }

        $desmarcar = $pdo->prepare(
            "UPDATE sige_carnet_proyeccion SET es_actual = 0
             WHERE numero_documento = :documento AND sige_carnet_id <> :carnet_id AND es_actual = 1"
        );
        $desmarcar->execute(array(':documento' => $documento, ':carnet_id' => $carnetId));

        $upsert = $pdo->prepare(
            "INSERT INTO sige_carnet_proyeccion
             (sige_carnet_id, id_inscripcion, numero_documento, uid_rfid,
              estado_operativo, motivo_inactivacion, requiere_reactivacion, fecha_emision, vigencia_hasta,
              es_actual, ultimo_evento_id, version_estado, version, actualizado_en)
             VALUES
             (:carnet_id, :id_inscripcion, :documento, :uid,
              :estado, :motivo, :requiere_reactivacion, :fecha_emision, :vigencia_hasta,
              1, '', :version_estado, :version_legacy, NOW())
             ON DUPLICATE KEY UPDATE
                id_inscripcion = VALUES(id_inscripcion),
                numero_documento = VALUES(numero_documento),
                uid_rfid = VALUES(uid_rfid),
                estado_operativo = VALUES(estado_operativo),
                motivo_inactivacion = VALUES(motivo_inactivacion),
                requiere_reactivacion = VALUES(requiere_reactivacion),
                fecha_emision = VALUES(fecha_emision),
                vigencia_hasta = VALUES(vigencia_hasta),
                es_actual = 1,
                version_estado = VALUES(version_estado),
                version = VALUES(version_estado),
                actualizado_en = NOW()"
        );
        $upsert->execute(array(
            ':carnet_id' => $carnetId,
            ':id_inscripcion' => $idInscripcion,
            ':documento' => $documento,
            ':uid' => $carnet['uid_rfid'] ?? null,
            ':estado' => $estado,
            ':motivo' => strtoupper(trim((string) ($carnet['motivo'] ?? 'NO_APLICA'))),
            ':requiere_reactivacion' => !empty($sige['requiere_reactivacion']) ? 1 : 0,
            ':fecha_emision' => $this->fechaOpcional($carnet['fecha_emision'] ?? null),
            ':vigencia_hasta' => $this->fechaOpcional($carnet['vigencia_hasta'] ?? null),
            ':version_estado' => $version,
            ':version_legacy' => $version
        ));
    }

    public function obtenerEstadoCarnet($idInscripcion) {
        $idInscripcion = filter_var($idInscripcion, FILTER_VALIDATE_INT);
        if ($idInscripcion === false || $idInscripcion < 1) {
            throw new DomainException('Inscripción inválida.');
        }
        $pdo = $this->conexion();

        $stmtDoc = $pdo->prepare("SELECT identificacion FROM inscripcion WHERE id_inscripcion = :id LIMIT 1");
        $stmtDoc->execute(array(':id' => $idInscripcion));
        $doc = $stmtDoc->fetchColumn();
        if ($doc) {
            $this->conciliarProyeccionSiAusente($pdo, $idInscripcion, $doc);
        }

        $stmt = $pdo->prepare(
            "SELECT * FROM sige_carnet_proyeccion
             WHERE id_inscripcion = :id AND es_actual = 1
             ORDER BY version_estado DESC, id DESC LIMIT 1"
        );
        $stmt->execute(array(':id' => $idInscripcion));
        $carnet = $stmt->fetch();

        $pendiente = $pdo->prepare(
            "SELECT id_operacion, tipo, estado, ultimo_error, creado_en
             FROM sige_orden_outbox
             WHERE id_inscripcion = :id AND estado IN ('PENDIENTE','ENVIANDO','REINTENTO')
             ORDER BY id DESC LIMIT 1"
        );
        $pendiente->execute(array(':id' => $idInscripcion));
        $orden = $pendiente->fetch();

        $rechazada = $pdo->prepare(
            "SELECT id_operacion, tipo, estado, ultimo_error, creado_en, actualizado_en
             FROM sige_orden_outbox
             WHERE id_inscripcion = :id AND estado IN ('FALLIDA','CONFLICTO_OBSOLETO')
             ORDER BY id DESC LIMIT 1"
        );
        $rechazada->execute(array(':id' => $idInscripcion));
        $ordenRechazada = $rechazada->fetch();

        return array(
            'carnet' => $carnet ?: null,
            'orden_pendiente' => $orden ?: null,
            'orden_rechazada' => $ordenRechazada ?: null
        );
    }

    public function crearOrdenCarnet($tipo, $idInscripcion, $uidRfid, $motivo, array $usuario, $confirmado) {
        if ($confirmado !== true) {
            throw new DomainException('La operación requiere confirmación explícita.');
        }
        $tipos = array('ASIGNACION', 'REEMPLAZO', 'BLOQUEO', 'REACTIVACION_AUTORIZADA', 'RENOVACION');
        $tipo = strtoupper(trim((string) $tipo));
        if (!in_array($tipo, $tipos, true)) {
            throw new DomainException('Tipo de orden no soportado.');
        }
        $idInscripcion = filter_var($idInscripcion, FILTER_VALIDATE_INT);
        if ($idInscripcion === false || $idInscripcion < 1) {
            throw new DomainException('Inscripción inválida.');
        }
        $pdo = $this->conexion();
        $pdo->beginTransaction();
        try {
            $rol = $this->resolverRolAutorizado($pdo, $usuario['codigo_rol'] ?? 0);
            $usuarioId = trim((string) ($usuario['id'] ?? ''));
            $usuarioNombre = trim((string) ($usuario['nombre'] ?? ''));
            if ($usuarioId === '' || $usuarioNombre === '') {
                throw new DomainException('Faltan metadatos del usuario responsable.');
            }
            $stmt = $pdo->prepare(
                "SELECT id_inscripcion, identificacion, fecha_inscripcion, fecha_entrega_carnet FROM inscripcion WHERE id_inscripcion = :id FOR UPDATE"
            );
            $stmt->execute(array(':id' => $idInscripcion));
            $estudiante = $stmt->fetch();
            if (!$estudiante) throw new DomainException('Inscripción no encontrada.');

            $resConcil = $this->conciliarProyeccionSiAusente($pdo, (int) $idInscripcion, $estudiante['identificacion']);
            $carnet = $resConcil['carnet'];
            $verificado = !empty($resConcil['verificado']);
            $esNuevo = !empty($resConcil['es_nuevo']);

            if (!$carnet && !$esNuevo) {
                if (!defined('SIGE_API_KEY') || trim((string) SIGE_API_KEY) === '') {
                    throw new DomainException('Integración SIGE no configurada. No es posible determinar la versión del carné sin conexión.');
                }
                throw new DomainException('No fue posible verificar si el estudiante posee un carné previo en SIGE. Intente nuevamente.');
            }

            $uidRfid = trim((string) $uidRfid);
            $motivo = strtoupper(trim((string) $motivo));
            if (in_array($tipo, array('ASIGNACION', 'REEMPLAZO'), true) && $uidRfid === '') {
                throw new DomainException('La operación requiere uid_rfid.');
            }
            if ($tipo === 'ASIGNACION' && $carnet) {
                throw new DomainException('Ya existe un carnet actual; use REEMPLAZO.');
            }
            if ($tipo !== 'ASIGNACION' && !$carnet) {
                throw new DomainException('No existe un carnet proyectado para esta operación.');
            }
            if ($tipo === 'RENOVACION') {
                if (!$carnet) {
                    throw new DomainException('El estudiante no tiene carné previo para renovar.');
                }
                $uidRfid = (string) ($carnet['uid_rfid'] ?? '');
                $motivo = $motivo !== '' ? $motivo : 'VENCIMIENTO';
            }
            if ($tipo === 'REEMPLAZO' && strcasecmp($uidRfid, (string) $carnet['uid_rfid']) === 0) {
                throw new DomainException('El nuevo UID debe ser diferente al carnet reemplazado.');
            }
            if ($tipo === 'BLOQUEO' && $motivo === '') {
                throw new DomainException('El bloqueo requiere motivo.');
            }
            if ($tipo === 'REACTIVACION_AUTORIZADA') {
                if ($carnet['estado_operativo'] !== 'INACTIVO'
                    && (int) ($carnet['requiere_reactivacion'] ?? 0) !== 1) {
                    throw new DomainException('El carnet no está inactivo.');
                }
                if (in_array($carnet['motivo_inactivacion'], array('PERDIDA', 'ROBO'), true)) {
                    throw new DomainException('El carnet requiere REEMPLAZO con un nuevo UID.');
                }
                $motivo = $motivo !== '' ? $motivo : 'PAGO_VERIFICADO_EXTERNAMENTE';
            }
            if (in_array($tipo, array('BLOQUEO', 'REACTIVACION_AUTORIZADA'), true)) {
                $uidRfid = '';
            }
            if ($carnet) {
                $expectedVersion = (int) $carnet['version_estado'];
            } elseif ($esNuevo) {
                $expectedVersion = 0;
            } else {
                throw new DomainException('No es posible determinar la versión autoritativa del carné en SIGE.');
            }
            $pendiente = $pdo->prepare(
                "SELECT id_operacion FROM sige_orden_outbox
                 WHERE id_inscripcion = :id AND tipo = :tipo
                   AND estado IN ('PENDIENTE','ENVIANDO','REINTENTO')
                 ORDER BY id DESC LIMIT 1 FOR UPDATE"
            );
            $pendiente->execute(array(':id' => $idInscripcion, ':tipo' => $tipo));
            $existente = $pendiente->fetchColumn();
            if ($existente) {
                $pdo->commit();
                return array('id_operacion' => $existente, 'duplicado' => true, 'expected_version' => $expectedVersion);
            }

            $idOperacion = $this->uuidV4();
            $ahora = date('Y-m-d H:i:s');
            $fechaValidaStr = $this->normalizarFecha(!empty($estudiante['fecha_entrega_carnet']) ? $estudiante['fecha_entrega_carnet'] : ($estudiante['fecha_inscripcion'] ?? null));
            $fechaDateOnly = substr($fechaValidaStr, 0, 10);
            $fechaIso = date('Y-m-d\TH:i:sP', strtotime($fechaValidaStr));

            $payloadArray = array(
                'id_operacion' => $idOperacion,
                'tipo' => $tipo,
                'numero_documento' => $estudiante['identificacion'],
                'uid_rfid' => $uidRfid === '' ? null : $uidRfid,
                'motivo' => $motivo === '' ? null : $motivo,
                'expected_version' => $expectedVersion,
                'ocurrido_en' => $ahora,
                'fecha_emision' => $fechaValidaStr,
                'fecha_entrega' => $fechaValidaStr,
                'fecha_registro' => $fechaValidaStr,
                'fecha_inscripcion' => $fechaValidaStr,
                'fecha_entrega_valida' => $fechaValidaStr,
                'fecha_entrega_corte' => $fechaDateOnly,
                'fecha_registro_corte' => $fechaDateOnly,
                'fecha_entrega_iso' => $fechaIso,
                'usuario' => array('id' => $usuarioId, 'nombre' => $usuarioNombre, 'rol' => $rol)
            );
            $payload = json_encode($payloadArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($payload === false) throw new RuntimeException('No fue posible serializar la orden.');
            $insert = $pdo->prepare(
                "INSERT INTO sige_orden_outbox
                 (id_operacion, tipo, id_inscripcion, numero_documento, expected_version,
                  payload, usuario_id, usuario_nombre, usuario_rol, confirmado_en,
                  estado, intentos, proximo_intento_en, creado_en, actualizado_en)
                 VALUES
                 (:operacion, :tipo, :id, :documento, :expected_version,
                  :payload, :usuario_id, :usuario_nombre, :usuario_rol, :confirmado_en,
                  'PENDIENTE', 0, :proximo_intento_en, :creado_en, :actualizado_en)"
            );
            $insert->execute(array(
                ':operacion' => $idOperacion,
                ':tipo' => $tipo,
                ':id' => $idInscripcion,
                ':documento' => $estudiante['identificacion'],
                ':expected_version' => $expectedVersion,
                ':payload' => $payload,
                ':usuario_id' => $usuarioId,
                ':usuario_nombre' => $usuarioNombre,
                ':usuario_rol' => $rol,
                ':confirmado_en' => $ahora,
                ':proximo_intento_en' => $ahora,
                ':creado_en' => $ahora,
                ':actualizado_en' => $ahora
            ));
            if (in_array($tipo, array('ASIGNACION', 'REEMPLAZO'), true) && $idInscripcion !== null) {
                $updInsc = $pdo->prepare(
                    "UPDATE inscripcion 
                     SET chip_carnet = 'SI', 
                         uid_rfid = :uid,
                         fecha_entrega_carnet = COALESCE(
                             NULLIF(fecha_entrega_carnet, '0000-00-00 00:00:00'),
                             NULLIF(fecha_inscripcion, '0000-00-00 00:00:00'),
                             NOW()
                         )
                     WHERE id_inscripcion = :id"
                );
                $updInsc->execute(array(':uid' => $uidRfid, ':id' => $idInscripcion));
            }
            $pdo->commit();

            try {
                require_once(__DIR__ . '/../Services/SigeWebhook.php');
                (new SigeWebhook())->enviarEstudiante((int)$idInscripcion, 'ACTUALIZAR');
            } catch (Throwable $wEx) {
                error_log('Error enviando webhook estudiante tras orden: ' . $wEx->getMessage());
            }

            return array('id_operacion' => $idOperacion, 'duplicado' => false, 'expected_version' => $expectedVersion);
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $ex;
        }
    }

    private function bloquearProyeccionActual(PDO $pdo, $idInscripcion, $documento) {
        $documentoClean = trim((string) $documento);
        if ($idInscripcion !== null) {
            $stmt = $pdo->prepare(
                "SELECT * FROM sige_carnet_proyeccion
                 WHERE id_inscripcion = :id AND es_actual = 1
                 ORDER BY version_estado DESC, id DESC LIMIT 1 FOR UPDATE"
            );
            $stmt->execute(array(':id' => $idInscripcion));
            $fila = $stmt->fetch();
            if ($fila) {
                return $fila;
            }

            // Hallazgo 2 fix: If not found by idInscripcion, check if a projection exists by document.
            // Strict identity validation: document must match inscription document strictly.
            if ($documentoClean !== '') {
                $chkDoc = $pdo->prepare(
                    "SELECT id_inscripcion FROM inscripcion
                     WHERE id_inscripcion = :id AND identificacion = :doc LIMIT 1"
                );
                $chkDoc->execute(array(':id' => $idInscripcion, ':doc' => $documentoClean));
                if ($chkDoc->fetchColumn() !== false) {
                    $stmtDoc = $pdo->prepare(
                        "SELECT * FROM sige_carnet_proyeccion
                         WHERE numero_documento = :doc AND es_actual = 1
                         ORDER BY version_estado DESC, id DESC LIMIT 1 FOR UPDATE"
                    );
                    $stmtDoc->execute(array(':doc' => $documentoClean));
                    $filaDoc = $stmtDoc->fetch();
                    if ($filaDoc) {
                        if ($filaDoc['id_inscripcion'] === null || (int)$filaDoc['id_inscripcion'] !== (int)$idInscripcion) {
                            $updLink = $pdo->prepare("UPDATE sige_carnet_proyeccion SET id_inscripcion = :id WHERE id = :pid");
                            $updLink->execute(array(':id' => $idInscripcion, ':pid' => $filaDoc['id']));
                            $filaDoc['id_inscripcion'] = $idInscripcion;
                        }
                        return $filaDoc;
                    }
                }
            }
            return null;
        } else {
            $stmt = $pdo->prepare(
                "SELECT * FROM sige_carnet_proyeccion
                 WHERE numero_documento = :documento AND es_actual = 1
                 ORDER BY version_estado DESC, id DESC LIMIT 1 FOR UPDATE"
            );
            $stmt->execute(array(':documento' => $documentoClean));
            $fila = $stmt->fetch();
            return $fila ?: null;
        }
    }

    private function marcarEvento(PDO $pdo, $idEvento, $nota) {
        $stmt = $pdo->prepare(
            "UPDATE sige_carnet_evento SET procesado = 1, error_proceso = :nota WHERE id_evento = :id"
        );
        $stmt->execute(array(':nota' => $nota, ':id' => $idEvento));
    }

    private function resolverRolAutorizado(PDO $pdo, $codigoRol) {
        $stmt = $pdo->prepare(
            "SELECT nombre_rol FROM roles WHERE codigo_rol = :codigo AND estado_rol = 'on' LIMIT 1"
        );
        $stmt->execute(array(':codigo' => (int) $codigoRol));
        $rol = strtoupper(trim((string) $stmt->fetchColumn()));
        $aliases = array(
            'PROGRAMADOR' => 'ADMIN',
            'CARNETIZACION' => 'OPERATIVO'
        );
        if (isset($aliases[$rol])) $rol = $aliases[$rol];
        if (!in_array($rol, array('ADMIN', 'OPERATIVO'), true)) {
            throw new DomainException('El rol no está autorizado para ordenar cambios de carnet.');
        }
        return $rol;
    }

    private function resolverInscripcion(PDO $pdo, $ticId, $documento) {
        if ($ticId !== null) {
            $stmt = $pdo->prepare(
                "SELECT id_inscripcion FROM inscripcion
                 WHERE id_inscripcion = :id AND identificacion = :documento LIMIT 1"
            );
            $stmt->execute(array(':id' => $ticId, ':documento' => $documento));
            $id = $stmt->fetchColumn();
            if ($id !== false) return (int) $id;
        }
        $stmt = $pdo->prepare(
            "SELECT id_inscripcion FROM inscripcion
             WHERE identificacion = :documento ORDER BY id_inscripcion DESC LIMIT 1"
        );
        $stmt->execute(array(':documento' => $documento));
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    private function normalizarEvento(array $evento) {
        foreach (array('id_evento', 'tipo', 'version_estado', 'ocurrido_en', 'estudiante', 'carnet') as $campo) {
            if (!array_key_exists($campo, $evento)) throw new SigeEventoInvalido('Falta el campo ' . $campo . '.');
        }
        if (!$this->esUuid($evento['id_evento'])) throw new SigeEventoInvalido('id_evento no es un UUID válido.');
        $tipos = array('CARNET_ASIGNADO', 'CARNET_BLOQUEADO', 'CARNET_REACTIVADO', 'CARNET_REEMPLAZADO', 'CARNET_VENCIDO', 'REACTIVACION_REQUERIDA', 'CARNET_RENOVADO');
        if (!in_array($evento['tipo'], $tipos, true)) throw new SigeEventoInvalido('Tipo de evento no soportado.');
        $version = filter_var($evento['version_estado'], FILTER_VALIDATE_INT);
        if ($version === false || $version < 1) throw new SigeEventoInvalido('version_estado debe ser un entero positivo.');
        $documento = trim((string) ($evento['estudiante']['numero_documento'] ?? ''));
        $carnetId = filter_var($evento['carnet']['sige_carnet_id'] ?? null, FILTER_VALIDATE_INT);
        $estado = strtoupper(trim((string) ($evento['carnet']['estado'] ?? '')));
        if ($documento === '' || $carnetId === false || !in_array($estado, array('ACTIVO', 'INACTIVO'), true)) {
            throw new SigeEventoInvalido('Identidad o carnet inválido.');
        }
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $evento['ocurrido_en']);
        if (!$fecha || $fecha->format('Y-m-d H:i:s') !== $evento['ocurrido_en']) {
            throw new SigeEventoInvalido('ocurrido_en debe usar Y-m-d H:i:s.');
        }
        $emergencyId = trim((string) ($evento['emergency_id'] ?? ''));
        if ($emergencyId !== '' && !$this->esUuid($emergencyId)) throw new SigeEventoInvalido('emergency_id no es UUID válido.');
        return array(
            'id_evento' => strtolower($evento['id_evento']),
            'tipo' => $evento['tipo'],
            'version_estado' => (int) $version,
            'origen' => strtoupper(trim((string) ($evento['origen'] ?? 'SIGE'))),
            'emergency_id' => $emergencyId === '' ? null : strtolower($emergencyId),
            'ocurrido_en' => $evento['ocurrido_en'],
            'tic_id_inscripcion' => isset($evento['estudiante']['tic_id_inscripcion']) ? (int) $evento['estudiante']['tic_id_inscripcion'] : null,
            'numero_documento' => $documento,
            'sige_carnet_id' => (int) $carnetId,
            'uid_rfid' => isset($evento['carnet']['uid_rfid']) ? trim((string) $evento['carnet']['uid_rfid']) : null,
            'estado' => $estado,
            'motivo' => strtoupper(trim((string) ($evento['carnet']['motivo'] ?? 'NO_APLICA'))),
            'requiere_reactivacion' => $evento['tipo'] === 'CARNET_REACTIVADO' ? 0 :
                (filter_var($evento['requiere_reactivacion'] ?? ($evento['carnet']['requiere_reactivacion'] ?? false), FILTER_VALIDATE_BOOLEAN) ? 1 : 0),
            'fecha_emision' => $this->fechaOpcional($evento['carnet']['fecha_emision'] ?? null),
            'vigencia_hasta' => $this->fechaOpcional($evento['carnet']['vigencia_hasta'] ?? null)
        );
    }

    private function fechaOpcional($valor) {
        if ($valor === null || $valor === '') return null;
        $fechaSoloDia = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        if ($fechaSoloDia && $fechaSoloDia->format('Y-m-d') === $valor) {
            return $valor . ' 00:00:00';
        }
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $valor);
        if (!$fecha || $fecha->format('Y-m-d H:i:s') !== $valor) throw new SigeEventoInvalido('Fecha de carnet inválida.');
        return $valor;
    }

    private function normalizarFecha($valor) {
        $v = trim((string) $valor);
        if ($v === '' || $v === '0000-00-00 00:00:00' || $v === '0000-00-00') return date('Y-m-d H:i:s');
        try {
            $dt = new DateTimeImmutable($v);
            return $dt->format('Y-m-d H:i:s');
        } catch (Throwable $e) {
            return date('Y-m-d H:i:s');
        }
    }

    private function esUuid($valor) {
        return is_string($valor) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $valor) === 1;
    }

    public function consultarEstadoOrdenOutbox($idOperacion) {
        $pdo = $this->conexion();
        $stmt = $pdo->prepare(
            "SELECT id_operacion, tipo, estado, ultimo_http_code, ultimo_error, creado_en, actualizado_en
             FROM sige_orden_outbox WHERE id_operacion = :op LIMIT 1"
        );
        $stmt->execute(array(':op' => strtolower(trim((string)$idOperacion))));
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function revertirChipInscripcion($idInscripcion) {
        $pdo = $this->conexion();
        $stmt = $pdo->prepare("UPDATE inscripcion SET chip_carnet = 'NO', uid_rfid = NULL WHERE id_inscripcion = :id");
        $stmt->execute(array(':id' => (int)$idInscripcion));
    }

    private function uuidV4() {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
    }
}
