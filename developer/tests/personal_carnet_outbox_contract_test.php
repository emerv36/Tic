<?php
$path = dirname(__DIR__) . '/Controller/personalCarnetController.php';
$source = file_get_contents($path);

if ($source === false) {
    throw new RuntimeException('No se pudo leer personalCarnetController.php');
}
if (strpos($source, "'PENDIENTE'") === false) {
    throw new RuntimeException('El comando de carné no queda pendiente en la outbox.');
}
if (preg_match('/UPDATE\s+sige_personal_proyeccion/i', $source) === 1) {
    throw new RuntimeException('El controlador muta directamente la proyección autoritativa de SIGE.');
}
if (strpos($source, "':cmd' => 'CARNET'") === false) {
    throw new RuntimeException('El comando no conserva el ruteo CARNET.');
}

echo "personal_carnet_outbox_contract_test: OK\n";
