<?php 
require_once '../developer/PDOConn.php';

class InfoDocente extends db
{

  public function loadInfoDocente($whe)
  {
      $sql = "SELECT codigo, identificacion, nombre, apellido, nombre_largo, telefono, celular, email, estado FROM pro_docente ".$whe;
      $res = $this->table($sql);
      return $res;
  }

  public function insertDocente($identificacion, $nombre, $apellido, $nombre_largo, $telefono, $celular, $email, $estado)
  {
    $insert = "INSERT INTO pro_docente (identificacion, nombre, apellido, nombre_largo, telefono, celular, email, estado) VALUES (:identificacion, :nombre, :apellido, :nombre_largo, :telefono, :celular, :email, :estado) RETURNING codigo";
    $params = array(':identificacion' => $identificacion, ':nombre' => $nombre, ':apellido' => $apellido, ':nombre_largo' => $nombre_largo, ':telefono' => $telefono, ':celular' => $celular, ':email' => $email, ':estado' => $estado);
    $res = $this->DataRow($insert,$params);
    return $res;
  }


  public function editItemDocente($identificacion, $nombre, $apellido, $nombre_largo, $telefono, $celular, $email, $codigo)
  {

    $update = "UPDATE pro_docente SET identificacion = :identificacion, nombre = :nombre, apellido = :apellido, nombre_largo = :nombre_largo, telefono = :telefono, celular = :celular, email = :email WHERE codigo = :codigo";   
    $params = array(':identificacion' => $identificacion, ':nombre' => $nombre, ':apellido'=> $apellido, ':nombre_largo' => $nombre_largo, ':telefono'=> $telefono, ':celular'=> $celular, ':email'=> $email, ':codigo' => $codigo);
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


  public function editEstadoDocente($cod, $estado)
  {
    $update = "UPDATE pro_docente SET estado = :estado WHERE codigo = :cod";
    $params = array(':cod' => $cod, ':estado' => $estado);
    $res = $this->query($update, $params);
    return $res;
  }

}