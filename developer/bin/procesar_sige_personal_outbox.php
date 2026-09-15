<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

date_default_timezone_set('America/Bogota');
require_once __DIR__ . '/../Services/PersonalOutboxDispatcher.php';

$lockPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tic_sige_personal_outbox.lock';
$lock = fopen($lockPath, 'c');
if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
    fwrite(STDOUT, "Ya existe un worker de personal en ejecución.\n");
    exit(0);
}

try {
    $procesadas = (new PersonalOutboxDispatcher())->procesarPendientes(25);
    fwrite(STDOUT, "Órdenes de personal procesadas: " . $procesadas . "\n");
    exit(0);
} catch (Throwable $ex) {
    error_log('Worker outbox personal TIC-SIGE: ' . $ex->getMessage());
    fwrite(STDERR, "Error al procesar la outbox de personal.\n");
    exit(1);
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
