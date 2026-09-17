<?php
/**
 * Script de Comparación de Código Fuente (Local vs Producción)
 * 
 * Genera un reporte detallado con resumen de hashes MD5 y permite
 * ver las diferencias exactas de código línea por línea (Diff).
 */

header('Content-Type: text/html; charset=utf-8');

$secret = 'scv_diff_2026';
$token = $_GET['token'] ?? '';
if ($token !== $secret) {
    http_response_code(403);
    die('<h2>Acceso no autorizado</h2><p>Agregue ?token=scv_diff_2026 a la URL</p>');
}

function listarArchivos($dir, &$resultados = array()) {
    $archivos = scandir($dir);
    foreach ($archivos as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            if (pathinfo($path, PATHINFO_EXTENSION) === 'php' || pathinfo($path, PATHINFO_EXTENSION) === 'js') {
                $resultados[] = $path;
            }
        } else if ($value != "." && $value != "..") {
            $nombreDir = basename($path);
            if (!in_array($nombreDir, array('bower_components', 'plugins', 'artifacts', 'scratch', '.git'))) {
                listarArchivos($path, $resultados);
            }
        }
    }
    return $resultados;
}

$baseDir = __DIR__;
$archivoDiff = $_GET['file'] ?? '';

// Si solicitan el contenido de un archivo específico en JSON para diff
if (!empty($archivoDiff)) {
    $archivoReal = realpath($baseDir . '/' . ltrim($archivoDiff, '/\\'));
    if ($archivoReal && strpos($archivoReal, $baseDir) === 0 && is_file($archivoReal)) {
        echo json_encode(array(
            'rel' => $archivoDiff,
            'md5' => md5_file($archivoReal),
            'content' => file_get_contents($archivoReal)
        ), JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(array('error' => 'Archivo no encontrado'));
    }
    exit;
}

// Generar inventario de hashes
$todos = listarArchivos($baseDir);
$manifest = array();

foreach ($todos as $fullPath) {
    $relPath = str_replace('\\', '/', substr($fullPath, strlen($baseDir) + 1));
    $manifest[$relPath] = array(
        'md5' => md5_file($fullPath),
        'size' => filesize($fullPath),
        'mtime' => date('Y-m-d H:i:s', filemtime($fullPath))
    );
}

if (isset($_GET['format']) && $_GET['format'] === 'json') {
    echo json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comparador de Código - TIC SCV</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body { background: #f4f6f9; font-family: monospace, sans-serif; padding: 20px; }
        .box { background: #fff; border-radius: 6px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .diff-added { background-color: #e6ffed; color: #22863a; }
        .diff-removed { background-color: #ffeef0; color: #cb2431; }
        pre { background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; max-height: 500px; overflow: auto; }
        table code { background: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="box">
        <h3><i class="glyphicon glyphicon-transfer"></i> Comparador de Código TIC (Local vs Producción)</h3>
        <p class="text-muted">Herramienta de auditoría para detectar diferencias línea por línea entre ambientes.</p>
        <hr>

        <div class="row">
            <div class="col-md-6">
                <h4>1. Comparar desde esta vista</h4>
                <p>Ingresa la URL del otro servidor (ej. la de producción o la local) que tenga este mismo script cargado:</p>
                <div class="input-group">
                    <input type="text" id="targetUrl" class="form-control" placeholder="https://tic.scv.edu.co/comparar_codigo.php?token=scv_diff_2026">
                    <span class="input-group-btn">
                        <button class="btn btn-primary" onclick="ejecutarComparacion()"><i class="glyphicon glyphicon-search"></i> Comparar Ahora</button>
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-info" style="margin-top: 25px;">
                    <strong>Resumen Local Actual:</strong> <?php echo count($manifest); ?> archivos rastreados (.php / .js).
                </div>
            </div>
        </div>

        <br>
        <div id="resultadoComparacion"></div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
var manifestLocal = <?php echo json_encode($manifest); ?>;

function ejecutarComparacion() {
    var targetUrl = $.trim($('#targetUrl').val());
    if (!targetUrl) {
        alert('Por favor ingresa la URL remota del script comparar_codigo.php');
        return;
    }

    var fetchUrl = targetUrl + (targetUrl.indexOf('?') >= 0 ? '&' : '?') + 'format=json';
    $('#resultadoComparacion').html('<div class="alert alert-warning"><i class="glyphicon glyphicon-refresh"></i> Consultando servidor remoto...</div>');

    $.ajax({
        url: fetchUrl,
        dataType: 'json',
        success: function(manifestRemoto) {
            evaluarDiferencias(manifestRemoto, targetUrl);
        },
        error: function(xhr) {
            $('#resultadoComparacion').html('<div class="alert alert-danger">Error al consultar el servidor remoto (HTTP ' + xhr.status + '). Asegúrate de que la URL contenga el token correcto y no tenga bloqueos CORS.</div>');
        }
    });
}

function evaluarDiferencias(remoto, targetUrl) {
    var html = '<table class="table table-bordered table-striped"><thead><tr><th>Archivo</th><th>Estado</th><th>MD5 Local</th><th>MD5 Remoto</th><th>Acción</th></tr></thead><tbody>';
    var difs = 0;

    $.each(manifestLocal, function(file, dataLocal) {
        var dataRemota = remoto[file];
        if (!dataRemota) {
            difs++;
            html += '<tr class="danger"><td>' + file + '</td><td><span class="label label-danger">Falta en Remoto</span></td><td><code>' + dataLocal.md5 + '</code></td><td>-</td><td>-</td></tr>';
        } else if (dataLocal.md5 !== dataRemota.md5) {
            difs++;
            html += '<tr class="warning"><td><b>' + file + '</b></td><td><span class="label label-warning">DIFERENTE</span></td><td><code>' + dataLocal.md5 + '</code></td><td><code>' + dataRemota.md5 + '</code></td><td><button class="btn btn-xs btn-info" onclick="verDiff(\'' + file + '\', \'' + targetUrl + '\')">Ver Diferencias de Código</button></td></tr>';
        }
    });

    $.each(remoto, function(file, dataRemota) {
        if (!manifestLocal[file]) {
            difs++;
            html += '<tr class="info"><td>' + file + '</td><td><span class="label label-info">Solo en Remoto</span></td><td>-</td><td><code>' + dataRemota.md5 + '</code></td><td>-</td></tr>';
        }
    });

    if (difs === 0) {
        html = '<div class="alert alert-success"><h4><i class="glyphicon glyphicon-ok-sign"></i> ¡Los dos entornos son 100% idénticos!</h4> No existe ninguna diferencia en el contenido de los archivos PHP ni JS.</div>';
    } else {
        html = '<div class="alert alert-warning">Se encontraron <b>' + difs + '</b> archivo(s) con diferencias entre ambos servidores.</div>' + html + '</tbody></table><div id="diffBox"></div>';
    }

    $('#resultadoComparacion').html(html);
}

function verDiff(file, targetUrl) {
    $('#diffBox').html('<div class="alert alert-info">Cargando contenidos para comparar línea por línea...</div>');
    
    var urlLocal = 'comparar_codigo.php?token=scv_diff_2026&file=' + encodeURIComponent(file);
    var urlRemota = targetUrl + (targetUrl.indexOf('?') >= 0 ? '&' : '?') + 'file=' + encodeURIComponent(file);

    $.when($.getJSON(urlLocal), $.getJSON(urlRemota)).done(function(resLocal, resRemoto) {
        var txt1 = resLocal[0].content;
        var txt2 = resRemoto[0].content;
        
        var diffHtml = calcularDiffTexto(txt1, txt2, file);
        $('#diffBox').html('<h4>Diferencia Línea por Línea: <code>' + file + '</code></h4>' + diffHtml);
    }).fail(function() {
        $('#diffBox').html('<div class="alert alert-danger">Error al obtener el contenido de uno de los dos servidores para la comparación.</div>');
    });
}

function calcularDiffTexto(oldStr, newStr, filename) {
    var l1 = oldStr.split('\n');
    var l2 = newStr.split('\n');
    var max = Math.max(l1.length, l2.length);
    var out = '<pre>';
    var diffCount = 0;

    for (var i = 0; i < max; i++) {
        var line1 = l1[i] !== undefined ? l1[i] : '';
        var line2 = l2[i] !== undefined ? l2[i] : '';

        if (line1 !== line2) {
            diffCount++;
            out += '<div class="diff-removed">- L' + (i+1) + ': ' + escapeHtml(line1) + '</div>';
            out += '<div class="diff-added">+ R' + (i+1) + ': ' + escapeHtml(line2) + '</div>';
        }
    }
    out += '</pre>';

    if (diffCount === 0) return '<div class="alert alert-success">Los archivos son idénticos.</div>';
    return out;
}

function escapeHtml(text) {
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}
</script>
</body>
</html>
