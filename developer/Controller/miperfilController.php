<?php
session_start();
date_default_timezone_set("America/Bogota");
header('content-type: application/json; charset=utf-8');


if(isset($_GET['case'])){
  $case=$_GET['case'];
}
if(isset($_POST['codusuario'])){
  $codusuario=$_POST['codusuario'];
}
if(isset($_POST['contraActual'])){
  $contraActual=$_POST['contraActual'];
}
if(isset($_POST['nuevaContra'])){
  $nuevaContra=$_POST['nuevaContra'];
}
if(isset($_GET['nombphoto'])){
  $nombphoto=$_GET['nombphoto'];
}
// Variables editar usuarios
if(isset($_POST['nombre'])){
  $nombre =$_POST['nombre'];
}
if(isset($_POST['apellido'])){
  $apellido =$_POST['apellido'];
}



$createtable = array(
  'data' => array()
);

//Llamados de clases
require('../Models/Miperfil.php');
require('../Config/PDOConn.php');

//Creación de objetos
$db = new db();
$mp = new MiPerfil();


switch ($case) {
      /************************ procesos para miperfil.php **************************/
    
      case 'editContrasena':
        $row = $mp->BuscarUsuario($_SESSION['IN_codigo_usuCA']);
  
        if($row != ''){
          if($row['password']==sha1($contraActual)){
            $query = $mp->EditarContraseña(sha1($nuevaContra), $_SESSION['IN_codigo_usuCA']);
            if($query){
              $json = json_encode(array("success"=>true));
            }else{
              $json = json_encode(array("success"=>false,"mensaje" => "No se Actualizó la información. Por favor, intentelo de nuevo"));
            }
          }else{
            $json = json_encode(array("success"=>false,"mensaje" => "La contraseña actual ingresada no es correcta"));
          }
        }
      break;
    
    /************************  FIN procesos para miperfil.php ****************************/
  
}
echo $json;
?>