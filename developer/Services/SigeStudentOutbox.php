<?php
require_once(__DIR__ . '/../Config/PDOconn.php');
require_once(__DIR__ . '/SigeWebhook.php');

/** Cola durable para converger los datos maestros de estudiantes TIC -> SIGE. */
class SigeStudentOutbox {
    const MAX_INTENTOS = 12;

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

    public function enviarEstudiante($idInscripcion, $evento = 'ACTUALIZAR') {
        $idInscripcion = filter_var($idInscripcion, FILTER_VALIDATE_INT);
        $evento = strtoupper(trim((string) $evento));
        if ($idInscripcion === false || $idInscripcion < 1) {
            throw new InvalidArgumentException('Inscripción inválida para sincronización SIGE.');
        }
        if ($evento === '' || strlen($evento) > 50 || !preg_match('/^[A-Z0-9_]+$/', $evento)) {
            throw new InvalidArgumentException('Evento de estudiante inválido.');
        }

        $pdo = $this->conexion();
        $idOperacion = $this->uuidV4();
        $stmt = $pdo->prepare(
            "INSERT INTO sige_estudiante_outbox
             (id_operacion, id_inscripcion, evento, estado, intentos, proximo_intento_en,
              creado_en, actualizado_en)
             VALUES
             (:operacion, :inscripcion, :evento, 'PENDIENTE', 0, :proximo, :creado, :actualizado)"
        );
        $ahora = date('Y-m-d H:i:s');
        $stmt->execute(array(
            ':operacion' => $idOperacion,
            ':inscripcion' => $idInscripcion,
            ':evento' => $evento,
            ':proximo' => $ahora,
            ':creado' => $ahora,
            ':actualizado' => $ahora
        ));

        try {
            $this->procesarPendientes(1);
        } catch (Throwable $ex) {
            error_log('Outbox estudiante SIGE pendiente: ' . $ex->getMessage());
        }

        $estado = $pdo->prepare("SELECT estado FROM sige_estudiante_outbox WHERE id_operacion = :id");
        $estado->execute(array(':id' => $idOperacion));
        return $estado->fetchColumn() === 'ENVIADA';
    }

    public function procesarPendientes($limite = 25) {
        $limite = max(1, min(100, (int) $limite));
        $pdo = $this->conexion();
        $pdo->exec(
            "UPDATE sige_estudiante_outbox
             SET estado = 'REINTENTO', locked_by = NULL, locked_at = NULL,
                 proximo_intento_en = NOW(), ultimo_error = 'Lease ENVIANDO expirado',
                 actualizado_en = NOW()
             WHERE estado = 'ENVIANDO' AND locked_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)"
        );

        $resumen = array('reclamadas' => 0, 'enviadas' => 0, 'reintentos' => 0, 'fallidas' => 0);
        while ($resumen['reclamadas'] < $limite) {
            $fila = $this->reclamarSiguiente($pdo);
            if (!$fila) break;
            $resumen['reclamadas']++;
            $resultado = $this->enviarYFinalizar($pdo, $fila);
            $resumen[$resultado]++;
        }
        return $resumen;
    }

    private function reclamarSiguiente(PDO $pdo) {
        $worker = $this->uuidV4();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                "SELECT o.* FROM sige_estudiante_outbox o
                 WHERE o.estado IN ('PENDIENTE','REINTENTO') AND o.proximo_intento_en <= NOW()
                   AND NOT EXISTS (
                       SELECT 1 FROM sige_estudiante_outbox anterior
                       WHERE anterior.id_inscripcion = o.id_inscripcion AND anterior.id < o.id
                         AND anterior.estado IN ('PENDIENTE','REINTENTO','ENVIANDO')
                   )
                 ORDER BY o.id ASC LIMIT 1 FOR UPDATE"
            );
            $stmt->execute();
            $fila = $stmt->fetch();
            if (!$fila) {
                $pdo->commit();
                return null;
            }
            $update = $pdo->prepare(
                "UPDATE sige_estudiante_outbox
                 SET estado = 'ENVIANDO', intentos = intentos + 1,
                     locked_by = :worker, locked_at = NOW(), actualizado_en = NOW()
                 WHERE id = :id"
            );
            $update->execute(array(':worker' => $worker, ':id' => $fila['id']));
            $pdo->commit();
            $fila['intentos'] = (int) $fila['intentos'] + 1;
            $fila['locked_by'] = $worker;
            return $fila;
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $ex;
        }
    }

    private function enviarYFinalizar(PDO $pdo, array $fila) {
        $exitoso = false;
        $error = '';
        try {
            $exitoso = (new SigeWebhook())->enviarEstudiante(
                (int) $fila['id_inscripcion'],
                (string) $fila['evento'],
                (string) $fila['id_operacion'],
                $fila['estado_academico_fijado'] ?? null
            );
            if (!$exitoso) $error = 'SIGE no confirmó el webhook; consultar sige_webhook_log.';
        } catch (Throwable $ex) {
            $error = $ex->getMessage();
        }

        if ($exitoso) {
            $stmt = $pdo->prepare(
                "UPDATE sige_estudiante_outbox
                 SET estado = 'ENVIADA', enviado_en = NOW(), ultimo_error = NULL,
                     locked_by = NULL, locked_at = NULL, actualizado_en = NOW()
                 WHERE id = :id AND locked_by = :worker"
            );
            $stmt->execute(array(':id' => $fila['id'], ':worker' => $fila['locked_by']));
            return 'enviadas';
        }

        $terminal = (int) $fila['intentos'] >= self::MAX_INTENTOS;
        $estado = $terminal ? 'FALLIDA' : 'REINTENTO';
        $stmt = $pdo->prepare(
            "UPDATE sige_estudiante_outbox
             SET estado = :estado, proximo_intento_en = :proximo, ultimo_error = :error,
                 locked_by = NULL, locked_at = NULL, actualizado_en = NOW()
             WHERE id = :id AND locked_by = :worker"
        );
        $stmt->execute(array(
            ':estado' => $estado,
            ':proximo' => $this->siguienteIntento((int) $fila['intentos']),
            ':error' => substr($error !== '' ? $error : 'Error de sincronización no especificado.', 0, 1000),
            ':id' => $fila['id'],
            ':worker' => $fila['locked_by']
        ));
        return $terminal ? 'fallidas' : 'reintentos';
    }

    private function siguienteIntento($intentos) {
        if ($intentos <= 1) $minutos = 1;
        elseif ($intentos === 2) $minutos = 5;
        elseif ($intentos === 3) $minutos = 15;
        else $minutos = 30;
        return date('Y-m-d H:i:s', time() + ($minutos * 60));
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
