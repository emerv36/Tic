<?php
session_start();
date_default_timezone_set('America/Bogota');
header('content-type: application/json; charset=utf-8');


require_once '../classes/Usuarios.php';

//require_once 'PDOConn.php';

if(isset($_GET['case'])){
    $case=$_GET['case'];
}

// VARIABLE Login
if(isset($_POST['usuario'])){
  $usuario = strtolower(trim($_POST['usuario']));
}
if(isset($_POST['password'])){
  $password = strtolower(trim($_POST['password']));
}

$l = new Usuarios();

switch ($case) {

    /************************  procesos para login.php ****************************/
    case 'iniciarsesion':
      $row = $l->login($usuario);

      if($row != ''){
      	if($row['password']==sha1($password)){
      		if ($row['email_confirmado'] == true) {
            if($row['estado']=='on'){
              $_SESSION['session'] = true;
              $_SESSION['codigo_usu']=$row['codigo_usu'];
              $_SESSION['usuario']=$row['usuario'];
              $_SESSION['nombre_usu']=$row['nombre_usu'];
              $_SESSION['codperfil']=$row['codigo_perfil'];
              $_SESSION['nombre_perfil']=$row['nombre_perfil'];
              $_SESSION['foto']=$row['foto'];

              $_SESSION['codrol']=$row['codigo_rol'];
              $_SESSION['nombre_rol']=$row['nombre_rol'];
            
              $json=json_encode(array("success"=>true));
            }else{
              $json=json_encode(array("success"=>false,'mensaje' => "Error: Usuario deshabilitado"));
            }
          }else{
            $json=json_encode(array("success"=>false,'mensaje' => "Error: Usuario no confirmado, por favor confirme su correo electronico."));
          }
      	}else{

      		$json=json_encode(array("success"=>false,'mensaje' => "Error: Usuario y Contraseña no coinciden"));
      	}
      }else{
        $json=json_encode(array("success"=>false,'mensaje' => "Error: Usuario inexistente ".$row));
      }

    break;

}
echo $json;

?>

