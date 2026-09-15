<?php
session_start();
date_default_timezone_set("America/Bogota");
header('content-type: application/json; charset=utf-8');
require('../Models/Version.php');
$v= new Version();
    if(isset($_GET['case'])){ $case = $_GET['case'];
    }

//variables para editar
 if(isset($_POST['nombre_version'])){$nombre_version =strtoupper($_POST['nombre_version']);}
 if(isset($_POST['id_version'])){ $id_version = strtoupper($_POST['id_version']); }
 if(isset($_POST['estado_version'])){ $estado_version = strtoupper($_POST['estado_version']); }
//fin variables 

//variables para registrar versiones
  if(isset($_POST['Rnombre_version'])){$Rnombre_version = strtoupper($_POST['Rnombre_version']);}
  if(isset($_POST['Restado_version'])){$Restado_version = $_POST['Restado_version'];}
//fin variables

//variables editar estado de las version
  if(isset($_POST['codUsuario'])){  $codigo = $_POST['codUsuario'];  }
  if(isset($_POST['estado'])){  $estado = $_POST['estado'];  }
//fin variables

$table = array(
'data' =>array()
);
switch($case){
// case para el listado de las sedes
    case'ListarVersion':
    $tabla = $v->ListarVersion();
    $j=1;
        foreach($tabla as $datos => $data){
            if($data['estado_version']=='on'){
                $estado ='Habilitado';
            }else if($data['estado_version']=='off'){
                $estado ='Deshabilitado';
            }else{
                $estado = 'Error';
            }
   
            $edit = '<div class="btn btn-sm btn-warning fa fa-pencil-square-o data-toggle="modal" data-target="" onclick="ModalEditarVersion('.$data['id_version'].',
                            \''.$data['nombre_version'].'\',
                            \''.$data['estado_version'].'\')"></div>';

            $delete = '<div class="btn btn-sm btn-danger fa fa-trash-o" data-toggle="modal" data-target="modalconfirmar" onClick="FeEditEstado('.$data["id_version"].',\'off\')"> </div>';

            $restore = '<div class="btn btn-sm btn-success glyphicon glyphicon-check" data-toggle="modal" data-target="modalconfirmar" onClick="FeEditEstado('.$data["id_version"].', \'on\')"></div>';
            
            if($data["estado_version"] == 'on'){
                 $opcion = $edit.' '.$delete;
              }else{
                 $opcion = $restore;
              }
            array_push($table['data'], array($j, ucwords(strtolower($data['nombre_version'])),
                                                  ucwords(strtolower($estado))
                                                 ,$opcion));
            $j++;
        }
        $json = json_encode($table);
    break;
// fin case 


//case editar version
  case'EditarVersion':
      $version=$v->EditarVersion($id_version, $nombre_version, $estado_version);
         if($version){
          $json = json_encode(array("success"=>true));
         }else{
             $json = json_encode(array("success"=>false, "mensaje" => "No se pudo actualizar la información. Intentelo de nuevo."));
         }
  break;
//fin 

//case para registrar version
 case'RegistrarVersion':
       $ValidarVersion = $v->ValidarVersion($Rnombre_version, $Restado_version);
         if($ValidarVersion==''){
            $query=$v->RegistrarVersion($Rnombre_version, $Restado_version);
              if($query){
                 $json = json_encode(array("success"=>true));
              }else{
                $json = json_encode(array("success"=>false, "mensaje"=>"No se pudo realizar el regisro"));
              }
         }else{
           $json = json_encode(array("success"=>false, "mensaje"=>"Nombre de version registarda"));
         }
 break;
//fin case

//case para editar estado version
 case'EditarEstadoVersion':
      $query = $v->EditarEstadoVersion($codigo, $estado);
         if($query){
             $json = json_encode(array("success"=>true));
         }else{
             $json = json_encode(array("success" => false,"mensaje" => "No se ha podido actualizar la información. Por favor intentelo de nuevo"));
         }
 break;
//fin case 
}
echo $json;
?>