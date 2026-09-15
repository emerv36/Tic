<?php
  require("../phpmailer/class.phpmailer.php"); 
  require("../phpmailer/class.smtp.php");
  $mail = new PHPMailer();
  $mail->IsSMTP();
  $mail->SMTPAuth = true;
  $mail->SMTPKeepAlive = true; 
  $mail->SMTPSecure = "tls";
  $mail->SMTPDebug  = 0;
 $mail->Host = 'smtp.gmail.com';
 $mail->Port = 587;
 $mail->Username = "info@scv.edu.co";
 $mail->Password = "info2021*";
 $mail->Charset='utf-8';
?>