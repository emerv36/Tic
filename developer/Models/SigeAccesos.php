<?php
/**
 * SigeAccesos - Modelo para gestionar los logs de acceso físico de SIGE
 * 
 * Maneja la inserción y consulta de registros de entrada/salida
 * recibidos desde el sistema de torniquetes (SIGE).
 */
require_once(__DIR__ . '/../Config/PDOconn.php');

class SigeAccesos extends db {

    /**
     * Inserta un registro individual de acceso físico.
     * 
     * @param int    $sige_id          ID del registro en SIGE
     * @param int    $carnet_id        ID del carnet en SIGE
     * @param string $uid_leido        UID RFID leído por el torniquete
     * @param string $fecha_hora       Fecha y hora del acceso (YYYY-MM-DD HH:MM:SS)
     * @param string $tipo_movimiento  ENTRADA o SALIDA
     * @param string $resultado        PERMITIDO o DENEGADO
     * @param string $motivo_rechazo   Motivo del rechazo o NO_APLICA
     * @return bool  true si la inserción fue exitosa
     */
    public function insertarAcceso(array $acceso) {
        $sql = "INSERT INTO sige_log_accesos 
                (sige_id, carnet_id, uid_leido, fecha_hora, tipo_movimiento, resultado, motivo_rechazo,
                 origen, terminal_id, operacion_manual_id, estudiante_id, acceso_relacionado_id,
                 usuario_autorizador_id, motivo_manual_codigo, fecha_registro)
                VALUES 
                (:sige_id, :carnet_id, :uid_leido, :fecha_hora, :tipo_movimiento, :resultado, :motivo_rechazo,
                 :origen, :terminal_id, :operacion_manual_id, :estudiante_id, :acceso_relacionado_id,
                 :usuario_autorizador_id, :motivo_manual_codigo, :fecha_registro)";
        $params = array(
            ':sige_id' => $acceso['sige_id'],
            ':carnet_id' => $acceso['carnet_id'],
            ':uid_leido' => $acceso['uid_leido'],
            ':fecha_hora' => $acceso['fecha_hora'],
            ':tipo_movimiento' => $acceso['tipo_movimiento'],
            ':resultado' => $acceso['resultado'],
            ':motivo_rechazo' => $acceso['motivo_rechazo'],
            ':origen' => $acceso['origen'],
            ':terminal_id' => $acceso['terminal_id'],
            ':operacion_manual_id' => $acceso['operacion_manual_id'],
            ':estudiante_id' => $acceso['estudiante_id'],
            ':acceso_relacionado_id' => $acceso['acceso_relacionado_id'],
            ':usuario_autorizador_id' => $acceso['usuario_autorizador_id'],
            ':motivo_manual_codigo' => $acceso['motivo_manual_codigo'],
            ':fecha_registro' => $this->datetimeNow()
        );
        return $this->query($sql, $params);
    }

    /**
     * Verifica si un registro de SIGE ya fue procesado (evitar duplicados).
     * 
     * @param int $sige_id ID del registro en SIGE
     * @return bool true si ya existe
     */
    public function existeRegistro($sige_id) {
        $sql = "SELECT id FROM sige_log_accesos WHERE sige_id = :sige_id LIMIT 1";
        $params = array(':sige_id' => $sige_id);
        $resultado = $this->row($sql, $params);
        return !empty($resultado);
    }

    /**
     * Consulta accesos por rango de fechas.
     * 
     * @param string $fecha_inicio Fecha inicio (YYYY-MM-DD)
     * @param string $fecha_fin    Fecha fin (YYYY-MM-DD)
     * @return array Lista de registros de acceso
     */
    public function obtenerAccesosPorFecha($fecha_inicio, $fecha_fin) {
        $sql = "SELECT la.*, i.nombre_estudiante, i.apellido_estudiante, i.identificacion
                FROM sige_log_accesos la
                LEFT JOIN inscripcion i ON la.uid_leido = i.uid_rfid
                WHERE DATE(la.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                ORDER BY la.fecha_hora DESC";
        $params = array(
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin'    => $fecha_fin
        );
        return $this->table($sql, $params);
    }

    /**
     * Consulta los últimos N accesos registrados.
     * 
     * @param int $limite Cantidad de registros a retornar
     * @return array
     */
    public function obtenerUltimosAccesos($limite = 50) {
        $sql = "SELECT la.*, i.nombre_estudiante, i.apellido_estudiante, i.identificacion
                FROM sige_log_accesos la
                LEFT JOIN inscripcion i ON la.uid_leido = i.uid_rfid
                ORDER BY la.fecha_hora DESC
                LIMIT " . intval($limite);
        return $this->table($sql);
    }
}

?>
