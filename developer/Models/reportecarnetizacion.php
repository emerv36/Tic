<?php
require_once('../Config/PDOconn.php');
class ReporteCarnetizacion extends db
{
	function reporteEtapaPorLote($lote){
	    $query = "SELECT s.nombre_sede AS sede_nombre, COUNT(*) AS cantidad, id_lote_inscripcionfk, CONCAT(YEAR(fecha_inscripcion),'_',MONTH(fecha_inscripcion)) AS periodo, etapa, codigo, estado_inscripcion AS valor_registro,
        CASE  
            WHEN estado_inscripcion='1' THEN 'EN PROCESO' 
            WHEN estado_inscripcion='2' THEN 'RECIBIDO'
            WHEN estado_inscripcion='3' THEN 'ENTREGADO'                                  
        END AS estado_inscripcion 
        FROM inscripcion i
        INNER JOIN lotecarnet l ON i.id_lote_inscripcionfk=l.id_lote 
        INNER JOIN sede s ON i.id_sede_inscripcionfk=s.id_sede
        WHERE id_lote_inscripcionfk=:lote
        GROUP BY  MONTH(fecha_inscripcion), estado_inscripcion";
        $valores = array(
            ":lote" => $lote    
        );
        $resultado = $this->table($query, $valores);
        return $resultado;
	}
	
	function reporteEtapa(){
	    $query = "SELECT s.nombre_sede AS sede_nombre, COUNT(*) AS cantidad, id_lote_inscripcionfk, CONCAT(YEAR(fecha_inscripcion),'_',MONTH(fecha_inscripcion)) AS periodo, etapa, codigo, estado_inscripcion AS valor_registro,
        CASE  
            WHEN estado_inscripcion='1' THEN 'EN PROCESO' 
            WHEN estado_inscripcion='2' THEN 'RECIBIDO'
            WHEN estado_inscripcion='3' THEN 'ENTREGADO'                                  
        END AS estado_inscripcion
        FROM inscripcion i
        INNER JOIN lotecarnet l ON i.id_lote_inscripcionfk=l.id_lote 
        INNER JOIN sede s ON i.id_sede_inscripcionfk=s.id_sede
        GROUP BY MONTH(fecha_inscripcion), estado_inscripcion";
        $resultado = $this->table($query);
        return $resultado;
	}
	
	function reporteRangoFechaEstado($fecha1, $fecha2, $estado){
	    $query = "SELECT nombre_programa AS programa, s.nombre_sede AS sede_nombre, identificacion AS identidad, UPPER(CONCAT(nombre_estudiante,' ',apellido_estudiante)) AS estudiante,email_estudiante AS correo, celular_estudiante AS telefono,fecha_inscripcion AS fecha, id_lote_inscripcionfk, CONCAT(YEAR(fecha_inscripcion),'_',MONTH(fecha_inscripcion)) AS periodo, etapa, codigo, estado_inscripcion AS valor_registro,
        CASE  
            WHEN estado_inscripcion='1' THEN 'EN PROCESO' 
            WHEN estado_inscripcion='2' THEN 'RECIBIDO'
            WHEN estado_inscripcion='3' THEN 'ENTREGADO'                                  
        END AS estado_inscripcion
        FROM inscripcion i
        INNER JOIN lotecarnet l ON i.id_lote_inscripcionfk=l.id_lote
        INNER JOIN sede s ON i.id_sede_inscripcionfk=s.id_sede
        INNER JOIN programa pro ON i.id_programa_inscripcionfk=pro.id_programa 
        WHERE (estado_inscripcion=:estado) AND (fecha_inscripcion BETWEEN :uno AND :dos)";
        $valores = array(
            ":uno" => $fecha1,
            ":dos" => $fecha2,
            ":estado" => $estado
        );
        $resultado = $this->table($query, $valores);
        return $resultado;
	}
	
	function reporteRangoFecha($fecha1, $fecha2){
	    $query = "SELECT nombre_programa AS programa, s.nombre_sede AS sede_nombre, identificacion AS identidad, UPPER(CONCAT(nombre_estudiante,' ',apellido_estudiante)) AS estudiante,email_estudiante AS correo, celular_estudiante AS telefono,fecha_inscripcion AS fecha, id_lote_inscripcionfk, CONCAT(YEAR(fecha_inscripcion),'_',MONTH(fecha_inscripcion)) AS periodo, etapa, codigo, estado_inscripcion AS valor_registro,
        CASE  
            WHEN estado_inscripcion='1' THEN 'EN PROCESO' 
            WHEN estado_inscripcion='2' THEN 'RECIBIDO'
            WHEN estado_inscripcion='3' THEN 'ENTREGADO'                                  
        END AS estado_inscripcion
        FROM inscripcion i
        INNER JOIN lotecarnet l ON i.id_lote_inscripcionfk=l.id_lote
        INNER JOIN sede s ON i.id_sede_inscripcionfk=s.id_sede
        INNER JOIN programa pro ON i.id_programa_inscripcionfk=pro.id_programa
        WHERE fecha_inscripcion BETWEEN :uno AND :dos";
        $valores = array(
            ":uno" => $fecha1,
            ":dos" => $fecha2 
        );
        $resultado = $this->table($query, $valores);
        return $resultado;
	}
	
	function reporteEstadoTodos(){
	    $query = "SELECT s.nombre_sede AS sede_nombre, COUNT(*) AS cantidad, id_lote_inscripcionfk, CONCAT(YEAR(fecha_inscripcion),'_',MONTH(fecha_inscripcion)) AS periodo, etapa, codigo, estado_inscripcion AS valor_registro,
        CASE  
            WHEN estado_inscripcion='1' THEN 'EN PROCESO' 
            WHEN estado_inscripcion='2' THEN 'RECIBIDO'
            WHEN estado_inscripcion='3' THEN 'ENTREGADO'                                  
        END AS estado_inscripcion 
        FROM inscripcion i
        INNER JOIN lotecarnet l ON i.id_lote_inscripcionfk=l.id_lote 
        INNER JOIN sede s ON i.id_sede_inscripcionfk=s.id_sede
        GROUP BY estado_inscripcion";
        $resultado = $this->table($query);
        return $resultado;
	}
	
	function reporteEstado($estado){
	    $query = "SELECT s.nombre_sede AS sede_nombre, COUNT(*) AS cantidad, id_lote_inscripcionfk, CONCAT(YEAR(fecha_inscripcion),'_',MONTH(fecha_inscripcion)) AS periodo, etapa, codigo, estado_inscripcion AS valor_registro,
        CASE  
            WHEN estado_inscripcion='1' THEN 'EN PROCESO' 
            WHEN estado_inscripcion='2' THEN 'RECIBIDO'
            WHEN estado_inscripcion='3' THEN 'ENTREGADO'                                  
        END AS estado_inscripcion 
        FROM inscripcion i
        INNER JOIN lotecarnet l ON i.id_lote_inscripcionfk=l.id_lote 
        INNER JOIN sede s ON i.id_sede_inscripcionfk=s.id_sede
        WHERE estado_inscripcion=:estado
        GROUP BY estado_inscripcion";
        $valores = array(
            ":estado" => $estado    
        );
        $resultado = $this->table($query, $valores);
        return $resultado;
	}
	
	function reporteTipoTodos(){
	    $query = "SELECT s.nombre_sede AS sede_nombre, identificacion AS identidad, UPPER(CONCAT(nombre_estudiante,' ',apellido_estudiante)) AS estudiante, email_estudiante AS correo, celular_estudiante AS telefono,fecha_inscripcion AS fecha, id_lote_inscripcionfk, CONCAT(YEAR(fecha_inscripcion),'_',MONTH(fecha_inscripcion)) AS periodo, etapa, codigo, estado_inscripcion AS valor_registro,
        CASE  
            WHEN estado_inscripcion='1' THEN 'EN PROCESO' 
            WHEN estado_inscripcion='2' THEN 'RECIBIDO'
            WHEN estado_inscripcion='3' THEN 'ENTREGADO'                                  
        END AS estado_inscripcion,
        CASE  
            WHEN categoria_carnet='1' THEN 'ESTUDIANTE' 
            WHEN categoria_carnet='2' THEN 'FUNCIONARIO'
            WHEN categoria_carnet='3' THEN 'PRACTICANTE'                                  
        END AS categoria_carnet
        FROM inscripcion i
        INNER JOIN lotecarnet l ON i.id_lote_inscripcionfk=l.id_lote
        INNER JOIN sede s ON i.id_sede_inscripcionfk=s.id_sede";
        $resultado = $this->table($query, $valores);
        return $resultado;
	}
	
	function reporteTipo($tipo){
	    $query = "SELECT s.nombre_sede AS sede_nombre, identificacion AS identidad, UPPER(CONCAT(nombre_estudiante,' ',apellido_estudiante)) AS estudiante,email_estudiante AS correo, celular_estudiante AS telefono,fecha_inscripcion AS fecha, id_lote_inscripcionfk, CONCAT(YEAR(fecha_inscripcion),'_',MONTH(fecha_inscripcion)) AS periodo, etapa, codigo, estado_inscripcion AS valor_registro,
        CASE  
            WHEN estado_inscripcion='1' THEN 'EN PROCESO' 
            WHEN estado_inscripcion='2' THEN 'RECIBIDO'
            WHEN estado_inscripcion='3' THEN 'ENTREGADO'                                  
        END AS estado_inscripcion,
        CASE  
            WHEN categoria_carnet='1' THEN 'ESTUDIANTE' 
            WHEN categoria_carnet='2' THEN 'FUNCIONARIO'
            WHEN categoria_carnet='3' THEN 'PRACTICANTE'                                  
        END AS categoria_carnet
        FROM inscripcion i
        INNER JOIN lotecarnet l ON i.id_lote_inscripcionfk=l.id_lote
        INNER JOIN sede s ON i.id_sede_inscripcionfk=s.id_sede
        WHERE categoria_carnet=:tipo";
        $valores = array(
            ":tipo" => $tipo
        );
        $resultado = $this->table($query, $valores);
        return $resultado;
	}
}
?>