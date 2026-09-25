<?php
// developer/tests/test_entregado_email.php

date_default_timezone_set("America/Bogota");
chdir(__DIR__ . '/../Controller');
require_once('../Config/PDOconn.php');
require_once('../Config/confiemail.php');
require_once('../Services/MailAcuseService.php');

$dbPass = (defined('pass') && pass !== '') ? pass : (getenv('TIC_DB_PASS') ?: 'B.quilla54');

try {
    $pdo = new PDO(connstring, user, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "=== PRUEBA DE ENVÍO DE CORREO DE ENTREGADO CON ACUSE Y REGISTRO EN LOG ===\n";

    $txtcorreo = 'asesorscv2021@gmail.com';
    $identidad = '1081798628';
    $txtnombre = 'Estudiante Prueba Entregado';
    $txtprograma = 'Técnico en Sistemas';
    $fechaNotificacion = date('d/m/Y h:i A');

    $mailAcuseService = new MailAcuseService();
    $asuntoCorreo = "¡Hemos entregado tú carnet proceso finalizado!" . " - " . $identidad;
    
    // Registrar el envío en la BD
    $datosAcuse = $mailAcuseService->registrarEnvioCorreo($txtcorreo, $asuntoCorreo, $identidad, 'CARNETIZACION');
    echo "[✓] Registro en log_correos_acuses id: " . $datosAcuse['id'] . "\n";
    echo "[✓] URL de acuse: " . $datosAcuse['url_acuse'] . "\n";

    $htmlBotonAcuse = $mailAcuseService->generarHtmlBotonAcuse($datosAcuse['url_acuse'], 5);

    $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Carnet Entregado</title></head>';
    $html .= '<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: \'Segoe UI\', Arial, sans-serif; color: #334155;">';
    $html .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f1f5f9; padding: 30px 10px;"><tr><td align="center">';
    $html .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid #e2e8f0;">';
    $html .= '<tr><td align="center" style="background-color: #ffffff; padding: 25px 20px; border-bottom: 3px solid #0284c7;">';
    $html .= '<img src="http://tic.scv.edu.co/assets/images/logoNuevo.png" alt="System Center" style="max-width: 280px; width: 80%; height: auto; display: block;" />';
    $html .= '</td></tr>';
    $html .= '<tr><td align="center" style="padding: 25px 30px 10px 30px;">';
    $html .= '<span style="display: inline-block; background-color: #0284c7; color: #ffffff; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 6px 16px; border-radius: 20px;">CARNET ENTREGADO EXITOSAMENTE</span>';
    $html .= '</td></tr>';
    $html .= '<tr><td style="padding: 15px 30px 25px 30px;">';
    $html .= '<h2 style="color: #0f172a; font-size: 20px; margin-top: 0; font-weight: 600; text-align: center;">¡Felicitaciones, ' . $txtnombre . '!</h2>';
    $html .= '<p style="font-size: 14px; line-height: 1.6; color: #475569; text-align: center; margin-bottom: 25px;">El Departamento de Tecnologías de la Información de <strong>SYSTEM CENTER</strong> informa que tu carnet estudiantil ha sido entregado exitosamente.</p>';
    $html .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 25px;">';
    $html .= '<tr><td style="padding: 15px 20px;"><table border="0" cellpadding="0" cellspacing="0" width="100%">';
    $html .= '<tr><td style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 600; width: 140px;">Estudiante:</td><td style="padding: 6px 0; font-size: 14px; color: #0f172a; font-weight: 600;">' . $txtnombre . '</td></tr>';
    $html .= '<tr><td style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 600;">Identificación:</td><td style="padding: 6px 0; font-size: 14px; color: #334155;">C.C ' . $identidad . '</td></tr>';
    $html .= '<tr><td style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 600;">Programa:</td><td style="padding: 6px 0; font-size: 14px; color: #334155;">' . $txtprograma . '</td></tr>';
    $html .= '<tr><td style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 600;">Fecha Notificación:</td><td style="padding: 6px 0; font-size: 14px; color: #334155;">' . $fechaNotificacion . '</td></tr>';
    $html .= '</table></td></tr></table>';

    // Botón de Acuse
    $html .= $htmlBotonAcuse;

    $html .= '<div style="background-color: #f0fdf4; border-left: 4px solid #0284c7; padding: 12px 16px; border-radius: 4px; margin-bottom: 25px;"><p style="margin: 0; font-size: 13px; color: #0369a1; line-height: 1.5;">📌 <strong>Recomendaciones importantes:</strong><br>• Debes portar tu carnet en un lugar visible en las instalaciones de System Center.<br>• En caso de pérdida, el costo de renovación es de $10.000 ($22.600 en etapa productiva).</p></div>';
    $html .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px;"><tr><td align="center"><a href="http://tic.scv.edu.co/verificacion" target="_blank" style="display: inline-block; background-color: #0056b3; color: #ffffff; font-size: 14px; font-weight: 600; text-decoration: none; padding: 12px 28px; border-radius: 6px;">Consultar Estado del Carnet</a></td></tr></table>';
    $html .= '</td></tr>';
    $html .= '<tr><td align="center" style="background-color: #f8fafc; padding: 20px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;">Instituto Centro de Sistemas S.A.S. - System Center<br>Departamento de Tecnologías de la Información</td></tr>';
    $html .= '</table></td></tr></table></body></html>';

    $mail->MsgHTML($html);
    $mail->SetFrom('info@scv.edu.co', utf8_decode('System Center - Tecnologias de la información'));
    $mail->Subject = utf8_decode($asuntoCorreo);
    $mail->AddAddress($txtcorreo);
    $mail->IsHTML(true);
    $mail->smtpConnect(array("ssl" => array(
        "verify_peer" => false,
        "verify_peer_name" => false,
        "allow_self_signed" => true
    )));

    if ($mail->Send()) {
        echo "[✓] Correo enviado exitosamente vía SMTP a: $txtcorreo\n";
    } else {
        echo "[X] Error enviando correo: " . $mail->ErrorInfo . "\n";
    }

} catch (Exception $e) {
    echo "ERROR PRUEBA: " . $e->getMessage() . "\n";
}
