<?php
require_once(__DIR__ . '/../Config/PDOconn.php');
require_once(__DIR__ . '/Q10Gateway.php');

/** Sondea cada 15 minutos únicamente estudiantes con carnet físico proyectado. */
class Q10AcademicPoller {
    private $pdo;
    private $gateway;

    public function __construct($gateway = null) {
        $this->pdo = new PDO(connstring, user, pass, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ));
        $this->gateway = $gateway ?: new Q10Gateway();
    }

    public function ejecutar($limite = 10) {
        $lock = $this->pdo->query("SELECT GET_LOCK('tic_q10_academic_poll', 0)")->fetchColumn();
        if ((int) $lock !== 1) {
            throw new RuntimeException('Ya existe un sondeo Q10 global en ejecución.');
        }
        try {
            return $this->ejecutarProtegido($limite);
        } finally {
            try {
                $this->pdo->query("SELECT RELEASE_LOCK('tic_q10_academic_poll')")->fetchColumn();
            } catch (Throwable $ignored) {
                error_log('No fue posible liberar explícitamente el bloqueo global Q10.');
            }
        }
    }

    private function ejecutarProtegido($limite = 10) {
        $limite = max(1, min(25, (int) $limite));
        $this->sembrarPoblacion();
        $resumen = array('consultados' => 0, 'cambios_encolados' => 0, 'errores' => 0,
            'no_encontrados_pendientes' => 0, 'codigo_control' => 'Q10_CONTROL_NO_EJECUTADO');

        $documentos = $this->documentosHabilitados();
        $control = $this->gateway->validarPoblacion($documentos);
        $resumen['codigo_control'] = $control['codigo'];
        if (!$control['disponible']) {
            $this->registrarFalloControl($control);
            $resumen['errores'] = max(1, count($documentos));
            return $resumen;
        }

        $filas = $this->candidatos($limite);
        $erroresConsecutivos = 0;
        foreach ($filas as $fila) {
            $resultado = $this->gateway->consultar($fila['numero_documento']);
            $resumen['consultados']++;
            if (!$resultado['disponible']) {
                $this->registrarFallo($fila, $resultado);
                $resumen['errores']++;
                $erroresConsecutivos++;
                if ($erroresConsecutivos >= 3) break;
                continue;
            }
            $erroresConsecutivos = 0;
            if (!$resultado['encontrado']) {
                $repeticiones = $this->siguienteRepeticionNoEncontrado($fila);
                if ($repeticiones < 2) {
                    $this->guardarCandidatoNoEncontrado($fila, $resultado, $repeticiones);
                    $resumen['no_encontrados_pendientes']++;
                    continue;
                }
            }
            if ($this->confirmarEstado($fila, $resultado)) {
                $resumen['cambios_encolados']++;
            }
        }
        return $resumen;
    }

    private function siguienteRepeticionNoEncontrado(array $fila) {
        return $fila['candidato_estado'] === 'NO_ENCONTRADO'
            ? ((int) $fila['candidato_repeticiones'] + 1) : 1;
    }

    private function sembrarPoblacion() {
        $this->pdo->exec(
            "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_sige_q10_poblacion (
                id_inscripcion INT NOT NULL PRIMARY KEY,
                numero_documento VARCHAR(50) NOT NULL
             ) ENGINE=MEMORY"
        );
        $this->pdo->exec("DELETE FROM tmp_sige_q10_poblacion");
        $this->pdo->exec(
            "INSERT INTO tmp_sige_q10_poblacion (id_inscripcion, numero_documento)
             SELECT c.id_inscripcion, c.identificacion
             FROM (
                 SELECT i.id_inscripcion, i.identificacion,
                        ROW_NUMBER() OVER (
                            PARTITION BY REGEXP_REPLACE(UPPER(TRIM(i.identificacion)), '[^A-Z0-9]', '')
                            ORDER BY (p.id_inscripcion IS NOT NULL) DESC, i.id_inscripcion DESC
                        ) AS orden_documento
                 FROM inscripcion i
                 LEFT JOIN sige_carnet_proyeccion p
                   ON p.id_inscripcion = i.id_inscripcion AND p.es_actual = 1
                 WHERE (p.id_inscripcion IS NOT NULL
                        OR (i.uid_rfid IS NOT NULL AND TRIM(i.uid_rfid) <> ''))
                   AND i.identificacion IS NOT NULL
                   AND REGEXP_REPLACE(UPPER(TRIM(i.identificacion)), '[^A-Z0-9]', '') <> ''
             ) c
             WHERE c.orden_documento = 1"
        );
        $this->pdo->beginTransaction();
        try {
            $this->pdo->exec(
                "INSERT INTO sige_q10_snapshot
                    (id_inscripcion, numero_documento, habilitado, proximo_sondeo_en, creado_en, actualizado_en)
                 SELECT id_inscripcion, numero_documento, 1, NOW(), NOW(), NOW()
                 FROM tmp_sige_q10_poblacion
                 ON DUPLICATE KEY UPDATE
                    candidato_estado = IF(habilitado = 0 OR sige_q10_snapshot.numero_documento <> VALUES(numero_documento), NULL, candidato_estado),
                    candidato_repeticiones = IF(habilitado = 0 OR sige_q10_snapshot.numero_documento <> VALUES(numero_documento), 0, candidato_repeticiones),
                    ultimo_estado_observado = IF(habilitado = 0 OR sige_q10_snapshot.numero_documento <> VALUES(numero_documento), NULL, ultimo_estado_observado),
                    estado_encolado_sige = IF(habilitado = 0 OR sige_q10_snapshot.numero_documento <> VALUES(numero_documento), NULL, estado_encolado_sige),
                    errores_consecutivos = IF(habilitado = 0 OR sige_q10_snapshot.numero_documento <> VALUES(numero_documento), 0, errores_consecutivos),
                    proximo_sondeo_en = IF(habilitado = 0 OR sige_q10_snapshot.numero_documento <> VALUES(numero_documento), NOW(), proximo_sondeo_en),
                    numero_documento = VALUES(numero_documento), habilitado = 1, actualizado_en = NOW()"
            );
            $this->pdo->exec(
                "UPDATE sige_q10_snapshot s
                 LEFT JOIN tmp_sige_q10_poblacion t ON t.id_inscripcion = s.id_inscripcion
                 SET s.habilitado = 0, s.actualizado_en = NOW()
                 WHERE t.id_inscripcion IS NULL AND s.habilitado <> 0"
            );
            $this->pdo->commit();
        } catch (Throwable $ex) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $ex;
        }
    }

    private function documentosHabilitados() {
        return $this->pdo->query(
            "SELECT numero_documento FROM sige_q10_snapshot WHERE habilitado = 1 ORDER BY id_inscripcion"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    private function candidatos($limite) {
        $sql = "SELECT * FROM sige_q10_snapshot
                WHERE habilitado = 1 AND proximo_sondeo_en <= NOW()
                ORDER BY proximo_sondeo_en, id_inscripcion LIMIT " . (int) $limite;
        return $this->pdo->query($sql)->fetchAll();
    }

    private function registrarFalloControl(array $resultado) {
        $stmt = $this->pdo->prepare(
            "UPDATE sige_q10_snapshot SET ultimo_sondeo_en = NOW(),
             proximo_sondeo_en = DATE_ADD(NOW(), INTERVAL 15 MINUTE),
             errores_consecutivos = errores_consecutivos + 1,
             candidato_estado = NULL, candidato_repeticiones = 0,
             ultimo_codigo = :codigo, ultimo_http_code = :http, actualizado_en = NOW()
             WHERE habilitado = 1"
        );
        $stmt->execute(array('codigo' => $resultado['codigo'], 'http' => $resultado['http_code']));
    }

    private function registrarFallo(array $fila, array $resultado) {
        $errores = (int) $fila['errores_consecutivos'] + 1;
        $minutos = min(120, 15 * (1 << min(3, $errores - 1)));
        $stmt = $this->pdo->prepare(
            "UPDATE sige_q10_snapshot SET ultimo_sondeo_en = NOW(),
             proximo_sondeo_en = DATE_ADD(NOW(), INTERVAL :minutos MINUTE),
             errores_consecutivos = :errores, ultimo_codigo = :codigo,
             ultimo_http_code = :http, candidato_estado = NULL, candidato_repeticiones = 0,
             actualizado_en = NOW() WHERE id_inscripcion = :id"
        );
        $stmt->execute(array('minutos' => $minutos, 'errores' => $errores,
            'codigo' => $resultado['codigo'], 'http' => $resultado['http_code'], 'id' => $fila['id_inscripcion']));
    }

    private function guardarCandidatoNoEncontrado(array $fila, array $resultado, $repeticiones) {
        $stmt = $this->pdo->prepare(
            "UPDATE sige_q10_snapshot SET ultimo_sondeo_en = NOW(),
             proximo_sondeo_en = DATE_ADD(NOW(), INTERVAL 15 MINUTE),
             candidato_estado = 'NO_ENCONTRADO', candidato_repeticiones = :repeticiones,
             errores_consecutivos = 0, ultimo_codigo = :codigo, ultimo_http_code = :http,
             actualizado_en = NOW() WHERE id_inscripcion = :id"
        );
        $stmt->execute(array('repeticiones' => $repeticiones, 'codigo' => $resultado['codigo'],
            'http' => $resultado['http_code'], 'id' => $fila['id_inscripcion']));
    }

    private function confirmarEstado(array $fila, array $resultado) {
        $estado = $resultado['estado'];
        $cambio = $fila['estado_encolado_sige'] !== $estado;
        $this->pdo->beginTransaction();
        try {
            if ($cambio) {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO sige_estudiante_outbox
                        (id_operacion, id_inscripcion, evento, estado_academico_fijado, estado,
                         intentos, proximo_intento_en, creado_en, actualizado_en)
                     VALUES (LOWER(UUID()), :id, 'Q10_ESTADO', :academico, 'PENDIENTE', 0, NOW(), NOW(), NOW())"
                );
                $stmt->execute(array('id' => $fila['id_inscripcion'], 'academico' => $estado));
            }
            $update = $this->pdo->prepare(
                "UPDATE sige_q10_snapshot SET ultimo_estado_observado = :estado,
                 estado_encolado_sige = CASE WHEN :cambio = 1 THEN :estado2 ELSE estado_encolado_sige END,
                 ultimo_sondeo_en = NOW(), proximo_sondeo_en = DATE_ADD(NOW(), INTERVAL 15 MINUTE),
                 candidato_estado = NULL, candidato_repeticiones = 0, errores_consecutivos = 0,
                 ultimo_codigo = :codigo, ultimo_http_code = :http, actualizado_en = NOW()
                 WHERE id_inscripcion = :id"
            );
            $update->execute(array('estado' => $estado, 'cambio' => $cambio ? 1 : 0, 'estado2' => $estado,
                'codigo' => $resultado['codigo'], 'http' => $resultado['http_code'], 'id' => $fila['id_inscripcion']));
            $this->pdo->commit();
            return $cambio;
        } catch (Throwable $ex) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $ex;
        }
    }
}
