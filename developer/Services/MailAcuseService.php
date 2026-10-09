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

    /**
     * Reenvía un acuse de recibo corrigiendo el correo electrónico del destinatario.
     * Reinicia el token, la fecha de envío y el contador de recordatorios para reiniciar los 5 días hábiles de silencio positivo.
     */
    public function reenviarCorreoAcuse($idAcuse, $nuevoCorreo) {
        if (!filter_var($nuevoCorreo, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'El correo electrónico no es válido.'];
        }

        try {
            $stmtGet = $this->pdo->prepare("SELECT * FROM log_correos_acuses WHERE id = :id LIMIT 1");
            $stmtGet->execute([':id' => $idAcuse]);
            $registro = $stmtGet->fetch();

            if (!$registro) {
                return ['status' => 'error', 'message' => 'Registro de acuse no encontrado.'];
            }

            $nuevoToken = bin2hex(random_bytes(32));

            $stmtUpd = $this->pdo->prepare("UPDATE log_correos_acuses 
                SET destinatario_email = :email,
                    token_acuse = :token,
                    fecha_envio = NOW(),
                    estado_envio = 'ENVIADO',
                    estado_acuse = 'PENDIENTE',
                    intentos_recordatorio = 0,
                    fecha_ultimo_recordatorio = NULL,
                    fecha_confirmacion = NULL,
                    ip_confirmacion = NULL,
                    user_agent = NULL
                WHERE id = :id");

            $stmtUpd->execute([
                ':email' => $nuevoCorreo,
                ':token' => $nuevoToken,
                ':id' => $idAcuse
            ]);

            $query = http_build_query([
                'case' => 'AcuseRecibo',
                'token' => $nuevoToken
            ], '', '&', PHP_QUERY_RFC3986);

            $urlAcuse = $this->obtenerBaseUrl() . "/developer/Controller/reportecarnetizacionControler.php?" . $query;

            // Intentar envío por correo mediante confiemail / PHPMailer si está configurado
            $envioMailExitoso = false;
            $confiemailPath = __DIR__ . '/../Config/confiemail.php';
            if (file_exists($confiemailPath)) {
                try {
                    require_once($confiemailPath);
                    if (isset($mail) && is_object($mail)) {
                        $htmlBoton = $this->generarHtmlBotonAcuse($urlAcuse, 5);
                        $asunto = $registro['asunto'] ?? 'Notificación Acuse de Recibo';
                        
                        $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Notificación</title></head>';
                        $html .= '<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px;">';
                        $html .= '<table width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">';
                        $html .= '<tr><td>';
                        $html .= '<h3 style="color: #0056b3;">Notificación de Carnetización / Comunicado Institucional</h3>';
                        $html .= '<p>Se ha actualizado la dirección de correo para el envío de esta comunicación.</p>';
                        $html .= $htmlBoton;
                        $html .= '</td></tr></table></body></html>';

                        $mail->ClearAllRecipients();
                        $mail->MsgHTML($html);
                        $mail->SetFrom('info@scv.edu.co', utf8_decode('System Center - Tecnologias de la información'));
                        $mail->Subject = utf8_decode($asunto);
                        $mail->AddAddress($nuevoCorreo);
                        $mail->IsHTML(true);
                        if (method_exists($mail, 'smtpConnect')) {
                            $mail->smtpConnect(array("ssl" => array(
                                "verify_peer" => false,
                                "verify_peer_name" => false,
                                "allow_self_signed" => true
                            )));
                        }
                        $envioMailExitoso = $mail->Send();
                    }
                } catch (Exception $exMail) {
                    error_log("MailAcuseService Error al enviar PHPMailer en reenvío: " . $exMail->getMessage());
                }
            }

            return [
                'status' => 'success',
                'message' => 'Correo corregido y acuse reenviado exitosamente.' . ($envioMailExitoso ? '' : ' (Se actualizó el token y enlace en la base de datos).'),
                'url_acuse' => $urlAcuse
            ];

        } catch (Exception $e) {
            error_log("MailAcuseService Error en reenviarCorreoAcuse: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
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

