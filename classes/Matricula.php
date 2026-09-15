<?php 
require_once '../developer/PDOConn.php';

class Matricula extends db
{

  public function loadSedeM()
    {
      $sql = "SELECT codigo_sede as cod, nombre_sede as nombre FROM pro_sedes WHERE estado_sede = 'on'";
      $res = $this->table($sql);
      return $res;
    }


  public function loadLectivoM()
    {
      $sql = "SELECT codigo as cod, nombre as nombre FROM pro_aniolectivo WHERE estado = 'on'";
      $res = $this->table($sql);
      return $res;
    }

  public function loadProgramaM()
  {
    $sql = "SELECT codigo as cod, nombre as nombre FROM pro_programa WHERE estado = 'on'";
    $res = $this->table($sql);
    return $res;
  }

  public function loadMetodoPago()
  {
    $sql = "SELECT p.codigo AS cod, p.nombre AS nombre FROM con_parametro_valor p INNER JOIN con_parametro c on c.codigo = p.codparametro WHERE c.codigo = 5";
    $res = $this->table($sql);
    return $res;
  }

  public function loadDatosM($whe)
  {
      $sql = "SELECT prm.codigo, pro.nombre AS programa, mod.nombre AS modulo, prm.fechainicio, prm.fechafin, prm.valor FROM pro_programa_modulo prm
                INNER JOIN pro_sedes sed ON codsede = codigo_sede
                INNER JOIN pro_programa pro ON codprograma = pro.codigo 
                INNER JOIN pro_modulo mod ON prm.codmodulo = mod.codigo
                INNER JOIN pro_aniolectivo ani ON prm.codanio = ani.codigo WHERE prm.estado = 'on'".$whe;
          $res = $this->table($sql);
          return $res;
  }


  public function loadEstudianteM($whe)
  {
      $sql = "SELECT codigo, identificacion, nombre_largo, direccion, telefono, celular, email FROM est_estudiante ".$whe;
      $res = $this->table($sql);
      return $res;
  }

  public function insertMatricula($codes, $codprograma, $valor)
  { 
    $insert = "INSERT INTO aca_estumatricula (codestudiante, codprograma_modulo, fechamatricula, valorpagado) VALUES (:codes, :codprograma, :fecha, :valor)  RETURNING codigo";
      $params = array(':codes' => $codes, ':codprograma' => $codprograma, ':fecha' => $this->datetimeNow(), ':valor' => $valor);
      $res = $this->DataRow($insert,$params);
      return $res;  
  }

  public function insertPago($codestudiante, $pCantidad, $pMetodoPago, $pReferencia)
  { 
    $insert = "INSERT INTO tes_pagos_modulo (codestumatricula, fecha, valor, referencia, codtipopago) VALUES (:codestudiante, :fecha, :valor, :referencia, :codtipopago)  RETURNING codigo";
      $params = array(':codestudiante' => $codestudiante, ':fecha' => $this->datetimeNow(), ':valor' => $pCantidad, ':referencia' => $pReferencia, 'codtipopago' => $pMetodoPago);
      $res = $this->DataRow($insert, $params);
      return $res;  
  }

  public function notRepetMatricula($codes, $codprograma){
    $sql = "SELECT * FROM aca_estumatricula WHERE codestudiante = :codestudiante AND codprograma_modulo = :codprograma_modulo";
    $params = array('codestudiante' => $codes, 'codprograma_modulo' => $codprograma);
    $res = $this->row($sql, $params);
    return $res;
  }

  public function buscarEstudiantexID($identificacion)
  {
    $sql = "SELECT * FROM est_estudiante WHERE identificacion = :identificacion";
    $params = array(':identificacion' => $identificacion);
    $res = $this->table($sql,$params);
    return $res;
  }
   

}