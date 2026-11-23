<?php
// developer/bin/cron_recordatorios_acuses.php
// Tarea programada (una vez al día) para acuses de recibo de carnetización:
// 1. Envía recordatorios por correo a los acuses PENDIENTES (máx. 2, separados ~48 h),
//    incluidos los envíos iniciales FALLIDOS (el recordatorio actúa como reintento).
// 2. Aplica SILENCIO ADMINISTRATIVO POSITIVO (CONFIRMADO_TACITO) cuando, después del último
//    recordatorio enviado con éxito, transcurren 5 días hábiles (Colombia) sin confirmación.
//
// Uso:
//   php cron_recordatorios_acuses.php            -> ejecuta (envía correos y actualiza la BD)
//   php cron_recordatorios_acuses.php --simular  -> solo lista lo que haría, sin enviar ni actualizar
//   php cron_recordatorios_acuses.php --limite=50
//
// Cron sugerido (cPanel), con salida a log:
//   0 20 * * * TIC_SIGE_SECRETS_FILE=/home/uybntujx/secrets.json /usr/local/bin/php /home/uybntujx/tic.scv.edu.co/developer/bin/cron_recordatorios_acuses.php >> /home/uybntujx/cron_acuses.log 2>&1

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

date_default_timezone_set('America/Bogota');

$opciones = getopt('', ['simular', 'limite::']);
$simular = isset($opciones['simular']);
$limite = isset($opciones['limite']) ? max(1, (int)$opciones['limite']) : 200;

$lockPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tic_recordatorios_acuses.lock';
$lock = fopen($lockPath, 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    fwrite(STDERR, "Ya existe una ejecución de recordatorios de acuses en curso.\n");
    exit(2);
}

require_once(__DIR__ . '/../Config/PDOconn.php');
require_once(__DIR__ . '/../Services/MailAcuseService.php');

function registrarLinea($texto) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $texto . PHP_EOL;
}

$codigoSalida = 0;
$mail = null;

try {
    $servicio = new MailAcuseService();
    registrarLinea('=== Recordatorios y silencio positivo de acuses' . ($simular ? ' (SIMULACIÓN)' : '') . ' ===');

    // 1. Silencio positivo primero: así un acuse que ya cumplió su plazo no recibe más correos.
    if ($simular) {
        registrarLinea('Silencio positivo: omitido en simulación.');
    } else {
        $tacitos = $servicio->aplicarSilencioPositivo();
        registrarLinea('Silencio positivo aplicado a ' . count($tacitos) . ' acuse(s)' . (count($tacitos) ? ': ' . implode(', ', $tacitos) : '') . '.');
    }

    // 2. Recordatorios
    $pendientes = $servicio->obtenerPendientesParaRecordatorio($limite);
    registrarLinea('Acuses elegibles para recordatorio: ' . count($pendientes) . '.');

    $enviados = 0;
    $fallidos = 0;

    if (!$simular && count($pendientes) > 0) {
        $mail = $servicio->crearMailer(); // una conexión SMTP reutilizada (SMTPKeepAlive)
    }

    foreach ($pendientes as $registro) {
        $numero = (int)$registro['intentos_recordatorio'] + 1;
        if ($simular) {
            registrarLinea("  [simulado] Recordatorio #$numero -> {$registro['destinatario_email']} (acuse {$registro['id']}, envío inicial {$registro['fecha_envio']}, estado_envio {$registro['estado_envio']})");
            continue;
        }

        $resultado = $servicio->enviarRecordatorio($registro, $mail);
        if ($resultado['ok']) {
            $enviados++;
            registrarLinea("  OK    Recordatorio #$numero -> {$registro['destinatario_email']} (acuse {$registro['id']})");
        } else {
            $fallidos++;
            registrarLinea("  ERROR Recordatorio #$numero -> {$registro['destinatario_email']} (acuse {$registro['id']}): {$resultado['error']}");
        }
        usleep(300000); // pausa corta para no saturar el SMTP
    }

    registrarLinea("Recordatorios enviados: $enviados. Fallidos: $fallidos.");
    registrarLinea('=== Fin ===');
    $codigoSalida = $fallidos > 0 ? 1 : 0;

} catch (Throwable $e) {
    registrarLinea('ERROR CRON RECORDATORIOS: ' . $e->getMessage());
    error_log('cron_recordatorios_acuses: ' . $e->getMessage());
    $codigoSalida = 1;
}

if (is_object($mail)) {
    try { $mail->smtpClose(); } catch (Throwable $e) { /* sin acción */ }
}
flock($lock, LOCK_UN);
fclose($lock);
exit($codigoSalida);
