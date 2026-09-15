<?php
session_start();
date_default_timezone_set("America/Bogota");
header('content-type: application/json; charset=utf-8');
require('../Models/Sedes.php');

$s = new Sedes();
    if(isset($_GET['case'])){
    $case = $_GET['case'];
    }
//variables para editar sedes

  if(isset($_POST['nombre_sede'])){$nombre_sede =strtoupper($_POST['nombre_sede']);}
  if(isset($_POST['id_sede'])){$id_sede = strtoupper($_POST['id_sede']);}

//fin variables para editar sedes

//variables para registra sedes
  if(isset($_POST['Rnombre_sede'])){$Rnombre_sede = strtoupper($_POST['Rnombre_sede']);}
 //fin variables para registra sedes

//variables editar estado de las sedes
  if(isset($_POST['codUsuario'])){  $codigo = $_POST['codUsuario'];  }
  if(isset($_POST['estado'])){      $estado = $_POST['estado'];  }

//fin variables editar estado de las sedes
$table = array(
'data' =>array()
);
switch($case){
// case para el listado de las sedes
    case'ListarSedes':
    $tabla = $s->ListarSedes();
    $j=1;
        foreach($tabla as $datos => $data){
            if($data['estado_sede']=='on'){
                $estado ='Habilitado';
            }else if($data['estado_sede']=='off'){
                $estado ='Deshabilitado';
            }else{
                $estado = 'Error';
            }


            $edit = '<div class="btn btn-sm btn-warning fa fa-pencil-square-o data-toggle="modal" 
            onclick="ModalEditarSedes('.$data['id_sede'].',
                                       \''.$data['nombre_sede'].'\')"> </div>';

            $delete = '<div class="btn btn-sm btn-danger fa fa-trash-o" data-toggle="modal" data-target="modalconfirmar" onClick="FeEditEstado('.$data["id_sede"].',\'off\')"> </div>';

            $restore = '<div class="btn btn-sm btn-success glyphicon glyphicon-check" data-toggle="modal" data-target="modalconfirmar" onClick="FeEditEstado('.$data["id_sede"].', \'on\')"></div>';
            
            if($data["estado_sede"] == 'on'){
                 $opcion = $edit.' '.$delete;
              }else{
                 $opcion = $restore;
              }
            array_push($table['data'], array($j, ucwords(strtoupper($data['nombre_sede'])),
                                                 ucwords(strtoupper($estado))
                                                 ,$opcion));
            $j++;
        }
        $json = json_encode($table);
    break;
    case'ListarEmpresas':
    $tabla = $s->ListarEmpresas();
    $j=1;
        foreach($tabla as $datos => $data){
            if($data['estado_sede']=='on'){
                $estado ='Habilitado';
            }else if($data['estado_sede']=='off'){
                $estado ='Deshabilitado';
            }else{
                $estado = 'Error';
            }


            $edit = '<div class="btn btn-sm btn-warning fa fa-pencil-square-o data-toggle="modal" 
            onclick="ModalEditarSedes('.$data['id_sede'].',
                                       \''.$data['nombre_sede'].'\')"> </div>';

            $delete = '<div class="btn btn-sm btn-danger fa fa-trash-o" data-toggle="modal" data-target="modalconfirmar" onClick="FeEditEstado('.$data["id_sede"].',\'off\')"> </div>';

            $restore = '<div class="btn btn-sm btn-success glyphicon glyphicon-check" data-toggle="modal" data-target="modalconfirmar" onClick="FeEditEstado('.$data["id_sede"].', \'on\')"></div>';
            
            if($data["estado_sede"] == 'on'){
                 $opcion = $edit.' '.$delete;
              }else{
                 $opcion = $restore;
              }
            array_push($table['data'], array($j, ucwords(strtoupper($data['nombre_sede'])),
                                                 ucwords(strtoupper($estado))
                                                 ,$opcion));
            $j++;
        }
        $json = json_encode($table);
    break;
// fin case para el listado de las sedes

//case para editar sedes
  case'EditarSedes':
      $editarsede=$s->EditarSedes($id_sede, $nombre_sede);
         if($editarsede){
          $json = json_encode(array("success"=>true));
         }else{
             $json = json_encode(array("success"=>false, "mensaje" => "No se pudo actualizar la información. Intentelo de nuevo."));
         }
  break;
//fin case para editar sedes

//case para registrar sedes
 case'RegistrarSedes':
       $Rnombre_sede=trim(strtoupper($Rnombre_sede));
       $ValidarSede = $s->ValidarSede($Rnombre_sede);
         if($ValidarSede==''){
            $query=$s->RegistrarSedes($Rnombre_sede);
              if($query){
                 $json = json_encode(array("success"=>true));
              }else{
                $json = json_encode(array("success"=>false, "mensaje"=>"No se pudo realizar el regisro"));
              }
         }else{
           $json = json_encode(array("success"=>false, "mensaje"=>"Nombre de sede se encuentra registarda, verifique por favor"));
         }
 break;
 
 case'RegistrarEmpresa':
       $Rnombre_sede=trim(strtoupper($Rnombre_sede));
       $ValidarSede = $s->ValidarEmpresa($Rnombre_sede);
         if($ValidarSede==''){
            $query=$s->RegistrarEmpresa($Rnombre_sede);
              if($query){
                 $json = json_encode(array("success"=>true));
              }else{
                $json = json_encode(array("success"=>false, "mensaje"=>"No se pudo realizar el regisro"));
              }
         }else{
           $json = json_encode(array("success"=>false, "mensaje"=>"Nombre de sede se encuentra registarda, verifique por favor"));
         }
 break;
//fin case para registrar sedes

//case para editar estado de la sede
 case'EditarEstadoSedes':
      $query = $s->EditarEstadoSedes($codigo, $estado);
         if($query){
             $json = json_encode(array("success"=>true));
         }else{
             $json = json_encode(array("success" => false,"mensaje" => "No se ha podido actualizar la información. Por favor intentelo de nuevo"));
         }
 break;

//fin case para editar estado de la sede
}
echo $json;
?>