<?php
session_start();
date_default_timezone_set("America/Bogota");
header('content-type: application/json; charset=utf-8');
require('../Models/OtrasOpciones.php');


//configuración y llamado de la libreria phpmailer, para el envio de correos
require("../phpmailer/class.phpmailer.php");
require("../phpmailer/class.smtp.php");



$op =  new OtrasOpciones();
if(isset($_GET['case'])){  $case = $_GET['case'];  }


//configuracion del email
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->SMTPKeepAlive = true; 
$mail->SMTPSecure = "tls";
$mail->SMTPDebug  = 0;
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->Username = "info@scv.edu.co";
$mail->Password = str_replace(' ', '', "wcdt hhon vvku zzlg");
$mail->Charset='utf-8';
$mail->SetFrom('info@scv.edu.co',utf8_decode('System Center ¡Módulo Programado!'));
$mail->AltBody = "";

switch($case){

//case para la carga de los curso por horaios, programas y sedes
case 'ConsultaRecordModuloEstudiante':  
   if(isset($_POST['txtidentidad'])){  $identidad=$_POST['txtidentidad'];}
  
    $resulado=$op->ConsultaRecordModuloEstudiante($identidad);    
    $lista = array();
    foreach($resulado as $data){
      array_push($lista,
            array('id_inscripcion'=>$data['id_inscripcion'],
                 'identificacion'=>$data['identificacion'],
                 'nombre_estudiante'=>$data['nombre_estudiante'],
                 'apellido_estudiante'=>$data['apellido_estudiante'],
                 'id_detalle_modulofk'=>$data['id_detalle_modulofk'],
                 'nombre_modulo'=>$data['nombre_modulo'],
                 'codigo_curso'=>$data['codigo_curso'],  
                 'periodo'=>$data['periodo'],      
                 'fechainicio'=>$data['fechainicio'],     
                 'fechafin'=>$data['fechafin'],    
                 'nombre_docente'=>$data['nombre_docente'],  
                 'apellido_docente'=>$data['apellido_docente'],
                 'id_horario_cursofk'=>$data['id_horario_cursofk'], 
                 'fecha_carga'=>$data['fecha_carga'],      
                 'nombre_horario'=>$data['nombre_horario'],
                 'dia_horario'=>$data['dia_horario'],
                 'id_sede_cursofk'=>$data['id_sede_cursofk'],
                 'nombre_sede'=>$data['nombre_sede'],
                 'id_detalle_cargafk'=>$data['id_detalle_cargafk'],
                 'id_detalle_periodofk'=>$data['id_detalle_periodofk'],
                 'nombre_programa'=>$data['nombre_programa'],
                 'id_detalle'=>$data['id_detalle'],
                 'estado_carga'=>$data['estado_carga']));
      }
     if(count($lista) != 0){
        $json = json_encode(array("success"=>true, 'lista'=>$lista));
    
      }else{
         $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron carga académicas"));
      }
break;

 //case para eliminar inscripciones de estudiantes
 case 'EliminarModulo':  
   if(isset($_POST['txtidetalle'])){ $txtidetalle=$_POST['txtidetalle'];}

        $resultado=$op->EliminarModulo($txtidetalle);   
      

    if ($resultado!=''){
       $json = json_encode(array("success"=>true, 'mensaje'=>"Módulo retirado satisfactoriamente"));
      
      }else{
          $json = json_encode(array("success"=>true, 'mensaje'=>"No es posible retirar el módoulo"));
     }
 break;


 //case para consulta de estudiantes para matricula individual
 case 'ConsultaEstudiantesModuloAdicional':  
   if(isset($_POST['txtidentidadbuscar'])){  $identidad          =$_POST['txtidentidadbuscar'];}
  
    $resulado=$op->ConsultaEstudiantesModuloAdicional($identidad);    
    $lista = array();
    foreach($resulado as $data){
      array_push($lista,
            array('id_inscripcion'=>$data['id_inscripcion'],
                 'identificacion'=>$data['identificacion'],
                 'nombre_estudiante'=>$data['nombre_estudiante'],
                 'apellido_estudiante'=>$data['apellido_estudiante'],
                 'id_detalle_modulofk'=>$data['id_detalle_modulofk'],
                 'nombre_modulo'=>$data['nombre_modulo'],
                 'codigo_curso'=>$data['codigo_curso'],  
                 'periodo'=>$data['periodo'],      
                 'fechainicio'=>$data['fechainicio'],     
                 'fechafin'=>$data['fechafin'],    
                 'nombre_docente'=>$data['nombre_docente'],  
                 'apellido_docente'=>$data['apellido_docente'],
                 'id_horario_cursofk'=>$data['id_horario_cursofk'], 
                 'fecha_carga'=>$data['fecha_carga'],      
                 'nombre_horario'=>$data['nombre_horario'],
                 'dia_horario'=>$data['dia_horario'],
                 'nombre_sede'=>$data['nombre_sede'],
                 'id_detalle_cargafk'=>$data['id_detalle_cargafk'],
                 'id_detalle'=>$data['id_detalle'],
                 'id_detalle_periodofk'=>$data['id_detalle_periodofk'],
                 'id_programa_inscripcionfk'=>$data['id_programa_inscripcionfk'],
                 'id_sede_inscripcionfk'=>$data['id_sede_inscripcionfk'],
                 'nombre_programa'=>$data['nombre_programa'],
                 'estado_carga'=>$data['estado_carga']));
      }
     if(count($lista) != 0){
        $json = json_encode(array("success"=>true, 'lista'=>$lista));
    
      }else{
         $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron carga académicas"));
      }
break;


//funcion para listar los estudiantes cruzados de los cursos
case 'ListarEstudiantesCruzados':
   $est=$op->ListarEstudiantesCruzados();     
    $lista = array();
         foreach($est as $data){
         array_push($lista,
               array('id_estudiante_matricula_detallefk'=>$data['id_estudiante_matricula_detallefk'],
                      'identificacion'=>$data['identificacion'],
                     'apellido_estudiante'=> $data['apellido_estudiante'],
                      'nombre_estudiante'=>$data['nombre_estudiante'],
                      'descripcion_convenio'=>$data['descripcion_convenio'],
                      'nombre_sede'=>$data['nombre_sede'],
                      'nombre_horario'=>$data['nombre_horario'],
                      'dia_horario'=>$data['dia_horario'],
                      'nombre_programa'=>$data['nombre_programa'],
                      'id_cursofk'=>$data['id_cursofk'],
                  //    'id_curso_cambiofk'=>$data['id_curso_cambiofk'],
                      'id_sede_inscripcionfk'=>$data['id_sede_inscripcionfk'],
                      'id_programa_inscripcionfk'=>$data['id_programa_inscripcionfk'],
                      'id_horario_inscripcionfk'=>$data['id_horario_inscripcionfk'],
                      'id_periodo_inscripcionfk'=>$data['id_periodo_inscripcionfk'],
                      'periodo'=>$data['periodo'],
                      'periodo_cruce'=>$data['periodo_cruce'],
                      'codigo_curso'=>strtoupper($data['codigo_curso'])));
         }
        if(count($lista) != 0){
           $json = json_encode(array("success"=>true, 'lista'=>$lista));
       
         }else{
            $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron estudiantes con mòdulos cruzados"));
         }
break;


//funcion para la carga de pensum de estudiantes a mover
case 'CargarPensumMoverCruzados':  

   if(isset($_POST['Fmidprograma'])){  $Rprograma= $_POST['Fmidprograma'];} 
   if(isset($_POST['Fmidsede'])){      $Rsede = $_POST['Fmidsede'];} 

   $modulos=$op->CargarPensumMoverCruzados($Rsede,$Rprograma);     
   $pensum = array();
   foreach($modulos as $data){
     array_push($pensum,
           array('id_pensum_modulofk'=>$data['id_pensum_modulofk'],
                'nombre_modulo'=>$data['nombre_modulo'],
                'nombre_version'=>$data['nombre_version'],
                'int_horas_pensum'=>$data['int_horas_pensum'],
                'id_modulo'=>$data['cod']));
     }
    if(count($pensum) != 0){
       $json = json_encode(array("success"=>true, 'pensum'=>$pensum));
   
     }else{
        $json = json_encode(array("success"=>false, 'mensaje'=>"¡No se encontraron módulos!"));
     }
break;


//funcion para la carga de pensum de estudiantes para matricula de modulo adicional
case 'CargarPensumEstudiantes':  

   if(isset($_POST['infoidprograma'])){  $Rprograma= $_POST['infoidprograma'];} 
   if(isset($_POST['infoidsede'])){      $Rsede = $_POST['infoidsede'];} 

   $modulos=$op->CargarPensumEstudiantes($Rsede,$Rprograma);     
   $pensum = array();
   foreach($modulos as $data){
     array_push($pensum,
           array('id_pensum_modulofk'=>$data['id_pensum_modulofk'],
                'nombre_modulo'=>$data['nombre_modulo'],
                'nombre_version'=>$data['nombre_version'],
                'int_horas_pensum'=>$data['int_horas_pensum'],
                'id_modulo'=>$data['cod']));
     }
    if(count($pensum) != 0){
       $json = json_encode(array("success"=>true, 'pensum'=>$pensum));
   
     }else{
        $json = json_encode(array("success"=>false, 'mensaje'=>"¡No se encontraron módulos!"));
     }
break;




//funcion para la carga de modulos programados por el estudiante cruzado
case 'CargarRecordModulosEstudianteCruzados':
   if(isset($_POST['Fmidestudiante'])){ $estudiante=$_POST['Fmidestudiante'];} 
   if(isset($_POST['Fmidprograma'])){ $programa=$_POST['Fmidprograma'];} 
   $est=$op->CargarModulosVistosEstudianteMover($estudiante,$programa);     
    $lista = array();
         foreach($est as $data){
         array_push($lista,
               array('id_modulo_cargafk'=>$data['id_modulo_cargafk'],
                     'nombre_modulo'=>$data['nombre_modulo'],
                     'periodo'=> $data['periodo'],
                     'id_programa_inscripcionfk'=> $data['id_programa_inscripcionfk'],
                     'codigo_curso'=>$data['codigo_curso'],));
         }
        if(count($lista) != 0){
           $json = json_encode(array("success"=>true, 'lista'=>$lista));
       
         }else{
           
            $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontró record de módulos"));
         }
break;


//funcion para la carga de modulos programados por el estudiante cruzado
case 'CargarRecordModulosEstudianteModuloAdicional':
   if(isset($_POST['infoidestudiante'])){ $estudiante=$_POST['infoidestudiante'];} 
   if(isset($_POST['infoidprograma'])){   $programa=$_POST['infoidprograma'];} 
   $est=$op->CargarRecordModulosEstudianteModuloAdicional($estudiante,$programa);     
    $lista = array();
         foreach($est as $data){
         array_push($lista,
               array('id_modulo_cargafk'=>$data['id_modulo_cargafk'],
                     'nombre_modulo'=>$data['nombre_modulo'],
                     'periodo'=> $data['periodo'],
                     'id_programa_inscripcionfk'=> $data['id_programa_inscripcionfk'],
                     'codigo_curso'=>$data['codigo_curso'],));
         }
        if(count($lista) != 0){
           $json = json_encode(array("success"=>true, 'lista'=>$lista));
       
         }else{
           
            $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontró record de módulos"));
         }
break;


//case para cargar periodos para mover los estudiantes cruzados
case 'CargarPeriodoMoverEstudiantesCruzado':
   $est=$op->CargarPeriodoMoverEstudiantesCruzado();   
   $json=json_encode($est);  
  break;


  
//funcion para la carga de curso para mover estudiantes cruzados en igual horario 
case 'CargarCursosMoverCruzados':
   if(isset($_GET['Fmperiodoinicio'])){  $periodo=$_GET['Fmperiodoinicio'];} 
   if(isset($_GET['Fmcurso'])){  $curso=$_GET['Fmcurso'];} 
 
 $est=$op->CargarCursosMoverCruzados($periodo,$curso);   
   $json=json_encode($est);  
  break;


//buscar sede programa estudiantes cruzados
case 'BuscarSedeProgramaCruzados':
   if(isset($_POST['Fmcursobuscar'])){      $Rcurso = $_POST['Fmcursobuscar'];} 
            $resultado=$op->BuscarSedeProgramaCruzados($Rcurso);
            $sede=$resultado['id_sede_cursofk'];
            $programa=$resultado['id_programa_cursofk'];
            $horario=$resultado['nombre_horario'];
            $dia=$resultado['dia_horario'];
            $nombre_programa=$resultado['nombre_programa'];
            $nombre_sede=$resultado['nombre_sede'];

   $json = json_encode(array("success"=>true,"sede"=>$sede,"programa"=>$programa,"horario"=>$horario,"dia"=>$dia,"nombre_programa"=>$nombre_programa, "nombre_sede"=>$nombre_sede));
break; 

//case para la carga de los pensum para los cursos a mover
case 'CargarPensumCursoMover':  
   if(isset($_POST['Fmsedecurso'])){$Rsede=$_POST['Fmsedecurso'];}
   if(isset($_POST['Fmprogramacurso'])){$Rprograma=$_POST['Fmprogramacurso'];}

   $modulos=$op->CargarPensumCursoMover($Rsede,$Rprograma);     
     $pensum = array();
     foreach($modulos as $data){
     array_push($pensum,
           array('id_pensum_modulofk'=>$data['id_pensum_modulofk'],
                'nombre_modulo'=>$data['nombre_modulo'],
                'tipo_modulo'=>$data['tipo_modulo'],
                'nombre_version'=>$data['nombre_version'],
                'int_horas_pensum'=>$data['int_horas_pensum'],
                'id_modulo'=>$data['cod']));
     }
    if(count($pensum) != 0){
       $json = json_encode(array("success"=>true, 'pensum'=>$pensum));
   
     }else{
        $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron módulos para el programa seleccionado"));
     }
break; 

//funcion para la carga de estudiantes para mover
case 'ListarEstudianteCursoMover':
   $Rcurso = $_POST['Fmcursobuscar'];
   $est=$op->ListarEstudianteCursoMover($Rcurso);     
    $lista = array();
         foreach($est as $data){
         array_push($lista,
               array('id_estudiante_matricula_detallefk'=>$data['id_estudiante_matricula_detallefk'],
                    'identificacion'=>$data['identificacion'],
                    'apellido_estudiante'=> $data['apellido_estudiante'],
                    'nombre_estudiante'=>$data['nombre_estudiante'],
                    'email_estudiante'=>$data['email_estudiante'],
                    'descripcion_convenio'=>$data['descripcion_convenio'],
                    'nombre_sede'=>$data['nombre_sede'],
                    'nombre_horario'=>$data['nombre_horario'],
                    'nombre_programa'=>$data['nombre_programa'],
                    'dia_horario'=>$data['dia_horario'],
                    'periodo'=>$data['periodo'],
                    'id_periodo_inscripcionfk'=>$data['id_periodo_inscripcionfk'],
                    'id_horario_inscripcionfk'=>$data['id_horario_inscripcionfk'],
                    'id_cursofk'=>$data['id_cursofk'],
                    'codigo_curso'=>strtoupper($data['codigo_curso'])));
         }
        if(count($lista) != 0){
           $json = json_encode(array("success"=>true, 'lista'=>$lista));
       
         }else{
            $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontraron estudiantes matriculados en el curso seleccionado"));
         }
break;


   //buscar ultimo modulo programado del curso donde se mueve los estudiantes cruzados
   case 'BuscarUltimoModuloProgramadoCursoMover':
      if(isset($_POST['Fmcursobuscar'])){       $Rcurso=$_POST['Fmcursobuscar'];} 
     
        $est=$op->BuscarUltimoModuloProgramadoCursoMover($Rcurso);     
         $lista = array();
              foreach($est as $data){
              array_push($lista,
                    array('id_curso_cargafk'=>$data['id_curso_cargafk'],
                          'id_sede_cursofk'=>$data['id_sede_cursofk'],
                          'id_programa_cursofk'=>$data['id_programa_cursofk'],
                          'id_modulo'=>$data['id_modulo'],
                          'nombre_modulo'=>$data['nombre_modulo'],
                          'periodo'=> $data['periodo'],
                          'tipo'=>$data['tipo']));
              }
             if(count($lista) != 0){
                $json = json_encode(array("success"=>true, 'lista'=>$lista));
            
              }else{
                 $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontró record de módulos"));
              }
     break;
  

     

//funcion para la busqueda de la informacion del detalle del modulo programado cuando se activa la matricula
case'ValidarMoverEstudianteCruzado': 
   if(isset($_POST['curso'])){    $curso=$_POST['curso'];} 
   if(isset($_POST['periodo'])){   $periodo =$_POST['periodo'];} 
    $buscar=$op->ValidarMoverEstudianteCruzado($curso,$periodo);
  
   if($buscar){ 
      $idcarga=$buscar['id_carga'];
      $iddocente=$buscar['id_docente_cargafk'];
      $idmodulo=$buscar['id_modulo_cargafk'];
      $idperiodo=$buscar['id_periodo_cargafk'];
      $modulo=$buscar['nombre_modulo'];
    
      $json = json_encode(array("success"=>true,"carga"=>$idcarga,"docente"=>$iddocente, "modulo"=>$idmodulo, "encontrado"=>"1", "nombre_modulo"=>$modulo,"periodo"=>$idperiodo));           
      }else{
       $json = json_encode(array("success"=>false, "encontrado" => "0", "nombre_modulo"=>""));
      }
break;


// case para validar el modulo programado al mover el estudiante nuevo
case 'ValidarModuloValidoMoverEstudianteCruzado':
   if(isset($_POST['Fmidsede'])){     $idsede=$_POST['Fmidsede'];} 
   if(isset($_POST['Fmidprograma'])){ $idprograma=$_POST['Fmidprograma'];} 
   if(isset($_POST['FMmodulo'])){     $idmodulo=$_POST['FMmodulo'];} 

   $validar=$op->ValidarModuloValidoMoverEstudianteCruzado($idmodulo, $idsede, $idprograma);
   if ($validar!=''){
      $json = json_encode(array("success"=>true, "mensaje" => "Módulo en común es valido","sede"=>$idsede, "programa"=>$idprograma, "Modulo"=>$idmodulo));
        }else{
         $json = json_encode(array("success"=>false, "mensaje" => "No es posible mover el estudiante de curso, el módulo programado en el curso destino en el périodo reciente no es común para su plan de estudio, seleccione un curso diferente"));
 
      }
break;   

//funcion para la carga de curso para mover estudiantes cruzados en igual horario 
case 'CargarModulosProgramadosCursoDestino':
   if(isset($_GET['fcursos'])){ $curso=$_GET['fcursos'];} 
   if(isset($_GET['fperiodos'])){ $periodo=$_GET['fperiodos'];}
 //  $periodo = $periodo + 1; 
   $resultado=$op->CargarModulosProgramadosCursoDestino($curso,$periodo);   
   $json=json_encode($resultado);  
  break;


  //funcion para guardar el estudiante de cruzado a normal en un curso validado
case 'GuardarMoverEstudianteCruzado':
  //variables locales 
   if(isset($_POST['Fmidestudiante'])){   $estudiante=$_POST['Fmidestudiante'];}
   if(isset($_POST['Fmcursocambio'])){    $curso=$_POST['Fmcursocambio'];}
   if(isset($_POST['Fmprogramacurso'])){  $programa=$_POST['Fmprogramacurso'];}
   if(isset($_POST['Fmidsede'])){         $sede=$_POST['Fmidsede'];}
   if(isset($_POST['moduloprogramado'])){ $Rmodulo=$_POST['moduloprogramado'];}
   if(isset($_POST['Fmperiodocruce'])){   $idperiodo=$_POST['Fmperiodocruce'];}
   if(isset($_POST['FMdocente'])){        $iddocente=$_POST['FMdocente'];}
   if(isset($_POST['FMcarga'])){          $idcarga=$_POST['FMcarga'];}
 
  $query=$op->GuardarMoverEstudianteCruzado($estudiante,$curso, $programa);
            if ($query!=''){
                  $nperiodo=$idperiodo + 1; // periodo siguiente del periodo cruce
                  $resultado=$op-> GuardarEstudianteDetalleCargaCruzado($idcarga, $estudiante, $Rmodulo, $nperiodo, $curso, $iddocente);
                  $resultado=$op->AbrirGrupoCruzado($curso);
                  $json = json_encode(array("success"=>true,"mensaje"=>"¡Módulo matriculado!"));
            }else{
               $json = json_encode(array("success"=>false, "mensaje" => ""));
            }
    break;



//funcion para la carga de modulos vistos por estudiantes 
case 'CargarRecordModulosEstudiante':
   if(isset($_POST['Restudiante'])){ $estudiante=$_POST['Restudiante'];} 
   $est=$amd->CargarRecordModulosEstudiante($estudiante);     
    $lista = array();
         foreach($est as $data){
         array_push($lista,
               array('id_modulo_cargafk'=>$data['id_modulo_cargafk'],
                     'nombre_modulo'=>$data['nombre_modulo'],
                      'periodo'=> $data['periodo'],
                      'codigo_curso'=>$data['codigo_curso'],));
         }
        if(count($lista) != 0){
           $json = json_encode(array("success"=>true, 'lista'=>$lista));
       
         }else{
           
            $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontró record de módulos"));
         }
break;


// case para validar el modulo programado al mover el estudiante nuevo
case 'ValidarModuloAdicional':
   if(isset($_GET['estudiantes'])){     $idestudiante=$_GET['estudiantes'];} 
   if(isset($_GET['modulos'])){         $idmodulo=$_GET['modulos'];} 
 
   $validar=$op->ValidarModuloAdicional($idmodulo, $idestudiante);
   if ($validar!=''){
      $nombre =$validar['nombre_estudiante'];
      $apellido =$validar['apellido_estudiante'];
      $modulo =$validar['nombre_modulo'];
      $periodo =$validar['periodo'];
           
      $json = json_encode(array("success"=>true, "mensaje" => "Módulo"." ".$modulo. " " ."se encuentra en el record de módulos para el estudiante"." ".$nombre." ".$apellido." "."programado en el périodo"." ".$periodo." ".",Seleccione un módulo diferente"));
       
       }else{
       
         $json = json_encode(array("success"=>false, "mensaje" => "Buscando Modulos..."));
 
      }
break;   

//case para la busqueda del modulo adicional

case 'BuscarModuloAdicional':
   if(isset($_GET['infomodulo'])){     $idmodulo=$_GET['infomodulo'];} 
   if(isset($_GET['infoperiodo'])){    $idperiodo=$_GET['infoperiodo'];} 
   $est=$op->BuscarModuloAdicional($idmodulo, $idperiodo);     
    $lista = array();
         foreach($est as $data){
         array_push($lista,
               array('id_carga'=>$data['id_carga'],
                     'id_curso_cargafk'=>$data['id_curso_cargafk'],
                     'codigo_curso'=> $data['codigo_curso'],
                     'id_modulo_cargafk'=>$data['id_modulo_cargafk'],
                     'nombre_modulo'=>$data['nombre_modulo'],
                     'id_periodo_cargafk'=>$data['id_periodo_cargafk'],
                     'periodo'=>$data['periodo'],
                     'fechainicio'=>$data['fechainicio'],
                     'fechafin'=>$data['fechafin'],
                     'nombre_sede'=>$data['nombre_sede'],
                     'nombre_horario'=>$data['nombre_horario'],
                     'dia_horario'=>$data['dia_horario'],
                     'id_docente_cargafk'=>$data['id_docente_cargafk'],
                     'nombre_programa'=>$data['nombre_programa'],
                     'nombre_docente'=>$data['nombre_docente'],
                     'apellido_docente'=>$data['apellido_docente']));
          }
        if(count($lista) != 0){
           $json = json_encode(array("success"=>true, 'lista'=>$lista));
       
         }else{
            $json = json_encode(array("success"=>false, 'mensaje'=>"¡Búsqueda sin resultados, seleccione un módulo diferente!"));
         }
break;

 //case para eliminar inscripciones de estudiantes
 case 'GuardarModuloAdicional':  
   
   if(isset($_POST['MMcarga'])){          $carga=$_POST['MMcarga'];} 
   if(isset($_POST['MMestudiante'])){     $estudiante=$_POST['MMestudiante'];} 
   if(isset($_POST['MMmodulo'])){         $modulo=$_POST['MMmodulo'];} 
   if(isset($_POST['MMperiodo'])){        $periodo=$_POST['MMperiodo'];} 
   if(isset($_POST['MMcurso'])){          $curso=$_POST['MMcurso'];} 
   if(isset($_POST['MMdocente'])){        $docente=$_POST['MMdocente'];} 

   $resultado = $op->GuardarModuloAdicional($carga,$estudiante, $modulo, $periodo, $curso, $docente);
   

    if ($resultado!=''){
       $json = json_encode(array("success"=>true, 'mensaje'=>"Módulo matriculado"));
            $op->MatriculaCursoAdicional($estudiante, $curso);
       
      }else{
          $json = json_encode(array("success"=>true, 'mensaje'=>"No es posible matricular el módulo"));
     }
 break;
//caso para envio de email modulo adicional programado
// case para validar el modulo programado al mover el estudiante nuevo
case 'EnviarEmailMatriculaModuloAdicional':
   if(isset($_POST['MMestudiante'])){     $idestudiante=$_POST['MMestudiante'];} 
   if(isset($_POST['MMmodulo'])){         $idmodulo=$_POST['MMmodulo'];} 
   if(isset($_POST['MMperiodo'])){        $idperiodo=$_POST['MMperiodo'];} 

   $resultado=$op->EnviarEmailMatriculaModuloAdicional($idmodulo,$idperiodo,$idestudiante);
            $txtnombre=$resultado['nombre_estudiante'].' '.$resultado['apellido_estudiante'];
            $txtcorreo= $resultado['email_estudiante']; 
            $txtdocente=$resultado['nombre_docente'].' '.$resultado['apellido_docente'];
            $txtwatshap=$resultado['link_whatsaap_curso'];
            $txtcurso=$resultado['codigo_curso'];
            $txtperiodo=$resultado['periodo'];
            $txthorario=$resultado['nombre_horario'];
            $txtmodulo=$resultado['nombre_modulo'];
            $txtfecha=$resultado['fecha_detalle_carga'];
            $txtsede=$resultado['nombre_sede'];
            $txtdia=$resultado['dia_horario'];
 
            $html = "<!DOCTYPE html>";
            $html .= "<html>";
            $html .= "<head>";
            $html .="<body>";
            //$html .= '<img src="https://scv.edu.co/portal/wp-content/uploads/2023/03/Mesa-de-trabajo-2.png" style="height: 75px"/><br>';
            $html .= '<img src="https://scv.edu.co/portal/wp-content/uploads/2020/11/logo-hotizontal.png" alt="" style="height: 75px" /><br>';                      
            $html .= 'Hola:<strong>,'.ucwords(strtolower($txtnombre)).' '.'</strong>El proceso de servicio educativo<strong>SYSTEM CENTER</strong> informa que tienes un nuevo módulo programado<br>';
            $html .= ' <br>';
            $html .= '<hr>';
            $html .= '<ul>';
            $html .= '<li><b>Curso:</b>'.' '.$txtcurso;
            $html .= '<li><b>Périodo académico:</b>'.' '.$txtperiodo;
            $html .= '<li><b>Sede/horario:</b>'.' '.$txtsede. '/'.$txthorario .'/' .$txtdia;
            $html .= '<li><b>Módulo programado:</b>'.' '.$txtmodulo;
            $html .= '<li><b>Nombre docente:</b>'.' '.$txtdocente;
            $html .= '</ul>';
            $html .= '<hr>';
            $html .= '<ul>';                  
            $html .= '<li><b>Grupo whatssap alternancia:</b>'.' '.$txtwatshap;
            $html .= '<li><b>Fecha programación:</b>'.' '.$txtfecha;
            $html .= '</ul>';
            $html .= 'La información anterior corresponde a la programacíón del módulo adicional a tu programa en el périodo:'.' '.$txtperiodo.'<br>';
            $html .= '<hr>';
            $html .= 'Use el link del grupo de whatsaap para estar en contacto con el docente programado<br>';
            $html .= 'Conocozca más de nuestra institución visitando nuestra págna web https://www.scv.edu.co<br>';                
            $html .= '</body>';
            $html .= '</html>';
            $mail->MsgHTML($html);
            $mail->Subject = utf8_decode('Périodo:'.' '.$txtperiodo.' '.'Carga académica SYSTEM CENTER');
            $mail->AddAddress($txtcorreo);  
            $mail->addCC('coordgeneral1@scv.edu.co');  
            $mail->IsHTML(true);
            $mail->smtpConnect( array("ssl" => array("verify_peer" => false,
                                                   "verify_peer_name" => false,
                                                   "allow_self_signed" => true)));
         if ($mail->Send()) {
            $json = json_encode(array("success" => true, "Correo enviado satisfactoriamente"));     
            }else{
            $json=json_encode(array("success"=>false,"mensaje"=>$txtcorreo->ErrorInfo));
            }
            $mail->ClearAddresses($txtcorreo);
         

break;

//funcion para la carga de modulo sugridos en el ultimo periodo
case 'CargarUltimoPeriodoProgramado':
   if(isset($_POST['Fmperiodocruce'])){ $periodo=$_POST['Fmperiodocruce'];} 

    $est=$op->CargarUltimoPeriodoProgramado($periodo);     
    $lista = array();
         foreach($est as $data){
         array_push($lista,
               array('id_curso_cargafk'=>$data['id_curso_cargafk'],
                     'codigo_curso'=>$data['codigo_curso'],
                     'nombre_horario'=> $data['nombre_horario'],
                     'dia_horario'=> $data['dia_horario'],
                     'nombre_sede'=> $data['nombre_sede'],
                     'tipo_modulo'=> $data['tipo_modulo'],
                     'periodo'=> $data['periodo'],
                     'nombre_programa'=> $data['nombre_programa'],
                     'nombre_modulo'=>$data['nombre_modulo'],));
         }
        if(count($lista) != 0){
           $json = json_encode(array("success"=>true, 'lista'=>$lista));
       
         }else{
            $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontrarón módulo programados"));
         }
break;


//funcion para la carga de modulos transverales
case 'CargarModulosProgramadosTransversales':
   if(isset($_POST['Fmperiodocruce'])){ $periodo=$_POST['Fmperiodocruce'];} 
    $est=$op->CargarModulosProgramadosTransversales($periodo);     
    $lista = array();
         foreach($est as $data){
         array_push($lista,
               array('id_curso_cargafk'=>$data['id_curso_cargafk'],
                     'codigo_curso'=>$data['codigo_curso'],
                     'nombre_horario'=> $data['nombre_horario'],
                     'dia_horario'=> $data['dia_horario'],
                     'nombre_sede'=> $data['nombre_sede'],
                     'tipo_modulo'=> $data['tipo_modulo'],
                     'periodo'=> $data['periodo'],
                     'nombre_programa'=> $data['nombre_programa'],
                     'nombre_modulo'=>$data['nombre_modulo'],));
         }
        if(count($lista) != 0){
           $json = json_encode(array("success"=>true, 'lista'=>$lista));
       
         }else{
            $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontrarón módulo programados"));
         }
break;

//funcion para la carga de modulo programados especificos
case 'CargarModulosProgramadosEspecificos':
   if(isset($_POST['Fmperiodocruce'])){ $periodo=$_POST['Fmperiodocruce'];} 
    $est=$op->CargarModulosProgramadosEspeficios($periodo);     
    $lista = array();
         foreach($est as $data){
         array_push($lista,
               array('id_curso_cargafk'=>$data['id_curso_cargafk'],
                     'codigo_curso'=>$data['codigo_curso'],
                     'nombre_horario'=> $data['nombre_horario'],
                     'dia_horario'=> $data['dia_horario'],
                     'nombre_sede'=> $data['nombre_sede'],
                     'tipo_modulo'=> $data['tipo_modulo'],
                     'periodo'=> $data['periodo'],
                     'nombre_programa'=> $data['nombre_programa'],
                     'nombre_modulo'=>$data['nombre_modulo'],));
         }
        if(count($lista) != 0){
           $json = json_encode(array("success"=>true, 'lista'=>$lista));
       
         }else{
            $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontrarón módulo programados"));
         }
break;


 //funcion para la carga de modulo programados especificos
case 'CargarModulosProgramadosIgualHorario':
   if(isset($_POST['Fmperiodocruce'])){ $periodo=$_POST['Fmperiodocruce'];} 
   if(isset($_POST['Fmidhorario'])){ $horario=$_POST['Fmidhorario'];} 
    $est=$op->CargarModulosProgramadosIgualHorario($periodo,$horario);     
    $lista = array();
         foreach($est as $data){
         array_push($lista,
               array('id_curso_cargafk'=>$data['id_curso_cargafk'],
                     'codigo_curso'=>$data['codigo_curso'],
                     'nombre_horario'=> $data['nombre_horario'],
                     'dia_horario'=> $data['dia_horario'],
                     'nombre_sede'=> $data['nombre_sede'],
                     'tipo_modulo'=> $data['tipo_modulo'],
                     'periodo'=> $data['periodo'],
                     'nombre_programa'=> $data['nombre_programa'],
                     'nombre_modulo'=>$data['nombre_modulo'],));
         }
        if(count($lista) != 0){
           $json = json_encode(array("success"=>true, 'lista'=>$lista));
       
         }else{
            $json = json_encode(array("success"=>false, 'mensaje'=>"No se encontrarón módulo programados"));
         }
break;


 }

echo $json;
?>