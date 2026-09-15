<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

/**
 * Worker para procesar asincronamente los eventos de personal de SIGE en TIC.
 * Se debe configurar en cron para ejecutarse cada 1-5 minutos.
 *
 * Comando cron sugerido:
 * * * * * * php /ruta/a/tic.scv.edu.co/sige_personal_inbox_worker.php
 */
require_once(__DIR__ . '/../Services/PersonalInboxProcessor.php');

// Evitar múltiples ejecuciones simultáneas mediante bloqueo de archivo
$lockFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tic_sige_personal_inbox.lock';
$fp = fopen($lockFile, 'c');
if ($fp === false) {
    fwrite(STDERR, "No fue posible crear el lock del worker.\n");
    exit(1);
}
if (!flock($fp, LOCK_EX | LOCK_NB)) {
    echo "El worker ya se encuentra en ejecución.\n";
    fclose($fp);
    exit(0);
}

try {
    $procesador = new PersonalInboxProcessor();
    $resumen = $procesador->procesarPendientes(100);
    echo 'Resumen inbox: ' . json_encode($resumen, JSON_UNESCAPED_UNICODE) . "\n";
    if ($resumen['pendientes'] > 0 || $resumen['fallidos'] > 0) exit(2);
} catch (Exception $e) {
    fwrite(STDERR, "Error en el worker: " . $e->getMessage() . "\n");
    error_log("PersonalInboxWorker Fatal Error: " . $e->getMessage());
    exit(1);
} finally {
    flock($fp, LOCK_UN);
    fclose($fp);
}
