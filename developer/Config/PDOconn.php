<?php
date_default_timezone_set('America/Bogota');
require_once __DIR__ . '/config.php';

class db {
    /*Funciones de operacion retorna 0 o 1*/
    function query($sql,$params=null){
        try {
            
            $conn= new PDO(connstring,user,pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $sw= true; 
        } catch (Exception $ex) {
            error_log("Database error: " . $ex->getMessage());
            // $sw=json_encode(array("mensaje" => $ex->getMessage()));
            $sw=false;
        }
      return $sw;
    }

    /*Devuelve una fila*/
    function row($sql,$params=null){
        try {
            
            $conn= new PDO(connstring,user,pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
             
        } catch (Exception $ex) {
            error_log("Database error: " . $ex->getMessage());
        }
      return $row;
    }
    

      function generatecod() {
        $randomString = str_shuffle("ghijklx6789".uniqid());
        return $randomString;
    } 

    function base64url_encode($data) {
      return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    function urlservidor(){
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
        return $actual_link;
    }

    function urlimg(){
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/images/";
        return $actual_link;
        }
    /*Devuelve una id del que se insertó*/
    function DataRow($sql,$params=null){
        try {
            
            $conn= new PDO(connstring,user,pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $DataRow = $stmt->fetch(PDO::FETCH_ASSOC);
             
        } catch (Exception $ex) {
            error_log("Database error: " . $ex->getMessage());
        }
      return $DataRow;
    }

    /*Devuelve una tabla*/
    function table($sql,$params=null){
        $data=array();
        
        try {
            
            $conn= new PDO(connstring,user,pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            while( $row = $stmt->fetch(PDO::FETCH_ASSOC)){
                 $data[] = $row;   
            }  
        } catch (Exception $ex) {
            error_log("Database error: " . $ex->getMessage());
        }
      return $data;
    }

   
   function TableStr($sql,$params=null){
    $data=array();
    try{
        $conn=new PDO(connstring,user,pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        while( $row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::PARAM_STR)){
                 $data[] = $row;   
            }  
        } catch (Exception $ex) {
            error_log("Database error: " . $ex->getMessage());
        }
      return $data;
    }



    // *Devolver hora actual*

    function datetimeNow(){
        $fechaactual = getdate();
        $hora=$fechaactual['hours'];
        $minuto=$fechaactual['minutes'];
        $segundo=$fechaactual['seconds'];

        $anio=$fechaactual['year'];
        $mes=$fechaactual['mon'];
        $dia=$fechaactual['mday'];
        $actual =  ($anio<=9 ? '0' .$anio : $anio)."-".($mes<=9 ? '0' .$mes : $mes)."-".($dia<=9 ? '0' .$dia : $dia)." ".($hora<=9 ? '0' .$hora : $hora).":".($minuto<=9 ? '0' .$minuto : $minuto).":".($segundo<=9 ? '0' .$segundo : $segundo);

        return $actual;
    }

    function dateNow(){

        $fechaactual = getdate();

        $anio=$fechaactual['year'];
        $mes=$fechaactual['mon'];
        $dia=$fechaactual['mday'];


        $actual =  ($anio<=9 ? '0' .$anio : $anio)."-".($mes<=9 ? '0' .$mes : $mes)."-".($dia<=9 ? '0' .$dia : $dia);

        return $actual;
    }

    function urls_amigables($url) {
        // Tranformamos todo a minusculas
        $url = strtolower($url);
        //Rememplazamos caracteres especiales latinos
        $find = array('á', 'é', 'í', 'ó', 'ú', 'ñ');
        $repl = array('a', 'e', 'i', 'o', 'u', 'n');
        $url = str_replace ($find, $repl, $url);
        // Añadimos los guiones
        $find = array(' ', '&', '\r\n', '\n', '+');
        $url = str_replace ($find, '-', $url);
        // Eliminamos y Reemplazamos otros carácteres especiales
        $find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
        $repl = array('', '-', '');
        $url = preg_replace ($find, $repl, $url);
        return $url;
    }

    
}

?>

