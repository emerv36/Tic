<?php

$path = dirname(__DIR__) . '/migrations/20260830_tic_sige_personal_catalogos.sql';
$sql = file_get_contents($path);
if ($sql === false) throw new RuntimeException('No fue posible leer la migración de catálogos.');

function assertCatalogMigration($condition, $message) {
    if ($condition !== true) throw new RuntimeException($message);
}

foreach (array(
    'sige_personal_cargo_catalogo',
    'sige_personal_dependencia_catalogo',
    'sige_personal_catalogo_auditoria'
) as $table) {
    assertCatalogMigration(
        preg_match('/CREATE TABLE IF NOT EXISTS\s+' . preg_quote($table, '/') . '\s*\(/i', $sql) === 1,
        'Falta la tabla dedicada ' . $table . '.'
    );
}

assertCatalogMigration(substr_count($sql, 'nombre_normalizado VARCHAR(190) NOT NULL') === 2, 'Ambos catálogos requieren nombre normalizado.');
assertCatalogMigration(substr_count($sql, 'version BIGINT UNSIGNED NOT NULL DEFAULT 1') === 2, 'Ambos catálogos requieren versión monotónica.');
assertCatalogMigration(substr_count($sql, "estado ENUM('ACTIVO', 'INACTIVO') NOT NULL DEFAULT 'ACTIVO'") === 2, 'Ambos catálogos requieren estado lógico.');
assertCatalogMigration(substr_count($sql, 'UNIQUE KEY uq_sige_personal_') >= 3, 'Faltan restricciones únicas.');
assertCatalogMigration(stripos($sql, 'FOREIGN KEY') === false, 'La auditoría polimórfica no debe declarar una FK ambigua.');

foreach (array('id_sede', 'sede_id', 'id_programa', 'programa_id', 'docente_id', 'administrativo_id', 'persona_id') as $forbidden) {
    assertCatalogMigration(stripos($sql, $forbidden) === false, 'La migración contiene una dependencia prohibida: ' . $forbidden);
}

echo "catalog_migration_contract_test: OK\n";
