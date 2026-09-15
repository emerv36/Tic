#Requires -RunAsAdministrator
$ErrorActionPreference = 'Stop'
$base = 'C:\ProgramData\SCV\TicSigePersonal'
$secretFile = Join-Path $base 'secrets.json'
$legacyConfig = 'C:\xampp\htdocs\tic.scv.edu.co\developer\Config\config.php'

function Set-BaseAcl([string] $path) {
    $acl = New-Object System.Security.AccessControl.DirectorySecurity
    $acl.SetAccessRuleProtection($true, $false)
    $inherit = [System.Security.AccessControl.InheritanceFlags] 'ContainerInherit,ObjectInherit'
    $propagation = [System.Security.AccessControl.PropagationFlags]::None
    $allow = [System.Security.AccessControl.AccessControlType]::Allow
    $acl.AddAccessRule((New-Object System.Security.AccessControl.FileSystemAccessRule((New-Object System.Security.Principal.SecurityIdentifier('S-1-5-18')), 'FullControl', $inherit, $propagation, $allow)))
    $acl.AddAccessRule((New-Object System.Security.AccessControl.FileSystemAccessRule((New-Object System.Security.Principal.SecurityIdentifier('S-1-5-32-544')), 'FullControl', $inherit, $propagation, $allow)))
    $acl.AddAccessRule((New-Object System.Security.AccessControl.FileSystemAccessRule((New-Object System.Security.Principal.SecurityIdentifier('S-1-5-19')), 'ReadAndExecute', $inherit, $propagation, $allow)))
    Set-Acl -LiteralPath $path -AclObject $acl
}

function Set-SecretAcl([string] $path) {
    $acl = New-Object System.Security.AccessControl.FileSecurity
    $acl.SetAccessRuleProtection($true, $false)
    $allow = [System.Security.AccessControl.AccessControlType]::Allow
    $acl.AddAccessRule((New-Object System.Security.AccessControl.FileSystemAccessRule((New-Object System.Security.Principal.SecurityIdentifier('S-1-5-18')), 'FullControl', $allow)))
    $acl.AddAccessRule((New-Object System.Security.AccessControl.FileSystemAccessRule((New-Object System.Security.Principal.SecurityIdentifier('S-1-5-32-544')), 'FullControl', $allow)))
    $acl.AddAccessRule((New-Object System.Security.AccessControl.FileSystemAccessRule((New-Object System.Security.Principal.SecurityIdentifier('S-1-5-19')), 'Read', $allow)))
    Set-Acl -LiteralPath $path -AclObject $acl
}

function Assert-SecretAcl([string] $path) {
    $acl = Get-Acl -LiteralPath $path
    if (-not $acl.AreAccessRulesProtected) { throw 'La DACL del almacén no está protegida.' }
    $expected = @{
        'S-1-5-18' = [System.Security.AccessControl.FileSystemRights]::FullControl
        'S-1-5-32-544' = [System.Security.AccessControl.FileSystemRights]::FullControl
        'S-1-5-19' = ([System.Security.AccessControl.FileSystemRights]::Read -bor [System.Security.AccessControl.FileSystemRights]::Synchronize)
    }
    $rules = @($acl.Access)
    if ($rules.Count -ne 3 -or ($rules | Where-Object AccessControlType -ne 'Allow')) { throw 'Cantidad o tipo de ACE inesperado.' }
    foreach ($rule in $rules) {
        $sid = $rule.IdentityReference.Translate([System.Security.Principal.SecurityIdentifier]).Value
        if (-not $expected.ContainsKey($sid) -or $rule.FileSystemRights -ne $expected[$sid]) { throw 'Derechos inesperados en el almacén.' }
        $expected.Remove($sid)
    }
    if ($expected.Count -ne 0) { throw 'Faltan identidades requeridas en el almacén.' }
}

function Read-Exact([System.IO.Stream] $stream, [int] $length) {
    $buffer = New-Object byte[] $length; $offset = 0
    while ($offset -lt $length) { $read = $stream.Read($buffer, $offset, $length - $offset); if ($read -le 0) { throw 'Conexión MySQL cerrada.' }; $offset += $read }
    return $buffer
}
function Read-MySqlPacket([System.IO.Stream] $stream) {
    $header = Read-Exact $stream 4
    $length = [int]$header[0] -bor ([int]$header[1] -shl 8) -bor ([int]$header[2] -shl 16)
    return Read-Exact $stream $length
}
function Write-MySqlPacket([System.IO.Stream] $stream, [byte[]] $payload, [byte] $sequence) {
    $header = [byte[]]@(($payload.Length -band 255), (($payload.Length -shr 8) -band 255), (($payload.Length -shr 16) -band 255), $sequence)
    $stream.Write($header, 0, 4); $stream.Write($payload, 0, $payload.Length); $stream.Flush()
}
function Get-MySqlNativeToken([string] $password, [byte[]] $seed) {
    $sha = [System.Security.Cryptography.SHA1]::Create(); $encoding = [System.Text.Encoding]::UTF8
    $stage1 = $sha.ComputeHash($encoding.GetBytes($password)); $stage2 = $sha.ComputeHash($stage1)
    $joined = New-Object byte[] ($seed.Length + $stage2.Length); [Array]::Copy($seed, 0, $joined, 0, $seed.Length); [Array]::Copy($stage2, 0, $joined, $seed.Length, $stage2.Length)
    $stage3 = $sha.ComputeHash($joined); $token = New-Object byte[] 20
    for ($i=0; $i -lt 20; $i++) { $token[$i] = $stage1[$i] -bxor $stage3[$i] }
    return $token
}
function Test-TicDatabase([string] $password) {
    $client = New-Object System.Net.Sockets.TcpClient
    try {
        $pending = $client.BeginConnect('127.0.0.1', 3306, $null, $null)
        if (-not $pending.AsyncWaitHandle.WaitOne(5000)) { throw 'Timeout conectando a MySQL TIC.' }
        $client.EndConnect($pending); $stream = $client.GetStream(); $stream.ReadTimeout = 5000; $stream.WriteTimeout = 5000; $handshake = Read-MySqlPacket $stream
        $i=1; while ($handshake[$i] -ne 0) { $i++ }; $i += 1 + 4
        $seed1 = [byte[]]$handshake[$i..($i+7)]; $i += 9
        $capLow = [int]$handshake[$i] -bor ([int]$handshake[$i+1] -shl 8); $i += 2
        $i += 1 + 2; $capHigh = [int]$handshake[$i] -bor ([int]$handshake[$i+1] -shl 8); $i += 2
        $authLength = [int]$handshake[$i]; $i += 11
        $part2Length = [Math]::Max(12, $authLength - 8); $seed2 = [byte[]]$handshake[$i..($i+$part2Length-1)] | Where-Object { $_ -ne 0 }
        $seed = [byte[]]($seed1 + $seed2); [byte[]]$token = @(Get-MySqlNativeToken $password $seed)
        $memory = New-Object System.IO.MemoryStream; $writer = New-Object System.IO.BinaryWriter($memory)
        $flags = [uint32]0x000AA20D; $writer.Write($flags); $writer.Write([uint32]16777216); $writer.Write([byte]33); $writer.Write((New-Object byte[] 23)); $writer.Write([System.Text.Encoding]::UTF8.GetBytes('root')); $writer.Write([byte]0); $writer.Write([byte]$token.Length); $writer.Write($token); $writer.Write([System.Text.Encoding]::UTF8.GetBytes('uybntujx_tic')); $writer.Write([byte]0); $writer.Write([System.Text.Encoding]::ASCII.GetBytes('mysql_native_password')); $writer.Write([byte]0)
        Write-MySqlPacket $stream $memory.ToArray() 1; $response = Read-MySqlPacket $stream
        if ($response[0] -ne 0) { throw 'La credencial TIC no autenticó contra MySQL.' }
    } finally { $client.Dispose() }
}

New-Item -ItemType Directory -Path $base -Force | Out-Null
Set-BaseAcl $base
$temporary = Join-Path $base ('secrets.' + [guid]::NewGuid().ToString('N') + '.tmp')
trap { if (Test-Path -LiteralPath $temporary) { Remove-Item -LiteralPath $temporary -Force }; throw }
if (Test-Path -LiteralPath $secretFile) { throw 'El almacén definitivo ya existe; no se sobrescribe.' }
$legacy = Get-Content -LiteralPath $legacyConfig -Raw
$withoutBlocks = [regex]::Replace($legacy, '(?s)/\*.*?\*/', '')
$withoutComments = [regex]::Replace($withoutBlocks, '(?m)^\s*//.*$', '')
$matches = [regex]::Matches($withoutComments, "define\('pass','([^']+)'\)")
if ($matches.Count -ne 1) { throw 'La credencial TIC activa no es inequívoca.' }
$secrets = @{
    TIC_DB_PASS = $matches[0].Groups[1].Value
}
foreach ($name in @('SIGE_OUTBOUND_API_KEY','SIGE_INBOUND_API_KEY','SIGE_TIC_OUTBOUND_API_KEY','SIGE_TIC_INBOUND_API_KEY','SIGE_DB_PASS')) {
    $value = [Environment]::GetEnvironmentVariable($name, 'Machine')
    if ([string]::IsNullOrWhiteSpace($value)) { throw "Falta la credencial requerida: $name" }
    $secrets[$name] = $value
}
$json = $secrets | ConvertTo-Json
[System.IO.File]::WriteAllText($temporary, $json, (New-Object System.Text.UTF8Encoding($false)))
Set-SecretAcl $temporary
$bytes = [System.IO.File]::ReadAllBytes($temporary)
if ($bytes.Length -lt 2 -or ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF)) { throw 'El almacén tiene BOM o está vacío.' }
$decoded = Get-Content -LiteralPath $temporary -Raw | ConvertFrom-Json
foreach ($name in @('TIC_DB_PASS','SIGE_OUTBOUND_API_KEY','SIGE_INBOUND_API_KEY','SIGE_TIC_OUTBOUND_API_KEY','SIGE_TIC_INBOUND_API_KEY','SIGE_DB_PASS')) {
    if ([string]::IsNullOrWhiteSpace($decoded.$name)) { throw "Validación fallida: $name" }
}
Test-TicDatabase $decoded.TIC_DB_PASS
Assert-SecretAcl $temporary
Move-Item -LiteralPath $temporary -Destination $secretFile
Assert-SecretAcl $secretFile
'SECRETOS_PERSONAL_MIGRADOS=OK'
