<?php   
require_once '../developer/PDOConn.php';

class Perfiles extends db
{

    public function loadPerfiles()
    {
      $sql = "SELECT p.codigo_perfil, p.nombre_perfil, p.estado_perfil, r.codigo_rol, r.nombre_rol, r.menus
              FROM usu_perfiles p
              INNER JOIN usu_roles r ON r.codigo_rol = p.codrol_fk WHERE p.estado_perfil = :estado_perfil";
      $params = array(':estado_perfil'=>'on');
      $res = $this->table($sql,$params);
      return $res;
    } 


    public function loadRol()
    {
      $sql = "SELECT codigo_rol as cod, nombre_rol as nombre, menus, estado_rol FROM usu_roles WHERE estado_rol = 'on'";
      $res = $this->table($sql);
      return $res;
    }


    public function editItemPerfil($nombre_perfil, $estado_perfil, $codperfil)
    {
      $update = "UPDATE usu_perfiles SET nombre_perfil = :nombre_perfil, estado_perfil = :estado WHERE codigo_perfil = :codperfil";   
      $params = array(':nombre_perfil' => $nombre_perfil, ':estado' => $estado_perfil, ':codperfil'=>$codperfil);
      $res = $this->table($update, $params);
      return $res;
    }


    public function insertItemPerfil($nombre_perfil, $rol_perfil, $estado_perfil)
    {

      $insert = "INSERT INTO usu_perfiles (nombre_perfil, codrol_fk, estado_perfil) VALUES (:nombre_perfil, :rol, :estado)";
      $paramsInsert = array(
                            ':nombre_perfil' => $nombre_perfil,
                            ':rol' => $rol_perfil, 
                            ':estado' => $estado_perfil
                            );
      $res = $this->table($insert, $paramsInsert);
      return $res;
    }



    public function loadmodulos()
    {
      $sqlmenu = "SELECT codigo_menu, nombre_menu, nivel,codsuperior FROM usu_menu WHERE link!='#' AND estado=:estado order by codigo_menu asc";
      $params = array(':estado' => 'on');
      $res = $this->table($sqlmenu, $params);
      return $res;  
    }


    public function loadmodulosS($codrol)
    {
      $sqlrolesmenu = "SELECT menus FROM usu_roles WHERE codigo_rol=:codrol AND estado_rol=:estado";
      $params = array(':estado' => 'on',':codrol' => $codrol);
      $res = $this->row($sqlrolesmenu, $params);
      return $res;
    }


    public function updateConfigRolmenu($menus, $codrol)
    {
      $update = "UPDATE usu_roles SET menus = :menus WHERE codigo_rol = :codrol";
      $params = array(':menus' => $menus, ':codrol'=>$codrol);
      $res = $this->row($update, $params);
      return $res;
    }
}