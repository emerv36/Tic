<?php
// developer/Controller/acusesController.php

require_once('../Config/PDOconn.php');

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? 'listar';

$dbPass = (defined('pass') && pass !== '') ? pass : (getenv('TIC_DB_PASS') ?: 'B.quilla54');

try {
    $pdo = new PDO(connstring, user, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    if ($action === 'listar') {
        $estado = $_GET['estado'] ?? '';
        
        $sql = "SELECT id, estudiante_id, destinatario_email, asunto, tipo_notificacion, fecha_envio, estado_envio, estado_acuse, intentos_recordatorio, fecha_ultimo_recordatorio, fecha_confirmacion, ip_confirmacion, user_agent FROM log_correos_acuses";
        $params = [];

        if (!empty($estado)) {
            $sql .= " WHERE estado_acuse = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY id DESC LIMIT 200";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $registros = $stmt->fetchAll();

        echo json_encode([
            'status' => 'success',
            'data' => $registros
        ]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
