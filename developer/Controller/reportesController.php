<?php
  session_start();
  date_default_timezone_set("America/Bogota");
  header('content-type: application/json; charset=utf-8');
  require('../Models/Reportes.php');
  //require("../../class/html2pdf_v4.03/html2pdf.class.php");

  $r= new Reportes ();
  
  if(isset($_GET['case'])){ $case = $_GET['case'];}
  //variables editar estado de los Reportes
  if(isset($_POST['codreporte'])){$codreporte=$_POST['codreporte'];}
  if(isset($_POST['estado'])){$estado=$_POST['estado']; }
  //fin variables editar estado de los reportes

  //variables para editar y actualizar la info de los reportes
  if(isset($_POST['EidReporte'])){ $EidReporte = $_POST['EidReporte'];}
  if(isset($_POST['EnombreReporte'])){$EnombreReporte = $_POST['EnombreReporte'];}
  if(isset($_POST['EnombreArchivo'])){$EnombreArchivo = $_POST['EnombreArchivo'];}
  if(isset($_POST['EcodigoReporte'])){$EcodigoReporte = $_POST['EcodigoReporte'];}
  if(isset($_POST['EversionReporte'])){$EversionReporte = $_POST['EversionReporte'];}
  if(isset($_POST['EplantelReporte'])){$EplantelReporte = $_POST['EplantelReporte'];}
  if(isset($_POST['EestadoReporte'])){$EestadoReporte = $_POST['EestadoReporte'];}
  //fin variables 

  //variables para registrar la info de los reporte
  if(isset($_POST['RnombreReporte'])){$RnombreReporte   = $_POST['RnombreReporte'];}
  if(isset($_POST['RcodigoReporte'])){$RcodigoReporte    = $_POST['RcodigoReporte'];}
  if(isset($_POST['RversionReporte'])){$RversionReporte  = $_POST['RversionReporte'];}
  if(isset($_POST['RplantelReporte'])){$RplantelReporte  = $_POST['RplantelReporte'];}
  if(isset($_POST['RestadoReporte'])){$RestadoReporte    = $_POST['RestadoReporte'];}
  if(isset($_POST['RnombreArchivo'])){$RnombreArchivo    = $_POST['RnombreArchivo'];}

  //fin variables 


  //variables para registro de version de reportes
  if (isset($_POST['RnombreVersion'])){$RnombreVersion=$_POST['RnombreVersion'];}
  if (isset($_POST['RfechaVersion'])){$RfechaVersion=$_POST['RfechaVersion'];}
  //fin variables

  //variables para editar registro de version de reportes
  if (isset($_POST['Eideversion'])){$Eidversion=$_POST['Eideversion'];}
  if (isset($_POST['EnombreVersion'])){$EnombreVersion=$_POST['EnombreVersion'];}
  if (isset($_POST['EfechaVersion'])){$EfechaVersion=$_POST['EfechaVersion'];}
  //fin variables



  $createtablereporte = array(
      'data' => array()
  );

  $createtableversion = array(
      'data' => array()
  );
  

  switch($case){

//case para listar todos los reportes
   case'ListarReportes':
     $table = $r->ListarReportes();
     $j=1;
     foreach($table as $datarow => $reportes){
        if($reportes['estado_reporte']=='on'){
            $estado = 'Habilitado';
        }else if ($reportes['estado_reporte']=='off'){
            $estado = 'Deshabilitado';
        }else{
            $estado = 'Error';
        }
    $edit='<div class="btn btn-sm btn-warning fa fa-pencil-square-o data-toggle="modal" data-target="#editar-reportes"
        onclick="ModalEditarReportes('."'".$reportes['codigo_reporte']."'".',
                                    '."'".$reportes['nombre_reporte']."'".',
                                    '."'".$reportes['nombre_archivo']."'".',
                                    '.$reportes['id_version_reportefk'].',
                                    '.$reportes['id_plantelfk'].',
                                    '."'".$reportes['estado_reporte']."'".',
                                    '.$reportes['id_reporte'].') "></div>';
        
    $delete = '<div class="btn btn-sm btn-danger fa fa-trash-o"  data-toggle="modal" data-target="modalconfirmar"  
        onClick="ModalEditEstadoReportes('.$reportes["id_reporte"].',\'off\')"></div>';
        
    $restore = '<div class="btn btn-sm btn-success glyphicon glyphicon-check" data-toggle="modal" data-target="modalconfirmar"
         onClick="ModalEditEstadoReportes('.$reportes["id_reporte"].', \'on\')"></div>';
        
    if($reportes["estado_reporte"] == 'on'){
             $opcion = $edit.' '.$delete;
        }else{
             $opcion = $restore;
       }
    array_push($createtablereporte['data'], 
                                    array($j,
                                    $reportes['codigo_reporte'],
                                    ucwords(strtolower($reportes['nombre_reporte'])),
                                    $reportes['nombre_archivo'],
                                    $reportes['nombre_version_reporte'],
                                    $reportes['fecha_version_reporte'],
                                    $estado,
                                    $opcion));
        $j++;
     }

     $response = json_encode($createtablereporte);
   break;
//fin case para listar todos los reportes



//case para editar el estado de los reportes
  case'EditarEstadoReportes':
        $query = $r->EditarEstadoReportes($codreporte, $estado);
        if($query){
            $response = json_encode(array("success"=>true));
        }else{
            $response = json_encode(array("success" => false,"mensaje" => "No se ha podido actualizar la información. Por favor intentelo de nuevo"));
        }
  break;
//fin case para editar el estado de los Reportes


//case para editar y actualizar la info de los Reportes
  case'ActualizarReportes':
     $query =$r->ActualizarReportes($EcodigoReporte,
                                    $EnombreReporte,
                                    $EnombreArchivo,
                                    $EversionReporte,
                                    $EplantelReporte,
                                    $EestadoReporte,
                                    $EidReporte);
  
        if($query){
            $response = json_encode(array("success"=>true));
          }else{    
            $response = json_encode(array("success" => false, "mensaje" => "No ha podido actualizar la información. Por favor, intentelo de nuevo."));
        }
      
 break;
//fin case 

//case para registrar la información de los Reportes
    case'RegistrarReportes':
        $validarReporte = $r->ValidarReportes($RcodigoReporte);
        if($validarReporte==''){
           $query=$r->RegistrarReportes($RcodigoReporte, $RnombreReporte, $RnombreArchivo, $RversionReporte, $RplantelReporte, $RestadoReporte);

             if($query){
                $response = json_encode(array("success"=>true));
             }else{
               $response = json_encode(array("success"=>false, "mensaje"=>"No se pudo realizar el regisro"));
             }
        }else{
          $response = json_encode(array("success"=>false, "mensaje"=>"El código del reporte se encuentra registrado"));
        }
    break;
//fin case 


// case para registrar las versiones de los reportes
case 'RegistrarVersion':
      $query=$r->RegistrarVersion($RnombreVersion,$RfechaVersion);
      
      if($query){
             $response = json_encode(array("success"=>true, "mensaje"=>"Registro almacenado"));
          }else{
            $response = json_encode(array("success"=>false, "mensaje"=>"No se pudo realizar el regisro"));
          }
   


      break;
//fin case

// case para registrar las versiones de los reportes
case 'ActualizarVersion':
    $query=$r->ActualizarVersion($Eidversion,
                                 $EnombreVersion,
                                 $EfechaVersion );
    
    if($query){
           $response = json_encode(array("success"=>true, "mensaje"=>"Registro actualizado"));
        }else{
          $response = json_encode(array("success"=>false, "mensaje"=>"No se pudo realizar el regisro"));
        }
 


    break;


case 'ListarVersion':
     $tableversion = $r->ListarVersion();
     $j=1;

     foreach($tableversion as $datarow => $version){

     

    $opcion = '<div class="btn btn-sm btn-warning fa fa-pencil-square-o"   onclick="ModalEditarVersion('.$version['id_version_reporte'].',\''.$version['nombre_version_reporte'].'\',
         \''.$version['fecha_version_reporte'].'\')"></div>';



        array_push($createtableversion['data'],
                                      array($j,
                                      $version['nombre_version_reporte'],
                                      $version['fecha_version_reporte'],
                                      $opcion));
        $j++;
     }
     $response = json_encode($createtableversion);
     break;//fin case


// case cargar las versiones de los formatos
case 'CargarVersion':
     $CargarVersion=$r->CargarVersion();
     $response=json_encode($CargarVersion);

break;//fin case


//case cargar plantel
case 'CargarPlantel':
    $CargarPlantel=$r->CargarPlantel();   
    $response=json_encode($CargarPlantel);
break;//fin case


}
 echo $response;