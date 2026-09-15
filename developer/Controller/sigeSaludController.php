<?php
date_default_timezone_set('America/Bogota');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once(__DIR__ . '/../Config/PDOconn.php');
require_once(__DIR__ . '/../Config/sige_config.php');

function responderSalud($codigo, array $body) {
    http_response_code($codigo);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    responderSalud(405, array('status' => 'ERROR', 'codigo' => 'METODO_NO_PERMITIDO'));
}
$authorization = '';
if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    $authorization = trim($_SERVER['HTTP_AUTHORIZATION']);
} elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authorization = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
} elseif (function_exists('getallheaders')) {
    foreach (getallheaders() as $nombre => $valor) {
        if (strcasecmp($nombre, 'Authorization') === 0) {
            $authorization = trim($valor);
            break;
        }
    }
}
$token = '';
if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
    $token = trim($matches[1]);
}
if ($token === '' || SIGE_INBOUND_API_KEY === '' || !hash_equals((string) SIGE_INBOUND_API_KEY, $token)) {
    responderSalud(401, array('status' => 'ERROR', 'codigo' => 'NO_AUTORIZADO'));
}
try {
    $pdo = new PDO(connstring, user, pass, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 3
    ));
    $pdo->query('SELECT 1')->fetchColumn();
    responderSalud(200, array(
        'status' => 'OK',
        'servicio' => 'TIC',
        'base_datos' => 'OK',
        'fecha' => date('Y-m-d H:i:s')
    ));
} catch (Throwable $ex) {
    error_log('sigeSaludController: ' . $ex->getMessage());
    responderSalud(503, array('status' => 'ERROR', 'codigo' => 'BASE_DATOS_NO_DISPONIBLE'));
}
