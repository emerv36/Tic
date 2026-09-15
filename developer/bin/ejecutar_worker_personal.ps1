param([Parameter(Mandatory=$true)][ValidateSet('HEALTH','OUTBOX','CALLBACK','INBOX')][string]$Worker)
$ErrorActionPreference='Stop';$base='C:\ProgramData\SCV\TicSigePersonal';$runtime=Join-Path $base 'runtime';$php=Join-Path $runtime 'php\php.exe';$phpIni=Join-Path $runtime 'php\php-worker.ini';$logRoot=Join-Path $base 'logs'
$env:TIC_SIGE_SECRETS_FILE=Join-Path $base 'secrets.json';$env:TIC_SIGE_RUNTIME_ROOT=$runtime
$scripts=@{HEALTH=Join-Path $runtime 'tic\developer\bin\personal_transport_health.php';OUTBOX=Join-Path $runtime 'tic\developer\bin\procesar_sige_personal_outbox.php';CALLBACK=Join-Path $runtime 'sige\bin\sync_personal_events.php';INBOX=Join-Path $runtime 'tic\developer\bin\sige_personal_inbox_worker.php'}
$log=Join-Path $logRoot($Worker.ToLowerInvariant()+'.log');if((Test-Path $log)-and(Get-Item $log).Length-ge 5MB){$archive=$log+'.1';if(Test-Path $archive){Remove-Item -LiteralPath $archive -Force};Move-Item -LiteralPath $log -Destination $archive}
"[$((Get-Date).ToString('s'))] INICIO $Worker"|Out-File $log -Append -Encoding utf8
try{& $php -c $phpIni $scripts[$Worker] 2>&1|ForEach-Object{($_-replace '(?i)(bearer|api[_ -]?key|password|pass)\s*[:=]\s*\S+','$1=[REDACTADO]')}|Out-File $log -Append -Encoding utf8;$code=$LASTEXITCODE}catch{$safe=($_.Exception.Message-replace '(?i)(bearer|api[_ -]?key|password|pass)\s*[:=]\s*\S+','$1=[REDACTADO]')-replace '[\r\n]+',' ';"Error controlado: $safe"|Out-File $log -Append -Encoding utf8;$code=1}
"[$((Get-Date).ToString('s'))] FIN $Worker EXIT=$code"|Out-File $log -Append -Encoding utf8;exit $code
