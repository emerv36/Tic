<?php
require_once "developer/Config/config.php";
try {
    $conn = new PDO(connstring, user, pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET SESSION sql_mode = ''");
    $conn->exec("ALTER TABLE inscripcion ADD COLUMN uid_rfid VARCHAR(100) NULL AFTER chip_carnet");
    echo "Exito";
} catch(Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Exito (Ya existía)";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
