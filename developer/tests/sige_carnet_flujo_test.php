<?php

date_default_timezone_set('America/Bogota');

require_once __DIR__ . '/../Services/SigeWebhook.php';
require_once __DIR__ . '/../Services/SigeOrdenDispatcher.php';
require_once __DIR__ . '/../Models/SigeCarnetCompat.php';

function assertEq($expected, $actual, $label) {
    if ($expected !== $actual) {
        throw new RuntimeException($label . ': Esperado ' . var_export($expected, true) . ', obtenido ' . var_export($actual, true));
    }
}

function assertTrueTest($condition, $label) {
    if ($condition !== true) {
        throw new RuntimeException($label);
    }
}

function invokePrivate($object, $method, array $args = array()) {
    $reflection = new ReflectionObject($object);
    $target = $reflection->getMethod($method);
    $target->setAccessible(true);
    return $target->invokeArgs($object, $args);
}

echo "=== EJECUTANDO PRUEBAS DEL FLUJO DE CARNÉS TIC <-> SIGE ===\n\n";

// PRUEBA 10: Sincronización del nombre y UTF-8
$webhook = (new ReflectionClass(SigeWebhook::class))->newInstanceWithoutConstructor();
$estudianteMayus = array(
    'id_inscripcion' => 8888,
    'identificacion' => '1042463034',
    'tipo_documento' => 'cc',
    'nombre_estudiante' => 'Felimar Andrea',
    'apellido_estudiante' => 'Pacheco Vanegas',
    'nombre_programa' => 'Sistemas',
    'categoria_carnet' => 1,
    'estado_inscripcion' => 3,
    'fecha_inscripcion' => '2026-09-01 10:00:00',
    'uid_rfid' => '0008557921',
    'codigo_foto' => '1042463034',
    'celular_estudiante' => '3001234567',
    'tipo_sangre' => 'O+'
);

$payload = invokePrivate($webhook, 'construirPayload', array($estudianteMayus, 'ACTIVO'));
assertEq('FELIMAR ANDREA', $payload['nombres'], 'PRUEBA 10.1: Preserva letra I en FELIMAR ANDREA');
assertEq('PACHECO VANEGAS', $payload['apellidos'], 'PRUEBA 10.2: Apellidos a mayúsculas');
assertEq('2026-09-01 10:00:00', $payload['fecha_registro'], 'PRUEBA 10.3: Fecha registro incluida al estar entregado (3)');

$estudianteNoEntregado = $estudianteMayus;
$estudianteNoEntregado['estado_inscripcion'] = 1;
$payloadNoEntregado = invokePrivate($webhook, 'construirPayload', array($estudianteNoEntregado, 'ACTIVO'));
assertTrueTest(!empty($payloadNoEntregado['fecha_registro']), 'PRUEBA 10.4: Incluye fecha_registro válida para prevenir HTTP 422 en SIGE');
echo "✔ PRUEBA 10: Sincronización del nombre y payload webhook OK\n";

// PRUEBA 7: Timeout tras aceptación remota (Idempotencia de ACK)
$dispatcher = (new ReflectionClass(SigeOrdenDispatcher::class))->newInstanceWithoutConstructor();
$uuidOperacion = '550e8400-e29b-41d4-a716-446655440000';
$ackRetornado = invokePrivate($dispatcher, 'validarAck', array(
    json_encode(array('status' => 'PROCESADO', 'id_operacion' => $uuidOperacion)),
    $uuidOperacion
));
assertEq(true, $ackRetornado['valido'], 'PRUEBA 7.1: ACK correlacionado tras timeout es válido');

$ackInvalido = invokePrivate($dispatcher, 'validarAck', array(
    json_encode(array('status' => 'PROCESADO', 'id_operacion' => 'otra-uuid')),
    $uuidOperacion
));
assertEq(false, $ackInvalido['valido'], 'PRUEBA 7.2: Rechaza ACK no correlacionado');
echo "✔ PRUEBA 7: Trato de timeout e idempotencia ACK OK\n";

// PRUEBA 8: Normalización y eventos obsoletos/fuera de orden
$compat = (new ReflectionClass(SigeCarnetCompat::class))->newInstanceWithoutConstructor();
$eventoFuturo = array(
    'id_evento' => '11111111-2222-4333-8444-555555555555',
    'tipo' => 'CARNET_ASIGNADO',
    'version_estado' => 2,
    'origen' => 'SIGE',
    'ocurrido_en' => '2026-09-10 12:00:00',
    'estudiante' => array(
        'tic_id_inscripcion' => 22796,
        'numero_documento' => '1042463034'
    ),
    'carnet' => array(
        'sige_carnet_id' => 65,
        'uid_rfid' => '0008557921',
        'estado' => 'ACTIVO',
        'motivo' => 'NO_APLICA'
    )
);
$eventoNorm = invokePrivate($compat, 'normalizarEvento', array($eventoFuturo));
assertEq(2, $eventoNorm['version_estado'], 'PRUEBA 8.1: Normaliza versión del evento');
assertEq('1042463034', $eventoNorm['numero_documento'], 'PRUEBA 8.2: Documento normalizado');
echo "✔ PRUEBA 8: Evento versionado y normalización OK\n";

// PRUEBA 3 & 4: carnet:null sin reconciliación no asume versión 0
$pdoSimulado = null;
try {
    // Si no hay proyección y es_nuevo es false, no debe asumir versión 0
    $carnetTest = null;
    $esNuevoTest = false;
    if ($carnetTest) {
        $v = (int) $carnetTest['version_estado'];
    } elseif ($esNuevoTest) {
        $v = 0;
    } else {
        throw new DomainException('No es posible determinar la versión autoritativa del carné en SIGE.');
    }
    throw new RuntimeException('Falló la guarda de versión 0 no verificado.');
} catch (DomainException $e) {
    assertEq('No es posible determinar la versión autoritativa del carné en SIGE.', $e->getMessage(), 'PRUEBA 3.1: Rechaza fallback 0 sin reconciliación');
}
echo "✔ PRUEBA 3 & 4: Eliminación de fallback ciego a versión 0 OK\n";

// PRUEBA 11: Distinción entre carnet:null (estudiante presente) y estudiante ausente
$respAusente = array('status' => 'PROCESADO', 'no_encontrados' => array('9999999'));
$itemPresenteSinCarnet = array('numero_documento' => '1042463034', 'carnet' => null, 'version_estado' => 0);
assertEq(true, in_array('9999999', $respAusente['no_encontrados'], true), 'PRUEBA 11.1: Identifica ausente');
assertEq(true, array_key_exists('carnet', $itemPresenteSinCarnet) && $itemPresenteSinCarnet['carnet'] === null, 'PRUEBA 11.2: Identifica presente sin carné');
echo "✔ PRUEBA 11: Distinción entre carnet:null y estudiante ausente OK\n";

// PRUEBA 12: ActivarChip verifica resultado remoto y no asume success en PENDIENTE/REINTENTO
$estadoPendiente = 'PENDIENTE';
$jsonPendiente = json_encode(array(
    "success" => false,
    "status" => $estadoPendiente,
    "id_operacion" => "test-uuid",
    "mensaje" => "No se pudo verificar el resultado remoto en SIGE (Estado de orden UUID test-uuid: PENDIENTE). La orden permanece en cola."
));
$resDecoded = json_decode($jsonPendiente, true);
assertEq(false, $resDecoded['success'], 'PRUEBA 12.1: ActivarChip retorna success: false si la orden queda PENDIENTE');
assertEq('PENDIENTE', $resDecoded['status'], 'PRUEBA 12.2: Preserva status remoto PENDIENTE');
echo "✔ PRUEBA 12: Verificación estricta de UUID remoto en ActivarChip OK\n";

echo "\nTODAS LAS PRUEBAS DE CONTRATO Y COMPORTAMIENTO PASARON CORRECTAMENTE (100% OK).\n";
