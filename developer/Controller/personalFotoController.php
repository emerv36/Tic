<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../Config/PDOconn.php';
require_once __DIR__ . '/../Config/personal_ui_config.php';
require_once __DIR__ . '/../Services/PersonalCatalogAuthorization.php';

if (!TIC_SIGE_PERSONAL_UI_ENABLED) {
    http_response_code(404);
    exit;
}

$defaultAvatar = __DIR__ . '/../../assets/images/fotoperfil/user.png';

// Validar que exista alguna sesión autenticada en TIC
if (empty($_SESSION['SiigaBv']) && empty($_SESSION['USERID']) && empty($_SESSION['IN_codigo_usuCA'])) {
    if (file_exists($defaultAvatar)) {
        header('Content-Type: image/png');
        readfile($defaultAvatar);
    } else {
        http_response_code(401);
    }
    exit;
}

$uuid = $_GET['uuid'] ?? '';

if (!preg_match('/^[0-9a-f-]{32,36}$/i', $uuid)) {
    if (file_exists($defaultAvatar)) {
        header('Content-Type: image/png');
        readfile($defaultAvatar);
    } else {
        http_response_code(400);
    }
    exit;
}

$sigeStorageRoot = getenv('SIGE_PERSONAL_PHOTO_STORAGE_ROOT') ?: 'C:/xampp/sige-storage/personas/fotos';
$fotoPath = null;

try {
    $pdo = new PDO(connstring, user, pass, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ));
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    $stmt = $pdo->prepare(
        "SELECT pf.ruta_relativa 
         FROM sige_db.persona_fotos pf 
         JOIN sige_db.personas p ON pf.persona_id = p.id 
         WHERE p.persona_uuid = :uuid LIMIT 1"
    );
    $stmt->execute(array(':uuid' => $uuid));
    $row = $stmt->fetch();
    
    if (!empty($row['ruta_relativa'])) {
        $candidata = rtrim($sigeStorageRoot, '/\\') . '/' . $row['ruta_relativa'];
        if (file_exists($candidata)) {
            $fotoPath = $candidata;
        }
    }
} catch (Exception $e) {
    error_log("Error buscando foto en personalFotoController: " . $e->getMessage());
}

// Fallbacks locales si existieran
if (!$fotoPath || !file_exists($fotoPath)) {
    if (file_exists(__DIR__ . "/../../assets/fotoperfil/p_" . $uuid . ".jpg")) {
        $fotoPath = __DIR__ . "/../../assets/fotoperfil/p_" . $uuid . ".jpg";
    }
}

if ($fotoPath && file_exists($fotoPath)) {
    $mime = mime_content_type($fotoPath) ?: 'image/jpeg';
    header('Content-Type: ' . $mime);
    header('Cache-Control: private, max-age=3600');
    readfile($fotoPath);
    exit;
}

if (file_exists($defaultAvatar)) {
    header('Content-Type: image/png');
    readfile($defaultAvatar);
    exit;
}

http_response_code(404);
exit;
