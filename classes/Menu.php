<?php 
require_once '../developer/PDOConn.php';

class Menu extends db
{


  public function rolesMenu($codrol)
  {
    $sql="SELECT menus FROM usu_roles WHERE codigo_rol=:codrol AND estado_rol=:estado";
    $params = array(':codrol'=>$codrol, 'estado'=>'on');
    $res = $this->table($sql,$params);
    return $res;
  }

  public function listarMenuxNivelUno()
  {
    $sql="SELECT codigo_menu, imagen, nombre_menu, nivel, orden, codsuperior, link, estado, target FROM usu_menu WHERE estado = :estado AND nivel = :nivel ORDER BY orden ASC";
    $params = array(':nivel'=>1, 'estado'=>'on');
    $res = $this->table($sql,$params);
    return $res;
  }

  public function listarSubmenuxCodSuperior($codigo_menu)
  {
    $sql="SELECT codigo_menu, nombre_menu, link FROM usu_menu WHERE codsuperior = :codsuperior ORDER BY orden ASC";
    $params = array(':codsuperior'=>$codigo_menu);
    $res = $this->table($sql,$params);
    return $res;
  }

  public function loadMenu()
  {
    $sql="SELECT codigo_menu, imagen, nombre_menu, nivel, orden, codsuperior, link, estado, target FROM usu_menu WHERE estado = :estado ORDER BY codigo_menu ASC";
    $params = array('estado'=>'on');
    $res = $this->table($sql,$params);
    return $res;
  }

  public function loadMenuImagen($codsuperior)
  {
    $sql="SELECT nombre_menu, imagen FROM usu_menu WHERE codigo_menu = :codmenu";
    $params = array('codmenu'=>$codsuperior);
    $res = $this->row($sql,$params);
    return $res;
  }

  public function MenuOrdenMax($nivel_menu)
  {
    $sql="SELECT max(orden) as \"nivelmax\" FROM usu_menu WHERE nivel = :nivel";
    $params = array(':nivel' =>  $nivel_menu);
    $res = $this->row($sql,$params);
    return $res;
  }

  public function loadPadresMenus()
  {
    $sql="SELECT codigo_menu, nombre_menu, nivel, orden, codsuperior, link, imagen, target, estado FROM usu_menu WHERE estado = :estado AND nivel = :nivel ORDER BY orden ASC";
    $params = array(':nivel'=>'1', ':estado' => 'on');
    $res = $this->table($sql,$params);
    return $res;
  }

  public function MenuOrdenMaxNivel2($nivel_menu, $codsuperior)
  {
    $sql="SELECT max(orden) as \"nivelmax\" FROM usu_menu WHERE nivel = :nivel AND codsuperior=:codsuperior";
    $params = array(':nivel' =>  $nivel_menu, 'codsuperior'=>$codsuperior);
    $res = $this->row($sql,$params);
    return $res;
  }

  public function insertarMenu($nombre_menu, $nivel, $orden, $codsuperior, $link, $imagen, $target, $estado)
  {
    $sql="INSERT INTO usu_menu (nombre_menu, nivel, orden, codsuperior, link, imagen, target, estado) VALUES (:nombre_menu, :nivel, :orden, :codsuperior, :link, :imagen, :target, :estado)";
    $params = array(
                    ':nombre_menu' => $nombre_menu,
                    ':nivel' => $nivel, 
                    ':orden' => $orden, 
                    ':codsuperior' => $codsuperior, 
                    ':link' => $link, 
                    ':imagen' => $imagen, 
                    ':target' => $target, 
                    ':estado' => $estado
                  );

    $this->query($sql,$params);
  }

  public function buscarMenu($nombre_menu, $where)
  {
    $sql="SELECT codigo_menu, imagen, nombre_menu, nivel, orden, codsuperior, link, estado, target FROM usu_menu WHERE nombre_menu LIKE '%".$nombre_menu."%' ".$where." ORDER BY codigo_menu ASC";
    $res = $this->table($sql);
    return $res;
  }

  public function editItemMenu($icono_menu, $nombre_menu, $link_menu, $estado_menu, $codmenu)
  {
    $sql="UPDATE usu_menu SET imagen = :imagen, nombre_menu = :nombre_menu, link = :link_menu, estado = :estado WHERE codigo_menu = :codmenu";
     $params = array(':imagen' => $icono_menu, ':nombre_menu' => $nombre_menu, ':link_menu' => $link_menu, ':estado' => $estado_menu, ':codmenu'=>$codmenu);
    $this->query($sql,$params);
  }

}