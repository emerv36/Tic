<?php
// developer/Services/MailAcuseService.php

require_once(__DIR__ . '/DiasHabilesColombia.php');

class MailAcuseService {
    // Plazo de silencio administrativo positivo, en días hábiles, contado desde la última notificación enviada
    const DIAS_SILENCIO_POSITIVO = 5;
    // Recordatorios máximos por acuse
    const MAX_RECORDATORIOS = 2;
    // Separación mínima entre notificaciones. Se usan 46 h (no 48) para que un cron diario
    // a la misma hora no se salte un día por diferencias de segundos.
    const HORAS_ENTRE_RECORDATORIOS = 46;

    private $pdo;

    public function __construct(?PDO $pdo = null) {
        if ($pdo !== null) {
            $this->pdo = $pdo;
            return;
        }
        // Credenciales desde config.php (constante pass) o la variable de entorno TIC_DB_PASS; sin valores en el código
        $dbPass = (defined('pass') && pass !== '') ? pass : (getenv('TIC_DB_PASS') ?: '');
        $this->pdo = new PDO(connstring, user, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    /**
     * Genera un token único y registra el envío de correo.
     * El registro queda como ENVIADO; el llamador debe invocar marcarResultadoEnvio()
     * con el resultado real de PHPMailer.
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

            return [
                'id' => $this->pdo->lastInsertId(),
                'token' => $token,
                'url_acuse' => $this->construirUrlAcuse($token)
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
     * Guarda el resultado real del envío. Un acuse FALLIDO nunca pasa a silencio positivo.
     */
    public function marcarResultadoEnvio($idAcuse, $enviado) {
        if (empty($idAcuse)) {
            return;
        }
        try {
            $stmt = $this->pdo->prepare("UPDATE log_correos_acuses SET estado_envio = :estado WHERE id = :id");
            $stmt->execute([':estado' => $enviado ? 'ENVIADO' : 'FALLIDO', ':id' => $idAcuse]);
        } catch (Exception $e) {
            error_log("MailAcuseService Error en marcarResultadoEnvio: " . $e->getMessage());
        }
    }

    /**
     * Construye el HTML del botón de acuse de recibo y la cláusula de silencio positivo.
     */
    public function generarHtmlBotonAcuse($urlAcuse, $diasSilencioPositivo = self::DIAS_SILENCIO_POSITIVO) {
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
     * Reinicia el token, la fecha de envío y el contador de recordatorios para reiniciar los días hábiles de silencio positivo.
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

            $urlAcuse = $this->construirUrlAcuse($nuevoToken);
            $asunto = $registro['asunto'] ?: 'Notificación Acuse de Recibo';

            $html = $this->plantillaCorreo(
                'Notificación de Carnetización / Comunicado Institucional',
                '<p>Se ha actualizado la dirección de correo para el envío de esta comunicación.</p>',
                $this->generarHtmlBotonAcuse($urlAcuse)
            );

            $resultado = $this->enviarHtml(null, $nuevoCorreo, $asunto, $html);
            $this->marcarResultadoEnvio($idAcuse, $resultado['ok']);

            if (!$resultado['ok']) {
                return [
                    'status' => 'error',
                    'message' => 'Se actualizó el correo en el sistema, pero el envío falló: ' . $resultado['error'] . '. Intente de nuevo más tarde.',
                    'url_acuse' => $urlAcuse
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Correo corregido y acuse reenviado exitosamente.',
                'url_acuse' => $urlAcuse
            ];

        } catch (Exception $e) {
            error_log("MailAcuseService Error en reenviarCorreoAcuse: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // ------------------------------------------------------------------
    // Recordatorios y silencio positivo (usados por developer/bin/cron_recordatorios_acuses.php)
    // ------------------------------------------------------------------

    /**
     * Acuses PENDIENTES que deben recibir recordatorio:
     * - menos de MAX_RECORDATORIOS enviados, y
     * - la última notificación (envío inicial o último recordatorio) tiene al menos HORAS_ENTRE_RECORDATORIOS.
     * Incluye envíos iniciales FALLIDOS: el recordatorio actúa como reintento.
     */
    public function obtenerPendientesParaRecordatorio($limite = 200) {
        $stmt = $this->pdo->prepare("SELECT * FROM log_correos_acuses
            WHERE estado_acuse = 'PENDIENTE'
              AND intentos_recordatorio < :max
              AND COALESCE(fecha_ultimo_recordatorio, fecha_envio) <= NOW() - INTERVAL :horas HOUR
            ORDER BY id ASC
            LIMIT " . (int)$limite);
        $stmt->bindValue(':max', self::MAX_RECORDATORIOS, PDO::PARAM_INT);
        $stmt->bindValue(':horas', self::HORAS_ENTRE_RECORDATORIOS, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Envía un recordatorio con el mismo token del acuse (los enlaces anteriores siguen siendo válidos).
     * Solo cuenta el recordatorio si PHPMailer confirma el envío.
     */
    public function enviarRecordatorio(array $registro, $mail = null) {
        $numero = (int)$registro['intentos_recordatorio'] + 1;
        $esUltimo = $numero >= self::MAX_RECORDATORIOS;
        $nombre = $this->obtenerNombreEstudiante($registro['estudiante_id'] ?? null);

        $saludo = $nombre !== '' ? 'Hola, ' . htmlspecialchars($nombre) . '.' : 'Hola.';
        $cuerpo = '<p>' . $saludo . '</p>'
            . '<p>Te recordamos que tienes pendiente confirmar el recibido de la siguiente notificación de <strong>SYSTEM CENTER</strong>:</p>'
            . '<p style="background:#f8fafc;border-left:4px solid #0284c7;padding:10px 14px;">' . htmlspecialchars($registro['asunto']) . '</p>'
            . ($esUltimo ? '<p><strong>Este es el último recordatorio.</strong></p>' : '');

        $html = $this->plantillaCorreo(
            'Recordatorio: confirma el recibido de tu notificación',
            $cuerpo,
            $this->generarHtmlBotonAcuse($this->construirUrlAcuse($registro['token_acuse']))
        );

        $asunto = 'Recordatorio ' . $numero . ' de ' . self::MAX_RECORDATORIOS . ': ' . $registro['asunto'];
        $resultado = $this->enviarHtml($mail, $registro['destinatario_email'], $asunto, $html);

        if ($resultado['ok']) {
            $upd = $this->pdo->prepare("UPDATE log_correos_acuses
                SET intentos_recordatorio = :intentos,
                    fecha_ultimo_recordatorio = NOW(),
                    estado_envio = 'ENVIADO'
                WHERE id = :id AND estado_acuse = 'PENDIENTE'");
            $upd->execute([':intentos' => $numero, ':id' => $registro['id']]);
        } else {
            error_log("MailAcuseService recordatorio fallido (acuse {$registro['id']}): " . $resultado['error']);
        }

        return $resultado + ['numero' => $numero];
    }

    /**
     * Aplica CONFIRMADO_TACITO solo a acuses que:
     * - recibieron todos los recordatorios con envío exitoso (estado_envio = ENVIADO), y
     * - superaron DIAS_SILENCIO_POSITIVO días hábiles desde la última notificación.
     * Devuelve los IDs actualizados.
     */
    public function aplicarSilencioPositivo(?DateTimeInterface $ahora = null) {
        $ahora = $ahora ?: new DateTimeImmutable('now');

        $stmt = $this->pdo->prepare("SELECT id, COALESCE(fecha_ultimo_recordatorio, fecha_envio) AS ultima_notificacion
            FROM log_correos_acuses
            WHERE estado_acuse = 'PENDIENTE'
              AND estado_envio = 'ENVIADO'
              AND intentos_recordatorio >= :max");
        $stmt->bindValue(':max', self::MAX_RECORDATORIOS, PDO::PARAM_INT);
        $stmt->execute();

        $upd = $this->pdo->prepare("UPDATE log_correos_acuses
            SET estado_acuse = 'CONFIRMADO_TACITO'
            WHERE id = :id AND estado_acuse = 'PENDIENTE'");

        $aplicados = [];
        foreach ($stmt->fetchAll() as $fila) {
            $desde = new DateTimeImmutable($fila['ultima_notificacion']);
            if (DiasHabilesColombia::plazoVencido($desde, self::DIAS_SILENCIO_POSITIVO, $ahora)) {
                $upd->execute([':id' => $fila['id']]);
                if ($upd->rowCount() > 0) {
                    $aplicados[] = (int)$fila['id'];
                }
            }
        }
        return $aplicados;
    }

    // ------------------------------------------------------------------
    // Correo
    // ------------------------------------------------------------------

    /**
     * Crea una instancia nueva de PHPMailer con la configuración SMTP de confiemail.php.
     */
    public function crearMailer() {
        $mail = null;
        include(__DIR__ . '/../Config/confiemail.php');
        if (!is_object($mail)) {
            throw new RuntimeException('No se pudo inicializar PHPMailer desde confiemail.php');
        }
        $mail->CharSet = 'UTF-8';
        return $mail;
    }

    /**
     * Envía un correo HTML. Si $mail es null crea un mailer nuevo y cierra la conexión al terminar.
     * Devuelve ['ok' => bool, 'error' => string].
     */
    public function enviarHtml($mail, $destinatario, $asunto, $html) {
        $propio = ($mail === null);
        try {
            if ($propio) {
                $mail = $this->crearMailer();
            }
            $mail->ClearAllRecipients();
            $mail->SetFrom('info@scv.edu.co', 'System Center - Tecnologías de la información');
            $mail->Subject = $asunto;
            $mail->MsgHTML($html);
            $mail->IsHTML(true);
            $mail->AddAddress($destinatario);
            $mail->smtpConnect([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);
            $ok = (bool)$mail->Send();
            $error = $ok ? '' : (string)$mail->ErrorInfo;
        } catch (Throwable $e) {
            $ok = false;
            $error = $e->getMessage();
        }

        if ($propio && is_object($mail)) {
            try { $mail->smtpClose(); } catch (Throwable $e) { /* sin acción */ }
        }
        return ['ok' => $ok, 'error' => $error];
    }

    public function construirUrlAcuse($token) {
        $query = http_build_query([
            'case' => 'AcuseRecibo',
            'token' => $token
        ], '', '&', PHP_QUERY_RFC3986);
        return $this->obtenerBaseUrl() . "/developer/Controller/reportecarnetizacionControler.php?" . $query;
    }

    private function plantillaCorreo($titulo, $cuerpoHtml, $htmlBoton) {
        $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Notificación</title></head>';
        $html .= '<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px;">';
        $html .= '<table width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">';
        $html .= '<tr><td>';
        $html .= '<h3 style="color: #0056b3;">' . htmlspecialchars($titulo) . '</h3>';
        $html .= $cuerpoHtml;
        $html .= $htmlBoton;
        $html .= '<p style="font-size: 12px; color: #94a3b8; text-align: center;">Instituto Centro de Sistemas S.A.S. - System Center<br>Departamento de Tecnologías de la Información</p>';
        $html .= '</td></tr></table></body></html>';
        return $html;
    }

    private function obtenerNombreEstudiante($identificacion) {
        if (empty($identificacion)) {
            return '';
        }
        try {
            $stmt = $this->pdo->prepare("SELECT nombre_estudiante, apellido_estudiante FROM inscripcion
                WHERE identificacion = :identificacion ORDER BY id_inscripcion DESC LIMIT 1");
            $stmt->execute([':identificacion' => $identificacion]);
            $fila = $stmt->fetch();
            if (!$fila) {
                return '';
            }
            return ucwords(mb_strtolower(trim($fila['nombre_estudiante'] . ' ' . $fila['apellido_estudiante']), 'UTF-8'));
        } catch (Exception $e) {
            return '';
        }
    }

    private function obtenerBaseUrl() {
        $envBase = getenv('TIC_PUBLIC_BASE_URL');
        if (!empty($envBase)) {
            return rtrim($envBase, '/');
        }

        // Desde CLI (cron) no hay HTTP_HOST ni SCRIPT_NAME web: usar el dominio público
        if (PHP_SAPI === 'cli') {
            return 'https://tic.scv.edu.co';
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
