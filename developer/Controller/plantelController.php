<?php
session_start();
date_default_timezone_set("America/Bogota");
header('content-type: application/json; charset=utf-8');
require('../Models/Plantel.php');
$p = new Plantel ();
if(isset($_GET['case'])){ $case = $_GET['case']; }


//variables para registrar la info del plantel
if(isset($_POST['nit_plantel'])){           $nit_plantel            =   $_POST['nit_plantel'];}
if(isset($_POST['nombre_plantel'])){        $nombre_plantel         =   $_POST['nombre_plantel'];}
if(isset($_POST['direccion_plantel'])){     $direccion_plantel      =   $_POST['direccion_plantel'];}
if(isset($_POST['ciudad_plantel'])){        $ciudad_plantel         =   $_POST['ciudad_plantel'];}
if(isset($_POST['nombre_corto'])){          $nombre_corto           =   $_POST['nombre_corto'];}



//variables editar estado del documento
if(isset($_POST['codUsuario'])){  $codigo = $_POST['codUsuario'];  }
if(isset($_POST['estado'])){  $estado = $_POST['estado'];  }

$table = array('data' =>array());

switch($case){

//case para actualizar la info de los planteles
  case 'ListarPlantel':
  $listar=$p->ListarPlantel();
  $response=json_encode($listar);
  break;


  case'RegistrarPlantel':

   $Plantel =$p->ValidarNit($nit_plantel);
   $id = $Plantel['id_plantel'];
       
       if($id_plantel == $id){
        
      
         $query = $p->RegistrarPlantel($nit_plantel, $nombre_plantel, $nombre_corto, $direccion_plantel,$ciudad_plantel);
           
            if($query){

            $response = json_encode(array("success" => true ,"Información registrada satisfactoriamente"));
            
            }else{
            
            $response = json_encode(array("success" =>false, "mensaje" => "No se ha podido actualizar la información."));
            }
       
       }else{
     
            $query = $p->ActualizarPlantel($id_plantel,$nit_plantel,$nombre_plantel, $nombre_corto, $direccion_plantel,$ciudad_plantel);
              
               if($query){
              
                $response = json_encode(array("success" => true, "Información actualizada satisfactoriamente"));
              
                }else{
              
                $response = json_encode(array("success" =>false, "mensaje" => "No se ha podido actualizar la información."));
              
             }

       }

  break;
// fin case 

case "BuscarPorcentaje":
$buscapor=$p->BuscarPorcentaje();
$response=json_encode($buscapor);
break;


case "ContarRegistros":
$contar=$p->ContarRegistros();
$response=json_encode($contar);
break;


case'RegistrarParametros':


    $query = $p->RegistrarParametros($id_plantel,
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
                                     $DesdeNotaSuperior,
                                     $HastaNotaSuperior,
                                     $DesdeNotaAlto,
                                     $HastaNotaAlto);

          
   if($query){

        $response = json_encode(array("success" => true ,"Información registrada satisfactoriamente"));
            
       }else{
            
       $response = json_encode(array("success" =>false, "mensaje" => "No se ha podido actualizar la información."));
     }  



break;            

case 'ListarConceptos':
$concepto=$p->ListarConceptos();

       $lista = array();
        foreach($concepto as $data){
            array_push($lista,
                  array('id_par'=>$data['id_par'],
                         'concepto'=>$data['concepto'],
                         'porcentaje'=>$data['porcentaje'],
                         'nota_equivalente'=>$data['nota_equivalente']));
            }

           if(count($lista) != 0){
              $response = json_encode(array("success"=>true, 'lista'=>$lista));
          
            }else{
               $response = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron estudiantes registrados"));
            }

       break;


       //caso para el registro de los documentos
  case'RegistrarDocumento':
         if(isset($_POST['Rnombre_documento'])){  $nombre=strtoupper($_POST['Rnombre_documento']);}
         if(isset($_POST['Rarea_documento'])){    $area =  $_POST['Rarea_documento'];}
         if(isset($_POST['Restado_documento'])){  $estado =  $_POST['Restado_documento'];}

      $validar=$p->ValidarDocumento($nombre,$area);
      if ($validar==''){
         $query = $p->RegistrarDocumento($nombre,$area,$estado);
     
         if($query){
       
               $response = json_encode(array("success" => true ,"Información registrada satisfactoriamente"));
                   
              }else{
                   
              $response = json_encode(array("success" =>false, "mensaje" => "No se ha podido actualizar la información."));
            }  
      } else{
         $response = json_encode(array("success" =>false, "mensaje" => "Docuemto se encuentra registrado en el area académica"));
     

      }  

 
     break;       
     
   case 'ListarDocumentos':
     $tabla = $p->ListarDocumentos();
     $j=1;
         foreach($tabla as $datos => $data){
             if($data['estado_documento']=='on'){
                 $estado ='Habilitado';
             }else if($data['estado_documento']=='off'){
                 $estado ='Deshabilitado';
             }else{
                 $estado = 'Error';
             }
 
             $edit = '<div class="btn btn-sm btn-warning fa fa-pencil-square-o data-toggle="modal" 
             onclick="ModalEditarDocumento('.$data['id_documento'].', 
                                           \''.$data['nombre_documento'].'\',
                                           '.$data['id_documento_areafk'].',
                                           \''.$data['estado_documento'].'\')"> </div>';


            $clonar = '<div class="btn btn-sm btn-primary fa fa-legal data-toggle="modal" 
                                           onclick="ModalClonarDocumento('.$data['id_documento'].', 
                                                                         \''.$data['nombre_documento'].'\')"> </div>';
 
             $delete = '<div class="btn btn-sm btn-danger fa fa-trash-o" data-toggle="modal" data-target="modalconfirmar" onClick="FeEditEstado('.$data["id_documento"].',\'off\')"> </div>';
 
             $restore = '<div class="btn btn-sm btn-success glyphicon glyphicon-check" data-toggle="modal" data-target="modalconfirmar" onClick="FeEditEstado('.$data["id_documento"].', \'on\')"></div>';
             
             if($data["estado_documento"] == 'on'){
                  $opcion = $edit.' '.$delete .' '.$clonar;
               }else{
                  $opcion = $restore;
               }
             array_push($table['data'], array($j,
                                            $data['nombre_documento'],
                                            $data['nombre_area'],
                                            $estado,
                                            $opcion));
             $j++;
         }
         $response = json_encode($table);
     break;


 //caso para actualizar documentos
  case'UpdateDocumento':
   if(isset($_POST['txtnombre_documento'])){  $nombre= strtoupper($_POST['txtnombre_documento']);}
   if(isset($_POST['txtarea_documento'])){    $area =  $_POST['txtarea_documento'];}
   if(isset($_POST['txtestado_documento'])){  $estado =  $_POST['txtestado_documento'];}
   if(isset($_POST['txtid_documento'])){      $id =  $_POST['txtid_documento'];}

 $query = $p->UpdateDocumento($id,$nombre, $area, $estado);

 if($query){

       $response = json_encode(array("success" => true ,"Información registrada satisfactoriamente"));
           
      }else{
           
      $response = json_encode(array("success" =>false, "mensaje" => "No se ha podido actualizar la información."));
    }  
break;

//case para editar el estado de los documentos
case'EditarEstadoDocumento':
   $query = $p->EditarEstadoDocumento($codigo, $estado);
      if($query){
          $response = json_encode(array("success"=>true));
      }else{
          $response = json_encode(array("success" => false,"mensaje" => "No se ha podido actualizar la información. Por favor intentelo de nuevo"));
      }
break;



//caso para la clonacion de los documentos
case'ClonarDocumento':
         if(isset($_POST['Cnombre'])){  $nombre=strtoupper($_POST['Cnombre']);}
         if(isset($_POST['Carea'])){    $area =  $_POST['Carea'];}
    
      $validar=$p->ValidarDocumento($nombre,$area);
      if ($validar==''){
         $query = $p->ClonarDocumento($nombre,$area);
     
         if($query){
       
               $response = json_encode(array("success" => true ,"Información registrada satisfactoriamente"));
                   
              }else{
                   
              $response = json_encode(array("success" =>false, "mensaje" => "No se ha podido actualizar la información."));
            }  
      } else{
         $response = json_encode(array("success" =>false, "mensaje" => "Docuemto se encuentra registrado en el area académica"));
      }  
   
 
     break;   
   }

  echo $response;
?>
