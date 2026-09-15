<?php 
require_once '../developer/PDOConn.php';

class Programas extends db
{


  public function loadProgramas($whe)
  {
      $sql = "SELECT codigo, nombre, estado FROM pro_programa ".$whe;
      $res = $this->table($sql);
      return $res;
  }

  public function editItemPrograma($nombre, $codigo)
  {

    $update = "UPDATE pro_programa SET nombre = :nombre WHERE codigo = :codigo";   
    $params = array(':nombre' => $nombre, ':codigo'=> $codigo);
    $res = $this->query($update, $params);
    return $res;

  }

  public function insertPrograma($nombre, $estado)
  {
     $insert = "INSERT INTO pro_programa (nombre, estado) VALUES (:nombre, :estado)";
      $params = array(':nombre' => ($nombre), ':estado' => $estado);
      $res = $this->query($insert,$params);
      return $res;
  }


  public function editEstadoPrograma($codusuario, $estado)
  {
    $update = "UPDATE pro_programa SET estado = :estado WHERE codigo = :codusuario";
    $params = array(':codusuario' => $codusuario, ':estado' => $estado);
    $res = $this->query($update, $params);
    return $res;
  }
}