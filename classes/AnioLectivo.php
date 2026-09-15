<?php 
require_once '../developer/PDOConn.php';

class AnioLectivo extends db
{


  public function loadLectivo($whe)
  {
      $sql = "SELECT codigo, nombre, estado FROM pro_aniolectivo ".$whe;
      $res = $this->table($sql);
      return $res;
  }

  public function editItemLectivo($nombre, $codigo)
  {

    $update = "UPDATE pro_aniolectivo SET nombre = :nombre WHERE codigo = :codigo";   
    $params = array(':nombre' => $nombre, ':codigo'=> $codigo);
    $res = $this->query($update, $params);
    return $res;

  }

  public function insertLectivo($nombre, $estado)
  {
     $insert = "INSERT INTO pro_aniolectivo (nombre, estado) VALUES (:nombre, :estado)";
      $params = array(':nombre' => ($nombre), ':estado' => $estado);
      $res = $this->query($insert,$params);
      return $res;
  }


  public function editEstadoLectivo($codusuario, $estado)
  {
    $update = "UPDATE pro_aniolectivo SET estado = :estado WHERE codigo = :codusuario";
    $params = array(':codusuario' => $codusuario, ':estado' => $estado);
    $res = $this->query($update, $params);
    return $res;
  }
}