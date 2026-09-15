<?php
require_once(__DIR__ . '/Config/config.php');
require_once(__DIR__ . '/Config/PDOconn.php');

$pdo = new PDO("mysql:host=localhost;dbname=uybntujx_tic;charset=utf8", 'root', 'B.quilla54', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$pdo->exec("UPDATE sige_personal_outbox SET estado = IF(intentos >= 12, 'FALLIDA', 'PENDIENTE') WHERE estado = 'ENVIANDO'");
echo "=== NON-ENVIADA ROWS IN sige_personal_outbox AFTER CLEANUP ===\n";
$stmt = $pdo->query("SELECT id, persona_uuid, comando, estado, intentos, ultimo_error, proximo_intento_en, creado_en, actualizado_en FROM sige_personal_outbox WHERE estado != 'ENVIADA' ORDER BY id ASC");
print_r($stmt->fetchAll());
