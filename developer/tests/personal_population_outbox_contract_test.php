<?php
$path = dirname(__DIR__) . '/Controller/personalController.php';
$source = file_get_contents($path);

if ($source === false) throw new RuntimeException('No se pudo leer personalController.php');
if (strpos($source, "'PENDIENTE'") === false) {
    throw new RuntimeException('La población de personal no se encola como PENDIENTE.');
}
if (preg_match('/(?:INSERT\s+INTO|UPDATE)\s+sige_personal_proyeccion/i', $source) === 1) {
    throw new RuntimeException('El controlador muta directamente la proyección técnica.');
}
if (preg_match('/file_put_contents\s*\(/i', $source) === 1) {
    throw new RuntimeException('El controlador conserva una copia local no autoritativa de la foto.');
}

echo "personal_population_outbox_contract_test: OK\n";
