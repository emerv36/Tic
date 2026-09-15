<?php
session_start();
require('../Models/Login.php');
//require('../Config/PDOconn.php');
//require_once ('../Models/Login.php');
//require_once ("../Config/PDOConn.php");
//$db = new db(); 
$l = new Login();

require("../phpmailer/class.phpmailer.php");
require("../phpmailer/class.smtp.php");


$mail=new PHPMailer();
$mail->setLanguage('es');
$from='info@siiga.com.co';
$fromName='REGISTRO DE USUARIO SIIGA';
$host='mail.siiga.com.co ';
$username='info';
$password='info@2020..';
$port='465';
$secure='false';
$emailadmin='josegaitan123@gmail.com';
$mail->Host=$host;
$mail->SMTauth=true;
$mail->UserName=$username;
$mail->Password=$password;
$mail->Port=$port;
$mail->SMTPSecure=$secure;

$mail->from =$from;
$mail->fromName =$fromName;
$mail->addReplyTo($from,$fromName);

$mail->setFrom('info@siiga.com.co','Nuevo registro de usuario');
$mail->addBCC($emailadmin,'Recuperación de contraseña usuario Siiga');
$mail->isHTML(true);
$mail->Charset='utf-8';
$mail->WordWrap=50;
$mail->AltBody = "";

  if(isset($_GET['case'])){ $case=$_GET['case']; }
  // variables login
  if(isset($_POST['usuario'])){  $usuario = trim($_POST['usuario']);}
  if(isset($_POST['password'])){ $password = strtolower(trim($_POST['password']));  }
  // fin variables login
 
  // variable para la consulta del web service
  if(isset($_POST['identificacion'])){ $identificacion=$_POST['identificacion']; }
  // fin 

  //navegador del visitante
  $user_agent = $_SERVER['HTTP_USER_AGENT'];
  $ip         =$l->getRealIP();




// variables de recuperar cuenta
if(isset($_POST['RecuperarCuenta'])){
  $RecuperarCuenta= $_POST['RecuperarCuenta'];
}

if(isset($_POST['PasswordCambiar'])){
  $Contra=$_POST['PasswordCambiar'];
}

if(isset($_POST['RecuperarPasswor'])){
   $recuperarContra=$_POST['RecuperarPasswor'];
}


//fin variables de recuperar cuenta
$createtable = array(
  'data' => array()
);
    
switch ($case) {
  case 'iniciarsesion':
  //    $server = $db->urlservidor();      
      $row1 = $l->Iniciarsesion($usuario);
   
      if($row1 != ''){
       if($row1['password']==sha1($password)){

          if($row1['estado']=='on'){
            if($row1['email_confirmado']=='t'){
           
            session_regenerate_id(true);
            $_SESSION['SiigaBv'] = true;
            $_SESSION['IN_codigo_usuCA']   =$row1['codigo_usu'];
            $_SESSION['IN_usuario']        =$row1['usuario'];
            $_SESSION['IN_email']          =$row1['email'];
            $_SESSION['IN_codperfil']      =$row1['codigo_perfil'];
            $_SESSION['IN_nombre_perfil']  =$row1['nombre_perfil'];
            $_SESSION['IN_foto']           =$row1['img_usuario'];
            $_SESSION['IN_nombre']         =$row1['email'];
            $_SESSION['IN_codrol']         =$row1['codigo_rol'];
            $_SESSION['IN_nombre_rol']     =$row1['nombre_rol'];
            $_SESSION['nombres']           =$row1['nombres_usuario'];
           // $_SESSION['server']=$server;
            
            $query=$l->auditoria($_SESSION['nombres'],$_SESSION['IN_nombre_perfil'], $user_agent, $ip);    
                       
            $json=json_encode(array("success"=>true,'mensaje'=>"Bienvenido al sistema".$_SESSION['nombres']));
          
          }else{

            $json=json_encode(array("success"=>false,'mensaje' => "Error: Debe confirmar su correo electrónico."));
  
          }
          
       } else{
            $json=json_encode(array("success"=>false,'mensaje' => "Error: Usuario deshabilitado"));
          }

        }else{

          $json=json_encode(array("success"=>false,'mensaje' => "Usuario o contraseña no existente, verifique por favor"));
        }
      }else{
        $json=json_encode(array("success"=>false,'mensaje' => "Usuario o contraseña no existente, verifique por favor"));
    
      }
  
    break;
   // fin case inicio de session

   // case consultar
    

 // case recuperar cuenta usuario

 case 'recuperarCuentaUsuario':
 $mail->Subject = utf8_decode("Recuperación de contraseña.");

 $respuesta = $l->validarCorreoRecuperacion($RecuperarCuenta);
 if($respuesta){ 
  
   $token = $db->generatecod();
   $query = $l->insertarToken($RecuperarCuenta, $token);
   
     if($query
     ){
      
       $urlconfirmar = $db->urlservidor()."RecuperarCuenta/".$db->base64url_encode($RecuperarCuenta.','.$token);

       $html = '<!DOCTYPE html>';
       $html .= '<html>';
       $html .= '<head>';
       $html .= '<title>System Center</title>';
       $html .= '</head>';
       $html .= '<style> 
             body{
               text-align:center;
             }
           /*button*/
             .button{
               display: inline-block;
               padding:0 10px;
               color: #4c4c4c;
               height: 40px;
               text-align: center;
               line-height: 35px;
               white-space: nowrap;
                cursor: pointer;
               box-sizing: border-box; 
               border: 1px solid #CCCCCC;
               margin-bottom:10px;
               border-radius:3px;
               font-weight: bold;
               outline: 0;
               text-decoration: none;
             }
             .primary-button{
               color: #fff !important;
               background: #1b3357 !important;
               border: 1px solid #1b3357;
             }
         </style>';
       $html .= '<body style="margin: 0; padding: 0;">';
       $html .= '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
       $html .= '<tr>';
       $html .= '<td style="padding: 10px 0 30px 0;">';
       $html .= '<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc; border-collapse: collapse;">';
       $html .= '<tr>';
       $html .= '<td align="center" style="color: #153643; font-size: 28px; font-weight: bold; font-family: Arial, sans-serif;">';
       $html .= '<img src="../../assets/images/logo-system-06.png" alt="" style="height: 70px" />';
       //$html .= '<img src="https://scv.edu.co/portal/wp-content/uploads/2023/03/Mesa-de-trabajo-2.png" style="height: 70px"/><br>';
       $html .= '</td>';
       $html .= '</tr>';
       $html .= '<tr>';
       $html .= '<td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">';
       $html .= '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
       $html .= '<tr>';
       $html .= '<td style="color: #153643; font-family: Arial, sans-serif; font-size: 24px;">';
       $html .= '<b>Hola, '.strtoupper($respuesta['nombres_usuario']).'</b>';
       $html .= '</td>';
       $html .= '</tr>';
       $html .= '<tr>';
       $html .= '<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">';
       $html .= 'Hemos recibido una solicitud para poder acceder a tu cuenta: ';
       $html .= '<b>'.$RecuperarCuenta.'</b><br>';
       $html .= '</td> ';
       $html .= '</tr>';
       $html .= '<tr>';
       $html .= '<td style="padding: 0px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 15px; line-height: 20px;">';
       $html .= 'Si no solicitaste restablecer tu contraseña, es posible que otra persona esté intentando acceder a tu cuenta; si es así, haz caso omiso a este correo.';
       $html .= '<br><br>';
       $html .= '<a class="button primary-button" style="text-align:center" href="'.$urlconfirmar.'">CAMBIAR CONTRASEÑA</a>';
       $html .= '</td>';
       $html .= '</tr>';
       $html .= '</table>';
       $html .= '</td>';
       $html .= '</tr>';
       $html .= '</table>';
       $html .= '</td>';
       $html .= '</tr>';
       $html .= '</table>';
       $html .= '</body>';
       $html .= '</html>';

       $mail->MsgHTML($html);
       $mail->AddAddress($RecuperarCuenta,"");

       $mail->IsHTML(true);
       $mail->smtpConnect(
       array(
         "ssl" => array(
             "verify_peer" => false,
             "verify_peer_name" => false,
             "allow_self_signed" => true
         )
       )
       );

            if ($mail->Send()) {
              $json = json_encode(array("success" => true, "mensaje"=>"Correo enviado exitosamente.")); 
            }else{
              $json=json_encode(array("success"=>true,"mensaje"=>$mail->ErrorInfo));
            }
            
          }else{
            $json = json_encode(array("success" =>false,"mensaje"=>"No se ha podido insertar el Usuario"));
          }
          }else{
            $json = json_encode(array("success"=>false,"mensaje" => "El correo electrónico no es valido."));
          }
    break;

// fin case recuperar cuenta usuario

// case cambiar contraseña
case 'CambiarPassword':

      $row = $l->BuscarCorreo($recuperarContra);
      if($row != ''){
        // if($row['password']!=sha1($Contra)){
          $query = $l->EditarContraseña(sha1($Contra), $row['codigo_usu']);
          if($query){
            $json = json_encode(array("success"=>true));
          }else{
            $json = json_encode(array("success"=>false,"mensaje" => "No se Actualizó la información. Por favor, intentelo de nuevo"));
          }
        // }else{
        //   $json = json_encode(array("success"=>false,"mensaje" => "Su nueva contraseña no coincide con la anterior"));
        // }
      }else{
          $json = json_encode(array("success"=>false,"mensaje" => "Error"));
      }
    break;
//fin cambiar contraseña

case 'VerificarCarnet': 
  if(isset($_POST['txtidentidad'])){ $txtidentidad =$_POST['txtidentidad'];  }
 
  $query = $l->VerificarCarnet($txtidentidad);
  if($query != ''){
     $estado=$query['valor_registro'];
     $valor=$query['estado_inscripcion'];
     $nombre=$query['nombre_estudiante'];
     $apellidos=$query['apellido_estudiante'];
     $lugar = 'PRINCIPAL';
     if($query['lugar_reclamo']!="")
     $lugar = $query['lugar_reclamo'];
     if ($estado==1){
        $json = json_encode(array("success" =>true,"mensaje"=>"Apreciado estudiante:"." ".$nombre." ".$apellidos." "."Su carnet se encuentra en estado"." ".$valor." "."consulte nuevamente la próxima semana"));
        }else if ($estado==2){
         $json = json_encode(array("success" =>true,"mensaje"=>"Apreciado estudiante: "." ".$nombre." ".$apellidos." "."Su carnet se encuentra en estado"." ".$valor." "."puede acercarse a las instalaciones de la sede $lugar para su entrega"));
        }else if ($estado==3){
       $json = json_encode(array("success" =>true,"mensaje"=>"Apreciado estudiante: "." ".$nombre." ".$apellidos." "."Su carnet se encuentra en estado"." ".$valor." "."verifique en el departamento de nuevas tecnologías"));
     }
   }else{
    $json = json_encode(array("success" =>false,"mensaje"=>"Numero de identidad no se encuentra registrada en la base de datos"));
  }

}
echo $json;
