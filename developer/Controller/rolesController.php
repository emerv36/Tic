<?php
session_start();
date_default_timezone_set("America/Bogota");
header('content-type: application/json; charset=utf-8');

if(isset($_GET['case'])){
    $case=$_GET['case'];
}

// variables para gestionarroles.php
if(isset($_POST['nombre_rol'])){
  $nombre_rol = $_POST['nombre_rol'];
}
if(isset($_POST['estado_rol'])){
  $estado_rol = $_POST['estado_rol'];
}
if(isset($_POST['codrol'])){
  $codrol = $_POST['codrol'];
}
if(isset($_POST['codusuario'])){
  $codusuario = $_POST['codusuario'];
}
if(isset($_POST['estado'])){
  $estado = $_POST['estado'];
}

$createtable = array(
  'data' => array()
);

require_once '../Models/Roles.php';


//Creación de objetos
$re = new Roles();


switch ($case) {
  /************************  procesos para gestionarrol.php ****************************/
    case 'loadRoles':
      $table = $re->LoadRoles(); 
      $i = 1;
      foreach ($table as $datarow => $data) {

        if($data["estado_rol"] == 'on'){
          $estado = 'Habilitado';
        }else {
          $estado = 'Inhabilitado';
        }
        $edit = '<div class="btn btn-warning btn-sm fa fa-pencil-square-o" data-toggle="modal"  data-target="#modal-rolEdit" onclick="fmodalEditar('.$data["codigo_rol"].', \''.$data["nombre_rol"].'\', \''.$data["estado_rol"].'\')"></div>';

        $delete = '<div class="btn btn-danger btn-sm fa fa-trash-o"  data-toggle="modal" data-target="#dialog-confirm" onclick="feditEstado('.$data["codigo_rol"].',\'off\')"></div>';

        $restore = '<div class="btn btn-success glyphicon glyphicon-check" data-toggle="modal" data-target="#dialog-confirm" onClick="feditEstado('.$data["codigo_rol"].', \'on\')"></div>';

        if($data["estado_rol"] == 'on'){
          $options = $edit.' '.$delete;
        }else{
          $options = $restore;
        }


        array_push($createtable['data'], array($i,
                                             $data["nombre_rol"],
                                             //$data["menus"],
                                             $estado,
                                             $options));
        $i++;
      }
      $json = json_encode($createtable);
    break;

    case 'editItemRol':
      $query = $re->editRol($nombre_rol, $estado_rol, $codrol);  

      if($query){
        $json = json_encode(array("success"=>true));
      }else{
        $json = json_encode(array("success"=>false,"mensaje" => "No se Actualizó la información. Por favor, intentelo de nuevo"));
      }
    break;

    case 'buscarRol':
      $table = $re->BuscarRol($nombre_menu, $where);
      $i = 1;
      
      foreach ($table as $datarow => $data) {
        if($data["estado_rol"] == 'on'){
          $estado = 'Habilitado';
        }else if($data["estado_rol"] == 'off'){
          $estado = 'Inhabilitado';
        }else{
          $estado = 'Error';
        }

        $edit = '<div class="col mdc-button mdc-ripple-upgraded no-padding" data-mdc-auto-init="MDCRipple" onclick="fmodalEditar('.$data["codigo_rol"].', \''.$data["nombre_rol"].'\', \''.$data["estado_rol"].'\')"><i class="material-icons">mode_edit</i></div>';
        $options = $edit;
          
        array_push($createtable['data'], array($i, $data["nombre_rol"], $data["menus"], $estado, $options));
        $i++;
      }
      $json = json_encode($createtable);
    break;

    case 'editEstadoRoles':
      $query = $re->EditarEstadoRoles($codusuario, $estado);
      if($query){
        $json = json_encode(array("success"=>true));
      }else{
        $json = json_encode(array("success"=>false, "mensaje" => "No se Actualizó la información. Por favor, intentelo de nuevo"));
      }
    break;
  /************************  FIN procesos para gestionarroles.php ****************************/

  

}
echo $json;
?>