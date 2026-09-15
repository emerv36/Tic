<?php

require_once __DIR__ . '/../Config/PDOconn.php';

class PersonalCatalogValidationException extends InvalidArgumentException {}
class PersonalCatalogConflictException extends RuntimeException {}
class PersonalCatalogDuplicateException extends RuntimeException {}
class PersonalCatalogOperationConflictException extends RuntimeException {}

/**
 * Servicio autoritativo TIC para los catálogos de cargo y dependencia de personal.
 * No administra personas ni reutiliza catálogos académicos, programa o sede.
 */
class PersonalCatalogService {
    private $pdo;

    private const TABLES = array(
        'CARGO' => 'sige_personal_cargo_catalogo',
        'DEPENDENCIA' => 'sige_personal_dependencia_catalogo'
    );

    public function __construct(PDO $pdo = null) {
        $this->pdo = $pdo ?: $this->crearConexion();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function listar($catalogo, $incluirInactivos = true) {
        $tabla = $this->tabla($catalogo);
        $sql = "SELECT id, nombre, version, estado, creado_en, actualizado_en FROM {$tabla}";
        if (!$incluirInactivos) $sql .= " WHERE estado = 'ACTIVO'";
        $sql .= ' ORDER BY nombre ASC, id ASC';
        return $this->pdo->query($sql)->fetchAll();
    }

    /** Devuelve exclusivamente el snapshot permitido para un vínculo administrativo. */
    public function obtenerSnapshotActivo($catalogo, $id) {
        $tabla = $this->tabla($catalogo);
        $id = $this->enteroPositivo($id, 'id');
        $stmt = $this->pdo->prepare(
            "SELECT id, nombre, version FROM {$tabla}
             WHERE id = :id AND estado = 'ACTIVO' LIMIT 1"
        );
        $stmt->execute(array(':id' => $id));
        $row = $stmt->fetch();
        if (!$row) throw new PersonalCatalogValidationException('El catálogo no existe o está inactivo.');
        return array(
            'id' => (int) $row['id'],
            'nombre_snapshot' => (string) $row['nombre'],
            'version' => (int) $row['version']
        );
    }

    public function crear($catalogo, $nombre, array $actor, $idOperacion = null) {
        $catalogo = $this->catalogo($catalogo);
        $tabla = self::TABLES[$catalogo];
        $actor = $this->actor($actor);
        $nombre = $this->nombre($nombre);
        $normalizado = self::normalizarNombre($nombre);
        $idOperacion = $idOperacion === null ? self::uuidV4() : $this->uuid($idOperacion);

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO {$tabla}
                 (nombre, nombre_normalizado, version, estado, creado_por_usuario_id,
                  actualizado_por_usuario_id, creado_en, actualizado_en)
                 VALUES (:nombre, :normalizado, 1, 'ACTIVO', :actor, :actor2,
                         CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
            );
            $stmt->execute(array(
                ':nombre' => $nombre,
                ':normalizado' => $normalizado,
                ':actor' => $actor['id'],
                ':actor2' => $actor['id']
            ));
            $id = (int) $this->pdo->lastInsertId();
            $despues = $this->buscarBloqueando($tabla, $id);
            $this->auditar($idOperacion, $catalogo, $id, 'CREAR', null, $despues, $actor);
            $this->pdo->commit();
            return $despues;
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            if ($this->esNombreDuplicado($exception)) {
                throw new PersonalCatalogDuplicateException('Ya existe un registro con ese nombre.', 0, $exception);
            }
            if ($this->esOperacionDuplicada($exception)) {
                throw new PersonalCatalogOperationConflictException('El id_operacion ya fue utilizado.', 0, $exception);
            }
            throw $exception;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function actualizar($catalogo, $id, $nombre, $estado, $expectedVersion, array $actor, $idOperacion = null) {
        $catalogo = $this->catalogo($catalogo);
        $tabla = self::TABLES[$catalogo];
        $id = $this->enteroPositivo($id, 'id');
        $expectedVersion = $this->enteroPositivo($expectedVersion, 'expected_version');
        $actor = $this->actor($actor);
        $nombre = $this->nombre($nombre);
        $normalizado = self::normalizarNombre($nombre);
        $estado = strtoupper(trim((string) $estado));
        if (!in_array($estado, array('ACTIVO', 'INACTIVO'), true)) {
            throw new PersonalCatalogValidationException('Estado de catálogo inválido.');
        }
        $idOperacion = $idOperacion === null ? self::uuidV4() : $this->uuid($idOperacion);

        $this->pdo->beginTransaction();
        try {
            $antes = $this->buscarBloqueando($tabla, $id);
            if ($antes === null) throw new PersonalCatalogValidationException('Registro de catálogo no encontrado.');
            if ((int) $antes['version'] !== $expectedVersion) {
                throw new PersonalCatalogConflictException('La versión del catálogo cambió; recargue antes de guardar.');
            }
            if ($antes['nombre'] === $nombre && $antes['nombre_normalizado'] === $normalizado && $antes['estado'] === $estado) {
                $this->pdo->commit();
                return $antes;
            }

            $nuevaVersion = $expectedVersion + 1;
            $stmt = $this->pdo->prepare(
                "UPDATE {$tabla}
                 SET nombre = :nombre, nombre_normalizado = :normalizado,
                     estado = :estado, version = :version,
                     actualizado_por_usuario_id = :actor, actualizado_en = CURRENT_TIMESTAMP
                 WHERE id = :id AND version = :expected"
            );
            $stmt->execute(array(
                ':nombre' => $nombre,
                ':normalizado' => $normalizado,
                ':estado' => $estado,
                ':version' => $nuevaVersion,
                ':actor' => $actor['id'],
                ':id' => $id,
                ':expected' => $expectedVersion
            ));
            if ($stmt->rowCount() !== 1) {
                throw new PersonalCatalogConflictException('No fue posible aplicar la versión esperada.');
            }
            $despues = $this->buscarBloqueando($tabla, $id);
            $accion = $this->accion($antes['estado'], $estado);
            $this->auditar($idOperacion, $catalogo, $id, $accion, $antes, $despues, $actor);
            $this->pdo->commit();
            return $despues;
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            if ($this->esNombreDuplicado($exception)) {
                throw new PersonalCatalogDuplicateException('Ya existe un registro con ese nombre.', 0, $exception);
            }
            if ($this->esOperacionDuplicada($exception)) {
                throw new PersonalCatalogOperationConflictException('El id_operacion ya fue utilizado.', 0, $exception);
            }
            throw $exception;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $exception;
        }
    }

    public static function normalizarNombre($nombre) {
        $nombre = preg_replace('/\s+/u', ' ', trim((string) $nombre));
        return mb_strtoupper($nombre, 'UTF-8');
    }

    public static function uuidV4() {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' .
            substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
    }

    private function crearConexion() {
        $pdo = new PDO(connstring, user, pass, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ));
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("SET time_zone = '-05:00'");
        return $pdo;
    }

    private function tabla($catalogo) {
        return self::TABLES[$this->catalogo($catalogo)];
    }

    private function catalogo($catalogo) {
        $catalogo = strtoupper(trim((string) $catalogo));
        if (!isset(self::TABLES[$catalogo])) {
            throw new PersonalCatalogValidationException('Catálogo no soportado.');
        }
        return $catalogo;
    }

    private function nombre($nombre) {
        $nombre = preg_replace('/\s+/u', ' ', trim((string) $nombre));
        $length = mb_strlen($nombre, 'UTF-8');
        if ($length < 1 || $length > 190) {
            throw new PersonalCatalogValidationException('El nombre debe tener entre 1 y 190 caracteres.');
        }
        return $nombre;
    }

    private function actor(array $actor) {
        $id = $this->enteroPositivo($actor['id'] ?? null, 'actor.id');
        $nombre = preg_replace('/\s+/u', ' ', trim((string) ($actor['nombre'] ?? '')));
        $rol = strtoupper(trim((string) ($actor['rol'] ?? '')));
        if ($nombre === '' || mb_strlen($nombre, 'UTF-8') > 190) {
            throw new PersonalCatalogValidationException('Actor inválido.');
        }
        if ($rol !== 'CARNETIZACION') {
            throw new PersonalCatalogValidationException('Solo CARNETIZACION puede administrar catálogos.');
        }
        return array('id' => $id, 'nombre' => $nombre, 'rol' => $rol);
    }

    private function uuid($value) {
        $value = strtolower(trim((string) $value));
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value)) {
            throw new PersonalCatalogValidationException('id_operacion inválido.');
        }
        return $value;
    }

    private function enteroPositivo($value, $field) {
        $validated = filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
        if ($validated === false) throw new PersonalCatalogValidationException($field . ' inválido.');
        return (int) $validated;
    }

    private function buscarBloqueando($tabla, $id) {
        $stmt = $this->pdo->prepare(
            "SELECT id, nombre, nombre_normalizado, version, estado, creado_en, actualizado_en
             FROM {$tabla} WHERE id = :id FOR UPDATE"
        );
        $stmt->execute(array(':id' => $id));
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['id'] = (int) $row['id'];
        $row['version'] = (int) $row['version'];
        return $row;
    }

    private function accion($estadoAnterior, $estadoNuevo) {
        if ($estadoAnterior === 'ACTIVO' && $estadoNuevo === 'INACTIVO') return 'INACTIVAR';
        if ($estadoAnterior === 'INACTIVO' && $estadoNuevo === 'ACTIVO') return 'REACTIVAR';
        return 'ACTUALIZAR';
    }

    private function auditar($idOperacion, $catalogo, $id, $accion, $antes, array $despues, array $actor) {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sige_personal_catalogo_auditoria
             (id_operacion, catalogo, catalogo_id, accion, version_anterior, version_nueva,
              datos_antes, datos_despues, actor_usuario_id, actor_nombre, actor_rol, ocurrido_en)
             VALUES
             (:operacion, :catalogo, :catalogo_id, :accion, :version_anterior, :version_nueva,
              :antes, :despues, :actor_id, :actor_nombre, :actor_rol, CURRENT_TIMESTAMP)'
        );
        $stmt->execute(array(
            ':operacion' => $idOperacion,
            ':catalogo' => $catalogo,
            ':catalogo_id' => $id,
            ':accion' => $accion,
            ':version_anterior' => $antes === null ? null : (int) $antes['version'],
            ':version_nueva' => (int) $despues['version'],
            ':antes' => $antes === null ? null : json_encode($antes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            ':despues' => json_encode($despues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            ':actor_id' => $actor['id'],
            ':actor_nombre' => $actor['nombre'],
            ':actor_rol' => $actor['rol']
        ));
    }

    private function esNombreDuplicado(PDOException $exception) {
        return strpos(strtolower($exception->getMessage()), 'nombre_normalizado') !== false;
    }

    private function esOperacionDuplicada(PDOException $exception) {
        $message = strtolower($exception->getMessage());
        return strpos($message, 'id_operacion') !== false
            || strpos($message, 'uq_sige_personal_catalogo_auditoria_operacion') !== false;
    }
}
