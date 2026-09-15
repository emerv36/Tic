<?php
// developer/bin/diagnostico_estudiante_sige.php
if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

date_default_timezone_set('America/Bogota');
require_once(__DIR__ . '/../Config/PDOconn.php');
require_once(__DIR__ . '/../Config/sige_config.php');
require_once(__DIR__ . '/../Services/SigeStudentOutbox.php');
require_once(__DIR__ . '/../Services/SigeOrdenDispatcher.php');
require_once(__DIR__ . '/../Models/SigeCarnetCompat.php');

$doc = '1090465000';
$uid = '0008272973';

$dbPass = (defined('pass') && pass !== '') ? pass : (getenv('TIC_DB_PASS') ?: 'B.quilla54');

$pdo = new PDO(connstring, user, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

echo "====================================================\n";
echo " PASO 1: VERIFICAR REGISTRO EN TABLA INSCRIPCION\n";
echo "====================================================\n";
$stmt = $pdo->prepare("SELECT id_inscripcion, identificacion, nombre_estudiante, apellido_estudiante, id_programa_inscripcionfk, estado_inscripcion, fecha_inscripcion, chip_carnet, uid_rfid FROM inscripcion WHERE identificacion = :doc");
$stmt->execute([':doc' => $doc]);
$est = $stmt->fetch();

if (!$est) {
    die("ERROR CRÍTICO: El documento $doc NO existe en la tabla inscripcion de TIC.\n");
}
print_r($est);
$idInscripcion = (int) $est['id_inscripcion'];

echo "\n====================================================\n";
echo " PASO 2: VERIFICAR COLA DE ESTUDIANTES (sige_estudiante_outbox)\n";
echo "====================================================\n";
$stmt = $pdo->prepare("SELECT * FROM sige_estudiante_outbox WHERE id_inscripcion = :id ORDER BY id DESC LIMIT 5");
$stmt->execute([':id' => $idInscripcion]);
$outboxEst = $stmt->fetchAll();
if (empty($outboxEst)) {
    echo "AVISO: No hay registros en sige_estudiante_outbox para esta inscripción.\n";
} else {
    print_r($outboxEst);
}

echo "\n====================================================\n";
echo " PASO 3: VERIFICAR COLA DE ÓRDENES (sige_orden_outbox)\n";
echo "====================================================\n";
$stmt = $pdo->prepare("SELECT * FROM sige_orden_outbox WHERE id_inscripcion = :id OR numero_documento = :doc ORDER BY id DESC LIMIT 5");
$stmt->execute([':id' => $idInscripcion, ':doc' => $doc]);
$outboxOrd = $stmt->fetchAll();
if (empty($outboxOrd)) {
    echo "AVISO: No hay órdenes de carnet en sige_orden_outbox para este estudiante.\n";
} else {
    print_r($outboxOrd);
}

echo "\n====================================================\n";
echo " PASO 4: VERIFICAR PROYECCIÓN DE CARNET (sige_carnet_proyeccion)\n";
echo "====================================================\n";
$stmt = $pdo->prepare("SELECT * FROM sige_carnet_proyeccion WHERE id_inscripcion = :id OR numero_documento = :doc");
$stmt->execute([':id' => $idInscripcion, ':doc' => $doc]);
print_r($stmt->fetchAll());

echo "\n====================================================\n";
echo " PASO 5: VERIFICAR BLOQUEOS TEMPORALES (.lock en /tmp)\n";
echo "====================================================\n";
$tmp = sys_get_temp_dir();
$lockFiles = [
    $tmp . '/tic_sige_estudiantes_outbox.lock',
    $tmp . '/tic_sige_outbox.lock'
];
foreach ($lockFiles as $lf) {
    if (file_exists($lf)) {
        echo "Lock detectado: $lf (Modificado: " . date('Y-m-d H:i:s', filemtime($lf)) . ")\n";
    } else {
        echo "Lock libre: $lf\n";
    }
}

echo "\n====================================================\n";
echo " PASO 6: ACCIÓN DE ERRADICACIÓN Y DISPARO FORZADO\n";
echo "====================================================\n";

// 6.1 Forzar sincronización del perfil del estudiante a SIGE
echo "-> 6.1 Enviando perfil del estudiante a SIGE...\n";
$studentOutbox = new SigeStudentOutbox();
try {
    $enviadoEst = $studentOutbox->enviarEstudiante($idInscripcion, 'ACTUALIZAR');
    echo "Resultado envio estudiante: " . ($enviadoEst ? "EXITOSO (ENVIADA)" : "PENDIENTE / REINTENTO") . "\n";
} catch (Throwable $e) {
    echo "Excepción enviando estudiante: " . $e->getMessage() . "\n";
}

// 6.2 Crear o re-despachar la orden de carnet con el UID físico
echo "\n-> 6.2 Generando / Despachando orden de carnet (RFID: $uid)...\n";
$compat = new SigeCarnetCompat();
try {
    $orden = $compat->crearOrdenCarnet(
        'ASIGNACION',
        $idInscripcion,
        $uid,
        'NO_APLICA',
        ['id' => '1', 'nombre' => 'ADMINISTRADOR', 'codigo_rol' => 1],
        true
    );
    echo "Orden generada exitosamente:\n";
    print_r($orden);
} catch (DomainException $de) {
    echo "Aviso de negocio en orden: " . $de->getMessage() . "\n";
    if (strpos($de->getMessage(), 'Ya existe') !== false) {
        echo "Intentando como REEMPLAZO...\n";
        try {
            $orden = $compat->crearOrdenCarnet(
                'REEMPLAZO',
                $idInscripcion,
                $uid,
                'DETERIORO',
                ['id' => '1', 'nombre' => 'ADMINISTRADOR', 'codigo_rol' => 1],
                true
            );
            print_r($orden);
        } catch (Throwable $re) {
            echo "Error en reemplazo: " . $re->getMessage() . "\n";
        }
    }
} catch (Throwable $e) {
    echo "Excepción generando orden: " . $e->getMessage() . "\n";
}

// 6.3 Vaciar colas pendientes hacia SIGE de inmediato
echo "\n-> 6.3 Vaciando colas hacia SIGE...\n";
try {
    $resEst = $studentOutbox->procesarPendientes(10);
    echo "Resumen salida estudiantes: " . json_encode($resEst) . "\n";
} catch (Throwable $e) {
    echo "Error vaciando outbox estudiantes: " . $e->getMessage() . "\n";
}

try {
    $dispatcher = new SigeOrdenDispatcher();
    $resOrd = $dispatcher->procesarPendientes(10);
    echo "Órdenes de carnet despachadas: " . $resOrd . "\n";
} catch (Throwable $e) {
    echo "Error vaciando outbox órdenes: " . $e->getMessage() . "\n";
}

echo "\n====================================================\n";
echo " AUDITORÍA FINALIZADA\n";
echo "====================================================\n";
