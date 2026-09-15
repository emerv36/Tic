<?php 
require_once('../Config/PDOconn.php');
class Perfiles extends db
{
  public function LoadPerfiles ()
  {
    $sql = "SELECT p.codigo_perfil,
                   p.nombre_perfil,
                   p.estado_perfil,
                   r.codigo_rol,
                   r.nombre_rol,
                   r.menus
              FROM usuperfil p
              INNER JOIN roles r ON r.codigo_rol = p.codrol_fk  ORDER BY p.codigo_perfil ASC";
    $res = $this->table($sql);
    return $res;
  }

  public function LoadRol ()
  {
    $sql = "SELECT codigo_rol as cod, nombre_rol as nombre, menus, estado_rol FROM roles WHERE estado_rol = 'on'";
    $res = $this->table($sql);
    return $res;
  }

  public function EditarPerfil ($estado, $codperfil)
  {
    $sql = "UPDATE usuperfil SET  estado_perfil = :estado WHERE codigo_perfil = :codperfil";
    $params = array(':estado' => $estado, ':codperfil'=> $codperfil);
    $res = $this->query($sql, $params);
    return $res;
  }

  
  /// agregar perfil
  public function InsertPerfil ($rol, $nombre, $estado)
  {
    $sql = "INSERT INTO usuperfil (nombre_perfil, codrol_fk, estado_perfil) VALUES (:nombre_perfil, :rol, :estado)";
    $params = array(':nombre_perfil' => $nombre, ':rol' => $rol, ':estado' => $estado);
    $res = $this->query($sql, $params);
    return $res;
  }

// validar nombre del perfil
public function ValidarPerfil($nombre_perfil)
  {
    $sql = "SELECT nombre_perfil FROM usuperfil 
            WHERE nombre_perfil=:nombre_perfil  ORDER BY nombre_perfil ASC";
            $params = array(':nombre_perfil' => $nombre_perfil);
            $res = $this->row($sql,$params);
            return $res;
  }


//cargar perfil de docente
public function CargarPerfilDocente(){
 $sql="SELECT codigo_perfil, nombre_perfil FROM usuperfil
 WHERE nombre_perfil IN('docente','Docente','DOCENTE','DOCENTES') OR nombre_perfil='DOCENTE' OR nombre_perfil='DOCENTES'";
 $res = $this->row($sql);
 return $res;
}

//cargar perfil estudiantes

public function CargarPerfilEstudiante(){
 $sql="SELECT codigo_perfil, nombre_perfil FROM usuperfil
 WHERE nombre_perfil IN('estudiante','Estudiante','estudiantes','Estudiantes', 'ESTUDIANTE') OR nombre_perfil='ESTUDIANTE' OR nombre_perfil='ESTUDIANTE'";
 $res = $this->row($sql);
 return $res;
}

  
}