<?php
$source = file_get_contents(dirname(__DIR__) . '/Controller/personalController.php');
if ($source === false) throw new RuntimeException('No se pudo leer personalController.php.');

foreach (array('PersonalCatalogService', 'consultarPersona(', "listar('CARGO', false)", "listar('DEPENDENCIA', false)", "obtenerSnapshotActivo('CARGO'", "obtenerSnapshotActivo('DEPENDENCIA'", "'cargo' => \$cargoSnapshot", "'dependencia' => \$dependenciaSnapshot", "'PERSONA_CREAR'", "'VINCULO_ACTUALIZAR'", "'PERSONAL_SYNC'", "'persona_uuid' =>", "'expected_persona_version' =>", "'foto_base64' => \$fotoBase64", "'usuario' => array") as $required) {
    if (strpos($source, $required) === false) throw new RuntimeException('Falta integración de catálogo autoritativo: ' . $required);
}
foreach (array("'Rector'", "'Coordinador'", "'Auxiliar Administrativo'", "'Secretaría General'", "'Área de TI'") as $forbidden) {
    if (strpos($source, $forbidden) !== false) throw new RuntimeException('Permanece catálogo codificado en el controlador: ' . $forbidden);
}
foreach (array("'3000000000'", "? 'O+'", "'mocked' => true", "'op-' . bin2hex") as $forbidden) {
    if (strpos($source, $forbidden) !== false) throw new RuntimeException('Permanece comportamiento simulado/fabricado: ' . $forbidden);
}

$client = file_get_contents(dirname(__DIR__) . '/Services/SigePersonalClient.php');
foreach (array("'tipo' => 'FOTO_ASOCIAR'", "'expected_persona_version'", "'campo_multipart' => 'foto'", "'usuario' =>") as $required) {
    if (strpos($client, $required) === false) throw new RuntimeException('Cliente de foto fuera de contrato: ' . $required);
}
$config = file_get_contents(dirname(__DIR__) . '/Config/sige_config.php');
if (strpos($config, 'DEV_API_KEY') !== false) throw new RuntimeException('Permanece una credencial simulada de desarrollo.');

$frontend = file_get_contents(dirname(__DIR__, 2) . '/javascripts/gestionarpersonal.js');
foreach (array("case=finalizar", 'foto_base64: rawBase64') as $required) {
    if (strpos($frontend, $required) === false) throw new RuntimeException('El frontend no entrega el comando compacto: ' . $required);
}
foreach (array('case=transferirFoto', 'fotoAck.persona_version', 'vinculoExistente') as $forbidden) {
    if (strpos($frontend, $forbidden) !== false) throw new RuntimeException('El frontend conserva una orquestación parcial obsoleta: ' . $forbidden);
}

echo "personal_controller_catalog_contract_test: OK\n";
