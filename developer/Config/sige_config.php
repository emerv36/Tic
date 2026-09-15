<?php
/** Configuración de integración TIC <-> SIGE sin secretos en el código. */

if (!function_exists('sigeEnv')) {
    function sigeEnv($nombre) {
        static $secretos = null;
        if ($secretos === null) {
            $archivo = getenv('TIC_SIGE_SECRETS_FILE') ?: (is_readable('/home/uybntujx/secrets.json') ? '/home/uybntujx/secrets.json' : 'C:\\ProgramData\\SCV\\TicSigePersonal\\secrets.json');
            $contenido = is_readable($archivo) ? file_get_contents($archivo) : false;
            if (is_string($contenido) && substr($contenido, 0, 3) === "\xEF\xBB\xBF") $contenido = substr($contenido, 3);
            $secretos = is_string($contenido) ? json_decode($contenido, true) : array();
            if (!is_array($secretos)) $secretos = array();
        }
        if (array_key_exists($nombre, $secretos)) return trim((string) $secretos[$nombre]);
        $valor = isset($_SERVER[$nombre]) ? $_SERVER[$nombre] : (isset($_ENV[$nombre]) ? $_ENV[$nombre] : getenv($nombre));
        return ($valor === false || $valor === null) ? '' : trim((string) $valor);
    }
}

define('SIGE_WEBHOOK_URL', sigeEnv('SIGE_WEBHOOK_URL') ?: 'https://sige.scv.edu.co/sige/public/api/webhook/tic/estudiante');

// Conserva el nombre de constante esperado por SigeWebhook y el dispatcher,
// pero su fuente es exclusivamente la credencial outbound TIC -> SIGE.
define('SIGE_API_KEY', sigeEnv('SIGE_OUTBOUND_API_KEY'));

define('TIC_BASE_URL', sigeEnv('TIC_BASE_URL') ?: 'https://tic.scv.edu.co');
define('SIGE_WEBHOOK_TIMEOUT', max(1, (int) (sigeEnv('SIGE_WEBHOOK_TIMEOUT') ?: 10)));
define('Q10_API_KEY', sigeEnv('Q10_API_KEY'));

if (!defined('SIGE_CARNET_COMMAND_URL')) {
    define(
        'SIGE_CARNET_COMMAND_URL',
        sigeEnv('SIGE_CARNET_COMMAND_URL') ?: 'https://sige.scv.edu.co/sige/public/api/integracion/tic/carnets/comandos'
    );
}

if (!defined('SIGE_PERSONAL_COMMAND_URL')) {
    define(
        'SIGE_PERSONAL_COMMAND_URL',
        sigeEnv('SIGE_PERSONAL_COMMAND_URL') ?: 'https://sige.scv.edu.co/sige/public/api/v1/tic/personal/sync'
    );
}

if (!defined('SIGE_PERSONAL_LINK_COMMAND_URL')) {
    define(
        'SIGE_PERSONAL_LINK_COMMAND_URL',
        sigeEnv('SIGE_PERSONAL_LINK_COMMAND_URL') ?: 'https://sige.scv.edu.co/sige/public/api/integracion/tic/personas/vinculos/comandos'
    );
}

if (!defined('SIGE_PERSONAL_CARNET_COMMAND_URL')) {
    define(
        'SIGE_PERSONAL_CARNET_COMMAND_URL',
        sigeEnv('SIGE_PERSONAL_CARNET_COMMAND_URL') ?: 'https://sige.scv.edu.co/sige/public/api/v1/tic/carnets/eventos'
    );
}

if (!defined('SIGE_CARNET_RECONCILIATION_URL')) {
    define(
        'SIGE_CARNET_RECONCILIATION_URL',
        sigeEnv('SIGE_CARNET_RECONCILIATION_URL') ?: 'https://sige.scv.edu.co/sige/public/api/integracion/tic/carnets/reconciliar'
    );
}

// Entrada SIGE -> TIC independiente de la llave outbound.
if (!defined('SIGE_INBOUND_API_KEY')) {
    define('SIGE_INBOUND_API_KEY', sigeEnv('SIGE_INBOUND_API_KEY'));
}

if (!defined('SIGE_OUTBOX_MAX_ATTEMPTS')) {
    define('SIGE_OUTBOX_MAX_ATTEMPTS', 12);
}

if (!defined('TIC_SIGE_URL')) {
    define('TIC_SIGE_URL', sigeEnv('TIC_SIGE_URL') ?: 'https://sige.scv.edu.co/sige/public');
}

if (!defined('TIC_SIGE_API_KEY')) {
    // La consulta de personal usa la credencial outbound real de TIC cuando
    // no se ha separado todavía una credencial específica para ese endpoint.
    define('TIC_SIGE_API_KEY', sigeEnv('TIC_SIGE_API_KEY') ?: SIGE_API_KEY);
}

?>
