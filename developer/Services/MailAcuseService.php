<?php
// developer/Services/MailAcuseService.php

class MailAcuseService {
    private $pdo;

    public function __construct() {
        $dbPass = (defined('pass') && pass !== '') ? pass : (getenv('TIC_DB_PASS') ?: 'B.quilla54');
        $this->pdo = new PDO(connstring, user, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    /**
     * Genera un token único y registra el envío de correo.
     */
    public function registrarEnvioCorreo($destinatarioEmail, $asunto, $estudianteId = null, $tipoNotificacion = 'CARNETIZACION') {
        try {
            $token = bin2hex(random_bytes(32));
            $stmt = $this->pdo->prepare("INSERT INTO log_correos_acuses 
                (estudiante_id, destinatario_email, asunto, token_acuse, tipo_notificacion, fecha_envio, estado_envio, estado_acuse)
                VALUES (:estudiante_id, :email, :asunto, :token, :tipo, NOW(), 'ENVIADO', 'PENDIENTE')");
            $stmt->execute([
                ':estudiante_id' => $estudianteId,
                ':email' => $destinatarioEmail,
                ':asunto' => $asunto,
                ':token' => $token,
                ':tipo' => $tipoNotificacion
            ]);

            $query = http_build_query([
                'case' => 'AcuseRecibo',
                'token' => $token
            ], '', '&', PHP_QUERY_RFC3986);

            return [
                'id' => $this->pdo->lastInsertId(),
                'token' => $token,
                'url_acuse' => $this->obtenerBaseUrl() . "/developer/Controller/reportecarnetizacionControler.php?" . $query
            ];
        } catch (Exception $e) {
            error_log("MailAcuseService Error en registrarEnvioCorreo: " . $e->getMessage());
            return [
                'id' => null,
                'token' => null,
                'url_acuse' => $this->obtenerBaseUrl() . "/developer/Controller/reportecarnetizacionControler.php?case=AcuseRecibo"
            ];
        }
    }

    /**
     * Construye el HTML del botón de acuse de recibo y la cláusula de silencio positivo.
     */
    public function generarHtmlBotonAcuse($urlAcuse, $diasSilencioPositivo = 5) {
        return '
        <div style="margin: 30px 0; text-align: center; background-color: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef;">
            <p style="font-size: 15px; color: #333333; margin-bottom: 15px; font-weight: 600;">
                Por favor confirme la recepción de esta notificación haciendo clic en el siguiente botón:
            </p>
            <a href="' . htmlspecialchars($urlAcuse) . '" target="_blank" style="background-color: #0056b3; color: #ffffff; padding: 12px 28px; text-decoration: none; font-weight: bold; border-radius: 5px; display: inline-block; font-size: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                ✓ Confirmar Recibido de Notificación
            </a>
            <p style="font-size: 12px; color: #6c757d; margin-top: 18px; line-height: 1.4; text-align: justify;">
                * <strong>Aviso Importante (Silencio Administrativo Positivo):</strong> Dispone de un plazo de ' . intval($diasSilencioPositivo) . ' días hábiles a partir del recibo de esta comunicación para confirmar la recepción o manifestar cualquier inconformidad. Vencido dicho plazo sin pronunciamiento expreso, la institución asumirá la recepción conforme para todos los efectos legales e institucionales.
            </p>
        </div>';
    }

    private function obtenerBaseUrl() {
        $envBase = getenv('TIC_PUBLIC_BASE_URL');
        if (!empty($envBase)) {
            return rtrim($envBase, '/');
        }

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $protocol = "https://";
        }
        
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'tic.scv.edu.co';
        
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $baseDir = explode('/developer', $scriptDir)[0];
        $baseDir = rtrim($baseDir, '/');

        if ($baseDir !== '' && $baseDir[0] !== '/') {
            $baseDir = '/' . $baseDir;
        }

        return $protocol . $host . $baseDir;
    }
}
