<?php
session_start();
date_default_timezone_set("America/Bogota");
header('content-type: application/json; charset=utf-8');
require('../Models/Dashadmision.php');

$das = new Dashadmision();
    if(isset($_GET['case'])){
    $case = $_GET['case'];
    }
//variables

  if(isset($_POST['fecha'])){ $fecha =$_POST['fecha']; }



switch($case){
// case para contar los estudiantes inscriptos hoy
    case'ContarInscritosHoy':
   
    $info = $das->ContarInscritosHoy($fecha);
    $total=$info['total'];
    $json = json_encode(array("success"=>true, "total" =>$total));
 
  break;
// fin case 


case'TotalProceso':
    $info = $das->TotalProceso();
    $total=$info['TotalProceso'];
    $json = json_encode(array("success"=>true, "TotalProceso" =>$total)); 
  break;

  case'TotalConChip':
    $info = $das->TotalConChip();
    $total=$info['TotalConChip'];
    $json = json_encode(array("success"=>true, "TotalConChip" =>$total)); 
  break;

  case'TotalSinChip':
    $info = $das->TotalSinChip();
    $total=$info['TotalSinChip'];
    $json = json_encode(array("success"=>true, "TotalSinChip" =>$total)); 
  break;

  case'TotalCarnetFuncionario':
    $info = $das->TotalCarnetFuncionario();
    $total=$info['TotalCarnetFuncionario'];
    $json = json_encode(array("success"=>true, "TotalCarnetFuncionario" =>$total)); 
  break;


 case'Totalinscrito':   
    $info  = $das->Totalinscrito($fecha);
    $total = $info['total'];

    if ($info !=''){
    $json = json_encode(array("success"=>true, "total" =>$total)); 
    }else{
    $json = json_encode(array("success"=>false, "total" =>"0")); 
    }
  break;

  case'TotalRealizado':   
    $res  = $das->TotalRealizado();
    $total = $res['totalRealizado'];
     $json = json_encode(array("success"=>true, "totalRealizado" =>$total)); 
     break;
    
    


    case'TotalEntregado':   
      $res  = $das->TotalEntregado();
      $total = $res['TotalEntregado'];
      $json = json_encode(array("success"=>true, "TotalEntregado" =>$total));       
      
  break;

  case'TotalRegistros':   
    $res  = $das->TotalRegistros();
    $total = $res['total'];
    if ($res !=''){
     $json = json_encode(array("success"=>true, "total" =>$total)); 
      }else{
     $json = json_encode(array("success"=>false, "totales" =>"0")); 
    }
break; 

 
  case 'CarnetAgrupadosEstado':
    if(isset($_POST['filtroestado'])){ $estado =$_POST['filtroestado']; }

    $inscripcion =$das->CarnetAgrupadosEstado($estado);
    $agrupado = array();
         foreach($inscripcion as $data){
         array_push($agrupado,
               array('nombre_sede'=>$data['nombre_sede'],
                     'nombre_programa'=>$data['nombre_programa'],
                     'estado_inscripcion'=>$data['estado_inscripcion'],
                     'chip_carnet'=>$data['chip_carnet'],
                     'TotalCarnet'=>$data['TotalCarnet']));
         }
        if(count($agrupado) != 0){
           $json = json_encode(array("success"=>true, 'agrupado'=>$agrupado));
       
         }else{
            $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron matriculas"));
         }
 break;

 case 'CarnetizacionAgrupada':
   
  $inscripcion =$das->CarnetizacionAgrupada();
  $agrupado = array();
       foreach($inscripcion as $data){
       array_push($agrupado,
             array('nombre_sede'=>$data['nombre_sede'],
                   'id_sede_inscripcionfk'=>$data['id_sede_inscripcionfk'],
                   'nombre_programa'=>$data['nombre_programa'],
                   'id_programa_inscripcionfk'=>$data['id_programa_inscripcionfk'],
                   'estado_inscripcion'=>$data['estado_inscripcion'],
                   'valor_registro'=>$data['valor_registro'],
                   'chip_carnet'=>$data['chip_carnet'],
                   'TotalCarnet'=>$data['TotalCarnet']));
       }
      if(count($agrupado) != 0){
         $json = json_encode(array("success"=>true, 'agrupado'=>$agrupado));
     
       }else{
          $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron carnet"));
       }
break;

 

 case 'PoblacionSede':
  $inscripcion =$das->PoblacionSede();
  $agrupado = array();
       foreach($inscripcion as $data){
       array_push($agrupado,
             array('id_sede_inscripcionfk'=>$data['id_sede_inscripcionfk'],
                   'nombre_sede'=>$data['nombre_sede'],
                   'totalprograma'=>$data['totalprograma']));
       }
      if(count($agrupado) != 0){
         $json = json_encode(array("success"=>true, 'agrupado'=>$agrupado));
     
       }else{
          $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron matriculas"));
       }
break;

//cargar los periodos de matriculas
case'CargarPeriodoMatricula':
$info = $das->CargarPeriodoMatricula();
$json = json_encode($info);
break;

}
echo $json;
?>