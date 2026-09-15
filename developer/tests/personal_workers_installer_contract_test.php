<?php
$source = file_get_contents(dirname(__DIR__) . '/bin/instalar_workers_personal.ps1');
foreach (array('#Requires -RunAsAdministrator','S-1-5-19','RunLevel Limited','MultipleInstances IgnoreNew','HEALTH','LastTaskResult','TIC_SIGE_PERSONAL_UI_ENABLED','Restart-Service','Unregister-ScheduledTask','status-ne401','C:\\ProgramData\\SCV\\TicSigePersonal','Copy-PinnedTree','Copy-PinnedFiles','Assert-TreeAcl','secrets.json','ExpectedPhpDigest','ExpectedSourceDigest') as $required) {
    if (strpos($source, $required) === false) throw new RuntimeException('Instalador incompleto: ' . $required);
}
if (strpos($source, "UserId 'SYSTEM'") !== false
    || preg_match('/Register-ScheduledTask[^\r\n]*\s-Force(?:\s|\||$)/', $source)) {
    throw new RuntimeException('El instalador conserva SYSTEM o sobreescritura de tareas.');
}
if (strpos($source, 'TIC_SIGE_PERSONAL_CARNET_TEMPLATES_READY') !== false) throw new RuntimeException('El instalador no debe habilitar plantillas.');
$wrapper = file_get_contents(dirname(__DIR__) . '/bin/ejecutar_worker_personal.ps1');
foreach (array('5MB', 'Move-Item', '[REDACTADO]') as $required) {
    if (strpos($wrapper, $required) === false) throw new RuntimeException('Wrapper incompleto: ' . $required);
}
foreach (array('php-worker.ini', ' -c $phpIni ', 'curl-ca-bundle.crt', 'expectedSettings', 'Move-Item -LiteralPath $stage -Destination $runtime') as $required) {
    if (strpos($source . $wrapper, $required) === false) throw new RuntimeException('Aislamiento PHP incompleto: ' . $required);
}
if (stripos($wrapper, 'xampp\\htdocs') !== false || stripos($wrapper, 'C:\\xampp\\php') !== false || strpos($wrapper, 'TIC_SIGE_SECRETS_FILE') === false) {
    throw new RuntimeException('El wrapper no está aislado de htdocs o no carga secretos protegidos.');
}
if (strpos($source, 'catch{[Environment]::SetEnvironmentVariable(\'TIC_SIGE_PERSONAL_UI_ENABLED\',$null,\'Machine\')') === false) {
    throw new RuntimeException('El rollback no garantiza UI apagada.');
}
echo "personal_workers_installer_contract_test: OK\n";
