<?php
if (!function_exists('ticProtectedSecret')) {
    function ticProtectedSecret($name) {
        static $secrets = null;
        if ($secrets === null) {
            $path = getenv('TIC_SIGE_SECRETS_FILE') ?: 'C:\\ProgramData\\SCV\\TicSigePersonal\\secrets.json';
            $raw = is_readable($path) ? file_get_contents($path) : false;
            if (is_string($raw) && substr($raw, 0, 3) === "\xEF\xBB\xBF") $raw = substr($raw, 3);
            $secrets = is_string($raw) ? json_decode($raw, true) : array();
            if (!is_array($secrets)) $secrets = array();
        }
        return array_key_exists($name, $secrets) ? trim((string) $secrets[$name]) : trim((string) (getenv($name) ?: ''));
    }
}

define('host','localhost');
define('user','root');
define('pass', ticProtectedSecret('TIC_DB_PASS') ?: (getenv('TIC_DB_PASS') ?: 'B.quilla54'));
define('dbname','uybntujx_tic');
define('connstring','mysql:host='.host.';dbname='.dbname.';charset=utf8');

/*pgSQL*/
//define('connstring','pgsql:host='.host.';port=5432;dbname='.dbname);
?>
