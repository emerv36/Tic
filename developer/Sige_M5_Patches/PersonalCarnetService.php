<?php
require_once __DIR__ . '/PersonalEligibilityService.php';

class PersonalCarnetService {
    private $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function procesar(array $payload) {
        $idOperacion = $payload['id_operacion'] ?? null;
        $tipo = $payload['tipo'] ?? null;
        $personaUuid = $payload['persona_uuid'] ?? null;
        $uidRfid = $payload['uid_rfid'] ?? null;
        $expectedVersion = $payload['expected_persona_version'] ?? null;
        
        if (!$idOperacion || !$tipo || !$personaUuid || $expectedVersion === null) {
            return ['http_status' => 400, 'status' => 'ERROR', 'message' => 'Faltan campos requeridos'];
        }

        $payloadJson = json_encode($payload);
        $payloadHash = hash('sha256', $payloadJson);

        try {
            $this->db->beginTransaction();

            // 1. Verificar idempotencia
            $stmt = $this->db->prepare("SELECT id FROM integracion_comandos WHERE id_operacion = :id");
            $stmt->execute(['id' => $idOperacion]);
            if ($stmt->fetch()) {
                $this->db->rollBack();
                return ['http_status' => 202, 'status' => 'OK', 'message' => 'Operación previamente procesada'];
            }

            // Bloquear persona y verificar versión
            $stmt = $this->db->prepare("SELECT id, version_estado FROM personas WHERE uuid = :uuid FOR UPDATE");
            $stmt->execute(['uuid' => $personaUuid]);
            $persona = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$persona) {
                $this->db->rollBack();
                return ['http_status' => 404, 'status' => 'ERROR', 'message' => 'Persona no encontrada'];
            }

            if ((int)$persona['version_estado'] !== (int)$expectedVersion) {
                $this->db->rollBack();
                return ['http_status' => 409, 'status' => 'ERROR', 'message' => 'Conflicto de versiones. TIC debe actualizar primero.'];
            }
            
            // 2. Ejecutar la acción de carné
            $estadoCarnet = null;
            $motivo = $payload['motivo'] ?? null;
            
            if (in_array($tipo, ['ASIGNACION', 'REEMPLAZO'])) {
                if (!$uidRfid) {
                    $this->db->rollBack();
                    return ['http_status' => 400, 'status' => 'ERROR', 'message' => 'uid_rfid es requerido para ' . $tipo];
                }
                
                // Invariante: uid_rfid debe ser único en carnets
                $stmt = $this->db->prepare("SELECT id FROM carnets WHERE uid_rfid = :uid AND estado = 'ACTIVO' LIMIT 1 FOR UPDATE");
                $stmt->execute(['uid' => $uidRfid]);
                if ($stmt->fetch()) {
                    $this->db->rollBack();
                    return ['http_status' => 409, 'status' => 'ERROR', 'message' => 'El UID RFID ya está en uso.'];
                }
                
                // Inactivar carnets anteriores
                $stmt = $this->db->prepare("UPDATE carnets SET estado = 'INACTIVO' WHERE persona_id = :pid AND estado = 'ACTIVO'");
                $stmt->execute(['pid' => $persona['id']]);
                
                // Insertar nuevo carné (vigencia_hasta = NULL para personal)
                $stmt = $this->db->prepare(
                    "INSERT INTO carnets (persona_id, uid_rfid, estado, vigencia_hasta, emitido_en) 
                     VALUES (:pid, :uid, 'ACTIVO', NULL, NOW())"
                );
                $stmt->execute(['pid' => $persona['id'], 'uid' => $uidRfid]);
                $estadoCarnet = 'EMITIDO';
                
            } elseif ($tipo === 'BLOQUEO') {
                // Inactivar carnets anteriores
                $stmt = $this->db->prepare("UPDATE carnets SET estado = 'BLOQUEADO' WHERE persona_id = :pid AND estado = 'ACTIVO'");
                $stmt->execute(['pid' => $persona['id']]);
                $estadoCarnet = 'BLOQUEADO';
            } elseif ($tipo === 'ENTREGA_CONFIRMADA') {
                $estadoCarnet = 'ENTREGADO';
                // En SIGE no hay un campo específico de entregado en carnets actualmente, 
                // pero el evento indicará a TIC que se entregó.
            }
            
            // Incrementar la versión de la persona
            $nuevaVersion = (int)$persona['version_estado'] + 1;
            $stmt = $this->db->prepare("UPDATE personas SET version_estado = :v WHERE id = :id");
            $stmt->execute(['v' => $nuevaVersion, 'id' => $persona['id']]);
            
            // Insertar el comando en la tabla de idempotencia
            $stmt = $this->db->prepare(
                "INSERT INTO integracion_comandos (id_operacion, tipo, numero_documento, expected_version, payload_hash, payload)
                 VALUES (:id_operacion, :tipo, :documento, :expected_version, :payload_hash, :payload)"
            );
            // Reutilizamos numero_documento = uuid por la estructura legada (aunque idealmente debería ser persona_uuid). 
            // Para mantener compatibilidad con integracion_comandos, ponemos el uuid en numero_documento.
            $stmt->execute([
                'id_operacion' => $idOperacion,
                'tipo' => $tipo,
                'documento' => $personaUuid, 
                'expected_version' => $expectedVersion,
                'payload_hash' => $payloadHash,
                'payload' => $payloadJson
            ]);

            // Generar evento
            $idEvento = self::uuidV4();
            $eventoPayload = [
                'contract_version' => '1.0',
                'id_evento' => $idEvento,
                'tipo_evento' => 'PERSONA_CARNET_ACTUALIZADO',
                'persona_uuid' => $personaUuid,
                'version_estado' => $nuevaVersion,
                'ocurrido_en' => date('Y-m-d H:i:s'),
                'carnet' => [
                    'estado' => $estadoCarnet,
                    'uid_rfid' => in_array($tipo, ['ASIGNACION', 'REEMPLAZO']) ? $uidRfid : null,
                    'motivo' => $motivo,
                    'vigencia_hasta' => null
                ]
            ];
            
            $eventoJson = json_encode($eventoPayload);
            $eventoHash = hash('sha256', $eventoJson);
            
            $stmt = $this->db->prepare(
                "INSERT INTO integracion_eventos_outbox (id_evento, tipo, documento, payload_hash, payload)
                 VALUES (:id_evento, :tipo, :documento, :payload_hash, :payload)"
            );
            $stmt->execute([
                'id_evento' => $idEvento,
                'tipo' => 'PERSONA_CARNET_ACTUALIZADO',
                'documento' => $personaUuid,
                'payload_hash' => $eventoHash,
                'payload' => $eventoJson
            ]);

            $this->db->commit();
            return ['http_status' => 202, 'status' => 'OK', 'message' => 'Procesado correctamente'];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("PersonalCarnetService Error: " . $e->getMessage());
            return ['http_status' => 500, 'status' => 'ERROR', 'message' => 'Error interno en SIGE'];
        }
    }
    
    private static function uuidV4() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
