<?php
require dirname(__DIR__) . '/Services/SigePersonalClient.php';

$operation = '123e4567-e89b-42d3-a456-426614174210';
$person = '123e4567-e89b-42d3-a456-426614174211';
$valid = json_encode(array(
    'contract_version' => '1.0', 'id_operacion' => $operation, 'status' => 'PROCESADO',
    'codigo' => 'FOTO_ACTUALIZADA', 'mensaje' => 'OK', 'persona_uuid' => $person, 'persona_version' => 4
));
$ack = SigePersonalClient::validarAckProcesado($valid, 200, $operation, $person);
if ($ack['persona_version'] !== 4) throw new RuntimeException('No aceptó el ACK válido.');
$ackBom = SigePersonalClient::validarAckProcesado("\xEF\xBB\xBF" . $valid, 200, $operation, $person);
if ($ackBom['persona_version'] !== 4) throw new RuntimeException('No normalizó el BOM real de SIGE.');

$invalid = array(
    '', '{', json_encode(array()),
    str_replace($operation, '123e4567-e89b-42d3-a456-426614174299', $valid),
    str_replace('PROCESADO', 'ACEPTADO', $valid),
    str_replace($person, '123e4567-e89b-42d3-a456-426614174298', $valid),
    str_replace('"persona_version":4', '"persona_version":null', $valid),
    str_replace('"persona_version":4', '"persona_version":"4"', $valid)
);
foreach ($invalid as $response) {
    try {
        SigePersonalClient::validarAckProcesado($response, 200, $operation, $person);
        throw new RuntimeException('Aceptó un ACK inválido: ' . $response);
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Aceptó un ACK') === 0) throw $e;
    }
}
try {
    SigePersonalClient::validarAckProcesado($valid, 500, $operation, $person);
    throw new RuntimeException('Aceptó HTTP 500.');
} catch (Exception $e) {
    if ($e->getMessage() === 'Aceptó HTTP 500.') throw $e;
}

echo "sige_personal_client_ack_test: OK\n";
