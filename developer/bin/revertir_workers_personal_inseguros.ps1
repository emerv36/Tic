#Requires -RunAsAdministrator
$ErrorActionPreference = 'Stop'
$names = @('TIC-SIGE Personal Outbox','SIGE-TIC Personal Callback','TIC-SIGE Personal Inbox')
[Environment]::SetEnvironmentVariable('TIC_SIGE_PERSONAL_UI_ENABLED', $null, 'Machine')
foreach ($name in $names) {
    Unregister-ScheduledTask -TaskName $name -Confirm:$false -ErrorAction SilentlyContinue
}
Restart-Service -Name 'Apache2.4' -Force
Write-Output 'WORKERS_SYSTEM=RETIRADOS'
Write-Output 'UI_PERSONAL=DESHABILITADA'
