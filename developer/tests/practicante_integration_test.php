<?php
putenv('TIC_DB_PASS=B.quilla54');
$_ENV['TIC_DB_PASS'] = 'B.quilla54';

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../Config/PDOconn.php';
require_once __DIR__ . '/../Services/PersonalCatalogService.php';
require_once __DIR__ . '/../Services/SigePersonalClient.php';

try {
    $pdo = new PDO(connstring, user, pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo "1. Checking Practicante in sige_personal_cargo_catalogo...\n";
    $service = new PersonalCatalogService($pdo);
    $snapshot = $service->obtenerSnapshotActivo('CARGO', 11);
    if (strtolower($snapshot['nombre_snapshot']) !== 'practicante') {
        throw new RuntimeException("Expected Practicante cargo, got: " . $snapshot['nombre_snapshot']);
    }
    echo "SUCCESS: Cargo Practicante found with ID {$snapshot['id']} (version {$snapshot['version']}).\n";

    echo "2. Checking SigePersonalClient method...\n";
    $client = new SigePersonalClient();
    if (!method_exists($client, 'consultarPersonaPorDocumento')) {
        throw new RuntimeException("Method consultarPersonaPorDocumento missing in SigePersonalClient.");
    }
    echo "SUCCESS: SigePersonalClient has consultarPersonaPorDocumento.\n";

    echo "\nALL PRACTICANTE INTEGRATION TESTS PASSED SUCCESSFULLY.\n";
} catch (Throwable $t) {
    echo "TEST FAILED: " . $t->getMessage() . "\n" . $t->getTraceAsString() . "\n";
    exit(1);
}
