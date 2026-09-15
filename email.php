<?php
require("developer/phpmailer/class.phpmailer.php");
require("developer/phpmailer/class.smtp.php");

$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->SMTPKeepAlive = true; 
$mail->SMTPSecure = "tls";
$mail->SMTPDebug  = 0;
$mail->Host = "smtp.gmail.com";
$mail->Port = 587;

$mail->Username = "info@scv.edu.co";
$mail->Password = "info2021*";
$mail->SetFrom('info@scv.edu.co', utf8_decode('Nuevo Usuario SIIGA'));
$mail->Subject = utf8_decode("Bienvenido registro de usuario SIIGA");
$txtcorreo='asesorscv2021@gmail.com';
$mail->AltBody = "";

$html = "<!DOCTYPE html>";
$html .= "<html>";
$html .= "<head>";
$html .= "<title>Siiga Correo de confirmación usuario</title>";
$html .= "<meta charset='UTF-8'>";
$html .="</head>";
$html .="<body>";
$html .= 'Te damos la bienvenida a <b>SIIGA (Sistema de Gestión Académica)</b> <br>';
$html .= 'Tus datos de acceso a nuestra plataforma <br>';
$html .= 'Usuario: <b>'.$txtcorreo.'</b> <br>';
$html .= '<b style="font-size:14px;">Nota: Por razones de seguridad recomendamos realizar el cambio de contraseña al momento de realizar sesión por primera vez, el opción editar pérfil en la esquina superior derecha de la pantalla principal, solo tu eres el responsable de la privacidad de tus datos de accesso. <br> ¡Buena suerte! </b>';
$html .= '</span></p>';
$html .= '</body>';
$html .= '</html>';

$mail->MsgHTML($html);
$mail->AddAddress($txtcorreo);  
$mail->addCC('josegaitan123@yahoo.es');  
$mail->IsHTML(true);
$mail->Send();
$mail->smtpConnect( array("ssl" => array("verify_peer" => false,
                                        "verify_peer_name" => false,
                                        "allow_self_signed" => true)));

if ($mail->Send()) {
    $json = json_encode(array("success" => true, "Correo enviado satisfactoriamente"));     
   }else{
   $json=json_encode(array("success"=>false,"mensaje"=>$correo->ErrorInfo));
}
?>
