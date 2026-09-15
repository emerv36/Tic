 <?php
 require_once('../Config/PDOconn.php');
class Sedes extends db {

//funcion para la consulta de listar las sedes
    public function ListarSedes(){
        
        $sql ="SELECT id_sede,
                      nombre_sede,
                      estado_sede 
                      FROM sede 
                      WHERE tipo_sede='1'
                      ORDER BY id_sede ASC";
        $res =$this->table($sql);
        return $res;
    }
    public function ListarEmpresas(){
        
        $sql ="SELECT id_sede,
                      nombre_sede,
                      estado_sede 
                      FROM sede 
                      WHERE tipo_sede='2'
                      ORDER BY id_sede ASC";
        $res =$this->table($sql);
        return $res;
    }
//fin funcion para la consulta de listar las sedes

//funcion para la sentencia sql de editar sedes
    public function EditarSedes($id_sede, $nombre_sede){     
        $sql="UPDATE sede SET nombre_sede=:nombre_sede
        WHERE id_sede=:id_sede";
        $parametros =array('id_sede'=>$id_sede,
                           ':nombre_sede'=>$nombre_sede);
        $respuesta =$this->query($sql, $parametros);
        return $respuesta;
    }
//fin funcion para la sentencia sql de editar sedes

//fucion para validar que la sede exista o no
    public function ValidarSede($nombre_sede){
        $sql="SELECT id_sede, trim(nombre_sede) as nombre_sede FROM sede WHERE nombre_sede = :nombre_sede AND tipo_sede=1";
        $params = array(':nombre_sede'=>$nombre_sede);
        $res=$this->row($sql, $params);
        return $res;
    }
    
    public function ValidarEmpresa($nombre_sede){
        $sql="SELECT id_sede, trim(nombre_sede) as nombre_sede FROM sede WHERE nombre_sede = :nombre_sede AND tipo_sede=2";
        $params = array(':nombre_sede'=>$nombre_sede);
        $res=$this->row($sql, $params);
        return $res;
    }
    
//fin fucion para validar que la sede exista o no 

//función para insertar nueva sede
    public function RegistrarSedes($NombreSede){
        $insert="INSERT INTO sede(nombre_sede,
                                  estado_sede,
                                  fechacreacion_sede)
                                VALUES(:nombre_sede,
                                       :estado_sede,
                                       :fechacreacion_sede)";
        $params=array(':nombre_sede'=>$NombreSede,
                       ':estado_sede'=>'on',
                       ':fechacreacion_sede'=>$this->datetimeNow());
        $respuesta =$this->query($insert, $params);
        return $respuesta;

    }
    public function RegistrarEmpresa($NombreSede){
        $insert="INSERT INTO sede(nombre_sede,
                                  estado_sede,
                                  fechacreacion_sede,
                                  tipo_sede)
                                VALUES(:nombre_sede,
                                       :estado_sede,
                                       :fechacreacion_sede,
                                       :tipo)";
        $params=array(':nombre_sede'=>$NombreSede,
                       ':estado_sede'=>'on',
                       ':fechacreacion_sede'=>$this->datetimeNow(),
                       ':tipo' => "2");
        $respuesta =$this->query($insert, $params);
        return $respuesta;

    }
//fin función para insertar nueva sede

//función  para la sentencia sql de editar el estado de las sedes
    public function EditarEstadoSedes($codigo, $estado){
        $sql="UPDATE sede SET estado_sede = :estado WHERE id_sede = :id_sede";
        $parametros = array(':id_sede' => $codigo, ':estado'=>$estado);
        $resultado = $this->query($sql, $parametros);
        return $resultado;

    }
//fin función  para la sentencia sql de editar el estado de las sedes

}


?>
