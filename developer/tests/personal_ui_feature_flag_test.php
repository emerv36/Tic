<?php
putenv('TIC_SIGE_PERSONAL_UI_ENABLED');
putenv('TIC_SIGE_PERSONAL_CARNET_TEMPLATES_READY');
require dirname(__DIR__) . '/Config/personal_ui_config.php';
if (TIC_SIGE_PERSONAL_UI_ENABLED !== false || TIC_SIGE_PERSONAL_CARNET_TEMPLATES_READY !== false) {
    throw new RuntimeException('Las funciones de personal deben permanecer apagadas por defecto.');
}
$controller = file_get_contents(dirname(__DIR__) . '/Controller/personalController.php');
$cardController = file_get_contents(dirname(__DIR__) . '/Controller/personalCarnetController.php');
foreach (array($controller, $cardController) as $source) {
    if (strpos($source, 'if (!TIC_SIGE_PERSONAL_UI_ENABLED)') === false) {
        throw new RuntimeException('Un controlador de personal no aplica el feature flag.');
    }
}
echo "personal_ui_feature_flag_test: OK\n";
