<?php
require_once(__DIR__ . '/../Config/PDOconn.php');

class PersonalEventReceiverService {
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

    public function receive(string $rawPayload) {
        $maxSize = 5 * 1024 * 1024; // 5MB limit
        if (strlen($rawPayload) > $maxSize) {
            return array('status' => 400, 'message' => 'Payload excede el tamaño máximo permitido (5MB).', 'codigo' => 'PAYLOAD_TOO_LARGE');
        }

        $data = json_decode($rawPayload, true);
        if (!is_array($data)) {
            return array('status' => 400, 'message' => 'JSON inválido.', 'codigo' => 'INVALID_JSON');
        }

        $tipoEvento = $data['tipo'] ?? null;
        if (!is_string($tipoEvento) || trim($tipoEvento) === '') {
            return array('status' => 400, 'message' => 'Falta campo obligatorio: tipo', 'codigo' => 'MISSING_FIELD');
        }

        $required = array('contract_version', 'id_evento', 'id_operacion', 'tipo', 'entidad', 'persona_uuid', 'persona_version', 'origen', 'ocurrido_en', 'data');
        foreach ($required as $field) {
            if (!array_key_exists($field, $data) || ($field !== 'id_operacion' && ($data[$field] === null || (is_string($data[$field]) && trim($data[$field]) === '')))) {
                return array('status' => 400, 'message' => "Falta campo obligatorio: $field", 'codigo' => 'MISSING_FIELD');
            }
        }

        if (!$this->validarContrato($data)) {
            return array('status' => 422, 'message' => 'El evento no cumple el contrato personal v1.', 'codigo' => 'CONTRACT_INVALID');
        }

        $idEvento = strtolower(trim((string)$data['id_evento']));
        $hash = hash('sha256', $rawPayload);

        try {
            $insert = $this->pdo->prepare(
                "INSERT INTO sige_personal_inbox 
                 (id_evento, tipo_evento, persona_uuid, version_estado, ocurrido_en, payload_hash, payload, procesado) 
                 VALUES 
                 (:id_evento, :tipo_evento, :persona_uuid, :version_estado, :ocurrido_en, :hash, :payload, 0)"
            );
            $insert->execute(array(
                ':id_evento'       => $idEvento,
                ':tipo_evento'     => strtoupper(trim($tipoEvento)),
                ':persona_uuid'    => trim((string)$data['persona_uuid']),
                ':version_estado'  => (int)$data['persona_version'],
                ':ocurrido_en'     => trim((string)$data['ocurrido_en']),
                ':hash'            => $hash,
                ':payload'         => $rawPayload
            ));

            return array('status' => 202, 'message' => 'Evento recibido exitosamente.', 'codigo' => 'PROCESADO');

        } catch (PDOException $e) {
            // 23000 is SQLSTATE for Integrity constraint violation (duplicate key)
            if ($e->getCode() == '23000' || $e->getCode() == 1062) {
                $stmt = $this->pdo->prepare("SELECT payload_hash FROM sige_personal_inbox WHERE id_evento = :id_evento");
                $stmt->execute(array(':id_evento' => $idEvento));
                $existing = $stmt->fetch();
                if ($existing) {
                    if ($existing['payload_hash'] === $hash) {
                        return array('status' => 202, 'message' => 'Evento ya recibido.', 'codigo' => 'IDEMPOTENT_OK');
                    } else {
                        return array('status' => 409, 'message' => 'Conflicto: El ID de evento ya existe con un payload diferente.', 'codigo' => 'DUPLICATE_CONFLICT');
                    }
                }
                $version = $this->pdo->prepare("SELECT id_evento, payload_hash FROM sige_personal_inbox WHERE persona_uuid = :uuid AND version_estado = :version LIMIT 1");
                $version->execute(array(':uuid' => $data['persona_uuid'], ':version' => $data['persona_version']));
                $existingVersion = $version->fetch();
                if ($existingVersion) {
                    return array('status' => 409, 'message' => 'Conflicto: la versión de persona ya pertenece a otro evento.', 'codigo' => 'PERSON_VERSION_CONFLICT');
                }
            }
            error_log("Error guardando evento personal SIGE: " . $e->getMessage());
            return array('status' => 500, 'message' => 'Error interno procesando el evento.', 'codigo' => 'INTERNAL_ERROR');
        } catch (Throwable $e) {
            error_log("Error guardando evento personal SIGE: " . $e->getMessage());
            return array('status' => 500, 'message' => 'Error interno procesando el evento.', 'codigo' => 'INTERNAL_ERROR');
        }
    }

    private function validarContrato(array $evento) {
        $claves = array('contract_version', 'id_evento', 'id_operacion', 'tipo', 'entidad', 'persona_uuid', 'persona_version', 'origen', 'ocurrido_en', 'data');
        if (!$this->clavesExactas($evento, $claves)
            || $evento['contract_version'] !== '1.0'
            || !$this->esUuid($evento['id_evento'])
            || !($evento['id_operacion'] === null || $this->esUuid($evento['id_operacion']))
            || !$this->esUuid($evento['persona_uuid'])
            || !is_int($evento['persona_version']) || $evento['persona_version'] < 1
            || $evento['origen'] !== 'SIGE'
            || !$this->esFechaBogota($evento['ocurrido_en'])
            || !is_array($evento['data'])) {
            return false;
        }

        $tipo = $evento['tipo'];
        $entidad = $evento['entidad'];
        $data = $evento['data'];
        if (in_array($tipo, array('PERSONA_CREADA', 'PERSONA_ACTUALIZADA'), true)) {
            return $entidad === 'PERSONA' && $this->validarPersona($data);
        }
        if ($tipo === 'ESTADO_INSTITUCIONAL_CAMBIADO') {
            return $entidad === 'PERSONA' && $this->validarEstadoInstitucional($data);
        }
        if (in_array($tipo, array('VINCULO_CREADO', 'VINCULO_ACTUALIZADO', 'VINCULO_REACTIVADO', 'VINCULO_DESACTIVADO'), true)) {
            return $entidad === 'VINCULO' && $this->validarVinculo($data);
        }
        if ($tipo === 'FOTO_ACTUALIZADA') {
            return $entidad === 'FOTO' && $this->validarFoto($data);
        }
        if (in_array($tipo, array('CARNET_ASIGNADO', 'CARNET_REEMPLAZADO', 'CARNET_BLOQUEADO', 'CARNET_ENTREGADO'), true)) {
            return $entidad === 'CARNET' && $this->validarCarnet($data);
        }
        return false;
    }

    private function validarPersona(array $data) {
        return $this->clavesExactas($data, array('tipo_documento', 'numero_documento', 'nombres', 'apellidos', 'estado', 'vinculos_habilitantes'))
            && $this->cadena($data['tipo_documento'], 1, 20)
            && $this->cadena($data['numero_documento'], 3, 50)
            && $this->cadena($data['nombres'], 1, 150)
            && $this->cadena($data['apellidos'], 1, 150)
            && in_array($data['estado'], array('ACTIVA', 'INACTIVA'), true)
            && is_int($data['vinculos_habilitantes']) && $data['vinculos_habilitantes'] >= 0;
    }

    private function validarVinculo(array $data) {
        if (!$this->clavesExactas($data, array('vinculo_uuid', 'tipo', 'estado', 'cargo', 'dependencia'))
            || !$this->esUuid($data['vinculo_uuid'])
            || !in_array($data['tipo'], array('DOCENTE', 'ADMINISTRATIVO'), true)
            || !in_array($data['estado'], array('ACTIVO', 'INACTIVO'), true)) {
            return false;
        }
        if ($data['tipo'] === 'DOCENTE') {
            return $data['cargo'] === null && $data['dependencia'] === null;
        }
        return is_array($data['cargo']) && is_array($data['dependencia'])
            && $this->validarCatalogo($data['cargo']) && $this->validarCatalogo($data['dependencia']);
    }

    private function validarCatalogo(array $data) {
        return $this->clavesExactas($data, array('id', 'nombre_snapshot', 'version'))
            && is_int($data['id']) && $data['id'] >= 1
            && $this->cadena($data['nombre_snapshot'], 1, 190)
            && is_int($data['version']) && $data['version'] >= 1;
    }

    private function validarFoto(array $data) {
        return $this->clavesExactas($data, array('sha256', 'mime', 'bytes'))
            && is_string($data['sha256']) && preg_match('/^[0-9a-f]{64}$/', $data['sha256']) === 1
            && $data['mime'] === 'image/jpeg'
            && is_int($data['bytes']) && $data['bytes'] >= 1 && $data['bytes'] <= 5242880;
    }

    private function validarCarnet(array $data) {
        return $this->clavesExactas($data, array('sige_carnet_id', 'uid_rfid', 'estado', 'motivo', 'fecha_emision', 'vigencia_hasta', 'entregado_en'))
            && is_int($data['sige_carnet_id']) && $data['sige_carnet_id'] >= 1
            && $this->cadena($data['uid_rfid'], 1, 100)
            && in_array($data['estado'], array('ACTIVO', 'INACTIVO'), true)
            && in_array($data['motivo'], array('NO_APLICA', 'PERDIDA', 'ROBO', 'DETERIORO', 'BAJA_INSTITUCIONAL', 'BLOQUEO_OPERATIVO'), true)
            && $this->esFechaBogota($data['fecha_emision'])
            && $data['vigencia_hasta'] === null
            && ($data['entregado_en'] === null || $this->esFechaBogota($data['entregado_en']));
    }

    private function validarEstadoInstitucional(array $data) {
        if (!$this->clavesExactas($data, array('accion', 'alcance', 'persona_estado', 'vinculos_habilitantes', 'vinculos_afectados', 'carnet', 'solicitud_talento_humano_id'))
            || !in_array($data['accion'], array('BAJA_INSTITUCIONAL', 'REACTIVACION_INSTITUCIONAL'), true)
            || !in_array($data['alcance'], array('VINCULO', 'PERSONA'), true)
            || !in_array($data['persona_estado'], array('ACTIVA', 'INACTIVA'), true)
            || !is_int($data['vinculos_habilitantes']) || $data['vinculos_habilitantes'] < 0
            || !is_array($data['vinculos_afectados']) || !array_is_list($data['vinculos_afectados']) || count($data['vinculos_afectados']) < 1
            || !$this->cadena($data['solicitud_talento_humano_id'], 1, 100)) {
            return false;
        }
        foreach ($data['vinculos_afectados'] as $vinculo) {
            if (!is_array($vinculo) || !$this->validarVinculo($vinculo)) return false;
        }
        return $data['carnet'] === null || (is_array($data['carnet']) && $this->validarCarnet($data['carnet']));
    }

    private function clavesExactas(array $data, array $claves) {
        $actuales = array_keys($data);
        sort($actuales);
        sort($claves);
        return $actuales === $claves;
    }

    private function esUuid($valor) {
        return is_string($valor) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $valor) === 1;
    }

    private function esFechaBogota($valor) {
        if (!is_string($valor)) return false;
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $valor, new DateTimeZone('America/Bogota'));
        return $fecha && $fecha->format('Y-m-d H:i:s') === $valor;
    }

    private function cadena($valor, $minimo, $maximo) {
        if (!is_string($valor)) return false;
        $longitud = function_exists('mb_strlen') ? mb_strlen($valor, 'UTF-8') : strlen($valor);
        return $longitud >= $minimo && $longitud <= $maximo;
    }
}
