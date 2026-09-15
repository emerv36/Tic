<?php 
require_once '../developer/PDOConn.php';

class Modulo extends db
{

  public function loadSModulos($whe)
  {
      $sql = "SELECT pm.codigo as code, pm.nombre as modulo, pm.descripcion as descripcion, codprograma, pp.nombre as programa, pm.estado as estado FROM pro_modulo pm
                      INNER JOIN pro_programa pp ON pp.codigo = codprograma ".$whe;
      $res = $this->table($sql);
      return $res;
  }

  public function insertModulo($nombre, $descripcion, $programa, $estado)
  {
     $insert = "INSERT INTO pro_modulo (nombre, descripcion, codprograma, estado) VALUES (:nombre, :descripcion, :codprograma, :estado)";
      $params = array(':nombre' => ucwords($nombre), ':descripcion' => $descripcion, ':codprograma' => $programa, ':estado' => $estado);
      $res = $this->query($insert,$params);
      return $res;
  }


  public function editItemModulo($nombre, $descripcion, $codigo)
  {

    $update = "UPDATE pro_modulo SET nombre = :nombre, descripcion = :descripcion WHERE codigo = :codigo";   
    $params = array(':nombre' => $nombre, ':descripcion' => $descripcion, ':codigo'=> $codigo);
    $res = $this->query($update, $params);
    return $res;

  }

  // public function loadPerfilesS()
  // {
  //   $sql = "SELECT codigo_sede, nombre_sede, estado_sede FROM pro_sedes WHERE estado_sede = :estado ORDER BY nombre_sede ASC";
  //   $params = array(':estado' => 'on');
  //   $res = $this->table($sql, $params);
  //   return $res;
  // }


  public function editEstadoModulo($codusuario, $estado)
  {
    $update = "UPDATE pro_modulo SET estado = :estado WHERE codigo = :codusuario";
    $params = array(':codusuario' => $codusuario, ':estado' => $estado);
    $res = $this->query($update, $params);
    return $res;
  }

}