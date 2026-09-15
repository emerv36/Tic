<?php
/**
 * SigeWebhook - Servicio emisor de webhook para SIGE
 * 
 * Envía datos de estudiantes y practicantes al Sistema de Carnetización y Acceso (SIGE)
 * cuando se crea, actualiza o cambia el estado en TIC.
 * 
 * Uso:
 *   $webhook = new SigeWebhook();
 *   $webhook->enviarEstudiante($id_inscripcion, 'CREAR');
 */
require_once(__DIR__ . '/../Config/PDOconn.php');
require_once(__DIR__ . '/../Config/sige_config.php');
require_once(__DIR__ . '/Q10Gateway.php');

class Q10NoDisponibleException extends RuntimeException {}

class SigeWebhook extends db {

    /**
     * Envía los datos de un estudiante o practicante a SIGE vía webhook HTTP POST.
     * 
     * @param int    $id_inscripcion ID de la inscripción en TIC
     * @param string $evento         Tipo de evento: CREAR, ACTUALIZAR, ENTREGA, ESTADO, CHIP
     * @return bool  true si el envío fue exitoso (HTTP 2xx), false en caso contrario
     */
    public function enviarEstudiante($id_inscripcion, $evento = 'ACTUALIZAR', $idOperacion = null, $estadoAcademicoFijado = null) {
        try {
            if (trim((string) SIGE_API_KEY) === '') {
                throw new RuntimeException('Integración outbound SIGE no configurada.');
            }

            // 1. Consultar datos completos del estudiante / practicante
            $estudiante = $this->obtenerDatosEstudiante($id_inscripcion);

            if (!$estudiante) {
                $this->registrarLog($id_inscripcion, '', $evento, null, 'Estudiante no encontrado', 0, false);
                return false;
            }

            // El sondeo periódico fija el estado observado para evitar una segunda consulta distinta.
            if (in_array($estadoAcademicoFijado, array('ACTIVO', 'INACTIVO'), true)) {
                $estadoAcademico = $estadoAcademicoFijado;
            } else {
                $estadoQ10 = (new Q10Gateway())->consultar($estudiante['identificacion']);
                if (!$estadoQ10['disponible']) {
                    $this->registrarLog(
                        $id_inscripcion, $estudiante['identificacion'], $evento . '_Q10_PENDIENTE',
                        null, $estadoQ10['codigo'], $estadoQ10['http_code'], false
                    );
                    $estadoAcademico = in_array((int)$estudiante['estado_inscripcion'], array(1, 2, 3), true) ? 'ACTIVO' : null;
                } elseif (!$estadoQ10['encontrado']) {
                    $this->registrarLog(
                        $id_inscripcion, $estudiante['identificacion'], $evento . '_Q10_NO_ENCONTRADO_PENDIENTE',
                        null, $estadoQ10['codigo'], $estadoQ10['http_code'], false
                    );
                    $estadoAcademico = in_array((int)$estudiante['estado_inscripcion'], array(1, 2, 3), true) ? 'ACTIVO' : null;
                } else {
                    $estadoAcademico = $estadoQ10['estado'];
                }
            }

            // 3. Construir payload; sin Q10 se omite estado_academico para no regresarlo.
            $payload = $this->construirPayload($estudiante, $estadoAcademico);
            if ($idOperacion !== null) {
                $payload['id_operacion'] = strtolower(trim((string) $idOperacion));
                $payload['evento'] = strtoupper(trim((string) $evento));
            }

            // 4. Enviar la petición HTTP POST a SIGE
            $resultado = $this->enviarHTTP($payload);

            // 5. Registrar en el log de auditoría
            $this->registrarLog(
                $id_inscripcion,
                $estudiante['identificacion'],
                $evento,
                json_encode($payload),
                $resultado['respuesta'],
                $resultado['http_code'],
                $resultado['exitoso']
            );

            return $resultado['exitoso'];

        } catch (Exception $ex) {
            error_log("SigeWebhook Error: " . $ex->getMessage());
            $this->registrarLog(
                $id_inscripcion,
                '',
                $evento,
                null,
                'Exception: ' . $ex->getMessage(),
                0,
                false
            );
            return false;
        }
    }

    /**
     * Obtiene los datos de la inscripción necesarios para el webhook.
     * Utiliza LEFT JOIN para garantizar que inscripciones sin programa o tipo de identidad sean procesadas.
     * 
     * @param int $id_inscripcion
     * @return array|null
     */
    private function obtenerDatosEstudiante($id_inscripcion) {
        $sql = "SELECT i.id_inscripcion,
                       i.identificacion,
                       COALESCE(t.nombre_identidad, 'C.C') AS tipo_documento,
                       i.nombre_estudiante,
                       i.apellido_estudiante,
                       COALESCE(p.nombre_programa, 'PRACTICANTE') AS nombre_programa,
                       COALESCE(i.categoria_carnet, 1) AS categoria_carnet,
                       i.estado_inscripcion,
                       i.fecha_inscripcion,
                       i.chip_carnet,
                       i.uid_rfid,
                       i.codigo_foto,
                       i.celular_estudiante,
                       i.tipo_sangre
                FROM inscripcion i
                LEFT JOIN tipo_identidad t ON i.tipo_identificacionfk = t.id_tipo
                LEFT JOIN programa p ON i.id_programa_inscripcionfk = p.id_programa
                WHERE i.id_inscripcion = :id_inscripcion";
        $params = array(':id_inscripcion' => $id_inscripcion);
        $resultado = $this->row($sql, $params);
        return $resultado;
    }

    private function construirPayload($estudiante, $estadoAcademico) {
        // Resolver nombre del archivo de foto de perfil
        $identificacion = trim((string) $estudiante['identificacion']);
        $codigo_foto = trim((string) ($estudiante['codigo_foto'] ?? ''));
        $archivo_foto = ($codigo_foto !== '' && is_numeric($codigo_foto)) ? $codigo_foto : $identificacion;
        $foto_url = rtrim(TIC_BASE_URL, '/') . '/assets/fotoperfil/' . rawurlencode($archivo_foto) . '.jpg';

        $categoriaId = (int) ($estudiante['categoria_carnet'] ?? 1);
        $tipoCategoria = ($categoriaId === 3) ? 'PRACTICANTE' : (($categoriaId === 2) ? 'FUNCIONARIO' : 'ESTUDIANTE');

        $payload = array(
            'tic_id_inscripcion'    => (int) $estudiante['id_inscripcion'],
            'tipo_documento'       => strtoupper($estudiante['tipo_documento']),
            'numero_documento'     => $estudiante['identificacion'],
            'nombres'              => strtoupper($estudiante['nombre_estudiante']),
            'apellidos'            => strtoupper($estudiante['apellido_estudiante']),
            'programa'             => strtoupper($estudiante['nombre_programa']),
            'categoria_carnet'     => $categoriaId,
            'tipo_categoria'       => $tipoCategoria,
            'foto_url'             => $foto_url,
            'uid_rfid'             => $estudiante['uid_rfid'],
            'celular'              => $this->normalizarCelular($estudiante['celular_estudiante'] ?? null),
            'rh'                   => $this->normalizarRh($estudiante['tipo_sangre'] ?? null)
        );
        if ($estadoAcademico !== null) {
            $payload['estado_academico'] = $estadoAcademico;
        }
        if ($this->esFechaValida($estudiante['fecha_inscripcion'])) {
            $payload['fecha_registro'] = $estudiante['fecha_inscripcion'];
        }
        return $payload;
    }

    private function normalizarCelular($valor) {
        if (!is_string($valor)) return null;
        $normalizado = preg_replace('/[\s\-\(\)]+/u', '', trim($valor));
        return is_string($normalizado) && preg_match('/^3[0-9]{9}$/', $normalizado) ? $normalizado : null;
    }

    private function normalizarRh($valor) {
        $normalizado = strtoupper(trim((string) $valor));
        return in_array($normalizado, array('O+','O-','A+','A-','B+','B-','AB+','AB-'), true)
            ? $normalizado : null;
    }

    private function esFechaValida($valor) {
        if (!$valor || $valor === '0000-00-00 00:00:00') return false;
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $valor);
        return $fecha && $fecha->format('Y-m-d H:i:s') === $valor;
    }

    private function consultarEstadoQ10($identificacion) {
        if (!defined('Q10_API_KEY') || trim((string) Q10_API_KEY) === '') {
            return array('disponible' => false, 'estado' => null, 'codigo' => 'Q10_NO_CONFIGURADO', 'http_code' => 0);
        }

        $url_q10 = "https://api.q10.com/v1/usuarios?Limit=1000&numero_identificacion=" . rawurlencode($identificacion);
        
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url_q10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_HTTPHEADER => array(
                'Api-Key: ' . (defined('Q10_API_KEY') ? Q10_API_KEY : ''),
                'Accept: application/json'
            ),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ));

        $respuesta = curl_exec($ch);
        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($respuesta === false) {
            return array('disponible' => false, 'estado' => null,
                'codigo' => 'Q10_TRANSPORTE_' . ($curlError ? 'ERROR' : 'DESCONOCIDO'), 'http_code' => 0);
        }
        if ($http_code < 200 || $http_code >= 300) {
            return array('disponible' => false, 'estado' => null,
                'codigo' => 'Q10_HTTP_' . $http_code, 'http_code' => $http_code);
        }
        $datos = json_decode($respuesta, true);
        if (!is_array($datos) || json_last_error() !== JSON_ERROR_NONE) {
            return array('disponible' => false, 'estado' => null,
                'codigo' => 'Q10_JSON_INVALIDO', 'http_code' => $http_code);
        }
        foreach (array('data', 'Data', 'usuarios', 'Usuarios') as $contenedor) {
            if (isset($datos[$contenedor]) && is_array($datos[$contenedor])) {
                $datos = $datos[$contenedor];
                break;
            }
        }
        foreach ($datos as $usuario) {
            if (!is_array($usuario)) continue;
            $numero = $usuario['Numero_identificacion'] ?? $usuario['numero_identificacion'] ?? null;
            if ((string) $numero !== (string) $identificacion) continue;
            $roles = $usuario['Roles'] ?? $usuario['roles'] ?? array();
            foreach ($roles as $rol) {
                $nombre = $rol['Nombre'] ?? $rol['nombre'] ?? '';
                $activo = $rol['Estado'] ?? $rol['estado'] ?? false;
                if ($nombre === 'Estudiante' && filter_var($activo, FILTER_VALIDATE_BOOLEAN)) {
                    return array('disponible' => true, 'estado' => 'ACTIVO',
                        'codigo' => 'Q10_OK', 'http_code' => $http_code);
                }
            }
            return array('disponible' => true, 'estado' => 'INACTIVO',
                'codigo' => 'Q10_OK', 'http_code' => $http_code);
        }
        return array('disponible' => true, 'estado' => 'INACTIVO',
            'codigo' => 'Q10_NO_ENCONTRADO', 'http_code' => $http_code);
    }

    /**
     * Envía una petición HTTP POST con cURL al endpoint de SIGE.
     * Incluye el header de Authorization con Bearer token.
     * 
     * @param array $payload Datos a enviar como JSON
     * @return array Con llaves: exitoso (bool), http_code (int), respuesta (string)
     */
    private function enviarHTTP($payload) {
        $json_payload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL            => SIGE_WEBHOOK_URL,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json_payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => SIGE_WEBHOOK_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Bearer ' . SIGE_API_KEY,
                'Accept: application/json'
            ),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ));

        $respuesta = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error_curl = curl_error($ch);
        curl_close($ch);

        // Si hay error de cURL (timeout, conexión rechazada, etc.)
        if ($respuesta === false) {
            return array(
                'exitoso'   => false,
                'http_code' => 0,
                'respuesta' => 'cURL Error: ' . $error_curl
            );
        }

        // Consideramos exitoso si el HTTP code está en el rango 2xx
        $exitoso = ($http_code >= 200 && $http_code < 300);

        return array(
            'exitoso'   => $exitoso,
            'http_code' => $http_code,
            'respuesta' => $respuesta
        );
    }

    /**
     * Registra el resultado del envío del webhook en la tabla sige_webhook_log.
     * 
     * @param int    $id_inscripcion
     * @param string $identificacion
     * @param string $evento
     * @param string $payload_enviado  JSON enviado (o null)
     * @param string $respuesta_sige   Respuesta de SIGE (o mensaje de error)
     * @param int    $http_code
     * @param bool   $exitoso
     */
    private function registrarLog($id_inscripcion, $identificacion, $evento, $payload_enviado, $respuesta_sige, $http_code, $exitoso) {
        $sql = "INSERT INTO sige_webhook_log 
                (id_inscripcion, identificacion, evento, payload_enviado, respuesta_sige, http_code, exitoso, fecha_envio)
                VALUES 
                (:id_inscripcion, :identificacion, :evento, :payload_enviado, :respuesta_sige, :http_code, :exitoso, :fecha_envio)";
        $params = array(
            ':id_inscripcion' => $id_inscripcion,
            ':identificacion' => $identificacion,
            ':evento'          => $evento,
            ':payload_enviado' => $payload_enviado,
            ':respuesta_sige'  => $respuesta_sige,
            ':http_code'       => $http_code,
            ':exitoso'         => $exitoso ? 1 : 0,
            ':fecha_envio'     => $this->datetimeNow()
        );
        $this->query($sql, $params);
    }

    /**
     * Obtiene el último ID de inscripción creado para una identificación dada.
     * Útil para el caso GuardarInscripcion donde no tenemos el id_inscripcion directamente.
     * 
     * @param string $identificacion Número de documento del estudiante
     * @return int|null ID de inscripción o null si no se encuentra
     */
    public function obtenerUltimaInscripcion($identificacion) {
        $sql = "SELECT id_inscripcion FROM inscripcion 
                WHERE identificacion = :identificacion 
                ORDER BY id_inscripcion DESC LIMIT 1";
        $params = array(':identificacion' => $identificacion);
        $resultado = $this->row($sql, $params);
        return $resultado ? $resultado['id_inscripcion'] : null;
    }
}
?>
