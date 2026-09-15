<?php
require_once(__DIR__ . '/../Services/PersonalInboxProcessor.php');

function runInboxProcessorTests() {
    $pdo = new PDO('sqlite::memory:', '', '', array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
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
            error_proceso TEXT NULL
        )
    ");

    $pdo->exec("
        CREATE TABLE sige_personal_proyeccion (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            persona_uuid TEXT NOT NULL UNIQUE,
            tipo_documento TEXT NOT NULL,
            numero_documento TEXT NOT NULL,
            nombres TEXT NOT NULL,
            apellidos TEXT NOT NULL,
            estado_persona TEXT NOT NULL,
            vinculos_json TEXT NOT NULL,
            tiene_foto INTEGER NOT NULL DEFAULT 0,
            sige_carnet_id INTEGER NULL,
            uid_rfid TEXT NULL,
            carnet_estado TEXT NULL,
            carnet_motivo TEXT NULL,
            vigencia_hasta DATETIME NULL,
            requiere_reactivacion INTEGER NOT NULL DEFAULT 0,
            version_estado INTEGER NOT NULL DEFAULT 1,
            actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE sige_personal_conflictos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_operacion TEXT NOT NULL,
            persona_uuid TEXT NOT NULL,
            version_esperada INTEGER NOT NULL,
            version_actual_sige INTEGER NOT NULL,
            estado_resolucion TEXT NOT NULL DEFAULT 'PENDIENTE',
            comentario_resolucion TEXT NULL,
            resuelto_por_usuario_id INTEGER NULL,
            resuelto_por_usuario_nombre TEXT NULL,
            resuelto_por_usuario_rol TEXT NULL,
            creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            resuelto_en DATETIME NULL
        )
    ");

    $pdo->exec("
        CREATE TABLE sige_personal_auditoria (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            persona_uuid TEXT NOT NULL,
            id_operacion TEXT NULL,
            id_evento TEXT NULL,
            accion TEXT NOT NULL,
            antes_json TEXT NULL,
            despues_json TEXT NULL,
            actor_id INTEGER NULL,
            actor_nombre TEXT NULL,
            actor_rol TEXT NULL,
            actor_origen TEXT NOT NULL,
            creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $processor = new PersonalInboxProcessor($pdo);

    // TEST 1: Persona creada (Version 1)
    $payload1 = json_encode(array(
        'tipo' => 'PERSONA_CREADA',
        'data' => array(
            'tipo_documento' => 'CC',
            'numero_documento' => '123',
            'nombres' => 'Juan',
            'apellidos' => 'Perez',
            'estado' => 'ACTIVA'
        ),
        'actor' => array('nombre_snapshot' => 'Admin')
    ));
    $pdo->exec("INSERT INTO sige_personal_inbox (id_evento, tipo_evento, persona_uuid, version_estado, ocurrido_en, payload_hash, payload) 
                VALUES ('evt-1', 'PERSONA_CREADA', 'uuid-1', 1, CURRENT_TIMESTAMP, 'hash1', '$payload1')");
    
    $resumen = $processor->procesarPendientes(10);
    if ($resumen['procesados'] !== 1 || $resumen['pendientes'] !== 0 || $resumen['fallidos'] !== 0) throw new Exception("Test 1: Debería procesar 1 evento");

    $proy = $pdo->query("SELECT * FROM sige_personal_proyeccion WHERE persona_uuid = 'uuid-1'")->fetch();
    if ($proy['version_estado'] != 1 || $proy['nombres'] != 'Juan') throw new Exception("Test 1: Proyección no creada correctamente");

    $aud = $pdo->query("SELECT * FROM sige_personal_auditoria WHERE persona_uuid = 'uuid-1' AND id_evento = 'evt-1'")->fetch();
    if (!$aud) throw new Exception("Test 1: Auditoría no registrada");

    // TEST 2: Vínculo creado (Version 2)
    $payload2 = json_encode(array(
        'tipo' => 'VINCULO_CREADO',
        'data' => array(
            'vinculo_uuid' => 'vinc-1',
            'tipo' => 'DOCENTE',
            'estado' => 'ACTIVO'
        )
    ));
    $pdo->exec("INSERT INTO sige_personal_inbox (id_evento, tipo_evento, persona_uuid, version_estado, ocurrido_en, payload_hash, payload) 
                VALUES ('evt-2', 'VINCULO_CREADO', 'uuid-1', 2, CURRENT_TIMESTAMP, 'hash2', '$payload2')");
    
    $processor->procesarPendientes(10);
    $proy2 = $pdo->query("SELECT * FROM sige_personal_proyeccion WHERE persona_uuid = 'uuid-1'")->fetch();
    if ($proy2['version_estado'] != 2) throw new Exception("Test 2: Versión no actualizada");
    
    $vinculos = json_decode($proy2['vinculos_json'], true);
    if (count($vinculos) !== 1 || $vinculos[0]['tipo'] !== 'DOCENTE') throw new Exception("Test 2: Vínculo no agregado al JSON");

    // TEST 3: Brecha de versión (Llega versión 4, se espera 3)
    $pdo->exec("INSERT INTO sige_personal_inbox (id_evento, tipo_evento, persona_uuid, version_estado, ocurrido_en, payload_hash, payload) 
                VALUES ('evt-4', 'FOTO_ACTUALIZADA', 'uuid-1', 4, CURRENT_TIMESTAMP, 'hash4', '{\"tipo\":\"FOTO_ACTUALIZADA\"}')");
    
    $processor->procesarPendientes(10);
    $proy3 = $pdo->query("SELECT * FROM sige_personal_proyeccion WHERE persona_uuid = 'uuid-1'")->fetch();
    if ($proy3['version_estado'] == 4) throw new Exception("Test 3: No debió actualizarse a 4");
    
    $conflicto = $pdo->query("SELECT * FROM sige_personal_conflictos WHERE persona_uuid = 'uuid-1'")->fetch();
    if (!$conflicto || $conflicto['version_esperada'] != 3 || $conflicto['version_actual_sige'] != 4) throw new Exception("Test 3: Conflicto no registrado correctamente");
    
    $inboxEvt4 = $pdo->query("SELECT procesado, error_proceso FROM sige_personal_inbox WHERE id_evento = 'evt-4'")->fetch();
    if ($inboxEvt4['procesado'] != 0 || strpos((string)$inboxEvt4['error_proceso'], 'Brecha de versión') === false) throw new Exception("Test 3: Evento 4 debió quedar pendiente y auditado con la brecha.");

    // TEST 4: Llega el evento faltante (Version 3)
    $carnetAsignado = json_encode(array('tipo' => 'CARNET_ASIGNADO', 'data' => array(
        'sige_carnet_id' => 99, 'uid_rfid' => 'AABBCCDD', 'estado' => 'ACTIVO',
        'motivo' => 'NO_APLICA', 'fecha_emision' => '2026-08-31 14:45:00',
        'vigencia_hasta' => null, 'entregado_en' => null
    )));
    $pdo->exec("INSERT INTO sige_personal_inbox (id_evento, tipo_evento, persona_uuid, version_estado, ocurrido_en, payload_hash, payload) 
                VALUES ('evt-3', 'CARNET_ASIGNADO', 'uuid-1', 3, CURRENT_TIMESTAMP, 'hash3', " . $pdo->quote($carnetAsignado) . ")");
    
    $processor->procesarPendientes(10); // evt-4 falla (aun espera 3 porque fue el 1ero por ID), luego evt-3 procesa
    $processor->procesarPendientes(10); // evt-4 procesa

    $proy4 = $pdo->query("SELECT * FROM sige_personal_proyeccion WHERE persona_uuid = 'uuid-1'")->fetch();
    if ($proy4['version_estado'] != 4 || $proy4['sige_carnet_id'] != 99 || $proy4['tiene_foto'] != 1 || $proy4['carnet_estado'] !== 'EMITIDO') throw new Exception("Test 4: Auto-recuperación de cola falló. Versión actual: " . $proy4['version_estado']);

    $conflictoCerrado = $pdo->query("SELECT estado_resolucion FROM sige_personal_conflictos WHERE persona_uuid = 'uuid-1'")->fetch();
    if ($conflictoCerrado['estado_resolucion'] !== 'RESUELTO') throw new Exception("Test 4: Conflicto no fue resuelto automáticamente");

    // TEST 5: Entrega deriva el estado visual ENTREGADO sin alterar el estado físico recibido.
    $carnetEntregado = json_encode(array('tipo' => 'CARNET_ENTREGADO', 'data' => array(
        'sige_carnet_id' => 99, 'uid_rfid' => 'AABBCCDD', 'estado' => 'ACTIVO',
        'motivo' => 'NO_APLICA', 'fecha_emision' => '2026-08-31 14:45:00',
        'vigencia_hasta' => null, 'entregado_en' => '2026-08-31 15:00:00'
    )));
    $pdo->exec("INSERT INTO sige_personal_inbox (id_evento, tipo_evento, persona_uuid, version_estado, ocurrido_en, payload_hash, payload)
                VALUES ('evt-5', 'CARNET_ENTREGADO', 'uuid-1', 5, CURRENT_TIMESTAMP, 'hash5', " . $pdo->quote($carnetEntregado) . ")");
    $processor->procesarPendientes(10);
    $proy5 = $pdo->query("SELECT * FROM sige_personal_proyeccion WHERE persona_uuid = 'uuid-1'")->fetch();
    if ($proy5['version_estado'] != 5 || $proy5['carnet_estado'] !== 'ENTREGADO') throw new Exception("Test 5: No derivó ENTREGADO");

    // TEST 6: Un carné físicamente INACTIVO se proyecta como BLOQUEADO.
    $carnetBloqueado = json_encode(array('tipo' => 'CARNET_BLOQUEADO', 'data' => array(
        'sige_carnet_id' => 99, 'uid_rfid' => 'AABBCCDD', 'estado' => 'INACTIVO',
        'motivo' => 'ROBO', 'fecha_emision' => '2026-08-31 14:45:00',
        'vigencia_hasta' => null, 'entregado_en' => '2026-08-31 15:00:00'
    )));
    $pdo->exec("INSERT INTO sige_personal_inbox (id_evento, tipo_evento, persona_uuid, version_estado, ocurrido_en, payload_hash, payload)
                VALUES ('evt-6', 'CARNET_BLOQUEADO', 'uuid-1', 6, CURRENT_TIMESTAMP, 'hash6', " . $pdo->quote($carnetBloqueado) . ")");
    $processor->procesarPendientes(10);
    $proy6 = $pdo->query("SELECT * FROM sige_personal_proyeccion WHERE persona_uuid = 'uuid-1'")->fetch();
    if ($proy6['version_estado'] != 6 || $proy6['carnet_estado'] !== 'BLOQUEADO' || $proy6['carnet_motivo'] !== 'ROBO') throw new Exception("Test 6: No derivó BLOQUEADO");

    // TEST 7: Reemplazo proyecta un nuevo carné ACTIVO todavía no entregado.
    $carnetReemplazado = json_encode(array('tipo' => 'CARNET_REEMPLAZADO', 'data' => array(
        'sige_carnet_id' => 100, 'uid_rfid' => '11223344', 'estado' => 'ACTIVO',
        'motivo' => 'NO_APLICA', 'fecha_emision' => '2026-09-01 08:00:00',
        'vigencia_hasta' => null, 'entregado_en' => null
    )));
    $pdo->exec("INSERT INTO sige_personal_inbox (id_evento, tipo_evento, persona_uuid, version_estado, ocurrido_en, payload_hash, payload)
                VALUES ('evt-7', 'CARNET_REEMPLAZADO', 'uuid-1', 7, CURRENT_TIMESTAMP, 'hash7', " . $pdo->quote($carnetReemplazado) . ")");
    $processor->procesarPendientes(10);
    $proy7 = $pdo->query("SELECT * FROM sige_personal_proyeccion WHERE persona_uuid = 'uuid-1'")->fetch();
    if ($proy7['version_estado'] != 7 || $proy7['sige_carnet_id'] != 100 || $proy7['carnet_estado'] !== 'EMITIDO') throw new Exception("Test 7: Reemplazo no proyectado");

    // TEST 8: Baja institucional reutiliza el mismo mapper físico -> UI.
    $baja = json_encode(array('tipo' => 'ESTADO_INSTITUCIONAL_CAMBIADO', 'data' => array(
        'persona_estado' => 'INACTIVA', 'vinculos_afectados' => array(),
        'carnet' => array('sige_carnet_id' => 100, 'uid_rfid' => '11223344', 'estado' => 'INACTIVO',
            'motivo' => 'BAJA_INSTITUCIONAL', 'fecha_emision' => '2026-09-01 08:00:00',
            'vigencia_hasta' => null, 'entregado_en' => null)
    )));
    $pdo->exec("INSERT INTO sige_personal_inbox (id_evento, tipo_evento, persona_uuid, version_estado, ocurrido_en, payload_hash, payload)
                VALUES ('evt-8', 'ESTADO_INSTITUCIONAL_CAMBIADO', 'uuid-1', 8, CURRENT_TIMESTAMP, 'hash8', " . $pdo->quote($baja) . ")");
    $processor->procesarPendientes(10);
    $proy8 = $pdo->query("SELECT * FROM sige_personal_proyeccion WHERE persona_uuid = 'uuid-1'")->fetch();
    if ($proy8['version_estado'] != 8 || $proy8['estado_persona'] !== 'INACTIVA' || $proy8['carnet_estado'] !== 'BLOQUEADO') throw new Exception("Test 8: Baja institucional corrompió carné UI");

    // TEST 9: Reactivación con carné activo y entregado deriva ENTREGADO.
    $reactivacion = json_encode(array('tipo' => 'ESTADO_INSTITUCIONAL_CAMBIADO', 'data' => array(
        'persona_estado' => 'ACTIVA', 'vinculos_afectados' => array(),
        'carnet' => array('sige_carnet_id' => 101, 'uid_rfid' => '55667788', 'estado' => 'ACTIVO',
            'motivo' => 'NO_APLICA', 'fecha_emision' => '2026-09-01 09:00:00',
            'vigencia_hasta' => null, 'entregado_en' => '2026-09-01 09:30:00')
    )));
    $pdo->exec("INSERT INTO sige_personal_inbox (id_evento, tipo_evento, persona_uuid, version_estado, ocurrido_en, payload_hash, payload)
                VALUES ('evt-9', 'ESTADO_INSTITUCIONAL_CAMBIADO', 'uuid-1', 9, CURRENT_TIMESTAMP, 'hash9', " . $pdo->quote($reactivacion) . ")");
    $processor->procesarPendientes(10);
    $proy9 = $pdo->query("SELECT * FROM sige_personal_proyeccion WHERE persona_uuid = 'uuid-1'")->fetch();
    if ($proy9['version_estado'] != 9 || $proy9['estado_persona'] !== 'ACTIVA' || $proy9['carnet_estado'] !== 'ENTREGADO') throw new Exception("Test 9: Reactivación institucional no proyectada");

    // TEST 10: El procesamiento dirigido no consume eventos ajenos al run.
    $pdo->exec("INSERT INTO sige_personal_inbox (id_evento, tipo_evento, persona_uuid, version_estado, ocurrido_en, payload_hash, payload) VALUES
        ('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaa10', 'FOTO_ACTUALIZADA', 'uuid-1', 10, CURRENT_TIMESTAMP, 'hash10', '{\"tipo\":\"FOTO_ACTUALIZADA\",\"data\":{}}'),
        ('evt-unrelated', 'PERSONA_CREADA', 'uuid-other', 1, CURRENT_TIMESTAMP, 'hashU', '{\"tipo\":\"PERSONA_CREADA\",\"data\":{\"tipo_documento\":\"CC\",\"numero_documento\":\"999\",\"nombres\":\"Otro\",\"apellidos\":\"Usuario\",\"estado\":\"ACTIVA\"}}')");
    $scoped = $processor->procesarEventos(array('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaa10'));
    if ($scoped['procesados'] !== 1 || (int)$pdo->query("SELECT procesado FROM sige_personal_inbox WHERE id_evento='evt-unrelated'")->fetchColumn() !== 0) throw new Exception("Test 10: El run consumió un evento ajeno");

    // TEST 11: Una colisión histórica de persona-versión no se descarta como obsoleta.
    $pdo->exec("INSERT INTO sige_personal_inbox (id_evento, tipo_evento, persona_uuid, version_estado, ocurrido_en, payload_hash, payload) 
                VALUES ('evt-old', 'VINCULO_CREADO', 'uuid-1', 2, CURRENT_TIMESTAMP, 'hashOld', '{}')");
    $processor->procesarPendientes(10);
    
    $inboxOld = $pdo->query("SELECT procesado, error_proceso FROM sige_personal_inbox WHERE id_evento = 'evt-old'")->fetch();
    if ($inboxOld['procesado'] != 0 || strpos($inboxOld['error_proceso'], 'otro id_evento') === false) throw new Exception("Test 11: Colisión histórica se descartó silenciosamente");
    
    $proy10 = $pdo->query("SELECT * FROM sige_personal_proyeccion WHERE persona_uuid = 'uuid-1'")->fetch();
    if ($proy10['version_estado'] != 10) throw new Exception("Test 11: Versión no debió cambiar");

    echo "personal_inbox_processor_test: OK\n";
}

runInboxProcessorTests();
