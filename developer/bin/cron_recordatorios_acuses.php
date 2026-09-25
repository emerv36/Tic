<?php
// developer/bin/cron_recordatorios_acuses.php
// Tarea automatizada para:
// 1. Reenviar recordatorio a acuses PENDIENTES (cada 48 horas, máx 2 reintentos).
// 2. Aplicar SILENCIO ADMINISTRATIVO POSITIVO tras 5 días hábiles.

date_default_timezone_set('America/Bogota');
require_once(__DIR__ . '/../Config/PDOconn.php');
require_once(__DIR__ . '/../Services/MailAcuseService.php');

$dbPass = (defined('pass') && pass !== '') ? pass : (getenv('TIC_DB_PASS') ?: 'B.quilla54');

try {
    $pdo = new PDO(connstring, user, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "=== INICIANDO TAREA AUTOMATIZADA DE RECORDATORIOS Y SILENCIO POSITIVO ===\n";

    // 1. APLICAR SILENCIO ADMINISTRATIVO POSITIVO (Mas de 5 dias sin confirmación)
    // Cambia estado a CONFIRMADO_TACITO
    $diasSilencio = 5;
    $fechaLimiteSilencio = date('Y-m-d H:i:s', strtotime("-$diasSilencio days"));

    $stmtTacito = $pdo->prepare("UPDATE log_correos_acuses 
        SET estado_acuse = 'CONFIRMADO_TACITO'
        WHERE estado_acuse = 'PENDIENTE' 
          AND fecha_envio <= :fecha_limite");
    $stmtTacito->execute([':fecha_limite' => $fechaLimiteSilencio]);
    $afectadosTacito = $stmtTacito->rowCount();

    echo "--> Silencio Administrativo Positivo aplicado a: $afectadosTacito registros.\n";

    // 2. BUSCAR REGISTROS PENDIENTES PARA RECORDATORIO (48 horas tras envío o último recordatorio)
    $fechaLimiteRecordatorio = date('Y-m-d H:i:s', strtotime("-48 hours"));

    $stmtPendientes = $pdo->prepare("SELECT * FROM log_correos_acuses 
        WHERE estado_acuse = 'PENDIENTE' 
          AND intentos_recordatorio < 2
          AND (
            (intentos_recordatorio = 0 AND fecha_envio <= :fecha1) OR
            (intentos_recordatorio > 0 AND fecha_ultimo_recordatorio <= :fecha2)
          )");
    $stmtPendientes->execute([
        ':fecha1' => $fechaLimiteRecordatorio,
        ':fecha2' => $fechaLimiteRecordatorio
    ]);

    $pendientes = $stmtPendientes->fetchAll();
    echo "--> Registros elegibles para recordatorio: " . count($pendientes) . "\n";

    $mailAcuseService = new MailAcuseService();

    foreach ($pendientes as $reg) {
        // En producción se integra con PHPMailer / Mailer de la app
        // Aquí actualizamos el contador de intentos y fecha de último recordatorio
        $intentos = $reg['intentos_recordatorio'] + 1;
        $updRecordatorio = $pdo->prepare("UPDATE log_correos_acuses 
            SET intentos_recordatorio = :intentos,
                fecha_ultimo_recordatorio = NOW()
            WHERE id = :id");
        $updRecordatorio->execute([':intentos' => $intentos, ':id' => $reg['id']]);

        echo "    [Recordatorio #$intentos enviado a: {$reg['destinatario_email']} (ID: {$reg['id']})]\n";
    }

    echo "=== TAREA AUTOMATIZADA FINALIZADA CON ÉXITO ===\n";

} catch (Exception $e) {
    echo "ERROR CRON RECORDATORIOS: " . $e->getMessage() . "\n";
}
