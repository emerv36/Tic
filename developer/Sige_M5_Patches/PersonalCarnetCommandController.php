<?php
require_once __DIR__ . '/../Services/PersonalCarnetService.php';
require_once __DIR__ . '/../Config/Database.php';

class PersonalCarnetCommandController {
    private $service;

    public function __construct() {
        $db = (new Database())->getConnection();
        $this->service = new PersonalCarnetService($db);
    }

    public function procesar() {
        header('Content-Type: application/json; charset=utf-8');

        // Autenticación S2S
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? '';
        if (strpos($auth, 'Bearer ') !== 0 || substr($auth, 7) !== PERSONAL_INTEGRATION_API_KEY) {
            http_response_code(401);
            echo json_encode(['status' => 'ERROR', 'message' => 'No autorizado']);
            return;
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);

        if (!$payload) {
            http_response_code(400);
            echo json_encode(['status' => 'ERROR', 'message' => 'JSON inválido']);
            return;
        }

        try {
            $resultado = $this->service->procesar($payload);
            http_response_code($resultado['http_status']);
            echo json_encode([
                'status' => $resultado['status'] ?? 'ERROR',
                'message' => $resultado['message'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("PersonalCarnetCommandController Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'ERROR', 'message' => 'Error interno del servidor']);
        }
    }
}
