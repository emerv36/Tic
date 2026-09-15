<?php
if (PHP_SAPI !== 'cli') exit(1);

// Algunos archivos legacy conservan BOM. El buffer evita que ese byte marque
// headers_sent antes de cargar la configuración de sesión de SIGE.
ob_start();
require dirname(__DIR__) . '/Config/PDOconn.php';
require dirname(__DIR__) . '/Config/sige_config.php';
require dirname(__DIR__) . '/Services/SigePersonalClient.php';
if (trim((string) SIGE_API_KEY) === '' || trim((string) SIGE_INBOUND_API_KEY) === '') throw new RuntimeException('Faltan credenciales TIC protegidas.');
$tic=new PDO(connstring,user,pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);if((int)$tic->query('SELECT 1')->fetchColumn()!==1)throw new RuntimeException('TIC DB no disponible.');
$runtimeRoot = trim((string) getenv('TIC_SIGE_RUNTIME_ROOT'));
$sigeConfig = $runtimeRoot !== '' ? rtrim($runtimeRoot, '/\\') . '/sige/app/Config/config.php' : trim((string) getenv('SIGE_APP_CONFIG_PATH'));
if ($sigeConfig === '' || !is_file($sigeConfig)) throw new RuntimeException('No se configuró el runtime aislado de SIGE.');
require $sigeConfig;
ob_end_clean();
if (trim((string) DB_PASS) === '' || trim((string) TIC_OUTBOUND_API_KEY) === '') throw new RuntimeException('Faltan credenciales SIGE protegidas.');
$sige=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET,DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);if((int)$sige->query('SELECT 1')->fetchColumn()!==1)throw new RuntimeException('SIGE DB no disponible.');
$person=(new SigePersonalClient())->consultarPersona('CC','1001916903');if(!$person||($person['persona_uuid']??'')!=='77cc18e9-c856-4a87-8b18-ef4527a74cfe')throw new RuntimeException('Consulta autenticada SIGE falló.');
function healthPost($url,$payload,array $headers){$ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_HTTPHEADER=>$headers,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>20]);$body=curl_exec($ch);$http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$error=curl_error($ch);curl_close($ch);if($body===false)throw new RuntimeException($error);$body=(string)$body;if(substr($body,0,3)==="\xEF\xBB\xBF")$body=substr($body,3);return[$http,json_decode($body,true)];}
[$compactHttp,$compactBody]=healthPost(SIGE_PERSONAL_COMMAND_URL,'{"contract_version":"1.0"}',['Content-Type: application/json','Authorization: Bearer '.SIGE_API_KEY,'Accept: application/json']);if($compactHttp!==422||!is_array($compactBody)||($compactBody['codigo']??'')!=='PAYLOAD_INVALIDO')throw new RuntimeException('Endpoint compacto/autenticación no disponible.');
$event=$sige->query("SELECT id_evento,payload FROM integracion_personal_outbox WHERE persona_uuid='77cc18e9-c856-4a87-8b18-ef4527a74cfe' AND estado='SINCRONIZADO' ORDER BY persona_version DESC LIMIT 1")->fetch();if(!$event)throw new RuntimeException('No existe evento real para replay de salud.');
[$callbackHttp,$callbackBody]=healthPost(TIC_PERSONAL_WEBHOOK_URL,$event['payload'],['Content-Type: application/json','Authorization: Bearer '.TIC_OUTBOUND_API_KEY,'X-Event-Id: '.$event['id_evento']]);if($callbackHttp<200||$callbackHttp>=300||!is_array($callbackBody)||($callbackBody['status']??'')!=='PROCESADO'||!hash_equals(strtolower($event['id_evento']),strtolower((string)($callbackBody['id_evento']??''))))throw new RuntimeException('Callback autenticado/idempotente falló.');
if((int)$tic->query("SELECT COUNT(*) FROM sige_personal_outbox WHERE estado IN('PENDIENTE','REINTENTO','ENVIANDO')")->fetchColumn()!==0||(int)$tic->query('SELECT COUNT(*) FROM sige_personal_inbox WHERE procesado=0')->fetchColumn()!==0)throw new RuntimeException('Las colas TIC no están limpias.');echo "PERSONAL_TRANSPORT_HEALTH=OK\n";
