<?php
require_once('../Config/PDOconn.php');
class Restaurarpass extends db {
//función sql para listar las inducciones programadas


// función sql para resturar el pass de los estudiantes
  public function RestaurarpassEstudiante($txtRidinscripcion,
                                          $txtRegistroPerfil,
                                          $txtRegistroIdentidad,
                                          $txtPassword_estudiante){

        $update = "UPDATE inscripcion SET id_inscripcion_perfilfk  =:id_perfil,  
                                          identificacion           =:identificacion,                             
                                          password_estudiante      =:password_estudiante

                                          WHERE id_inscripcion = :id_inscripcion";

                    $params = array(':id_inscripcion'       =>$txtRidinscripcion,
                                    ':identificacion'       =>$txtRegistroIdentidad,
                                    ':id_perfil'            =>$txtRegistroPerfil,                                  
                                    ':password_estudiante'  =>$txtPassword_estudiante);
                    $resultado = $this->query($update, $params);
                    return $resultado;
  }
//fin función


  public function BusquedaUsuarios($txtusuario){
  $sql="SELECT codigo_usu,usuario, email,nombres_usuario,  codperfil_fk, nombre_perfil FROM usuario u
    INNER JOIN usuperfil p ON u.codperfil_fk=p.codigo_perfil
   WHERE nombres_usuario LIKE :valor ORDER BY nombres_usuario ASC";

  $parametros = array(":valor" => '%'.$txtusuario.'%');
   
  $resultado = $this->table($sql, $parametros);
  return $resultado;  
  } 



 //funcion restaur password usuarios
  public function RestaurarpassUsuario($txtRcodigo,$txtRidentidad){

        $update = "UPDATE usuario SET     codigo_usu  =:codigo_usu,  
                                          usuario     =:usuario,
                                          password    =:password

                                          WHERE codigo_usu =:codigo_usu";

                    $params = array(':codigo_usu' =>$txtRcodigo,
                                    ':usuario'    =>$txtRidentidad,
                                    ':password'   =>sha1($txtRidentidad));

                    $resultado = $this->query($update, $params);
                    return $resultado;
  }


///funcion restaurar password docentes
 public function RestaurarpassDocente($txtDcodigo,
                                      $txtDidentidad,
                                      $txtDpassword){

        $update = "UPDATE docente SET     id_docente       =:id_docente,  
                                          cedula_docente   =:cedula_docente,  
                                          password_docente =:password_docente

                                          WHERE id_docente =:id_docente";

                    $params = array(':id_docente'         =>$txtDcodigo,
                                    ':cedula_docente'     =>$txtDidentidad,
                                    ':password_docente'   =>$txtDpassword);
                    $resultado = $this->query($update, $params);
                    return $resultado;
  }

//fin funci



}

 