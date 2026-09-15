<?php
/**
 * sige_migration.php - Script de migración para tablas de integración SIGE
 * 
 * Ejecutar una sola vez para crear las tablas necesarias:
 *   - sige_log_accesos: Logs de acceso físico recibidos de SIGE
 *   - sige_webhook_log: Logs de webhooks enviados a SIGE
 * 
 * Uso: Acceder vía navegador o ejecutar desde CLI:
 *   php developer/sige_migration.php
 */
require_once(__DIR__ . '/Config/config.php');

echo "<pre>";
echo "=== MIGRACIÓN SIGE - Creación de tablas ===\n\n";

try {
    $conn = new PDO(connstring, user, pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ============================================================
    // Tabla 1: sige_log_accesos
    // Almacena los registros de entrada/salida recibidos desde SIGE
    // ============================================================
    $sql_accesos = "CREATE TABLE IF NOT EXISTS sige_log_accesos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sige_id INT NOT NULL COMMENT 'ID del registro en SIGE',
        carnet_id INT NOT NULL COMMENT 'ID del carnet en SIGE',
        uid_leido VARCHAR(100) NOT NULL COMMENT 'UID RFID leído por el torniquete',
        fecha_hora DATETIME NOT NULL COMMENT 'Fecha y hora del acceso físico',
        tipo_movimiento ENUM('ENTRADA','SALIDA') NOT NULL COMMENT 'Tipo de movimiento',
        resultado ENUM('PERMITIDO','DENEGADO') NOT NULL COMMENT 'Resultado del acceso',
        motivo_rechazo VARCHAR(100) DEFAULT 'NO_APLICA' COMMENT 'Motivo del rechazo si aplica',
        fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro en TIC',
        INDEX idx_sige_id (sige_id),
        INDEX idx_uid (uid_leido),
        INDEX idx_fecha (fecha_hora),
        INDEX idx_carnet (carnet_id),
        UNIQUE KEY uk_sige_id (sige_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Log de accesos físicos recibidos desde SIGE (torniquetes)'";

    $conn->exec($sql_accesos);
    echo "✅ Tabla 'sige_log_accesos' creada exitosamente.\n";

    // ============================================================
    // Tabla 2: sige_webhook_log
    // Almacena el historial de webhooks enviados desde TIC hacia SIGE
    // ============================================================
    $sql_webhook = "CREATE TABLE IF NOT EXISTS sige_webhook_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_inscripcion INT NOT NULL COMMENT 'ID de inscripción en TIC',
        identificacion VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'Número de documento del estudiante',
        evento VARCHAR(50) NOT NULL COMMENT 'Tipo de evento: CREAR, ACTUALIZAR, ENTREGA, ESTADO, CHIP',
        payload_enviado TEXT COMMENT 'JSON enviado a SIGE',
        respuesta_sige TEXT COMMENT 'Respuesta recibida de SIGE',
        http_code INT DEFAULT 0 COMMENT 'Código HTTP de respuesta',
        exitoso TINYINT(1) DEFAULT 0 COMMENT '1=exitoso, 0=fallido',
        fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del envío',
        INDEX idx_inscripcion (id_inscripcion),
        INDEX idx_identificacion (identificacion),
        INDEX idx_fecha (fecha_envio),
        INDEX idx_exitoso (exitoso)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Log de webhooks enviados desde TIC hacia SIGE'";

    $conn->exec($sql_webhook);
    echo "✅ Tabla 'sige_webhook_log' creada exitosamente.\n";

    echo "\n=== MIGRACIÓN COMPLETADA EXITOSAMENTE ===\n";
    echo "\nTablas creadas:\n";
    echo "  1. sige_log_accesos   - Para recibir accesos de SIGE\n";
    echo "  2. sige_webhook_log   - Para auditoría de webhooks enviados\n";

} catch (PDOException $ex) {
    echo "❌ ERROR en la migración: " . $ex->getMessage() . "\n";
}

echo "</pre>";
?>
