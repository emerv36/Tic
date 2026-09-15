<?php
require dirname(__DIR__) . '/Config/PDOconn.php';
require 'C:/xampp/htdocs/Sige/app/Config/config.php';

$suffix = bin2hex(random_bytes(5));
$ticDb = 'tic_reconcile_test_' . $suffix;
$sigeDb = 'sige_reconcile_test_' . $suffix;
if (!preg_match('/^[a-z_]+[0-9a-f]{10}$/', $ticDb) || !preg_match('/^[a-z_]+[0-9a-f]{10}$/', $sigeDb)) throw new RuntimeException('Nombres temporales inseguros.');
$root = new PDO('mysql:host=' . host . ';charset=utf8mb4', user, pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$ticTables = array('sige_personal_cargo_catalogo','sige_personal_dependencia_catalogo','sige_personal_proyeccion','sige_personal_outbox','sige_personal_inbox','sige_personal_auditoria','sige_personal_conflictos');
$sigeTables = array('personas','persona_vinculos','persona_fotos','carnets','accesos','estudiantes','integracion_personal_comandos','integracion_personal_outbox','integracion_catalogos_observados','personas_auditoria','persona_vinculos_auditoria');
try {
    $root->exec("CREATE DATABASE `$ticDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $root->exec("CREATE DATABASE `$sigeDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    foreach ($ticTables as $table) {
        $root->exec("CREATE TABLE `$ticDb`.`$table` LIKE `" . dbname . "`.`$table`");
        $root->exec("INSERT INTO `$ticDb`.`$table` SELECT * FROM `" . dbname . "`.`$table`");
    }
    foreach ($sigeTables as $table) {
        $root->exec("CREATE TABLE `$sigeDb`.`$table` LIKE `" . DB_NAME . "`.`$table`");
        $root->exec("INSERT INTO `$sigeDb`.`$table` SELECT * FROM `" . DB_NAME . "`.`$table`");
    }
    putenv('TIC_RECONCILE_DB=' . $ticDb);
    putenv('SIGE_RECONCILE_DB=' . $sigeDb);
    $script = dirname(__DIR__, 2) . '/artifacts/reconcile_admin_card_20260901.php';
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script) . ' --apply 2>&1';
    exec($command, $output, $exitCode);
    if ($exitCode !== 0) throw new RuntimeException("Apply clonado falló:\n" . implode("\n", $output));

    $tic = new PDO('mysql:host=' . host . ';dbname=' . $ticDb . ';charset=utf8mb4', user, pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC));
    $sige = new PDO('mysql:host=' . DB_HOST . ';dbname=' . $sigeDb . ';charset=utf8mb4', DB_USER, DB_PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC));
    $foreignEvent = '123e4567-e89b-42d3-a456-426614174299';
    $foreignPerson = '123e4567-e89b-42d3-a456-426614174298';
    $foreignPayload = '{"evento":"NO_PERTENECE_A_LA_CONCILIACION"}';
    $foreign = $tic->prepare("INSERT INTO sige_personal_inbox(id_evento,tipo_evento,persona_uuid,version_estado,ocurrido_en,payload,payload_hash,procesado) VALUES(:event,'PERSONA_CREADA',:person,99,NOW(),:payload,:hash,0)");
    $foreign->execute(array('event' => $foreignEvent, 'person' => $foreignPerson, 'payload' => $foreignPayload, 'hash' => hash('sha256', $foreignPayload)));
    $projection = $tic->query("SELECT * FROM sige_personal_proyeccion WHERE numero_documento='1001916903'")->fetch();
    if (!$projection || $projection['persona_uuid'] !== '77cc18e9-c856-4a87-8b18-ef4527a74cfe' || $projection['carnet_estado'] !== 'ENTREGADO' || (int)$projection['version_estado'] !== 6) throw new RuntimeException('La proyección final clonada es incorrecta.');
    if ((int)$tic->query("SELECT COUNT(*) FROM sige_personal_auditoria WHERE accion='ARCHIVAR_SIMULACION'")->fetchColumn() !== 2) throw new RuntimeException('No archivó exactamente dos simulaciones.');
    $pendingOwn = $tic->prepare("SELECT COUNT(*) FROM sige_personal_inbox WHERE procesado=0 AND id_evento<>:foreign");
    $pendingOwn->execute(array('foreign' => $foreignEvent));
    if ((int)$pendingOwn->fetchColumn() !== 0) throw new RuntimeException('El inbox clonado dejó eventos propios pendientes.');
    $card = $sige->query("SELECT c.*,p.persona_version FROM carnets c JOIN personas p ON p.id=c.persona_id WHERE p.numero_documento_normalizado='1001916903' AND c.estado='ACTIVO'")->fetch();
    if (!$card || substr($card['uid_rfid'], -4) !== '8967' || $card['entregado_en'] === null || (int)$card['persona_version'] !== 6) throw new RuntimeException('El carné SIGE clonado es incorrecto.');
    if ((int)$sige->query("SELECT COUNT(*) FROM accesos WHERE terminal_id='RECONCILIACION-TIC-SIGE' AND resultado='PERMITIDO'")->fetchColumn() !== 1) throw new RuntimeException('La prueba clonada del kiosco no fue permitida.');

    $auditCount = (int)$tic->query("SELECT COUNT(*) FROM sige_personal_auditoria WHERE accion='ARCHIVAR_SIMULACION'")->fetchColumn();
    $eventCount = (int)$sige->query("SELECT COUNT(*) FROM integracion_personal_outbox WHERE persona_uuid='77cc18e9-c856-4a87-8b18-ef4527a74cfe'")->fetchColumn();
    $cardCount = (int)$sige->query("SELECT COUNT(*) FROM carnets c JOIN personas p ON p.id=c.persona_id WHERE p.numero_documento_normalizado='1001916903'")->fetchColumn();

    // La segunda ejecución prueba recuperación/idempotencia y conserva todos los conteos.
    exec($command, $secondOutput, $secondExit);
    if ($secondExit !== 0) throw new RuntimeException("Reanudación clonada falló:\n" . implode("\n", $secondOutput));
    if ((int)$sige->query("SELECT COUNT(*) FROM accesos WHERE terminal_id='RECONCILIACION-TIC-SIGE' AND resultado='PERMITIDO'")->fetchColumn() !== 1) throw new RuntimeException('La reanudación duplicó la prueba de acceso.');
    if ((int)$tic->query("SELECT COUNT(*) FROM sige_personal_auditoria WHERE accion='ARCHIVAR_SIMULACION'")->fetchColumn() !== $auditCount) throw new RuntimeException('La reanudación duplicó la auditoría de archivo.');
    if ((int)$sige->query("SELECT COUNT(*) FROM integracion_personal_outbox WHERE persona_uuid='77cc18e9-c856-4a87-8b18-ef4527a74cfe'")->fetchColumn() !== $eventCount) throw new RuntimeException('La reanudación duplicó eventos SIGE.');
    if ((int)$sige->query("SELECT COUNT(*) FROM carnets c JOIN personas p ON p.id=c.persona_id WHERE p.numero_documento_normalizado='1001916903'")->fetchColumn() !== $cardCount) throw new RuntimeException('La reanudación duplicó el carné.');
    if ((int)$tic->query("SELECT COUNT(*) FROM sige_personal_proyeccion WHERE persona_uuid='77cc18e9-c856-4a87-8b18-ef4527a74cfe' AND version_estado=6")->fetchColumn() !== 1) throw new RuntimeException('La reanudación alteró la proyección final.');
    if ((int)$tic->query("SELECT COUNT(*) FROM sige_personal_inbox WHERE id_evento='$foreignEvent' AND procesado=0")->fetchColumn() !== 1) throw new RuntimeException('La conciliación procesó un evento ajeno.');
    echo "reconcile_admin_card_clone_test: OK\n";
} finally {
    putenv('TIC_RECONCILE_DB');putenv('SIGE_RECONCILE_DB');
    $root->exec("DROP DATABASE IF EXISTS `$ticDb`");
    $root->exec("DROP DATABASE IF EXISTS `$sigeDb`");
}
