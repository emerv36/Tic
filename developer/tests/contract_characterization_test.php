<?php

date_default_timezone_set('America/Bogota');

require_once dirname(__DIR__) . '/Services/SigeWebhook.php';
require_once dirname(__DIR__) . '/Services/SigeOrdenDispatcher.php';
require_once dirname(__DIR__) . '/Services/SigeStudentOutbox.php';
require_once dirname(__DIR__) . '/Services/SigeReconciliationWorker.php';
require_once dirname(__DIR__) . '/Services/Q10Gateway.php';
require_once dirname(__DIR__) . '/Models/SigeCarnetCompat.php';

function assertSameContract($expected, $actual, $label) {
    if ($expected !== $actual) {
        throw new RuntimeException(
            $label . ': esperado ' . var_export($expected, true)
            . ', recibido ' . var_export($actual, true)
        );
    }
}

function assertTrueContract($condition, $label) {
    if ($condition !== true) {
        throw new RuntimeException($label);
    }
}

function invokePrivateContract($object, $method, array $args = array()) {
    $reflection = new ReflectionObject($object);
    $target = $reflection->getMethod($method);
    $target->setAccessible(true);
    return $target->invokeArgs($object, $args);
}

function loadSchemaContract($name) {
    $path = dirname(__DIR__) . '/Contracts/personal/v1/' . $name;
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException('No fue posible leer ' . $name);
    }
    return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
}

function loadSourceContract($relativePath) {
    $path = dirname(__DIR__, 2) . '/' . $relativePath;
    $source = file_get_contents($path);
    if ($source === false) {
        throw new RuntimeException('No fue posible leer ' . $relativePath);
    }
    return $source;
}

function assertSourceContainsContract($relativePath, array $needles) {
    $source = loadSourceContract($relativePath);
    foreach ($needles as $needle) {
        assertTrueContract(
            strpos($source, $needle) !== false,
            $relativePath . ' perdió la regla caracterizada: ' . $needle
        );
    }
}

function applyPersonalEventContract(array &$projection, array &$inbox, array $event) {
    $hash = hash('sha256', json_encode($event, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    if (isset($inbox[$event['id_evento']])) {
        if (!hash_equals($inbox[$event['id_evento']], $hash)) {
            throw new RuntimeException('ID_EVENTO_CONFLICTIVO');
        }
        return 'REPLAY';
    }
    $inbox[$event['id_evento']] = $hash;
    $persona = $event['persona_uuid'];
    if (isset($projection[$persona]) && $event['persona_version'] <= $projection[$persona]['persona_version']) {
        return 'OBSOLETO_IGNORADO';
    }
    $projection[$persona] = array(
        'persona_version' => $event['persona_version'],
        'tipo' => $event['tipo'],
        'data' => $event['data']
    );
    return 'PROCESADO';
}

function processIdempotentCommandContract(array &$inbox, array $command, array $ack, $httpCode) {
    $id = $command['id_operacion'];
    $hash = hash('sha256', json_encode($command, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    if (isset($inbox[$id])) {
        if (!hash_equals($inbox[$id]['hash'], $hash)) {
            return array('http' => 409, 'codigo' => 'ID_OPERACION_CONFLICTIVO');
        }
        return array('http' => $inbox[$id]['http'], 'ack' => $inbox[$id]['ack']);
    }
    $inbox[$id] = array('hash' => $hash, 'http' => $httpCode, 'ack' => $ack);
    return array('http' => $httpCode, 'ack' => $ack);
}

// Caracteriza el payload maestro estudiantil vigente TIC -> SIGE.
$webhookReflection = new ReflectionClass(SigeWebhook::class);
$webhook = $webhookReflection->newInstanceWithoutConstructor();
$estudiante = array(
    'id_inscripcion' => 9001,
    'identificacion' => '100200300',
    'tipo_documento' => 'CC',
    'nombre_estudiante' => 'Ana',
    'apellido_estudiante' => 'Pérez',
    'nombre_programa' => 'Sistemas',
    'estado_inscripcion' => 3,
    'fecha_inscripcion' => '2026-08-30 14:30:00',
    'uid_rfid' => 'ABC123',
    'codigo_foto' => '100200300',
    'celular_estudiante' => '(300) 123-4567',
    'tipo_sangre' => 'ab+'
);
$payloadActivo = invokePrivateContract($webhook, 'construirPayload', array($estudiante, 'ACTIVO'));
assertSameContract('3001234567', $payloadActivo['celular'] ?? null, 'Webhook normaliza celular maestro TIC');
assertSameContract('AB+', $payloadActivo['rh'] ?? null, 'Webhook normaliza RH maestro TIC');
$estudianteInvalido = $estudiante;
$estudianteInvalido['celular_estudiante'] = '123';
$estudianteInvalido['tipo_sangre'] = 'DESCONOCIDO';
$payloadInvalido = invokePrivateContract($webhook, 'construirPayload', array($estudianteInvalido, 'ACTIVO'));
assertSameContract(null, $payloadInvalido['celular'], 'Webhook neutraliza celular TIC inválido');
assertSameContract(null, $payloadInvalido['rh'], 'Webhook neutraliza RH TIC inválido');
assertSameContract(9001, $payloadActivo['tic_id_inscripcion'], 'ID de inscripción estudiantil');
assertSameContract('100200300', $payloadActivo['numero_documento'], 'Documento estudiantil');
assertSameContract('ACTIVO', $payloadActivo['estado_academico'], 'Estado Q10 presente');
assertSameContract('2026-08-30 14:30:00', $payloadActivo['fecha_registro'], 'Fecha TIC presente al entregar');
assertSameContract('ABC123', $payloadActivo['uid_rfid'], 'UID legacy conservado');

$payloadSinQ10 = invokePrivateContract($webhook, 'construirPayload', array($estudiante, null));
assertTrueContract(!array_key_exists('estado_academico', $payloadSinQ10), 'Sin Q10 no debe regresarse estado académico.');

$estudiante['estado_inscripcion'] = 2;
$payloadNoEntregado = invokePrivateContract($webhook, 'construirPayload', array($estudiante, 'ACTIVO'));
assertTrueContract(!array_key_exists('fecha_registro', $payloadNoEntregado), 'La fecha TIC solo viaja al entregar.');

// Caracteriza ACK estricto del dispatcher estudiantil vigente.
$dispatcherReflection = new ReflectionClass(SigeOrdenDispatcher::class);
$dispatcher = $dispatcherReflection->newInstanceWithoutConstructor();
$operacion = '123e4567-e89b-42d3-a456-426614174000';
$ackValido = invokePrivateContract($dispatcher, 'validarAck', array(
    json_encode(array('status' => 'PROCESADO', 'id_operacion' => $operacion)),
    $operacion
));
assertSameContract(true, $ackValido['valido'], 'ACK correlacionado');

$ackAjeno = invokePrivateContract($dispatcher, 'validarAck', array(
    json_encode(array('status' => 'PROCESADO', 'id_operacion' => '123e4567-e89b-42d3-a456-426614174001')),
    $operacion
));
assertSameContract(false, $ackAjeno['valido'], 'ACK de otra operación');

// Caracteriza el evento versionado SIGE -> TIC vigente.
$carnetReflection = new ReflectionClass(SigeCarnetCompat::class);
$carnet = $carnetReflection->newInstanceWithoutConstructor();
$evento = array(
    'id_evento' => '123e4567-e89b-42d3-a456-426614174010',
    'tipo' => 'CARNET_ASIGNADO',
    'version_estado' => 4,
    'origen' => 'SIGE',
    'ocurrido_en' => '2026-08-30 15:00:00',
    'estudiante' => array(
        'tic_id_inscripcion' => 9001,
        'numero_documento' => '100200300'
    ),
    'carnet' => array(
        'sige_carnet_id' => 77,
        'uid_rfid' => 'ABC123',
        'estado' => 'ACTIVO',
        'motivo' => 'NO_APLICA',
        'fecha_emision' => '2026-08-30',
        'vigencia_hasta' => '2027-02-28'
    )
);
$eventoNormalizado = invokePrivateContract($carnet, 'normalizarEvento', array($evento));
assertSameContract(4, $eventoNormalizado['version_estado'], 'Versión monotónica de evento');
assertSameContract(9001, $eventoNormalizado['tic_id_inscripcion'], 'Correlación de inscripción');
assertSameContract(77, $eventoNormalizado['sige_carnet_id'], 'ID autoritativo SIGE');

// Caracteriza reglas puras y guardas críticas de la integración estudiantil.
$q10Reflection = new ReflectionClass(Q10Gateway::class);
$q10 = $q10Reflection->newInstanceWithoutConstructor();
$q10Activo = invokePrivateContract($q10, 'interpretarUsuario', array(array(
    'Roles' => array(array('Nombre' => 'Estudiante', 'Estado' => true))
)));
assertSameContract('ACTIVO', $q10Activo['estado'], 'Q10 estudiante activo');
$q10Inactivo = invokePrivateContract($q10, 'interpretarUsuario', array(array(
    'Roles' => array(array('Nombre' => 'Estudiante', 'Estado' => false))
)));
assertSameContract('INACTIVO', $q10Inactivo['estado'], 'Q10 estudiante inactivo');
$q10ContratoInvalido = invokePrivateContract($q10, 'interpretarUsuario', array(array(
    'Roles' => array(array('Nombre' => 'Estudiante', 'Estado' => 'yes'))
)));
assertSameContract('Q10_CONTRATO_ESTADO_ROL_INVALIDO', $q10ContratoInvalido['codigo'], 'Q10 booleano estricto');

$outboxReflection = new ReflectionClass(SigeStudentOutbox::class);
$outbox = $outboxReflection->newInstanceWithoutConstructor();
$ahora = time();
$proximoPrimerIntento = strtotime(invokePrivateContract($outbox, 'siguienteIntento', array(1)));
assertTrueContract($proximoPrimerIntento >= $ahora + 55 && $proximoPrimerIntento <= $ahora + 65, 'Backoff inicial del outbox');

$reconciliationReflection = new ReflectionClass(SigeReconciliationWorker::class);
$reconciliation = $reconciliationReflection->newInstanceWithoutConstructor();
assertSameContract('2026-08-30', invokePrivateContract($reconciliation, 'fechaOpcional', array('2026-08-30 16:00:00')), 'Fecha de reconciliación');
try {
    invokePrivateContract($reconciliation, 'fechaOpcional', array('2026-02-30'));
    throw new RuntimeException('La reconciliación aceptó una fecha imposible.');
} catch (RuntimeException $exception) {
    assertTrueContract(strpos($exception->getMessage(), 'Fecha autoritativa inválida') !== false, 'Fecha inválida fail-closed');
}

assertSourceContainsContract('developer/Services/SigeStudentOutbox.php', array('NOT EXISTS (', 'locked_by', 'proximo_intento_en'));
assertSourceContainsContract('developer/Models/SigeCarnetCompat.php', array("<= (int) \$actual['version_estado']", 'EVENTO_OBSOLETO_IGNORADO'));
assertSourceContainsContract('developer/Models/SigeAccesos.php', array('WHERE sige_id = :sige_id LIMIT 1'));
assertSourceContainsContract('developer/Controller/sigeAccesosController.php', array('existeRegistro', 'accepted_ids'));
assertSourceContainsContract('developer/Services/SigeReconciliationWorker.php', array("(int) \$actual['version_estado'] >= \$version", "GET_LOCK('tic_sige_reconciliacion', 0)"));
assertSourceContainsContract('developer/Services/Q10AcademicPoller.php', array("\$repeticiones < 2", 'erroresConsecutivos >= 3'));

// Verifica que los nuevos esquemas son JSON válido, cerrados y separados del modelo académico.
$schemaNames = array(
    'persona-command.schema.json',
    'vinculo-command.schema.json',
    'foto-command.schema.json',
    'carnet-command.schema.json',
    'estado-institucional-command.schema.json',
    'integration-ack.schema.json',
    'integration-event.schema.json',
    'integration-problem.schema.json'
    ,'persona-query-response.schema.json'
);
$forbidden = array(
    'tic_id_inscripcion', 'id_inscripcion', 'estado_academico',
    'fecha_inscripcion', 'id_programa', 'id_sede', 'id_lote'
);
foreach ($schemaNames as $schemaName) {
    $schema = loadSchemaContract($schemaName);
    assertSameContract('https://json-schema.org/draft/2020-12/schema', $schema['$schema'] ?? null, $schemaName . ' draft');
    assertSameContract(false, $schema['additionalProperties'] ?? null, $schemaName . ' debe cerrar el envelope');
    $encoded = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    foreach ($forbidden as $field) {
        assertTrueContract(strpos($encoded, '"' . $field . '"') === false, $schemaName . ' contiene campo académico prohibido: ' . $field);
    }
}

$personaSchema = loadSchemaContract('persona-command.schema.json');
assertSameContract('1.0', $personaSchema['properties']['contract_version']['const'], 'Versión contrato persona');
assertSameContract('CARNETIZACION', $personaSchema['$defs']['usuarioCarnetizacion']['properties']['rol']['const'], 'Rol exacto persona');
assertSameContract('^3[0-9]{9}$', $personaSchema['properties']['persona']['properties']['celular']['pattern'], 'Celular colombiano exacto');

$vinculoSchema = loadSchemaContract('vinculo-command.schema.json');
assertTrueContract(
    in_array('ADMINISTRATIVO', $vinculoSchema['properties']['vinculo']['properties']['tipo']['enum'], true),
    'Vínculo administrativo soportado.'
);
assertTrueContract(isset($vinculoSchema['$defs']['catalogo']['required']), 'Snapshot/version de catálogo requerido.');

$carnetSchema = loadSchemaContract('carnet-command.schema.json');
assertTrueContract(isset($carnetSchema['properties']['persona_uuid']), 'Carné de personal debe usar persona_uuid.');
assertSameContract('CARNETIZACION', $carnetSchema['$defs']['usuarioCarnetizacion']['properties']['rol']['const'], 'Rol exacto carné');
assertTrueContract(
    !in_array('BAJA_INSTITUCIONAL', $carnetSchema['allOf'][2]['then']['properties']['motivo']['enum'], true),
    'La baja institucional no puede ejecutarse como bloqueo aislado.'
);

$fotoSchema = loadSchemaContract('foto-command.schema.json');
assertSameContract(5242880, $fotoSchema['properties']['archivo']['properties']['bytes']['maximum'], 'Límite de foto');
assertTrueContract(isset($fotoSchema['properties']['archivo']['properties']['sha256']), 'Hash de foto obligatorio.');

$estadoSchema = loadSchemaContract('estado-institucional-command.schema.json');
assertTrueContract(in_array('BAJA_INSTITUCIONAL', $estadoSchema['properties']['tipo']['enum'], true), 'Baja institucional atómica.');
assertTrueContract(in_array('REACTIVACION_INSTITUCIONAL', $estadoSchema['properties']['tipo']['enum'], true), 'Reactivación institucional atómica.');

$ackSchema = loadSchemaContract('integration-ack.schema.json');
assertSameContract(array('PROCESADO', 'RECHAZADO'), $ackSchema['properties']['status']['enum'], 'Estados ACK correlacionado');
assertTrueContract(isset($ackSchema['properties']['vinculo_uuid']), 'ACK de vínculo devuelve UUID generado.');

$querySchema = loadSchemaContract('persona-query-response.schema.json');
assertSameContract(100, $querySchema['properties']['page_size']['maximum'], 'Límite de consulta autoritativa');
assertSameContract(false, $querySchema['$defs']['persona']['additionalProperties'], 'Persona consultada usa snapshot cerrado.');
assertTrueContract(isset($querySchema['$defs']['vinculo']['allOf']), 'Consulta conserva invariantes docente/administrativo.');

$eventSchema = loadSchemaContract('integration-event.schema.json');
assertTrueContract(isset($eventSchema['properties']['persona_version']), 'Evento de personal usa versión agregada.');
assertSameContract(false, $eventSchema['$defs']['carnetData']['additionalProperties'], 'Payload de evento carné cerrado.');

// Validaciones semánticas que JSON Schema no expresa por sí solo.
$ejemploPersona = json_decode(file_get_contents(dirname(__DIR__) . '/Contracts/personal/v1/examples/persona-create.valid.json'), true, 512, JSON_THROW_ON_ERROR);
assertTrueContract(filter_var($ejemploPersona['persona']['correo'], FILTER_VALIDATE_EMAIL) !== false, 'Correo del ejemplo válido.');
$ocurrido = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $ejemploPersona['ocurrido_en']);
$erroresFecha = DateTimeImmutable::getLastErrors();
assertTrueContract($ocurrido !== false && ($erroresFecha === false || ($erroresFecha['warning_count'] === 0 && $erroresFecha['error_count'] === 0)), 'ocurrido_en Bogotá válido.');

foreach (glob(dirname(__DIR__) . '/Contracts/personal/v1/examples/*.json') as $examplePath) {
    if (strpos(basename($examplePath), '.semantic-invalid.json') !== false) continue;
    $example = json_decode(file_get_contents($examplePath), true, 512, JSON_THROW_ON_ERROR);
    if (!isset($example['ocurrido_en'])) continue;
    $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $example['ocurrido_en']);
    $dateErrors = DateTimeImmutable::getLastErrors();
    assertTrueContract(
        $date !== false
        && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
        && $date->format('Y-m-d H:i:s') === $example['ocurrido_en'],
        basename($examplePath) . ' contiene ocurrido_en inválido.'
    );
}

$fechaInvalida = json_decode(file_get_contents(dirname(__DIR__) . '/Contracts/personal/v1/examples/persona-date.semantic-invalid.json'), true, 512, JSON_THROW_ON_ERROR);
$date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $fechaInvalida['ocurrido_en']);
$dateErrors = DateTimeImmutable::getLastErrors();
assertTrueContract(
    $date === false || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)),
    'El receptor debe rechazar una fecha imposible aunque cumpla la forma textual.'
);
$correoInvalido = json_decode(file_get_contents(dirname(__DIR__) . '/Contracts/personal/v1/examples/persona-email.semantic-invalid.json'), true, 512, JSON_THROW_ON_ERROR);
assertSameContract(false, filter_var($correoInvalido['persona']['correo'], FILTER_VALIDATE_EMAIL), 'Correo semánticamente inválido');

// Un cambio institucional se consume como snapshot agregado: no hay entrega parcial intraversión.
$eventoInstitucional = json_decode(file_get_contents(dirname(__DIR__) . '/Contracts/personal/v1/examples/event-institutional.valid.json'), true, 512, JSON_THROW_ON_ERROR);
$projection = array();
$inbox = array();
assertSameContract('PROCESADO', applyPersonalEventContract($projection, $inbox, $eventoInstitucional), 'Evento institucional agregado');
$snapshot = $projection[$eventoInstitucional['persona_uuid']]['data'];
assertSameContract(2, count($snapshot['vinculos_afectados']), 'Snapshot contiene todos los vínculos afectados');
assertSameContract('INACTIVO', $snapshot['carnet']['estado'], 'Snapshot contiene resultado del carné');
assertSameContract('REPLAY', applyPersonalEventContract($projection, $inbox, $eventoInstitucional), 'Replay exacto de evento');
$eventoAnterior = $eventoInstitucional;
$eventoAnterior['id_evento'] = '123e4567-e89b-42d3-a456-426614174119';
$eventoAnterior['persona_version'] = 4;
assertSameContract('OBSOLETO_IGNORADO', applyPersonalEventContract($projection, $inbox, $eventoAnterior), 'Entrega fuera de orden');
$eventoConflictivo = $eventoInstitucional;
$eventoConflictivo['data']['persona_estado'] = 'ACTIVA';
try {
    applyPersonalEventContract($projection, $inbox, $eventoConflictivo);
    throw new RuntimeException('Se aceptó un replay conflictivo.');
} catch (RuntimeException $exception) {
    assertSameContract('ID_EVENTO_CONFLICTIVO', $exception->getMessage(), 'Replay conflictivo');
}

// Replay de comando: conserva exactamente HTTP y ACK; payload distinto usa 409.
$command = json_decode(file_get_contents(dirname(__DIR__) . '/Contracts/personal/v1/examples/persona-create.valid.json'), true, 512, JSON_THROW_ON_ERROR);
$ack = json_decode(file_get_contents(dirname(__DIR__) . '/Contracts/personal/v1/examples/ack-processed.valid.json'), true, 512, JSON_THROW_ON_ERROR);
$ack['id_operacion'] = $command['id_operacion'];
$commandInbox = array();
$firstResponse = processIdempotentCommandContract($commandInbox, $command, $ack, 201);
$replayResponse = processIdempotentCommandContract($commandInbox, $command, $ack, 201);
assertSameContract($firstResponse, $replayResponse, 'Replay conserva HTTP y ACK exactos');
$conflictingCommand = $command;
$conflictingCommand['persona']['nombres'] = 'OTRO NOMBRE';
$conflictingResponse = processIdempotentCommandContract($commandInbox, $conflictingCommand, $ack, 201);
assertSameContract(array('http' => 409, 'codigo' => 'ID_OPERACION_CONFLICTIVO'), $conflictingResponse, 'UUID con payload distinto');

echo "contract_characterization_test: OK\n";
