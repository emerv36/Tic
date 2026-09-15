<?php

date_default_timezone_set('America/Bogota');
require_once dirname(__DIR__) . '/Services/PersonalCatalogService.php';

function assertSameCatalog($expected, $actual, $label) {
    if ($expected !== $actual) {
        throw new RuntimeException($label . ': esperado ' . var_export($expected, true) . ', recibido ' . var_export($actual, true));
    }
}

function assertThrowsCatalog($class, callable $callback, $label) {
    try {
        $callback();
    } catch (Throwable $exception) {
        if ($exception instanceof $class) return;
        throw new RuntimeException($label . ': excepción inesperada ' . get_class($exception) . ': ' . $exception->getMessage());
    }
    throw new RuntimeException($label . ': no lanzó excepción.');
}

class PersonalCatalogSqlitePdo extends PDO {
    public function __construct() {
        parent::__construct('sqlite::memory:');
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    public function prepare($query, $options = array()): PDOStatement|false {
        return parent::prepare(str_replace(' FOR UPDATE', '', $query), $options);
    }
}

$pdo = new PersonalCatalogSqlitePdo();
foreach (array('cargo', 'dependencia') as $catalog) {
    $pdo->exec(
        "CREATE TABLE sige_personal_{$catalog}_catalogo (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            nombre_normalizado TEXT NOT NULL UNIQUE,
            version INTEGER NOT NULL DEFAULT 1,
            estado TEXT NOT NULL DEFAULT 'ACTIVO',
            creado_por_usuario_id INTEGER NOT NULL,
            actualizado_por_usuario_id INTEGER NOT NULL,
            creado_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )"
    );
}
$pdo->exec(
    'CREATE TABLE sige_personal_catalogo_auditoria (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        id_operacion TEXT NOT NULL UNIQUE,
        catalogo TEXT NOT NULL,
        catalogo_id INTEGER NOT NULL,
        accion TEXT NOT NULL,
        version_anterior INTEGER NULL,
        version_nueva INTEGER NOT NULL,
        datos_antes TEXT NULL,
        datos_despues TEXT NOT NULL,
        actor_usuario_id INTEGER NOT NULL,
        actor_nombre TEXT NOT NULL,
        actor_rol TEXT NOT NULL,
        ocurrido_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);

$service = new PersonalCatalogService($pdo);
$actor = array('id' => 42, 'nombre' => 'Auxiliar TIC', 'rol' => 'CARNETIZACION');

assertSameCatalog('COORDINADOR ACADÉMICO', PersonalCatalogService::normalizarNombre("  Coordinador   académico "), 'Normalización única');

$cargo = $service->crear('CARGO', 'Coordinador Académico', $actor, '123e4567-e89b-42d3-a456-426614174301');
assertSameCatalog(1, $cargo['version'], 'Versión inicial cargo');
assertSameCatalog('ACTIVO', $cargo['estado'], 'Estado inicial cargo');
assertSameCatalog('COORDINADOR ACADÉMICO', $cargo['nombre_normalizado'], 'Nombre normalizado persistido');

assertThrowsCatalog(PersonalCatalogDuplicateException::class, function () use ($service, $actor) {
    $service->crear('CARGO', " coordinador   académico ", $actor, '123e4567-e89b-42d3-a456-426614174302');
}, 'Unicidad normalizada');

$actualizado = $service->actualizar(
    'CARGO', $cargo['id'], 'Coordinador de Formación', 'ACTIVO', 1, $actor,
    '123e4567-e89b-42d3-a456-426614174303'
);
assertSameCatalog(2, $actualizado['version'], 'Actualización incrementa versión');
assertSameCatalog('Coordinador de Formación', $actualizado['nombre'], 'Actualización de nombre');

$sinCambios = $service->actualizar(
    'CARGO', $cargo['id'], 'Coordinador de Formación', 'ACTIVO', 2, $actor,
    '123e4567-e89b-42d3-a456-426614174304'
);
assertSameCatalog(2, $sinCambios['version'], 'No-op conserva versión');

assertThrowsCatalog(PersonalCatalogConflictException::class, function () use ($service, $cargo, $actor) {
    $service->actualizar(
        'CARGO', $cargo['id'], 'Nombre obsoleto', 'ACTIVO', 1, $actor,
        '123e4567-e89b-42d3-a456-426614174305'
    );
}, 'Versión obsoleta');

$inactivo = $service->actualizar(
    'CARGO', $cargo['id'], 'Coordinador de Formación', 'INACTIVO', 2, $actor,
    '123e4567-e89b-42d3-a456-426614174306'
);
assertSameCatalog(3, $inactivo['version'], 'Inactivación incrementa versión');
assertSameCatalog('INACTIVO', $inactivo['estado'], 'Inactivación lógica');

// El UPDATE debe revertirse si la auditoría falla por UUID de operación repetido.
$pdo->exec("UPDATE sige_personal_cargo_catalogo SET actualizado_por_usuario_id = 999, actualizado_en = '2000-01-01 00:00:00' WHERE id = " . (int) $cargo['id']);
$antesDelRollback = $service->listar('CARGO', true)[0];
assertThrowsCatalog(PersonalCatalogOperationConflictException::class, function () use ($service, $cargo, $actor) {
    $service->actualizar(
        'CARGO', $cargo['id'], 'Nombre que debe revertirse', 'ACTIVO', 3, $actor,
        '123e4567-e89b-42d3-a456-426614174301'
    );
}, 'UUID de auditoría repetido');
$despuesDelRollback = $service->listar('CARGO', true)[0];
assertSameCatalog(3, (int) $despuesDelRollback['version'], 'Rollback conserva versión');
assertSameCatalog('INACTIVO', $despuesDelRollback['estado'], 'Rollback conserva estado');
assertSameCatalog($antesDelRollback['nombre'], $despuesDelRollback['nombre'], 'Rollback conserva nombre');
assertSameCatalog('2000-01-01 00:00:00', $despuesDelRollback['actualizado_en'], 'Rollback conserva timestamp centinela');
$filaRollback = $pdo->query('SELECT nombre_normalizado, actualizado_por_usuario_id FROM sige_personal_cargo_catalogo WHERE id = ' . (int) $cargo['id'])->fetch();
assertSameCatalog('COORDINADOR DE FORMACIÓN', $filaRollback['nombre_normalizado'], 'Rollback conserva nombre normalizado');
assertSameCatalog(999, (int) $filaRollback['actualizado_por_usuario_id'], 'Rollback conserva usuario actualizador');
assertSameCatalog(3, (int) $pdo->query('SELECT COUNT(*) FROM sige_personal_catalogo_auditoria')->fetchColumn(), 'Rollback no deja auditoría parcial');

$reactivado = $service->actualizar(
    'CARGO', $cargo['id'], 'Coordinador de Formación', 'ACTIVO', 3, $actor,
    '123e4567-e89b-42d3-a456-426614174308'
);
assertSameCatalog(4, $reactivado['version'], 'Reactivación incrementa versión');
assertSameCatalog('ACTIVO', $reactivado['estado'], 'Reactivación lógica');

$dependencia = $service->crear('DEPENDENCIA', 'Bienestar Institucional', $actor, '123e4567-e89b-42d3-a456-426614174307');
assertSameCatalog(1, $dependencia['version'], 'Catálogo dependencia independiente');
assertSameCatalog(1, count($service->listar('DEPENDENCIA', false)), 'Lista activos dependencia');
assertSameCatalog(1, count($service->listar('CARGO', false)), 'Lista incluye cargo reactivado');
assertSameCatalog(1, count($service->listar('CARGO', true)), 'Lista incluye cargo inactivo');

$cargoSnapshot = $service->obtenerSnapshotActivo('CARGO', $cargo['id']);
assertSameCatalog(
    array('id' => $cargo['id'], 'nombre_snapshot' => 'Coordinador de Formación', 'version' => 4),
    $cargoSnapshot,
    'Snapshot mínimo de cargo'
);
$dependencySnapshot = $service->obtenerSnapshotActivo('DEPENDENCIA', $dependencia['id']);
assertSameCatalog(
    array('id' => $dependencia['id'], 'nombre_snapshot' => 'Bienestar Institucional', 'version' => 1),
    $dependencySnapshot,
    'Snapshot mínimo de dependencia'
);
$service->actualizar(
    'DEPENDENCIA', $dependencia['id'], 'Bienestar Institucional', 'INACTIVO', 1, $actor,
    '123e4567-e89b-42d3-a456-426614174309'
);
assertThrowsCatalog(PersonalCatalogValidationException::class, function () use ($service, $dependencia) {
    $service->obtenerSnapshotActivo('DEPENDENCIA', $dependencia['id']);
}, 'Snapshot inactivo prohibido');

assertSameCatalog(6, (int) $pdo->query('SELECT COUNT(*) FROM sige_personal_catalogo_auditoria')->fetchColumn(), 'Auditoría solo de mutaciones confirmadas');
$actions = $pdo->query('SELECT accion FROM sige_personal_catalogo_auditoria ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
assertSameCatalog(array('CREAR', 'ACTUALIZAR', 'INACTIVAR', 'REACTIVAR', 'CREAR', 'INACTIVAR'), $actions, 'Acciones auditadas');

assertThrowsCatalog(PersonalCatalogValidationException::class, function () use ($service) {
    $service->crear('DEPENDENCIA', 'Tesorería', array('id' => 7, 'nombre' => 'Programador', 'rol' => 'PROGRAMADOR'));
}, 'Rol distinto de CARNETIZACION');
assertThrowsCatalog(PersonalCatalogValidationException::class, function () use ($service, $actor) {
    $service->crear('SEDE', 'Norte', $actor);
}, 'Catálogo sede prohibido');

echo "personal_catalog_service_test: OK\n";
