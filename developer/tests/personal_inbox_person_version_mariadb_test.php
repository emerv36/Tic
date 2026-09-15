<?php
require_once dirname(__DIR__) . '/Config/PDOconn.php';

$pdo = new PDO(connstring, user, pass, array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
));
$pdo->exec("CREATE TEMPORARY TABLE sige_personal_inbox (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_evento CHAR(36) NOT NULL,
    persona_uuid CHAR(36) NOT NULL,
    version_estado INT UNSIGNED NOT NULL
) ENGINE=InnoDB");
$pdo->exec("INSERT INTO sige_personal_inbox (id_evento,persona_uuid,version_estado) VALUES
    ('11111111-1111-4111-8111-111111111111','aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',1)");

$sql = file_get_contents(dirname(__DIR__) . '/migrations/20260901_tic_sige_personal_inbox_persona_version.sql');
$sql = preg_replace('/^\s*--.*$/m', '', (string)$sql);
$statements = array_filter(array_map('trim', explode(';', $sql)));
for ($run = 1; $run <= 2; $run++) {
    foreach ($statements as $statement) $pdo->exec($statement);
}

$index = $pdo->query("SHOW INDEX FROM sige_personal_inbox WHERE Key_name='uq_sige_personal_inbox_persona_version'")->fetchAll();
if (count($index) !== 2) throw new RuntimeException('No se creó la unicidad compuesta persona-versión.');

$rechazado = false;
try {
    $pdo->exec("INSERT INTO sige_personal_inbox (id_evento,persona_uuid,version_estado) VALUES
        ('22222222-2222-4222-8222-222222222222','aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',1)");
} catch (PDOException $e) {
    $rechazado = (string)$e->getCode() === '23000';
}
if (!$rechazado) throw new RuntimeException('La base aceptó dos eventos para la misma persona-versión.');

$pdo->exec('DROP TEMPORARY TABLE sige_personal_inbox');
$pdo->exec("CREATE TEMPORARY TABLE sige_personal_inbox (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_evento CHAR(36) NOT NULL,
    persona_uuid CHAR(36) NOT NULL,
    version_estado INT UNSIGNED NOT NULL
) ENGINE=InnoDB");
$pdo->exec("INSERT INTO sige_personal_inbox (id_evento,persona_uuid,version_estado) VALUES
    ('33333333-3333-4333-8333-333333333333','bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',1),
    ('44444444-4444-4444-8444-444444444444','bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',1)");
$falloSeguro = false;
try {
    foreach ($statements as $statement) $pdo->exec($statement);
} catch (PDOException $e) {
    $falloSeguro = (string)$e->getCode() === '23000';
}
if (!$falloSeguro) throw new RuntimeException('La migración no se detuvo ante duplicados históricos.');

echo "personal_inbox_person_version_mariadb_test: OK\n";
