<?php

session_start();
date_default_timezone_set('America/Bogota');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../Services/PersonalCatalogApi.php';
require_once __DIR__ . '/../Config/personal_catalog_config.php';

if (!TIC_SIGE_PERSONAL_CATALOGS_ENABLED) {
    http_response_code(404);
    echo json_encode(array('success' => false, 'status' => 'NO_DISPONIBLE'));
    return;
}

try {
    $api = new PersonalCatalogApi(
        new PersonalCatalogAuthorization(),
        new PersonalCatalogService()
    );
    $request = $_SERVER['REQUEST_METHOD'] === 'GET' ? $_GET : $_POST;
    $result = $api->handle($_GET['case'] ?? '', $_SERVER['REQUEST_METHOD'] ?? '', $request, $_SESSION);
    http_response_code($result['http']);
    echo json_encode($result['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    error_log('personalCatalogController: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'status' => 'ERROR_INTERNO',
        'mensaje' => 'No fue posible procesar la operación.'
    ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
