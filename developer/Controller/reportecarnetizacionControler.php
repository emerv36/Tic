<?php
session_start();
date_default_timezone_set("America/Bogota");
header('content-type: application/json; charset=utf-8');
require('../Models/reportecarnetizacion.php');
$instancia = new ReporteCarnetizacion();

if (isset($_GET['case'])) {
    $case = $_GET['case'];
}

if (isset($_POST['lote'])) {
    $lote = $_POST['lote'];
}
if (isset($_POST['fecha1'])) {
    $fecha1 = $_POST['fecha1'];
}
if (isset($_POST['fecha2'])) {
    $fecha2 = $_POST['fecha2'];
}
if (isset($_POST['estado'])) {
    $estado = $_POST['estado'];
}
if (isset($_POST['tipo'])) {
    $tipo = $_POST['tipo'];
}


switch ($case) {
    case 'reporteEtapaPorLote':
        $table = $instancia->reporteEtapaPorLote($lote);
        if(count($table) > 0){
            $j = 1;
            $total = 0;
            $cuerpo_tabla = '';
            $cabecera_tabla = 
            '<tr>
                <th>No</th>
                <th>CANTIDAD</th>
                <th>PÉRIODO</th>
                <th>LOTE</th>
                <th>ETAPA</th>
            </tr>';
            foreach ($table as $datarow => $fila) {
                $cuerpo_tabla .= 
                '<tr>
                    <td>' . $j . '</td>
                    <td>' . $fila['cantidad'] . '</td>
                    <td>' . $fila['periodo'] . '</td>
                    <td>' . $fila['codigo'] . '</td>
                    <td>' . $fila['etapa'] . '</td>
                </tr>';
                $total += $fila['cantidad'];
                $j++;
            }
            $cuerpo_tabla .= 
            '<tr>
                <td>TOTALES:</td>
                <td>' . $total . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>';
            $response = json_encode(array("success" => true, "mensaje" => "Generando archivo", "cabecera" => $cabecera_tabla, "cuerpo" => $cuerpo_tabla, "nombre_archivo" => 'reporteEtapaPorLote'.'_'. date('m-d-Y h:i:s a', time())));
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "No hay datos disponibles"));
        }
    break;
    
    case 'reporteEtapa':
        $table = $instancia->reporteEtapa();
        if(count($table) > 0){
            $j = 1;
            $total = 0;
            $cuerpo_tabla = '';
            $cabecera_tabla = 
            '<tr>
                <th>No</th>
                <th>CANTIDAD</th>
                <th>PÉRIODO</th>
                <th>LOTE</th>
                <th>ETAPA</th>
            </tr>';
            foreach ($table as $datarow => $fila) {
                $cuerpo_tabla .= 
                '<tr>
                    <td>' . $j . '</td>
                    <td>' . $fila['cantidad'] . '</td>
                    <td>' . $fila['periodo'] . '</td>
                    <td>' . $fila['codigo'] . '</td>
                    <td>' . $fila['etapa'] . '</td>
                </tr>';
                $total += $fila['cantidad'];
                $j++;
            }
            $cuerpo_tabla .= 
            '<tr>
                <td>TOTALES:</td>
                <td>' . $total . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>';
            $response = json_encode(array("success" => true, "mensaje" => "Generando archivo", "cabecera" => $cabecera_tabla, "cuerpo" => $cuerpo_tabla, "nombre_archivo" => 'reporteEtapa'.'_'. date('m-d-Y h:i:s a', time())));
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "No hay datos disponibles"));
        }
    break;
    
    case 'reporteRangoFechaEstado':
        $table = $instancia->reporteRangoFechaEstado($fecha1, $fecha2, $estado);
        if(count($table) > 0){
            $j = 1;
            $total = 0;
            $cuerpo_tabla = '';
            $estado_reporte = '';
            $cabecera_tabla = 
            '<tr>
                <th>No</th>
                <th>IDENTIDAD</th>
                <th>ESTUDIANTE</th>
                <th>TELÉFONO</th>
                <th>CORREO</th>
                <th>PÉRIODO</th>
                <th>LOTE</th>
                <th>ETAPA</th>
                <th>SEDE</th>
                <th>PROGRAMA</th>
                <th>FECHA</th>
            </tr>';
            foreach ($table as $datarow => $fila) {
                if($j == 1){
                    $estado_reporte = $fila['estado_inscripcion'];
                }
                $cuerpo_tabla .= 
                '<tr>
                    <td>' . $j . '</td>
                    <td>' . $fila['identidad'] . '</td>
                    <td>' . $fila['estudiante'] . '</td>
                    <td>' . $fila['telefono'] . '</td>
                    <td>' . $fila['correo'] . '</td>
                    <td>' . $fila['periodo'] . '</td>
                    <td>' . $fila['codigo'] . '</td>
                    <td>' . $fila['etapa'] . '</td>
                    <td>' . $fila['sede_nombre'] . '</td>
                    <td>' . $fila['programa'] . '</td>
                    <td>' . $fila['fecha'] . '</td>
                </tr>';
                $j++;
            }
            $total = count($table);
            $cuerpo_tabla .= 
            '<tr>
                <td>TOTALES:</td>
                <td>' . $total . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>';
            $response = json_encode(array("success" => true, "mensaje" => "Generando archivo", "cabecera" => $cabecera_tabla, "cuerpo" => $cuerpo_tabla, "nombre_archivo" => 'reporteRangoFechaEstado'.$estado_reporte.'_'. date('m-d-Y h:i:s a', time())));
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "No hay datos disponibles"));
        }
    break;
    
    case 'reporteRangoFecha':
        $table = $instancia->reporteRangoFecha($fecha1, $fecha2);
        if(count($table) > 0){
            $j = 1;
            $total = 0;
            $cuerpo_tabla = '';
            $cabecera_tabla = 
            '<tr>
                <th>No</th>
                <th>IDENTIDAD</th>
                <th>ESTUDIANTE</th>
                <th>TELÉFONO</th>
                <th>CORREO</th>
                <th>PÉRIODO</th>
                <th>LOTE</th>
                <th>ETAPA</th>
                <th>SEDE</th>
                <th>PROGRAMA</th>
                <th>FECHA</th>
                <th>ESTADO</th>
            </tr>';
            foreach ($table as $datarow => $fila) {
                $cuerpo_tabla .= 
                '<tr>
                    <td>' . $j . '</td>
                    <td>' . $fila['identidad'] . '</td>
                    <td>' . $fila['estudiante'] . '</td>
                    <td>' . $fila['telefono'] . '</td>
                    <td>' . $fila['correo'] . '</td>
                    <td>' . $fila['periodo'] . '</td>
                    <td>' . $fila['codigo'] . '</td>
                    <td>' . $fila['etapa'] . '</td>
                    <td>' . $fila['sede_nombre'] . '</td>
                    <td>' . $fila['programa'] . '</td>
                    <td>' . $fila['fecha'] . '</td>
                    <td>' . $fila['estado_inscripcion'] . '</td>
                </tr>';
                $j++;
            }
            $total = count($table);
            $cuerpo_tabla .= 
            '<tr>
                <td>TOTALES:</td>
                <td>' . $total . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>';
            $response = json_encode(array("success" => true, "mensaje" => "Generando archivo", "cabecera" => $cabecera_tabla, "cuerpo" => $cuerpo_tabla, "nombre_archivo" => 'reporteRangoFecha'.'_'. date('m-d-Y h:i:s a', time())));
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "No hay datos disponibles"));
        }
    break;
    
    case 'reporteEstadoTodos':
        $table = $instancia->reporteEstadoTodos();
        if(count($table) > 0){
            $j = 1;
            $total = 0;
            $cuerpo_tabla = '';
            $cabecera_tabla = 
            '<tr>
                <th>No</th>
                <th>CANTIDAD</th>
                <th>ESTADO</th>
            </tr>';
            foreach ($table as $datarow => $fila) {
                $cuerpo_tabla .= 
                '<tr>
                    <td>' . $j . '</td>
                    <td>' . $fila['cantidad'] . '</td>
                    <td>' . $fila['estado_inscripcion'] . '</td>
                </tr>';
                $total += $fila['cantidad'];
                $j++;
            }
            $cuerpo_tabla .= 
            '<tr>
                <td>TOTALES:</td>
                <td>' . $total . '</td>
                <td></td>
            </tr>';
            $response = json_encode(array("success" => true, "mensaje" => "Generando archivo", "cabecera" => $cabecera_tabla, "cuerpo" => $cuerpo_tabla, "nombre_archivo" => 'reporteEstadoTodos'.'_'. date('m-d-Y h:i:s a', time())));
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "No hay datos disponibles"));
        }
    break;
    
    case 'reporteEstado':
        $table = $instancia->reporteEstado($estado);
        if(count($table) > 0){
            $j = 1;
            $total = 0;
            $cuerpo_tabla = '';
            $cabecera_tabla = 
            '<tr>
                <th>No</th>
                <th>CANTIDAD</th>
                <th>ESTADO</th>
            </tr>';
            foreach ($table as $datarow => $fila) {
                $cuerpo_tabla .= 
                '<tr>
                    <td>' . $j . '</td>
                    <td>' . $fila['cantidad'] . '</td>
                    <td>' . $fila['estado_inscripcion'] . '</td>
                </tr>';
                $total += $fila['cantidad'];
                $j++;
            }
            $cuerpo_tabla .= 
            '<tr>
                <td>TOTALES:</td>
                <td>' . $total . '</td>
                <td></td>
            </tr>';
            $response = json_encode(array("success" => true, "mensaje" => "Generando archivo", "cabecera" => $cabecera_tabla, "cuerpo" => $cuerpo_tabla, "nombre_archivo" => 'reporteEstado'.'_'. date('m-d-Y h:i:s a', time())));
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "No hay datos disponibles"));
        }
    break;
    
    case 'reporteTipoTodos':
        $table = $instancia->reporteTipoTodos();
        if(count($table) > 0){
            $j = 1;
            $total = 0;
            $cuerpo_tabla = '';
            $cabecera_tabla = 
            '<tr>
                <th>No</th>
                <th>IDENTIDAD</th>
                <th>ESTUDIANTE</th>
                <th>TELÉFONO</th>
                <th>CORREO</th>
                <th>PÉRIODO</th>
                <th>LOTE</th>
                <th>ETAPA</th>
                <th>SEDE</th>
                <th>FECHA</th>
                <th>TIPO</th>
            </tr>';
            foreach ($table as $datarow => $fila) {
                $cuerpo_tabla .= 
                '<tr>
                    <td>' . $j . '</td>
                    <td>' . $fila['identidad'] . '</td>
                    <td>' . $fila['estudiante'] . '</td>
                    <td>' . $fila['telefono'] . '</td>
                    <td>' . $fila['correo'] . '</td>
                    <td>' . $fila['periodo'] . '</td>
                    <td>' . $fila['codigo'] . '</td>
                    <td>' . $fila['etapa'] . '</td>
                    <td>' . $fila['sede_nombre'] . '</td>
                    <td>' . $fila['fecha'] . '</td>
                    <td>' . $fila['categoria_carnet'] . '</td>
                </tr>';
                $j++;
            }
            $total = count($table);
            $cuerpo_tabla .= 
            '<tr>
                <td>TOTALES:</td>
                <td>' . $total . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>';
            $response = json_encode(array("success" => true, "mensaje" => "Generando archivo", "cabecera" => $cabecera_tabla, "cuerpo" => $cuerpo_tabla, "nombre_archivo" => 'reporteTipo'.'_'. date('m-d-Y h:i:s a', time())));
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "No hay datos disponibles"));
        }
    break;
    
    case 'reporteTipo':
        $table = $instancia->reporteTipo($tipo);
        if(count($table) > 0){
            $j = 1;
            $total = 0;
            $cuerpo_tabla = '';
            $tipo_estado='';
            $cabecera_tabla = 
            '<tr>
                <th>No</th>
                <th>IDENTIDAD</th>
                <th>ESTUDIANTE</th>
                <th>TELÉFONO</th>
                <th>CORREO</th>
                <th>PÉRIODO</th>
                <th>LOTE</th>
                <th>ETAPA</th>
                <th>SEDE</th>
                <th>FECHA</th>
                <th>ESTADO</th>
            </tr>';
            foreach ($table as $datarow => $fila) {
                if($j == 1){
                    $tipo_estado = $fila['categoria_carnet'];
                }
                $cuerpo_tabla .= 
                '<tr>
                    <td>' . $j . '</td>
                    <td>' . $fila['identidad'] . '</td>
                    <td>' . $fila['estudiante'] . '</td>
                    <td>' . $fila['telefono'] . '</td>
                    <td>' . $fila['correo'] . '</td>
                    <td>' . $fila['periodo'] . '</td>
                    <td>' . $fila['codigo'] . '</td>
                    <td>' . $fila['etapa'] . '</td>
                    <td>' . $fila['sede_nombre'] . '</td>
                    <td>' . $fila['fecha'] . '</td>
                    <td>' . $fila['estado_inscripcion'] . '</td>
                </tr>';
                $j++;
            }
            $total = count($table);
            $cuerpo_tabla .= 
            '<tr>
                <td>TOTALES:</td>
                <td>' . $total . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>';
            $response = json_encode(array("success" => true, "mensaje" => "Generando archivo", "cabecera" => $cabecera_tabla, "cuerpo" => $cuerpo_tabla, "nombre_archivo" => 'reporteTipo'.$tipo_estado.'_'. date('m-d-Y h:i:s a', time())));
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "No hay datos disponibles"));
        }
    break;

    case 'AcuseRecibo':
        header("Content-Type: text/html; charset=utf-8");
        header("Cache-Control: no-cache, must-revalidate");

        $token = isset($_GET['token']) ? trim($_GET['token']) : '';
        $mensaje = '';
        $tipoMensaje = 'info';
        $detalles = null;

        if (!empty($token)) {
            $dbPass = (defined('pass') && pass !== '') ? pass : (getenv('TIC_DB_PASS') ?: 'B.quilla54');
            try {
                $pdo = new PDO(connstring, user, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);

                $stmt = $pdo->prepare("SELECT * FROM log_correos_acuses WHERE token_acuse = :token LIMIT 1");
                $stmt->execute([':token' => $token]);
                $registro = $stmt->fetch();

                if ($registro) {
                    if ($registro['estado_acuse'] === 'CONFIRMADO_EXPRESO' || $registro['estado_acuse'] === 'CONFIRMADO_TACITO') {
                        $tipoMensaje = 'warning';
                        $mensaje = 'Este acuse de recibo ya fue registrado previamente.';
                        $detalles = $registro;
                    } else {
                        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
                        $fechaAhora = date('Y-m-d H:i:s');

                        $update = $pdo->prepare("UPDATE log_correos_acuses 
                            SET estado_acuse = 'CONFIRMADO_EXPRESO',
                                fecha_confirmacion = :fecha,
                                ip_confirmacion = :ip,
                                user_agent = :ua
                            WHERE token_acuse = :token");
                        $update->execute([
                            ':fecha' => $fechaAhora,
                            ':ip' => $ip,
                            ':ua' => $userAgent,
                            ':token' => $token
                        ]);

                        $registro['estado_acuse'] = 'CONFIRMADO_EXPRESO';
                        $registro['fecha_confirmacion'] = $fechaAhora;
                        $tipoMensaje = 'success';
                        $mensaje = '¡Muchas gracias! Tu acuse de recibo ha sido registrado exitosamente en nuestro sistema.';
                        $detalles = $registro;
                    }
                } else {
                    $tipoMensaje = 'error';
                    $mensaje = 'El código de confirmación no es válido o la notificación ha expirado.';
                }
            } catch (Exception $e) {
                $tipoMensaje = 'error';
                $mensaje = 'Ocurrió un inconveniente al procesar la confirmación. Por favor intente más tarde.';
            }
        } else {
            $tipoMensaje = 'error';
            $mensaje = 'Enlace de confirmación incompleto.';
        }

        echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acuse de Recibo - System Center</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: #f1f5f9; font-family: "Segoe UI", system-ui, -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; color: #334155; padding: 20px; }
        .card { background: #ffffff; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); max-width: 480px; width: 100%; padding: 40px 30px; text-align: center; border: 1px solid #e2e8f0; }
        .icon-circle { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px auto; font-size: 40px; }
        .bg-success { background-color: #dcfce7; color: #166534; }
        .bg-warning { background-color: #fef3c7; color: #92400e; }
        .bg-error { background-color: #fee2e2; color: #991b1b; }
        h2 { font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 12px; }
        p.msg { font-size: 15px; color: #475569; line-height: 1.5; margin-bottom: 24px; }
        .details-container { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; text-align: left; font-size: 13px; color: #334155; margin-bottom: 24px; }
        .details-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .badge { display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 12px; text-transform: uppercase; background-color: #16a34a; color: #ffffff; }
        .btn-close { background-color: #0284c7; color: #ffffff; border: none; padding: 12px 28px; font-size: 14px; font-weight: 600; border-radius: 8px; cursor: pointer; width: 100%; }
        .countdown-text { font-size: 12px; color: #94a3b8; margin-top: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <div style="margin-bottom: 20px;">
            <img src="../../assets/images/nuevoLogo-400x82.png" alt="System Center" style="max-height: 45px; width: auto;" onerror="this.style.display=\'none\'">
        </div>';

        if ($tipoMensaje === 'success') {
            echo '<div class="icon-circle bg-success">✓</div><h2>¡Confirmación Registrada!</h2>';
        } elseif ($tipoMensaje === 'warning') {
            echo '<div class="icon-circle bg-warning">i</div><h2>Acuse Ya Confirmado</h2>';
        } else {
            echo '<div class="icon-circle bg-error">✕</div><h2>Aviso de Verificación</h2>';
        }

        echo '<p class="msg">' . htmlspecialchars($mensaje) . '</p>';

        if ($detalles) {
            echo '<div class="details-container">
                <div class="details-row"><span>Destinatario:</span><strong>' . htmlspecialchars($detalles['destinatario_email']) . '</strong></div>
                <div class="details-row"><span>Identificación:</span><strong>' . htmlspecialchars($detalles['estudiante_id'] ?? 'N/A') . '</strong></div>
                <div class="details-row"><span>Estado:</span><span class="badge">' . htmlspecialchars($detalles['estado_acuse']) . '</span></div>';
            if (!empty($detalles['fecha_confirmacion'])) {
                echo '<div class="details-row"><span>Fecha Confirmación:</span><strong>' . htmlspecialchars($detalles['fecha_confirmacion']) . '</strong></div>';
            }
            echo '</div>';
        }

        echo '<button class="btn-close" onclick="cerrarVentana();">Cerrar esta Ventana</button>
            <p class="countdown-text" id="countdown">Esta ventana se cerrará automáticamente en <b id="sec">5</b> segundos...</p>
        </div>
        <script>
            var segundos = 5;
            var timer = setInterval(function() {
                segundos--;
                var el = document.getElementById("sec");
                if (el) el.innerText = segundos;
                if (segundos <= 0) {
                    clearInterval(timer);
                    cerrarVentana();
                }
            }, 1000);
            function cerrarVentana() {
                window.opener = null;
                window.open("", "_self");
                window.close();
                document.getElementById("countdown").innerText = "Puedes cerrar de forma segura esta pestaña del navegador.";
            }
        </script>
</body>
</html>';
        exit();
}
if (isset($response)) { echo $response; }
