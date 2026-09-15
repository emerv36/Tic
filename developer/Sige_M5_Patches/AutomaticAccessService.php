<?php

require_once APP_PATH . '/Services/AccessPersonResolver.php';

class AutomaticAccessService {
    private $db;

    public function __construct(PDO $db) { $this->db = $db; }

    public function validar($uid, $terminalId) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT c.id AS carnet_id,c.estado AS carnet_estado,c.vigencia_hasta,c.fecha_emision,c.creado_en,
                        c.persona_id, e.id AS estudiante_id, COALESCE(e.nombres, p.nombres) AS nombres, COALESCE(e.apellidos, p.apellidos) AS apellidos, COALESCE(e.numero_documento, p.numero_documento_normalizado) AS numero_documento, e.estado_academico,
                        e.requiere_reactivacion, e.bloqueo_financiero, COALESCE(e.foto_url, pf.ruta_relativa) AS foto_url,
                        p.estado AS persona_estado,
                        (SELECT COUNT(*) FROM persona_vinculos pv WHERE pv.persona_id=c.persona_id AND pv.estado='ACTIVO' AND pv.tipo_vinculo IN('DOCENTE','ADMINISTRATIVO')) AS vinculos_personal_activos
                 FROM carnets c LEFT JOIN estudiantes e ON c.estudiante_id=e.id LEFT JOIN personas p ON c.persona_id=p.id LEFT JOIN persona_fotos pf ON p.id=pf.persona_id
                 WHERE c.uid_rfid=:uid LIMIT 1 FOR UPDATE"
            );
            $stmt->execute(['uid' => $uid]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);
            $estudianteInfo = $registro ? [
                'nombres' => $registro['nombres'], 'apellidos' => $registro['apellidos'],
                'documento' => $registro['numero_documento'], 'foto_url' => $registro['foto_url'],
            ] : null;
            $resultado = 'DENEGADO';
            $motivo = 'NO_APLICA';
            $instruccion = '';
            $personaId = null;

            if (!$registro) {
                $motivo = 'NO_REGISTRADO';
                $instruccion = 'CARNET NO ENCONTRADO — Por favor diríjase a la oficina de Carnetización para activar su carnet.';
            } else {
                try {
                    $personaId = (new AccessPersonResolver($this->db))->resolveCurrent(
                        $registro['estudiante_id'] !== null ? (int) $registro['estudiante_id'] : null,
                        (int) $registro['carnet_id']
                    );
                } catch (DomainException $e) {
                    $motivo = 'CONFLICTO_IDENTIDAD';
                    $instruccion = 'IDENTIDAD EN VERIFICACIÓN — Diríjase a la oficina de Carnetización para resolver su situación.';
                }
                $ahora = date('Y-m-d H:i:s');
                if ($motivo === 'CONFLICTO_IDENTIDAD') {
                    // La lectura queda auditada sin atribución y sin corregir datos maestros.
                } elseif ($registro['carnet_estado'] === 'INACTIVO') {
                    $motivo = 'CARNET_INACTIVO';
                    $instruccion = 'CARNET INACTIVO — Diríjase a la oficina de Carnetización para reactivarlo.';
                } elseif ($registro['fecha_emision'] > $ahora) {
                    $motivo = 'CARNET_NO_VIGENTE';
                    $instruccion = 'CARNET AÚN NO VIGENTE — Diríjase a la oficina de Carnetización.';
                } elseif ($registro['vigencia_hasta'] !== null && $registro['vigencia_hasta'] < $ahora) {
                    $motivo = 'CARNET_VENCIDO';
                    $instruccion = 'CARNET VENCIDO — Diríjase a la oficina de Carnetización para renovarlo.';
                } elseif ($registro['estudiante_id'] === null && $registro['persona_estado'] !== 'ACTIVA') {
                    $motivo = 'PERSONA_INACTIVA';
                    $instruccion = 'VÍNCULO INACTIVO — Diríjase a la oficina de Talento Humano o Administración.';
                } elseif ($registro['estudiante_id'] === null && (int) $registro['vinculos_personal_activos'] < 1) {
                    $motivo = 'VINCULO_INACTIVO';
                    $instruccion = 'SIN VÍNCULO ACTIVO — Diríjase a la oficina de Talento Humano o Administración.';
                } elseif ($registro['estudiante_id'] !== null && (string) $registro['estado_academico'] !== '1') {
                    if ($registro['estado_academico'] === null) {
                        $motivo = 'ESTADO_ACADEMICO_NO_CONFIRMADO';
                        $instruccion = 'ESTADO ACADÉMICO PENDIENTE — Su matrícula está en proceso de confirmación. Diríjase a Secretaría Académica.';
                    } else {
                        $motivo = 'ESTUDIANTE_INACTIVO';
                        $instruccion = 'MATRÍCULA NO VIGENTE — Diríjase a la oficina de Registro Académico.';
                    }
                } elseif ($registro['estudiante_id'] !== null && (int) $registro['requiere_reactivacion'] === 1) {
                    $motivo = 'REACTIVACION_REQUERIDA';
                    $instruccion = 'CARNET REQUIERE ACTUALIZACIÓN — Diríjase a la oficina de Carnetización.';
                } elseif ($registro['estudiante_id'] !== null && $registro['bloqueo_financiero'] == 1) {
                    $motivo = 'BLOQUEO_FINANCIERO';
                    $instruccion = 'SITUACIÓN FINANCIERA PENDIENTE — Diríjase a la oficina de Tesorería.';
                } else {
                    $resultado = 'PERMITIDO';
                }
            }

            $audit = $this->db->prepare(
                "INSERT INTO accesos
                    (carnet_id,uid_leido,tipo_movimiento,resultado,motivo_rechazo,origen,terminal_id,estudiante_id,persona_id)
                 VALUES (:carnet,:uid,'ENTRADA',:resultado,:motivo,'AUTOMATICO',:terminal,:estudiante,:persona)"
            );
            $audit->execute([
                'carnet' => $registro ? $registro['carnet_id'] : null, 'uid' => $uid,
                'resultado' => $resultado, 'motivo' => $motivo, 'terminal' => $terminalId,
                'estudiante' => $registro ? $registro['estudiante_id'] : null, 'persona' => $personaId,
            ]);
            $accesoId = (int) $this->db->lastInsertId();
            $this->db->commit();
            return ['status' => $resultado, 'instruccion' => $instruccion, 'estudiante' => $estudianteInfo,
                'acceso_id' => $accesoId, 'motivo_rechazo' => $motivo];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}
