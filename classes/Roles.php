<?php 
require_once '../developer/PDOConn.php';

class Roles extends db
{

  public function loadRoles($whe)
  {
      $sql = "SELECT codigo_rol, nombre_rol, menus, estado_rol FROM usu_roles ".$whe;
      $res = $this->table($sql);
      return $res;
  }

  public function insertRol($nombre_rol, $estado_rol)
  {
     $insert = "INSERT INTO usu_roles (nombre_rol, menus, estado_rol) VALUES (:nombre_rol, :menus, :estado_rol)";
      $params = array(':nombre_rol' => ucwords($nombre_rol), ':menus' => '1', ':estado_rol' => $estado_rol);
      $res = $this->query($insert,$params);
      return $res;
  }


  public function editItemRol($nombre_rol, $codigo_rol)
  {

    $update = "UPDATE usu_roles SET nombre_rol = :nombre_rol WHERE codigo_rol = :codigo_rol";   
    $params = array(':nombre_rol' => $nombre_rol, ':codigo_rol'=>$codigo_rol);
    $res = $this->query($update, $params);
    return $res;

  }


  public function editEstadoRol($codusuario, $estado)
  {
    $update = "UPDATE usu_roles SET estado_rol = :estado_rol WHERE codigo_rol = :codigo_rol";
    $params = array(':codigo_rol' => $codusuario, ':estado_rol' => $estado);
    $res = $this->query($update, $params);
    return $res;
  }
}