<?php
// developer/tests/acuses_integration_test.php

date_default_timezone_set('America/Bogota');
require_once(__DIR__ . '/../Config/PDOconn.php');
require_once(__DIR__ . '/../Services/MailAcuseService.php');

$dbPass = (defined('pass') && pass !== '') ? pass : (getenv('TIC_DB_PASS') ?: 'B.quilla54');

try {
    $pdo = new PDO(connstring, user, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "=== INICIANDO PRUEBA DE INTEGRACIÓN DE ACUSES Y SILENCIO POSITIVO ===\n";

    // 1. Probar registro de correo con token
    $mailService = new MailAcuseService();
    $testEmail = 'estudiante_prueba_' . time() . '@scv.edu.co';
    $resultadoEnvio = $mailService->registrarEnvioCorreo($testEmail, 'Notificación de Prueba Carnetización', '1081798628');

    echo "[✓] Registro de correo exitoso. Token: " . $resultadoEnvio['token'] . "\n";
    echo "[✓] URL de acuse generada: " . $resultadoEnvio['url_acuse'] . "\n";

    // 2. Probar generación de plantilla HTML con botón y cláusula
    $htmlBoton = $mailService->generarHtmlBotonAcuse($resultadoEnvio['url_acuse']);
    if (strpos($htmlBoton, 'Silencio Administrativo Positivo') !== false && strpos($htmlBoton, $resultadoEnvio['token']) !== false) {
        echo "[✓] Plantilla HTML generada correctamente con botón y cláusula tácita.\n";
    } else {
        throw new Exception("Error al validar la plantilla HTML del botón.");
    }

    // 3. Simular clic en el acuse de recibo explícito
    $token = $resultadoEnvio['token'];
    $stmtConfirmar = $pdo->prepare("UPDATE log_correos_acuses 
        SET estado_acuse = 'CONFIRMADO_EXPRESO',
            fecha_confirmacion = NOW(),
            ip_confirmacion = '127.0.0.1',
            user_agent = 'TestAgent/1.0'
        WHERE token_acuse = :token");
    $stmtConfirmar->execute([':token' => $token]);

    // 4. Verificar estado final en BD
    $stmtCheck = $pdo->prepare("SELECT * FROM log_correos_acuses WHERE token_acuse = :token");
    $stmtCheck->execute([':token' => $token]);
    $registro = $stmtCheck->fetch();

    if ($registro && $registro['estado_acuse'] === 'CONFIRMADO_EXPRESO') {
        echo "[✓] Verificación en Base de Datos: Estado actualizado a CONFIRMADO_EXPRESO.\n";
    } else {
        throw new Exception("Error al verificar el registro en la base de datos.");
    }

    echo "=== PRUEBA DE INTEGRACIÓN COMPLETADA CON ÉXITO ===\n";

} catch (Exception $e) {
    echo "ERROR PRUEBA INTEGRACIÓN: " . $e->getMessage() . "\n";
    exit(1);
}
