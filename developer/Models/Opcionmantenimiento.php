<?php
 require_once('../Config/PDOconn.php');
  class  Opcionmantenimiento extends db {



//funcion para la carga academica de los docentes
public function BuscarCargarAcademica($periodo, $curso){
  $select="SELECT id_carga,
           id_curso_cargafk,
           COUNT(id_detalle_cargafk) AS cantidad,
           codigo_curso,
           id_modulo_cargafk,
           nombre_modulo,
           id_docente_cargafk,
           nombre_docente,
           apellido_docente,
           id_periodo_cargafk,
           periodo,
           fecha_carga,
           id_sede_cursofk,
           nombre_sede,
           id_horario_cursofk,
           nombre_horario,
           dia_horario,
           id_ambiente_cargafk,
           nombre_ambiente,
           fecha_carga,
           creado_por,
           estado_carga 
           FROM carga_academica ca 
               INNER JOIN detalle_carga 	dc 	ON ca.id_carga=dc.id_detalle_cargafk 
               INNER JOIN curso         	c 	ON ca.id_curso_cargafk=c.id_curso
               INNER JOIN modulo        	m	ON ca.id_modulo_cargafk=m.id_modulo
               INNER JOIN docente       	d 	ON ca.id_docente_cargafk=d.id_docente
               INNER JOIN periodo       	p 	ON ca.id_periodo_cargafk=p.id_periodo
               INNER JOIN sede          	s 	ON c.id_sede_cursofk=s.id_sede
               INNER JOIN horario       	h 	ON c.id_horario_cursofk=h.id_horario
               INNER JOIN ambiente      	a 	ON ca.id_ambiente_cargafk=a.id_ambiente
               WHERE   id_periodo_cargafk=:periodo AND id_curso_cargafk=:curso AND estado_carga='on'
               GROUP BY id_detalle_cargafk
               ORDER BY codigo_curso ASC";
$parametros = array(':periodo'=>$periodo,
                    ':curso'=>$curso );
$resultado = $this->table($select,$parametros);
return $resultado;
}


//funcion para validar las notas registradas del modulo
public function ValidarCarga($curso, $modulo, $periodo){
$select="SELECT id_nota_cursofk, id_nota_modulofk, id_nota_periodofk
          FROM notas
          WHERE id_nota_cursofk=:curso AND id_nota_modulofk=:modulo AND id_nota_periodofk=:periodo
          GROUP BY id_nota_cursofk,id_nota_modulofk";
$params = array(':curso'=>$curso, 
                ':modulo'=>$modulo,
                ':periodo'=>$periodo);
$res=$this->row($select, $params);
return $res;
}

//funcion eliminar carga academica
public function EliminarCargarAcademica($carga){
  $select="DELETE FROM carga_academica WHERE id_carga=:carga";
  $params = array(':carga'=>$carga);
  $res=$this->query($select, $params);
  return $res;
}

//funcion para eliminar el detalle de la carga academica
public function ElimininarDetalleCarga($carga){
  $select="DELETE FROM detalle_carga WHERE id_detalle_cargafk=:carga";
  $params = array(':carga'=>$carga);
  $res=$this->query($select, $params);
  return $res;
}

//funcion para eliminar los registro de asistencia del modulo programado

public function EliminarAsistencia($carga){
  $select ="DELETE from asistencia
           WHERE id_carga=:id_carga";
           $params=array(':id_carga'=>$carga);
           $resultado =$this->query($select, $params);
          return $resultado;
 }


//funcion para la carga de cursos por periodos
public function SelectCursosPeriodos($periodo){
        $select="SELECT id_carga,
                id_curso_cargafk as cod,
                codigo_curso as nombre,
                id_modulo_cargafk,
                nombre_modulo,
                id_docente_cargafk,
                nombre_docente,
                apellido_docente,
                id_periodo_cargafk,
                periodo,
                fecha_carga,
                id_sede_cursofk,
                nombre_sede,
                id_horario_cursofk,
                nombre_horario,
                id_programa_cursofk,
                dia_horario,
                id_ambiente_cargafk,
                nombre_ambiente,
                fecha_carga,
                creado_por FROM carga_academica ca 
                    INNER JOIN detalle_carga 	dc 	ON ca.id_carga=dc.id_detalle_cargafk 
                    INNER JOIN curso         	c 	ON ca.id_curso_cargafk=c.id_curso
                    INNER JOIN modulo        	m	  ON ca.id_modulo_cargafk=m.id_modulo
                    INNER JOIN docente       	d 	ON ca.id_docente_cargafk=d.id_docente
                    INNER JOIN periodo       	p 	ON ca.id_periodo_cargafk=p.id_periodo
                    INNER JOIN sede          	s 	ON c.id_sede_cursofk=s.id_sede
                    INNER JOIN horario       	h 	ON c.id_horario_cursofk=h.id_horario
                    INNER JOIN ambiente      	a 	ON ca.id_ambiente_cargafk=a.id_ambiente
                    WHERE   id_periodo_cargafk=:periodo
                    GROUP BY id_detalle_cargafk
                    ORDER BY id_programa_cursofk ASC";
 $params = array(':periodo'=>$periodo);
 $resultado = $this->table($select,$params);
return $resultado;
}

//funcion para busquedad de estudiantes
public function ListarEstudiantes($txtidentidad){
  $select = "SELECT id_inscripcion, 
                     identificacion,
                     tipo_identificacionfk,
                     nombre_identidad,
                     nombre_estudiante,
                     apellido_estudiante,
                     telefono_estudiante,
                     email_estudiante,
                     celular_estudiante,
                     fecha_nacimiento,
                     barrio_estudiante,
                     direccion_estudiante,
                     ciudad_estudiante,
                     id_colegiofk,
                     nombre_colegio,
                     nivel_academico,
                     graduado,
                     ultimo_anio,
                     ultimo_nivel_aprobado,
                     estado_civil,
                     discapacidad,
                     multicultura,
                     tipo_sangre,
                     numero_hijo,
                     estrato,
                     medio_transporte,
                     zona,
                     ocupacion,
                     id_epsfk,
                     id_sede_inscripcionfk,
                     nombre_sede,
                     id_periodo_inscripcionfk,
                     periodo,
                     id_programa_inscripcionfk,
                     nombre_programa,
                     id_horario_inscripcionfk,
                     nombre_horario,
                     dia_horario,
                     id_convenio_inscripcionfk,
                     descripcion_convenio,
                     id_medio_inscripcionfk,
                     origen_medio,
                     fecha_inscripcion,
                     usuario_registra_inscripcion,
                     estado_registro AS valor_registro,
                     CASE  
                     WHEN estado_registro='1' THEN 'INSCRITO' 
                     WHEN estado_registro='2' THEN 'MATRICULADO'                                    
                     END AS estado_registro    
                     FROM inscripcion i
                     INNER JOIN tipo_identidad          ti    ON i.tipo_identificacionfk=ti.id_tipo
                     INNER JOIN sede                    s     ON i.id_sede_inscripcionfk=s.id_sede
                     INNER JOIN colegio                 col   ON i.id_colegiofk=col.id_colegio
                     INNER JOIN periodo                 p     ON i.id_periodo_inscripcionfk=p.id_periodo
                     INNER JOIN programa                pt    ON i.id_programa_inscripcionfk=pt.id_programa
                     INNER JOIN horario                 h     ON i.id_horario_inscripcionfk=h.id_horario
                     INNER JOIN convenio                c     ON i.id_convenio_inscripcionfk=c.id_convenio
                     INNER JOIN medio_entrada           m     ON i.id_medio_inscripcionfk=m.id_medio
                     WHERE identificacion = :identificacion  AND estado_inscripcion='on' AND estado_registro='1'";
   $parametros = array(':identificacion' =>$txtidentidad);
   $resultado = $this->table($select,$parametros);
   return $resultado;
}


//funcion para la eliminar modulo duplicado
public function EliminarModuloDuplicado($iddetalle){
  $select="DELETE FROM detalle_carga WHERE id_detalle=:id";
  $params = array(':id'=>$iddetalle);
  $resultado=$this->query($select, $params);
  return $resultado;
}
//funcion para consulta de estudiantes matriculados en la base de datos
//funcion para busquedad de estudiantes
public function ListarMatriculas($txtidentidad){
  $select = "SELECT id_inscripcion, 
                    id_matricula_inscripcionfk,
                    identificacion,
                    tipo_identificacionfk,
                    nombre_identidad,
                    nombre_estudiante,
                    apellido_estudiante,
                    telefono_estudiante,
                    email_estudiante,
                    id_sede_inscripcionfk,
                    nombre_sede,
                    id_periodo_inscripcionfk,
                    periodo,
                    id_programa_inscripcionfk,
                    nombre_programa,
                    id_horario_inscripcionfk,
                    nombre_horario,
                    dia_horario,                  
                    fecha_inscripcion,
                    usuario_registra_inscripcion,
                    matricula_activa AS valor_matricula,
                    CASE  
                    WHEN matricula_activa='1' THEN 'INACTIVA' 
                    WHEN matricula_activa='2' THEN 'ACTIVA'                                    
                    END AS valor_matricula    
                    FROM inscripcion i
                    INNER JOIN tipo_identidad          ti    ON i.tipo_identificacionfk=ti.id_tipo
                    INNER JOIN sede                    s     ON i.id_sede_inscripcionfk=s.id_sede
                    INNER JOIN periodo                 p     ON i.id_periodo_inscripcionfk=p.id_periodo
                    INNER JOIN programa                pt    ON i.id_programa_inscripcionfk=pt.id_programa
                    INNER JOIN horario                 h     ON i.id_horario_inscripcionfk=h.id_horario
                    INNER JOIN matricula               ma    ON ma.id_matricula_inscripcionfk=i.id_inscripcion
                    INNER JOIN matricula_curso         mc    ON i.id_inscripcion=mc.id_estudiante_matricula_detallefk
                    WHERE identificacion = :identificacion   AND matricula_activa='2'";
   $parametros = array(':identificacion' =>$txtidentidad);
   $resultado = $this->table($select,$parametros);
   return $resultado;
}

//funcion para la busqueda de estudintes segun el modulo programado
public function BuscarEstudianteMatricula($identidad){
      $select="SELECT id_detalle_cargafk, 
                  id_detalle_carga_estfk,
                  identificacion,
                  nombre_estudiante,
                  apellido_estudiante,
                  id_detalle_modulofk,
                  nombre_modulo,
                  id_detalle_periodofk,
                  periodo,
                  id_detalle_cursofk,
                  codigo_curso,
                  id_detalle_docentefk,
                  nombre_docente,
                  apellido_docente
                  FROM 
                  detalle_carga dc 
                  INNER JOIN inscripcion 	i 	ON dc.id_detalle_carga_estfk=i.id_inscripcion
                  INNER JOIN modulo 	m       ON dc.id_detalle_modulofk=m.id_modulo
                  INNER JOIN periodo 	p	ON dc.id_detalle_periodofk=p.id_periodo
                  INNER JOIN curso         c       ON dc.id_detalle_cursofk=c.id_curso
                  INNER JOIN docente       d       ON dc.id_detalle_docentefk=d.id_docente
                  WHERE identificacion =:identidad";
                  $parametros = array(':identidad' =>$identidad);
                  $resultado = $this->table($select,$parametros);
              return $resultado;
}
//funcion para validar estudiante matriculado en curso
public function ValidarEstudianteMatriculado($curso, $identidad){
    $select = "SELECT id_matricula_curso,
                      id_estudiante_matricula_detallefk,
                      id_inscripcion,
                      identificacion,
                      nombre_estudiante,
                      apellido_estudiante,
                      id_programa_inscripcionfk,
                      id_sede_inscripcionfk,
                      nombre_sede,
                      id_horario_inscripcionfk,
                      nombre_horario,
                      dia_horario,
                      nombre_programa,
                      id_cursofk
                      FROM matricula_curso mc 
                      INNER JOIN inscripcion i ON mc.id_estudiante_matricula_detallefk=i.id_inscripcion
                      INNER JOIN programa    p ON i.id_programa_inscripcionfk = p.id_programa
                      INNER JOIN sede        s ON i.id_sede_inscripcionfk=s.id_sede
                      INNER JOIN horario     h ON i.id_horario_inscripcionfk=h.id_horario
              WHERE  id_cursofk=:curso AND identificacion=:identidad";
              $parametros = array(
                             ':identidad' => $identidad,
                             ':curso' => $curso);
                $resultado = $this->row($select, $parametros);
                return $resultado;
}
//funcion para busqueda de modulo validado
public function ValidarModuloEstudiante($idestudiante,$curso){
$select="SELECT id_detalle_cargafk, 
                id_detalle_carga_estfk,
                identificacion,
                nombre_estudiante,
                apellido_estudiante,
                id_detalle_modulofk,
                nombre_modulo,
                id_detalle_periodofk,
                periodo,
                id_detalle_cursofk,
                codigo_curso,
                id_detalle_docentefk,
                nombre_docente,
                apellido_docente
                FROM 
                detalle_carga dc 
                INNER JOIN inscripcion 	  i 	ON dc.id_detalle_carga_estfk=i.id_inscripcion
                INNER JOIN modulo 	      m   ON dc.id_detalle_modulofk=m.id_modulo
                INNER JOIN periodo 	      p	  ON dc.id_detalle_periodofk=p.id_periodo
                INNER JOIN curso          c   ON dc.id_detalle_cursofk=c.id_curso
                INNER JOIN docente        d   ON dc.id_detalle_docentefk=d.id_docente
                WHERE id_detalle_carga_estfk=:estudiante AND id_detalle_cursofk=:curso";
                 $parametros = array(
                  ':estudiante'=>$idestudiante,
                  ':curso'=>$curso);
     $resultado = $this->table($select, $parametros);
     return $resultado;
}


//para el estudiante que se esta buscado, toca revisar esta
public function ValidarModuloPensum($modulo,$sede, $programa){
$select="SELECT id_pensum_modulofk,
                nombre_modulo,
                id_pensum_sedefk,
                id_pensum_programafk
                FROM pensum p 
                INNER JOIN modulo m ON p.id_pensum_modulofk=m.id_modulo
                WHERE id_pensum_modulofk=:modulo AND id_pensum_sedefk=:sede AND id_pensum_programafk=:programa";
                $parametros = array(':modulo'=>$modulo,
                                      ':sede'=>$sede,
                                      ':programa'=>$programa);
                $resultado = $this->table($select, $parametros);
                return $resultado;
}
///verificar si el modulo se ha visto por el estudiante en otro curso
public function ValidarModuloOtroCurso($identificacion,$modulo){
  $select="SELECT id_detalle_cargafk, 
                    id_detalle_carga_estfk,
                    identificacion,
                    nombre_estudiante,
                    apellido_estudiante,
                    id_detalle_modulofk,
                    nombre_modulo,
                    id_detalle_periodofk,
                    periodo,
                    id_detalle_cursofk,
                    codigo_curso,
                    id_detalle_docentefk,
                    nombre_docente,
                    apellido_docente
                    FROM 
                      detalle_carga dc 
                        INNER JOIN inscripcion 	  i 	ON dc.id_detalle_carga_estfk=i.id_inscripcion
                        INNER JOIN modulo 	      m   ON dc.id_detalle_modulofk=m.id_modulo
                        INNER JOIN periodo 	      p	  ON dc.id_detalle_periodofk=p.id_periodo
                        INNER JOIN curso          c   ON dc.id_detalle_cursofk=c.id_curso
                        INNER JOIN docente        d   ON dc.id_detalle_docentefk=d.id_docente
                    WHERE identificacion=:identificacion AND id_detalle_modulofk=:modulo";
                    $parametros = array(':identificacion'=>$identificacion,
                                        ':modulo'=>$modulo);
                    $resultado = $this->row($select, $parametros);
      return $resultado;
}

 public function TraerInfoEstudiante($identidad){
 $select="SELECT id_inscripcion,
                  identificacion,
                  nombre_estudiante,
                  apellido_estudiante,
                  id_sede_inscripcionfk,
                  nombre_sede,
                  id_programa_inscripcionfk,
                  nombre_programa,
                  id_horario_inscripcionfk
                  nombre_horario,
                  dia_horario    
                    FROM inscripcion i 
                        INNER JOIN sede 		s ON i.id_sede_inscripcionfk=s.id_sede
                        INNER JOIN programa 	p ON i.id_programa_inscripcionfk=p.id_programa
                        INNER JOIN horario         h ON i.id_horario_inscripcionfk=h.id_horario
                    WHERE identificacion=:identidad";
  $parametros = array(':identidad'=>$identidad);
  $resultado = $this->row($select, $parametros);
  return $resultado;
}

//funcon para comprobar si el modulo correponde al plan de estudio del programa
public function ValidarModuloPrograma($sede, $programa, $modulo){
  $select="SELECT id_pensum_modulofk,
                nombre_modulo,
                id_pensum_sedefk,
                id_pensum_programafk
                FROM pensum p 
                INNER JOIN modulo m ON p.id_pensum_modulofk=m.id_modulo
                WHERE id_pensum_modulofk=:modulo AND id_pensum_sedefk=:sede AND id_pensum_programafk=:programa";
                $parametros = array(':modulo'=>$modulo,
                                     ':sede'=>$sede,
                                     ':programa'=>$programa);
                $resultado = $this->row($select, $parametros);
                return $resultado;
}
//funcion para buscar el id del docente
public function HallarInfocarga($modulo, $curso, $periodo){
  $select="SELECT id_detalle_cargafk,
  id_detalle_modulofk,
  id_detalle_periodofk,
  id_detalle_docentefk,
  id_detalle_cursofk
    FROM detalle_carga
    WHERE id_detalle_modulofk=:modulo AND id_detalle_cursofk=:curso AND id_detalle_periodofk=:periodo 
    GROUP BY id_detalle_cargafk";
    $parametros = array(':modulo'=>$modulo,
                        ':curso'=>$curso,
                        ':periodo'=>$periodo);
    $resultado = $this->row($select, $parametros);
    return $resultado;
}


//funcion para guardar el modulo en el detalle de la carga academica
public function GuardarCargarModulo($idcarga, $idestudiante, $idmodulo, $idperiodo, $idcurso, $iddocente){    
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

//validar el modulo matriculado
public function ValidarModuloMatriculado($modulo, $curso, $identidad){
$select="SELECT id_detalle_carga_estfk,
                identificacion,
                id_detalle_modulofk,
                id_detalle_cursofk
                 FROM detalle_carga dc 
                 INNER JOIN inscripcion i ON dc.id_detalle_carga_estfk=i.id_inscripcion 
             WHERE identificacion=:identificacion AND id_detalle_modulofk=:modulo AND id_detalle_cursofk=:curso";
              $parametros = array(':modulo'=>$modulo,
                                  ':curso'=>$curso,
                                  ':identificacion'=>$identidad);
              $resultado = $this->row($select, $parametros);
              return $resultado; 
    }

//public function buscar modulos duplicados
public function BuscarModulosDuplicados($identidad){
  $select="SELECT id_detalle,
                  id_detalle_cargafk,
                  identificacion,
                  id_detalle_carga_estfk,
                  id_detalle_modulofk,
                  nombre_modulo,
                  id_detalle_cursofk,
                  codigo_curso,
                  id_horario_cursofk,
                  nombre_horario,
                  dia_horario,
                  id_detalle_docentefk,
                  nombre_docente,
                  apellido_docente,
                  id_detalle_periodofk,
                  periodo,
                  nombre_sede,
                  fecha_detalle_carga
                  FROM detalle_carga dc 
                  INNER JOIN inscripcion i ON dc.id_detalle_carga_estfk=i.id_inscripcion
                  INNER JOIN modulo      m ON dc.id_detalle_modulofk=m.id_modulo
                  INNER JOIN curso       c ON dc.id_detalle_cursofk=c.id_curso
                  INNER JOIN docente     d ON dc.id_detalle_docentefk=d.id_docente
                  INNER JOIN horario     h ON c.id_horario_cursofk=h.id_horario
                  INNER JOIN periodo     p ON dc.id_detalle_periodofk=p.id_periodo
                  INNER JOIN sede        s ON c.id_sede_cursofk=s.id_sede
                  WHERE identificacion=:identidad";
                $parametros = array(':identidad'=>$identidad);
                $resultado = $this->table($select, $parametros);
      return $resultado; 
      }

}


