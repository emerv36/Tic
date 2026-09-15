<?php
require_once(__DIR__ . '/../Config/sige_config.php');
require_once(__DIR__ . '/../Services/PersonalEventReceiverService.php');

class PersonalEventReceiverController {
    
    public function handleRequest() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(array('status' => 'RECHAZADO', 'codigo' => 'METHOD_NOT_ALLOWED', 'mensaje' => 'Solo se permite POST.'));
            return;
        }

        $headers = function_exists('getallheaders') ? getallheaders() : $this->getHeadersPolyfill();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(array('status' => 'RECHAZADO', 'codigo' => 'UNAUTHORIZED', 'mensaje' => 'Token no proporcionado.'));
            return;
        }

        $token = trim($matches[1]);
        if (trim((string)SIGE_INBOUND_API_KEY) === '' || !hash_equals((string)SIGE_INBOUND_API_KEY, $token)) {
            http_response_code(401);
            echo json_encode(array('status' => 'RECHAZADO', 'codigo' => 'UNAUTHORIZED', 'mensaje' => 'Token inválido.'));
            return;
        }

        $rawPayload = file_get_contents('php://input');
        $decoded = json_decode($rawPayload, true);
        
        $xEventId = $headers['X-Event-Id'] ?? $headers['x-event-id'] ?? '';
        if (is_array($decoded) && isset($decoded['id_evento'])) {
            if ($xEventId === '' || $xEventId !== $decoded['id_evento']) {
                http_response_code(400);
                echo json_encode(array('status' => 'RECHAZADO', 'codigo' => 'BAD_REQUEST', 'mensaje' => 'Header X-Event-Id no coincide con el payload id_evento.'));
                return;
            }
        }

        $service = new PersonalEventReceiverService();
        $result = $service->receive($rawPayload);

        http_response_code($result['status']);
        
        $response = array(
            'status'  => ($result['status'] >= 200 && $result['status'] < 300) ? 'PROCESADO' : 'RECHAZADO',
            'codigo'  => $result['codigo'],
            'mensaje' => $result['message']
        );

        $decoded = json_decode($rawPayload, true);
        if (is_array($decoded) && isset($decoded['id_evento'])) {
            $response['id_evento'] = $decoded['id_evento'];
        }

        echo json_encode($response);
    }

    private function getHeadersPolyfill() {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
