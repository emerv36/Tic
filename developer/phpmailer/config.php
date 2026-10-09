<?php
$mail->IsSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPSecure = "tls";
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->Username = "info@scv.edu.co";
$mail->Password = str_replace(' ', '', "wcdt hhon vvku zzlg");
$mail->From ="info@scv.edu.co";
$mail->FromName='Notificación Scv';
$mail->SMTPKeepAlive = true; 
$mail->SMTPDebug  = 0;
$mail->CharSet='utf-8';
?>