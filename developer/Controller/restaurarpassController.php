<?php
  session_start();
  date_default_timezone_set("America/Bogota");
  header('content-type: application/json; charset=utf-8');
  require('../Models/Restaurarpass.php');
  $res= new Restaurarpass ();
  
  if(isset($_GET['case'])){  $case = $_GET['case']; }


//variables para actulizacion
  if(isset($_POST['txtRegistroPerfil'])){       $txtRegistroPerfil      = $_POST['txtRegistroPerfil']; }
  if(isset($_POST['txtRidinscripcion'])){       $txtRidinscripcion      = $_POST['txtRidinscripcion']; }
  if(isset($_POST['txtRegistroIdentidad'])){    $txtRegistroIdentidad   = $_POST['txtRegistroIdentidad']; }

  if(isset($_POST['txtRcodigo'])){              $txtRcodigo             = $_POST['txtRcodigo']; }
  if(isset($_POST['txtRidentidad'])){           $txtRidentidad          = $_POST['txtRidentidad']; }
  if(isset($_POST['txtRpassword'])){            $txtRpassword           = $_POST['txtRpassword']; }

  if(isset($_POST['txtDcodigo'])){              $txtDcodigo             = $_POST['txtDcodigo']; }
  if(isset($_POST['txtDidentidad'])){           $txtDidentidad          = $_POST['txtDidentidad']; }

  
//fin variables


 
 
 switch($case){

//case para restaurar pass de estudiantes
  case'RestaurarpassEstudiante':
            $txtPassword_estudiante=sha1($txtRegistroIdentidad);
        
              $query = $res->RestaurarpassEstudiante($txtRidinscripcion,$txtRegistroPerfil, $txtRegistroIdentidad, $txtPassword_estudiante);
                    if($query){
                        $json = json_encode(array("success" => true, "mensaje" => "¡Password restaurado!"));
                    }else{
                        $json = json_encode(array("success" => false, "mensaje" => "No se pudo restaurar el password."));
                    }
  break;
//fin case 



//case para restaurar pass de estudiantes
  case'RestaurarpassUsuario':
            $txtRpassword=sha1($txtRidentidad);
        
              $query = $res->RestaurarpassUsuario($txtRcodigo,$txtRidentidad);
                    if($query){
                        $json = json_encode(array("success" => true, "mensaje" => "¡Password restaurado!"));
                    }else{
                        $json = json_encode(array("success" => false, "mensaje" => "No se pudo restaurar el password."));
                    }
  break;
//fin case 



//case para restaurar pass de estudiantes
  case'RestaurarpassDocente':
            $txtDpassword=sha1($txtDidentidad);
        
              $query = $res->RestaurarpassDocente($txtDcodigo,$txtDidentidad, $txtDpassword);
                    if($query){
                        $json = json_encode(array("success" => true, "mensaje" => "¡Password restaurado!"));
                    }else{
                        $json = json_encode(array("success" => false, "mensaje" => "No se pudo restaurar el password."));
                    }
  break;
//fin case 




//case busqueda de estudiantes

 case 'BusquedaUsuarios':
       $txtusuario = $_POST['txtusuario'];

       $buscar=$res->BusquedaUsuarios($txtusuario);

       $codigo_usu=$buscar['codigo_usu'];

       if($buscar!=''){

              $json = json_encode(array("success" => true, "codigo_usu" => $codigo_usu)); 


       }else{
        $json = json_encode(array("success" => false, "mensaje" => "No se encontrator resultados."));
       }

   break;    

}
echo $json;
