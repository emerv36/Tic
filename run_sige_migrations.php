<?php
$configFile = 'C:/xampp/htdocs/Sige/app/Config/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
}

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'uybntujx_tic');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $migrations = [
        'C:/xampp/htdocs/Sige/migrations/20260830_013_card_person_not_null.sql',
        'C:/xampp/htdocs/Sige/migrations/20260830_014_outbox_person_not_null.sql'
    ];
    
    foreach ($migrations as $file) {
        $sql = file_get_contents($file);
        echo "Running: $file\n";
        $pdo->exec($sql);
        echo "Successfully executed $file\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
