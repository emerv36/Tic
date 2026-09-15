<?php

date_default_timezone_set('America/Bogota');

require_once dirname(__DIR__) . '/Models/SigeAccesos.php';
require_once dirname(__DIR__) . '/Models/SigeCarnetCompat.php';
require_once dirname(__DIR__) . '/Services/SigeReconciliationWorker.php';
require_once dirname(__DIR__) . '/Services/Q10AcademicPoller.php';

function assertSameLegacyBehavior($expected, $actual, $label) {
    if ($expected !== $actual) {
        throw new RuntimeException($label . ': esperado ' . var_export($expected, true) . ', recibido ' . var_export($actual, true));
    }
}

function invokePrivateLegacyBehavior($object, $method, array $args = array()) {
    $reflection = new ReflectionObject($object);
    $target = $reflection->getMethod($method);
    $target->setAccessible(true);
    return $target->invokeArgs($object, $args);
}

function setPrivateLegacyBehavior($object, $property, $value) {
    $reflection = new ReflectionObject($object);
    $target = $reflection->getProperty($property);
    $target->setAccessible(true);
    $target->setValue($object, $value);
}

// Ejecuta las validaciones públicas de creación de comandos sin abrir conexión.
$carnetReflection = new ReflectionClass(SigeCarnetCompat::class);
$carnet = $carnetReflection->newInstanceWithoutConstructor();
try {
    $carnet->crearOrdenCarnet('ASIGNACION', 1, 'UID-1', null, array(), false);
    throw new RuntimeException('Se aceptó un comando sin confirmación.');
} catch (DomainException $exception) {
    assertSameLegacyBehavior('La operación requiere confirmación explícita.', $exception->getMessage(), 'Confirmación de comando');
}
try {
    $carnet->crearOrdenCarnet('NO_SOPORTADO', 1, 'UID-1', null, array(), true);
    throw new RuntimeException('Se aceptó un comando no soportado.');
} catch (DomainException $exception) {
    assertSameLegacyBehavior('Tipo de orden no soportado.', $exception->getMessage(), 'Tipo de comando');
}

// Ejecuta la idempotencia de acceso contra un doble del repositorio legacy.
class SigeAccesosBehaviorProbe extends SigeAccesos {
    public $existingRow = null;
    public $lastParams = null;
    public function row($sql, $params = null) {
        $this->lastParams = $params;
        return $this->existingRow;
    }
}
$access = new SigeAccesosBehaviorProbe();
assertSameLegacyBehavior(false, $access->existeRegistro(501), 'Acceso nuevo');
$access->existingRow = array('id' => 9);
assertSameLegacyBehavior(true, $access->existeRegistro(501), 'Acceso duplicado');
assertSameLegacyBehavior(array(':sige_id' => 501), $access->lastParams, 'Correlación de acceso');

// Ejecuta la decisión de doble NO_ENCONTRADO usada por el poller.
$pollerReflection = new ReflectionClass(Q10AcademicPoller::class);
$poller = $pollerReflection->newInstanceWithoutConstructor();
$primeraAusencia = invokePrivateLegacyBehavior($poller, 'siguienteRepeticionNoEncontrado', array(array(
    'candidato_estado' => null,
    'candidato_repeticiones' => 0
)));
assertSameLegacyBehavior(1, $primeraAusencia, 'Primera ausencia Q10 pendiente');
$segundaAusencia = invokePrivateLegacyBehavior($poller, 'siguienteRepeticionNoEncontrado', array(array(
    'candidato_estado' => 'NO_ENCONTRADO',
    'candidato_repeticiones' => $primeraAusencia
)));
assertSameLegacyBehavior(2, $segundaAusencia, 'Segunda ausencia Q10 confirmable');

// Ejecuta reconciliación de versión menor/igual sobre un repositorio SQLite aislado.
$reconciliationPdo = new PDO('sqlite::memory:');
$reconciliationPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$reconciliationPdo->sqliteCreateFunction('NOW', function () { return '2026-08-30 16:30:00'; });
$reconciliationPdo->exec(
    'CREATE TABLE sige_reconciliacion_conflicto (
        ejecucion_id INTEGER NULL, numero_documento TEXT NULL, tipo TEXT NOT NULL,
        version_tic INTEGER NULL, version_sige INTEGER NULL, detalle TEXT,
        respuesta_hash TEXT NULL, detectado_en TEXT, ultima_deteccion_en TEXT, resuelto INTEGER
    )'
);
$reconciliationReflection = new ReflectionClass(SigeReconciliationWorker::class);
$reconciliation = $reconciliationReflection->newInstanceWithoutConstructor();
setPrivateLegacyBehavior($reconciliation, 'pdo', $reconciliationPdo);
setPrivateLegacyBehavior($reconciliation, 'ejecucionId', 1);
$lote = array(array('id' => 1, 'id_inscripcion' => 9001, 'numero_documento' => '100200300', 'version_estado' => 5));
$menor = array('http_code' => 200, 'respuesta' => json_encode(array(
    'status' => 'PROCESADO',
    'estudiantes' => array(array(
        'numero_documento' => '100200300', 'version_estado' => 4,
        'requiere_reactivacion' => false, 'carnet' => null
    )),
    'no_encontrados' => array()
)));
$resultadoMenor = invokePrivateLegacyBehavior($reconciliation, 'procesarRespuesta', array($lote, $menor));
assertSameLegacyBehavior(array('actualizados' => 0, 'conflictos' => 1), $resultadoMenor, 'Reconciliación no decrementa');
assertSameLegacyBehavior('SIGE_VERSION_MENOR', $reconciliationPdo->query('SELECT tipo FROM sige_reconciliacion_conflicto')->fetchColumn(), 'Conflicto de versión menor');
$igual = $menor;
$igual['respuesta'] = str_replace('"version_estado":4', '"version_estado":5', $menor['respuesta']);
$resultadoIgual = invokePrivateLegacyBehavior($reconciliation, 'procesarRespuesta', array($lote, $igual));
assertSameLegacyBehavior(array('actualizados' => 0, 'conflictos' => 0), $resultadoIgual, 'Reconciliación de versión igual');

// Ejecuta la ruta real de evento obsoleto con SQLite; solo sustituye la conexión.
class LegacyBehaviorSqlitePdo extends PDO {
    public function __construct() {
        parent::__construct('sqlite::memory:');
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    public function prepare($query, $options = array()): PDOStatement|false {
        return parent::prepare(str_replace(' FOR UPDATE', '', $query), $options);
    }
}
$eventPdo = new LegacyBehaviorSqlitePdo();
$eventPdo->exec('CREATE TABLE inscripcion (id_inscripcion INTEGER PRIMARY KEY, identificacion TEXT)');
$eventPdo->exec("INSERT INTO inscripcion VALUES (9001, '100200300')");
$eventPdo->exec(
    'CREATE TABLE sige_carnet_evento (
        id_evento TEXT PRIMARY KEY, tipo_evento TEXT, version_estado INTEGER, origen TEXT,
        emergency_id TEXT NULL, id_inscripcion INTEGER NULL, numero_documento TEXT,
        sige_carnet_id INTEGER, uid_rfid TEXT, ocurrido_en TEXT, payload TEXT,
        payload_hash TEXT, procesado INTEGER, error_proceso TEXT NULL
    )'
);
$eventPdo->exec(
    'CREATE TABLE sige_carnet_proyeccion (
        id INTEGER PRIMARY KEY, sige_carnet_id INTEGER, id_inscripcion INTEGER,
        numero_documento TEXT, version_estado INTEGER, es_actual INTEGER
    )'
);
$eventPdo->exec("INSERT INTO sige_carnet_proyeccion VALUES (1, 77, 9001, '100200300', 5, 1)");
$source = file_get_contents(dirname(__DIR__) . '/Models/SigeCarnetCompat.php');
$source = preg_replace('/class SigeCarnetCompat\s*\{/', 'class SigeCarnetCompatBehaviorHarness {', $source, 1);
$source = preg_replace(
    '/    private function conexion\(\) \{.*?^    \}/ms',
    "    private function conexion() { return \$GLOBALS['legacyBehaviorEventPdo']; }",
    $source,
    1,
    $replaceCount
);
if ($replaceCount !== 1) throw new RuntimeException('No se pudo instrumentar la conexión del receptor de eventos.');
$source = preg_replace('/^<\?php\s*/', '', $source, 1);
$GLOBALS['legacyBehaviorEventPdo'] = $eventPdo;
eval($source);
$eventReceiver = new SigeCarnetCompatBehaviorHarness();
$obsoleteEvent = array(
    'id_evento' => '123e4567-e89b-42d3-a456-426614174120',
    'tipo' => 'CARNET_BLOQUEADO',
    'version_estado' => 4,
    'origen' => 'SIGE',
    'ocurrido_en' => '2026-08-30 16:30:00',
    'estudiante' => array('tic_id_inscripcion' => 9001, 'numero_documento' => '100200300'),
    'carnet' => array(
        'sige_carnet_id' => 77, 'uid_rfid' => 'UID-1', 'estado' => 'INACTIVO',
        'motivo' => 'BLOQUEO_OPERATIVO', 'fecha_emision' => '2026-08-01',
        'vigencia_hasta' => '2027-02-01'
    )
);
$obsoleteResult = $eventReceiver->registrarEventoYProyectar($obsoleteEvent);
assertSameLegacyBehavior('OBSOLETO_IGNORADO', $obsoleteResult['status'], 'Evento obsoleto ejecutado');
assertSameLegacyBehavior(5, (int) $eventPdo->query('SELECT version_estado FROM sige_carnet_proyeccion WHERE id = 1')->fetchColumn(), 'Proyección no regresa');
assertSameLegacyBehavior('EVENTO_OBSOLETO_IGNORADO', $eventPdo->query('SELECT error_proceso FROM sige_carnet_evento')->fetchColumn(), 'Evento obsoleto auditado');

echo "legacy_integration_behavior_test: OK\n";
