<?php 
 require_once('../Config/PDOconn.php');
 class AsignarMenuPerfil extends db
 {
   public function BuscarMenu ()
   {
     $sql = "SELECT codigo_menu, nombre_menu, nivel,codsuperior FROM menu WHERE link!='#' AND estado=:estado ORDER BY codigo_menu ASC";
     $params = array( ':estado' => 'on');    
     $res = $this->table($sql, $params);
     return $res;
   }

   public function BuscarRolesMenu ($codrol)
   {
     $sql = "SELECT menus FROM roles WHERE codigo_rol=:codrol AND estado_rol=:estado";
     $params = array(':codrol'=>$codrol, ':estado' => 'on');
     $res = $this->row($sql, $params);
     return $res;
   }

   public function EditRolMenu ($menus, $codrol)
   {
     $sql = "UPDATE roles SET menus = :menus WHERE codigo_rol = :codrol";
     $params = array(':menus' => $menus, ':codrol'=>$codrol);
     $res = $this->query($sql, $params);
     return $res;
   }
 
   public function LoadRol ()
   {
     $sql = "SELECT codigo_rol as cod,
                    nombre_rol as nombre,
                    menus, estado_rol FROM roles WHERE estado_rol = 'on' ORDER BY nombre ASC";
     $res = $this->table($sql);
     return $res;
   }
    
 
     
}