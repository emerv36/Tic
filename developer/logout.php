<?php
session_start();
session_unset();
session_destroy();
unset($_SESSION['SiigaBv'] );
unset($_SESSION['IN_usuario']);
unset($_SESSION['IN_email']);
unset($_SESSION['IN_codperfil']);
unset($_SESSION['IN_nombre_perfil']);
unset($_SESSION['IN_foto']);
unset($_SESSION['nombre_perfil']);
unset($_SESSION['IN_nombre']);
unset($_SESSION['IN_nombres']);
unset($_SESSION['nombres']);
unset($_SESSION['IN_codrol']);
unset($_SESSION['IN_nombre_rol']);
header("Location: ../login");
?>
