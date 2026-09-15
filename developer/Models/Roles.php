<?php 
require_once('../Config/PDOconn.php');
class Roles extends db
{
  public function LoadRoles ()
  {
    $sql = "SELECT r.codigo_rol, r.nombre_rol, r.menus, r.estado_rol
              FROM roles r ORDER BY r.codigo_rol DESC";
    $res = $this->table($sql);
    return $res;
  }

  public function rol($codigo){
    $sql= "SELECT codigo_rol, nombre_rol FROM roles WHERE nombre_rol =:codigo_rol";
    $params = array(':codigo_rol'=>$codigo);
    $respuesta = $this->row($sql, $params);
    return $respuesta;
  }

  public function editRol ($nombre_rol, $estado_rol, $codrol)
  {
    $sql = "UPDATE roles SET nombre_rol = :nombre_rol, estado_rol = :estado WHERE codigo_rol = :codrol";
    $params = array(':nombre_rol' => $nombre_rol, ':estado' => $estado_rol, ':codrol'=>$codrol);
    $res = $this->query($sql, $params);
    return $res;
  }

  public function insertRol ($nombre)
  {
    $sql = "INSERT INTO roles (nombre_rol, estado_rol) VALUES (:nombre_rol, :estado_rol)";
    $params = array(':nombre_rol' => $nombre, ':estado_rol'=>'on');
    $res = $this->query($sql, $params);
    return $res;
  } 

  public function BuscarRol ($nombre, $where)
  {
      $sql = "SELECT codigo_rol, imagen, nombre_menu, nivel, orden, codsuperior, link, estado, target FROM menu WHERE nombre_menu LIKE '%".$nombre_menu."%' ".$where." ORDER BY codigo_menu ASC";
    $params = array(':nombre_rol' => $nombre, ':estado' => $estado);
    $res = $this->query($sql, $params);
    return $res;
  } 

  public function editarEstadoRoles ($codusuario, $estado)
  {
    $sql = "UPDATE roles SET estado_rol = :estado WHERE codigo_rol = :codusuario";
    $params = array(':codusuario' => $codusuario, ':estado' => $estado);
    $res = $this->query($sql, $params);
    return $res;
  }
}