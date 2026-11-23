<?php
// developer/tests/acuses_recordatorios_test.php
// Prueba de días hábiles (festivos Colombia) y de la lógica de recordatorios / silencio positivo.
// La parte de BD corre dentro de una transacción que se revierte y usa un mailer falso: no envía correos.
// Uso: php developer/tests/acuses_recordatorios_test.php

date_default_timezone_set('America/Bogota');
require_once(__DIR__ . '/../Services/DiasHabilesColombia.php');

$fallos = 0;
function comprobar($condicion, $descripcion) {
    global $fallos;
    echo ($condicion ? '[OK]   ' : '[FALLA] ') . $descripcion . PHP_EOL;
    if (!$condicion) { $fallos++; }
}

// ---------------- Días hábiles ----------------
$esperados2026 = ['2026-01-01', '2026-01-12', '2026-03-23', '2026-04-02', '2026-04-03', '2026-05-01',
    '2026-05-18', '2026-06-08', '2026-06-15', '2026-06-29', '2026-07-20', '2026-08-07', '2026-08-17',
    '2026-10-12', '2026-11-02', '2026-11-16', '2026-12-08', '2026-12-25'];
$calculados = array_keys(DiasHabilesColombia::festivos(2026));
sort($calculados);
comprobar($calculados === $esperados2026, 'Festivos de Colombia 2026 (18 fechas)');

// Viernes 13/11/2026 + 5 hábiles: lun 16 festivo -> 17,18,19,20,23 => 23/11/2026
$quinto = DiasHabilesColombia::sumarDiasHabiles(new DateTimeImmutable('2026-11-13 20:00'), 5);
comprobar($quinto->format('Y-m-d') === '2026-11-23', '5 días hábiles desde 13/11/2026 = 23/11/2026 (salta festivo 16/11)');
comprobar(!DiasHabilesColombia::plazoVencido(new DateTimeImmutable('2026-11-13 20:00'), 5, new DateTimeImmutable('2026-11-23 20:00')), 'Plazo NO vencido durante el 5.º día hábil');
comprobar(DiasHabilesColombia::plazoVencido(new DateTimeImmutable('2026-11-13 20:00'), 5, new DateTimeImmutable('2026-11-24 20:00')), 'Plazo vencido al día siguiente del 5.º día hábil');

// ---------------- Lógica de recordatorios (BD local) ----------------
require_once(__DIR__ . '/../Config/PDOconn.php');
require_once(__DIR__ . '/../Services/MailAcuseService.php');

class MailerFalso {
    public $ErrorInfo = '';
    public $enviados = [];
    public $fallarPara = [];
    private $para = '';
    public $Subject = '';
    public function ClearAllRecipients() { $this->para = ''; }
    public function SetFrom($a, $b = '') {}
    public function MsgHTML($html) {}
    public function IsHTML($v) {}
    public function AddAddress($correo) { $this->para = $correo; }
    public function smtpConnect($o = []) { return true; }
    public function Send() {
        if (in_array($this->para, $this->fallarPara, true)) { $this->ErrorInfo = 'SMTP simulado: rechazo'; return false; }
        $this->enviados[] = [$this->para, $this->Subject];
        return true;
    }
}

try {
    $pdo = new PDO(connstring, user, pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $pdo->query("SELECT 1 FROM log_correos_acuses LIMIT 1");
} catch (Throwable $e) {
    echo "[OMITIDO] Pruebas de BD: no hay conexión local o no existe log_correos_acuses (" . $e->getMessage() . ")" . PHP_EOL;
    exit($fallos > 0 ? 1 : 0);
}

$pdo->beginTransaction();
try {
    $sufijo = bin2hex(random_bytes(4));
    $insertar = $pdo->prepare("INSERT INTO log_correos_acuses
        (estudiante_id, destinatario_email, asunto, token_acuse, fecha_envio, estado_envio, estado_acuse, intentos_recordatorio, fecha_ultimo_recordatorio)
        VALUES (NULL, :email, 'Prueba', :token, :envio, :estado_envio, 'PENDIENTE', :intentos, :ult)");
    $crear = function ($etiqueta, $envio, $estadoEnvio = 'ENVIADO', $intentos = 0, $ult = null) use ($insertar, $pdo, $sufijo) {
        $insertar->execute([':email' => "$etiqueta.$sufijo@prueba.test", ':token' => bin2hex(random_bytes(32)),
            ':envio' => $envio, ':estado_envio' => $estadoEnvio, ':intentos' => $intentos, ':ult' => $ult]);
        return (int)$pdo->lastInsertId();
    };
    $hace = function ($expr) { return date('Y-m-d H:i:s', strtotime($expr)); };

    $idAntiguo  = $crear('antiguo', $hace('-50 days'));              // pendiente antiguo sin recordatorios
    $idReciente = $crear('reciente', $hace('-10 hours'));            // aún no toca recordatorio
    $idFallido  = $crear('fallido', $hace('-3 days'), 'FALLIDO');    // envío inicial falló -> reintento
    $idRechazo  = $crear('rechazo', $hace('-3 days'));               // el SMTP rechazará el recordatorio
    $idListo    = $crear('listo', $hace('-30 days'), 'ENVIADO', 2, $hace('-15 days')); // ya cumplió plazo
    $idEnPlazo  = $crear('enplazo', $hace('-6 days'), 'ENVIADO', 2, $hace('-1 days'));  // 2 recordatorios, plazo corriendo
    $idFallido2 = $crear('fallido2', $hace('-30 days'), 'FALLIDO', 2, $hace('-15 days')); // fallido: nunca tácito

    $servicio = new MailAcuseService($pdo);
    $mailer = new MailerFalso();
    $mailer->fallarPara = ["rechazo.$sufijo@prueba.test"];

    // Silencio positivo
    $tacitos = $servicio->aplicarSilencioPositivo();
    comprobar(in_array($idListo, $tacitos, true), 'Tácito aplicado tras 2 recordatorios y 5 días hábiles');
    comprobar(!in_array($idEnPlazo, $tacitos, true), 'Tácito NO aplicado si el plazo sigue corriendo');
    comprobar(!in_array($idAntiguo, $tacitos, true), 'Tácito NO aplicado a pendiente antiguo sin recordatorios');
    comprobar(!in_array($idFallido2, $tacitos, true), 'Tácito NO aplicado a envío FALLIDO');

    // Recordatorios
    $ids = array_map(function ($r) { return (int)$r['id']; }, $servicio->obtenerPendientesParaRecordatorio(1000));
    comprobar(in_array($idAntiguo, $ids, true), 'Pendiente antiguo es elegible para recordatorio');
    comprobar(in_array($idFallido, $ids, true), 'Envío FALLIDO es elegible (reintento)');
    comprobar(!in_array($idReciente, $ids, true), 'Envío de hace 10 h NO es elegible');
    comprobar(!in_array($idEnPlazo, $ids, true), 'Acuse con 2 recordatorios NO recibe más');

    $porId = [];
    foreach ($servicio->obtenerPendientesParaRecordatorio(1000) as $r) { $porId[(int)$r['id']] = $r; }
    $r1 = $servicio->enviarRecordatorio($porId[$idAntiguo], $mailer);
    $r2 = $servicio->enviarRecordatorio($porId[$idFallido], $mailer);
    $r3 = $servicio->enviarRecordatorio($porId[$idRechazo], $mailer);

    $leer = $pdo->prepare("SELECT * FROM log_correos_acuses WHERE id = :id");
    $leer->execute([':id' => $idAntiguo]); $a = $leer->fetch();
    comprobar($r1['ok'] && (int)$a['intentos_recordatorio'] === 1 && $a['fecha_ultimo_recordatorio'] !== null, 'Recordatorio enviado suma intento y fecha');
    $leer->execute([':id' => $idFallido]); $f = $leer->fetch();
    comprobar($r2['ok'] && $f['estado_envio'] === 'ENVIADO', 'Reintento exitoso cambia FALLIDO -> ENVIADO');
    $leer->execute([':id' => $idRechazo]); $x = $leer->fetch();
    comprobar(!$r3['ok'] && (int)$x['intentos_recordatorio'] === 0, 'Recordatorio rechazado NO suma intento');
    comprobar(strpos($mailer->enviados[0][1], 'Recordatorio 1 de 2') === 0, 'Asunto del recordatorio numerado');

    $ids2 = array_map(function ($r) { return (int)$r['id']; }, $servicio->obtenerPendientesParaRecordatorio(1000));
    comprobar(!in_array($idAntiguo, $ids2, true), 'No se repite el recordatorio antes de 46 h');
} finally {
    $pdo->rollBack();
}

echo PHP_EOL . ($fallos === 0 ? 'TODAS LAS PRUEBAS PASARON' : "$fallos PRUEBA(S) FALLARON") . PHP_EOL;
exit($fallos > 0 ? 1 : 0);
