<?php 
require_once '../developer/PDOConn.php';

class Usuarios extends db
{
  public function loadeps()
  {
    $sql = "SELECT codigo as cod, nombre as nombre FROM con_parametro_valor WHERE codparametro = ( SELECT codigo FROM    con_parametro WHERE codigo = 1 )";
    $res = $this->table($sql);
    return $res;
  }

  public function loadtiposangre()
  {
    $sql = "SELECT codigo as cod, nombre as nombre FROM con_parametro_valor WHERE codparametro = ( SELECT codigo FROM    con_parametro WHERE codigo = 2)";
    $res = $this->table($sql);
    return $res;
  }

  public function login($usuario)
  {
    $sql="SELECT us.codigo_usu, us.usuario, us.password, us.estado, us.nombre_usu, us.foto, us.estado, pf.codigo_perfil, pf.nombre_perfil, ro.codigo_rol, ro.nombre_rol, us.email_confirmado FROM usu_usuarios us
                  INNER JOIN usu_perfiles pf ON pf.codigo_perfil = us.codperfil_fk 
                  INNER JOIN usu_roles ro ON ro.codigo_rol = pf.codrol_fk
                  WHERE us.email = :usuario";
    $params = array(':usuario'=>$usuario);
    $res = $this->row($sql,$params);
    return $res;
  }


  public function loadUsuarios($whe)
  {

    $sql = "SELECT codigo_usu, usuario, codperfil_fk, estado, nombre_usu, foto, identificacion, email, email_confirmado, pf.nombre_perfil 
            FROM usu_usuarios 
            INNER JOIN usu_perfiles pf ON pf.codigo_perfil = codperfil_fk ".$whe." ORDER BY codigo_usu ASC";
            $res = $this->table($sql);
            return $res;
  }


  public function loadPerfilesS()
  {
    $sql = "SELECT codigo_perfil, nombre_perfil, estado_perfil FROM usu_perfiles WHERE estado_perfil = :estado ORDER BY nombre_perfil ASC";
    $params = array(':estado' => 'on');
    $res = $this->table($sql, $params);
    return $res;
  }


  public function editEstadoCliente($codusuario, $estado)
  {
    $update = "UPDATE usu_usuarios SET estado = :estado WHERE codigo_usu = :codusuario";
    $params = array(':codusuario' => $codusuario, ':estado' => $estado);
    $res = $this->table($update, $params);
    return $res;
  }

  public function urlservidor(){
      $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/systemcenter/";
      // $actual_link = "http://aplicaciones.americana.edu.co:9090/uve/pg/";
      return $actual_link;
  }

  public function editModalUsuario($codusuario, $nombre, $identificacion, $email)
  {

    $update = "UPDATE usu_usuarios SET nombre_usu = :nombre, identificacion = :identificacion, email = :email WHERE codigo_usu = :codusuario";
    $params = array(':codusuario' => $codusuario, ':nombre' => $nombre, ':identificacion' => $identificacion, ':email' => $email);
    $res = $this->table($update,$params);
    return $res;

  }
  public  function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

  public function generatecod() {
      $randomString = str_shuffle("ghijklx6789".uniqid());
      return $randomString;
  } 
  
  public function insertUsuario($identificacion, $pass, $codperfil, $estado, $nombre, $email, $token)
  {
    $insert = "INSERT INTO usu_usuarios (usuario, identificacion, password, codperfil_fk, estado, nombre_usu, email, codvalidacion) VALUES (:usuario, :identificacion, :pass, :codperfil, :estado, :nombre, :email, :codvalidacion)  RETURNING codigo_usu";
      $params = array(':usuario' => $email, ':identificacion' => $identificacion, ':pass' =>  sha1($pass), ':codperfil' => $codperfil, ':estado' => $estado, ':nombre' => $nombre, ':email' => $email, ':codvalidacion' => $token);
      $res = $this->DataRow($insert,$params);
      return $res;  
  }

  public function insertUsuarioArea($codusuario, $codareanivel)
  {
    $insert = "INSERT INTO usuario_area (codusuario, codarea_nivel) VALUES (:codusuario, :codareanivel)";
    $params = array(':codusuario' => $codusuario, ':codareanivel' =>  $codareanivel);
    $res = $this->query($insert,$params);
    return $res;  
  }

  public function loadAreasxNivel($codnivel)
  {
    
    $sql = "SELECT an.*, a.nombre as area FROM
            area_nivel an INNER JOIN area a ON an.codarea=a.codigo
            WHERE codnivel=:codnivel";
            $params = array(':codnivel' => $codnivel);
            $res = $this->table($sql, $params);
            return $res;
  }


  public function editUsuarioNombre($identificacion, $identificacionNew, $nombre)
  {

    $update = "UPDATE usu_usuarios SET nombre_usu = :nombre, identificacion = :identificacionNew WHERE identificacion = :identificacion";
    $params = array(':nombre' => $nombre, ':identificacionNew' => $identificacionNew, ':identificacion' => $identificacion);
    $res = $this->query($update,$params);
    //echo $res;
    return $res;  

  }


  public function buscarUsuarioID($identificacion)
  {

    $sql = "SELECT * FROM usu_usuarios WHERE identificacion = :identificacion";
    $params = array(':identificacion' => $identificacion);
    $res = $this->row($sql,$params);
    return $res;

  }


  public function insertUsuarioBus($usuario, $identificacion, $pass, $codperfil, $nombre, $email)
  {
    $insert = "INSERT INTO usu_usuarios (usuario, identificacion, password, codperfil_fk, estado, nombre_usu, email) VALUES (:usuario, :identificacion, :pass, :codperfil, :estado, :nombre, :email)  RETURNING codigo_usu";
      $params = array(':usuario' => $usuario, ':identificacion' => $identificacion, ':pass' =>  sha1($pass), ':codperfil' => $codperfil, ':estado' => 'on', ':nombre' => $nombre, ':email' => $email);
      $res = $this->DataRow($insert,$params);
      return $res;  
  }

  public function ValidarCorreo($email){
    $sql = "SELECT * FROM usu_usuarios WHERE email = :email";
    $params = array(':email' => $email);
    $res = $this->table($sql, $params);
    return $res;
  }
}