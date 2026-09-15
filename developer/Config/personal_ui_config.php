<?php
// Feature flags fail-closed: producción solo los habilita de forma explícita.
if (!defined('TIC_SIGE_PERSONAL_UI_ENABLED')) {
    $uiVal = $_SERVER['TIC_SIGE_PERSONAL_UI_ENABLED'] ?? $_ENV['TIC_SIGE_PERSONAL_UI_ENABLED'] ?? getenv('TIC_SIGE_PERSONAL_UI_ENABLED');
    define('TIC_SIGE_PERSONAL_UI_ENABLED', (string)$uiVal === '1');
}
if (!defined('TIC_SIGE_PERSONAL_CARNET_TEMPLATES_READY')) {
    $tplVal = $_SERVER['TIC_SIGE_PERSONAL_CARNET_TEMPLATES_READY'] ?? $_ENV['TIC_SIGE_PERSONAL_CARNET_TEMPLATES_READY'] ?? getenv('TIC_SIGE_PERSONAL_CARNET_TEMPLATES_READY');
    define('TIC_SIGE_PERSONAL_CARNET_TEMPLATES_READY', (string)$tplVal === '1');
}
?>
