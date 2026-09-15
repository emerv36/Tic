<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}
require_once(__DIR__ . '/../Config/sige_config.php');
if (!defined('Q10_API_KEY') || trim((string) Q10_API_KEY) === '') {
    fwrite(STDERR, "Q10_API_KEY no está configurada.\n");
    exit(3);
}
$lockPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tic_q10_academic_poll.lock';
$lock = fopen($lockPath, 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    fwrite(STDERR, "Ya existe un sondeo Q10 en ejecución.\n");
    exit(2);
}
require_once(__DIR__ . '/../Services/Q10AcademicPoller.php');
try {
    $limite = getenv('Q10_POLL_BATCH') ?: 10;
    $resumen = (new Q10AcademicPoller())->ejecutar($limite);
    echo json_encode($resumen, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    flock($lock, LOCK_UN);
    fclose($lock);
    exit($resumen['errores'] > 0 ? 1 : 0);
} catch (Throwable $ex) {
    error_log('Sondeo académico Q10: ' . $ex->getMessage());
    fwrite(STDERR, "Error al ejecutar el sondeo académico Q10.\n");
    flock($lock, LOCK_UN);
    fclose($lock);
    exit(1);
}
