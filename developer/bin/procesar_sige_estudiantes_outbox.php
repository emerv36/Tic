<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$lockPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tic_sige_estudiantes_outbox.lock';
$lock = fopen($lockPath, 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    fwrite(STDERR, "Ya existe un worker de estudiantes TIC-SIGE en ejecución.\n");
    exit(2);
}

require_once(__DIR__ . '/../Services/SigeStudentOutbox.php');
try {
    $resumen = (new SigeStudentOutbox())->procesarPendientes(25);
    echo json_encode($resumen, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    $fallo = $resumen['reintentos'] > 0 || $resumen['fallidas'] > 0;
    flock($lock, LOCK_UN);
    fclose($lock);
    exit($fallo ? 1 : 0);
} catch (Throwable $ex) {
    error_log('Worker outbox estudiantes SIGE: ' . $ex->getMessage());
    fwrite(STDERR, "Error al procesar la outbox de estudiantes SIGE.\n");
    flock($lock, LOCK_UN);
    fclose($lock);
    exit(1);
}
