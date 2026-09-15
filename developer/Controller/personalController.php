<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../Services/PersonalCatalogAuthorization.php';
require_once __DIR__ . '/../Config/personal_ui_config.php';
require_once __DIR__ . '/../Config/PDOconn.php';
require_once __DIR__ . '/../Services/SigePersonalClient.php';
require_once __DIR__ . '/../Services/PersonalCatalogService.php';

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

$client = new SigePersonalClient();
$case = isset($_GET['case']) ? $_GET['case'] : '';

switch ($case) {
    case 'catalogos':
        $catalogService = new PersonalCatalogService();
        echo json_encode(array(
            'cargos' => array_values($catalogService->listar('CARGO', false)),
            'dependencias' => array_values($catalogService->listar('DEPENDENCIA', false))
        ));
        break;

    case 'buscarSige':
        if (!isset($_POST['csrf']) || !hash_equals(PersonalCatalogAuthorization::asegurarToken($_SESSION), $_POST['csrf'])) {
            http_response_code(403);
            echo json_encode(array('status' => 'ERROR', 'message' => 'Token CSRF inválido.'));
            exit;
        }
        $tipo = $_POST['tipo_documento'];
        $num = $_POST['numero_documento'];
        
        try {
            $persona = $client->consultarPersona($tipo, $num);
            if ($persona) {
                echo json_encode(array(
                    'existe' => true,
                    'persona' => $persona
                ));
            } else {
                echo json_encode(array(
                    'existe' => false
                ));
            }
        } catch (Exception $e) {
            http_response_code(502);
            echo json_encode(array('status' => 'ERROR', 'message' => 'Error al contactar SIGE: ' . $e->getMessage()));
        }
        break;
        
    case 'transferirFoto':
        error_log("INICIANDO TRANSFERIR FOTO");
        if (!isset($_POST['csrf']) || !hash_equals(PersonalCatalogAuthorization::asegurarToken($_SESSION), $_POST['csrf'])) {
            http_response_code(403);
            echo json_encode(array('status' => 'ERROR', 'message' => 'Token CSRF inválido.'));
            exit;
        }
        
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(array('status' => 'ERROR', 'message' => 'No se recibió un archivo válido.'));
            exit;
        }
        
        $tmpPath = $_FILES['foto']['tmp_name'];
        $mime = mime_content_type($tmpPath);
        $size = filesize($tmpPath);
        
        if ($size > 5 * 1024 * 1024) {
            echo json_encode(array('status' => 'ERROR', 'message' => 'La foto excede 5MB.'));
            exit;
        }
        
        if ($mime !== 'image/jpeg' && $mime !== 'image/png') {
            echo json_encode(array('status' => 'ERROR', 'message' => 'Formato no soportado. Debe ser JPEG o PNG.'));
            exit;
        }
        
        $sha256 = hash_file('sha256', $tmpPath);
        
        $personaUuid = $_POST['persona_uuid'] ?? '';
        $idOperacion = self_uuidV4();
        $expectedVersion = filter_var($_POST['expected_persona_version'] ?? null, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
        if ($expectedVersion === false) {
            http_response_code(422);
            echo json_encode(array('status' => 'ERROR', 'message' => 'La versión esperada de la persona es obligatoria.'));
            exit;
        }
        
        try {
            $ack = $client->transferirFoto($personaUuid, $idOperacion, (int)$expectedVersion, $tmpPath, $mime, $sha256, $actor);
            if (is_file($tmpPath)) unlink($tmpPath);
            
            echo json_encode(array(
                'status' => 'OK',
                'id_operacion' => $ack['id_operacion'],
                'persona_uuid' => $ack['persona_uuid'],
                'persona_version' => $ack['persona_version'],
                'metadata' => array(
                    'sha256' => $sha256,
                    'mime' => $mime,
                    'bytes' => $size
                )
            ));
        } catch (Exception $e) {
            if (is_file($tmpPath)) unlink($tmpPath);
            http_response_code(502);
            echo json_encode(array('status' => 'ERROR', 'message' => 'Error al transferir a SIGE: ' . $e->getMessage()));
        }
        break;
        
    case 'finalizar':
        $payloadRaw = file_get_contents('php://input');
        $payload = json_decode($payloadRaw, true);
        
        if (!isset($payload['csrf']) || !hash_equals(PersonalCatalogAuthorization::asegurarToken($_SESSION), $payload['csrf'])) {
            http_response_code(403);
            echo json_encode(array('status' => 'ERROR', 'message' => 'Token CSRF inválido.'));
            exit;
        }
        
        $identidad = $payload['identidad'];
        $vinculoUi = $payload['vinculo'];

        // Para una identidad existente no se confía en campos deshabilitados
        // del navegador: se recupera nuevamente el snapshot autoritativo SIGE.
        if (empty($identidad['es_nuevo'])) {
            try {
                $snapshotSige = $client->consultarPersona($identidad['tipo_documento'] ?? '', $identidad['numero_documento'] ?? '');
            } catch (Exception $e) {
                http_response_code(502);
                echo json_encode(array('status' => 'ERROR', 'message' => 'No fue posible confirmar la identidad vigente en SIGE.'));
                exit;
            }
            if (!$snapshotSige) {
                http_response_code(409);
                echo json_encode(array('status' => 'ERROR', 'message' => 'La persona dejó de existir en SIGE; reinicie la búsqueda.'));
                exit;
            }
            foreach (array('persona_uuid','persona_version','tipo_documento','numero_documento','nombres','apellidos','correo','celular','rh') as $campo) {
                $identidad[$campo] = $snapshotSige[$campo] ?? null;
            }
        }
        
        $idOperacion = self_uuidV4();
        
        // El endpoint compacto de SIGE orquesta persona -> vínculo -> foto con
        // una sola operación idempotente. Para una identidad ya existente,
        // SIGE decide si el vínculo se crea o se actualiza bajo versión real.
        $comando = !empty($identidad['es_nuevo']) ? 'PERSONA_CREAR' : 'VINCULO_ACTUALIZAR';
        $comandoTransporte = 'PERSONAL_SYNC';
        
        try {
            $catalogService = new PersonalCatalogService();
            $cargoSnapshot = null;
            $dependenciaSnapshot = null;
            if (($vinculoUi['tipo'] ?? '') === 'ADMINISTRATIVO') {
                $cargoSnapshot = $catalogService->obtenerSnapshotActivo('CARGO', $payload['cargo_id'] ?? null);
                $dependenciaSnapshot = $catalogService->obtenerSnapshotActivo('DEPENDENCIA', $payload['dependencia_id'] ?? null);
            }
        } catch (PersonalCatalogValidationException $e) {
            http_response_code(422);
            echo json_encode(array('status' => 'ERROR', 'message' => $e->getMessage()));
            exit;
        }

        // El adaptador compacto recibe tanto los IDs como sus snapshots
        // autoritativos TIC; SIGE nunca resuelve ni administra estos catálogos.
        $vinculo = array(
            'tipo' => $vinculoUi['tipo'],
            'cargo_id' => $cargoSnapshot['id'] ?? null,
            'dependencia_id' => $dependenciaSnapshot['id'] ?? null,
            'cargo' => $cargoSnapshot,
            'dependencia' => $dependenciaSnapshot,
            'fecha_inicio' => $payload['fecha_inicio'] ?? date('Y-m-d')
        );
        
        $celular = trim((string)($identidad['celular'] ?? ''));
        $rh = trim((string)($identidad['rh'] ?? ''));
        $fotoBase64 = trim((string)($payload['foto_base64'] ?? ''));
        if (preg_match('/^3[0-9]{9}$/', $celular) !== 1
            || !in_array($rh, array('O+','O-','A+','A-','B+','B-','AB+','AB-'), true)
            || $fotoBase64 === '') {
            http_response_code(422);
            echo json_encode(array('status' => 'ERROR', 'message' => 'Celular, RH y fotografía reales son obligatorios; TIC no genera valores sustitutos.'));
            exit;
        }
        $comandoData = array(
            'contract_version' => '1.0',
            'id_operacion' => $idOperacion,
            'comando' => $comando,
            'persona_uuid' => $comando === 'PERSONA_CREAR' ? null : $identidad['persona_uuid'],
            'expected_persona_version' => $comando === 'PERSONA_CREAR' ? 0 : (int)$identidad['persona_version'],
            'ocurrido_en' => date('Y-m-d H:i:s'),
            'identidad' => array(
                'tipo_documento' => trim((string)$identidad['tipo_documento']),
                'numero_documento' => trim((string)$identidad['numero_documento']),
                'nombres' => trim((string)$identidad['nombres']),
                'apellidos' => trim((string)$identidad['apellidos']),
                'correo' => !empty($identidad['correo']) ? trim((string)$identidad['correo']) : null,
                'celular' => $celular,
                'rh' => $rh
            ),
            'vinculo' => $vinculo,
            'foto_base64' => $fotoBase64,
            'usuario' => array('id' => (string)$actor['id'], 'nombre' => $actor['nombre'], 'rol' => $actor['rol'])
        );
        
        $payloadJson = json_encode($comandoData);
        $payloadHash = hash('sha256', $payloadJson);
        
        try {
            $pdo = new PDO(connstring, user, pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("SET time_zone = '-05:00'");
            
            $pdo->beginTransaction();
            
            // 1. Registrar en outbox con estado PENDIENTE para que sea enviado a SIGE
            $stmt = $pdo->prepare(
                "INSERT INTO sige_personal_outbox (id_operacion, comando, tipo, persona_uuid, payload, estado, proximo_intento_en, intentos, creado_en, actualizado_en)
                 VALUES (:id, :cmd, :tipo, :uuid, :payload, 'PENDIENTE', NOW(), 0, NOW(), NOW())"
            );
            $stmt->execute(array(
                ':id' => $idOperacion,
                ':cmd' => $comandoTransporte,
                ':tipo' => $vinculo['tipo'],
                ':uuid' => $comando === 'PERSONA_CREAR' ? null : $identidad['persona_uuid'],
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
            error_log("Error insertando en base de datos (Personal): " . $e->getMessage());
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
