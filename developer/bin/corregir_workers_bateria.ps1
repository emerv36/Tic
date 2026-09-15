#Requires -RunAsAdministrator
$ErrorActionPreference='Stop'
$tasks=@('TIC-SIGE Personal Outbox','SIGE-TIC Personal Callback','TIC-SIGE Personal Inbox')
$health='TIC-SIGE Personal Health Preflight';$healthCreated=$false
function Wait-RealRun([string]$name,[datetime]$previous,[int]$seconds){$deadline=(Get-Date).AddSeconds($seconds);do{Start-Sleep 1;$task=Get-ScheduledTask $name;$info=Get-ScheduledTaskInfo $name;if($info.LastRunTime-gt$previous-and$task.State-notin@('Running','Queued')){return $info.LastTaskResult}}while((Get-Date)-lt$deadline);throw "Timeout real: $name"}
[Environment]::SetEnvironmentVariable('TIC_SIGE_PERSONAL_UI_ENABLED',$null,'Machine');Restart-Service Apache2.4 -Force
try{
 $settings=New-ScheduledTaskSettingsSet -MultipleInstances IgnoreNew -StartWhenAvailable -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -ExecutionTimeLimit(New-TimeSpan -Minutes 5)
 $baselines=@{};foreach($name in $tasks){if(-not(Get-ScheduledTask $name -ErrorAction SilentlyContinue)){throw "Falta tarea: $name"};$baselines[$name]=(Get-ScheduledTaskInfo $name).LastRunTime}
 foreach($name in $tasks){Set-ScheduledTask -TaskName $name -Settings $settings|Out-Null}
 $wrapper='C:\ProgramData\SCV\TicSigePersonal\runtime\tic\developer\bin\ejecutar_worker_personal.ps1';$principal=New-ScheduledTaskPrincipal -UserId 'S-1-5-19' -LogonType ServiceAccount -RunLevel Limited;$action=New-ScheduledTaskAction -Execute 'powershell.exe' -Argument('-NoProfile -ExecutionPolicy Bypass -File "'+$wrapper+'" HEALTH');$trigger=New-ScheduledTaskTrigger -Once -At(Get-Date).AddMinutes(10)
 Register-ScheduledTask -TaskName $health -Action $action -Trigger $trigger -Principal $principal -Settings $settings|Out-Null;$healthCreated=$true;$previous=(Get-ScheduledTaskInfo $health).LastRunTime;Start-ScheduledTask $health;if((Wait-RealRun $health $previous 90)-ne0){throw 'Health real falló'};Unregister-ScheduledTask $health -Confirm:$false;$healthCreated=$false
 foreach($name in $tasks){Start-ScheduledTask $name;if((Wait-RealRun $name $baselines[$name] 90)-ne0){throw "Worker real falló: $name"}}
 [Environment]::SetEnvironmentVariable('TIC_SIGE_PERSONAL_UI_ENABLED','1','Machine');Restart-Service Apache2.4 -Force;Start-Sleep 3;try{Invoke-WebRequest -UseBasicParsing 'http://localhost/tic.scv.edu.co/gestionarpersonal.php' -ErrorAction Stop|Out-Null;throw 'UI sin autenticación'}catch{if([int]$_.Exception.Response.StatusCode-ne401){throw 'UI no confirmó 401'}}
 'WORKERS_BATERIA=OK';'UI_PERSONAL=HABILITADA'
}catch{[Environment]::SetEnvironmentVariable('TIC_SIGE_PERSONAL_UI_ENABLED',$null,'Machine');if($healthCreated){Unregister-ScheduledTask $health -Confirm:$false -ErrorAction SilentlyContinue};Restart-Service Apache2.4 -Force -ErrorAction SilentlyContinue;throw}
