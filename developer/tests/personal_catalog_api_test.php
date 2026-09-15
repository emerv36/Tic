<?php

require_once dirname(__DIR__) . '/Services/PersonalCatalogApi.php';

function assertSameCatalogApi($expected, $actual, $label) {
    if ($expected !== $actual) throw new RuntimeException($label . ': valor inesperado.');
}

class PersonalCatalogApiSqlitePdo extends PDO {
    public function __construct() {
        parent::__construct('sqlite::memory:');
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    public function prepare($query, $options = array()): PDOStatement|false {
        return parent::prepare(str_replace(' FOR UPDATE', '', $query), $options);
    }
}

$pdo = new PersonalCatalogApiSqlitePdo();
$pdo->exec('CREATE TABLE roles (codigo_rol INTEGER PRIMARY KEY, nombre_rol TEXT, estado_rol TEXT)');
$pdo->exec('CREATE TABLE usuperfil (codigo_perfil INTEGER PRIMARY KEY, codrol_fk INTEGER, estado_perfil TEXT)');
$pdo->exec('CREATE TABLE usuario (codigo_usu INTEGER PRIMARY KEY, nombres_usuario TEXT, codperfil_fk INTEGER, estado TEXT)');
$pdo->exec("INSERT INTO roles VALUES (1, 'CARNETIZACION', 'on'), (2, 'PROGRAMADOR', 'on')");
$pdo->exec("INSERT INTO usuperfil VALUES (11, 1, 'on'), (12, 2, 'on')");
$pdo->exec("INSERT INTO usuario VALUES (101, 'Auxiliar', 11, 'on'), (102, 'Programador', 12, 'on')");
foreach (array('cargo', 'dependencia') as $catalog) {
    $pdo->exec("CREATE TABLE sige_personal_{$catalog}_catalogo (
        id INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT NOT NULL,
        nombre_normalizado TEXT NOT NULL UNIQUE, version INTEGER NOT NULL,
        estado TEXT NOT NULL, creado_por_usuario_id INTEGER NOT NULL,
        actualizado_por_usuario_id INTEGER NOT NULL, creado_en TEXT NOT NULL,
        actualizado_en TEXT NOT NULL
    )");
}
$pdo->exec('CREATE TABLE sige_personal_catalogo_auditoria (
    id INTEGER PRIMARY KEY AUTOINCREMENT, id_operacion TEXT NOT NULL UNIQUE,
    catalogo TEXT, catalogo_id INTEGER, accion TEXT, version_anterior INTEGER,
    version_nueva INTEGER, datos_antes TEXT, datos_despues TEXT,
    actor_usuario_id INTEGER, actor_nombre TEXT, actor_rol TEXT, ocurrido_en TEXT
)');

$api = new PersonalCatalogApi(new PersonalCatalogAuthorization($pdo), new PersonalCatalogService($pdo));
$session = array(
    'SiigaBv' => true, 'IN_codigo_usuCA' => 101,
    'IN_codperfil' => 11, 'IN_codrol' => 1,
    'PERSONAL_CATALOG_CSRF' => str_repeat('a', 64)
);

$created = $api->handle('Crear', 'POST', array(
    'catalogo' => 'CARGO', 'nombre' => 'Coordinador', 'csrf_token' => str_repeat('a', 64)
), $session);
assertSameCatalogApi(201, $created['http'], 'HTTP creación');
assertSameCatalogApi(1, $created['body']['data']['version'], 'Versión creación');

$listed = $api->handle('Listar', 'GET', array('catalogo' => 'CARGO', 'incluir_inactivos' => '1'), $session);
assertSameCatalogApi(200, $listed['http'], 'HTTP listado');
assertSameCatalogApi(1, count($listed['body']['data']), 'Listado protegido');

$updated = $api->handle('Actualizar', 'POST', array(
    'catalogo' => 'CARGO', 'id' => $created['body']['data']['id'],
    'nombre' => 'Coordinador General', 'estado' => 'ACTIVO', 'expected_version' => 1,
    'csrf_token' => str_repeat('a', 64)
), $session);
assertSameCatalogApi(200, $updated['http'], 'HTTP actualización');
assertSameCatalogApi(2, $updated['body']['data']['version'], 'Versión actualización');

$withoutCsrf = $api->handle('Crear', 'POST', array('catalogo' => 'CARGO', 'nombre' => 'Sin CSRF'), $session);
assertSameCatalogApi(403, $withoutCsrf['http'], 'Mutación sin CSRF');
assertSameCatalogApi('CSRF_INVALIDO', $withoutCsrf['body']['status'], 'Código CSRF');

$unauthenticated = $api->handle('Listar', 'GET', array('catalogo' => 'CARGO'), array());
assertSameCatalogApi(401, $unauthenticated['http'], 'Lectura sin sesión');

$programmer = $session;
$programmer['IN_codigo_usuCA'] = 102; $programmer['IN_codperfil'] = 12; $programmer['IN_codrol'] = 2;
$forbidden = $api->handle('Listar', 'GET', array('catalogo' => 'CARGO'), $programmer);
assertSameCatalogApi(403, $forbidden['http'], 'PROGRAMADOR no lista');

$wrongMethod = $api->handle('Crear', 'GET', array(), $session);
assertSameCatalogApi(405, $wrongMethod['http'], 'Método incorrecto');
$unknown = $api->handle('Eliminar', 'POST', array(), $session);
assertSameCatalogApi(404, $unknown['http'], 'No existe eliminación física');
$invalidCatalog = $api->handle('Listar', 'GET', array('catalogo' => 'SEDE'), $session);
assertSameCatalogApi(422, $invalidCatalog['http'], 'SEDE rechazada');
$obsolete = $api->handle('Actualizar', 'POST', array(
    'catalogo' => 'CARGO', 'id' => $created['body']['data']['id'],
    'nombre' => 'Obsoleto', 'estado' => 'ACTIVO', 'expected_version' => 1,
    'csrf_token' => str_repeat('a', 64)
), $session);
assertSameCatalogApi(409, $obsolete['http'], 'Versión obsoleta');
assertSameCatalogApi('VERSION_OBSOLETA', $obsolete['body']['status'], 'Código de conflicto');

echo "personal_catalog_api_test: OK\n";
