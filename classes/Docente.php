<?php 
require_once '../developer/PDOConn.php';

class Docente extends db
{

  public function loadDocente($whe)
  {
      $sql = "SELECT pmd.codigo, pd.nombre_largo, pp.nombre, pmd.estado
              FROM pro_modulo_docente pmd
              INNER JOIN pro_docente pd ON pd.codigo = pmd.coddocente 
              INNER JOIN pro_programa_modulo ppm ON ppm.codigo = pmd.codprograma_modulo
              INNER JOIN pro_programa pp ON pp.codigo = ppm.codprograma".$whe;
      $res = $this->table($sql);
      return $res;
  }

  public function EstadoDocente($codigo, $estado)
  {
    $update = "UPDATE pro_modulo_docente SET estado = :estado WHERE codigo = :codigo";
    $params = array(':codigo' => $codigo, ':estado' => $estado);
    $res = $this->query($update, $params);
    return $res;
  }

  public function loadDocentes()
  {
    $sql = "SELECT codigo as cod, nombre_largo as nombre FROM pro_docente WHERE estado = 'on'";
    $res = $this->table($sql);
    return $res;
  }
  public function loadProgr()
  {
    $sql = "SELECT codigo as cod, nombre as nombre FROM pro_programa WHERE estado = 'on'";
    $res = $this->table($sql);
    return $res;
  }

  public function CodAnioLectivo()
    {
      $sql =  "SELECT codigo as codigo, nombre FROM pro_aniolectivo WHERE estado = 'on'";
      $res = $this->row($sql);
      return $res;
    }

  public function LoadProgramas($codigo)
    {
      $sql = "SELECT DISTINCT (codprograma) FROM pro_programa_modulo WHERE codanio = :codanio AND estado = 'on'";
      $params = array(':codanio' => $codigo);
      $res = $this->table($sql, $params);
      return $res;
    }
  public function LoadProgramas2($codigo)
    {
      $sql = "SELECT codigo as cod, nombre FROM pro_programa WHERE estado = 'on' AND codigo ".$codigo;
      $res = $this->table($sql);
      return $res;
    }

  public function LoadModulos($codigo, $codigo1)
    {
      $sql = "SELECT DISTINCT (codmodulo) FROM pro_programa_modulo WHERE codprograma = :codprograma AND codanio = :codanio";
      $params = array(':codanio' => $codigo, ':codprograma' => $codigo1);
      $res = $this->table($sql, $params);
      return $res;
    }

  public function LoadModulos2($codigo)
    {
      $sql = "SELECT codigo as cod, nombre FROM pro_modulo WHERE codigo ".$codigo;
      $res = $this->table($sql);
      return $res;
    }

  public function LoadSedes($codigo, $codigo1, $codigo2)
    {
      $sql = "SELECT DISTINCT (codsede) FROM pro_programa_modulo WHERE codprograma = :codprograma AND codmodulo = :codmodulo AND codanio = :codanio";
      $params = array(':codanio' => $codigo, ':codprograma' => $codigo1, ':codmodulo' => $codigo2);
      $res = $this->table($sql, $params);
      return $res;
    }

  public function LoadSedes2($codigo)
    {
      $sql = "SELECT codigo_sede as cod, nombre_sede as nombre FROM pro_sedes WHERE codigo_sede ".$codigo;
      $res = $this->table($sql);
      return $res;
    }

public function LoadDisponibilidad($codigo, $codigo1, $codigo2, $codigo3)
    {
      $sql = "SELECT codigo, fechainicio, fechafin FROM pro_programa_modulo WHERE codprograma = :codprograma AND codmodulo = :codmodulo AND codanio = :codanio AND codsede = :codsede";
      $params = array(':codanio' => $codigo, ':codprograma' => $codigo1, ':codmodulo' => $codigo2, ':codsede' => $codigo3);
      $res = $this->table($sql, $params);
      return $res;
    }



  public function insertAsignacionDocente($codigo, $docente, $estado)
  {
     $insert = "INSERT INTO pro_modulo_docente (coddocente, codprograma_modulo, estado) VALUES (:coddocente, :codprograma_modulo, :estado)";
      $params = array(':codprograma_modulo' => $codigo, ':coddocente' => $docente, ':estado' => $estado);
      $res = $this->query($insert,$params);
      return $res;
  }
}


