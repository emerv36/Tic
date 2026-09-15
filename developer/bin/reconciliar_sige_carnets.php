<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Solo CLI.\n");
}

date_default_timezone_set('America/Bogota');
require_once(__DIR__ . '/../Services/SigeReconciliationWorker.php');

try {
    $worker = new SigeReconciliationWorker();
    $resultado = $worker->ejecutar();
    fwrite(STDOUT, json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    exit($resultado['status'] === 'COMPLETADA' ? 0 : 2);
} catch (Throwable $ex) {
    $salida = array(
        'status' => 'ERROR',
        'tipo' => get_class($ex),
        'mensaje' => $ex->getMessage(),
        'ocurrido_en' => date('Y-m-d H:i:s')
    );
    fwrite(STDERR, json_encode($salida, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    exit(1);
}

// Programar externamente cada 30 minutos, por ejemplo con el Programador de tareas:
// php C:\xampp\htdocs\tic.scv.edu.co\developer\bin\reconciliar_sige_carnets.php
