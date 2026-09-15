<?php 
require_once '../developer/PDOConn.php';

class ModuloProgramacion extends db   
{

  public function loadPmodulos($whe)
  {
      $sql = "SELECT pmp.codigo, pp.nombre, pmp.fecha, pmp.horainicio, pmp.horafin, pmp.cuposminimo, pmp.cuposmaximo, pmp.salon, pd.nombre_largo, pmp.estado FROM pro_modulo_programacion pmp
              INNER JOIN pro_modulo_docente md ON md.codprograma_modulo = pmp.codprograma_modulo
              INNER JOIN pro_docente pd ON pd.codigo = md.coddocente
              INNER JOIN pro_programa_modulo ppm ON ppm.codigo = pmp.codprograma_modulo 
              INNER JOIN pro_programa pp ON pp.codigo = ppm.codprograma".$whe;
      $res = $this->table($sql);
      return $res;
  }

  public function insertPmodulos($codprograma_modulo, $fecha, $horainicio, $horafin, $cuposminimo, $cuposmaximo, $salon, $codmodulodocente, $estado)
  {
     $insert = "INSERT INTO pro_modulo_programacion (codprograma_modulo, fecha, horainicio, horafin, cuposminimo, cuposmaximo, salon, codmodulodocente, estado) VALUES (:codprograma_modulo, :fecha, :horainicio, :horafin, :cuposminimo, :cuposmaximo, :salon, :codmodulodocente, :estado)";
      $params = array(':codprograma_modulo' => ($codprograma_modulo), ':fecha' => $fecha, ':horainicio' => $horainicio, ':horafin' => $horafin, ':cuposminimo' => $cuposminimo, ':cuposmaximo' => $cuposmaximo, ':salon' => $salon, ':codmodulodocente' => $codmodulodocente, ':estado' => $estado);
      $res = $this->query($insert,$params);
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

  public function CodAnioLectivo()
    {
      $sql =  "SELECT codigo as codigo, nombre FROM pro_aniolectivo WHERE estado = 'on'";
      $res = $this->row($sql);
      return $res;
    }

  public function LoadModulos($codigo, $codigo1)
    {
      $sql = "SELECT DISTINCT (codmodulo) FROM pro_programa_modulo WHERE estado = 'on' AND codprograma = :codprograma AND codanio = :codanio";
      $params = array(':codanio' => $codigo, ':codprograma' => $codigo1);
      $res = $this->table($sql, $params);
      return $res;
    }

  public function LoadDocente($codigo)
    {
      $sql = "SELECT md.codigo as cod, pd.nombre_largo as nombre FROM pro_modulo_docente md
                    INNER JOIN pro_docente pd ON pd.codigo = md.coddocente 
                          WHERE md.codprograma_modulo = :codprograma_modulo AND pd.estado = 'on'";
      $params = array(':codprograma_modulo' => $codigo);
      $res = $this->row($sql, $params);
      return $res;
    }

  public function LoadModulos2($codigo)
    {
      $sql = "SELECT codigo as cod, nombre FROM pro_modulo WHERE estado = 'on' AND codigo ".$codigo;
      $res = $this->table($sql);
      return $res;
    }

  public function LoadSedes($codigo, $codigo1, $codigo2)
    {
      $sql = "SELECT DISTINCT (codsede) FROM pro_programa_modulo WHERE estado = 'on' AND codprograma = :codprograma AND codmodulo = :codmodulo AND codanio = :codanio";
      $params = array(':codanio' => $codigo, ':codprograma' => $codigo1, ':codmodulo' => $codigo2);
      $res = $this->table($sql, $params);
      return $res;
    }

  public function LoadDisponibilidad($codigo, $codigo1, $codigo2, $codigo3)
    {
      $sql = "SELECT codigo, fechainicio, fechafin FROM pro_programa_modulo WHERE estado = 'on' AND codprograma = :codprograma AND codmodulo = :codmodulo AND codanio = :codanio AND codsede = :codsede";
      $params = array(':codanio' => $codigo, ':codprograma' => $codigo1, ':codmodulo' => $codigo2, ':codsede' => $codigo3);
      $res = $this->table($sql, $params);
      return $res;
    }

  public function LoadSedes2($codigo)
    {
      $sql = "SELECT codigo_sede as cod, nombre_sede as nombre FROM pro_sedes WHERE estado_sede = 'on' AND codigo_sede ".$codigo;
      $res = $this->table($sql);
      return $res;
    }

  public function loadModulosDocente($codp)
    {
      $sql = "SELECT pro.codigo as cod, pd.nombre_largo as nombre FROM  pro_modulo_docente pro 
                  INNER JOIN pro_docente pd ON pd.codigo = pro.coddocente
                    WHERE pro.estado = 'on' AND pro.codprograma_modulo = :codp" ;
      $res = $this->table($sql, array(':codp' => $codp));
      return $res;
    }

  public function EditLoadModulosPrograma()
    {
      $sql = "SELECT pro.codigo as cod, pro.nombre as nombre FROM pro_programa pro
              INNER JOIN pro_programa_modulo prom ON pro.codigo = prom.codprograma WHERE pro.estado='on'";
      $res = $this->table($sql);
      return $res;
    }

  public function EditloadModulosDocente($codp)
    {
      $sql = "SELECT pro.codigo as cod, pro.nombre_largo as nombre FROM pro_docente pro
              INNER JOIN pro_modulo_docente prom ON pro.codigo = prom.coddocente 
              WHERE pro.estado = 'on' AND prom.codprograma_modulo = :codp";
      $res = $this->table($sql, array(':codp' => $codp));
      return $res;
    }


  public function editModalModuloP($codigo, $fecha, $horainicio, $horafin, $cuposminimo, $cuposmaximo, $salon)
  {
      $update = "UPDATE pro_modulo_programacion SET fecha = :fecha, horainicio = :horainicio, horafin = :horafin, cuposminimo = :cuposminimo, cuposmaximo = :cuposmaximo, salon = :salon WHERE codigo = :codigo";   

      $params = array(
                      ':codigo' => $codigo,
                      ':fecha'=> $fecha,
                      ':horainicio' => $horainicio,
                      ':horafin' => $horafin,
                      ':cuposminimo' => $cuposminimo,
                      ':cuposmaximo' => $cuposmaximo,
                      ':salon' => $salon
                    );
      $res = $this->query($update, $params);
      
    return $res;

  }

  public function editEstadoPmodulo($cod, $estado)
    {
      $update = "UPDATE pro_modulo_programacion SET estado = :estado WHERE codigo = :cod";
      $params = array(':cod' => $cod, ':estado' => $estado);
      $res = $this->query($update, $params);
      return $res;
    }
 

}