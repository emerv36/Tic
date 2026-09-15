<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../Services/PersonalCatalogAuthorization.php';
require_once __DIR__ . '/../Config/personal_ui_config.php';
require_once __DIR__ . '/../Config/PDOconn.php';

if (!TIC_SIGE_PERSONAL_UI_ENABLED) {
    http_response_code(404);
    echo json_encode(array('status' => 'ERROR', 'message' => 'UI no habilitada'));
    exit;
}

try {
    $authorization = new PersonalCatalogAuthorization();
    $actor = $authorization->autorizar($_SESSION);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array('status' => 'ERROR', 'message' => 'No autorizado.'));
    exit;
}

$case = isset($_GET['case']) ? $_GET['case'] : '';

switch ($case) {
    case 'listar':
        if (!isset($_POST['csrf']) || !hash_equals(PersonalCatalogAuthorization::asegurarToken($_SESSION), $_POST['csrf'])) {
            http_response_code(403);
            echo json_encode(array('status' => 'ERROR', 'message' => 'Token CSRF inválido.'));
            exit;
        }

        try {
            $pdo = new PDO(connstring, user, pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            // Filtramos a personas ACTIVAS y con foto (tiene_foto = 1) - Requisito mínimo de elegibilidad
            $stmt = $pdo->query(
                "SELECT * FROM sige_personal_proyeccion 
                 WHERE estado_persona = 'ACTIVA' AND tiene_foto = 1
                 ORDER BY apellidos ASC, nombres ASC"
            );
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(array("data" => $data));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array('status' => 'ERROR', 'message' => 'Error al listar: ' . $e->getMessage()));
        }
        break;

    case 'comando':
        $payloadRaw = file_get_contents('php://input');
        $payload = json_decode($payloadRaw, true);
        
        if (!isset($payload['csrf']) || !hash_equals(PersonalCatalogAuthorization::asegurarToken($_SESSION), $payload['csrf'])) {
            http_response_code(403);
            echo json_encode(array('status' => 'ERROR', 'message' => 'Token CSRF inválido.'));
            exit;
        }
        
        $tipo = $payload['tipo'] ?? '';
        if (!in_array($tipo, ['ASIGNACION', 'REEMPLAZO', 'BLOQUEO', 'ENTREGA_CONFIRMADA', 'PRORROGA', 'VINCULO_CREAR'])) {
            http_response_code(400);
            echo json_encode(array('status' => 'ERROR', 'message' => 'Tipo de operación inválido.'));
            exit;
        }

        $idOperacion = self_uuidV4();
        
        // Formatear fecha Bogota
        $dt = new DateTime("now", new DateTimeZone('America/Bogota'));
        $ocurridoEn = $dt->format('Y-m-d H:i:s');
        
        $comandoData = array(
            'contract_version' => '1.0',
            'id_operacion' => $idOperacion,
            'tipo' => $tipo,
            'persona_uuid' => $payload['persona_uuid'],
            'expected_persona_version' => (int) $payload['expected_persona_version'],
            'ocurrido_en' => $ocurridoEn,
            'usuario' => array(
                'id' => (string) $actor['id'],
                'nombre' => $actor['nombre'],
                'rol' => $actor['rol'] // CARNETIZACION
            )
        );

        if (in_array($tipo, ['ASIGNACION', 'REEMPLAZO'])) {
            $comandoData['uid_rfid'] = $payload['uid_rfid'] ?? null;
        } else {
            $comandoData['uid_rfid'] = null;
        }

        if (in_array($tipo, ['REEMPLAZO', 'BLOQUEO'])) {
            $comandoData['motivo'] = $payload['motivo'] ?? null;
        } else {
            $comandoData['motivo'] = null;
        }

        if (isset($payload['vigencia_hasta']) && !empty($payload['vigencia_hasta'])) {
            $comandoData['vigencia_hasta'] = trim((string) $payload['vigencia_hasta']);
        }

        if ($tipo === 'VINCULO_CREAR' && isset($payload['vinculo']) && is_array($payload['vinculo'])) {
            $comandoData['vinculo'] = $payload['vinculo'];
        }
        
        $payloadJson = json_encode($comandoData);
        $payloadHash = hash('sha256', $payloadJson);
        
        try {
            $pdo = new PDO(connstring, user, pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("SET time_zone = '-05:00'");
            
            $pdo->beginTransaction();
            
            // Registrar el comando pendiente. SIGE es la única autoridad que
            // muta el carné; la proyección TIC se actualizará por webhook.
            $stmt = $pdo->prepare(
                "INSERT INTO sige_personal_outbox (id_operacion, comando, tipo, persona_uuid, payload, estado, proximo_intento_en, intentos, creado_en, actualizado_en)
                 VALUES (:id, :cmd, :tipo, :uuid, :payload, 'PENDIENTE', NOW(), 0, NOW(), NOW())"
            );
            $stmt->execute(array(
                ':id' => $idOperacion,
                ':cmd' => 'CARNET',
                ':tipo' => $tipo,
                ':uuid' => $payload['persona_uuid'],
                ':payload' => $payloadJson
            ));
            
            $pdo->commit();
            
            echo json_encode(array(
                'status' => 'OK',
                'id_operacion' => $idOperacion
            ));
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error procesando CARNET: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(array('status' => 'ERROR', 'message' => 'No se pudo guardar la operación en base de datos: ' . $e->getMessage()));
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode(array('status' => 'ERROR', 'message' => 'Acción no encontrada'));
        break;
}

function self_uuidV4() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
