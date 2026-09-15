<?php  
require_once '../developer/PDOConn.php';


Class MiPerfil extends db
{

 
 public function BuscarCorreo ($email)
  {
    $sql = "SELECT * FROM usu_usuarios WHERE email = :email_usuario";
    $params = array(':email_usuario' => $email);
    $res = $this->row($sql, $params);
    return $res;
  }
	public function editContrasena()
	{
		$select = "SELECT * FROM usu_usuarios WHERE codigo_usu = :codusuario";
		$paramsselect = array(':codusuario' => $_SESSION["codigo_usu"]);
	  	$row = $this->row($select,$paramsselect);
	  	return $row;
	}

	public function updatePassword($nuevaContra, $codusu)
	{
		$update = "UPDATE usu_usuarios SET password = :password WHERE codigo_usu = :codusuario";
	    $params = array(':password'=> sha1($nuevaContra), ':codusuario'=>$codusu);
	    $query = $this->query($update,$params);
	  	return $query;
	}

	public function changeNameProfile($nombre)
	{
		$update = "UPDATE usu_usuarios SET nombre_usu = :nombre WHERE codigo_usu = :codusu";
      	$params = array(':nombre' => $nombre, ':codusu'=>$_SESSION["codigo_usu"]);
      	$query = $this->query($update,$params);
	  	return $query;
	}

	public function changeImgProfile($urlFoto)
	{
		$sql="UPDATE usu_usuarios SET foto='". $urlFoto."' WHERE codigo_usu='".$_SESSION['codigo_usu']."'"; 
		$res = $this->query($sql);
		return $res;
	}

	    public function insertarToken($email, $token)
  {
    $sql =  "UPDATE usu_usuarios SET remember_token = :token WHERE email = :email"; 
    $params = array(':email' => $email, ':token' => $token);
    $res = $this->query($sql, $params);
    return $res;
  }

    public function validarCorreoRecuperacion($email)
  {
    $sql =  "SELECT * FROM usu_usuarios WHERE email = :email"; 
    $params = array(':email' => $email);
    $res = $this->row($sql, $params);
    return $res;
  }

}