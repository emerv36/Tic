<?php

require_once __DIR__ . '/../Config/PDOconn.php';

class PersonalCatalogUnauthorizedException extends RuntimeException {}
class PersonalCatalogForbiddenException extends RuntimeException {}
class PersonalCatalogCsrfException extends RuntimeException {}

/** Valida sesión, usuario, perfil y rol activo exacto antes de operar catálogos. */
class PersonalCatalogAuthorization {
    private $pdo;

    public function __construct(PDO $pdo = null) {
        $this->pdo = $pdo ?: new PDO(connstring, user, pass, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ));
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function autorizar(array $session, $csrfToken = null, $requiereCsrf = false) {
        if (($session['SiigaBv'] ?? false) !== true) {
            throw new PersonalCatalogUnauthorizedException('Sesión no autenticada.');
        }
        $usuarioId = $this->enteroPositivo($session['IN_codigo_usuCA'] ?? null);
        $perfilId = $this->enteroPositivo($session['IN_codperfil'] ?? null);
        $rolId = $this->enteroPositivo($session['IN_codrol'] ?? null);
        if ($usuarioId === null || $perfilId === null || $rolId === null) {
            throw new PersonalCatalogUnauthorizedException('Sesión incompleta.');
        }

        $stmt = $this->pdo->prepare(
            "SELECT u.codigo_usu, u.nombres_usuario, r.codigo_rol, r.nombre_rol
             FROM usuario u
             INNER JOIN usuperfil p ON p.codigo_perfil = u.codperfil_fk
             INNER JOIN roles r ON r.codigo_rol = p.codrol_fk
             WHERE u.codigo_usu = :usuario
               AND u.codperfil_fk = :perfil
               AND r.codigo_rol = :rol
               AND u.estado = 'on'
               AND p.estado_perfil = 'on'
               AND r.estado_rol = 'on'
             LIMIT 1"
        );
        $stmt->execute(array(':usuario' => $usuarioId, ':perfil' => $perfilId, ':rol' => $rolId));
        $row = $stmt->fetch();
        if (!$row) throw new PersonalCatalogForbiddenException('Usuario o rol inactivo/no coincidente.');
        if (strtoupper(trim((string) $row['nombre_rol'])) !== 'CARNETIZACION') {
            throw new PersonalCatalogForbiddenException('Se requiere el rol exacto CARNETIZACION.');
        }

        if ($requiereCsrf) {
            $expected = (string) ($session['PERSONAL_CATALOG_CSRF'] ?? '');
            $received = (string) $csrfToken;
            if ($expected === '' || $received === '' || !hash_equals($expected, $received)) {
                throw new PersonalCatalogCsrfException('Token CSRF inválido.');
            }
        }

        $nombre = preg_replace('/\s+/u', ' ', trim((string) $row['nombres_usuario']));
        if ($nombre === '') $nombre = 'USUARIO TIC ' . $usuarioId;
        return array('id' => $usuarioId, 'nombre' => $nombre, 'rol' => 'CARNETIZACION');
    }

    public static function asegurarToken(array &$session) {
        if (!isset($session['PERSONAL_CATALOG_CSRF'])
            || !is_string($session['PERSONAL_CATALOG_CSRF'])
            || preg_match('/^[0-9a-f]{64}$/', $session['PERSONAL_CATALOG_CSRF']) !== 1) {
            $session['PERSONAL_CATALOG_CSRF'] = bin2hex(random_bytes(32));
        }
        return $session['PERSONAL_CATALOG_CSRF'];
    }

    private function enteroPositivo($value) {
        $result = filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
        return $result === false ? null : (int) $result;
    }
}
