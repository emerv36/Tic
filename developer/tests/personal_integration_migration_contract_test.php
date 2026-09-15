<?php

$path = dirname(__DIR__) . '/migrations/20260831_tic_sige_personal_core.sql';
$sql = file_get_contents($path);
if ($sql === false) throw new RuntimeException('No fue posible leer la migración de integración personal.');

function assertPersonalMigration($condition, $message) {
    if ($condition !== true) throw new RuntimeException($message);
}

$expectedTables = [
    'sige_personal_outbox',
    'sige_personal_inbox',
    'sige_personal_proyeccion',
    'sige_personal_conflictos',
    'sige_personal_auditoria'
];

foreach ($expectedTables as $table) {
    assertPersonalMigration(
        preg_match('/CREATE TABLE IF NOT EXISTS\s+' . preg_quote($table, '/') . '\s*\(/i', $sql) === 1,
        'Falta la tabla dedicada ' . $table . '.'
    );
}

assertPersonalMigration(substr_count($sql, 'persona_uuid CHAR(36)') === 5, 'Todas las tablas deben referenciar persona_uuid.');
assertPersonalMigration(substr_count($sql, 'version_estado INT UNSIGNED NOT NULL') >= 2, 'Las tablas de inbox y proyección deben requerir version_estado.');
assertPersonalMigration(stripos($sql, 'UNIQUE KEY uq_sige_personal_inbox_persona_version (persona_uuid, version_estado)') !== false, 'El inbox debe impedir dos eventos para la misma persona-versión.');
assertPersonalMigration(substr_count($sql, 'id_operacion CHAR(36)') >= 3, 'Las tablas relacionadas a órdenes deben requerir id_operacion.');

// Proyecciones y JSONs
assertPersonalMigration(stripos($sql, 'vinculos_json LONGTEXT NOT NULL') !== false, 'La proyección debe contener el resumen de vínculos en JSON.');
assertPersonalMigration(stripos($sql, 'estado_persona VARCHAR(20) NOT NULL') !== false, 'La proyección debe usar estado_persona para evitar ambigüedad con el estado académico.');

// Auditoría y conflictos
assertPersonalMigration(stripos($sql, 'actor_origen VARCHAR(20) NOT NULL') !== false, 'La auditoría debe registrar actor_origen (TIC, SIGE, SISTEMA).');
assertPersonalMigration(stripos($sql, 'actor_nombre VARCHAR(150) NULL') !== false, 'La auditoría debe capturar actor_nombre como snapshot.');
assertPersonalMigration(stripos($sql, 'resuelto_por_usuario_nombre VARCHAR(150) NULL') !== false, 'Los conflictos deben capturar nombre del usuario que resuelve como snapshot.');

$forbiddenWords = ['id_sede', 'sede_id', 'id_programa', 'programa_id', 'id_inscripcion', 'inscripcion_id'];
foreach ($forbiddenWords as $forbidden) {
    assertPersonalMigration(stripos($sql, $forbidden) === false, 'La migración contiene una dependencia prohibida de estudiantes/sedes: ' . $forbidden);
}

echo "personal_integration_migration_contract_test: OK\n";
