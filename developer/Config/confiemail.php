<?php
  // Rutas absolutas y require_once: permite cargarlo desde CLI (developer/bin) y varias veces por petición
  require_once(__DIR__ . "/../phpmailer/class.phpmailer.php");
  require_once(__DIR__ . "/../phpmailer/class.smtp.php");
  $mail = new PHPMailer();
  $mail->IsSMTP();
  $mail->SMTPAuth = true;
  $mail->SMTPKeepAlive = true; 
  $mail->SMTPSecure = "tls";
  $mail->SMTPDebug  = 0;
 $mail->Host = 'smtp.gmail.com';
 $mail->Port = 587;
 $mail->Username = "info@scv.edu.co";
 $mail->Password = str_replace(' ', '', "wcdt hhon vvku zzlg");
 $mail->Charset='utf-8';
?>