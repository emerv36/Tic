<?php
// confirmar_recibo.php - Endpoint de acuse de recibo explícito / expreso

// Evitar bloqueos de caché en navegadores
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

require_once('developer/Config/PDOconn.php');

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$mensaje = '';
$tipoMensaje = 'info'; // success, warning, error
$detalles = null;

if (!empty($token)) {
    $dbPass = (defined('pass') && pass !== '') ? pass : (getenv('TIC_DB_PASS') ?: 'B.quilla54');
    try {
        $pdo = new PDO(connstring, user, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Consultar token
        $stmt = $pdo->prepare("SELECT * FROM log_correos_acuses WHERE token_acuse = :token LIMIT 1");
        $stmt->execute([':token' => $token]);
        $registro = $stmt->fetch();

        if ($registro) {
            if ($registro['estado_acuse'] === 'CONFIRMADO_EXPRESO' || $registro['estado_acuse'] === 'CONFIRMADO_TACITO') {
                $tipoMensaje = 'warning';
                $mensaje = 'Este acuse de recibo ya fue registrado previamente.';
                $detalles = $registro;
            } else {
                // Registrar la confirmación expresa
                $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
                $fechaAhora = date('Y-m-d H:i:s');

                $update = $pdo->prepare("UPDATE log_correos_acuses 
                    SET estado_acuse = 'CONFIRMADO_EXPRESO',
                        fecha_confirmacion = :fecha,
                        ip_confirmacion = :ip,
                        user_agent = :ua
                    WHERE token_acuse = :token");
                $update->execute([
                    ':fecha' => $fechaAhora,
                    ':ip' => $ip,
                    ':ua' => $userAgent,
                    ':token' => $token
                ]);

                $registro['estado_acuse'] = 'CONFIRMADO_EXPRESO';
                $registro['fecha_confirmacion'] = $fechaAhora;
                $tipoMensaje = 'success';
                $mensaje = '¡Muchas gracias! Tu acuse de recibo ha sido registrado exitosamente en nuestro sistema.';
                $detalles = $registro;
            }
        } else {
            $tipoMensaje = 'error';
            $mensaje = 'El código de confirmación no es válido o la notificación ha expirado.';
        }
    } catch (Exception $e) {
        $tipoMensaje = 'error';
        $mensaje = 'Ocurrió un inconveniente al procesar la confirmación. Por favor intente más tarde.';
    }
} else {
    $tipoMensaje = 'error';
    $mensaje = 'Enlace de confirmación incompleto.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acuse de Recibo - System Center</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #f1f5f9;
            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #334155;
            padding: 20px;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            max-width: 480px;
            width: 100%;
            padding: 40px 30px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px auto;
            font-size: 40px;
        }
        .bg-success { background-color: #dcfce7; color: #166534; }
        .bg-warning { background-color: #fef3c7; color: #92400e; }
        .bg-error { background-color: #fee2e2; color: #991b1b; }
        
        h2 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        p.msg {
            font-size: 15px;
            color: #475569;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .details-container {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            text-align: left;
            font-size: 13px;
            color: #334155;
            margin-bottom: 24px;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .details-row:last-child {
            margin-bottom: 0;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 12px;
            text-transform: uppercase;
        }
        .badge-success { background-color: #16a34a; color: #ffffff; }
        .badge-info { background-color: #0284c7; color: #ffffff; }
        
        .btn-close {
            background-color: #0284c7;
            color: #ffffff;
            border: none;
            padding: 12px 28px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s ease;
        }
        .btn-close:hover {
            background-color: #0369a1;
        }
        .countdown-text {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 14px;
        }
    </style>
</head>
<body>

    <div class="card">
        <div style="margin-bottom: 20px;">
            <img src="assets/images/logoNuevo.png" alt="System Center" style="max-height: 45px; width: auto;" onerror="this.style.display='none'">
        </div>

        <?php if ($tipoMensaje === 'success'): ?>
            <div class="icon-circle bg-success">✓</div>
            <h2>¡Confirmación Registrada!</h2>
        <?php elseif ($tipoMensaje === 'warning'): ?>
            <div class="icon-circle bg-warning">i</div>
            <h2>Acuse Ya Confirmado</h2>
        <?php else: ?>
            <div class="icon-circle bg-error">✕</div>
            <h2>Aviso de Verificación</h2>
        <?php endif; ?>

        <p class="msg"><?= htmlspecialchars($mensaje) ?></p>

        <?php if ($detalles): ?>
            <div class="details-container">
                <div class="details-row">
                    <span>Destinatario:</span>
                    <strong><?= htmlspecialchars($detalles['destinatario_email']) ?></strong>
                </div>
                <div class="details-row">
                    <span>Identificación:</span>
                    <strong><?= htmlspecialchars($detalles['estudiante_id'] ?? 'N/A') ?></strong>
                </div>
                <div class="details-row">
                    <span>Estado:</span>
                    <span class="badge badge-success"><?= htmlspecialchars($detalles['estado_acuse']) ?></span>
                </div>
                <?php if (!empty($detalles['fecha_confirmacion'])): ?>
                <div class="details-row">
                    <span>Fecha Confirmación:</span>
                    <strong><?= htmlspecialchars($detalles['fecha_confirmacion']) ?></strong>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <button class="btn-close" onclick="cerrarVentana();">Cerrar esta Ventana</button>

        <p class="countdown-text" id="countdown">Esta ventana se cerrará automáticamente en <b id="sec">5</b> segundos...</p>
    </div>

    <script>
        var segundos = 5;
        var timer = setInterval(function() {
            segundos--;
            var el = document.getElementById('sec');
            if (el) el.innerText = segundos;
            if (segundos <= 0) {
                clearInterval(timer);
                cerrarVentana();
            }
        }, 1000);

        function cerrarVentana() {
            window.opener = null;
            window.open('', '_self');
            window.close();
            // Si el navegador bloquea el auto-cierre por políticas de pestaña exterior:
            document.getElementById('countdown').innerText = "Puedes cerrar de forma segura esta pestaña del navegador.";
        }
    </script>
</body>
</html>
