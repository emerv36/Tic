<?php 
require_once '../developer/PDOConn.php';

class Estudiante extends db
{


  public function loadEstudiante($whe)
  {
      $sql = "SELECT codigo_usu, es.codigo, es.identificacion, es.pnombre, es.papellido, es.snombre, es.sapellido, es.nombre_largo, es.genero, es.fechanacimiento, es.direccion, es.telefono, es.celular, es.codeps, es.email, es.codtiposangre, es.coestrato, estado FROM est_estudiante es 
        INNER JOIN usu_usuarios usu ON es.identificacion = usu.identificacion ".$whe;
      $res = $this->table($sql);
      return $res;
  }

  public function editItemEstudiante($identificacion, $pnombre, $papellido, $snombre, $sapellido, $nombre, $genero, $fecha, $direccion, $telefono, $celular, $email, $codeps, $codtiposangre, $coestrato, $codigo)
  {

    $update = "UPDATE est_estudiante SET identificacion = :identificacion, pnombre = :pnombre, papellido = :papellido, snombre = :snombre, sapellido = :sapellido, nombre_largo = :nombre, genero = :genero, fechanacimiento = :fecha, direccion = :direccion, telefono = :telefono, celular = :celular, email = :email, codeps = :codeps, codtiposangre = :codtiposangre, coestrato = :coestrato WHERE codigo = :codigo";   
    $params = array(':identificacion' => $identificacion, ':pnombre' => $pnombre, ':papellido' => $papellido, ':snombre' => $snombre, ':sapellido' => $sapellido, ':nombre' => $nombre, ':genero' => $genero, ':fecha' => $fecha, ':direccion' => $direccion, ':telefono' => $telefono, ':celular' => $celular, ':email' => $email, ':codeps' => $codeps, ':codtiposangre' => $codtiposangre, ':coestrato' => $coestrato, ':codigo' => $codigo);
    $res = $this->query($update, $params);
    return $res;

  }

  public function insertEstudiante($identificacionE, $pnombre, $papellido, $snombre, $sapellido, $nombre, $genero, $fecha, $direccion, $telefono, $celular, $email, $codeps, $codtiposangre, $coestrato)
  {
     $insert = "INSERT INTO est_estudiante (identificacion, pnombre, papellido, snombre, sapellido, nombre_largo, genero, fechanacimiento, direccion, telefono, celular, email, codeps, codtiposangre, coestrato) VALUES (:identificacion, :pnombre, :papellido, :snombre, :sapellido, :nombre, :genero, :fecha, :direccion, :telefono, :celular, :email, :codeps, :codtiposangre, :coestrato)";
      $params = array(':identificacion' => $identificacionE, ':pnombre' => $pnombre, ':papellido' => $papellido, ':snombre' => $snombre, ':sapellido' => $sapellido, ':nombre' => $nombre, ':genero' => $genero, ':fecha' => $fecha, ':direccion' => $direccion, ':telefono' => $telefono , ':celular' => $celular, ':email' => $email, ':codeps' => $codeps, ':codtiposangre' => $codtiposangre, ':coestrato' => $coestrato);
      if($this->query($insert,$params)){
      return true;
    }
    else{
      return false;
    }
  }


  public function buscarEstudianteIDxCodigo($codigo)
  {

    $sql = "SELECT * FROM est_estudiante WHERE codigo = :codigo";
    $params = array(':codigo' => $codigo);
    $res = $this->row($sql,$params);
    return $res;

  }


  public function editEstadoEstudiante($codusuario, $estado)
  {
    $update = "UPDATE usu_usuarios SET estado = :estado WHERE codigo_usu = :codusuario";
    $params = array(':codusuario' => $codusuario, ':estado' => $estado);
    $res = $this->query($update, $params);
    return $res;
  }



}