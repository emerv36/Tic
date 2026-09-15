<?php
session_start();
date_default_timezone_set("America/Bogota");
header('content-type: application/json; charset=utf-8');
//Llamados de clases
require('../Models/Perfiles.php');
require('../Models/Roles.php');


$p = new Perfiles();
$rep = new Roles();


if(isset($_GET['case'])){
    $case=$_GET['case'];
}

//variables perfiles
if(isset($_POST['codperfil'])){  $codperfil = $_POST['codperfil'];}
if(isset($_POST['nombre_perfil'])){  $nombre_perfil = trim($_POST['nombre_perfil']);}
if(isset($_POST['rol'])){            $rol_perfil = $_POST['rol']; }
if(isset($_POST['estado_perfil'])){ $estado_perfil = $_POST['estado_perfil'];
}
// FIN VARIABLE PERFILES

$createtable = array(
  'data' => array()
);


switch ($case) {
  /************************  procesos para gestionarperfiles.php ****************************/
    case 'loadPerfiles':
      $table = $p->LoadPerfiles(); 
      $i = 1;
      foreach ($table as $datarow => $data) {

        if($data["estado_perfil"] == 'on'){
          $estado = 'Habilitado';
        }else if($data["estado_perfil"] == 'off'){
          $estado = 'Inhabilitado';
        }else{
          $estado = 'Error';
        }

        $edit='<div class="btn btn-sm btn-warning fa fa-pencil-square-o data-toggle="modal" data-target="#modal-EditPerfil" onclick="fmodalEditar('.$data["codigo_perfil"].',  \''.$data["estado_perfil"].'\')"></div>';
        $options = $edit;
        array_push($createtable['data'], array($i,
                                               $data["nombre_perfil"],
                                               $data["nombre_rol"],
                                               //$data["menus"], 
                                               $estado,
                                               $options));
        $i++;

      }
      $json = json_encode($createtable);
    break;

    case 'editItemPerfil':
      $query = $p->EditarPerfil($estado_perfil, $codperfil);
      if($query){
        $json = json_encode(array("success"=>true));
      }else{
        $json = json_encode(array("success"=>false,"mensaje" => "No se Actualizó la información. Por favor, intentelo de nuevo"));
      }
    break;


    case 'CargarPerfilDocente':
      $perfil = $p->CargarPerfilDocente();
      $id_perfil=$perfil['codigo_perfil'];
      
      if($perfil !=''){
       
        $json = json_encode(array("success"=>true,'CodigoPerfil'=>$id_perfil));
       
        }else{
       
        $json = json_encode(array("success"=>false,"mensaje" => "No se encontro perfil del docente"));
      }
   break;


       case 'CargarPerfilEstudiante':
      $perfil = $p->CargarPerfilEstudiante();
      $id_perfil=$perfil['codigo_perfil'];
      
      if($perfil !=''){
       
        $json = json_encode(array("success"=>true,'CodigoPerfil'=>$id_perfil));
       
        }else{
       
        $json = json_encode(array("success"=>false,"mensaje" => "No se encontro el perfil del estudiante"));
      }
   break;

    case 'loadRol':
      $table = $p->LoadRol();
      $json = json_encode($table);
    break;

    case 'loadPerfilesSelect':
      $sql = "SELECT codigo_perfil as cod, nombre_perfil as nombres_usuario FROM perfiles WHERE estado_perfil = 'on' ORDER BY nombre_perfil ASC";
     // $table = table($sql);
      $json = json_encode($table);
    break;

    case 'insertItemPerfil':
   
      $row =$rep->insertRol($nombre_perfil);
      
        $rol = $rep->rol($nombre_perfil);
        $r=$rol["codigo_rol"];
       
        if($row != ''){

        $validar=$p->ValidarPerfil($nombre_perfil);

        if ($validar == ''){

        $query = $p->InsertPerfil($r, $nombre_perfil, $estado_perfil);

        if($query){

            $json = json_encode(array("success"=>true));

        }else{

          $json = json_encode(array("success"=>false,"mensaje" => "Error al insertar Item de Perfil"));
        }
        } else{
          $json = json_encode(array("success"=>false,"mensaje" => "Perfil ya se encuentra creado"));
        }

       } else{
         $json = json_encode(array("success"=>false,"mensaje" => "Error al insertar Item de Rol"));
      } 
    break;
  /************************  FIN procesos para gestionarmenu.php **************************/
}
echo $json;


?>
