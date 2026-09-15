<?php
require_once(__DIR__ . '/../Services/PersonalOutboxDispatcher.php');

class TestPersonalOutboxDispatcher extends PersonalOutboxDispatcher {
    public $pdoMock;
    public $responses = [];
    public $requests = [];

    // Override para usar SQLite en memoria
    public function __construct(PDO $pdo) {
        $this->pdoMock = $pdo;
    }

    // Usamos reflection para sobreescribir conexion privada
    public function procesarPendientesTest($limite = 25) {
        $reflector = new ReflectionClass(PersonalOutboxDispatcher::class);
        $metodoConexion = $reflector->getMethod('conexion');
        $metodoConexion->setAccessible(true);
        
        // Simular conexion devolviendo pdoMock
        $pdo = $this->pdoMock;
        
        // Expirar leases
        $pdo->exec(
            "UPDATE sige_personal_outbox
             SET estado = 'REINTENTO', proximo_intento_en = CURRENT_TIMESTAMP,
                 ultimo_error = 'Lease ENVIANDO expirado', actualizado_en = CURRENT_TIMESTAMP
             WHERE estado = 'ENVIANDO'
               AND actualizado_en < datetime(CURRENT_TIMESTAMP, '-10 minutes')" // SQLite dialect for DATE_SUB
        );
        
        $procesadas = 0;
        while ($procesadas < $limite) {
            $orden = $this->reclamarSiguienteMock($pdo);
            if (!$orden) {
                break;
            }
            // enviarYFinalizar is private, we must call it using reflection
            $metodoEnviar = $reflector->getMethod('enviarYFinalizar');
            $metodoEnviar->setAccessible(true);
            $metodoEnviar->invoke($this, $pdo, $orden);
            $procesadas++;
        }
        return $procesadas;
    }
    
    private function reclamarSiguienteMock(PDO $pdo) {
        $pdo->beginTransaction();
        try {
            // SQLite no soporta FOR UPDATE, lo quitamos para el test
            $stmt = $pdo->query(
                "SELECT o.* FROM sige_personal_outbox o
                 WHERE o.estado IN ('PENDIENTE','REINTENTO')
                   AND o.proximo_intento_en <= CURRENT_TIMESTAMP
                   AND NOT EXISTS (
                       SELECT 1 FROM sige_personal_outbox prev
                       WHERE prev.persona_uuid = o.persona_uuid
                         AND prev.id < o.id
                         AND prev.estado NOT IN ('ENVIADA', 'FALLIDA', 'CONFLICTO_OBSOLETO')
                   )
                 ORDER BY o.id ASC LIMIT 1"
            );
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$orden) {
                $pdo->commit();
                return null;
            }
            
            $update = $pdo->prepare(
                "UPDATE sige_personal_outbox
                 SET estado = 'ENVIANDO', intentos = intentos + 1, actualizado_en = CURRENT_TIMESTAMP
                 WHERE id = :id"
            );
            $update->execute(array(':id' => $orden['id']));
            $pdo->commit();
            
            $orden['intentos'] = (int) $orden['intentos'] + 1;
            return $orden;
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }
    }

    protected function postJson($payload, $comando = null) {
        $this->requests[] = array('payload' => $payload, 'comando' => $comando);
        return array_shift($this->responses) ?: array('http_code' => 500, 'respuesta' => 'Fallback error');
    }

    public function urlFor($comando) {
        return $this->resolverUrl($comando);
    }
}

function runDispatcherTests() {
    date_default_timezone_set('UTC');
    $pdo = new PDO('sqlite::memory:', '', '', array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ));

    $pdo->exec("
        CREATE TABLE sige_personal_outbox (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_operacion TEXT NOT NULL,
            comando TEXT NULL,
            tipo TEXT NOT NULL,
            persona_uuid TEXT,
            payload TEXT NOT NULL,
            estado TEXT NOT NULL DEFAULT 'PENDIENTE',
            intentos INTEGER NOT NULL DEFAULT 0,
            proximo_intento_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ultimo_http_code INTEGER NULL,
            ultimo_error TEXT NULL,
            creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            enviado_en DATETIME NULL
        )
    ");

    $dispatcher = new TestPersonalOutboxDispatcher($pdo);

    // Test 1: Envío exitoso
    $pdo->exec("INSERT INTO sige_personal_outbox (id_operacion, comando, tipo, persona_uuid, payload) VALUES ('op-1', 'CARNET', 'ASIGNACION', 'uuid-1', '{}')");
    $dispatcher->responses[] = array('http_code' => 202, 'respuesta' => json_encode(array('status' => 'ACEPTADO', 'id_operacion' => 'op-1')));
    
    $procesadas = $dispatcher->procesarPendientesTest(5);
    if ($procesadas !== 1) throw new Exception("Test 1: Debería procesar 1 orden");
    
    $orden = $pdo->query("SELECT * FROM sige_personal_outbox WHERE id_operacion = 'op-1'")->fetch(PDO::FETCH_ASSOC);
    if ($orden['estado'] !== 'ENVIADA') throw new Exception("Test 1: Estado debe ser ENVIADA");
    if (($dispatcher->requests[0]['comando'] ?? null) !== 'CARNET') throw new Exception("Test 1: No preservó el tipo de endpoint");
    if ($dispatcher->urlFor('CARNET') !== SIGE_PERSONAL_CARNET_COMMAND_URL) throw new Exception("Test 1: CARNET no usa su endpoint dedicado");
    if ($dispatcher->urlFor('PERSONAL_SYNC') !== SIGE_PERSONAL_COMMAND_URL) throw new Exception("Test 1: PERSONAL_SYNC no usa el endpoint compacto");
    if ($dispatcher->urlFor('VINCULO_ACTUALIZAR') !== SIGE_PERSONAL_LINK_COMMAND_URL) throw new Exception("Test 1: El contrato canónico de vínculo perdió su endpoint dedicado");
    if ($dispatcher->urlFor('PERSONA') !== SIGE_PERSONAL_COMMAND_URL) throw new Exception("Test 1: Población no usa su endpoint dedicado");
    if ($dispatcher->urlFor('VINCULO_ACTUALIZAR') !== SIGE_PERSONAL_LINK_COMMAND_URL) throw new Exception("Test 1: Vínculo no usa su endpoint directo");

    // Test 2: Error transitorio (ej HTTP 500)
    $pdo->exec("INSERT INTO sige_personal_outbox (id_operacion, tipo, persona_uuid, payload) VALUES ('op-2', 'VINCULO_AGREGAR', 'uuid-2', '{}')");
    $dispatcher->responses[] = array('http_code' => 500, 'respuesta' => 'Internal Server Error');
    $dispatcher->procesarPendientesTest(5);
    
    $orden2 = $pdo->query("SELECT * FROM sige_personal_outbox WHERE id_operacion = 'op-2'")->fetch(PDO::FETCH_ASSOC);
    if ($orden2['estado'] !== 'REINTENTO') throw new Exception("Test 2: Estado debe ser REINTENTO");
    if ((int)$orden2['intentos'] !== 1) throw new Exception("Test 2: Intentos debe ser 1, es: " . $orden2['intentos']);
    // Altera proximo_intento_en para que sea procesable de inmediato y verificamos si se bloquea al límite
    
    // Test 3: Orden estricto por persona
    $pdo->exec("UPDATE sige_personal_outbox SET proximo_intento_en = datetime('now', '+1 hour') WHERE id_operacion = 'op-2'");
    // Insertamos una operacion para uuid-2 (misma persona) mas nueva y con fecha vencida (debería estar bloqueada por op-2 que está PENDIENTE/REINTENTO)
    $pdo->exec("INSERT INTO sige_personal_outbox (id_operacion, tipo, persona_uuid, payload, proximo_intento_en) VALUES ('op-3', 'VINCULO_AGREGAR', 'uuid-2', '{}', CURRENT_TIMESTAMP)");
    
    $procesadas = $dispatcher->procesarPendientesTest(5);
    if ($procesadas !== 0) throw new Exception("Test 3: Debería bloquear op-3 porque op-2 está inconclusa");

    // Resolvemos op-2 artificialmente para que op-3 fluya
    $pdo->exec("UPDATE sige_personal_outbox SET estado = 'ENVIADA' WHERE id_operacion = 'op-2'");
    $dispatcher->responses[] = array('http_code' => 409, 'respuesta' => json_encode(array('status' => 'RECHAZADO', 'id_operacion' => 'op-3', 'codigo' => 'CONFLICTO_OBSOLETO')));
    $procesadas = $dispatcher->procesarPendientesTest(5);
    if ($procesadas !== 1) throw new Exception("Test 3.5: Debería procesar op-3");
    
    $orden3 = $pdo->query("SELECT * FROM sige_personal_outbox WHERE id_operacion = 'op-3'")->fetch(PDO::FETCH_ASSOC);
    if ($orden3['estado'] !== 'CONFLICTO_OBSOLETO') throw new Exception("Test 3.5: Estado debe ser CONFLICTO_OBSOLETO, es: " . $orden3['estado']);

    // Test 4: Error terminal genérico (422 Validación)
    $pdo->exec("INSERT INTO sige_personal_outbox (id_operacion, tipo, persona_uuid, payload) VALUES ('op-4', 'PERSONA_CREAR', 'uuid-4', '{}')");
    $dispatcher->responses[] = array('http_code' => 422, 'respuesta' => json_encode(array('status' => 'RECHAZADO', 'id_operacion' => 'op-4', 'mensaje' => 'Dato inválido')));
    $dispatcher->procesarPendientesTest(5);
    $orden4 = $pdo->query("SELECT * FROM sige_personal_outbox WHERE id_operacion = 'op-4'")->fetch(PDO::FETCH_ASSOC);
    if ($orden4['estado'] !== 'FALLIDA') throw new Exception("Test 4: Estado debe ser FALLIDA, es: " . $orden4['estado']);

    // Test 5: Límite de intentos
    if (!defined('SIGE_OUTBOX_MAX_ATTEMPTS')) define('SIGE_OUTBOX_MAX_ATTEMPTS', 12);
    $pdo->exec("INSERT INTO sige_personal_outbox (id_operacion, tipo, persona_uuid, payload, intentos, proximo_intento_en) VALUES ('op-5', 'PERSONA_CREAR', 'uuid-5', '{}', 11, CURRENT_TIMESTAMP)");
    $dispatcher->responses[] = array('http_code' => 500, 'respuesta' => 'Error transitorio');
    $dispatcher->procesarPendientesTest(5);
    $orden5 = $pdo->query("SELECT * FROM sige_personal_outbox WHERE id_operacion = 'op-5'")->fetch(PDO::FETCH_ASSOC);
    if ($orden5['estado'] !== 'FALLIDA') throw new Exception("Test 5: Estado debe ser FALLIDA por límite de intentos");

    echo "personal_outbox_dispatcher_test: OK\n";
}

runDispatcherTests();
