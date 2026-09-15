<?php
// Simulación de prueba E2E (End-to-End) M6
// Este script verifica el flujo completo del comando y eventos de personal

require_once __DIR__ . '/../Config/PDOconn.php';
require_once __DIR__ . '/../Services/PersonalInboxProcessor.php';
require_once 'C:/xampp/htdocs/Sige/app/Services/PersonalCarnetService.php';

echo "Iniciando Test M6 E2E Integration...\n";

try {
    // Usar SQLite en memoria
    $db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Crear tablas necesarias para la simulación
    $db->exec("CREATE TABLE sige_personal_proyeccion (id INTEGER PRIMARY KEY, persona_uuid TEXT, tipo_documento TEXT, numero_documento TEXT, nombres TEXT, apellidos TEXT, estado_persona TEXT, vinculos_json TEXT, tiene_foto INTEGER, version_estado INTEGER, uid_rfid TEXT, carnet_estado TEXT, carnet_motivo TEXT, vigencia_hasta TEXT)");
    $db->exec("CREATE TABLE sige_personal_inbox (id INTEGER PRIMARY KEY, id_evento TEXT, tipo_evento TEXT, persona_uuid TEXT, version_estado INTEGER, ocurrido_en TEXT, payload TEXT, payload_hash TEXT, procesado INTEGER DEFAULT 0)");
    
    $db->exec("CREATE TABLE personas (id INTEGER PRIMARY KEY, uuid TEXT, version_estado INTEGER)");
    $db->exec("CREATE TABLE integracion_comandos (id INTEGER PRIMARY KEY, id_operacion TEXT, tipo TEXT, numero_documento TEXT, expected_version INTEGER, payload_hash TEXT, payload TEXT)");
    $db->exec("CREATE TABLE integracion_eventos_outbox (id INTEGER PRIMARY KEY, id_evento TEXT, tipo TEXT, documento TEXT, payload_hash TEXT, payload TEXT)");
    $db->exec("CREATE TABLE carnets (id INTEGER PRIMARY KEY, persona_id INTEGER, uid_rfid TEXT, estado TEXT, vigencia_hasta TEXT, emitido_en TEXT)");

    $personaUuid = '11111111-2222-3333-4444-555555555555';
    
    // 1. Preparar proyección inicial (TIC)
    $db->exec("INSERT INTO sige_personal_proyeccion (persona_uuid, tipo_documento, numero_documento, nombres, apellidos, estado_persona, vinculos_json, tiene_foto, version_estado) 
               VALUES ('$personaUuid', 'CC', '123456789', 'Juan', 'Prueba', 'ACTIVA', '[{\"tipo\":\"DOCENTE\"},{\"tipo\":\"ADMINISTRATIVO\"}]', 1, 1)");

    // 2. Insertar maestro en SIGE
    $db->exec("INSERT INTO personas (uuid, version_estado) VALUES ('$personaUuid', 1)");
    $personaId = $db->lastInsertId();

    // 3. Simular Acción de la UI (TIC) -> Creación de comando
    $idOperacion = 'op-1234';
    $payloadComando = [
        'id_operacion' => $idOperacion,
        'tipo' => 'ASIGNACION',
        'persona_uuid' => $personaUuid,
        'uid_rfid' => 'AA-BB-CC-DD',
        'expected_persona_version' => 1
    ];

    // 4. Servicio SIGE procesa el comando
    $sigeService = new PersonalCarnetService($db);
    $resultadoSige = $sigeService->procesar($payloadComando);
    
    if ($resultadoSige['http_status'] !== 202) {
        throw new Exception("Error en SIGE: " . json_encode($resultadoSige));
    }
    
    // Verificar idempotencia
    $resultadoSigeIdem = $sigeService->procesar($payloadComando);
    if ($resultadoSigeIdem['http_status'] !== 202 || $resultadoSigeIdem['message'] !== 'Operación previamente procesada') {
        throw new Exception("Fallo en idempotencia");
    }

    // 5. Simular Webhook de SIGE a TIC
    // (Extraemos el evento generado de integracion_eventos_outbox)
    $stmt = $db->query("SELECT payload FROM integracion_eventos_outbox WHERE documento = '$personaUuid' ORDER BY id DESC LIMIT 1");
    $eventoRaw = $stmt->fetchColumn();
    if (!$eventoRaw) throw new Exception("No se generó evento en SIGE");
    $evento = json_decode($eventoRaw, true);

    // 6. TIC Inbox procesa el evento
    $processor = new PersonalInboxProcessor($db);
    
    // Inyectamos el evento directamente en sige_personal_inbox
    $stmtIn = $db->prepare("INSERT INTO sige_personal_inbox (id_evento, tipo_evento, persona_uuid, version_estado, ocurrido_en, payload, payload_hash) 
                            VALUES (:id, :tipo, :uuid, :ver, NOW(), :pay, :hash)");
    $stmtIn->execute([
        'id' => $evento['id_evento'],
        'tipo' => $evento['tipo_evento'],
        'uuid' => $personaUuid,
        'ver' => $evento['version_estado'],
        'pay' => $eventoRaw,
        'hash' => hash('sha256', $eventoRaw)
    ]);
    
    $processor->procesarPendientes();

    // 7. Aserciones finales (TIC)
    $stmtProj = $db->query("SELECT * FROM sige_personal_proyeccion WHERE persona_uuid = '$personaUuid'");
    $proj = $stmtProj->fetch(PDO::FETCH_ASSOC);

    if ($proj['uid_rfid'] !== 'AA-BB-CC-DD') throw new Exception("UID no actualizado en TIC");
    if ($proj['carnet_estado'] !== 'EMITIDO') throw new Exception("Estado no actualizado a EMITIDO en TIC");
    if ($proj['version_estado'] != 2) throw new Exception("Versión no actualizada a 2 en TIC");

    $db->rollBack();
    echo "TEST M6 E2E: OK (Todos los invariantes validados exitosamente)\n";
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "TEST M6 E2E: FAILED - " . $e->getMessage() . "\n";
    exit(1);
}
