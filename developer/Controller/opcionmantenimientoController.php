<?php
session_start();
date_default_timezone_set("America/Bogota");
header('content-type: application/json; charset=utf-8');
require('../Models/Opcionmantenimiento.php');

$man =  new Opcionmantenimiento();
if(isset($_GET['case'])){  $case = $_GET['case'];}

switch($case){

   //case para la carga de los curso por horaios, programas y sedes
case 'BuscarCargarAcademica':  
   if(isset($_POST['txtselectcurso'])){  $curso     =$_POST['txtselectcurso'];}
   if(isset($_POST['txtperiodo'])){      $periodo   =$_POST['txtperiodo'];}
  
   $lista=$man->BuscarCargarAcademica($periodo, $curso);    
   $listacurso = array();
   foreach($lista as $data){
      array_push($listacurso,
            array('id_carga'=>$data['id_carga'],
                 'id_curso_cargafk'=>$data['id_curso_cargafk'],
                 'codigo_curso'=>$data['codigo_curso'],
                 'id_sede_cursofk'=>$data['id_sede_cursofk'],
                 'nombre_sede'=>$data['nombre_sede'],
                 'nombre_horario'=>$data['nombre_horario'],
                 'dia_horario'=>$data['dia_horario'],  
                 'id_modulo_cargafk'=>$data['id_modulo_cargafk'],      
                 'nombre_modulo'=>$data['nombre_modulo'],     
                 'nombre_docente'=>$data['nombre_docente'],    
                 'apellido_docente'=>$data['apellido_docente'],  
                 'id_periodo_cargafk'=>$data['id_periodo_cargafk'],
                 'periodo'=>$data['periodo'], 
                 'fecha_carga'=>$data['fecha_carga'],      
                 'periodo'=>$data['periodo']));
      }
     if(count($listacurso) != 0){
        $json = json_encode(array("success"=>true, 'listacurso'=>$listacurso));
        }else{
       $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron carga académicas"));
     }
break;

case 'SelectCursosPeriodos':
   if(isset($_GET['selectperiodo'])){  $periodo=$_GET['selectperiodo'];}
   $resultado=$man->SelectCursosPeriodos($periodo);   
   $json = json_encode($resultado);
break;

case 'EliminarCargarAcademica':  
   if(isset($_POST['txtidcurso'])){      $txtcurso       =$_POST['txtidcurso'];}
   if(isset($_POST['txtidmodulo'])){     $txtmodulo      =$_POST['txtidmodulo'];}
   if(isset($_POST['txtidcarga'])){      $txtcarga       =$_POST['txtidcarga'];}
   if(isset($_POST['txtidperiodo'])){    $txtperiodo     =$_POST['txtidperiodo'];}

   $resultado=$man->ValidarCarga($txtcurso, $txtmodulo, $txtperiodo);   

   if ($resultado!=''){
      $json = json_encode(array("success"=>true, 'mensaje'=>"No se posible eliminar la carga academica, el móudlo presenta notas registradas"));
   }else{
       
         $query=$man->EliminarCargarAcademica($txtcarga);   
         $eliminar->EliminarAsistencia($txtcarga);
         if ($query!=''){
            $json = json_encode(array("success"=>true, 'mensaje'=>"Carga académica eliminada de manera exitosa"));            
             $eliminar->ElimininarDetalleCarga($txtcarga);  
         }else{
            $json = json_encode(array("success"=>true, 'mensaje'=>""));
         }
     

   }

   break;

 //case para eliminar inscripciones de estudiantes
 case 'EliminarModuloDuplicado':  
   if(isset($_POST['txtidcarga'])){ $txtidcarga=$_POST['txtidcarga'];}
      
        $resultado=$man->EliminarModuloDuplicado($txtidcarga);   

    if ($resultado!=''){
       $json = json_encode(array("success"=>true, 'mensaje'=>"Módulo eliminado satisfactoriamente"));
   
      }else{
          $json = json_encode(array("success"=>true, 'mensaje'=>"No es posible eliminar el módulo"));
     }
 break;

   case 'ListarMatriculas':
      if(isset($_POST['txtidentidadfolio'])){    $txtidentidad     =$_POST['txtidentidadfolio'];}
      $matriculas = $man->ListarMatriculas($txtidentidad);   
      $lista = array();
        foreach($matriculas as $data){
           array_push($lista,
                 array('identificacion'=>$data['identificacion'],
                       'id_matricula_inscripcionfk'=>$data['id_matricula_inscripcionfk'],
                        'nombre_estudiante'=> $data['nombre_estudiante'],
                        'apellido_estudiante'=>$data['apellido_estudiante'],
                        'email_estudiante'=>$data['email_estudiante'],
                        'celular_estudiante'=>$data['celular_estudiante'],
                        'periodo'=>$data['periodo'],
                        'nombre_sede'=>$data['nombre_sede'],
                        'nombre_programa'=>$data['nombre_programa'],
                        'nombre_horario'=>$data['nombre_horario'],
                        'dia_horario'=>$data['dia_horario'],
                        'estado_registro'=>$data['estado_registro'],
                        'periodo'=>$data['periodo'],
                        'fecha_inscripcion'=>$data['fecha_inscripcion'],
                        'id_inscripcion'=>$data['id_inscripcion']));
                      
           }
          if(count($lista) != 0){
             $json = json_encode(array("success"=>true, 'lista'=>$lista));
         
           }else{
              $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron registros"));
           }
   break;

   case 'ValidarEstudianteMatriculado':
    if(isset($_POST['Bidentidad'])){      $identidad     =$_POST['Bidentidad'];}
    if(isset($_POST['txtselectcurso'])){  $curso         =$_POST['txtselectcurso'];}
    if(isset($_POST['idmodulo'])){        $modulo         =$_POST['idmodulo'];}
    

    $buscar=$man->ValidarModuloMatriculado($modulo, $curso, $identidad);
    if ($buscar !=''){
      $json = json_encode(array("success"=>false, 'mensaje'=>'Estudiante con identidad No'.' '.$identidad.' ' .'presenta el módulo selecciodo en su record de módulos programados, debe seleccionar un módulo diferente'));

     }else{
      $validar = $man->ValidarEstudianteMatriculado($curso,$identidad);

      if ($validar !=''){

         $json = json_encode(array("success"=>true, "identidad"=>$validar['identificacion'],
                                                    "id_inscripcion"=>$validar['id_inscripcion'],
                                                    "nombre_estudiante"=>$validar['nombre_estudiante'],
                                                    "apellido_estudiante"=>$validar['apellido_estudiante'],
                                                    "nombre_programa"=>$validar['nombre_programa'],
                                                    "nombre_sede"=>$validar['nombre_sede'],
                                                    "id_sede_inscripcionfk"=>$validar['id_sede_inscripcionfk'],
                                                    "id_programa_inscripcionfk"=>$validar['id_programa_inscripcionfk'],                                                   
                                                    "nombre_horario"=>$validar['nombre_horario'],
                                                    "dia_horario"=>$validar['dia_horario']
                                                ));
       

    }else{
    
        $json = json_encode(array("success"=>false, 'mensaje'=>'Estudiante con identidad No'.' '.$identidad.' '.'no se encontró matriculado en el curso, seleccione periodo y curso diferente'));
    
    }

     }
    
  
   break;




   //case para la busqueda de modulos matriculados
   case 'BuscarEstudianteMatricula':
    if(isset($_POST['Bidentidad'])){      $identidad     =$_POST['Bidentidad'];}
    if(isset($_POST['txtselectcurso'])){  $curso        =$_POST['txtselectcurso'];}

    $validar = $man->ValidarEstudianteMatriculado($curso,$identidad);
    if ($validar !=''){
        $identidad=$validar['identificacion'];
         $resultado = $man->BuscarEstudianteMatricula($identidad);   
         $lista = array();
                foreach($resultado as $data){
                    array_push($lista,
                        array('identificacion'=>$data['identificacion'],
                                'nombre_estudiante'=> $data['nombre_estudiante'],
                                'apellido_estudiante'=>$data['apellido_estudiante'],
                                'nombre_modulo'=>$data['nombre_modulo'],
                                'periodo'=>$data['periodo'],
                                'codigo_curso'=>$data['codigo_curso'],
                                'nombre_docente'=>$data['nombre_docente'],
                                'apellido_docente'=>$data['apellido_docente'],
                                'id_detalle_carga_estfk'=>$data['id_detalle_carga_estfk'],
                                'id_detalle_modulofk'=>$data['id_detalle_modulofk'],
                                'id_detalle_periodofk'=>$data['id_detalle_periodofk'],
                                'id_detalle_cursofk'=>$data['id_detalle_cursofk'],
                                'id_detalle_cursofk'=>$data['id_detalle_cursofk'],
                                'id_detalle_cargafk'=>$data['id_detalle_cargafk']));
                                
                    }
                    if(count($lista) != 0){
                    $json = json_encode(array("success"=>true, 'lista'=>$lista));
                
                    }else{
                        $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron registros"));
                    }
        }else{
            $json = json_encode(array("success"=>false, 'mensaje'=>"Estdiante no se encuentra matriculado en el curso"));
        }
 break;

   //case para listar las matriculas
   case 'ListarEstudiantes':
      if(isset($_POST['txtidentidad'])){    $txtidentidad     =$_POST['txtidentidad'];}
      $inscritos = $man->ListarEstudiantes($txtidentidad);   
      $lista = array();
        foreach($inscritos as $data){
           array_push($lista,
                 array('identificacion'=>$data['identificacion'],
                      'nombre_estudiante'=> $data['nombre_estudiante'],
                      'apellido_estudiante'=>$data['apellido_estudiante'],
                      'email_estudiante'=>$data['email_estudiante'],
                      'celular_estudiante'=>$data['celular_estudiante'],
                      'periodo'=>$data['periodo'],
                      'nombre_sede'=>$data['nombre_sede'],
                      'nombre_programa'=>$data['nombre_programa'],
                      'nombre_horario'=>$data['nombre_horario'],
                      'dia_horario'=>$data['dia_horario'],
                      'estado_registro'=>$data['estado_registro'],
                      'periodo'=>$data['periodo'],
                      'fecha_inscripcion'=>$data['fecha_inscripcion'],
                      'id_inscripcion'=>$data['id_inscripcion']));
                      
           }
          if(count($lista) != 0){
             $json = json_encode(array("success"=>true, 'lista'=>$lista));
         
           }else{
              $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron registros"));
           }
   break;

   //cargar la lista de modulos vistos
   case 'ValidarModuloEstudiante':
    if(isset($_POST['idestudiante'])){   $txtestudiante   =$_POST['idestudiante'];}
    if(isset($_POST['idcurso'])){        $txtcurso      =$_POST['idcurso'];}
    $resultado = $man->ValidarModuloEstudiante($txtestudiante,$txtcurso);   
    $lista = array();
      foreach($resultado as $data){
         array_push($lista,
               array('id_detalle_cargafk'=>$data['id_detalle_cargafk'],
                     'id_detalle_modulofk'=> $data['id_detalle_modulofk'],
                     'periodo'=>$data['periodo'],
                     'nombre_modulo'=>$data['nombre_modulo'],
                     'codigo_curso'=>$data['codigo_curso']));
                    
         }
        if(count($lista) != 0){
           $json = json_encode(array("success"=>true, 'lista'=>$lista));
       
         }else{
            $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron módulos"));
         }
 break;


 
//revisar este caso para la funcion programada
 case 'ValidarModuloPensum':
   if(isset($_POST['idprograma '])){    $txtprograma   =$_POST['idprograma'];}
   if(isset($_POST['idmodulo'])){       $txtmodulo     =$_POST['idmodulo'];}
   if(isset($_POST['idsede'])){         $txtsede       =$_POST['idsede'];}

   $resultado = $man->ValidarModuloPensum($txtprograma,$txtmodulo, $txtsede);   
   $lista = array();
     foreach($resultado as $data){
        array_push($lista,
              array('id_pensum_modulofk'=>$data['id_pensum_modulofk'],
                    'nombre_modulo'=> $data['nombre_modulo'],
                    'id_pensum_sedefk'=>$data['id_pensum_sedefk'],
                    'id_pensum_programafk'=>$data['id_pensum_programafk']));
                   
        }
       if(count($lista) != 0){
          $json = json_encode(array("success"=>true, 'lista'=>$lista));
      
        }else{
           $json = json_encode(array("success"=>false, 'mensaje'=>"Módulo no valido para el programa"));
        }
break;

//validar el modulo si el estudiante lo dios en otro curso

case 'ValidarModuloOtroCurso':
   if(isset($_POST['Bidentidad'])){   $txtestudiante=$_POST['Bidentidad'];}
   if(isset($_POST['idmodulo'])){     $txtmodulo=$_POST['idmodulo'];}
   $resultado = $man->ValidarModuloOtroCurso($txtestudiante,$txtmodulo);   
  
   if ($resultado!=''){
      
         $json = json_encode(array("success"=>true, 'mensaje'=>"No es posible matricular el módulo ". $resultado['nombre_modulo']." "."se encontró registrado en el record del estudiante programdo en el curso ".$resultado['codigo_curso']." "."Périodo "." ".$resultado['periodo']." "."Seleccione un módulo diferente"));
        
         }else{
            $json = json_encode(array("success"=>false, 'mensaje'=>"Módulo no se encontro"));
         }
   
             
break; 

case 'TraerInfoEstudiante':
   if(isset($_POST['Bidentidad'])){   $txtestudiante=$_POST['Bidentidad'];}
   
   $info=$man->TraerInfoEstudiante($txtestudiante);
       if ($info!=''){
            $json = json_encode(array("success"=>true, "identidad"=>$info['identificacion'],
                                          "id_inscripcion"=>$info['id_inscripcion'],
                                          "nombre_estudiante"=>$info['nombre_estudiante'],
                                          "apellido_estudiante"=>$info['apellido_estudiante'],
                                          "nombre_programa"=>$info['nombre_programa'],
                                          "nombre_sede"=>$info['nombre_sede'],
                                          "id_sede_inscripcionfk"=>$info['id_sede_inscripcionfk'],
                                          "id_programa_inscripcionfk"=>$info['id_programa_inscripcionfk'],                                                   
                                          "nombre_horario"=>$info['nombre_horario'],
                                          "dia_horario"=>$info['dia_horario']
        ));
    
          }else{
            $json = json_encode(array("success"=>false, 'mensaje'=>"Módulo no se encontro"));
         }
                
break; 

//case para validar el modulo en el programa academico

case 'ValidarModuloPrograma':
   if(isset($_POST['idmodulo'])){   $txtmodulo=$_POST['idmodulo'];}
   if(isset($_POST['idprograma'])){ $txtprograma=$_POST['idprograma'];}
   if(isset($_POST['idsede'])){     $txtsede=$_POST['idsede'];}


    $validar=$man->ValidarModuloPrograma($txtsede, $txtprograma, $txtmodulo);

       if ($validar!=''){
            $json = json_encode(array("success"=>true, "modulo"=>$validar['nombre_modulo']));    
          }else{
            $json = json_encode(array("success"=>false, 'mensaje'=>"Módulo seleccionado en el curso no es válido para el plan de estudio del programa academico, seleccione un módulo diferente"));
         }
                
break; 

case 'HallarInfocarga':
   if(isset($_POST['idmodulo'])){   $modulo=$_POST['idmodulo'];}
   if(isset($_POST['txtperiodo'])){ $periodo=$_POST['txtperiodo'];}
   if(isset($_POST['txtselectcurso'])){   $curso=$_POST['txtselectcurso'];}

  $validar=$man->HallarInfocarga($modulo, $curso, $periodo);
 
    if ($validar!=''){
       $modulo=$validar['id_detalle_modulofk'];
       $periodo=$validar['id_detalle_periodofk'];
       $curso=$validar['id_detalle_cursofk'];
       $carga=$validar['id_detalle_cargafk'];
       $docente=$validar['id_detalle_docentefk'];

       $json = json_encode(array("success"=>true, "modulo"=>$modulo,"periodo"=>$periodo,"curso"=>$curso, "carga"=>$carga, "docente"=>$docente));    
      }else{
   
      $json = json_encode(array("success"=>false, 'mensaje'=>"Módulo seleccionado en el curso no es válido para el plan de estudio del programa academico, seleccione un módulo diferente"));
      }
                
break;

case 'BuscarModulosDuplicados':
   if(isset($_POST['Didentidad'])){   $txtestudiante=$_POST['Didentidad'];}
   
   $resultado = $man->BuscarModulosDuplicados($txtestudiante);   
   $lista = array();
     foreach($resultado as $data){
        array_push($lista,
              array('id_detalle'=>$data['id_detalle'],
                    'id_detalle_cargafk'=> $data['id_detalle_cargafk'],
                    'periodo'=>$data['periodo'],
                    'nombre_modulo'=>$data['nombre_modulo'],
                    'codigo_curso'=>$data['codigo_curso'],
                    'nombre_horario'=>$data['nombre_horario'],
                    'nombre_sede'=>$data['nombre_sede'],
                    'dia_horario'=>$data['dia_horario'],
                    'nombre_docente'=>$data['nombre_docente'],
                    'apellido_docente'=>$data['apellido_docente'],
                    'fecha_detalle_carga'=>$data['fecha_detalle_carga'],
                    'id_detalle_carga_estfk'=>$data['id_detalle_carga_estfk'],
                    'id_detalle_modulofk'=>$data['id_detalle_modulofk'],
                    'id_detalle_cursofk'=>$data['id_detalle_cursofk']
                  ));
                   
          }
            if(count($lista) != 0){
               $json = json_encode(array("success"=>true, 'lista'=>$lista));
            
            }else{
               $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron módulos"));
            }
                
break; 


//informacion estudiante modulo duplicado
case 'InfoEstudiante':
   if(isset($_POST['Didentidad'])){   $txtestudiante=$_POST['Didentidad'];}
   
   $info=$man->TraerInfoEstudiante($txtestudiante);
       if ($info!=''){
            $json = json_encode(array("success"=>true, "identidad"=>$info['identificacion'],
                                          "id_inscripcion"=>$info['id_inscripcion'],
                                          "nombre_estudiante"=>$info['nombre_estudiante'],
                                          "apellido_estudiante"=>$info['apellido_estudiante'],
                                          "nombre_programa"=>$info['nombre_programa'],
                                          "nombre_sede"=>$info['nombre_sede'],
                                          "id_sede_inscripcionfk"=>$info['id_sede_inscripcionfk'],
                                          "id_programa_inscripcionfk"=>$info['id_programa_inscripcionfk'],                                                   
                                          "nombre_horario"=>$info['nombre_horario'],
                                          "dia_horario"=>$info['dia_horario']
        ));
    
          }else{
            $json = json_encode(array("success"=>false, 'mensaje'=>"Módulo no se encontro"));
         }
                
break; 

case 'GuardarCargarModulo':
   if(isset($_POST['idmodulo'])){         $idmodulo=$_POST['idmodulo'];}
   if(isset($_POST['txtperiodo'])){       $idperiodo=$_POST['txtperiodo'];}
   if(isset($_POST['txtselectcurso'])){   $idcurso=$_POST['txtselectcurso'];}
   if(isset($_POST['iddocente'])){        $iddocente=$_POST['iddocente'];}
   if(isset($_POST['idcarga'])){          $idcarga=$_POST['idcarga'];}
   if(isset($_POST['idestudiante'])){     $idestudiante=$_POST['idestudiante'];}
   

  $resultado=$man->GuardarCargarModulo($idcarga, $idestudiante, $idmodulo, $idperiodo, $idcurso, $iddocente);

  if ($resultado!=''){
     
      $json = json_encode(array("success"=>true, "mensaje"=>"¡Módulo asignado!"));    
      
      }else{
   
      $json = json_encode(array("success"=>false, 'mensaje'=>"error, no fue posible asignar el modul"));
      }

}
echo $json;
?>