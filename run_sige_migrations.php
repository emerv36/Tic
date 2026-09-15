<?php
require_once 'C:/xampp/htdocs/Sige/app/Config/config.php';
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
