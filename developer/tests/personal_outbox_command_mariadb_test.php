<?php
require_once dirname(__DIR__) . '/Config/PDOconn.php';

$pdo = new PDO(connstring, user, pass, array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
));
$pdo->exec("CREATE TEMPORARY TABLE sige_personal_outbox (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_operacion CHAR(36) NOT NULL,
    tipo VARCHAR(50) NOT NULL
) ENGINE=InnoDB");
$pdo->exec("INSERT INTO sige_personal_outbox (id_operacion,tipo) VALUES
    ('11111111-1111-4111-8111-111111111111','ASIGNACION'),
    ('22222222-2222-4222-8222-222222222222','PERSONA_CREAR')");

$sql = file_get_contents(dirname(__DIR__) . '/migrations/20260901_tic_sige_personal_outbox_command.sql');
$sql = preg_replace('/^\s*--.*$/m', '', (string)$sql);
$statements = array_filter(array_map('trim', explode(';', $sql)));
for ($run = 1; $run <= 2; $run++) {
    foreach ($statements as $statement) $pdo->exec($statement);
}

$rows = $pdo->query('SELECT tipo,comando FROM sige_personal_outbox ORDER BY id')->fetchAll();
if (($rows[0]['comando'] ?? null) !== 'CARNET' || ($rows[1]['comando'] ?? null) !== 'PERSONA_CREAR') {
    throw new RuntimeException('El backfill de comando no preservó el ruteo histórico.');
}
$column = $pdo->query("SHOW COLUMNS FROM sige_personal_outbox LIKE 'comando'")->fetch();
if (!$column || strtoupper((string)$column['Null']) !== 'NO') {
    throw new RuntimeException('La columna comando no quedó obligatoria.');
}

echo "personal_outbox_command_mariadb_test: OK\n";
