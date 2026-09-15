<?php
require dirname(__DIR__) . '/Config/PDOconn.php';
$suffix = bin2hex(random_bytes(5));$database = 'tic_outbox_archive_' . $suffix;
if (!preg_match('/^tic_outbox_archive_[0-9a-f]{10}$/', $database)) throw new RuntimeException('Nombre temporal inseguro.');
$root = new PDO('mysql:host=' . host . ';charset=utf8mb4', user, pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC));
function fixtureDigest(array $rows) {
    $manifest = array();
    foreach ($rows as $row) $manifest[] = implode('|', array($row['id'], $row['id_operacion'], $row['comando'], $row['tipo'], $row['persona_uuid'] ?? '', $row['estado'], hash('sha256', $row['payload'])));
    return hash('sha256', implode("\n", $manifest));
}
function runArchiveCommand($command, &$output = null) {
    $output = array();exec($command . ' 2>&1', $output, $exit);return $exit;
}
try {
    $root->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $root->exec("CREATE TABLE `$database`.sige_personal_outbox LIKE `" . dbname . "`.sige_personal_outbox");
    $root->exec("USE `$database`");
    $fixture = $root->prepare("INSERT INTO sige_personal_outbox(id,id_operacion,comando,tipo,persona_uuid,payload,estado,intentos,proximo_intento_en,creado_en,actualizado_en) VALUES(:id,:operation,:command,:type,:person,:payload,:status,0,NOW(),NOW(),NOW())");
    for ($id = 1; $id <= 30; $id++) {
        $personal = in_array($id, array(1, 26), true);$payload = json_encode(array('fixture_id' => $id));
        $fixture->execute(array('id'=>$id,'operation'=>sprintf('30000000-0000-4000-8000-%012d',$id),'command'=>$personal?'PERSONA_CREAR':'CARNET','type'=>$personal?'ADMINISTRATIVO':'ASIGNACION','person'=>$personal?null:'30000000-0000-4000-8000-000000000099','payload'=>$payload,'status'=>$personal?'FALLIDA':'EXITOSO'));
    }
    $fixture->execute(array('id'=>31,'operation'=>'30000000-0000-4000-8000-000000000031','command'=>'PERSONAL_SYNC','type'=>'ADMINISTRATIVO','person'=>'30000000-0000-4000-8000-000000000031','payload'=>'{"real":true}','status'=>'PENDIENTE'));
    $rows=$root->query('SELECT id,id_operacion,comando,tipo,persona_uuid,estado,payload FROM sige_personal_outbox WHERE id BETWEEN 1 AND 30 ORDER BY id')->fetchAll();
    putenv('TIC_PERSONAL_ARCHIVE_DB='.$database);putenv('TIC_PERSONAL_ARCHIVE_EXPECTED_DIGEST='.fixtureDigest($rows));
    $script=dirname(__DIR__,2).'/artifacts/archive_personal_simulation_outbox_20260901.php';$base=escapeshellarg(PHP_BINARY).' '.escapeshellarg($script);

    if(runArchiveCommand($base,$dry)!==0||strpos(implode("\n",$dry),'DRY_RUN')===false)throw new RuntimeException('El dry-run real falló.');
    if((int)$root->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$database' AND table_name='sige_personal_outbox_archivo'")->fetchColumn()!==0)throw new RuntimeException('Dry-run creó la tabla de archivo.');

    $root->exec("UPDATE sige_personal_outbox SET estado='PENDIENTE' WHERE id=2");
    if(runArchiveCommand($base,$badTerminal)===0)throw new RuntimeException('Aceptó una fila histórica no terminal.');
    $root->exec("UPDATE sige_personal_outbox SET estado='EXITOSO' WHERE id=2");
    $root->exec("UPDATE sige_personal_outbox SET payload='{}' WHERE id=3");
    if(runArchiveCommand($base,$badDigest)===0)throw new RuntimeException('Aceptó un manifiesto alterado.');
    $root->exec("UPDATE sige_personal_outbox SET payload='{" . '"fixture_id":3' . "}' WHERE id=3");

    $sql=file_get_contents(dirname(__DIR__).'/migrations/20260901_tic_sige_personal_outbox_archive.sql');$sql=preg_replace('/^\s*--.*$/m','',$sql);foreach(array_filter(array_map('trim',explode(';',$sql))) as $statement)$root->exec($statement);
    $root->exec("CREATE TRIGGER fail_archive BEFORE INSERT ON sige_personal_outbox_archivo FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fallo deliberado'");
    if(runArchiveCommand($base.' --apply',$failedApply)===0)throw new RuntimeException('No falló el apply con trigger.');
    if((int)$root->query('SELECT COUNT(*) FROM sige_personal_outbox')->fetchColumn()!==31||(int)$root->query('SELECT COUNT(*) FROM sige_personal_outbox_archivo')->fetchColumn()!==0)throw new RuntimeException('El fallo no revirtió archivo y retiro.');
    $root->exec('DROP TRIGGER fail_archive');

    if(runArchiveCommand($base.' --apply',$applied)!==0)throw new RuntimeException('Apply real falló: '.implode("\n",$applied));
    if((int)$root->query('SELECT COUNT(*) FROM sige_personal_outbox_archivo')->fetchColumn()!==30||(int)$root->query('SELECT COUNT(*) FROM sige_personal_outbox')->fetchColumn()!==1||(int)$root->query('SELECT COUNT(*) FROM sige_personal_outbox WHERE id=31')->fetchColumn()!==1)throw new RuntimeException('Apply no archivó 30 o alteró fila 31.');
    if(runArchiveCommand($base.' --apply',$replay)!==0||(int)$root->query('SELECT COUNT(*) FROM sige_personal_outbox_archivo')->fetchColumn()!==30)throw new RuntimeException('La reejecución no fue idempotente.');
    $source=file_get_contents($script);if(strpos($source,"PHP_SAPI !== 'cli'")===false)throw new RuntimeException('El artefacto no tiene guard CLI.');
    echo "personal_outbox_archive_mariadb_test: OK\n";
} finally {
    putenv('TIC_PERSONAL_ARCHIVE_DB');putenv('TIC_PERSONAL_ARCHIVE_EXPECTED_DIGEST');$root->exec("DROP DATABASE IF EXISTS `$database`");
}
