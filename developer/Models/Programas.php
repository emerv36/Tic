<?php
require_once('../Config/PDOconn.php');

class Programas extends db
{
    public function EditarCargoPracticante($txtidcargo, $txtnombrecargo, $txtsede)
    {
        $update = "UPDATE programa SET id_programa=:id_programa, nombre_programa=:nombre_programa, id_sedefk=:id_sedefk WHERE id_programa =:id_programa";

        $params = array(
            ':id_programa' => $txtidcargo,
            ':nombre_programa' => $txtnombrecargo,
            ':id_sedefk' => $txtsede
        );
        $resultado = $this->query($update, $params);
        return $resultado;
    }
    
    public function RegistrarCargoPracticante($Nombre, $sede)
    {
        $insert = "INSERT INTO programa(nombre_programa,
                                    id_sedefk,
                                    estado_programa,
                                    tipo_control)
                                    VALUES( :nombre_programa, 
                                            :id_sedefk,
                                            :estado_programa,
                                            :tipo_control)";

        $parametros = array(
            ':nombre_programa' => $Nombre,
            ':id_sedefk' => $sede,
            ':estado_programa' => 'on',
            ':tipo_control' => '3'
        );

        $resultado = $this->query($insert, $parametros);
        return $resultado;
    }
    
    //funcion para la validacion de cargos    
    public function ValidarCargoPracticanteId($id, $nombre, $sede)
    {
        $sql = "SELECT trim(nombre_programa) as nombre_programa, id_sedefk FROM programa WHERE nombre_programa IN (:nombre_programa) AND id_sedefk=:sede AND id_programa<>:id";
        $params = array(
            ':nombre_programa' => $nombre,
            ':sede' => $sede,
            ':id' => $id
        );
        $res = $this->row($sql, $params);
        return $res;
    }
    
    //funcion para la validacion de cargos    
    public function ValidarCargoPracticante($nombre, $sede)
    {
        $sql = "SELECT trim(nombre_programa) as nombre_programa, id_sedefk FROM programa WHERE nombre_programa IN (:nombre_programa) AND id_sedefk=:sede";
        $params = array(
            ':nombre_programa' => $nombre,
            ':sede' => $sede
        );
        $res = $this->row($sql, $params);
        return $res;
    }
    //función para cargar las sedes
    public function CargarEmpresas()
    {
        $select = "SELECT id_sede as cod, nombre_sede as nombre FROM sede WHERE tipo_sede=2";
        $resultado = $this->table($select);
        return $resultado;
    }
    public function ListarCargoPracticante()
    {
        $select = "SELECT id_programa,
                        codigo_programa,
                        nombre_programa,
                        id_sedefk,
                        nombre_sede,
                        estado_programa,
                        tipo_control
                        FROM programa p
                        INNER JOIN sede s ON p.id_sedefk=s.id_sede 
                        WHERE tipo_control='3' AND s.tipo_sede='2'
                         ORDER BY id_programa ASC";
        $resultado = $this->table($select);
        return $resultado;
    }
    
    public function ListarProgramas()
    {
        $select = "SELECT id_programa,
                        codigo_programa,
                        nombre_programa,
                        id_sedefk,
                        nombre_sede,
                        estado_programa,
                        tipo_control
                        FROM programa p
                        INNER JOIN sede s ON p.id_sedefk=s.id_sede
                        WHERE tipo_control='1' AND tipo_sede='1'
                         ORDER BY id_programa ASC";
        $resultado = $this->table($select);
        return $resultado;
    }

    //funcion para la lista de cargos
    public function ListarCargos()
    {
        $select = "SELECT id_programa,
                        codigo_programa,
                        nombre_programa,
                        id_sedefk,
                        nombre_sede,
                        estado_programa
                        FROM programa p
                        INNER JOIN sede s ON p.id_sedefk=s.id_sede
                        WHERE tipo_control='2' AND tipo_sede='1'
                         ORDER BY id_programa ASC";
        $resultado = $this->table($select);
        return $resultado;
    }

    //función  para la sentencia sql de editar el estado de las programas
    public function EditarEstadoProgramas($codigo, $estado)
    {

        $sql = "UPDATE programa SET estado_programa=:estado WHERE id_programa=:id_programa";
        $parametros = array(':id_programa' => $codigo, ':estado' => $estado);
        $resultado = $this->query($sql, $parametros);
        return $resultado;
    }
    //fin función  para la sentencia sql de editar el estado de las programas

    //fucion para validar que el programa exista o no
    public function ValidarPrograma($codigo, $sede)
    {
        $sql = "SELECT trim(codigo_programa)as codigo_programa, id_sedefk FROM programa WHERE codigo_programa = :codigo_programa AND id_sedefk =:id_sedefk";
        $params = array(':codigo_programa' => $codigo, ':id_sedefk' => $sede);
        $res = $this->row($sql, $params);
        return $res;
    }
    //fin fucion para validar que la programa exista o no 


    //funcion para validar el programa en la sede por el nombre
    public function ValidarNombrePrograma($programa, $sede)
    {
        $sql = "SELECT trim(nombre_programa)as nombre_programa, id_sedefk, tipo_control
         FROM programa WHERE nombre_programa IN (:nombre_programa) AND id_sedefk =:sede AND tipo_control='2'";
        $params = array(
            ':nombre_programa' => $programa,
            ':sede' => $sede
        );
        $res = $this->row($sql, $params);
        return $res;
    }

    //funcion para la validacion de cargos    
    public function ValidarCargo($nombre, $sede)
    {
        $sql = "SELECT trim(nombre_programa)as nombre_programa, id_sedefk FROM programa WHERE nombre_programa IN (:nombre_programa) AND id_sedefk =:sede";
        $params = array(
            ':nombre_programa' => $nombre,
            ':sede' => $sede
        );
        $res = $this->row($sql, $params);
        return $res;
    }

    //función para cargar las sedes
    public function CargarSedes()
    {
        $select = "SELECT id_sede as cod, nombre_sede as nombre FROM sede";
        $resultado = $this->table($select);
        return $resultado;
    }
    //fin función para cargar las sedes



    //función para ´la sentencia sql para actualizar la info de los programas 
    public function ActualizarInfoProgramas(
        $txtIdPrograma,
        $txtcodigoPrograma,
        $txtnombrePrograma,
        $txtSelectSedePrograma,
        $txtEstadoPrograma
    ) {

        $update = "UPDATE programa SET id_programa         =:id_programa,
                                   codigo_programa     =:codigo_programa,
                                   nombre_programa     =:nombre_programa,
                                   id_sedefk           =:id_sedefk,
                                   estado_programa     =:estado_programa,
                                   tipo_control        =:tipo_control
                                    WHERE id_programa =:id_programa";

        $params = array(
            ':id_programa' => $txtIdPrograma,
            ':codigo_programa' => $txtcodigoPrograma,
            ':nombre_programa' => $txtnombrePrograma,
            ':id_sedefk' => $txtSelectSedePrograma,
            ':estado_programa' => $txtEstadoPrograma,
            ':tipo_control' => '1'
        );
        $resultado = $this->query($update, $params);
        return $resultado;
    }
    //fin función para ´la sentencia sql para actualizar la info de los programas 


    //función para la sentencia sql, para registrar la info de los programas
    public function RegistrarInfoProgramas($Codigo, $NombrePrograma, $SelectSedePrograma, $EstadoPrograma)
    {
        $insert = "INSERT INTO programa(codigo_programa,
                                    nombre_programa,
                                    id_sedefk,
                                    estado_programa,
                                    tipo_control)
                                    VALUES( :codigo_programa,
                                            :nombre_programa, 
                                            :id_sedefk,
                                            :estado_programa,
                                            :tipo_control)";

        $parametros = array(
            ':codigo_programa' => $Codigo,
            ':nombre_programa' => $NombrePrograma,
            ':id_sedefk' => $SelectSedePrograma,
            ':estado_programa' => $EstadoPrograma,
            ':tipo_control' => '1'
        );

        $resultado = $this->query($insert, $parametros);
        return $resultado;
    }

    //fin función

    //funcion para guardar losm cargos
    public function RegistrarCargos($Nombre, $sede)
    {
        $insert = "INSERT INTO programa(nombre_programa,
                                    id_sedefk,
                                    estado_programa,
                                    tipo_control)
                                    VALUES( :nombre_programa, 
                                            :id_sedefk,
                                            :estado_programa,
                                            :tipo_control)";

        $parametros = array(
            ':nombre_programa' => $Nombre,
            ':id_sedefk' => $sede,
            ':estado_programa' => 'on',
            ':tipo_control' => '2'
        );

        $resultado = $this->query($insert, $parametros);
        return $resultado;
    }

    //funcion para editar cargos
    public function EditarCargos($txtidcargo, $txtnombrecargo, $txtsede)
    {
        $update = "UPDATE programa SET id_programa         =:id_programa,
                               nombre_programa     =:nombre_programa,
                               id_sedefk           =:id_sedefk
                              WHERE id_programa =:id_programa";

        $params = array(
            ':id_programa' => $txtidcargo,
            ':nombre_programa' => $txtnombrecargo,
            ':id_sedefk' => $txtsede
        );
        $resultado = $this->query($update, $params);
        return $resultado;
    }
}
