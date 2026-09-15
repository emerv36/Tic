<?php
require_once('../Config/PDOconn.php');
class Menu extends db
{
	public function CargarMenuDelUsuario() {
		$rol = $_SESSION["IN_nombre_rol"];
		$sql = "SELECT menus FROM roles WHERE nombre_rol = :nombre_rol";
		$params = array(":nombre_rol" => $rol);
		$menus_del_usuario = $this->row($sql, $params);
		$menu_array = explode(",", $menus_del_usuario["menus"]);
		$res_menu = $this->table("SELECT * from menu");
		$menu_final = [];
		foreach ($menu_array as $option) {
			foreach ($res_menu as $menu_global) {
				if ((int)$option == $menu_global["codigo_menu"]) {
					$menu_global["padre"] = array_filter($res_menu, function ($item) use ($menu_global) {
						return $item["codigo_menu"] == $menu_global["codsuperior"];
						
					});
					if ($menu_global["estado"] == "on") {
						array_push($menu_final, $menu_global);
					}
					break;
				}
			}
		}
		return $menu_final;
	}
	public function LoadMenu (){
		$sql = "SELECT codigo_menu, imagen, nombre_menu, nivel, orden, codsuperior, link, estado, target FROM menu ORDER BY codigo_menu ASC";
		// $params = array(':estado' => 'on');
		$res = $this->table($sql);
		return $res;
	}
	public function LoadMenu2($codsuperior){
		$sql = "SELECT nombre_menu, imagen FROM menu WHERE codigo_menu = :codmenu";
		$params = array(':codmenu' => $codsuperior);
		$res = $this->row($sql, $params);
		return $res;
	}
	public function RowRolesMenu($codrol){
		$sql = "SELECT menus FROM roles WHERE codigo_rol=:codrol AND estado_rol=:estado";
		$params = array(':codrol'=>$codrol, ':estado' => 'on');
		$res = $this->table($sql, $params);
		return $res;
	}
	public function Menus(){
		$sql = "SELECT codigo_menu, imagen, nombre_menu, nivel, orden, codsuperior, link, estado, target FROM menu WHERE estado = :estado AND nivel = :nivel ORDER BY orden ASC";
		$params = array(':estado' => 'on', ':nivel' => 1);
		$res = $this->table($sql, $params);
		return $res;
	}
	public function SubMenus($codsuperior){
		$sql = "SELECT codigo_menu, nombre_menu, link FROM menu WHERE codsuperior = :codsuperior ORDER BY orden ASC";
		$params = array(':codsuperior' => $codsuperior);
		$res = $this->table($sql, $params);
		return $res;
	}
	public function NivelMenu ($nivel){
		$sql = "SELECT max(orden) as \"nivelmax\" FROM menu WHERE nivel = :nivel";
		$params = array(':nivel' =>  $nivel);
		$res = $this->row($sql, $params);
		return $res;
	}
	public function NivelMenu2 ($nivel, $codsuperior){
		$sql = "SELECT max(orden) as \"nivelmax\" FROM menu WHERE nivel = :nivel AND codsuperior = :codsuperior";
	    $params = array(':nivel' =>  $nivel, ':codsuperior' => $codsuperior );
		$res = $this->row($sql, $params);
		return $res;
	}
	public function InsertMenu ($nombre, $nivel, $orden, $codsuperior, $link, $imagen, $target, $estado){
		$sql = "INSERT INTO menu (nombre_menu, nivel, orden, codsuperior, link, imagen, target, estado) VALUES (:nombre_menu, :nivel, :orden, :codsuperior, :link, :imagen, :target, :estado)";
		$params = array(':nombre_menu' => $nombre, ':nivel' => $nivel, ':orden' => $orden, ':codsuperior' => $codsuperior, ':link' => $link, ':imagen' => $imagen, ':target' => $target,':estado' => $estado);
		$res = $this->query($sql, $params);
		return $res;
	}
	public function InsertMenu2 ($nombre, $nivel, $orden, $codsuperior, $link, $imagen, $target, $estado){
		$sql = "INSERT INTO menu (nombre_menu, nivel, orden, codsuperior, link, imagen, target, estado) VALUES (:nombre_menu, :nivel, :orden, :codsuperior, :link, :imagen, :target, :estado)";
		$params = array(':nombre_menu' => $nombre, ':nivel' => $nivel, ':orden' => $orden, ':codsuperior' => $codsuperior, ':link' => $link, ':imagen' => $imagen, ':target' => $target,':estado' => $estado);
		$res = $this->query($sql, $params);
		return $res;
	}
	public function LoadPadresMenu (){
		$sql = "SELECT codigo_menu, nombre_menu, nivel, orden, codsuperior, link, imagen, target, estado FROM menu WHERE estado = :estado AND nivel = :nivel ORDER BY orden ASC";
		$params = array(':nivel'=>'1', ':estado' => 'on');
		$res = $this->table($sql, $params);
		return $res;
	}
	public function BuscarMenu ($nombreMenu, $where){
		$sql = "SELECT codigo_menu, imagen, nombre_menu, nivel, orden, codsuperior, link, estado,
		 target FROM menu WHERE nombre_menu LIKE '%".$nombreMenu."%' ".$where." ORDER BY codigo_menu ASC";
		$res = $this->table($sql);
		return $res;
	}
	public function CodigoSuperior ($codsuperior){
		$sql = "SELECT nombre_menu, imagen FROM menu WHERE codigo_menu = :codmenu";
		$params = array(':codmenu' => $codsuperior);
		$res = $this->row($sql, $params);
		return $res;
	}
	public function EditarMenu ($icono, $nombre, $link, $estado, $codmenu){
		$sql = "UPDATE menu SET imagen = :imagen, nombre_menu = :nombre_menu, link = :link_menu, estado = :estado WHERE codigo_menu = :codmenu";
		$params = array(':imagen' => $icono, ':nombre_menu' => $nombre, ':link_menu' => $link, ':estado' => $estado, ':codmenu'=>$codmenu);
		$res = $this->query($sql, $params);
		return $res;
	}
}
?>