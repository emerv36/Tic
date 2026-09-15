<?php
require_once(__DIR__ . '/../Config/PDOconn.php');

class SigeEventoDuplicadoConflictivo extends RuntimeException {}
class SigeEventoInvalido extends InvalidArgumentException {}

class SigeCarnet {
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
                "SELECT payload_hash FROM sige_carnet_evento WHERE id_evento = :id FOR UPDATE"
            );
            $existente->execute(array(':id' => $normalizado['id_evento']));
            $filaExistente = $existente->fetch();
            if ($filaExistente) {
                $pdo->rollBack();
                if (!hash_equals($filaExistente['payload_hash'], $hash)) {
                    throw new SigeEventoDuplicadoConflictivo(
                        'El id_evento ya existe con un payload diferente.'
                    );
                }
                return array('duplicado' => true, 'id_evento' => $normalizado['id_evento']);
            }

            $idInscripcion = $this->resolverInscripcion(
                $pdo,
                $normalizado['tic_id_inscripcion'],
                $normalizado['numero_documento']
            );

            $insertEvento = $pdo->prepare(
                "INSERT INTO sige_carnet_evento
                 (id_evento, tipo_evento, id_inscripcion, numero_documento, sige_carnet_id,
                  uid_rfid, ocurrido_en, payload, payload_hash, procesado)
                 VALUES
                 (:id_evento, :tipo, :id_inscripcion, :documento, :carnet_id,
                  :uid, :ocurrido_en, :payload, :payload_hash, 0)"
            );
            $insertEvento->execute(array(
                ':id_evento' => $normalizado['id_evento'],
                ':tipo' => $normalizado['tipo'],
                ':id_inscripcion' => $idInscripcion,
                ':documento' => $normalizado['numero_documento'],
                ':carnet_id' => $normalizado['sige_carnet_id'],
                ':uid' => $normalizado['uid_rfid'],
                ':ocurrido_en' => $normalizado['ocurrido_en'],
                ':payload' => $payload,
                ':payload_hash' => $hash
            ));

            if ($idInscripcion !== null) {
                $desmarcar = $pdo->prepare(
                    "UPDATE sige_carnet_proyeccion
                     SET es_actual = 0
                     WHERE id_inscripcion = :id_inscripcion
                       AND sige_carnet_id <> :carnet_id
                       AND es_actual = 1"
                );
                $desmarcar->execute(array(
                    ':id_inscripcion' => $idInscripcion,
                    ':carnet_id' => $normalizado['sige_carnet_id']
                ));
            }

            $upsert = $pdo->prepare(
                "INSERT INTO sige_carnet_proyeccion
                 (sige_carnet_id, id_inscripcion, numero_documento, uid_rfid,
                  estado_operativo, motivo_inactivacion, fecha_emision, vigencia_hasta,
                  es_actual, ultimo_evento_id, version, actualizado_en)
                 VALUES
                 (:carnet_id, :id_inscripcion, :documento, :uid,
                  :estado, :motivo, :fecha_emision, :vigencia_hasta,
                  1, :evento_id, 1, :actualizado_en)
                 ON DUPLICATE KEY UPDATE
                    id_inscripcion = VALUES(id_inscripcion),
                    numero_documento = VALUES(numero_documento),
                    uid_rfid = VALUES(uid_rfid),
                    estado_operativo = VALUES(estado_operativo),
                    motivo_inactivacion = VALUES(motivo_inactivacion),
                    fecha_emision = VALUES(fecha_emision),
                    vigencia_hasta = VALUES(vigencia_hasta),
                    es_actual = 1,
                    ultimo_evento_id = VALUES(ultimo_evento_id),
                    version = version + 1,
                    actualizado_en = VALUES(actualizado_en)"
            );
            $upsert->execute(array(
                ':carnet_id' => $normalizado['sige_carnet_id'],
                ':id_inscripcion' => $idInscripcion,
                ':documento' => $normalizado['numero_documento'],
                ':uid' => $normalizado['uid_rfid'],
                ':estado' => $normalizado['estado'],
                ':motivo' => $normalizado['motivo'],
                ':fecha_emision' => $normalizado['fecha_emision'],
                ':vigencia_hasta' => $normalizado['vigencia_hasta'],
                ':evento_id' => $normalizado['id_evento'],
                ':actualizado_en' => $normalizado['ocurrido_en']
            ));

            $marcar = $pdo->prepare(
                "UPDATE sige_carnet_evento SET procesado = 1 WHERE id_evento = :id"
            );
            $marcar->execute(array(':id' => $normalizado['id_evento']));
            $pdo->commit();
            return array(
                'duplicado' => false,
                'id_evento' => $normalizado['id_evento'],
                'id_inscripcion' => $idInscripcion
            );
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }
    }

    public function obtenerProyeccionActual($idInscripcion) {
        $pdo = $this->conexion();
        $stmt = $pdo->prepare(
            "SELECT * FROM sige_carnet_proyeccion
             WHERE id_inscripcion = :id AND es_actual = 1
             ORDER BY actualizado_en DESC, id DESC LIMIT 1"
        );
        $stmt->execute(array(':id' => $idInscripcion));
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function crearOrdenReactivacion($idInscripcion, $fechaAutorizacion = null) {
        $fechaAutorizacion = $fechaAutorizacion ?: date('Y-m-d H:i:s');
        $pdo = $this->conexion();
        $pdo->beginTransaction();
        try {
            $inscripcion = $pdo->prepare(
                "SELECT id_inscripcion, identificacion
                 FROM inscripcion WHERE id_inscripcion = :id FOR UPDATE"
            );
            $inscripcion->execute(array(':id' => $idInscripcion));
            $estudiante = $inscripcion->fetch();
            if (!$estudiante) {
                throw new DomainException('Inscripción no encontrada.');
            }

            $proyeccion = $pdo->prepare(
                "SELECT * FROM sige_carnet_proyeccion
                 WHERE id_inscripcion = :id AND es_actual = 1
                 ORDER BY actualizado_en DESC, id DESC LIMIT 1 FOR UPDATE"
            );
            $proyeccion->execute(array(':id' => $idInscripcion));
            $carnet = $proyeccion->fetch();
            if (!$carnet) {
                throw new DomainException('No existe un carnet proyectado para reactivar.');
            }
            if ($carnet['estado_operativo'] !== 'INACTIVO') {
                throw new DomainException('El carnet no está inactivo.');
            }
            if (in_array($carnet['motivo_inactivacion'], array('PERDIDA', 'ROBO'), true)) {
                throw new DomainException('El carnet requiere un nuevo UID RFID.');
            }

            $pendiente = $pdo->prepare(
                "SELECT id_operacion FROM sige_orden_outbox
                 WHERE id_inscripcion = :id
                   AND tipo = 'REACTIVACION_AUTORIZADA'
                   AND estado IN ('PENDIENTE','ENVIANDO','REINTENTO')
                 ORDER BY id DESC LIMIT 1 FOR UPDATE"
            );
            $pendiente->execute(array(':id' => $idInscripcion));
            $existente = $pendiente->fetchColumn();
            if ($existente) {
                $pdo->commit();
                return array('id_operacion' => $existente, 'duplicado' => true);
            }

            $idOperacion = $this->uuidV4();
            $payloadArray = array(
                'numero_documento' => $estudiante['identificacion'],
                'evento_carnet' => array(
                    'id_operacion' => $idOperacion,
                    'tipo' => 'REACTIVACION_AUTORIZADA',
                    'fecha_autorizacion' => $fechaAutorizacion
                )
            );
            $payload = json_encode($payloadArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($payload === false) {
                throw new RuntimeException('No fue posible serializar la orden.');
            }
            $insert = $pdo->prepare(
                "INSERT INTO sige_orden_outbox
                 (id_operacion, tipo, id_inscripcion, numero_documento, payload, estado,
                  intentos, proximo_intento_en, creado_en, actualizado_en)
                 VALUES
                 (:operacion, 'REACTIVACION_AUTORIZADA', :id, :documento, :payload,
                  'PENDIENTE', 0, :proximo_intento_en, :creado_en, :actualizado_en)"
            );
            $insert->execute(array(
                ':operacion' => $idOperacion,
                ':id' => $idInscripcion,
                ':documento' => $estudiante['identificacion'],
                ':payload' => $payload,
                ':proximo_intento_en' => $fechaAutorizacion,
                ':creado_en' => $fechaAutorizacion,
                ':actualizado_en' => $fechaAutorizacion
            ));
            $pdo->commit();
            return array('id_operacion' => $idOperacion, 'duplicado' => false);
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }
    }

    private function resolverInscripcion(PDO $pdo, $ticId, $documento) {
        if ($ticId !== null) {
            $stmt = $pdo->prepare(
                "SELECT id_inscripcion FROM inscripcion
                 WHERE id_inscripcion = :id AND identificacion = :documento LIMIT 1"
            );
            $stmt->execute(array(':id' => $ticId, ':documento' => $documento));
            $id = $stmt->fetchColumn();
            if ($id !== false) {
                return (int) $id;
            }
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
        foreach (array('id_evento', 'tipo', 'ocurrido_en', 'estudiante', 'carnet') as $campo) {
            if (!array_key_exists($campo, $evento)) {
                throw new SigeEventoInvalido('Falta el campo ' . $campo . '.');
            }
        }
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $evento['id_evento'])) {
            throw new SigeEventoInvalido('id_evento no es un UUID válido.');
        }
        $tipos = array(
            'CARNET_ASIGNADO', 'CARNET_BLOQUEADO', 'CARNET_REACTIVADO',
            'CARNET_REEMPLAZADO', 'CARNET_VENCIDO'
        );
        if (!in_array($evento['tipo'], $tipos, true)) {
            throw new SigeEventoInvalido('Tipo de evento no soportado.');
        }
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
        return array(
            'id_evento' => strtolower($evento['id_evento']),
            'tipo' => $evento['tipo'],
            'ocurrido_en' => $evento['ocurrido_en'],
            'tic_id_inscripcion' => isset($evento['estudiante']['tic_id_inscripcion'])
                ? (int) $evento['estudiante']['tic_id_inscripcion'] : null,
            'numero_documento' => $documento,
            'sige_carnet_id' => (int) $carnetId,
            'uid_rfid' => isset($evento['carnet']['uid_rfid']) ? trim((string) $evento['carnet']['uid_rfid']) : null,
            'estado' => $estado,
            'motivo' => strtoupper(trim((string) ($evento['carnet']['motivo'] ?? 'NO_APLICA'))),
            'fecha_emision' => $this->fechaOpcional($evento['carnet']['fecha_emision'] ?? null),
            'vigencia_hasta' => $this->fechaOpcional($evento['carnet']['vigencia_hasta'] ?? null)
        );
    }

    private function fechaOpcional($valor) {
        if ($valor === null || $valor === '') {
            return null;
        }
        $fechaSoloDia = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        if ($fechaSoloDia && $fechaSoloDia->format('Y-m-d') === $valor) {
            return $valor . ' 00:00:00';
        }
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $valor);
        if (!$fecha || $fecha->format('Y-m-d H:i:s') !== $valor) {
            throw new SigeEventoInvalido('Fecha de carnet inválida.');
        }
        return $valor;
    }

    private function uuidV4() {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' .
               substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
    }
}
