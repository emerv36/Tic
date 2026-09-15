<?php
session_start();
date_default_timezone_set("America/Bogota");
header('content-type: application/json; charset=utf-8');
require('Models/Modulos.php');
$m= new Modulos ();
if(isset($_GET['case'])){ $case = $_GET['case'];}
if(isset($_GET['RSedeProgramaModulo'])){ $RSedeProgramaModulo = $_GET['RSedeProgramaModulo'];}
if(isset($_GET['sedes'])){ $sedes = $_GET['sedes'];
}

//variables editar estado de los módulos
    if(isset($_POST['codUsuario'])){ $codigo = $_POST['codUsuario'];}
    if(isset($_POST['estado']))    { $estado = $_POST['estado'];}
//fin variables editar estado de los módulos
//Variables para clonar todos lo modulos de un programa
    if(isset($_POST['sedeorigenmod'])){            $sedeorigenmod          =$_POST['sedeorigenmod']; }
    if(isset($_POST['sededestmod'])){              $sededestmod            =$_POST['sededestmod']; }
    if(isset($_POST['programamod'])){              $programamod            =$_POST['programamod']; }
    if(isset($_POST['programadestmod'])){          $programadestmod        =$_POST['programadestmod']; }
//Fin variables para clonar todos lo modulos de un programa    
//variables para registrar modulos
    if(isset($_POST['RcodigoModulo'])){$RcodigoModulo =strtoupper($_POST['RcodigoModulo']);}
    if(isset($_POST['RnombreModulo'])){$RnombreModulo = strtoupper($_POST['RnombreModulo']);}
    if(isset($_POST['Rversion'])){$Rversion =strtoupper($_POST['Rversion']);}
    if(isset($_POST['RintenModulo'])){$RintenModulo = $_POST['RintenModulo'];}
    if(isset($_POST['RSedeProgramaModulo'])){ $RSedeProgramaModulo = $_POST['RSedeProgramaModulo']; }
    if(isset($_POST['ProgramaModulo'])){ $ProgramaModulo = $_POST['ProgramaModulo'];}
    if(isset($_POST['RestadoModulo'])){ $RestadoModulo = $_POST['RestadoModulo'];}
//fin variables 

//variables para editar modulos
    if(isset($_POST['EIdModulo'])){$EIdModulo =$_POST['EIdModulo'];}
    if(isset($_POST['EcodigoModulo'])){ $EcodigoModulo =strtoupper($_POST['EcodigoModulo']); }
    if(isset($_POST['EnombreModulo'])){$EnombreModulo = strtoupper($_POST['EnombreModulo']);}
    if(isset($_POST['Eversion'])){$Eversion =strtoupper($_POST['Eversion']);}
    if(isset($_POST['EintenHoras'])){ $EintenHoras = $_POST['EintenHoras'];}
    if(isset($_POST['ESedeProgramaModulo'])){$ESedeProgramaModulo = $_POST['ESedeProgramaModulo'];}
    if(isset($_POST['EProgramaModulo'])){$EProgramaModulo = $_POST['EProgramaModulo'];}
    if(isset($_POST['EestadoModulo'])){$EestadoModulo = $_POST['EestadoModulo'];}
//fin variables para editar modulos

//variables para clonar modulo
   if (isset($_POST['CnombreModulo'])){       $CnombreModulo=$_POST['CnombreModulo'];}
   if (isset($_POST['Cversion'])){            $Cversion=$_POST['Cversion'];}
   if (isset($_POST['CintenHoras'])){         $CintenHoras=$_POST['CintenHoras'];}
   if (isset($_POST['CSedeProgramaModulo'])){ $CSedeProgramaModulo=$_POST['CSedeProgramaModulo'];}
   if (isset($_POST['CProgramaModulo'])){     $CProgramaModulo=$_POST['CProgramaModulo'];}
   if (isset($_POST['CestadoModulo'])){       $CestadoModulo=$_POST['CestadoModulo'];}
   //fin variables

$createtable = array(
    'data'=>array()
);
switch($case){
//case para listar todos los modulos
  case'ListarModulos':
    $table = $m->ListarModulos();
    $r = 1;
      foreach($table as $datarow => $data){
         if($data['estado_modulo']=='on'){
             $estado = 'Habilitado';
            }else if($data['estado_modulo']=='off'){
                $estado = 'Deshabilitado';
           }else{
            $estado = 'Error';
           }
           $edit = '<div class="btn btn-sm btn-warning fa fa-pencil-square-o data-toggle="modal" title="Editar" data-target="#editar-modulos"
            onclick="ModalEditarModulos('.$data['id_modulo'].',\''.$data['nombre_modulo'].'\',
                                      \''.$data['id_versionfk'].'\',\''.$data['intensidad_horas'].'\', 
                                      \''.$data['id_modulo_sedefk'].'\', \''.$data['id_modulo_programafk'].'\',
                                       \''.$data['estado_modulo'].'\')"></div>';


              $clonar = '<div class="btn btn-sm btn-primary fa fa-legal data-toggle="modal" title="Clonar modulo" data-target="#clonar-modulos"
            onclick="ModalClonarModulos('.$data['id_modulo'].',\''.$data['nombre_modulo'].'\',
                                        \''.$data['id_versionfk'].'\',\''.$data['intensidad_horas'].'\',
                                         \''.$data['id_modulo_programafk'].'\',
                                          \''.$data['estado_modulo'].'\')
            "></div>';


            $delete = '<div class="btn btn-sm btn-danger fa fa-trash-o"  data-toggle="modal" title="Desactiva" data-target="modalconfirmar" 
            onClick="ModalEditEstadoModulos('.$data["id_modulo"].',\'off\')"></div>';


            $restore = '<div class="btn btn-sm btn-success glyphicon glyphicon-check" data-toggle="modal" title="Activar" data-target="modalconfirmar"
             onClick="ModalEditEstadoModulos('.$data["id_modulo"].', \'on\')"></div>';

            if($data["estado_modulo"] == 'on'){
                 $opcion = $edit.' '.$delete.' '.$clonar;
              }else{
                 $opcion = $restore;
              }
           array_push($createtable['data'], array($r,
                                            ucwords(strtolower($data['nombre_modulo'])),
                                            ucwords(strtolower($data['nombre_version'])),
                                            ucwords(strtolower($data['intensidad_horas'])),
                                            ucwords(strtolower($data['nombre_programa'])),
                                            ucwords(strtolower($data['nombre_sede'])),
                                            $estado,$opcion));
        $r++;
      }
      $response = json_encode($createtable);
  break;
//fin case para listar todos los modulos
// case para editar el estado de los Modulos
    case'EditarEstadoModulos':
    $query = $m->EditarEstadoModulos($codigo, $estado);
    if($query){
        $response = json_encode(array("success"=>true));
    }else{
        $response = json_encode(array("success" => false,"mensaje" => "No se ha podido actualizar la información. Por favor intentelo de nuevo"));
    }
    break;
//fin case para editar el estado de los Modulos
//case para cargar las sedes
    case'CargarSedes':
        $sedes=$m->CargarSedes();
        $response=json_encode($sedes);
    break;
//fin case para cargar sedes
//case para cargar las sedes
    case'CargarVersion':
        $version=$m->CargarVersion();
        $response=json_encode($version);
    break;
//fin case para cargar sedes
//case para cargar todos los programas 
    case'TodosProgramas':
        $TodosProgramas=$m->CargarTodosProgramas();
        $response=json_encode($TodosProgramas);
    break;
//fin case para cargar todos los programas 
//case para cargar los programas en el combobox en el formulario para registrar modulos
    case'CargarProgramas':
         $programas=$m->CargarProgramas($sedes);
         $response=json_encode($programas);
    break;
//fin case para cargar programas en el combobox en el formulario para registrar modulos
    case'CargarSedes2':
        $sedes2=$m->CargarSedes2($sedes);
        $response=json_encode($sedes2);
    break;
//case para recibir el envio ajax para cargar programas de acuerdo a su sede

//case para registrar info módulos
  case'RegistrarInfoModulos':
     $RnombreModulo = trim($RnombreModulo);
     $ValidarModulos = $m->ValidarModulosProgramas($RnombreModulo,$ProgramaModulo);
     if($ValidarModulos ==''){
        $query = $m->RegistrarInfoModulos($RnombreModulo, 
                                          $Rversion, 
                                          $RintenModulo,
                                          $RSedeProgramaModulo,
                                          $ProgramaModulo, 
                                          $RestadoModulo);
            if($query){
                $response = json_encode(array("success"=>true));
            }else{
            $response = json_encode(array("success"=>false, "mensaje"=>"No se ha podido, ingresar la información. Intentelo de nuevo."));
            }
     }else{
        $response = json_encode(array("success"=>false, "mensaje"=>"El módulo que intenta ingresar,ya se encuentra encuentra registrado en este programa."));
     }
  break;
//fin case para registrar info módulos


//case para registrar info módulos
  case'ClonarModulos':
     $ValidarModulos = $m->ValidarModulosProgramas($CnombreModulo,$CProgramaModulo);
     if($ValidarModulos ==''){
        $query = $m->RegistrarInfoModulos($CnombreModulo,$Cversion, $CintenHoras,$CSedeProgramaModulo, $CProgramaModulo,$CestadoModulo);
          if($query){
                $response = json_encode(array("success"=>true));
          }else{
           $response = json_encode(array("success"=>false, "mensaje"=>"No se ha podido, ingresar la información. Intentelo de nuevo."));
          }
     }else{
        $response = json_encode(array("success"=>false, "mensaje"=>"El módulo que intenta ingresar,ya se encuentra encuentra registrado en este programa, verifique los párametros seleccionados"));
     }
  break;
//fin case para registrar info módulos

//case para actulizar info de los módulos
case'ActualizarInfoModulos':
      $query = $m->ActualizarInfoModulos($EIdModulo,$EnombreModulo,$Eversion,$EintenHoras,$ESedeProgramaModulo,$EProgramaModulo,$EestadoModulo);
         if($query){
           $response = json_encode(array("success"=>true));
         }else{
            $response = json_encode(array("success"=>false, "mensaje"=>"Error, no se ha podido actualizar la información. Intentelo de nuevo."));
         }
   break;

   case 'ClonarModulosProgramas':
    $codigo=$m->ObtenerCodigo($programamod);
    $codi=$codigo['codigo_programa'];
    $codigo=$m->ObtenerCodigo($programadestmod);
    $codi2=$codigo['codigo_programa'];
    if($codi==$codi2){
    $areas=$m->LlamarModulos($programamod);
    $conta=0; $conta2=0;
            foreach($areas as $data){
             $ValidarModulos = $m->ValidarModulosProgramas($data['nombre_modulo'],$programadestmod);
              if ($ValidarModulos==''){
                $query=$m->RegistrarInfoModulos($data['nombre_modulo'],$data['id_versionfk'],$data['intensidad_horas'],$sededestmod,$programadestmod,$data['estado_modulo']);
                if($query){
                  $conta=$conta+1;
                  }   
              }else{
                $conta2=$conta2+1;;
              }              
            }
            if($conta2==0 &&$conta==0){
              $response = json_encode(array("success"=>false, "mensaje" => "No se pudo realizar la clonación. Intentelo de nuevo"));
            }else{
              if($conta2!=0 &&$conta==0){
                $response = json_encode(array("success"=>false, "mensaje" => "Los Modulos que desea clonar ya existen, por favor verifique"));
              }else{
                if($conta2==0 &&$conta!=0){
                  $response = json_encode(array("success"=>true, "mensaje" => "Modulos clonados con exito"));
                }else{
                  $response = json_encode(array("success"=>true, "mensaje" => "Se clonaron $conta modulos, los otros $conta2 ya estaban registrados"));
                }
              }
            
            }
          }else{ $response = json_encode(array("success"=>false, "mensaje" => "Los programas poseen codigos diferentes, por favor verifique"));}
          
                 
  break;  
   
  case 'ListarModulosPorPrograma':
    $tabla=$m->ListarModulos();
    $registros=array();
      foreach($tabla as $archivos){
         if($programamod==$archivos['id_modulo_programafk']){
        array_push($registros, array(
          'id_modulo'=>$archivos['id_modulo'],
          'nombre_modulo'=>$archivos['nombre_modulo'],
          'nombre_version'=>$archivos['nombre_version'],
          'intensidad_horas'=>$archivos['intensidad_horas'],
          'nombre_programa'=>$archivos['nombre_programa'],
          'nombre_sede'=>$archivos['nombre_sede']));
        }
      }
      $response = json_encode(array("success"=>true, 'registros'=>$registros));
    
      break;

      case 'ListarModulosPorPrograma':
        $tabla=$m->ListarModulos();
        $registros=array();
          foreach($tabla as $archivos){
             if($programamod==$archivos['id_modulo_programafk']){
            array_push($registros, array(
              'codigo_programa'=>$archivos['codigo_programa']));
            }
          }
          $response = json_encode(array("success"=>true, 'registros'=>$registros, ));
        
          break;
//fin case para actulizar info de los módulos
}
echo  $response;

?>