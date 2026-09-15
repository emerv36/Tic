<?php
require_once('../Config/PDOconn.php');
class Version extends db {

//funcion para listar versiones de pensum
    public function ListarVersion(){
        
        $sql ="SELECT id_version, nombre_version, estado_version FROM version_pensum ORDER BY id_version ASC";
        $res =$this->table($sql);
        return $res;
    }
//fin funcion

//funcion para editar version de pensum
    public function EditarVersion($id_version, $nombre_version, $estado_version){
     
        $sql="UPDATE version_pensum SET nombre_version = :nombre_version, estado_version=:estado_version  WHERE id_version = :id_version";
        $parametros =array(':id_version'=>$id_version,
                           ':nombre_version'=>$nombre_version,
                           ':estado_version'=>$estado_version);
        $respuesta =$this->query($sql, $parametros);
        return $respuesta;
    }
//fin funcion 

//fucion para validar el nombre de la version
    public function ValidarVersion($nombre_version){
        $sql="SELECT id_version, nombre_version FROM version WHERE nombre_version = :nombre_version";
        $params = array(':nombre_version'=>$nombre_version);
        $res=$this->row($sql, $params);
        return $res;
    }
//fin fucion 


//función para insertar nueva version
    public function RegistrarVersion($RnombreVersion,$Restado_version ){
        $insert="INSERT INTO version_pensum(nombre_version, estado_version)VALUES(:nombre_version,:estado_version)";
        $params=array(':nombre_version'=>$RnombreVersion,
                      ':estado_version'=>$Restado_version);
        $respuesta =$this->query($insert, $params);
        return $respuesta;

    }
//fin función 
    
//función editar estado de versiones
    public function EditarEstadoVersion($codigo, $estado){
        $sql="UPDATE version_pensum SET estado_version = :estado WHERE id_version = :id_version";
        $parametros = array(':id_version' => $codigo, ':estado'=>$estado);
        $resultado = $this->query($sql, $parametros);
        return $resultado;

    }
//fin función  


}


?>
