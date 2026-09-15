<?php

require_once dirname(__DIR__) . '/Services/PersonalCatalogAuthorization.php';

function assertSameAuthorization($expected, $actual, $label) {
    if ($expected !== $actual) throw new RuntimeException($label . ': valor inesperado.');
}
function assertThrowsAuthorization($class, callable $callback, $label) {
    try { $callback(); } catch (Throwable $exception) {
        if ($exception instanceof $class) return;
        throw new RuntimeException($label . ': recibió ' . get_class($exception));
    }
    throw new RuntimeException($label . ': no lanzó excepción.');
}

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->exec('CREATE TABLE roles (codigo_rol INTEGER PRIMARY KEY, nombre_rol TEXT, estado_rol TEXT)');
$pdo->exec('CREATE TABLE usuperfil (codigo_perfil INTEGER PRIMARY KEY, codrol_fk INTEGER, estado_perfil TEXT)');
$pdo->exec('CREATE TABLE usuario (codigo_usu INTEGER PRIMARY KEY, nombres_usuario TEXT, codperfil_fk INTEGER, estado TEXT)');
$pdo->exec("INSERT INTO roles VALUES (1, 'CARNETIZACION', 'on'), (2, 'PROGRAMADOR', 'on'), (3, 'OPERATIVO', 'on'), (4, 'CARNETIZACION', 'off')");
$pdo->exec("INSERT INTO usuperfil VALUES (11, 1, 'on'), (12, 2, 'on'), (13, 3, 'on'), (14, 4, 'on'), (15, 1, 'off')");
$pdo->exec("INSERT INTO usuario VALUES (101, 'Auxiliar Uno', 11, 'on'), (102, 'Programador', 12, 'on'), (103, 'Operativo', 13, 'on'), (104, 'Rol Inactivo', 14, 'on'), (105, 'Usuario Inactivo', 11, 'off'), (106, 'Perfil Inactivo', 15, 'on')");

$authorization = new PersonalCatalogAuthorization($pdo);
$session = array(
    'SiigaBv' => true, 'IN_codigo_usuCA' => 101, 'IN_codperfil' => 11,
    'IN_codrol' => 1, 'IN_nombre_rol' => 'PROGRAMADOR'
);
$token = PersonalCatalogAuthorization::asegurarToken($session);
assertSameAuthorization(64, strlen($token), 'Token de 256 bits');
$actor = $authorization->autorizar($session, $token, true);
assertSameAuthorization('CARNETIZACION', $actor['rol'], 'Rol proviene de base, no de nombre en sesión');
assertSameAuthorization(101, $actor['id'], 'Actor correlacionado');

assertThrowsAuthorization(PersonalCatalogCsrfException::class, function () use ($authorization, $session) {
    $authorization->autorizar($session, 'incorrecto', true);
}, 'CSRF incorrecto');
assertThrowsAuthorization(PersonalCatalogUnauthorizedException::class, function () use ($authorization) {
    $authorization->autorizar(array());
}, 'Sin sesión');
assertThrowsAuthorization(PersonalCatalogForbiddenException::class, function () use ($authorization, $session) {
    $changed = $session; $changed['IN_codrol'] = 2; $changed['IN_codperfil'] = 12; $changed['IN_codigo_usuCA'] = 102;
    $authorization->autorizar($changed);
}, 'PROGRAMADOR prohibido');
assertThrowsAuthorization(PersonalCatalogForbiddenException::class, function () use ($authorization, $session) {
    $changed = $session; $changed['IN_codrol'] = 3; $changed['IN_codperfil'] = 13; $changed['IN_codigo_usuCA'] = 103;
    $authorization->autorizar($changed);
}, 'OPERATIVO prohibido');
assertThrowsAuthorization(PersonalCatalogForbiddenException::class, function () use ($authorization, $session) {
    $changed = $session; $changed['IN_codrol'] = 4; $changed['IN_codperfil'] = 14; $changed['IN_codigo_usuCA'] = 104;
    $authorization->autorizar($changed);
}, 'Rol CARNETIZACION inactivo');
assertThrowsAuthorization(PersonalCatalogForbiddenException::class, function () use ($authorization, $session) {
    $changed = $session; $changed['IN_codigo_usuCA'] = 105;
    $authorization->autorizar($changed);
}, 'Usuario inactivo');
assertThrowsAuthorization(PersonalCatalogForbiddenException::class, function () use ($authorization, $session) {
    $changed = $session; $changed['IN_codigo_usuCA'] = 106; $changed['IN_codperfil'] = 15;
    $authorization->autorizar($changed);
}, 'Perfil inactivo');
assertThrowsAuthorization(PersonalCatalogForbiddenException::class, function () use ($authorization, $session) {
    $changed = $session; $changed['IN_codrol'] = 2;
    $authorization->autorizar($changed);
}, 'Sesión alterada no coincide con perfil real');

$sameToken = PersonalCatalogAuthorization::asegurarToken($session);
assertSameAuthorization($token, $sameToken, 'Token estable durante la sesión');
$weakSession = array('PERSONAL_CATALOG_CSRF' => 'token-corto');
$regeneratedToken = PersonalCatalogAuthorization::asegurarToken($weakSession);
assertSameAuthorization(64, strlen($regeneratedToken), 'Token débil regenerado');
assertSameAuthorization(1, preg_match('/^[0-9a-f]{64}$/', $regeneratedToken), 'Token regenerado hexadecimal');
$malformedSession = array('PERSONAL_CATALOG_CSRF' => str_repeat('z', 64));
$regeneratedMalformed = PersonalCatalogAuthorization::asegurarToken($malformedSession);
assertSameAuthorization(1, preg_match('/^[0-9a-f]{64}$/', $regeneratedMalformed), 'Token no hexadecimal regenerado');

echo "personal_catalog_authorization_test: OK\n";
