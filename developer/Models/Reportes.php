<?php
require_once('../Config/PDOconn.php');
class Reportes extends db
{

//funcion para listra los reportes 
    public function ListarReportes()
    {
        $select = "SELECT id_reporte,
                          codigo_reporte,
                          nombre_reporte,
                          nombre_archivo,
                          id_version_reportefk,
                          nombre_version_reporte,
                          fecha_version_reporte,
                          id_plantelfk,
                          nombre_plantel,
                         estado_reporte
        FROM reporte r 
        INNER JOIN version_reporte  v   ON r.id_version_reportefk=v.id_version_reporte
        INNER JOIN plantel          p   ON r.id_plantelfk=p.id_plantel
        ORDER BY id_reporte ASC ";
        $resultado = $this->table($select);
        return $resultado;
    }

//fin funcion



//funcion para listra los reportes 
public function ListarVersion()
    {
        $select = "SELECT * FROM version_reporte
        ORDER BY id_version_reporte ASC ";
        $resultado = $this->table($select);
        return $resultado;
    }

//fin funcion



//function cargar version
public function CargarVersion(){
       $select="SELECT id_version_reporte as cod, nombre_version_reporte as nombre FROM version_reporte";
       $resultado = $this->table($select);
       return $resultado;
}
//fin function


//function cargar version
public function CargarPlantel(){
       $select="SELECT id_plantel as cod, nombre_plantel as nombre FROM plantel";
       $resultado = $this->table($select);
       return $resultado;
}
//fin function

// funcion cargar los perfiles para la creacion del reporte




//función  para editar los reportes
    public function EditarEstadoReportes($codreporte, $estado)
    {
        $sql = "UPDATE reporte SET estado = :estado WHERE id_reporte = :id_reporte";
        $parametros = array(':id_reporte ' => $codreporte, ':estado' => $estado);
        $resultado = $this->query($sql, $parametros);
        return $resultado;
    }
//fin función





    
// función sql para actualizar reportes
public function ActualizarReportes($EcodigoReporte,
                                   $EnombreReporte,
                                   $EnombreArchivo,
                                   $EversionReporte,
                                   $EplantelReporte,
                                   $EestadoReporte,
                                   $EidReporte) {
  
  $update = "UPDATE reporte SET codigo_reporte        = :codigo_reporte, 
                                nombre_reporte        = :nombre_reporte,
                                nombre_archivo        = :archivo,
                                id_version_reportefk  = :id_version_reportefk,
                                id_plantelfk          = :id_plantelfk,
                                estado_reporte        = :estado_reporte
  
  WHERE id_reporte = :id_reporte";
  
  $params = array(':codigo_reporte' => $EcodigoReporte,
                   ':nombre_reporte'       => $EnombreReporte,
                   ':archivo'              => $EnombreArchivo,
                   ':id_version_reportefk' => $EversionReporte,
                   ':id_plantelfk'         => $EplantelReporte,
                   ':estado_reporte'       => $EestadoReporte,
                   ':id_reporte'           => $EidReporte);
        $resultado = $this->query($update, $params);
        return $resultado;
    }

//fin función 

// función sql para validar el codigo de reporte que no se repita
    public function ValidarReportes($RcodigoReporte)
    {

        $select = "SELECT codigo_reporte FROM  reporte WHERE codigo_reporte = :codigo";
        $params = array(':codigo' => $RcodigoReporte);
        $resultado = $this->row($select, $params);
        return $resultado;
    }

//fin  función sql para validar que un numero de periodos no se repita más de una vez

//función para registrar los reportes
    public function RegistrarReportes($RcodigoReporte,
                                      $RnombreReporte,
                                      $RnombreArchivo,
                                      $RversionReporte,
                                      $RplantelReporte,
                                       $RestadoReporte) {
        $insert = "INSERT INTO reporte (codigo_reporte,
                                        nombre_reporte,
                                        nombre_archivo,
                                        id_version_reportefk,
                                        id_plantelfk,
                                        estado_reporte) 
                   VALUES  (:codigo_reporte,
                            :nombre_reporte,
                            :nombre_archivo,
                            :id_version_reportefk,
                            :id_plantelfk,
                             :estado_reporte)";
        $params =  array(':codigo_reporte'  => $RcodigoReporte,
                         ':nombre_reporte'  => $RnombreReporte,
                         ':nombre_archivo' => $RnombreArchivo,
                         ':id_version_reportefk' => $RversionReporte,
                         ':id_plantelfk' => $RplantelReporte,
                         ':estado_reporte' => $RestadoReporte);
        $resultado = $this->query($insert, $params);
        return $resultado;
    }
//fin función 



//función para registrar los reportes
    public function RegistrarVersion($RnombreVersion,
                                     $RfechaVersion) {
        $insert = "INSERT INTO version_reporte (nombre_version_reporte,
                                                fecha_version_reporte ) 
                   VALUES  (:nombre_version_reporte,
                            :fecha_version_reporte)";
               $params =  array('nombre_version_reporte' => $RnombreVersion,
                                 'fecha_version_reporte' => $RfechaVersion);
        $resultado = $this->query($insert, $params);
        return $resultado;
    }
//fin función 


// función sql para actualizar reportes
public function ActualizarVersion($Eidversion,
                                  $EnombreVersion,
                                  $Efechaversion) {
  
  $update = "UPDATE version_reporte SET id_version_reporte = :id_version, 
                                        nombre_version_reporte = :nombre_version,
                                        fecha_version_reporte  = :fecha_version
  
  WHERE id_version_reporte = :id_version";
  
  $params = array(':id_version'=>$Eidversion,
                   ':nombre_version'=>$EnombreVersion,
                   ':fecha_version'=>$Efechaversion);
        $resultado = $this->query($update, $params);
        return $resultado;
    }
}
?>