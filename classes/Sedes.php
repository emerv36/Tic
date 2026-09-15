<?php 
require_once '../developer/PDOConn.php';

class Sedes extends db
{

  public function loadSedes($whe)
  {
      $sql = "SELECT codigo_sede, nombre_sede, estado_sede FROM pro_sedes ".$whe;
      $res = $this->table($sql);
      return $res;
  }

  public function insertSede($nombre_sede, $estado_sede)
  {
     $insert = "INSERT INTO pro_sedes (nombre_sede, estado_sede) VALUES (:nombre_sede, :estado_sede)";
      $params = array(':nombre_sede' => ucwords($nombre_sede), ':estado_sede' => $estado_sede);
      $res = $this->query($insert,$params);
      return $res;
  }


  public function editItemSede($nombre_sede, $codigo_sede)
  {

    $update = "UPDATE pro_sedes SET nombre_sede = :nombre_sede WHERE codigo_sede = :codigo_sede";   
    $params = array(':nombre_sede' => $nombre_sede, ':codigo_sede'=>$codigo_sede);
    $res = $this->query($update, $params);
    return $res;

  }


  public function editEstadoSede($codusuario, $estado)
  {
    $update = "UPDATE pro_sedes SET estado_sede = :estado WHERE codigo_sede = :codusuario";
    $params = array(':codusuario' => $codusuario, ':estado' => $estado);
    $res = $this->query($update, $params);
    return $res;
  }

}