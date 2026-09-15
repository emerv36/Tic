<?php
 require_once('../Config/PDOconn.php');
  class  OtrasOpciones extends db {
//funcion para la consulta de estudiantes con modulos programados 
public function ConsultaRecordModuloEstudiante($identidad){
        $select="SELECT id_inscripcion,
                        identificacion,
                        nombre_estudiante,
                        apellido_estudiante,
                        id_detalle_modulofk,
                        nombre_modulo,
                        codigo_curso,
                        periodo,
                        fechainicio,
                        fechafin,
                        nombre_docente,
                        apellido_docente,
                        id_horario_cursofk,
                        nombre_horario,
                        dia_horario,
                        id_sede_cursofk,
                        nombre_sede,
                        id_detalle,
                        id_detalle_cargafk,
                        estado_carga,
                        id_detalle_periodofk,
                        fecha_carga,
                        id_programa_inscripcionfk,
                        nombre_programa
                        FROM inscripcion i 
                        INNER JOIN detalle_carga 	  dc ON i.id_inscripcion = dc.id_detalle_carga_estfk
                        INNER JOIN modulo        	  m  ON dc.id_detalle_modulofk = m.id_modulo
                        INNER JOIN periodo       	  p  ON dc.id_detalle_periodofk=p.id_periodo
                        INNER JOIN curso         	  c  ON dc.id_detalle_cursofk=c.id_curso
                        INNER JOIN docente        	d  ON dc.id_detalle_docentefk=d.id_docente
                        INNER JOIN horario        	h  ON c.id_horario_cursofk=h.id_horario
                        INNER JOIN sede           	s  ON c.id_sede_cursofk=s.id_sede
                        INNER JOIN carga_academica 	ca ON dc.id_detalle_cargafk=ca.id_carga
                        INNER JOIN programa      	  pp  ON i.id_programa_inscripcionfk=pp.id_programa
                        WHERE ca.estado_carga='on' AND identificacion=:identidad";
 $params = array(':identidad'=>$identidad);
 $resultado = $this->table($select,$params);
return $resultado;
}

//consultas de estudiantes modulo adicional
public function ConsultaEstudiantesModuloAdicional($identidad){
                                $select="SELECT id_inscripcion,
                                identificacion,
                                nombre_estudiante,
                                apellido_estudiante,
                                id_detalle_modulofk,
                                nombre_modulo,
                                codigo_curso,
                                periodo,
                                fechainicio,
                                fechafin,
                                nombre_docente,
                                apellido_docente,
                                id_horario_cursofk,
                                nombre_horario,
                                dia_horario,
                                nombre_sede,
                                id_detalle,
                                id_detalle_cargafk,
                                estado_carga,
                                id_detalle_periodofk,
                                fecha_carga,
                                id_programa_inscripcionfk,
                                id_sede_inscripcionfk,
                                nombre_programa,
                                modulo_cruzado
                                FROM inscripcion i 
                                INNER JOIN detalle_carga 	  	dc ON i.id_inscripcion = dc.id_detalle_carga_estfk
                                INNER JOIN modulo        	  	m  ON dc.id_detalle_modulofk = m.id_modulo
                                INNER JOIN periodo       	  	p  ON dc.id_detalle_periodofk=p.id_periodo
                                INNER JOIN curso         		c  ON dc.id_detalle_cursofk=c.id_curso
                                INNER JOIN docente        		d  ON dc.id_detalle_docentefk=d.id_docente
                                INNER JOIN horario        		h  ON c.id_horario_cursofk=h.id_horario
                                INNER JOIN sede           		s  ON i.id_sede_inscripcionfk=s.id_sede
                                INNER JOIN carga_academica 		ca ON dc.id_detalle_cargafk=ca.id_carga
                                INNER JOIN programa      	  	pp ON i.id_programa_inscripcionfk=pp.id_programa
                                INNER JOIN matricula                    mm ON i.id_inscripcion=mm.id_matricula_inscripcionfk
                                INNER JOIN matricula_curso              mc ON mm.id_matricula_inscripcionfk=mc.id_estudiante_matricula_detallefk
                                WHERE ca.estado_carga='on'    AND identificacion=:identidad  AND modulo_cruzado=1
                                GROUP BY identificacion";
$params = array(':identidad'=>$identidad);
$resultado = $this->table($select,$params);
return $resultado;
}

//funcion para la elimnacion del modulo programado
public function EliminarModulo($id_detalle){
    $select="DELETE FROM detalle_carga WHERE id_detalle=:id";
    $params = array(':id'=>$id_detalle);
    $resultado=$this->query($select, $params);
    return $resultado;
  }



// funcion para la busqueda de estudiantes para matricula modulo adicional
public function ConsultaEstudianteInscritos($identidad){
  $select="SELECT id_inscripcion,
                  identificacion,
                  nombre_estudiante,
                  apellido_estudiante,
                  email_estudiante,
                  celular_estudiante,
                  id_periodo_inscripcionfk,
                  periodo,
                  id_sede_inscripcionfk,
                  nombre_sede,
                  id_programa_inscripcionfk,
                  nombre_programa,
                  id_horario_inscripcionfk,
                  nombre_horario,
                  dia_horario,
                  foto_estudiante,
                  fecha_inscripcion,
                  estado_registro AS valor_registro,
                  CASE  
                      WHEN estado_registro='1' THEN 'INSCRITO' 
                      WHEN estado_registro='2' THEN 'MATRICULADO'                                    
                  END AS estado_registro      
                  FROM inscripcion i 
                  INNER JOIN periodo  p  ON i.id_periodo_inscripcionfk=p.id_periodo
                  INNER JOIN programa pp ON i.id_programa_inscripcionfk=pp.id_programa
                  INNER JOIN horario  h  ON i.id_horario_inscripcionfk=h.id_horario
                  INNER JOIN sede     s  ON i.id_sede_inscripcionfk=s.id_sede
                  WHERE  estado_inscripcion='on' AND identificacion=:identidad  AND estado_registro='1'";
$params = array(':identidad'=>$identidad);
$resultado = $this->table($select,$params);
return $resultado;
}


//funcion para la lista de estudiantes cruzados
public function ListarEstudiantesCruzados(){
  $select="SELECT id_inscripcion,
                  identificacion,
                  nombre_estudiante,
                  apellido_estudiante,
                  id_convenio_inscripcionfk,
                  descripcion_convenio,
                  id_sede_inscripcionfk,
                  nombre_sede,
                  id_horario_inscripcionfk,
                  nombre_horario,
                  dia_horario,
                  id_programa_inscripcionfk,
                  nombre_programa,
                  id_periodo_inscripcionfk,
                  periodo,
                  id_estudiante_matricula_detallefk,
                  id_cursofk,
                  codigo_curso,
                  modulo_cruzado,
                  periodo_cruce
                  FROM inscripcion i
                  INNER JOIN convenio 		c	ON  i.id_convenio_inscripcionfk=c.id_convenio
                  INNER JOIN sede    		s	ON  i.id_sede_inscripcionfk=s.id_sede
                  INNER JOIN horario  		h	ON  i.id_horario_inscripcionfk=h.id_horario
                  INNER JOIN programa 		p	ON  i.id_programa_inscripcionfk=p.id_programa
                  INNER JOIN periodo  		pp	ON  i.id_periodo_inscripcionfk=pp.id_periodo
                  INNER JOIN matricula_curso 	mc	ON  i.id_inscripcion=mc.id_estudiante_matricula_detallefk
                  INNER JOIN curso         	cc   	ON  mc.id_cursofk=cc.id_curso
                  WHERE mc.modulo_cruzado='2'
                  GROUP BY i.id_inscripcion
                  ORDER BY i.apellido_estudiante";
                 $resultado = $this->table($select);
                 return $resultado;

}

//funcion para cargar los planes de estudios de los cursos seleccionados 
public function CargarPensumMoverCruzados($Rsede,$Rprograma){
  $select ="SELECT id_pensum,
                    id_pensum_modulofk,
                    id_modulo as cod,
                    nombre_modulo,
                    id_pensum_sedefk,
                    nombre_sede,
                    id_pensum_programafk,
                    nombre_programa,
                    id_pensum_versionfk,
                    nombre_version,
                    int_horas_pensum
                    FROM pensum p 
                    INNER JOIN modulo   m ON p.id_pensum_modulofk=m.id_modulo
                    INNER JOIN sede     s ON p.id_pensum_sedefk=s.id_sede
                    INNER JOIN programa pr ON p.id_pensum_programafk=pr.id_programa
                    INNER JOIN version_pensum vp ON p.id_pensum_versionfk=vp.id_version    
                    WHERE  id_pensum_programafk=:programa AND  id_pensum_sedefk=:sede 
                    ORDER BY id_modulo";
                    $parametros = array(':programa' => $Rprograma, 
                                        ':sede' =>  $Rsede);
                    $resultado = $this->table($select, $parametros);
      return $resultado;
}

//funcion para la carga de pensum de estudiantes

public function CargarPensumEstudiantes($Rsede,$Rprograma){
  $select ="SELECT id_pensum,
                    id_pensum_modulofk,
                    id_modulo as cod,
                    nombre_modulo,
                    id_pensum_sedefk,
                    nombre_sede,
                    id_pensum_programafk,
                    nombre_programa,
                    id_pensum_versionfk,
                    nombre_version,
                    int_horas_pensum
                    FROM pensum p 
                    INNER JOIN modulo   m ON p.id_pensum_modulofk=m.id_modulo
                    INNER JOIN sede     s ON p.id_pensum_sedefk=s.id_sede
                    INNER JOIN programa pr ON p.id_pensum_programafk=pr.id_programa
                    INNER JOIN version_pensum vp ON p.id_pensum_versionfk=vp.id_version    
                    WHERE  id_pensum_programafk=:programa AND  id_pensum_sedefk=:sede 
                    ORDER BY id_modulo";
                    $parametros = array(':programa' => $Rprograma, 
                                        ':sede' =>  $Rsede);
                    $resultado = $this->table($select, $parametros);
      return $resultado;
}


//funcion para cargar el record de modulos estudiantes cruados
public function CargarRecordModulosEstudianteCruzados($estudiante,$programa){
  $select="SELECT id_detalle_cargafk,
                  id_detalle_carga_estfk,
                  nombre_estudiante,
                  apellido_estudiante,
                  id_modulo_cargafk,
                  nombre_modulo,
                  id_periodo_cargafk,
                  periodo, 
                  id_curso_cargafk,
                  codigo_curso,
                  id_programa_inscripcionfk
                  FROM detalle_carga dt 
                  INNER JOIN inscripcion i       ON dt.id_detalle_carga_estfk=i.id_inscripcion
                  INNER JOIN carga_academica ca  ON dt.id_detalle_cargafk=ca.id_carga
                  INNER JOIN modulo          m   ON ca.id_modulo_cargafk=m.id_modulo
                  INNER JOIN periodo         p   ON ca.id_periodo_cargafk=p.id_periodo
                  INNER JOIN curso           c   ON ca.id_curso_cargafk=c.id_curso
                  WHERE id_detalle_carga_estfk=:estudiante AND id_programa_inscripcionfk=:programa
                  GROUP BY  id_modulo_cargafk
                  ORDER BY id_periodo_cargafk DESC";
      $parametros = array(':estudiante' => $estudiante,
                          ':programa'=>$programa );
     $resultado = $this->table($select, $parametros);
return $resultado;
}

//funcion para la carga de los modulo visto por el estudiantes para el movimiento del curso
public function CargarModulosVistosEstudianteMover($estudiante,$programa){
  $select="SELECT id_detalle_cargafk,
                  id_detalle_carga_estfk,
                  nombre_estudiante,
                  apellido_estudiante,
                  id_modulo_cargafk,
                  nombre_modulo,
                  id_periodo_cargafk,
                  periodo, 
                  id_curso_cargafk,
                  codigo_curso,
                  id_programa_inscripcionfk               
                  FROM detalle_carga dt 
                  INNER JOIN inscripcion i       ON dt.id_detalle_carga_estfk=i.id_inscripcion
                  INNER JOIN carga_academica ca  ON dt.id_detalle_cargafk=ca.id_carga
                  INNER JOIN modulo          m   ON ca.id_modulo_cargafk=m.id_modulo
                  INNER JOIN periodo         p   ON ca.id_periodo_cargafk=p.id_periodo
                  INNER JOIN curso           c   ON ca.id_curso_cargafk=c.id_curso
                  WHERE id_detalle_carga_estfk=:estudiante AND id_programa_inscripcionfk=:programa
                  GROUP BY  id_modulo_cargafk
                  ORDER BY id_modulo_cargafk";
      $parametros = array(':estudiante' => $estudiante,
                          ':programa'=>$programa);
     $resultado = $this->table($select, $parametros);
return $resultado;
}

//funcion para la carga de periodos para mover el estudiantes cruzado

public function CargarPeriodoMoverEstudiantesCruzado(){
  $select="SELECT id_periodo_cursofk AS cod,
                  periodo AS nombre
                  FROM curso c
                  INNER JOIN periodo p ON c.id_periodo_cursofk=p.id_periodo
                   GROUP BY id_periodo_cursofk";
 $resultado = $this->table($select);
   return $resultado;

}

//funcion para la carga de los curso para movimeinto de los estudiantes cruzados

function CargarCursosMoverCruzados($periodo, $curso){
  $select="SELECT  periodo_ofertafk,
                              sede_ofertafk,
                              programa_ofertafk,
                              curso_ofertafk as cod,
                              codigo_curso as nombre
                              FROM ofertaprograma op 
                              INNER JOIN  curso c ON op.curso_ofertafk=c.id_curso
                              WHERE periodo_ofertafk=:periodo AND curso_ofertafk<>:curso
                              ORDER BY programa_ofertafk";
$parametros = array(':periodo'=>$periodo,
                  ':curso'=>$curso);
$resultado = $this->table($select,$parametros);
return $resultado;
}

//funcion para la busquedad de sede programa de estudiantes cruzados
public function BuscarSedeProgramaCruzados($curso){
  $select="SELECT id_curso,
              id_sede_cursofk,
              nombre_sede,
              id_programa_cursofk,
              nombre_programa,
              id_horario_cursofk,
              nombre_horario,
              dia_horario
              FROM curso c 
              INNER JOIN sede s     ON  c.id_sede_cursofk=s.id_sede
              INNER JOIN programa p ON  c.id_programa_cursofk=p.id_programa
              INNER JOIN horario h    ON  c.id_horario_cursofk=h.id_horario
              WHERE id_curso=:curso";
$parametros = array(':curso'=>$curso);
$resultado = $this->row($select,$parametros);
return $resultado;
}

//funcion para cargar los planes de estudios de los cursos seleccionados 
public function CargarPensumCursoMOver($Rsede,$Rprograma){
  $select ="SELECT id_pensum,
              id_pensum_modulofk,
              id_modulo as cod,
              nombre_modulo,
              id_pensum_sedefk,
              nombre_sede,
              id_pensum_programafk,
              nombre_programa,
              id_pensum_versionfk,
              nombre_version,
              tipo_modulo,
              int_horas_pensum
              FROM pensum p 
              INNER JOIN modulo   m ON p.id_pensum_modulofk=m.id_modulo
              INNER JOIN sede     s ON p.id_pensum_sedefk=s.id_sede
              INNER JOIN programa pr ON p.id_pensum_programafk=pr.id_programa
              INNER JOIN version_pensum vp ON p.id_pensum_versionfk=vp.id_version    
              WHERE  id_pensum_programafk=:programa AND  id_pensum_sedefk=:sede 
              ORDER BY id_modulo";
              $parametros = array(':programa' => $Rprograma, 
                                   ':sede' =>  $Rsede);
              $resultado = $this->table($select, $parametros);
      return $resultado;
}
//fin funcion

//funcion para cargar los estudiantes matriculados del curso a mover
public function ListarEstudianteCursoMover($Rcurso){
  $select="SELECT id_matricula_curso,
                  id_estudiante_matricula_detallefk,
                  id_cursofk,
                  fecha_matricula_curso,
                  id_inscripcion,
                  identificacion,
                  nombre_estudiante,
                  apellido_estudiante,
                  email_estudiante,
                  id_convenio_inscripcionfk,
                  descripcion_convenio,
                  id_periodo_inscripcionfk,
                  codigo_curso,
                  periodo,
                  id_sede_inscripcionfk,
                  nombre_sede,
                  id_horario_inscripcionfk,
                  nombre_horario,
                  dia_horario,                
                  nombre_sede,
                  id_programa_inscripcionfk,
                  nombre_programa,
                  modulo_cruzado,
                  organizado_curso
FROM  matricula_curso mc 
INNER JOIN matricula   m     ON mc.id_matricula_curso=m.id_matricula
INNER JOIN inscripcion i     ON mc.id_estudiante_matricula_detallefk=i.id_inscripcion
INNER JOIN convenio    c     ON i.id_convenio_inscripcionfk=c.id_convenio
INNER JOIN periodo     p     ON i.id_periodo_inscripcionfk=p.id_periodo  
INNER JOIN curso       cc    ON mc.id_cursofk=cc.id_curso 
INNER JOIN sede        s     ON i.id_sede_inscripcionfk=s.id_sede
INNER JOIN horario     h     ON i.id_horario_inscripcionfk=h.id_horario
INNER JOIN programa    po    ON i.id_programa_inscripcionfk=po.id_programa

WHERE organizado_curso='2' AND modulo_cruzado='1' AND id_cursofk=:id_cursofk  ORDER BY id_estudiante_matricula_detallefk ASC";
$parametros = array(':id_cursofk'=> $Rcurso);
$resultado = $this->table($select, $parametros);
return $resultado;
}

//funcion para la busqueda del ultimo módulo programado
public function BuscarUltimoModuloProgramadoCursoMover($curso){
  $select=" SELECT id_curso_cargafk,
  id_sede_cursofk,
  id_programa_cursofk,
  id_modulo_cargafk,
  id_modulo,
  nombre_modulo,
  id_periodo_cargafk,
  periodo,
  tipo_modulo,
  IF (tipo_modulo='2', 'TRANSVERSAL', 'ESPECIFICO') AS tipo,
  MAX(id_periodo_cargafk) AS mayor       
  FROM carga_academica ca
  INNER  JOIN modulo  m 	ON ca.id_modulo_cargafk=m.id_modulo
  INNER  JOIN periodo p    ON ca.id_periodo_cargafk=p.id_periodo
  INNER  JOIN curso   c    ON ca.id_curso_cargafk=c.id_curso
              WHERE id_curso_cargafk=:curso 
              GROUP BY id_periodo_cargafk
              ORDER BY id_modulo";
$parametros = array(':curso'=>$curso);
$resultado = $this->table($select,$parametros);
return $resultado;
}


///funcion para validar el movimiento del estudiante cruzado si hay un modulo programado en el periodo de retiro
public function ValidarMoverEstudianteCruzado($curso, $periodo){
  $select ="SELECT id_carga,
                   id_curso_cargafk,
                   id_modulo_cargafk,
                   nombre_modulo,
                   id_periodo_cargafk,
                   id_docente_cargafk
            FROM carga_academica ca 
            INNER JOIN modulo m
            ON ca.id_modulo_cargafk=m.id_modulo
            WHERE id_periodo_cargafk=:periodo AND id_curso_cargafk=:curso";
                  $params=array(':curso'=>$curso,
                               ':periodo'=>$periodo);
                  $resultado =$this->row($select, $params);
  return $resultado;
 }
 
//funcion para validar el movimiento del estudiante curuzado verificar el modulo programado
public function ValidarModuloValidoMoverEstudianteCruzado($Rmodulo, $Rsede, $Rprograma){
  $select="SELECT id_pensum_modulofk,
                  id_pensum_sedefk,
                  id_pensum_programafk
                  FROM pensum
                  WHERE id_pensum_modulofk=:modulo AND id_pensum_sedefk=:sede AND id_pensum_programafk=:programa";
  $parametros = array(':modulo'=>$Rmodulo,
                      ':sede'   =>$Rsede,
                      ':programa'  =>$Rprograma);
  $resultado =$this->row($select,$parametros);
  return $resultado;
  }
   //funcion para cargar los modulo programados en el curso destino

   //funcion para la busqueda del ultimo módulo programado
public function CargarModulosProgramadosCursoDestino($curso,$periodo){
  $select=" SELECT id_curso_cargafk,
                   id_sede_cursofk,
                   id_programa_cursofk,
                   id_modulo_cargafk,
                   id_modulo as cod,
                   id_periodo_cargafk,
                   periodo,
                   CONCAT(nombre_modulo ,'/', periodo ) as nombre,
                   MAX(id_periodo_cargafk) AS mayor       
                  FROM carga_academica ca
                  INNER  JOIN modulo  m  	 ON ca.id_modulo_cargafk=m.id_modulo
                  INNER  JOIN curso   c    ON ca.id_curso_cargafk=c.id_curso
                  INNER  JOIN periodo p    ON ca.id_periodo_cargafk=p.id_periodo
                              WHERE id_curso_cargafk=:curso AND id_periodo_cargafk=:periodo
                              GROUP BY id_periodo_cargafk
                              ORDER BY id_modulo";
                $parametros = array(':curso'=>$curso,
                                    ':periodo'=>$periodo );
                $resultado = $this->table($select,$parametros);
                return $resultado;
                }

                //funcion mover estudiante cruzado al curso seleccionado 
public function GuardarMoverEstudianteCruzado($estudiante,$curso){  
  $update="UPDATE matricula_curso SET id_cursofk=:curso, modulo_cruzado=:cruzado WHERE id_estudiante_matricula_detallefk=:id";
  $params=array(':id'=>$estudiante,
                ':curso'=>$curso,
                ':cruzado'=>'1');
  $resultado =$this->query($update, $params);
  return $resultado;
} 

//funcion para guardar en el detalle de la asignación de modulos
public function GuardarEstudianteDetalleCargaCruzado($idcarga, $idestudiante, $idmodulo, $idperiodo, $idcurso, $iddocente){    
  $insert="INSERT INTO detalle_carga(id_detalle_cargafk,
                                  id_detalle_carga_estfk,
                                  id_detalle_modulofk,
                                  id_detalle_periodofk,
                                  id_detalle_cursofk,
                                  id_detalle_docentefk,
                                  fecha_detalle_carga)VALUES(:id_carga, 
                                                             :id_estudiante,
                                                             :id_modulo,
                                                             :id_periodo,
                                                             :id_curso,
                                                             :id_docente,
                                                             :fecha)";
  $params=array(':id_carga'=>$idcarga,
                ':id_estudiante'=>$idestudiante,
                ':id_modulo'=>$idmodulo,
                ':id_periodo'=>$idperiodo,
                ':id_curso'=>$idcurso, 
                ':id_docente'=>$iddocente,
                ':fecha' => $this->datetimeNow());
  $resultado =$this->query($insert, $params);
  return $resultado;
}  


//funcion abrir grupo cambiar de estado ofertado a abierto
public function AbrirGrupoCruzado($curso){
  $sql="UPDATE curso SET valor_registro='2'  WHERE id_curso=:curso";
  $parametros =array(':curso'=>$curso);
  $respuesta =$this->query($sql, $parametros);
  return $respuesta;
}

//funcion para la carga de record modulos estudiantes matricula modulo adicional
public function CargarRecordModulosEstudianteModuloAdicional($estudiante,$programa){
  $select="SELECT id_detalle_cargafk,
                  id_detalle_carga_estfk,
                  nombre_estudiante,
                  apellido_estudiante,
                  id_modulo_cargafk,
                  nombre_modulo,
                  id_periodo_cargafk,
                  periodo, 
                  id_curso_cargafk,
                  codigo_curso,
                  id_programa_inscripcionfk
                  FROM detalle_carga dt 
                  INNER JOIN inscripcion i       ON dt.id_detalle_carga_estfk=i.id_inscripcion
                  INNER JOIN carga_academica ca  ON dt.id_detalle_cargafk=ca.id_carga
                  INNER JOIN modulo          m   ON ca.id_modulo_cargafk=m.id_modulo
                  INNER JOIN periodo         p   ON ca.id_periodo_cargafk=p.id_periodo
                  INNER JOIN curso           c   ON ca.id_curso_cargafk=c.id_curso
                  WHERE id_detalle_carga_estfk=:estudiante AND id_programa_inscripcionfk=:programa
                  GROUP BY  id_modulo_cargafk
                  ORDER BY id_periodo_cargafk DESC";
      $parametros = array(':estudiante' => $estudiante,
                          ':programa'=>$programa);
     $resultado = $this->table($select, $parametros);
return $resultado;
}

//funcion para validar el modulo seleccionado en la matricula adicional
public function ValidarModuloAdicional($idmodulo, $idestudiante){
  $select="SELECT id_detalle_carga_estfk,
            id_detalle_modulofk,
            nombre_modulo,
            nombre_estudiante,
            apellido_estudiante,
            id_detalle_periodofk,
            periodo
            FROM detalle_carga dc 
            INNER JOIN modulo  	m ON dc.id_detalle_modulofk=m.id_modulo
            INNER JOIN periodo 	p ON dc.id_detalle_periodofk=p.id_periodo
            INNER JOIN inscripcion   i ON dc.id_detalle_carga_estfk=i.id_inscripcion	
            WHERE id_detalle_modulofk=:modulo AND id_detalle_carga_estfk=:idestudiante";
           $parametros = array(':idestudiante' => $idestudiante,
                               ':modulo'=>$idmodulo);
           $resultado = $this->row($select, $parametros);
            return $resultado;
}

//funcion para la busquedad de modulos para matricula de modulo adicional
public function BuscarModuloAdicional($idmodulo, $idperiodo){
  $select="SELECT id_carga,
                  id_curso_cargafk,
                  codigo_curso,
                  id_modulo_cargafk,
                  nombre_modulo,
                  id_periodo_cargafk,
                  periodo,
                  fechainicio,
                  fechafin,
                  id_docente_cargafk,
                  nombre_docente,
                  apellido_docente,
                  id_sede_cursofk,
                  nombre_sede,
                  id_horario_cursofk,
                  nombre_horario,
                  dia_horario,
                  id_programa_cursofk,
                  nombre_programa
                  FROM carga_academica ca
                  INNER JOIN curso  	     c ON ca.id_curso_cargafk=c.id_curso
                  INNER JOIN modulo 	     m ON ca.id_modulo_cargafk=m.id_modulo
                  INNER JOIN periodo 	     p ON ca.id_periodo_cargafk=p.id_periodo
                  INNER JOIN docente       d ON ca.id_docente_cargafk=d.id_docente
                  INNER JOIN sede          s ON c.id_sede_cursofk=s.id_sede
                  INNER JOIN horario       h ON c.id_horario_cursofk=h.id_horario
                  INNER JOIN programa      pp ON c.id_programa_cursofk=pp.id_programa
                  WHERE id_modulo_cargafk =:modulo AND id_periodo_cargafk=:periodo";
                  $parametros = array(':modulo' => $idmodulo,
                                      ':periodo'=>$idperiodo);
                  $resultado = $this->table($select, $parametros);
                    return $resultado;
}

//funcion para guardar modulo adicional
public function GuardarModuloAdicional($carga,$estudiante, $modulo, $periodo, $curso, $docente){
$insert = "INSERT INTO detalle_carga (id_detalle_cargafk, 
                                      id_detalle_carga_estfk, 
                                      id_detalle_modulofk,
                                      id_detalle_periodofk,
                                      id_detalle_cursofk,
                                      id_detalle_docentefk,
                                      fecha_detalle_carga)
                              VALUES(:detalle_carga, 
                                     :detalle_estudiante,
                                     :detalle_modulo,
                                     :detalle_periodo,
                                     :detalle_curso,
                                     :detalle_docente,
                                     :fecha_detalle_carga)";
                    $params = array( ':detalle_carga'=>$carga, 
                                     ':detalle_estudiante'=>$estudiante,
                                     ':detalle_modulo'=>$modulo,
                                     ':detalle_periodo'=>$periodo,
                                     ':detalle_curso'=>$curso,
                                     ':detalle_docente'=>$docente,
                                     ':fecha_detalle_carga' => $this->datetimeNow());
                              $resultado = $this->query($insert, $params);
  return $resultado;
}

//funcion matricular el estudiante en el curso para el modulo adicional
public function MatriculaCursoAdicional($estudiante, $curso){
  $insert = "INSERT INTO matricula_curso (id_estudiante_matricula_detallefk, 
                                          id_cursofk, 
                                          estado_matricula_curso,
                                          modulo_cruzado,
                                          organizado_curso,
                                          matricula_activa,
                                          periodo_cruce,
                                          modulo_adicional,
                                          fecha_matricula_curso)
                                VALUES(:estudiante, 
                                       :curso,
                                       :estado_matricula,
                                       :modulo_cruzado,
                                       :organizado_curso,
                                       :matricula_activa,
                                       :periodo_cruce,
                                       :modulo_adicional,
                                       :fecha_matricula_curso)";
                      $params = array( ':estudiante'=>$estudiante, 
                                       ':curso'=>$curso,
                                       ':estado_matricula'=>'on',
                                       ':organizado_curso'=>'2',
                                       ':modulo_cruzado'=>'1',
                                       ':matricula_activa'=>'2',
                                       ':modulo_adicional'=>'2',
                                       ':periodo_cruce'=>'0',
                                       ':fecha_matricula_curso' => $this->datetimeNow());
                                $resultado = $this->query($insert, $params);
    return $resultado;
  }

 //funcion para enviar email a estudiante informando que se ha matriculado en un módulo adicional
 public function EnviarEmailMatriculaModuloAdicional($idmodulo,$idperiodo,$idestudiante){
  $select="SELECT id_detalle_carga_estfk,
                  nombre_estudiante,
                  apellido_estudiante,
                  email_estudiante,
                  id_detalle_modulofk,
                  nombre_modulo,
                  nombre_estudiante,
                  apellido_estudiante,
                  id_detalle_periodofk,
                  periodo,
                  id_detalle_cursofk,
                  codigo_curso,
                  link_whatsaap_curso,
                  id_horario_cursofk,
                  nombre_horario,
                  dia_horario,
                  id_detalle_docentefk,
                  nombre_docente,
                  apellido_docente,
                  fecha_detalle_carga,
                  id_sede_cursofk,
                  nombre_sede
                  FROM detalle_carga dc 
                        INNER JOIN modulo  		    m ON dc.id_detalle_modulofk=m.id_modulo
                        INNER JOIN periodo 		    p ON dc.id_detalle_periodofk=p.id_periodo
                        INNER JOIN curso    	  	c ON dc.id_detalle_cursofk=c.id_curso
                        INNER JOIN inscripcion 	  i ON dc.id_detalle_carga_estfk=i.id_inscripcion	
                        INNER JOIN horario   	    h ON c.id_horario_cursofk=h.id_horario
                        INNER JOIN docente        d ON dc.id_detalle_docentefk=d.id_docente
                        INNER JOIN sede           s ON c.id_sede_cursofk=s.id_sede
 WHERE id_detalle_modulofk =:modulo AND id_detalle_periodofk=:periodo AND id_detalle_carga_estfk=:estudiante";
           $parametros = array(':modulo' => $idmodulo,
                               ':periodo'=>$idperiodo,
                               ':estudiante'=>$idestudiante);
           $resultado = $this->row($select, $parametros);
          return $resultado;
}

//funcion para cargar los módulos sugueridos del ultimo periodo porgramado
public function CargarUltimoPeriodoProgramado($periodo){
  $select="SELECT id_curso_cargafk,
                  codigo_curso,
                  id_horario_cursofk,
                  nombre_horario,
                  dia_horario,
                  id_sede_cursofk,
                  nombre_sede,
                  id_programa_cursofk,
                  nombre_programa,
                  id_modulo_cargafk,
                  nombre_modulo,
                  tipo_modulo,
                  id_docente_cargafk,
                  nombre_docente,
                  apellido_docente,
                  id_periodo_cargafk,
                  periodo,
                  estado_carga
                  FROM carga_academica ca 
                  INNER JOIN  curso 	  c 	  ON ca.id_curso_cargafk=c.id_curso
                  INNER JOIN  horario 	h	    ON c.id_horario_cursofk=h.id_horario
                  INNER JOIN  modulo 	  m	    ON ca.id_modulo_cargafk=m.id_modulo
                  INNER JOIN  docente   d     ON ca.id_docente_cargafk=d.id_docente
                  INNER JOIN  periodo   p     ON ca.id_periodo_cargafk=p.id_periodo
                  INNER JOIN  sede      s     ON c.id_sede_cursofk=s.id_sede
                  INNER JOIN  programa  pp    ON c.id_programa_cursofk=pp.id_programa
                  WHERE id_periodo_cargafk=:periodo
                  ORDER BY  id_horario_cursofk  ASC";
                  $parametros = array(':periodo'=>$periodo);
                  $resultado = $this->table($select, $parametros);
            return $resultado;
}

//funcion para la carga de modulo programdos transversales
public function CargarModulosProgramadosTransversales($periodo){
  $select="SELECT id_curso_cargafk,
                  codigo_curso,
                  id_horario_cursofk,
                  nombre_horario,
                  dia_horario,
                  id_sede_cursofk,
                  nombre_sede,
                  id_programa_cursofk,
                  nombre_programa,
                  id_modulo_cargafk,
                  nombre_modulo,
                  tipo_modulo,
                  id_docente_cargafk,
                  nombre_docente,
                  apellido_docente,
                  id_periodo_cargafk,
                  periodo,
                  estado_carga
                  FROM carga_academica ca 
                  INNER JOIN  curso 	  c 	  ON ca.id_curso_cargafk=c.id_curso
                  INNER JOIN  horario 	h	    ON c.id_horario_cursofk=h.id_horario
                  INNER JOIN  modulo 	  m	    ON ca.id_modulo_cargafk=m.id_modulo
                  INNER JOIN  docente   d     ON ca.id_docente_cargafk=d.id_docente
                  INNER JOIN  periodo   p     ON ca.id_periodo_cargafk=p.id_periodo
                  INNER JOIN  sede      s     ON c.id_sede_cursofk=s.id_sede
                  INNER JOIN  programa  pp    ON c.id_programa_cursofk=pp.id_programa
                  WHERE id_periodo_cargafk=:periodo AND tipo_modulo='2'
                  ORDER BY  id_horario_cursofk  ASC";
                  $parametros = array(':periodo'=>$periodo);
                  $resultado = $this->table($select, $parametros);
            return $resultado;
}
//funcion para la carga de modulo programados especificos
public function CargarModulosProgramadosEspeficios($periodo){
  $select="SELECT id_curso_cargafk,
                  codigo_curso,
                  id_horario_cursofk,
                  nombre_horario,
                  dia_horario,
                  id_sede_cursofk,
                  nombre_sede,
                  id_programa_cursofk,
                  nombre_programa,
                  id_modulo_cargafk,
                  nombre_modulo,
                  tipo_modulo,
                  id_docente_cargafk,
                  nombre_docente,
                  apellido_docente,
                  id_periodo_cargafk,
                  periodo,
                  estado_carga
                  FROM carga_academica ca 
                  INNER JOIN  curso 	  c 	  ON ca.id_curso_cargafk=c.id_curso
                  INNER JOIN  horario 	h	    ON c.id_horario_cursofk=h.id_horario
                  INNER JOIN  modulo 	  m	    ON ca.id_modulo_cargafk=m.id_modulo
                  INNER JOIN  docente   d     ON ca.id_docente_cargafk=d.id_docente
                  INNER JOIN  periodo   p     ON ca.id_periodo_cargafk=p.id_periodo
                  INNER JOIN  sede      s     ON c.id_sede_cursofk=s.id_sede
                  INNER JOIN  programa  pp    ON c.id_programa_cursofk=pp.id_programa
                  WHERE id_periodo_cargafk=:periodo AND tipo_modulo='1'
                  ORDER BY  id_horario_cursofk  ASC";
                  $parametros = array(':periodo'=>$periodo);
                  $resultado = $this->table($select, $parametros);
            return $resultado;
}

//funcion para la carga de modulo programados en igual horario matriculado
public function CargarModulosProgramadosIgualHorario($periodo, $horario){
  $select="SELECT id_curso_cargafk,
                  codigo_curso,
                  id_horario_cursofk,
                  nombre_horario,
                  dia_horario,
                  id_sede_cursofk,
                  nombre_sede,
                  id_programa_cursofk,
                  nombre_programa,
                  id_modulo_cargafk,
                  nombre_modulo,
                  tipo_modulo,
                  id_docente_cargafk,
                  nombre_docente,
                  apellido_docente,
                  id_periodo_cargafk,
                  periodo,
                  estado_carga
                  FROM carga_academica ca 
                  INNER JOIN  curso 	  c 	  ON ca.id_curso_cargafk=c.id_curso
                  INNER JOIN  horario 	h	    ON c.id_horario_cursofk=h.id_horario
                  INNER JOIN  modulo 	  m	    ON ca.id_modulo_cargafk=m.id_modulo
                  INNER JOIN  docente   d     ON ca.id_docente_cargafk=d.id_docente
                  INNER JOIN  periodo   p     ON ca.id_periodo_cargafk=p.id_periodo
                  INNER JOIN  sede      s     ON c.id_sede_cursofk=s.id_sede
                  INNER JOIN  programa  pp    ON c.id_programa_cursofk=pp.id_programa
                  WHERE id_periodo_cargafk=:periodo AND tipo_modulo='1'
                  ORDER BY  id_horario_cursofk AND id_horario_cursofk=:horario  ASC";
                  $parametros = array(':periodo'=>$periodo,
                                       ':horario'=>$horario);
                  $resultado = $this->table($select, $parametros);
            return $resultado;
}



}





