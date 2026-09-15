<?php
/**
 * sigeAccesosController.php - Endpoint receptor de accesos físicos de SIGE
 * 
 * Endpoint: POST /api/v1/sige/accesos
 * 
 * Recibe logs de entrada/salida desde el sistema de torniquetes (SIGE)
 * cada 5 minutos. Valida autenticación, itera sobre el array data[]
 * y almacena cada registro en la base de datos de TIC.
 * 
 * Formato esperado del body:
 * {
 *   "data": [
 *     {
 *       "id": 150,
 *       "carnet_id": 45,
 *       "uid_leido": "1A2B3C",
 *       "fecha_hora": "2026-08-25 07:15:00",
 *       "tipo_movimiento": "ENTRADA",
 *       "resultado": "PERMITIDO",
 *       "motivo_rechazo": "NO_APLICA"
 *     }
 *   ]
 * }
 */

// No usar sesiones: este endpoint es una API pública autenticada por token
date_default_timezone_set("America/Bogota");
header('Content-Type: application/json; charset=utf-8');

require_once(__DIR__ . '/../Config/sige_config.php');
require_once(__DIR__ . '/../Models/SigeAccesos.php');

// ============================================================
// 1. VALIDAR MÉTODO HTTP
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array(
        "success" => false,
        "mensaje" => "Método no permitido. Solo se acepta POST."
    ));
    exit;
}

// ============================================================
// 2. VALIDAR AUTENTICACIÓN (Bearer Token)
// ============================================================
$headers = getallheaders();
$auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';

// Algunos servidores envían el header en minúsculas
if (empty($auth_header) && isset($headers['authorization'])) {
    $auth_header = $headers['authorization'];
}

// Extraer el token del header "Bearer {token}"
$token_recibido = '';
if (preg_match('/^Bearer\s+(.+)$/i', $auth_header, $matches)) {
    $token_recibido = trim($matches[1]);
}

if (!defined('SIGE_INBOUND_API_KEY') || SIGE_INBOUND_API_KEY === ''
    || $token_recibido === ''
    || !hash_equals(SIGE_INBOUND_API_KEY, $token_recibido)) {
    http_response_code(401);
    echo json_encode(array(
        "success" => false,
        "mensaje" => "No autorizado. Token inválido o ausente."
    ));
    exit;
}

// ============================================================
// 3. LEER Y VALIDAR EL BODY JSON
// ============================================================
$body_raw = file_get_contents('php://input');
$body = json_decode($body_raw, true);

if ($body === null || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "mensaje" => "JSON inválido en el cuerpo de la petición."
    ));
    exit;
}

// Validar que existe la llave "data" y que es un array
if (!isset($body['data']) || !is_array($body['data'])) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "mensaje" => "Se esperaba un array dentro de la llave 'data'."
    ));
    exit;
}

// ============================================================
// 4. ITERAR SOBRE EL ARRAY data[] Y GUARDAR CADA REGISTRO
// ============================================================
$modelo = new SigeAccesos();
$total_recibidos = count($body['data']);
$total_insertados = 0;
$total_duplicados = 0;
$errores = array();
$ids_aceptados = array();

foreach ($body['data'] as $indice => $registro) {
    // Validar campos obligatorios de cada registro
    $campos_requeridos = array('id', 'fecha_hora', 'tipo_movimiento', 'resultado');
    $campos_faltantes = array();

    foreach ($campos_requeridos as $campo) {
        if (!isset($registro[$campo]) || $registro[$campo] === '') {
            $campos_faltantes[] = $campo;
        }
    }

    if (!empty($campos_faltantes)) {
        $errores[] = array(
            "indice"  => $indice,
            "sige_id" => isset($registro['id']) ? $registro['id'] : null,
            "error"   => "Campos faltantes: " . implode(', ', $campos_faltantes)
        );
        continue;
    }

    // Verificar si ya existe este registro (evitar duplicados)
    if ($modelo->existeRegistro($registro['id'])) {
        $total_duplicados++;
        $ids_aceptados[] = (int) $registro['id'];
        continue;
    }

    // Validar tipo_movimiento
    $tipo_movimiento = strtoupper($registro['tipo_movimiento']);
    if (!in_array($tipo_movimiento, array('ENTRADA', 'SALIDA'))) {
        $errores[] = array(
            "indice"  => $indice,
            "sige_id" => $registro['id'],
            "error"   => "tipo_movimiento inválido: " . $registro['tipo_movimiento']
        );
        continue;
    }

    // Validar resultado
    $resultado_acceso = strtoupper($registro['resultado']);
    if (!in_array($resultado_acceso, array('PERMITIDO', 'DENEGADO'))) {
        $errores[] = array(
            "indice"  => $indice,
            "sige_id" => $registro['id'],
            "error"   => "resultado inválido: " . $registro['resultado']
        );
        continue;
    }

    // Motivo de rechazo (opcional, default NO_APLICA)
    $motivo_rechazo = isset($registro['motivo_rechazo']) && !empty($registro['motivo_rechazo']) 
        ? strtoupper($registro['motivo_rechazo']) 
        : 'NO_APLICA';

    // Insertar en la base de datos
    $insertado = $modelo->insertarAcceso(array(
        'sige_id' => (int) $registro['id'],
        'carnet_id' => isset($registro['carnet_id']) && $registro['carnet_id'] !== ''
            ? (int) $registro['carnet_id'] : null,
        'uid_leido' => isset($registro['uid_leido']) && trim((string) $registro['uid_leido']) !== ''
            ? trim((string) $registro['uid_leido']) : null,
        'fecha_hora' => $registro['fecha_hora'],
        'tipo_movimiento' => $tipo_movimiento,
        'resultado' => $resultado_acceso,
        'motivo_rechazo' => $motivo_rechazo,
        'origen' => strtoupper(trim((string) ($registro['origen'] ?? 'AUTOMATICO'))),
        'terminal_id' => $registro['terminal_id'] ?? null,
        'operacion_manual_id' => $registro['operacion_manual_id'] ?? null,
        'estudiante_id' => $registro['estudiante_id'] ?? null,
        'acceso_relacionado_id' => $registro['acceso_relacionado_id'] ?? null,
        'usuario_autorizador_id' => $registro['usuario_autorizador_id'] ?? null,
        'motivo_manual_codigo' => $registro['motivo_manual_codigo'] ?? null
    ));

    if ($insertado) {
        $total_insertados++;
        $ids_aceptados[] = (int) $registro['id'];
    } else {
        $errores[] = array(
            "indice"  => $indice,
            "sige_id" => $registro['id'],
            "error"   => "Error al insertar en base de datos"
        );
    }
}

// ============================================================
// 5. RETORNAR RESPUESTA
// ============================================================
$codigo_http = ($total_insertados > 0) ? 201 : 200;
http_response_code($codigo_http);

$respuesta = array(
    "success"          => true,
    "mensaje"          => "Procesamiento completado.",
    "total_recibidos"  => $total_recibidos,
    "total_insertados" => $total_insertados,
    "total_duplicados" => $total_duplicados,
    "total_errores"    => count($errores),
    "accepted_ids"     => array_values(array_unique($ids_aceptados))
);

// Solo incluir detalle de errores si los hay
if (!empty($errores)) {
    $respuesta['errores'] = $errores;
}

echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);

?>
