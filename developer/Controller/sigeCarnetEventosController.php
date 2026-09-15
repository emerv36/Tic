<?php
date_default_timezone_set('America/Bogota');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once(__DIR__ . '/../Config/sige_config.php');
require_once(__DIR__ . '/../Models/SigeCarnetCompat.php');

function responderSigeCarnet($codigo, array $body) {
    http_response_code($codigo);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!defined('SIGE_INBOUND_API_KEY') || trim((string) SIGE_INBOUND_API_KEY) === '') {
    responderSigeCarnet(503, array(
        'success' => false,
        'codigo' => 'INTEGRACION_NO_CONFIGURADA'
    ));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    responderSigeCarnet(405, array('success' => false, 'codigo' => 'METODO_NO_PERMITIDO'));
}

$contentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower($_SERVER['CONTENT_TYPE']) : '';
if (strpos($contentType, 'application/json') !== 0) {
    responderSigeCarnet(415, array('success' => false, 'codigo' => 'CONTENT_TYPE_INVALIDO'));
}

$contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
if ($contentLength > 262144) {
    responderSigeCarnet(413, array('success' => false, 'codigo' => 'PAYLOAD_DEMASIADO_GRANDE'));
}

$headers = function_exists('getallheaders') ? getallheaders() : array();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
$token = '';
if (preg_match('/^Bearer\s+(.+)$/i', $auth, $matches)) {
    $token = trim($matches[1]);
}
if ($token === '' || !hash_equals((string) SIGE_INBOUND_API_KEY, $token)) {
    responderSigeCarnet(401, array('success' => false, 'codigo' => 'NO_AUTORIZADO'));
}

$raw = file_get_contents('php://input');
if ($raw === false || strlen($raw) > 262144) {
    responderSigeCarnet(413, array('success' => false, 'codigo' => 'PAYLOAD_DEMASIADO_GRANDE'));
}
$body = json_decode($raw, true);
if (!is_array($body) || json_last_error() !== JSON_ERROR_NONE) {
    responderSigeCarnet(400, array('success' => false, 'codigo' => 'JSON_INVALIDO'));
}

try {
    $modelo = new SigeCarnetCompat();
    $resultado = $modelo->registrarEventoYProyectar($body);
    responderSigeCarnet($resultado['duplicado'] ? 200 : 201, array(
        'success' => true,
        'status' => $resultado['status'],
        'duplicado' => $resultado['duplicado'],
        'id_evento' => $resultado['id_evento'],
        'id_inscripcion' => $resultado['id_inscripcion'] ?? null,
        'version_estado' => $resultado['version_estado']
    ));
} catch (SigeEventoDuplicadoConflictivo $ex) {
    responderSigeCarnet(409, array(
        'success' => false,
        'codigo' => 'ID_EVENTO_CONFLICTIVO',
        'mensaje' => $ex->getMessage()
    ));
} catch (SigeEventoInvalido $ex) {
    responderSigeCarnet(422, array(
        'success' => false,
        'codigo' => 'EVENTO_INVALIDO',
        'mensaje' => $ex->getMessage()
    ));
} catch (Throwable $ex) {
    error_log('SIGE evento carnet: ' . $ex->getMessage());
    responderSigeCarnet(503, array(
        'success' => false,
        'codigo' => 'ERROR_TRANSITORIO'
    ));
}
