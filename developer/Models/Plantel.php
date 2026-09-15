<?php
require_once('../Config/PDOconn.php');

class Plantel extends db{
 
  public function RegistrarPlantel($nit_plantel,
                                    $nombre_plantel,
                                    $nombre_corto,
                                    $direccion_plantel,
                                    $ciudad_plantel
                                  ){
        
          $insert = "INSERT INTO plantel (nit_plantel,
                                          nombre_plantel,
                                          nombre_corto,
                                          direccion_plantel,
                                          ciudad_plantel)
                    VALUES (:nit_plantel,
                            :nombre_plantel,
                            :nombre_corto,
                            :direccion_plantel,
                            :ciudad_plantel)";

      $parametros = array( ':nit_plantel'=> $nit_plantel,
                           ':nombre_plantel' =>$nombre_plantel,
                           ':nombre_corto' =>$nombre_corto,
                           ':direccion_plantel'=> $direccion_plantel,
                           ':ciudad_plantel' => $ciudad_plantel);

     $resultado = $this->query($insert, $parametros);
     return $resultado;

  }
 //fin funcion

//funcion para listar informacion del plantel
   public function ListarPlantel(){
        $select = "SELECT id_plantel,
                          nit_plantel,  
                          nombre_plantel,
                          nombre_corto,
                          direccion_plantel,
                          ciudad_plantel

                          FROM plantel";
        $resultado = $this->row($select);
        return $resultado;
    }




//Funcion para validar el NIT del plantel

public function ValidarNit($nit_plantel){
$select = "SELECT id_plantel, nit_plantel FROM  plantel WHERE nit_plantel = :nit_plantel ";
$params = array(':nit_plantel' => $nit_plantel);
$resultado = $this->row($select, $params);
return $resultado;
}


//funcion para validar el concepto
public function ValidarConcepto($concepto)
{
 $select = "SELECT concepto, porcentaje, pordisponible FROM  par_nota WHERE concepto = :concepto";
 $params = array(':concepto' => $concepto);
 $resultado = $this->row($select, $params);
return $resultado;   
}



//funcion para contar los registos


//funcion buscar porcwntajes
public function ContarRegistros(){
    $select ="SELECT count(*) AS total FROM  par_nota";
    $resultado = $this->row($select);
    return $resultado;  
}


//funcion buscar porcwntajes
public function BuscarPorcentaje(){
    $select ="SELECT 100 - SUM(porcentaje) AS suma_por FROM  par_nota";
    $resultado = $this->row($select);
    return $resultado;  
}


// funcion actualizar informacion del plantel 
  public function  ActualizarPlantel($id_plantel,
                                     $nit_plantel,
                                     $nombre_plantel,
                                     $nombre_corto,
                                     $direccion_plantel,
                                     $ciudad_plantel){

            $update = "UPDATE plantel SET nit_plantel               =:nit_plantel,
                                          nombre_plantel            =:nombre_plantel,
                                          nombre_corto              =:nombre_corto,
                                          direccion_plantel         =:direccion_plantel,
                                          ciudad_plantel            =:ciudad_plantel
                                         WHERE id_plantel       = :id_plantel";
                                
                             $params = array(':id_plantel'                =>$id_plantel,
                                             ':nit_plantel'               =>$nit_plantel,
                                             ':nombre_plantel'            =>$nombre_plantel,
                                             ':nombre_corto'              =>$nombre_corto,
                                             ':direccion_plantel'         =>$direccion_plantel,
                                             ':ciudad_plantel'            =>$ciudad_plantel
                                          );

                    $resultado = $this->query($update, $params);
                    return $resultado;
     }
//fin función 

         




//funcion para guardar los conceptos de evaluacion
public function RegistrarParametros($id_plantel,
                                    $plantel_nota_maxima,
                                    $plantel_nota_minima,
                                    $Nivelbajo,
                                    $Nivelbasico,
                                    $Nivelsuperior,
                                    $Nivelalto,
                                    $DesdeNotaBajo,
                                    $HastaNotaBajo,
                                    $DesdeNotaBasico,
                                    $HastaNotaBasico,
                                    $DesdeNotaAlto,
                                    $HastaNotaAlto,
                                    $DesdeNotaSuperior,
                                    $HastaNotaSuperior)
                                   {
        
            $update = "UPDATE plantel SET plantel_nota_maxima     =:plantel_nota_maxima,
                                          plantel_nota_minima     =:plantel_nota_minima,
                                          concepto_bajo           =:concepto_bajo,
                                          concepto_basico         =:concepto_basico,
                                          concepto_alto           =:concepto_alto,
                                          concepto_superior       =:concepto_superior,
                                          desde_nota_bajo         =:desde_nota_bajo,
                                          hasta_nota_bajo         =:hasta_nota_bajo,
                                          desde_nota_basico       =:desde_nota_basico,
                                          hasta_nota_basico       =:hasta_nota_basico,
                                          desde_nota_alto         =:desde_nota_alto,
                                          hasta_nota_alto         =:hasta_nota_alto,
                                          desde_nota_superior     =:desde_nota_superior,
                                          hasta_nota_superior     =:hasta_nota_superior                                      
                                          WHERE id_plantel         =:id_plantel";
                                
                             $params = array(':id_plantel'            =>$id_plantel,
                                             ':plantel_nota_maxima'   =>$plantel_nota_maxima,
                                             ':plantel_nota_minima'   =>$plantel_nota_minima,
                                             ':concepto_bajo'         =>$Nivelbajo,
                                             ':concepto_basico'       =>$Nivelbasico,
                                             ':concepto_alto'         =>$Nivelalto,
                                             ':concepto_superior'     =>$Nivelsuperior,
                                             ':desde_nota_bajo'       =>$DesdeNotaBajo,
                                             ':hasta_nota_bajo'       =>$HastaNotaBajo,
                                             ':desde_nota_basico'     =>$DesdeNotaBasico,
                                             ':hasta_nota_basico'     =>$HastaNotaBasico,  
                                             ':desde_nota_alto'       =>$DesdeNotaAlto, 
                                             ':hasta_nota_alto'       =>$HastaNotaAlto,
                                             ':desde_nota_superior'   =>$DesdeNotaSuperior,
                                             ':hasta_nota_superior'   =>$HastaNotaSuperior);

                    $resultado = $this->query($update, $params);
                    return $resultado;

  }


//funcion para listar los conceptos

public function  ListarConceptos(){
   $select="SELECT id_par,
                   concepto, 
                   porcentaje,
                   pordisponible,
                   id_plantel,
                   nombre_plantel,
                   (plantel_nota_maxima*porcentaje)/100 as nota_equivalente,

            FROM par_nota par 
            INNER JOIN plantel p ON par.id_plantelfk=p.id_plantel ORDER BY id_par ASC";
            $resultado = $this->table($select);
           return $resultado;
}


//funcion para el parametros de documentos estudiantes en practicas
public function RegistrarDocumento($Rnombre_documento, $Rarea_documento, $Restado_documento){

$insert = "INSERT INTO documento_practicante (nombre_documento,
                                              id_documento_areafk,
                                              estado_documento )
                                              VALUES (:nombre,
                                              :area,
                                              :estado)";
$parametros = array( ':nombre'=> $Rnombre_documento,
                      ':area' =>$Rarea_documento,
                      ':estado' =>$Restado_documento);
$resultado = $this->query($insert, $parametros);
return $resultado;
}

//funcion para listar los documentos por areas
public function ListarDocumentos(){
  $select="SELECT id_documento,
                  nombre_documento,
                  id_documento_areafk,
                  nombre_area,
                  estado_documento
                  FROM documento_practicante dp
                  INNER JOIN AREA a ON dp.id_documento_areafk=a.id_area";
$resultado = $this->table($select);
return $resultado;
}

//funcion para actualizar documentos
public function UpdateDocumento($txtid, $txtnombre_documento, $txtarea_documento, $txtestado_documento){
$sql="UPDATE documento_practicante SET nombre_documento=:nombre,
                                       id_documento_areafk=:area,
                                      estado_documento=:estado
                                      WHERE id_documento=:id";
     $parametros = array( ':id'=>$txtid,
                          ':nombre'=> $txtnombre_documento,
                          ':area' =>$txtarea_documento,
                          ':estado' =>$txtestado_documento);
                $resultado = $this->query($sql, $parametros);
                return $resultado;

}

//función  para cambiar el estado del documento
public function EditarEstadoDocumento($codigo, $estado){
  $sql="UPDATE documento_practicante SET estado_documento = :estado WHERE id_documento = :id_documento";
  $parametros = array(':id_documento' => $codigo, ':estado'=>$estado);
  $resultado = $this->query($sql, $parametros);
  return $resultado;
}

//funcion para validar el documento 
public function ValidarDocumento($nombre, $area){
  $sql="SELECT id_documento,
               nombre_documento,
               id_documento_areafk      
        FROM documento_practicante
        WHERE nombre_documento=:nombre AND id_documento_areafk=:area";
  $parametros = array(':nombre' => $nombre, 
                        ':area'=>$area);
  $resultado = $this->row($sql, $parametros);
  return $resultado;
}

//funcion para clonar los documentos

public function ClonarDocumento($Cnombre, $Carea){
  $insert = "INSERT INTO documento_practicante (nombre_documento,
                                                id_documento_areafk,
                                                estado_documento )
                                                VALUES (:nombre,
                                                :area,
                                                :estado)";
  $parametros = array( ':nombre'=> $Cnombre,
                        ':area' =>$Carea,
                        ':estado' =>'on');
  $resultado = $this->query($insert, $parametros);
  return $resultado;
  }
  


}

?>