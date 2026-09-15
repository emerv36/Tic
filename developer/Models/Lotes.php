<?php
 require_once('../Config/PDOconn.php');
class Lotes extends db {

//funcion para la consulta de listar las lotes
    public function ListarLotes($usuario, $rol = null){
        $where = "WHERE id_usuariofk = :id";
        $parametros = array(':id' => $usuario);

        // Si es Programador (Rol 1), ver todo
        if($rol == 1){
            $where = "";
            $parametros = array();
        }

        $sql ="SELECT id_lote,
                      etapa,
                      codigo,
                      estado_lote,
                      id_usuariofk,
                      nombres_usuario,
                      fecha_registro_lote
                      FROM lotecarnet l 
                      INNER JOIN usuario u ON l.id_usuariofk=u.codigo_usu 
                      $where
                      ORDER BY id_lote DESC";
      $resultado =$this->table($sql, $parametros);
      return $resultado;
    }
//fin funcion 

//funcion para editar lotes
    public function EditarLotes($id_lote, $codigo, $etapa, $estado){     
        $sql="UPDATE lotecarnet SET etapa = :etapa, codigo=:codigo, estado_lote=:estado_lote
        WHERE id_lote = :id_lote";
        $parametros =array('id_lote'=>$id_lote,
                           'etapa'=>$etapa,
                           ':codigo'=>$codigo,
                           ':estado_lote'=>$estado);
        $respuesta =$this->query($sql, $parametros);
        return $respuesta;
    }
//fin funcion 

//fucion para codigo del lote
    public function ValidarLote($codigo, $etapa){
        $sql="SELECT id_lote, trim(etapa) as etapa, codigo FROM lotecarnet
              WHERE etapa = :etapa AND codigo=:codigo";
        $params = array(':etapa'=>$etapa,
                        ':codigo'=>$codigo);
        $res=$this->row($sql, $params);
        return $res;
    }
//fin fucion para validar 

//función para insertar nueva lote
    public function RegistrarLote($codigo,$etapa, $estado, $usuario){
        $insert="INSERT INTO lotecarnet(codigo,
                                        etapa,
                                        estado_lote,
                                        fecha_registro_lote, 
                                        id_usuariofk)
                                VALUES(:codigo,
                                       :etapa,
                                       :estado_lote,
                                       :fecha_registro_lote,
                                       :usuario)";
        $params=array(':codigo'=>$codigo,
                      ':etapa'=>$etapa,
                      ':estado_lote'=>$estado,
                      ':fecha_registro_lote'=>$this->datetimeNow(),
                      ':usuario'=>$usuario);
        $respuesta =$this->query($insert, $params);
        return $respuesta;

    }
//fin función

//función  para la sentencia sql de editar 
    public function EditarEstadoLote($codigo, $estado){
        $sql="UPDATE lotecarnet SET estado_lote = :estado WHERE id_lote = :id_lote";
        $parametros = array(':id_lote' => $codigo, ':estado'=>$estado);
        $resultado = $this->query($sql, $parametros);
        return $resultado;
    }
//fin función  para la sentencia sql de editar el estado de las sedes

}
?>
