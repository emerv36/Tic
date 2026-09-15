<?php

$view = file_get_contents(dirname(__DIR__, 2) . '/gestionarcatalogospersonal.php');
$script = file_get_contents(dirname(__DIR__, 2) . '/javascripts/gestionarcatalogospersonal.js');
$controller = file_get_contents(dirname(__DIR__) . '/Controller/personalCatalogController.php');
$rewrite = file_get_contents(dirname(__DIR__, 2) . '/.htaccess');
if ($view === false || $script === false || $controller === false || $rewrite === false) {
    throw new RuntimeException('No fue posible leer la interfaz de catálogos.');
}
function assertCatalogUi($condition, $message) {
    if ($condition !== true) throw new RuntimeException($message);
}

assertCatalogUi(strpos($view, 'PersonalCatalogAuthorization') !== false, 'La vista debe autorizar en servidor.');
assertCatalogUi(strpos($view, 'TIC_SIGE_PERSONAL_CATALOGS_ENABLED') !== false, 'La vista debe permanecer detrás del feature flag.');
assertCatalogUi(strpos($view, 'asegurarToken') !== false, 'La vista debe generar CSRF.');
assertCatalogUi(strpos($view, 'htmlspecialchars($csrfToken') !== false, 'El CSRF debe escaparse.');
assertCatalogUi(strpos($script, ".text(item.nombre)") !== false, 'Los nombres deben insertarse como texto, no HTML.');
assertCatalogUi(strpos($script, 'csrf_token: csrf') !== false, 'Las mutaciones deben enviar CSRF.');
assertCatalogUi(strpos($script, '.html(item.nombre)') === false, 'No se permite HTML de nombres.');
assertCatalogUi(strpos($controller, 'PersonalCatalogApi') !== false, 'El adaptador HTTP debe usar la API probada.');
assertCatalogUi(strpos($controller, 'TIC_SIGE_PERSONAL_CATALOGS_ENABLED') !== false, 'La API debe permanecer detrás del feature flag.');
assertCatalogUi(strpos($controller, 'ERROR_INTERNO') !== false, 'Errores internos deben ocultarse al cliente.');
assertCatalogUi(strpos($rewrite, 'Personal/Catalogos/(Listar|Crear|Actualizar)') !== false, 'Falta ruta cerrada de catálogos.');
assertCatalogUi(stripos($view, 'sede') !== false, 'La interfaz debe advertir que dependencia no es sede.');
assertCatalogUi(stripos($view, 'id_sede') === false, 'La interfaz no debe usar id_sede.');

echo "personal_catalog_ui_contract_test: OK\n";
