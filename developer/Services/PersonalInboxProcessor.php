<?php
require_once(__DIR__ . '/../Config/PDOconn.php');

class PersonalInboxProcessor {
    private $pdo;

    public function __construct(PDO $pdo = null) {
        if ($pdo) {
            $this->pdo = $pdo;
        } else {
            $this->pdo = new PDO(connstring, user, pass, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ));
            $this->pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->pdo->exec("SET time_zone = '-05:00'");
        }
    }

    public function procesarPendientes($limite = 50) {
        $limite = max(1, min(200, (int)$limite));
        $stmt = $this->pdo->prepare("SELECT * FROM sige_personal_inbox WHERE procesado = 0 ORDER BY id ASC LIMIT :limite");
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        $eventos = $stmt->fetchAll();

        return $this->procesarRegistros($eventos);
    }

    public function procesarEventos(array $idsEvento) {
        $idsEvento = array_values(array_unique($idsEvento));
        if (count($idsEvento) < 1 || count($idsEvento) > 200) throw new InvalidArgumentException('La lista de eventos debe contener entre 1 y 200 IDs.');
        foreach ($idsEvento as $id) {
            if (!is_string($id) || preg_match('/^[0-9a-f-]{36}$/', $id) !== 1) throw new InvalidArgumentException('id_evento inválido.');
        }
        $placeholders = implode(',', array_fill(0, count($idsEvento), '?'));
        $stmt = $this->pdo->prepare("SELECT * FROM sige_personal_inbox WHERE procesado = 0 AND id_evento IN ($placeholders) ORDER BY version_estado ASC, id ASC");
        $stmt->execute($idsEvento);
        return $this->procesarRegistros($stmt->fetchAll());
    }

    private function procesarRegistros(array $eventos) {
        $resumen = array('seleccionados' => count($eventos), 'procesados' => 0, 'pendientes' => 0, 'fallidos' => 0);
        foreach ($eventos as $evento) {
            $resultado = $this->procesarEvento($evento);
            if ($resultado === 'PROCESADO' || $resultado === 'OBSOLETO') $resumen['procesados']++;
            elseif ($resultado === 'PENDIENTE') $resumen['pendientes']++;
            else $resumen['fallidos']++;
        }
        return $resumen;
    }

    private function procesarEvento($eventoRecord) {
        $idInbox = $eventoRecord['id'];
        $payload = json_decode($eventoRecord['payload'], true);
        $personaUuid = $eventoRecord['persona_uuid'];
        $versionRecibida = (int) $eventoRecord['version_estado'];
        $idEvento = $eventoRecord['id_evento'];
        $tipoEvento = $eventoRecord['tipo_evento'];

        try {
            $this->pdo->beginTransaction();

            $forUpdate = ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') ? '' : 'FOR UPDATE';
            $stmt = $this->pdo->prepare("SELECT * FROM sige_personal_proyeccion WHERE persona_uuid = :uuid LIMIT 1 $forUpdate");
            $stmt->execute(array(':uuid' => $personaUuid));
            $proyeccion = $stmt->fetch();

            $versionActual = $proyeccion ? (int)$proyeccion['version_estado'] : 0;
            $versionEsperada = $versionActual + 1;

            if ($versionRecibida < $versionEsperada) {
                $aplicado = $this->pdo->prepare("SELECT id_evento FROM sige_personal_inbox WHERE persona_uuid = :uuid AND version_estado = :version AND procesado = 1 ORDER BY id ASC LIMIT 1");
                $aplicado->execute(array(':uuid' => $personaUuid, ':version' => $versionRecibida));
                $eventoAplicado = $aplicado->fetchColumn();
                if ($eventoAplicado === false || !hash_equals((string)$eventoAplicado, (string)$idEvento)) {
                    $this->registrarConflictoBrecha($personaUuid, $idEvento, $versionRecibida, $versionRecibida);
                    $this->pdo->commit();
                    $detalle = $eventoAplicado === false
                        ? 'Conflicto: no existe evidencia inbox del evento que aplicó esta versión.'
                        : 'Conflicto: la versión ya fue aplicada por otro id_evento.';
                    $this->incrementarFallo($idInbox, $detalle);
                    return 'PENDIENTE';
                }
                $this->marcarProcesado($idInbox, 'Obsoleto. Versión ' . $versionRecibida . ' < ' . $versionEsperada);
                $this->pdo->commit();
                return 'OBSOLETO';
            }

            if ($versionRecibida > $versionEsperada) {
                // Brecha de versión! Faltan eventos.
                $this->registrarConflictoBrecha($personaUuid, $idEvento, $versionEsperada, $versionRecibida);
                $this->pdo->commit();
                $this->incrementarFallo($idInbox, 'Brecha de versión. Esperaba ' . $versionEsperada . ' pero llegó ' . $versionRecibida);
                return 'PENDIENTE';
            }

            // Si llegamos acá, $versionRecibida === $versionEsperada. Procesamos!
            $antesJson = $proyeccion ? json_encode($proyeccion, JSON_UNESCAPED_UNICODE) : null;
            
            $this->aplicarCambios($proyeccion, $payload, $personaUuid, $versionRecibida);

            $stmtUpdated = $this->pdo->prepare("SELECT * FROM sige_personal_proyeccion WHERE persona_uuid = :uuid");
            $stmtUpdated->execute(array(':uuid' => $personaUuid));
            $proyeccionDespues = $stmtUpdated->fetch();
            $despuesJson = $proyeccionDespues ? json_encode($proyeccionDespues, JSON_UNESCAPED_UNICODE) : null;

            // Auditoria local
            $this->registrarAuditoria($personaUuid, $idEvento, $tipoEvento, $antesJson, $despuesJson, $payload);

            // Marcar inbox
            $this->marcarProcesado($idInbox);

            // Cerrar conflictos pendientes
            $this->pdo->prepare("UPDATE sige_personal_conflictos SET estado_resolucion = 'RESUELTO', resuelto_en = CURRENT_TIMESTAMP WHERE persona_uuid = :uuid AND version_esperada <= :ver AND estado_resolucion = 'PENDIENTE'")
                 ->execute(array(':uuid' => $personaUuid, ':ver' => $versionRecibida));

            $this->pdo->commit();
            return 'PROCESADO';
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->incrementarFallo($idInbox, $e->getMessage());
            error_log("Error procesando inbox $idInbox: " . $e->getMessage());
            return 'FALLIDO';
        }
    }

    private function aplicarCambios($proyeccionExistente, $payload, $personaUuid, $version) {
        $tipo = $payload['tipo'] ?? '';
        $data = $payload['data'] ?? array();
        
        $params = array(':uuid' => $personaUuid, ':version' => $version);
        
        if (!$proyeccionExistente) {
            $this->insertarProyeccionInicial($personaUuid, $version, $data);
            $proyeccionExistente = array('vinculos_json' => '[]');
        } else {
            $this->pdo->prepare("UPDATE sige_personal_proyeccion SET version_estado = :version, actualizado_en = CURRENT_TIMESTAMP WHERE persona_uuid = :uuid")
                 ->execute($params);
        }

        if (in_array($tipo, array('PERSONA_CREADA', 'PERSONA_ACTUALIZADA'))) {
            $this->pdo->prepare(
                "UPDATE sige_personal_proyeccion 
                 SET tipo_documento = :td, numero_documento = :nd, nombres = :nom, apellidos = :ape, estado_persona = :est 
                 WHERE persona_uuid = :uuid"
            )->execute(array(
                ':td' => $data['tipo_documento'] ?? '',
                ':nd' => $data['numero_documento'] ?? '',
                ':nom' => $data['nombres'] ?? '',
                ':ape' => $data['apellidos'] ?? '',
                ':est' => $data['estado'] ?? 'ACTIVA',
                ':uuid' => $personaUuid
            ));
        }
        
        if (in_array($tipo, array('VINCULO_CREADO', 'VINCULO_ACTUALIZADO', 'VINCULO_REACTIVADO', 'VINCULO_DESACTIVADO'))) {
            $vinculos = json_decode($proyeccionExistente['vinculos_json'] ?? '[]', true);
            if (!is_array($vinculos)) $vinculos = array();
            
            $encontrado = false;
            foreach ($vinculos as &$v) {
                if (isset($v['vinculo_uuid']) && $v['vinculo_uuid'] === $data['vinculo_uuid']) {
                    $v = $data;
                    $encontrado = true;
                    break;
                }
            }
            if (!$encontrado) {
                $vinculos[] = $data;
            }
            
            $this->pdo->prepare("UPDATE sige_personal_proyeccion SET vinculos_json = :vjson WHERE persona_uuid = :uuid")
                 ->execute(array(':vjson' => json_encode($vinculos, JSON_UNESCAPED_UNICODE), ':uuid' => $personaUuid));
        }
        
        if ($tipo === 'FOTO_ACTUALIZADA') {
            $this->pdo->prepare("UPDATE sige_personal_proyeccion SET tiene_foto = 1 WHERE persona_uuid = :uuid")
                 ->execute(array(':uuid' => $personaUuid));
        }

        if ($tipo === 'PERSONA_CARNET_ACTUALIZADO') {
            $carnet = $payload['carnet'] ?? array();
            $this->pdo->prepare(
                "UPDATE sige_personal_proyeccion 
                 SET carnet_estado = :estado, 
                     uid_rfid = :rfid, 
                     carnet_motivo = :motivo, 
                     vigencia_hasta = :vigencia
                 WHERE persona_uuid = :uuid"
            )->execute(array(
                ':estado' => $carnet['estado'] ?? null,
                ':rfid' => $carnet['uid_rfid'] ?? null,
                ':motivo' => $carnet['motivo'] ?? null,
                ':vigencia' => $carnet['vigencia_hasta'] ?? null,
                ':uuid' => $personaUuid
            ));
        }

        if (in_array($tipo, array('CARNET_ASIGNADO', 'CARNET_REEMPLAZADO', 'CARNET_BLOQUEADO', 'CARNET_ENTREGADO'))) {
            $this->actualizarCarnetProyeccion($personaUuid, $data);
        }

        if ($tipo === 'ESTADO_INSTITUCIONAL_CAMBIADO') {
            $this->pdo->prepare(
                "UPDATE sige_personal_proyeccion SET estado_persona = :est WHERE persona_uuid = :uuid"
            )->execute(array(':est' => $data['persona_estado'] ?? 'ACTIVA', ':uuid' => $personaUuid));
            
            $vinculos = json_decode($proyeccionExistente['vinculos_json'] ?? '[]', true);
            if (!is_array($vinculos)) $vinculos = array();
            
            $afectados = $data['vinculos_afectados'] ?? array();
            foreach ($afectados as $afectado) {
                $encontrado = false;
                foreach ($vinculos as &$v) {
                    if (isset($v['vinculo_uuid']) && isset($afectado['vinculo_uuid']) && $v['vinculo_uuid'] === $afectado['vinculo_uuid']) {
                        $v = $afectado;
                        $encontrado = true;
                        break;
                    }
                }
                if (!$encontrado) $vinculos[] = $afectado;
            }
            
            $this->pdo->prepare("UPDATE sige_personal_proyeccion SET vinculos_json = :vjson WHERE persona_uuid = :uuid")
                 ->execute(array(':vjson' => json_encode($vinculos, JSON_UNESCAPED_UNICODE), ':uuid' => $personaUuid));
                 
            if (isset($data['carnet']) && is_array($data['carnet'])) {
                $this->actualizarCarnetProyeccion($personaUuid, $data['carnet']);
            }
        }
    }

    private function actualizarCarnetProyeccion($personaUuid, array $carnet) {
        $estadoVisual = ($carnet['estado'] ?? null) === 'INACTIVO'
            ? 'BLOQUEADO'
            : (!empty($carnet['entregado_en']) ? 'ENTREGADO' : 'EMITIDO');
        $this->pdo->prepare(
            "UPDATE sige_personal_proyeccion
             SET sige_carnet_id = :cid, uid_rfid = :uid, carnet_estado = :estado,
                 carnet_motivo = :motivo, vigencia_hasta = :vigencia
             WHERE persona_uuid = :uuid"
        )->execute(array(
            ':cid' => $carnet['sige_carnet_id'] ?? null,
            ':uid' => $carnet['uid_rfid'] ?? null,
            ':estado' => $estadoVisual,
            ':motivo' => $carnet['motivo'] ?? null,
            ':vigencia' => $carnet['vigencia_hasta'] ?? null,
            ':uuid' => $personaUuid
        ));
    }

    private function insertarProyeccionInicial($personaUuid, $version, $data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sige_personal_proyeccion 
             (persona_uuid, tipo_documento, numero_documento, nombres, apellidos, estado_persona, vinculos_json, version_estado) 
             VALUES 
             (:uuid, :td, :nd, :nom, :ape, :est, :vjson, :ver)"
        );
        $stmt->execute(array(
            ':uuid' => $personaUuid,
            ':td' => $data['tipo_documento'] ?? 'N/A',
            ':nd' => $data['numero_documento'] ?? 'N/A',
            ':nom' => $data['nombres'] ?? 'N/A',
            ':ape' => $data['apellidos'] ?? 'N/A',
            ':est' => $data['estado'] ?? 'ACTIVA',
            ':vjson' => '[]',
            ':ver' => $version
        ));
    }

    private function marcarProcesado($idInbox, $error = null) {
        $this->pdo->prepare("UPDATE sige_personal_inbox SET procesado = 1, error_proceso = :err WHERE id = :id")
             ->execute(array(':err' => $error, ':id' => $idInbox));
    }

    private function incrementarFallo($idInbox, $error) {
        try {
            $pdo2 = $this->pdo;
            $pdo2->prepare("UPDATE sige_personal_inbox SET error_proceso = :err WHERE id = :id")
                 ->execute(array(':err' => substr($error, 0, 2000), ':id' => $idInbox));
        } catch (Throwable $e) {}
    }

    private function registrarConflictoBrecha($personaUuid, $idEvento, $esperada, $recibida) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM sige_personal_conflictos WHERE id_operacion = :id_evt AND estado_resolucion = 'PENDIENTE'");
        $stmt->execute(array(':id_evt' => $idEvento));
        if (!$stmt->fetch()) {
            $this->pdo->prepare(
                "INSERT INTO sige_personal_conflictos (id_operacion, persona_uuid, version_esperada, version_actual_sige, estado_resolucion, comentario_resolucion)
                 VALUES (:id_evt, :uuid, :esperada, :recibida, 'PENDIENTE', 'Brecha de versión detectada. Falta procesar eventos anteriores.')"
            )->execute(array(
                ':id_evt' => $idEvento,
                ':uuid' => $personaUuid,
                ':esperada' => $esperada,
                ':recibida' => $recibida
            ));
        }
    }

    private function registrarAuditoria($personaUuid, $idEvento, $accion, $antes, $despues, $payload) {
        $actorId = null;
        $actorNombre = null;
        $actorRol = null;
        if (isset($payload['actor']) && is_array($payload['actor'])) {
            $actorNombre = $payload['actor']['nombre_snapshot'] ?? null;
            $actorRol = $payload['actor']['rol'] ?? null;
        }

        $this->pdo->prepare(
            "INSERT INTO sige_personal_auditoria 
             (persona_uuid, id_evento, accion, antes_json, despues_json, actor_id, actor_nombre, actor_rol, actor_origen)
             VALUES 
             (:uuid, :evt, :accion, :antes, :despues, :aid, :anom, :arol, 'SIGE')"
        )->execute(array(
            ':uuid' => $personaUuid,
            ':evt' => $idEvento,
            ':accion' => substr($accion, 0, 50),
            ':antes' => $antes,
            ':despues' => $despues,
            ':aid' => $actorId,
            ':anom' => substr($actorNombre ?? '', 0, 150),
            ':arol' => substr($actorRol ?? '', 0, 50)
        ));
    }
}
