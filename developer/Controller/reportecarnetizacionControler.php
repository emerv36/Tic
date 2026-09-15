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
}
echo $response;
