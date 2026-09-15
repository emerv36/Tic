<?php
if (!defined('SIGE_INBOUND_API_KEY')) {
    define('SIGE_INBOUND_API_KEY', 'test_key_123');
}

require_once(__DIR__ . '/../Services/PersonalEventReceiverService.php');

function validPersonaEvent(array $changes = array()) {
    return array_merge(array(
        'contract_version' => '1.0',
        'id_evento' => '11111111-1111-4111-8111-111111111111',
        'id_operacion' => null,
        'tipo' => 'PERSONA_CREADA',
        'entidad' => 'PERSONA',
        'persona_uuid' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'persona_version' => 1,
        'origen' => 'SIGE',
        'ocurrido_en' => '2026-08-31 10:00:00',
        'data' => array(
            'tipo_documento' => 'CC',
            'numero_documento' => '12345',
            'nombres' => 'Juan',
            'apellidos' => 'Pérez',
            'estado' => 'ACTIVA',
            'vinculos_habilitantes' => 1
        )
    ), $changes);
}

function runEventReceiverTests() {
    $pdo = new PDO('sqlite::memory:', '', '', array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ));

    $pdo->exec("
        CREATE TABLE sige_personal_inbox (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_evento TEXT NOT NULL UNIQUE,
            tipo_evento TEXT NOT NULL,
            persona_uuid TEXT NOT NULL,
            version_estado INTEGER NOT NULL,
            ocurrido_en DATETIME NOT NULL,
            payload_hash TEXT NOT NULL,
            payload TEXT NOT NULL,
            procesado INTEGER NOT NULL DEFAULT 0,
            recibido_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ultimo_error TEXT NULL,
            UNIQUE (persona_uuid, version_estado)
        )
    ");

    $service = new PersonalEventReceiverService($pdo);

    // Test 1: Evento exitoso
    $payload1 = json_encode(validPersonaEvent(), JSON_UNESCAPED_UNICODE);
    $result1 = $service->receive($payload1);
    if ($result1['status'] !== 202 || $result1['codigo'] !== 'PROCESADO') throw new Exception("Test 1: Falló procesado inicial");

    $row = $pdo->query("SELECT * FROM sige_personal_inbox WHERE id_evento = '11111111-1111-4111-8111-111111111111'")->fetch();
    if (!$row) throw new Exception("Test 1: Fila no insertada");

    // Test 2: Idempotencia (mismo payload)
    $result2 = $service->receive($payload1);
    if ($result2['status'] !== 202 || $result2['codigo'] !== 'IDEMPOTENT_OK') throw new Exception("Test 2: Falló idempotencia");

    // Test 3: Conflicto (mismo ID pero payload diferente)
    $payload3 = json_encode(validPersonaEvent(array(
        'persona_version' => 2,
        'ocurrido_en' => '2026-08-31 10:00:01'
    )), JSON_UNESCAPED_UNICODE);
    $result3 = $service->receive($payload3);
    if ($result3['status'] !== 409 || $result3['codigo'] !== 'DUPLICATE_CONFLICT') throw new Exception("Test 3: Falló rechazo de conflicto");

    // Test 4: Payload demasiado grande (simulado)
    $largePayload = str_repeat("a", (5 * 1024 * 1024) + 1);
    $result4 = $service->receive($largePayload);
    if ($result4['status'] !== 400 || $result4['codigo'] !== 'PAYLOAD_TOO_LARGE') throw new Exception("Test 4: Falló validación de tamaño");

    // Test 5: JSON inválido
    $result5 = $service->receive("esto no es json");
    if ($result5['status'] !== 400 || $result5['codigo'] !== 'INVALID_JSON') throw new Exception("Test 5: Falló validación de JSON");

    // Test 6: Falta campo obligatorio
    $payload6 = json_encode(array(
        'id_evento' => 'evt-6',
        'tipo' => 'PERSONA_CREADA'
    ));
    $result6 = $service->receive($payload6);
    if ($result6['status'] !== 400 || $result6['codigo'] !== 'MISSING_FIELD') throw new Exception("Test 6: Falló validación de campos requeridos");

    // Test 7: El alias anterior tipo_evento ya no pertenece al contrato canónico.
    $evento7 = validPersonaEvent(array('id_evento' => '77777777-7777-4777-8777-777777777777'));
    $evento7['tipo_evento'] = $evento7['tipo'];
    unset($evento7['tipo']);
    $payload7 = json_encode($evento7, JSON_UNESCAPED_UNICODE);
    $result7 = $service->receive($payload7);
    if ($result7['status'] !== 400 || $result7['codigo'] !== 'MISSING_FIELD') throw new Exception("Test 7: Aceptó el alias tipo_evento");

    // Test 8: Un tipo fuera del contrato no puede consumir una versión.
    $payload8 = json_encode(validPersonaEvent(array(
        'id_evento' => '88888888-8888-4888-8888-888888888888',
        'tipo' => 'PERSONA_SINCRONIZADA',
        'persona_uuid' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
        'ocurrido_en' => '2026-08-31 10:00:03'
    )), JSON_UNESCAPED_UNICODE);
    $result8 = $service->receive($payload8);
    if ($result8['status'] !== 422 || $result8['codigo'] !== 'CONTRACT_INVALID') throw new Exception("Test 8: Aceptó tipo fuera del contrato");

    // Test 9: El esquema de carné exige los siete campos y estado físico.
    $evento9 = validPersonaEvent(array(
        'id_evento' => '99999999-9999-4999-8999-999999999999',
        'persona_uuid' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        'tipo' => 'CARNET_ASIGNADO',
        'entidad' => 'CARNET',
        'data' => array(
            'sige_carnet_id' => 99,
            'uid_rfid' => 'AABBCCDD',
            'estado' => 'ACTIVO',
            'motivo' => 'NO_APLICA',
            'fecha_emision' => '2026-08-31 10:00:00',
            'vigencia_hasta' => null,
            'entregado_en' => null
        )
    ));
    $result9 = $service->receive(json_encode($evento9));
    if ($result9['status'] !== 202 || $result9['codigo'] !== 'PROCESADO') throw new Exception("Test 9: Rechazó carnetData válido");

    $evento10 = $evento9;
    $evento10['id_evento'] = 'aaaaaaaa-1111-4111-8111-111111111111';
    $evento10['data']['estado'] = 'EMITIDO';
    $result10 = $service->receive(json_encode($evento10));
    if ($result10['status'] !== 422 || $result10['codigo'] !== 'CONTRACT_INVALID') throw new Exception("Test 10: Aceptó estado visual en vez de físico");

    $evento11 = validPersonaEvent(array('id_evento' => 'bbbbbbbb-1111-4111-8111-111111111111'));
    $evento11['propiedad_no_contratada'] = true;
    $result11 = $service->receive(json_encode($evento11));
    if ($result11['status'] !== 422 || $result11['codigo'] !== 'CONTRACT_INVALID') throw new Exception("Test 11: Aceptó propiedad adicional");

    $evento12 = validPersonaEvent(array('id_evento' => 'cccccccc-1111-4111-8111-111111111111'));
    $result12 = $service->receive(json_encode($evento12, JSON_UNESCAPED_UNICODE));
    if ($result12['status'] !== 409 || $result12['codigo'] !== 'PERSON_VERSION_CONFLICT') throw new Exception("Test 12: No detectó colisión persona-versión");

    echo "personal_event_receiver_test: OK\n";
}

runEventReceiverTests();
