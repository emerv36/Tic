<?php 
require_once('../Config/PDOconn.php');

class MiPerfil extends db
{
  public function EditarUsuario ($nombre, $apellido)
  {
    $sql = "UPDATE usuario SET nombres_usuario = :nombres_usuario, apellidos_usuario = :apellidos_usuario  WHERE codigo_usu = :codusu";
    $params = array(':nombres_usuario' => $nombre, ':apellidos_usuario' => $apellido, ':codusu'=>$_SESSION['IN_codigo_usuCA']);
    $res = $this->query($sql, $params);
    return $res;
  }

  public function BuscarUsuario ()
  {
    $sql = "SELECT * FROM usuario WHERE codigo_usu = :codusuario";
    $params = array(':codusuario' => $_SESSION['IN_codigo_usuCA']);
    $res = $this->row($sql, $params);
    return $res;
  }
      

  public function BuscarCorreo ($email)
  {
    $sql = "SELECT * FROM usuario WHERE email = :email";
    $params = array(':email' => $email);
    $res = $this->row($sql, $params);
    return $res;
  }

  public function EditarContraseña ($contraseña, $codusuario)
  {
    $sql = "UPDATE usuario SET password = :password WHERE codigo_usu = :codusuario";
    $params = array(':password'=> $contraseña, ':codusuario'=>$codusuario);
    $res = $this->query($sql, $params);
    return $res;
  }

  public function uploadFotoPerfil ($urlFoto, $codusuario)
  {
    $sql = "UPDATE usuario SET img = :urlfoto WHERE codigo_usu = :userid"; 
    $params = array(':urlfoto' => $urlFoto, ':userid' => $codusuario);
    $res = $this->query($sql, $params);
    return $res;
  }

  public function validarCorreoRecuperacion($email)
  {
    $sql =  "SELECT * FROM usuario WHERE email = :email"; 
    $params = array(':email' => $email);
    $res = $this->row($sql, $params);
    return $res;
  }

    public function insertarToken($email, $token)
  {
    $sql =  "UPDATE usuario SET remember_token = :token WHERE email = :email"; 
    $params = array(':email' => $email, ':token' => $token);
    $res = $this->query($sql, $params);
    return $res;
  }


    
} 

?>