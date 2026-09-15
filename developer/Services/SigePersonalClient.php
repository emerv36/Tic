<?php
require_once __DIR__ . '/../Config/sige_config.php';

class SigePersonalClient {
    private $baseUrl;
    private $apiKey;

    public function __construct() {
        $this->baseUrl = rtrim(TIC_SIGE_URL, '/');
        $this->apiKey = TIC_SIGE_API_KEY;
    }

    public function consultarPersona($tipo, $num) {
        return $this->consultarPersonaPorDocumento($num, $tipo);
    }

    public function consultarPersonaPorDocumento($num, $tipo = 'CC') {
        if (trim((string)$this->apiKey) === '') {
            throw new Exception('La credencial TIC_SIGE_API_KEY no está configurada.');
        }
        $tipoStr = trim((string)$tipo) !== '' ? trim((string)$tipo) : 'CC';
        $numStr = trim((string)$num);

        $url = $this->baseUrl . '/api/integracion/tic/personas?tipo_documento=' . urlencode($tipoStr) . '&numero_documento=' . urlencode($numStr);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Error cURL en consultarPersona: " . $error);
        }

        if ($httpCode === 200) {
            $body = self::decodificarJson($response);
            if (!is_array($body)) {
                throw new Exception('SIGE devolvió una consulta de persona malformada.');
            }
            $personas = $body['personas'] ?? $body['items'] ?? [];
            if (is_array($personas) && count($personas) >= 1) {
                return $personas[0];
            }
            return null;
        } elseif ($httpCode === 404) {
            return null; // No encontrada
        }
        
        throw new Exception("SIGE respondió HTTP $httpCode al consultar persona. Respuesta: " . $response);
    }

    public function transferirFoto($personaUuid, $idOperacion, $expectedVersion, $fotoPath, $mime, $sha256, array $actor) {
        if (trim((string)$this->apiKey) === '') {
            throw new Exception('La credencial TIC_SIGE_API_KEY no está configurada.');
        }
        $url = $this->baseUrl . '/api/integracion/tic/personas/' . urlencode($personaUuid) . '/foto';
        
        $metadata = json_encode([
            'contract_version' => '1.0',
            'id_operacion' => $idOperacion,
            'tipo' => 'FOTO_ASOCIAR',
            'persona_uuid' => $personaUuid,
            'expected_persona_version' => (int)$expectedVersion,
            'archivo' => [
                'campo_multipart' => 'foto',
                'sha256' => $sha256,
                'mime_declarado' => $mime,
                'bytes' => filesize($fotoPath)
            ],
            'ocurrido_en' => date('Y-m-d H:i:s'),
            'usuario' => array('id' => (string)$actor['id'], 'nombre' => $actor['nombre'], 'rol' => $actor['rol'])
        ]);

        $cfile = new CURLFile($fotoPath, $mime, basename($fotoPath));

        $postData = [
            'metadata' => $metadata,
            'foto' => $cfile
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30s para la foto

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Error cURL en transferirFoto: " . $error);
        }

        return self::validarAckProcesado($response, $httpCode, $idOperacion, $personaUuid);
    }

    public static function validarAckProcesado($response, $httpCode, $idOperacion, $personaUuid) {
        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception("SIGE respondió HTTP $httpCode al transferir foto. Respuesta: " . $response);
        }
        $body = self::decodificarJson($response);
        if (!is_array($body)) {
            throw new Exception('SIGE devolvió un ACK de foto que no es JSON válido.');
        }
        $required = array('contract_version','id_operacion','status','codigo','mensaje','persona_uuid','persona_version');
        foreach ($required as $key) {
            if (!array_key_exists($key, $body)) throw new Exception('SIGE devolvió un ACK de foto incompleto.');
        }
        $version = is_int($body['persona_version']) && $body['persona_version'] >= 1
            ? $body['persona_version'] : false;
        if ($body['contract_version'] !== '1.0'
            || !hash_equals(strtolower((string)$idOperacion), strtolower((string)$body['id_operacion']))
            || $body['status'] !== 'PROCESADO'
            || trim((string)$body['codigo']) === ''
            || !hash_equals(strtolower((string)$personaUuid), strtolower((string)$body['persona_uuid']))
            || $version === false) {
            throw new Exception('SIGE devolvió un ACK de foto no correlacionado o inválido.');
        }
        $body['persona_version'] = (int)$version;
        return $body;
    }

    private static function decodificarJson($response) {
        $json = (string)$response;
        if (substr($json, 0, 3) === "\xEF\xBB\xBF") $json = substr($json, 3);
        return json_decode($json, true);
    }
}
