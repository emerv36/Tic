<?php
$path = dirname(__DIR__) . '/bin/sige_personal_inbox_worker.php';
$source = file_get_contents($path);

if ($source === false) throw new RuntimeException('No se pudo leer el worker de inbox.');
if (strpos($source, "PHP_SAPI !== 'cli'") === false) {
    throw new RuntimeException('El worker de inbox puede ejecutarse fuera de CLI.');
}
if (strpos($source, 'sys_get_temp_dir()') === false) {
    throw new RuntimeException('El lock del worker no usa un directorio temporal no público.');
}
if (strpos($source, "['pendientes']") === false || strpos($source, "['fallidos']") === false || strpos($source, 'exit(2)') === false) {
    throw new RuntimeException('El worker no reporta estados pendientes/fallidos mediante su código de salida.');
}

echo "personal_inbox_worker_contract_test: OK\n";
