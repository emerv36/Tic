<?php
 require_once('../Config/PDOconn.php');
 class Usuarios extends db{

    function VerUsuarios(){
        $sql="SELECT codigo_usu,
                     usuario,
                     email,
                     codperfil_fk,
                     fechacreado_usu,
                     estado,
                     nombres_usuario,
                     fecha_nacimiento_usuario,
                     telefono,
                     dir_usuario,
                     nombre_perfil
        FROM usuario us INNER JOIN usuperfil per ON us.codperfil_fk=per.codigo_perfil ORDER BY codigo_usu ASC";
        $respuesta= $this->table($sql);
        return $respuesta;
    }

    function EditarEstadoUsuario($codigo, $estado){
      $sql="UPDATE usuario SET estado = :estado WHERE codigo_usu = :codigo_usu";
      $parametros = array(':codigo_usu' => $codigo, ':estado'=>$estado);
      $resultado = $this->query($sql, $parametros);
      return $resultado;

    }

    function ValidarCorreo($email){
      $sql="SELECT email FROM 
            usuario u, inscripcion i , docente d 
            WHERE u.email=:email OR i.email_estudiante=:email OR d.email_docente=:email
            GROUP BY 1 OR 2";
            $parametros = array(':email'=>$email);
            $respuesta = $this->row($sql, $parametros);
      return $respuesta;
    }

    



    function ValidarIdentificacion($id){
      $sql = "SELECT codigo_usu FROM usuario WHERE usuario = :usuario";
      $parametros = array(':usuario'=>$id);
      $respuesta = $this->row($sql, $parametros);
      return $respuesta;
    }



    function EditarUsuario($codigo, $nombre, $telefono, $direccion, $perfil, $fecha, $estado){
      $sql ="UPDATE usuario SET codigo_usu=:codigo_usu,
                                nombres_usuario = :nombres_usuario,
                                telefono = :telefono,
                                dir_usuario = :dir_usuario,
                                codperfil_fk=:codperfil_fk,
                                fecha_nacimiento_usuario = :fecha_nacimiento_usuario,
                                estado=:estado 
                                WHERE codigo_usu = :codigo_usu";
      $parametros = array(':codigo_usu'=>$codigo,
                          ':nombres_usuario'=>$nombre,
                          ':telefono'=>$telefono,
                          ':dir_usuario'=>$direccion,
                          ':codperfil_fk'=>$perfil,
                          ':fecha_nacimiento_usuario'=>$fecha,
                          ':estado'=>$estado);
      $respuesta = $this->query($sql, $parametros);
      return $respuesta;
    }

    function InsertarUsuarios($id, $pass, $email, $perfil, $nom, $dir, $tel, $estado, $fecha, $token){
      $sql= "INSERT INTO usuario( usuario,
                                  password, 
                                  email, 
                                  codperfil_fk, 
                                  nombres_usuario, 
                                  dir_usuario,
                                  telefono, 
                                  estado,
                                  fecha_nacimiento_usuario,
                                  codvalidacion,
                                  fechacreado_usu)
                VALUES(:usuario,
                      :password, 
                      :email,
                      :codperfil_fk,
                      :nombres_usuario,
                      :dir_usuario, 
                      :telefono,
                      :estado,
                      :fecha_nacimiento_usuario, 
                      :codvalidacion,
                      :fechacreado_usu)";
              $parametros = array(':usuario'=>$id,
                                  ':password'=>$pass,
                                  ':email'=>$email,
                                  ':codperfil_fk'=>$perfil,
                                  ':nombres_usuario'=>$nom,
                                  ':telefono'=>$tel,
                                  ':dir_usuario'=>$dir,
                                  ':estado'=>$estado,
                                  ':fecha_nacimiento_usuario'=>$fecha,
                                  ':codvalidacion'=>$token,
                                  ':fechacreado_usu'=> $this->datetimeNow() );
       $respuesta = $this->query($sql, $parametros);
        return $respuesta;

    }

    function cargarperfiles(){
      $sql="SELECT codigo_perfil as cod, upper(nombre_perfil) as nombre FROM usuperfil WHERE estado_perfil = 'on' ORDER BY nombre_perfil ASC";
      $respuesta = $this->table($sql);
      return $respuesta;
    }

    function generatecod() {
      $randomString = str_shuffle("ghijklx6789".uniqid());
      return $randomString;
    } 

    public function BuscarUsuario ()
  {
    $sql = "SELECT * FROM usuario WHERE codigo_usu = :codusuario";
    $params = array(':codusuario' => $_SESSION['IN_codigo_usuCA']);
    $res = $this->row($sql, $params);
    return $res;
  }

  public function EditarContraseña ($contraseña, $codusuario)
  {
    $sql = "UPDATE usuario SET password = :password WHERE codigo_usu = :codusuario";
    $params = array(':password'=> $contraseña, ':codusuario'=>$codusuario);
    $res = $this->query($sql, $params);
    return $res;
  }

  public function uploadFotoPerfil ($urlFoto, $codusuario)
  {
    $sql = "UPDATE usuario SET img_usuario = :urlfoto WHERE codigo_usu= :userid"; 
    $params = array(':urlfoto' => $urlFoto, ':userid' => $codusuario);
    $res = $this->query($sql, $params);
    return $res;
  }

  
  public function VerificandoRol ($Rol)
  {
    $sql="SELECT nombre_perfil FROM usuperfil WHERE codigo_perfil = :codigo_perfil";
    $params = array(':codigo_perfil'=>$Rol);
    $res=$this->row($sql, $params);
    return $res;
  }

  public function VerificandoNitEmpresa ($Nit)
  {
    $sql="SELECT * FROM empresa WHERE nit_empresa = :nit_empresa";
    $params = array(':nit_empresa'=>$Nit);
    $res=$this->row($sql, $params);
    return $res;
  }

  public function CambiarEstado ($Nit)
  {
    $sql="UPDATE empresa SET estado = :estado WHERE nit_empresa = :nit_empresa";
    $params=array(':estado'=>1,':nit_empresa'=>$Nit);
    $respuesta =$this->query($sql, $params);
    return $respuesta;

}

 
 }