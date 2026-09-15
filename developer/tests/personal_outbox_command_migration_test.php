<?php
$path = dirname(__DIR__) . '/migrations/20260901_tic_sige_personal_outbox_command.sql';
$sql = file_get_contents($path);

if ($sql === false) throw new RuntimeException('No se pudo leer la migración de comando.');
foreach (array(
    'ADD COLUMN IF NOT EXISTS comando',
    "THEN 'CARNET'",
    'MODIFY COLUMN comando VARCHAR(50) NOT NULL'
) as $required) {
    if (strpos($sql, $required) === false) {
        throw new RuntimeException('Falta invariante de migración: ' . $required);
    }
}

echo "personal_outbox_command_migration_test: OK\n";
