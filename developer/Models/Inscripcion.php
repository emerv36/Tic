<?php
require_once('../Config/PDOconn.php');
class Inscripcion extends db {

//función para insertar estudiantes en programa academico
 public function GuardarInscripcion($txtRegistroIdentificacion,
                                    $txtRegistroTipoIdentificacion,
                                    $txtRegistroNombreEstudiante,
                                    $txtRegistroApellidoEstudiante,
                                    $txtRegistroEmail,
                                    $txtRegistroCelular,
                                    $txtRegistroTipoSangre,
                                    $txtRegistroCategoriaCarnet,
                                    $txtRegistroSede,
                                    $txtRegistroPrograma,
                                    $txtRegistroLote,
                                    $txtRegistroIndicador,
                                    $txtRegistroCodigoFoto,
                                    $txtRegistroNovedad,
                                    $txtpassword_estudiante,
                                    $txtchip,
                                    $txtusuario,
                                    $txtLugarReclamo
                                     ){
    $creado_por=$_SESSION['nombres'];
    $insert = "INSERT INTO inscripcion (identificacion,
                                        tipo_identificacionfk,
                                        nombre_estudiante,
                                        apellido_estudiante,
                                        email_estudiante,
                                        celular_estudiante,
                                        tipo_sangre,
                                        categoria_carnet,
                                        id_sede_inscripcionfk,
                                        id_programa_inscripcionfk,
                                        id_lote_inscripcionfk,
                                        indicador_foto,
                                        codigo_foto,                                       
                                        obscarnetfk,
                                        estado_inscripcion,
                                        tipo_carnet,
                                        usuario_registra_inscripcion,
                                        password_estudiante,
                                        chip_carnet,
                                        id_usuario_carnetfk,
                                        lugar_reclamo)
                    VALUES(:identificacion,
                           :tipo_identificacionfk,
                           :nombre_estudiante,
                          :apellido_estudiante,
                          :email_estudiante,
                          :celular_estudiante,
                          :tipo_sangre,
                          :categoria_carnet,
                          :id_sede_inscripcionfk,
                          :id_programa_inscripcionfk,
                          :id_lote_inscripcionfk,
                          :indicador_foto,
                          :codigo_foto,
                          :obscarnetfk,
                          :estado_inscripcion,
                          :tipo_carnet,
                          :usuario_registra_inscripcion,
                          :password_estudiante,
                          :chip_carnet,
                          :usuario,
                          :lugar_reclamo)";

             $params=array(':identificacion'=>$txtRegistroIdentificacion,
                           ':tipo_identificacionfk'=>$txtRegistroTipoIdentificacion,
                           ':nombre_estudiante'=>$txtRegistroNombreEstudiante,
                           ':apellido_estudiante'=>$txtRegistroApellidoEstudiante,
                           ':email_estudiante'=>$txtRegistroEmail,
                           ':celular_estudiante'=>$txtRegistroCelular,
                           ':tipo_sangre'=>$txtRegistroTipoSangre,
                           ':categoria_carnet'=>$txtRegistroCategoriaCarnet,
                           ':id_sede_inscripcionfk'=>$txtRegistroSede,
                           ':id_programa_inscripcionfk'=>$txtRegistroPrograma,
                           ':id_lote_inscripcionfk'=>$txtRegistroLote,
                           ':indicador_foto'=>$txtRegistroIndicador,
                           ':codigo_foto'=>$txtRegistroCodigoFoto,
                           ':obscarnetfk'=>$txtRegistroNovedad,
                           ':estado_inscripcion'=>'1',
                           ':tipo_carnet'=>'1',
                           ':usuario_registra_inscripcion'=>$creado_por,
                           ':password_estudiante'=>$txtpassword_estudiante,
                           ':chip_carnet'=>$txtchip,
                           ':usuario'=>$txtusuario, 
                           ':lugar_reclamo' => $txtLugarReclamo
                           );
               $respuesta = $this->query($insert, $params);
               return $respuesta;
    }
//fin función para insertar estudiantes



//funcion para listar los estudiantes inscritos
public function ListarInscritos(){
      $sql="SELECT id_inscripcion,
                      identificacion,
                      tipo_identificacionfk,
                      codigo_identidad,
                      nombre_identidad,
                      id_lugar_expedicionfk,
                      nombre_ciudad,
                      nombre_estudiante,
                      apellido_estudiante,
                      telefono_estudiante,
                      celular_estudiante,
                      genero_estudiante,
                      email_estudiante,
                      celular_estudiante,
                      barrio_estudiante,
                      direccion_estudiante,     
                      id_ciudad_direccionfk,
                      nombre_ciudad AS ciudad_direccion,
                      id_ciudad_nacimientofk,
                      fecha_nacimiento,
                      nombre_ciudad_direccion,
                      id_colegiofk,
                      nombre_colegio, 
                      grado_anterior_aprobado,       
                      grado_aplicafk,
                      nombre_grado,
                      grado_anterior_cursadofk,
                      nombre_grado_anterior,
                      id_periodo_inscripcionfk,
                      nombre_periodo_anio,
                      id_horario_inscripcionfk,
                      nombre_horario,
                      dia_horario,
                      id_jornada_inscripcionfk,
                      nombre_jornada,     
                      discapacidad,
                      estado_civil,
                      multicultura,
                      tipo_sangre,
                      numero_hijo,
                      estrato,
                      medio_transporte,
                      zona,
                      ocupacion,
                      id_epsfk,
                      nombre_eps,
                      nombre_acudiente,
                      tipo_identidad_acudiente,
                      numero_documento_acudiente,
                      celular_acudiente,
                      telefono_acudiente,
                      direccion_acudiente,
                      parentesco,
                      ciudad_acudientefk,
                      nombre_ciudad_acudiente,
                      direccion_acudiente,
                      fecha_inscripcion,
                      estado_inscripcion,
                      usuario_registra_inscripcion,
                      foto_estudiante,
                      estado_registro AS valor_registro,
                      CASE  
                      WHEN estado_registro='1' THEN 'INSCRITO' 
                      WHEN estado_registro='2' THEN 'MATRICULADO'                                    
                      END AS estado_registro      
                      FROM inscripcion i
                            INNER JOIN tipo_identidad t  		          	ON i.tipo_identificacionfk=t.id_tipo
                            INNER JOIN ciudad c  				                ON i.id_lugar_expedicionfk=c.id_ciudad
                            INNER JOIN ciudad_direccion_estudiante cc  	ON i.id_ciudad_direccionfk=cc.id_ciudad_direccion
                            INNER JOIN colegio col                       ON i.id_colegiofk=col.id_colegio
                            INNER JOIN periodo_anio  p                   ON i.id_periodo_inscripcionfk=p.id_periodo_anio
                            INNER JOIN grado        g                    ON i.grado_aplicafk = g.id_grado
                            INNER JOIN grado_anterior ga                 ON i.grado_anterior_cursadofk=ga.id_grado_anterior
                            INNER JOIN horario      h                    ON i.id_horario_inscripcionfk=h.id_horario
                            INNER JOIN jornada      j                    ON h.id_jornadafk=j.id_jornada
                            INNER JOIN ciudad_acudiente ac               ON i.ciudad_acudientefk=ac.id_ciudad_acudiente
                            INNER JOIN eps         ep                    ON i.id_epsfk=ep.id_eps
       WHERE estado_registro='1' AND estado_inscripcion='on' ORDER BY id_inscripcion DESC";
       $respuesta =$this->table($sql);
       return $respuesta; 

}
//fin funcion

//funcion para cargas las inscripciones que fueron eliminadas

//funcion para listar los estudiantes inscritos
public function ListarMatriculados(){
 $sql="SELECT id_inscripcion, 
       identificacion,
       tipo_identificacionfk,
       nombre_identidad,
       nombre_estudiante,
       apellido_estudiante,
       telefono_estudiante,
       id_ciudadfk,
       nombre_ciudad,
      email_estudiante,
       celular_estudiante,
       id_sede_inscripcionfk,
       nombre_sede,
       id_periodo_inscripcionfk,
       periodo,
       id_programa_inscripcionfk,
       nombre_programa,
       nombre_programa,
       valor_programa,
       valor_pension,
       valor_seguro,
       id_horario_inscripcionfk,
       nombre_horario,
       id_convenio_inscripcionfk,
       descripcion_convenio,
       des_convenio,
       id_medio_inscripcionfk,
       origen_medio,
       fecha_inscripcion,
       usuario_registra_inscripcion,
       estado_inscripcion,
       estado_registro AS valor_registro,
        CASE  
            WHEN estado_registro='1' THEN 'INSCRITO' 
            WHEN estado_registro='2' THEN 'MATRICULADO'                                    
            END AS estado_registro      
       FROM inscripcion i
       INNER JOIN tipo_identidad          ti    ON i.tipo_identificacionfk=ti.id_tipo
       INNER JOIN sede                    s     ON i.id_sede_inscripcionfk=s.id_sede
       INNER JOIN periodo                 p     ON i.id_periodo_inscripcionfk=p.id_periodo
       INNER JOIN programa                pt    ON i.id_programa_inscripcionfk=pt.id_programa
       INNER JOIN horario                 h     ON i.id_horario_inscripcionfk=h.id_horario
       INNER JOIN convenio                c     ON i.id_convenio_inscripcionfk=c.id_convenio
       INNER JOIN medio_entrada           m     ON i.id_medio_inscripcionfk=m.id_medio
       INNER JOIN ciudad                  cc    ON i.id_ciudadfk=cc.id_ciudad
       WHERE estado_registro='2'  ORDER BY nombre_estudiante ASC";
       $respuesta =$this->table($sql);
       return $respuesta; 

}

function eliminarRegistro($codigo){
    $delete = "DELETE FROM inscripcion WHERE id_inscripcion=:id";
    $parametros = array(
        ":id" => $codigo
    );
    $respuesta = $this->query($delete, $parametros);
    return $respuesta;
}




//funcion para cargar los tipos de identificacion
  public function CargarTipoIdentificacion(){
      $sql="SELECT id_tipo as cod, 
                   nombre_identidad as nombre,
                   estado_identidad
                   FROM tipo_identidad
                   WHERE estado_identidad='on'";
      $respuesta =$this->table($sql);
      return $respuesta;
  }


//funcion


//funcion cambiar estado estudiante de inscrito a matriculado
public function CambiarEstadoEstudiante($id){
          $valor="2";
          $sql="UPDATE inscripcion SET estado_registro=:estado_registro,fecha_inscripcion=:fecha_inscripcion WHERE id_inscripcion=:codigo";
          $parametros =array(':estado_registro'=>$valor,':codigo'=>$id,':fecha_inscripcion'=> $this->datetimeNow());
          $respuesta =$this->query($sql, $parametros);
        return $respuesta;
}
 
//fin funcion 

 // función para actualizar la información del estudiante
  public function EditarInscripcion( $txtidinscripcion,
                                     $txtidentificacion,
                                     $txttipoidentificacion,
                                     $txtnombre,
                                     $txtapellido,
                                     $txtcelular,
                                     $txtemail,
                                     $txttiposangre,
                                     $txtlote,
                                     $txtindicador,
                                     $txtcodigofoto,
                                     $txtnovedad,
                                     $txtchip){
      
  $update="UPDATE inscripcion SET    id_inscripcion=:id_inscripcion,
                                     identificacion =:identificacion,
                                     tipo_identificacionfk =:tipo_identificacionfk,
                                     nombre_estudiante =:nombre_estudiante,
                                     apellido_estudiante =:apellido_estudiante,
                                     celular_estudiante=:celular,
                                     email_estudiante=:email_estudiante,
                                     tipo_sangre=:tipo_sangre,
                                     id_lote_inscripcionfk=:id_lote_inscripcionfk,
                                     indicador_foto=:indicador_foto,
                                     codigo_foto=:codigo_foto,
                                     obscarnetfk=:obscarnetfk,
                                     chip_carnet=:chip_carnet
                                
                                  WHERE id_inscripcion=:id_inscripcion";

      $parametros = array( ':id_inscripcion'=>$txtidinscripcion,
                           ':identificacion'=>$txtidentificacion,
                           ':tipo_identificacionfk'=>$txttipoidentificacion,
                           ':nombre_estudiante'=>$txtnombre,
                           ':apellido_estudiante'=>$txtapellido,
                           ':celular'=>$txtcelular,
                           ':email_estudiante'=>$txtemail,
                           ':tipo_sangre'=>$txttiposangre,
                           ':id_lote_inscripcionfk'=>$txtlote,
                           ':indicador_foto'=>$txtindicador,
                           ':codigo_foto'=>$txtcodigofoto,
                           ':obscarnetfk'=>$txtnovedad,
                           ':chip_carnet'=>$txtchip);
      $respuesta =$this->query($update, $parametros);
      return $respuesta;

        }
 //fin  función






//función sql para generar la ficha de impresion de inscripcion de estudiante
public function GenerarInformacionEstudiante($idestudiante){
  $sql ="SELECT id_inscripcion, 
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
  id_ciudadfk,
  nombre_ciudad,
  id_colegiofk,
  nombre_colegio,
  id_sede_inscripcionfk,
  nombre_sede,
  id_periodo_inscripcionfk,
  periodo,
  id_programa_inscripcionfk,
  nombre_programa,
  valor_programa,
  valor_pension,
  valor_seguro,
  id_horario_inscripcionfk,
  nombre_horario,
  dia_horario,
  id_convenio_inscripcionfk,
  descripcion_convenio,
  des_convenio,
  valor_descuento_convenio,
  valor_pension_convenio,
  id_medio_inscripcionfk,
  origen_medio,
  fecha_inscripcion,
  usuario_registra_inscripcion,
  estado_inscripcion,
  nivel_academicofk,
  nombre_nivel,
  graduado,
  ultimo_grado_aprobadofk,
  nombre_grado,
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
  nombre_eps,
  nombre_acudiente,
  tipo_identidad_acudiente,
  numero_documento_acudiente,
  fecha_nacimiento_acudiente,
  lugar_nacimiento,
  genero_acudiente,
  celular_acudiente,
  parentesco,     
  foto_estudiante,  
  estado_registro AS valor_registro,
  CASE  
      WHEN estado_registro='1' THEN 'INSCRITO' 
      WHEN estado_registro='2' THEN 'MATRICULADO'                                    
      END AS estado_registro,  
  categoria_carnet as valor_categoria,
  CASE  
      WHEN categoria_carnet='1' THEN 'ESTUDIANTE' 
      WHEN categoria_carnet='2' THEN 'FUNCIONARIO'                                    
      WHEN categoria_carnet='3' THEN 'PRACTICANTE'                                    
      END AS categoria_carnet  
  FROM inscripcion i
  INNER JOIN tipo_identidad          ti    ON i.tipo_identificacionfk=ti.id_tipo
  INNER JOIN sede                    s     ON i.id_sede_inscripcionfk=s.id_sede
  INNER JOIN periodo                 p     ON i.id_periodo_inscripcionfk=p.id_periodo
  INNER JOIN programa                pt    ON i.id_programa_inscripcionfk=pt.id_programa
  INNER JOIN horario                 h     ON i.id_horario_inscripcionfk=h.id_horario
  INNER JOIN convenio                c     ON i.id_convenio_inscripcionfk=c.id_convenio
  INNER JOIN medio_entrada           m     ON i.id_medio_inscripcionfk=m.id_medio
  INNER JOIN colegio                col    ON i.id_colegiofk=col.id_colegio
  INNER JOIN eps                     e     ON i.id_epsfk=e.id_eps
  INNER JOIN nivelacademico          na    ON i.nivel_academicofk=na.id_nivel 
  INNER JOIN grado_aprobado          ga    ON i.ultimo_grado_aprobadofk=ga.id_grado
  INNER JOIN ciudad                  cc    ON I.id_ciudadfk=cc.id_ciudad
             WHERE id_inscripcion = :id_inscripcion";
$parametros = array(':id_inscripcion' => $idestudiante);
$respuesta = $this->row($sql,$parametros);
return $respuesta;        
}



//funcion para cargar las sedes
public function CargarSedes(){
   $select = "SELECT id_sede as cod, nombre_sede as nombre FROM sede WHERE estado_sede = 'on'";
   $resultado = $this->table($select);
   return $resultado;
   
 }
 //fin funcion



//funcion para cargar los programas por sedes
public function CargarProgramas($sedes, $tipo, $tipo_sede){
    $select = "SELECT id_programa as cod,
                      nombre_programa as nombre,
                      tipo_control FROM programa p
                      INNER JOIN sede s ON s.id_sede=p.id_sedefk
     WHERE p.id_sedefk=:id_sedefk AND p.estado_programa=:estado AND p.tipo_control=:tipo AND s.tipo_sede=:t_sede ORDER BY nombre_programa ASC";
     $parametros = array(':id_sedefk'=>$sedes, 
                         ':tipo'=>$tipo,
                         ':t_sede'=>$tipo_sede,
                         ':estado'=>'on');
    $resultado = $this->table($select, $parametros);
    return $resultado;
    
  }
//fin funcion


//funcion para cargar todos los programas sin parametros
public function CargarTodosProgramas(){
    $select = "SELECT id_programa as cod, nombre_programa as nombre FROM programa WHERE estado_programa = 'on' ORDER BY nombre_programa  ASC";
    $resultado = $this->table($select);
    return $resultado;
    
}
//fin funcion



//funcion buscar estudiantes inscritos para el envio del email
 public function BuscarInfoEstudiantes($txtRegistroIdentificacion){
    $select = "SELECT identificacion,
                      tipo_identificacionfk,
                      nombre_identidad,
                      nombre_estudiante,
                      apellido_estudiante,
                      celular_estudiante,
                      email_estudiante,
                      fecha_inscripcion,
                      estado_inscripcion,
                      usuario_registra_inscripcion,
                      tipo_carnet,
                      lugar_reclamo
                      FROM inscripcion i 
                      INNER JOIN tipo_identidad t  ON i.tipo_identificacionfk=t.id_tipo
                      WHERE identificacion = :identificacion
                      order by id_inscripcion desc";
                      $parametros = array(':identificacion' =>$txtRegistroIdentificacion);
                      $resultado = $this->row($select,$parametros);
                      return $resultado;
 }



//funcion validar inscripcion 
 public function ValidarRegistroCarnet($identificacion){
 $select = "SELECT trim(identificacion) as identificacion
                        FROM inscripcion WHERE identificacion = :identificacion";
   $parametros = array(':identificacion'=>$identificacion);
   $resultado = $this->row($select,$parametros);
   return $resultado;
 }
//fin funcion  

public function ConsultaCarnet($lote){
  $select="SELECT id_inscripcion,
                  identificacion,
                  tipo_identificacionfk,
                  nombre_identidad,
                  nombre_estudiante,
                  apellido_estudiante,
                  celular_estudiante,
                  email_estudiante,
                  tipo_sangre,
                  id_sede_inscripcionfk,
                  nombre_sede,
                  id_programa_inscripcionfk,
                  nombre_programa,
                  id_lote_inscripcionfk,
                  codigo,
                  etapa,
                  estado_inscripcion AS valor_registro,
                  CASE  
                    WHEN estado_inscripcion='1' THEN 'EN PROCESO' 
                    WHEN estado_inscripcion='2' THEN 'REALIZADO'
                    WHEN estado_inscripcion='3' THEN 'ENTREGADO'   
                    WHEN estado_inscripcion='4' THEN 'CORRECCION'                               
                  END AS estado_inscripcion,
                  tipo_carnet AS valor_carnet,
                  CASE  
                    WHEN tipo_carnet='1' THEN 'NUEVO' 
                    WHEN tipo_carnet='2' THEN 'RENOVACION' 
                    END AS tipo_carnet,
                    fecha_inscripcion,
                    usuario_registra_inscripcion,
                    fecha_entrega_carnet,
                    observacion_correccion,
                    codigo_foto,
                    indicador_foto,
                    obscarnetfk,
                    observacion_novedad,
                    usuario_entrega_carnet,
                    chip_carnet
                    FROM inscripcion i
                        INNER JOIN tipo_identidad t  		          	ON i.tipo_identificacionfk=t.id_tipo
                        INNER JOIN sede           s  		          	ON i.id_sede_inscripcionfk=s.id_sede
                        INNER JOIN programa       p  		          	ON i.id_programa_inscripcionfk=p.id_programa
                        INNER JOIN lotecarnet     l  		          	ON i.id_lote_inscripcionfk=l.id_lote
                        INNER JOIN obscarnet     ob 		          	ON i.obscarnetfk=ob.id_obs
                        WHERE (estado_inscripcion='1' OR estado_inscripcion='2' OR estado_inscripcion='4' OR estado_inscripcion='3') AND (id_lote_inscripcionfk=:lote)
                        ORDER BY fecha_inscripcion ASC";
   $parametros = array(':lote'=>$lote);
   $resultado = $this->table($select,$parametros);
   return $resultado;                     
}


//funcion listra todos los carnet

public function ListarTodos(){
  $select="SELECT id_inscripcion,
                  identificacion,
                  tipo_identificacionfk,
                  nombre_identidad,
                  nombre_estudiante,
                  apellido_estudiante,
                  celular_estudiante,
                  email_estudiante,
                  tipo_sangre,
                  id_sede_inscripcionfk,
                  nombre_sede,
                  id_programa_inscripcionfk,
                  nombre_programa,
                  id_lote_inscripcionfk,
                  codigo,
                  etapa,
                  estado_inscripcion AS valor_registro,
                  CASE  
                    WHEN estado_inscripcion='1' THEN 'EN PROCESO' 
                    WHEN estado_inscripcion='2' THEN 'REALIZADO'
                    WHEN estado_inscripcion='3' THEN 'ENTREGADO'   
                    WHEN estado_inscripcion='4' THEN 'CORRECCION'                               
                  END AS estado_inscripcion,
                  tipo_carnet AS valor_carnet,
                  CASE  
                    WHEN tipo_carnet='1' THEN 'NUEVO' 
                    WHEN tipo_carnet='2' THEN 'RENOVACION' 
                    END AS tipo_carnet,
                    fecha_inscripcion,
                    usuario_registra_inscripcion,
                    fecha_entrega_carnet,
                    observacion_correccion,
                    codigo_foto,
                    indicador_foto,
                    obscarnetfk,
                    observacion_novedad,
                    usuario_entrega_carnet,
                    chip_carnet
                    FROM inscripcion i
                        INNER JOIN tipo_identidad t  		          	ON i.tipo_identificacionfk=t.id_tipo
                        INNER JOIN sede           s  		          	ON i.id_sede_inscripcionfk=s.id_sede
                        INNER JOIN programa       p  		          	ON i.id_programa_inscripcionfk=p.id_programa
                        INNER JOIN lotecarnet     l  		          	ON i.id_lote_inscripcionfk=l.id_lote
                        INNER JOIN obscarnet     ob 		          	ON i.obscarnetfk=ob.id_obs
                        WHERE (estado_inscripcion='1' OR estado_inscripcion='2' OR estado_inscripcion='4' OR estado_inscripcion='3') 
                        ORDER BY fecha_inscripcion ASC";   
   $resultado = $this->table($select);
   return $resultado;                     
}

//funcion para validar la entrada del email del estudiante
public function ValidarEmailEstudiante($Remail){
  $select="SELECT email FROM usuario u, inscripcion i 
           WHERE u.email=:email_estudiante OR i.email_estudiante=:email_estudiante 
           GROUP BY 1 OR 2 ";
  $parametros = array(':email_estudiante'=>$Remail);
  $resultado = $this->row($select, $parametros);
  return $resultado;
}
//fin funcion

//funcion para la toma de la foto en la inscripcion
public function GuardarFoto($Fid_inscripcion, $Foto){
  $sql ="UPDATE inscripcion SET foto_estudiante=:foto_estudiante WHERE id_inscripcion=:id_inscripcion";
  $parametros =array(':id_inscripcion'=>$Fid_inscripcion,
                      ':foto_estudiante'=>$Foto);
  $respuesta =$this->query($sql, $parametros);
  return $respuesta;
}




//funcion para el envio de email de documentos pendientes en la matricula
public function EnviarEmailDocumentosFaltantes($txtidmatricula){
$sql="SELECT id_matricula,
         id_matricula_inscripcionfk,
         folio,
         nombre_estudiante,
         apellido_estudiante,
         email_estudiante,
         id_sede_inscripcionfk,
         nombre_sede,
         id_programa_inscripcionfk,
         nombre_programa,    
         id_horario_inscripcionfk,
         nombre_horario,
         dia_horario,   
         doc_diploma,
         doc_cedula,
         doc_ti,
         doc_acta,
         doc_certificado,
         usuario_registra_matricula
         FROM matricula m 
         INNER JOIN inscripcion i ON m.id_matricula_inscripcionfk=i.id_inscripcion
         INNER JOIN sede        s ON i.id_sede_inscripcionfk=s.id_sede
         INNER JOIN programa    p ON i.id_programa_inscripcionfk=p.id_programa
         INNER JOIN horario     h ON i.id_horario_inscripcionfk=h.id_horario
         WHERE id_matricula=:id_matricula";
         $parametros =array(':id_matricula'=>$txtidmatricula);
         $respuesta =$this->row($sql, $parametros);
        return $respuesta;
}


//funcion para envio de email a los estudiantes q
public function EnviarEmailRecibidoLote($txtid){
                $sql="SELECT id_inscripcion,
                            identificacion,
                            tipo_identificacionfk,
                            nombre_identidad,
                            nombre_estudiante,
                            apellido_estudiante,
                            tipo_sangre,
                            id_programa_inscripcionfk,
                            nombre_programa,
                            id_sede_inscripcionfk,
                            nombre_sede,
                            email_estudiante,
                            id_lote_inscripcionfk,
                            lugar_reclamo 
                            FROM inscripcion i 
                            INNER JOIN programa p         ON i.id_programa_inscripcionfk=p.id_programa
                            INNER JOIN sede     s         ON i.id_sede_inscripcionfk=s.id_sede
                            INNER JOIN tipo_identidad t   ON i.tipo_identificacionfk=t.id_tipo
                            WHERE id_inscripcion=:id
                            order by id_inscripcion desc";  
             $parametros =array(':id'=>$txtid);
             $respuesta =$this->row($sql, $parametros);
             return $respuesta;
  }


public function CambiarEmail($txtidinscripcion,$txtemail){
$sql="UPDATE inscripcion SET id_inscripcion = :id,
                         email_estudiante=:email
                         WHERE id_inscripcion=:id";
$parametros =array(':id'=>$txtidinscripcion,
               ':email'=>$txtemail);
$respuesta =$this->query($sql, $parametros);
return $respuesta;
}


//funcion para el cambio de identidad
public function CambiarIdentidad($txtidinscripcion,$txtidentidad, $txttipo, $txtpass){
$sql="UPDATE inscripcion SET id_inscripcion = :id,
                         identificacion=:identidad,
                         tipo_identificacionfk=:tipo,
                         password_estudiante=:pass
                         WHERE id_inscripcion=:id";
$parametros =array(':id'=>$txtidinscripcion,
               ':identidad'=>$txtidentidad,
               ':tipo'=>$txttipo,
               ':pass'=>$txtpass);
$respuesta =$this->query($sql, $parametros);
return $respuesta;
}

//funcion para validar la identidad
public function ValidarIdentidad($identificacion){
$select="SELECT identificacion from inscripcion WHERE identificacion=:identidad";
$parametros =array(':identidad'=>$identificacion);
$respuesta =$this->row($select, $parametros);
return $respuesta;
}
//funcion para cargar el ultimo grado aprobado para edicion


  //funcion cargar sede
  function CargarSedeInscripcion(){
    $select="SELECT id_sede as cod, 
                    nombre_sede as nombre,
                    estado_sede FROM sede WHERE estado_sede='on' AND tipo_sede=1
                    ORDER BY id_sede ASC";
     $respuesta =$this->table($select);
     return $respuesta;  
  }
  //funcion cargar sede
  function CargarEmpresaInscripcion(){
    $select="SELECT id_sede as cod, 
                    nombre_sede as nombre,
                    estado_sede FROM sede WHERE estado_sede='on' AND tipo_sede=2
                    ORDER BY id_sede ASC";
     $respuesta =$this->table($select);
     return $respuesta;  
  }

  //funcion para la carga de lotes
  function CargarLotes($usuario, $rol = null){
    $where = "WHERE id_usuariofk=:usuario AND estado_lote='on'";
    $parametros = array(':usuario'=>$usuario);

    if($rol == 1){
      $where = "WHERE estado_lote='on'";
      $parametros = array();
    }

    $sql="SELECT id_lote AS cod,
                    CONCAT( etapa,'-',codigo) AS nombre,
                    estado_lote,
                    id_usuariofk
                FROM lotecarnet 
                $where
                ORDER BY id_lote ASC";
     $respuesta =$this->table($sql,$parametros);
     return $respuesta;  
  }


  
  //funcion para la carga de lotes
  function CargarMisLotes($usuario, $rol = null){
    $where = "WHERE id_usuariofk=:usuario";
    $parametros = array(':usuario'=>$usuario);

    if($rol == 1){
      $where = "";
      $parametros = array();
    }

    $sql="SELECT id_lote AS cod,
                 CONCAT( etapa,'-',codigo) AS nombre,
                 estado_lote,
                 id_usuariofk
         FROM lotecarnet 
         $where ORDER BY id_lote ASC";
     $respuesta =$this->table($sql,$parametros);
     return $respuesta;  
  }
  
  


  //funcion para la carga de todos los lotes de etapa practica
  function CargarLotesEtapaPractica($usuario, $rol = null){
    $where = "WHERE id_usuariofk=:usuario AND etapa='EP'";
    $parametros = array(':usuario'=>$usuario);

    if($rol == 1){
      $where = "WHERE etapa='EP'";
      $parametros = array();
    }

    $sql="SELECT id_lote AS cod,
                 CONCAT( etapa,'-',codigo) AS nombre,
                 estado_lote,
                 etapa,
                 id_usuariofk
            FROM lotecarnet
            $where 
            ORDER BY id_lote ASC";
     $respuesta =$this->table($sql,$parametros);
     return $respuesta;  
  }

  //funcion para la carga de todos los lotes de etapa practica
  function CargarLotesEtapaLectiva($usuario, $rol = null){
    $where = "WHERE id_usuariofk=:usuario AND etapa='EL'";
    $parametros = array(':usuario'=>$usuario);

    if($rol == 1){
      $where = "WHERE etapa='EL'";
      $parametros = array();
    }

    $sql="SELECT id_lote AS cod,
                 CONCAT( etapa,'-',codigo) AS nombre,
                 estado_lote,
                 etapa,
                 id_usuariofk
            FROM lotecarnet
            $where 
            ORDER BY id_lote ASC";
     $respuesta =$this->table($sql,$parametros);
     return $respuesta;  
  }


  //funcion para la carga de novedad de registro de carnet
  public function CargarObservacion(){
    $sql="SELECT id_obs as cod,
                 observacion_novedad as nombre
                 FROM obscarnet";
    $respuesta =$this->table($sql);
    return $respuesta;
  }

    public function ActivarChip($id_inscripcion, $rfid) {
        $sql = "UPDATE inscripcion SET chip_carnet = 'SI', uid_rfid = :rfid WHERE id_inscripcion = :id";
        $parametros = array(':id' => $id_inscripcion, ':rfid' => $rfid);
        $respuesta = $this->query($sql, $parametros);
        return $respuesta;
    }

public function EntregaCarnet($txtidinscripcion){
  $sql="UPDATE inscripcion SET estado_inscripcion=:estado,fecha_inscripcion=:fecha_inscripcion WHERE id_inscripcion=:id";
  $parametros =array(':id'=>$txtidinscripcion, ':estado'=>'3', ':fecha_inscripcion'=> $this->datetimeNow());
  $respuesta =$this->query($sql, $parametros);
  return $respuesta;
  }

//funcion para guardar cambios en el carnet
public function RealizarCambios($txtidinscripcion, $txtobservacion){
  $sql="UPDATE inscripcion SET estado_inscripcion = :estado,
                               observacion_correccion=:observacion,
                               fecha_inscripcion=NULL
                              WHERE id_inscripcion=:id";
  $parametros =array(':id'=>$txtidinscripcion,
                    ':observacion'=>$txtobservacion,
                    ':estado'=>'4');
  $respuesta =$this->query($sql, $parametros);
  return $respuesta;
  }

  //funcion para el cambio de lote  
  public function CambiarLote($txtclidinscripcion,$txtlote){
    $sql="UPDATE inscripcion SET id_lote_inscripcionfk = :lote
                                 WHERE id_inscripcion=:id";
    $parametros =array(':id'=>$txtclidinscripcion,
                       ':lote'=>$txtlote);
    $respuesta =$this->query($sql, $parametros);
    return $respuesta;
    }

//funcion para cambiar programas
 public function CambiarPrograma($txtPidinscripcion,$txtpsede, $txtpprograma){
    $sql="UPDATE inscripcion SET id_sede_inscripcionfk=:sede,
                                id_programa_inscripcionfk=:programa
                                 WHERE id_inscripcion=:id";
    $parametros =array(':sede'=>$txtpsede,
                       ':id'=>$txtPidinscripcion,
                       ':programa'=>$txtpprograma);
    $respuesta =$this->query($sql, $parametros);
    return $respuesta;
    }

//funcion para el cambio de carnet como recibido
public function CambiarRecibido($txtid){  
  $sql="UPDATE inscripcion SET estado_inscripcion=:estado,fecha_inscripcion=NULL WHERE id_inscripcion=:id";
  $parametros =array(':estado'=>'2', ':id'=>$txtid);
  $respuesta =$this->query($sql, $parametros);
return $respuesta;
}

//funcion para el como entregado
public function CambiarEntregado($txtid){  
  $sql="UPDATE inscripcion SET estado_inscripcion=:estado,fecha_inscripcion=NULL WHERE id_inscripcion=:id";
  $parametros =array(':estado'=>'4',
                     ':id'=>$txtid);
  $respuesta =$this->query($sql, $parametros);
return $respuesta;
}

}
//fin funcion 