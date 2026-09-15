<?php
require_once(__DIR__ . '/../Config/sige_config.php');

/** Consulta fail-closed del rol académico usando el catálogo completo de usuarios Q10. */
class Q10Gateway {
    private static $usuariosPorDocumento = null;
    private static $errorDirectorio = null;
    private static $ultimoHttpCode = 0;

    public function consultar($identificacion) {
        $documento = $this->normalizarDocumento($identificacion);
        if ($documento === '') {
            return $this->fallo('Q10_DOCUMENTO_INVALIDO', 0);
        }
        if (!defined('Q10_API_KEY') || trim((string) Q10_API_KEY) === '') {
            return $this->fallo('Q10_NO_CONFIGURADO', 0);
        }
        if (!$this->cargarDirectorio()) {
            return $this->fallo(self::$errorDirectorio ?: 'Q10_DIRECTORIO_NO_DISPONIBLE', self::$ultimoHttpCode);
        }
        if (!array_key_exists($documento, self::$usuariosPorDocumento)) {
            return array('disponible' => true, 'encontrado' => false, 'estado' => 'INACTIVO',
                'codigo' => 'Q10_NO_ENCONTRADO', 'http_code' => self::$ultimoHttpCode);
        }
        return $this->interpretarUsuario(self::$usuariosPorDocumento[$documento]);
    }

    /**
     * Circuito de control: antes de publicar inactivaciones el catálogo debe estar
     * completo y conservar al menos un usuario conocido de la población vigilada.
     */
    public function validarPoblacion(array $identificaciones) {
        if (!$this->cargarDirectorio()) {
            return $this->fallo(self::$errorDirectorio ?: 'Q10_DIRECTORIO_NO_DISPONIBLE', self::$ultimoHttpCode);
        }
        $activos = 0;
        $encontrados = 0;
        foreach ($identificaciones as $identificacion) {
            $resultado = $this->consultar($identificacion);
            if (!$resultado['disponible']) return $resultado;
            if ($resultado['encontrado']) {
                $encontrados++;
                if ($resultado['estado'] === 'ACTIVO') $activos++;
            }
        }
        if ($encontrados === 0 && count($identificaciones) > 0) {
            return $this->fallo('Q10_CIRCUITO_POBLACION_AUSENTE', self::$ultimoHttpCode);
        }
        return array('disponible' => true, 'encontrado' => true, 'estado' => null,
            'codigo' => 'Q10_CONTROL_OK', 'http_code' => self::$ultimoHttpCode,
            'usuarios_control' => $encontrados, 'activos_control' => $activos);
    }

    private function cargarDirectorio() {
        if (is_array(self::$usuariosPorDocumento)) return true;
        if (self::$errorDirectorio !== null) return false;

        $limite = 5000;
        $offset = 0;
        $paginas = 0;
        $directorio = array();
        $huellaAnterior = null;
        do {
            $resultado = $this->getJson(
                'https://api.q10.com/v1/usuarios?Limit=' . $limite . '&Offset=' . $offset
            );
            self::$ultimoHttpCode = $resultado['http_code'];
            if (!$resultado['disponible']) {
                self::$errorDirectorio = $resultado['codigo'];
                return false;
            }
            $usuarios = $this->extraerLista($resultado['datos']);
            if ($usuarios === null) {
                self::$errorDirectorio = 'Q10_CONTRATO_USUARIOS_INVALIDO';
                return false;
            }
            $cantidad = count($usuarios);
            if ($cantidad === 0) break;
            $huella = hash('sha256', json_encode(array_slice($usuarios, 0, 5)) . '|' . count($usuarios));
            if ($huellaAnterior !== null && hash_equals($huellaAnterior, $huella)) {
                self::$errorDirectorio = 'Q10_PAGINACION_REPETIDA';
                return false;
            }
            $huellaAnterior = $huella;

            foreach ($usuarios as $usuario) {
                if (!is_array($usuario)) {
                    self::$errorDirectorio = 'Q10_CONTRATO_USUARIO_INVALIDO';
                    return false;
                }
                $numero = $usuario['Numero_identificacion'] ?? $usuario['numero_identificacion'] ?? null;
                $documento = $this->normalizarDocumento($numero);
                if ($documento === '') continue;
                if (isset($directorio[$documento])) {
                    self::$errorDirectorio = 'Q10_DOCUMENTO_DUPLICADO';
                    return false;
                }
                $directorio[$documento] = $usuario;
            }
            $offset += $cantidad;
            $paginas++;
            if ($paginas > 20) {
                self::$errorDirectorio = 'Q10_PAGINACION_EXCEDIDA';
                return false;
            }
        } while (true);

        if (!$directorio) {
            self::$errorDirectorio = 'Q10_DIRECTORIO_VACIO';
            return false;
        }
        self::$usuariosPorDocumento = $directorio;
        return true;
    }

    private function interpretarUsuario(array $usuario) {
        $roles = $usuario['Roles'] ?? $usuario['roles'] ?? null;
        if (!is_array($roles)) {
            return $this->fallo('Q10_CONTRATO_ROLES_INVALIDO', self::$ultimoHttpCode);
        }
        $rolEstudiante = false;
        foreach ($roles as $rol) {
            if (!is_array($rol)) {
                return $this->fallo('Q10_CONTRATO_ROL_INVALIDO', self::$ultimoHttpCode);
            }
            $nombre = mb_strtolower(trim((string) ($rol['Nombre'] ?? $rol['nombre'] ?? '')), 'UTF-8');
            if ($nombre !== 'estudiante') continue;
            $rolEstudiante = true;
            $estado = $this->booleanoEstricto($rol['Estado'] ?? $rol['estado'] ?? null);
            if ($estado === null) {
                return $this->fallo('Q10_CONTRATO_ESTADO_ROL_INVALIDO', self::$ultimoHttpCode);
            }
            if ($estado) {
                return array('disponible' => true, 'encontrado' => true, 'estado' => 'ACTIVO',
                    'codigo' => 'Q10_OK', 'http_code' => self::$ultimoHttpCode);
            }
        }
        return array('disponible' => true, 'encontrado' => true, 'estado' => 'INACTIVO',
            'codigo' => $rolEstudiante ? 'Q10_ROL_ESTUDIANTE_INACTIVO' : 'Q10_SIN_ROL_ESTUDIANTE',
            'http_code' => self::$ultimoHttpCode);
    }

    private function getJson($url) {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => array('Api-Key: ' . Q10_API_KEY, 'Accept: application/json'),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ));
        $respuesta = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        if ($respuesta === false) {
            return array('disponible' => false, 'codigo' => 'Q10_TRANSPORTE_' . ($curlError ? 'ERROR' : 'DESCONOCIDO'),
                'http_code' => 0, 'datos' => null);
        }
        if ($httpCode < 200 || $httpCode >= 300) {
            return array('disponible' => false, 'codigo' => 'Q10_HTTP_' . $httpCode,
                'http_code' => $httpCode, 'datos' => null);
        }
        $datos = json_decode($respuesta, true);
        if (!is_array($datos) || json_last_error() !== JSON_ERROR_NONE) {
            return array('disponible' => false, 'codigo' => 'Q10_JSON_INVALIDO',
                'http_code' => $httpCode, 'datos' => null);
        }
        return array('disponible' => true, 'codigo' => 'Q10_OK', 'http_code' => $httpCode, 'datos' => $datos);
    }

    private function extraerLista(array $datos) {
        foreach (array('data', 'Data', 'usuarios', 'Usuarios') as $contenedor) {
            if (array_key_exists($contenedor, $datos)) {
                return is_array($datos[$contenedor]) ? $datos[$contenedor] : null;
            }
        }
        return array_is_list($datos) ? $datos : null;
    }

    private function normalizarDocumento($valor) {
        $valor = mb_strtoupper(trim((string) $valor), 'UTF-8');
        return preg_replace('/[^A-Z0-9]/u', '', $valor) ?: '';
    }

    private function booleanoEstricto($valor) {
        if (is_bool($valor)) return $valor;
        if (is_int($valor) && ($valor === 0 || $valor === 1)) return $valor === 1;
        if (is_string($valor)) {
            $normalizado = mb_strtolower(trim($valor), 'UTF-8');
            if (in_array($normalizado, array('true', '1'), true)) return true;
            if (in_array($normalizado, array('false', '0'), true)) return false;
        }
        return null;
    }

    private function fallo($codigo, $httpCode) {
        return array('disponible' => false, 'encontrado' => false, 'estado' => null,
            'codigo' => $codigo, 'http_code' => (int) $httpCode);
    }
}
