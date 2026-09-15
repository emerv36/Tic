<?php
session_start();
date_default_timezone_set("America/Bogota");
header('content-type: application/json; charset=utf-8');
require('../Models/Lotes.php');

$lote = new Lotes();
    if(isset($_GET['case'])){
    $case = $_GET['case'];
    }

     //variables editar estado de los programas
 if(isset($_POST['codUsuario'])){
    $codigo = $_POST['codUsuario'];
}
if(isset($_POST['estado'])){
    $estado = $_POST['estado'];
}

//variables para registrar lotes
  if(isset($_POST['Rcodigolote'])){ $Rcodigo       =$_POST['Rcodigolote'];}
  if(isset($_POST['RestadoLote'])){ $RestadoLote   =$_POST['RestadoLote'];}  
  if(isset($_POST['RtipoLote'])){   $Retapa       =$_POST['RtipoLote'];}
  

//variables para editar
if(isset($_POST['txtidlote'])){     $txtidlote      =$_POST['txtidlote'];}
if(isset($_POST['txtCodigoLote'])){ $txtcodigoLote  =$_POST['txtCodigoLote'];}
if(isset($_POST['txttipoLote'])){   $txttipoLote    =$_POST['txttipoLote'];}  
if(isset($_POST['txtEstadoLote'])){ $txtEstadoLote  =$_POST['txtEstadoLote'];}  


//variables editar estado de las sedes
  if(isset($_POST['codUsuario'])){  $codigo = $_POST['codUsuario'];  }
  if(isset($_POST['estado'])){      $estado = $_POST['estado'];  }

//fin variables editar estado de las sedes
$table = array(
'data' =>array()
);
switch($case){
// case para el listado de las sedes
    case'ListarLotes':
    $usuario = $_SESSION['IN_codigo_usuCA'];
    $rol = $_SESSION['IN_codrol'];
    $tabla = $lote->ListarLotes($usuario, $rol);
    $j=1;
        foreach($tabla as $datos => $data){
            if($data['estado_lote']=='on'){
                $estado ='Habilitado';
            }else if($data['estado_lote']=='off'){
                $estado ='Deshabilitado';
            }else{
                $estado = 'Error';
            }


            $edit = '<div class="btn btn-sm btn-warning fa fa-pencil-square-o data-toggle="modal" 
            onclick="ModalEditarLote('.$data['id_lote'].',                                   
                                       \''.$data['codigo'].'\',
                                       \''.$data['etapa'].'\',
                                       \''.$data['estado_lote'].'\',
                                       )"> </div>';

            $delete = '<div class="btn btn-sm btn-danger fa fa-trash-o" data-toggle="modal" data-target="modalconfirmar" onClick="FeEditEstado('.$data["id_lote"].',\'off\')"> </div>';

            $restore = '<div class="btn btn-sm btn-success glyphicon glyphicon-check" data-toggle="modal" data-target="modalconfirmar" onClick="FeEditEstado('.$data["id_lote"].', \'on\')"></div>';
            
            $pdf_btn = '<a href="exportar_carnet.php?lote='.$data['id_lote'].'" target="_blank" class="btn btn-sm btn-info fa fa-file-pdf-o" title="Exportar Carnets a PDF"></a>';

            if($data["estado_lote"] == 'on'){
                 $opcion = $edit.' '.$delete.' '.$pdf_btn;
              }else{
                 $opcion = $restore.' '.$pdf_btn;
              }
            array_push($table['data'], array($j, 
                                             strtoupper($data['codigo']),
                                             strtoupper($data['etapa']),
                                             strtoupper($data['nombres_usuario']),
                                             $data['fecha_registro_lote'],
                                             strtoupper($estado),
                                             $opcion));
            $j++;
        }
        $json = json_encode($table);
    break;
// fin case 

//case para editar lote
  case'EditarLotes':
      $res=$lote->EditarLotes($txtidlote,  $txtcodigoLote, $txttipoLote, $txtEstadoLote);
         if($res){
          $json = json_encode(array("success"=>true));
         }else{
             $json = json_encode(array("success"=>false, "mensaje" => "No se pudo actualizar la información. Intentelo de nuevo."));
         }
  break;
//fin case para editar lotes

//case para registrar lotes
 case'RegistrarLote':

  if(isset($_POST['id_sede'])){$id_sede = strtoupper($_POST['id_sede']);}
        $validar = $lote->ValidarLote($Rcodigo, $Retapa );
        $usuario= $_SESSION['IN_codigo_usuCA'];
        
        if($validar==''){
        
            $query=$lote->RegistrarLote($Rcodigo,$Retapa,$RestadoLote, $usuario);
             if($query){
                  $json = json_encode(array("success"=>true));
                  }else{
                  $json = json_encode(array("success"=>false, "mensaje"=>"No se pudo realizar el regisro"));
                }
           }else{
           $json = json_encode(array("success"=>false, "mensaje"=>"Código del lote se enecuentra registrado, verifique por favor"));
      }
 break;
//fin case para registrar sedes

//case para editar estado del lote
 case'EditarEstadoLote':
      $query = $lote->EditarEstadoLote($codigo, $estado);
         if($query){
             $json = json_encode(array("success"=>true));
         }else{
             $json = json_encode(array("success" => false,"mensaje" => "No se ha podido actualizar la información. Por favor intentelo de nuevo"));
         }
 break;

//fin case 
}
echo $json;
?>