<?php
require_once('../Config/PDOconn.php');

class Dashadmision extends db {

public function ContarInscritosHoy($fecha){        
        $sql ="SELECT COUNT(*) AS total  FROM inscripcion 
         WHERE  DATE(fecha_inscripcion)=:fecha";
        $parametros = array(':fecha' => $fecha);
        $resultado = $this->row($sql, $parametros);    
        return $resultado;
    }

    public function TotalProceso(){        
        $sql ="SELECT COUNT(*) AS TotalProceso
               FROM inscripcion 
               WHERE estado_inscripcion=:estado";
               $parametros = array(':estado' =>'1');
               $resultado = $this->row($sql, $parametros); 
              return $resultado;
    }

    public function TotalInscrito($fecha){        
        $sql="SELECT COUNT(*) AS total  FROM inscripcion 
         WHERE  DATE(fecha_inscripcion)<=:fecha AND  estado_registro='1'";
        $parametros = array(':fecha' => $fecha);
        $resultado = $this->row($sql, $parametros);    
        return $resultado;
    }

    public function TotalRealizado(){        
        $sql="SELECT COUNT(*) AS totalRealizado
          FROM inscripcion 
         WHERE  estado_inscripcion=:estado";
        $parametros = array(':estado' => '2');
        $resultado = $this->row($sql,$parametros);    
        return $resultado;
    }

    public function TotalEntregado(){        
        $sql="SELECT COUNT(*) AS TotalEntregado
          FROM inscripcion 
          WHERE  estado_inscripcion=:estado";
          $parametros = array(':estado'=>'3');
          $resultado = $this->row($sql,$parametros);   
        return $resultado;
    }


    public function TotalConChip(){        
        $sql="SELECT COUNT(*) AS TotalConChip
          FROM inscripcion 
          WHERE  chip_carnet=:chip";
          $parametros = array(':chip'=>'SI');
          $resultado = $this->row($sql,$parametros);   
        return $resultado;
    }

    public function TotalSinChip(){        
        $sql="SELECT COUNT(*) AS TotalSinChip
          FROM inscripcion 
          WHERE  chip_carnet=:chip";
          $parametros = array(':chip'=>'NO');
          $resultado = $this->row($sql,$parametros);   
        return $resultado;
    }

    public function TotalCarnetFuncionario(){        
        $sql="SELECT COUNT(*) AS TotalCarnetFuncionario
          FROM inscripcion 
          WHERE  categoria_carnet=:categoria";
          $parametros = array(':categoria'=>'2');
          $resultado = $this->row($sql,$parametros);   
        return $resultado;
    }


    
    public function TotalRegistros(){        
        $sql="SELECT COUNT(*) AS total
          FROM inscripcion";
        $resultado = $this->row($sql);    
        return $resultado;
    }

    public function CarnetAgrupadosEstado($estado){
        $sql="SELECT id_sede_inscripcionfk,
        nombre_sede,
        id_programa_inscripcionfk,
        nombre_programa,
        estado_inscripcion,
        chip_carnet,
        estado_inscripcion AS valor_registro,
                  CASE  
                    WHEN estado_inscripcion='1' THEN 'PROCESO' 
                    WHEN estado_inscripcion='2' THEN 'REALIZADO'
                    WHEN estado_inscripcion='3' THEN 'ENTREGADO'   
                    WHEN estado_inscripcion='4' THEN 'CORRECCION'                               
                  END AS estado_inscripcion,
        COUNT(estado_inscripcion) AS TotalCarnet           
        FROM inscripcion i
        INNER JOIN sede      s   ON i.id_sede_inscripcionfk=s.id_sede
        INNER JOIN programa  pr  ON i.id_programa_inscripcionfk=pr.id_programa
        WHERE i.estado_inscripcion=:estado        
        GROUP BY i.id_sede_inscripcionfk,
                 i.id_programa_inscripcionfk                  
        ORDER BY s.nombre_sede, pr.nombre_programa ASC";
        $parametros = array(':estado'=>$estado);
        $resultado = $this->table($sql, $parametros);    
        return $resultado;
    }

    public function PoblacionSede(){
        $sql="SELECT id_sede_inscripcionfk,
        nombre_sede,
        COUNT(id_sede_inscripcionfk) AS totalprograma           
        FROM inscripcion i
        INNER JOIN sede      s   ON i.id_sede_inscripcionfk=s.id_sede
        INNER JOIN programa  pr  ON i.id_programa_inscripcionfk=pr.id_programa
        GROUP BY i.id_sede_inscripcionfk                          
        ORDER BY s.nombre_sede ASC";
         $resultado = $this->table($sql);    
         return $resultado;

    }

//funcion cargar los periodos con matriculas 
   public function CargarPeriodoMatricula(){
       $sql="SELECT id_periodo_inscripcionfk as cod, periodo as nombre, estado_registro
       FROM inscripcion i  
       INNER JOIN periodo p ON i.id_periodo_inscripcionfk=p.id_periodo
       WHERE  estado_registro='2'
       GROUP BY  id_periodo_inscripcionfk";
      $resultado = $this->table($sql);    
     return $resultado;

   } 
 //funcion cargar agrupados de matriculas por periodos
 public function CarnetizacionAgrupada(){
    $sql="SELECT id_sede_inscripcionfk,
                nombre_sede,
                id_programa_inscripcionfk,
                nombre_programa,
                estado_inscripcion,
                chip_carnet,
                estado_inscripcion AS valor_registro,
                        CASE  
                            WHEN estado_inscripcion='1' THEN 'PROCESO' 
                            WHEN estado_inscripcion='2' THEN 'REALIZADO'
                            WHEN estado_inscripcion='3' THEN 'ENTREGADO'   
                            WHEN estado_inscripcion='4' THEN 'CORRECCION'                               
                        END AS estado_inscripcion,
                    COUNT(estado_inscripcion) AS TotalCarnet           
                FROM inscripcion i
                INNER JOIN sede      s   ON i.id_sede_inscripcionfk=s.id_sede
                INNER JOIN programa  pr  ON i.id_programa_inscripcionfk=pr.id_programa
                GROUP BY i.id_sede_inscripcionfk,
                        i.id_programa_inscripcionfk                  
                ORDER BY s.nombre_sede, pr.nombre_programa ASC";
    $resultado = $this->table($sql);    
    return $resultado;
   }
}

?>
