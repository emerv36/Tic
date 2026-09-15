<?php
 session_start();
 date_default_timezone_set("America/Bogota");
 header('content-type: application/json; charset=utf-8');
 require('../Models/AsignarMenuPerfil.php');

        if(isset($_GET['case'])){
            $case=$_GET['case'];
        }
        
        //VARIABLES ASIGNARMENU PERFILES
        if(isset($_POST['codrol'])){
        $codrol = $_POST['codrol'];
        }
        if(isset($_POST['menus'])){
        $menus = $_POST['menus'];
        }

        $createtable = array(
        'data' => array()
        );
        //Llamados de clases


 //Creación de objetos

 $asignarmenuperfil = new AsignarMenuPerfil();

 switch ($case) {
   /************************  procesos para asignarmenuperfiles.php ****************************/
     case 'loadmodulos':
       $tablemenu =$asignarmenuperfil->BuscarMenu();
      //  die(var_dump($tablemenu));
       $rowrolesmenu =$asignarmenuperfil->BuscarRolesMenu($codrol);
       $modulos = explode(",",$rowrolesmenu['menus']);

       $asignados = array();
       $noasignados = array();

       foreach ($tablemenu as $datarowmenu => $datamenu) {
         $pos = in_array($datamenu['codigo_menu'], $modulos);
         if($pos === false){
           array_push($noasignados, array('nombre_menu'=>$datamenu['nombre_menu'], 'codmenu'=>$datamenu['codigo_menu'],'nivel'=>$datamenu['nivel'],'codsuperior'=>$datamenu['codsuperior']));
         }else{
           array_push($asignados, array('nombre_menu'=>$datamenu['nombre_menu'], 'codmenu'=>$datamenu['codigo_menu'],'nivel'=>$datamenu['nivel'],'codsuperior'=>$datamenu['codsuperior']));
         }
       }

       $json = json_encode(array("success"=>true, 'asignados'=>$asignados, 'noasignados'=>$noasignados));
     break;

     case 'updateConfigRolmenu':
       $query =$asignarmenuperfil->EditRolMenu($menus, $codrol);
       if($query){
         $json = json_encode(array("success"=>true));
       }else{
         $json = json_encode(array("success"=>false,"mensaje" => "No se Actualizó la información. Por favor, intentelo de nuevo"));
       }
     break;

     case 'loadRol':
       $table =$asignarmenuperfil->LoadRol();
       $json = json_encode($table);
     break;
   /************************  FIN procesos para asignarmenuperfiles.php ****************************/


 




 }
 echo $json;
?>