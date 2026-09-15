<?php
require_once '../developer/PDOConn.php';

class Calendario extends db {
    public function listarDia($fecha,$hora){
      $sql="SELECT pmp.codigo, pmp.fecha, pmp.estado, pmp.salon
              FROM pro_modulo_programacion pmp
                INNER JOIN pro_programa_modulo ppm ON ppm.codigo = codprograma_modulo 
                INNER JOIN pro_modulo_docente pmd ON pmd.codprograma_modulo = ppm.codigo
                    WHERE pmp.horainicio  like ('".$hora."%') AND pmp.fecha = :fecha";

      $params =array(":fecha"=>$fecha);
      $res = $this->table($sql, $params);
        return $res;
    }

    public function listarClases($fecha,$hora){
      $sql="SELECT pmp.motivo_cancelacion, pmp.codigo, pp.nombre as programa, pm.nombre as modulo, pd.nombre_largo, pmp.fecha, pmp.horainicio as inicio, pmp.horafin as fin, 
        pmp.cuposmaximo, pmp.salon, ps.nombre_sede, pmp.codigo as codprogramamodulo, pmp.estado, (select count(codigo) as asistencia FROM pro_modulo_asistencia pma 
                      WHERE codmodulo_programacion = pmp.codigo AND pma.estado = 'on')  
              FROM pro_modulo_programacion pmp
                INNER JOIN pro_programa_modulo ppm ON ppm.codigo = codprograma_modulo 
                INNER JOIN pro_programa pp ON pp.codigo = ppm.codprograma
                INNER JOIN pro_modulo pm ON pm.codigo = ppm.codmodulo
                INNER JOIN pro_modulo_docente pmd ON pmd.codprograma_modulo = ppm.codigo
                INNER JOIN pro_docente pd ON pd.codigo = pmd.coddocente 
                INNER JOIN pro_sedes ps ON ps.codigo_sede = ppm.codsede
                    WHERE pmp.horainicio  like ('".$hora."%') AND pmp.fecha = :fecha";
      $params =array(":fecha"=>$fecha);
      $res = $this->table($sql, $params);
        return $res;
    }

    public function listarAsistencia($codigo){
      $sql="SELECT ee.identificacion, ee.nombre_largo, pma.estado AS estado FROM pro_modulo_asistencia pma 
              INNER JOIN aca_estumatricula ae ON ae.codigo = pma.codestumatricula
                  INNER JOIN est_estudiante ee ON ee.codigo = ae.codestudiante
                    WHERE pma.codmodulo_programacion = ".$codigo."";
      $res = $this->table($sql);
        return $res;
    }

    public function listarHorario($fecha,$hora, $codigo){
      $sql="SELECT pmp.motivo_cancelacion, pmp.estado as estado, ae.codigo as codmatricula, pmp.codigo as codprogramacion, pmp.fecha, pp.nombre as programa, ps.nombre_sede, pm.nombre as modulo, pmp.salon, (pmp.horainicio ||' - '|| pmp.horafin ) as horario, pmp.cuposminimo, pmp.cuposmaximo, pd.nombre_largo as  docente, (select count(pma.codigo) as asistencia  
                      FROM pro_modulo_asistencia pma 
                        WHERE pma.codmodulo_programacion = pmp.codigo
                         AND pma.estado = 'on')  
            FROM aca_estumatricula ae
            INNER JOIN pro_programa_modulo ppm ON ppm.codigo = ae.codprograma_modulo
            INNER JOIN pro_modulo_programacion pmp ON pmp.codprograma_modulo = ppm.codigo
            INNER JOIN pro_programa pp ON pp.codigo = ppm.codprograma
            INNER JOIN pro_sedes ps ON ps.codigo_sede = ppm.codsede
            INNER JOIN pro_modulo pm ON pm.codigo = ppm.codmodulo
            INNER JOIN est_estudiante ee ON ee.codigo = ae.codestudiante
            INNER JOIN pro_modulo_docente pmd ON pmd.codigo = codmodulodocente
            INNER JOIN pro_docente pd ON pd.codigo = pmd.coddocente
            INNER JOIN usu_usuarios uu ON uu.identificacion = ee.identificacion
            WHERE pmp.horainicio like ('".$hora."%') AND pmp.fecha = :fecha AND uu.codigo_usu = :codigo_usu";

      $params =array(":fecha"=>$fecha, ":codigo_usu"=>$codigo);
      $res = $this->row($sql, $params);
        return $res;
    }

    public function estadoAsistencia($codmatricula, $codprogramacion){
      $sql = "SELECT codigo, estado FROM pro_modulo_asistencia WHERE codestumatricula = :codestumatricula AND codmodulo_programacion = :codmodulo_programacion";
      $params = array('codestumatricula' => $codmatricula, ':codmodulo_programacion' => $codprogramacion);
      $res = $this->row($sql, $params);
        return $res;
    }

    public function confirmarAsistencia($codmatricula, $codprogramacion){
      $sql = "INSERT INTO pro_modulo_asistencia (codestumatricula, codmodulo_programacion, fecharegistro, estado) VALUES (:codestumatricula, :codprogramacion, :fecharegistro, :estado)";
      $params = array(':codestumatricula' => $codmatricula, ':codprogramacion' =>$codprogramacion, ':fecharegistro' =>$this->datetimeNow(), ':estado'=>'on');
      $res = $this->query($sql, $params);
        return $res;
    }

    public function cancelarAsistencia($codigo){
      $sql = "UPDATE pro_modulo_asistencia SET estado = :estado, fecharegistro = :fecharegistro WHERE codigo = :codigo";
      $params = array('codigo' => $codigo, ':fecharegistro' =>$this->datetimeNow(), ':estado' => 'off');
      $res = $this->query($sql, $params);
        return $res;
    }

    public function confirmarasistencia2($codigo){
      $sql = "UPDATE pro_modulo_asistencia SET estado = :estado, fecharegistro = :fecharegistro WHERE codigo = :codigo";
      $params = array('codigo' => $codigo, ':fecharegistro' =>$this->datetimeNow(), ':estado' => 'on');
      $res = $this->query($sql, $params);
        return $res;
    }

    public function listarHorarioDocente($fecha, $hora, $codigo){
      $sql="SELECT pmp.motivo_cancelacion, pmp.horainicio, pmp.estado AS estado, pmp.codigo AS codprogramacion, uu.codigo_usu, pp.nombre AS programa, pm.nombre AS modulo, ps.nombre_sede, pd.nombre_largo, (pmp.horainicio ||' - '|| pmp.horafin ) as horario, pmp.fecha, pmp.salon, pmp.cuposminimo, pmp.cuposmaximo, (select count(pma.codigo) as asistencia  
                      FROM pro_modulo_asistencia pma 
                        WHERE pma.codmodulo_programacion = pmp.codigo
                        AND pma.estado = 'on') 
              FROM pro_modulo_docente pmd  
              INNER JOIN pro_programa_modulo ppm ON ppm.codigo = codprograma_modulo
              INNER JOIN pro_modulo_programacion pmp ON pmp.codprograma_modulo = ppm.codigo
              INNER JOIN pro_modulo pm ON pm.codigo = ppm.codmodulo
              INNER JOIN pro_sedes ps ON ps.codigo_sede = ppm.codsede
              INNER JOIN pro_programa pp ON pp.codigo = ppm.codprograma
              INNER JOIN pro_docente pd ON pd.codigo = pmd.coddocente
              INNER JOIN usu_usuarios uu ON uu.identificacion = pd.identificacion
            WHERE pmp.horainicio LIKE ('".$hora."%') AND pmp.fecha = :fecha AND uu.codigo_usu = :codigo_usu";

      $params =array(':fecha'=>$fecha, ':codigo_usu'=>$codigo);
      $res = $this->row($sql, $params);
        return $res;
    }
   

    public function insertCancelacion($codigo, $cmotivacion){
      $sql = "UPDATE pro_modulo_programacion SET motivo_cancelacion = :motivo_cancelacion,  estado = 'off' WHERE codigo = :codigo";
      $params = array(':motivo_cancelacion' => $cmotivacion, ':codigo' => $codigo);
      $res = $this->query($sql, $params);
        return $res;
    }

 }
?>
