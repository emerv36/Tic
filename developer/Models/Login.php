<?php
//require($_SERVER['DOCUMENT_ROOT'].'/'.'developer/Config/PDOConn.php');
require_once('../Config/PDOconn.php');
class Login extends db
{

//funcion login de docentes
public function SessionDocente($usuario){
  $select="SELECT id_docente,
                  nombre_docente,
                  apellido_docente,
                  cedula_docente,
                  estado_docente,
                  email_docente,
                  id_perfilfk,
                  nombre_perfil,
                  codrol_fk,
                  nombre_rol,
                  password_docente,
                  foto_docente
 FROM docente d 
 INNER JOIN usuperfil p ON d.id_perfilfk=p.codigo_perfil
 INNER JOIN roles    r ON p.codrol_fk=r.codigo_rol 
 WHERE estado_docente='on' AND email_docente=:email_docente";
$params = array(':email_docente' => $usuario);
$res = $this->row($select,$params);
return $res;
}
//fin funcion

public function SessionEstudiante($usuario){
$select="SELECT identificacion,
                email_estudiante,
                id_inscripcion,
                nombre_estudiante,
                apellido_estudiante,
                estado_inscripcion,
                password_estudiante,
                id_inscripcion_perfilfk,
                UPPER(nombre_perfil) AS nombre_perfil,
                codrol_fk,
                nombre_rol
                FROM inscripcion i
                INNER JOIN usuperfil     p  ON i.id_inscripcion_perfilfk =p.codigo_perfil
                INNER JOIN roles         r  ON p.codrol_fk=r.codigo_rol
                WHERE estado_inscripcion='on' AND email_estudiante=:email_estudiante";
$params = array(':email_estudiante' => $usuario);
$res = $this->row($select,$params);  
return $res;    
}//fin funcion


///login para tabla de usuarios
public function Iniciarsesion($usuario){
		$select = "SELECT us.codigo_usu,
                      us.usuario,
                      us.password,
                      us.estado,
                      us.email,
                      us.estado,
                      pf.codigo_perfil,
                      pf.nombre_perfil,
                      ro.codigo_rol,
                      ro.nombre_rol, 
                      us.nombres_usuario,
                      us.img_usuario,
                      us.email_confirmado,
                     us.fecha_nacimiento_usuario
     FROM usuario us
                  INNER JOIN usuperfil pf ON pf.codigo_perfil = us.codperfil_fk 
                  INNER JOIN roles ro ON ro.codigo_rol = pf.codrol_fk
                  WHERE us.email = :usuario OR us.usuario = :usuario";
      	$params = array(':usuario' => $usuario);
      	$res = $this->row($select,$params);
		return $res;
	}

	
  public function urlServer ()
  {
    $res = $this->urlservidor();
    return $res;
  }
 

 
  public function validarCorreoRecuperacion($email)
  {
    $sql =  "SELECT email FROM usuario WHERE email = :email"; 
    $params = array(':email' => $email);
    $res = $this->row($sql, $params);
    return $res;
  }

  public function insertarToken($email, $token)
  {
    $sql =  "UPDATE usuario SET codvalidacion = :token WHERE email = :email"; 
    $params = array(':email' => $email, ':token' => $token);
    $res = $this->query($sql, $params);
    return $res;
  }

  public function EditarContraseña($contraseña, $codusuario)
  {
    $sql = "UPDATE usuario SET password = :password WHERE codigo_usu = :codusuario";
    $params = array(':password'=> $contraseña, ':codusuario'=>$codusuario);
    $res = $this->query($sql, $params);
    return $res;
  }
  
  public function BuscarCorreo($correo){
    $sql = "SELECT email FROM usuario WHERE email = :email";
    $params = array(':email' => $correo);
    $res = $this->row($sql, $params);
    return $res;
  }

  public function ValidarCorreo ($email){
    $sql = "SELECT email FROM usuario WHERE email = :email";
    $params = array(':email' => $email);
    $res = $this->table($sql, $params);
    return $res;
  }

    public function ValidarIdentificacion ($identificacion)
   {         
     $sql = "SELECT * FROM usuario WHERE usuario = :identificacion";
     $params = array(':identificacion' => $identificacion);
     $res = $this->table($sql, $params);
     return $res;
   }

   function generatecod() {
     $randomString = str_shuffle("ghijklx6789".uniqid());
     return $randomString;
   } 

  //function guardar entrada de usuario
   function auditoria($usuario,$perfil,$plataforma,$ip){
    $insert="INSERT INTO auditoria_usuario(nombre_usuario_auditoria,
                                          nombre_perfil_auditoria,
                                          fecha_hora_entrada,
                                          plataforma,
                                          direccion_ip)
                                VALUES(:nombre_usuario_auditoria,
                                       :nombre_perfil_auditoria,
                                       :fecha_hora_entrada,
                                       :plataforma,
                                       :direccion_ip)";
        $params=array( ':nombre_usuario_auditoria'=>$usuario,
                       ':nombre_perfil_auditoria'=>$perfil,
                       ':fecha_hora_entrada'=>$this->datetimeNow(),
                       ':plataforma'=>$plataforma,
                       ':direccion_ip'=>$ip);
        $res =$this->query($insert, $params);
        return $res;
 }  

 //obtener la ip real del visitante
 function getRealIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];
       
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
   
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

}
//funcion para verificar el carnet
function VerificarCarnet($identidad){
  $sql="SELECT identificacion, 
            nombre_estudiante,
            apellido_estudiante,
            estado_inscripcion AS valor_registro, 
            lugar_reclamo, 
            CASE  
                  WHEN estado_inscripcion='1' THEN 'EN PROCESO' 
                  WHEN estado_inscripcion='2' THEN 'REALIZADO'
                  WHEN estado_inscripcion='3' THEN 'ENTREGADO'   
            END AS estado_inscripcion
            FROM inscripcion
            WHERE identificacion=:identificacion
            ORDER BY id_inscripcion DESC";
  $parametros =array(':identificacion'=>$identidad);
  $respuesta =$this->row($sql,$parametros);
  return $respuesta;
 }
}
?>