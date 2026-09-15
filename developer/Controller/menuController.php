<?php
session_start();
require('../Models/Menu.php');
$m = new Menu();
    
if(isset($_GET['case'])){
  $case=$_GET['case'];
}

// VARIABLE menu
if(isset($_POST['codmenu'])){
  $codmenu = $_POST['codmenu'];
}
if(isset($_POST['icono'])){
  $icono_menu = trim($_POST['icono']);
}
if(isset($_POST['nombre_menu'])){
  $nombre_menu = ucfirst(mb_strtolower(trim($_POST['nombre_menu']), 'UTF-8'));
}
if(isset($_POST['nivel'])){
  $nivel_menu = $_POST['nivel'];
}
if(isset($_POST['link'])){
  $link_menu = mb_strtolower($_POST['link']);
}
if(isset($_POST['padre'])){
  $padre_menu = $_POST['padre'];
}
if(isset($_POST['estado'])){
  $estado_menu = $_POST['estado'];
}
// Cariables para MENU
if(isset($_GET['nombre_menu'])){
  $nombre_menu = ucfirst(mb_strtolower(trim($_GET['nombre_menu']), 'UTF-8'));
}
if(isset($_GET['nivel'])){
  $nivel_menu = $_GET['nivel'];
}
if(isset($_GET['padre'])){
  $padre_menu = $_GET['padre'];
}
// FIN VARIABLE MENU


$createtable = array(
  'data' => array()
);
    
switch ($case) {

    case "obtenerMenu":
      $items = $m->CargarMenuDelUsuario();
      $json = json_encode($items);
    break;

    case 'loadMenu':
      $table = $m->LoadMenu();

      $i = 1;
      foreach ($table as $datarow => $data) {

        if($data["estado"] == 'on'){
          $estado = 'Habilitado';
        }else if($data["estado"] == 'off'){
          $estado = 'Inhabilitado';
        }else{
          $estado = 'Error';
        }

        //$icono = '<i class="material-icons">'.$data["imagen"].'</i>';

        $icono='<i class="fa fa-'.$data["imagen"].'"></i>';
        $edit = '<div class="btn btn-sm btn-warning fa fa-pencil-square-o data-toggle="modal" data-target="#modal-menuEdit" onclick="modalEdit('.$data["codigo_menu"].', \''.$data["imagen"].'\',  \''.$data["nombre_menu"].'\', \''.$data["link"].'\', \''.$data["estado"].'\')">
        </div>';
        
        
        if($data["codsuperior"] == 0){
          $codsuperior = 'Sin menú superior';
        } else{
          $row = $m->LoadMenu2($data["codsuperior"]); 
          $codsuperior = '<b>'.$row["nombre_menu"].'</b>';
        }

        $options = $edit;
          
        array_push($createtable['data'], array($i, $icono, $data["nombre_menu"],$data["nivel"],$data["orden"], $codsuperior, $data["link"], $estado, $options));

        $i++;

      }
      $json = json_encode($createtable);
    break;

    case 'buscarMenu':
      $where ="";
      if($nivel_menu != ''){
        $where .= " AND nivel = ".$nivel_menu;
      }
      if($padre_menu != ''){
        $where .= " AND codsuperior = ".$padre_menu;
      }

      $table = $m->BuscarMenu($nombre_menu, $where);
      $i = 1;
      foreach ($table as $datarow => $data) {

        
        if($data["estado"] == 'on'){
          $estado = 'Habilitado';
        }else if($data["estado"] == 'off'){
          $estado = 'Inhabilitado';
        }else{
          $estado = 'Error';
        }
        $icono='<i class="fa fa-'.$data["imagen"].'"></i>';

        $edit = '<div class="btn btn-sm btn-warning fa fa-pencil-square-o onclick="modalEditNew('.$data["codigo_menu"].', \''.$data["imagen"].'\',  \''.$data["nombre_menu"].'\', \''.$data["link"].'\', \''.$data["estado"].'\')"></div>';

        if($data["codsuperior"] == 0){
          $codsuperior = 'Sin menú superior';
        } else{
          $row = $m->CodigoSuperior($data["codsuperior"]); 
          $codsuperior = '<b>'.$row["nombre_menu"].'</b>';
    
        }

        $options = $edit;
        array_push($createtable['data'], array($i, $icono, $data["nombre_menu"],$data["nivel"],$data["orden"], $codsuperior, $data["link"], $estado, $options));
        $i++;
      }
      $json = json_encode($createtable);
    break;

    case 'loadPadresMenu':
      $table = $m->LoadPadresMenu();

      if(count($table)>=1){
        $json = json_encode(array("success"=>true,"menu" =>$table));
      }else{
        $json = json_encode(array("success"=>false,"mensaje" => "No hay Menú disponibles"));
      }
    break;

    case 'insertItemMenu':

      if($nivel_menu == '1'){
        $row = $m->NivelMenu($nivel_menu);
        if($row == ''){
          $orden = 1;
        }else{
          $orden = ($row["nivelmax"] + 1);
        }

        $query = $m->InsertMenu($nombre_menu,$nivel_menu, $orden, 0, $link_menu, $icono_menu, '_self', $estado_menu);
        if($query){
          $json = json_encode(array("success"=>true,"orden" =>$orden));
        }else{
          $json = json_encode(array("success"=>false,"mensaje" => "Error al insertar Item para el Menú"));
        }
      }else if($nivel_menu == '2'){
        $row = $m->NivelMenu2($nivel_menu, $padre_menu);
        $orden = ($row["nivelmax"] + 1);

        $query2 = $m->InsertMenu2($nombre_menu,$nivel_menu, $orden, $padre_menu, $link_menu, $icono_menu, '_self', $estado_menu);
        if($query2){
          $json = json_encode(array("success"=>true,"orden" =>$orden));
        }else{
          $json = json_encode(array("success"=>false,"mensaje" => "Error al insertar Item para el Menú"));
        }
      }
    break;

    case 'editItemMenu':
      $query = $m->EditarMenu($icono_menu, $nombre_menu, $link_menu, $estado_menu, $codmenu);
      if($query){
        $json = json_encode(array("success"=>true));
      }else{
        $json = json_encode(array("success"=>false,"mensaje" => "No se Actualizó la información. Por favor, intentelo de nuevo"));
      }
    break;

}

echo $json;

?>