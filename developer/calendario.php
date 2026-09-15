<?php
session_start();
date_default_timezone_set('America/bogota');

require_once '../classes/Calendario.php';

//Creación de objetos
$c = new Calendario();

/*get*/
if(isset($_GET['case'])){
 $case=$_GET['case'];
}
if(isset($_GET['fecha'])){
 $fecha=$_GET['fecha'];
}
if(isset($_GET['hora'])){
 $hora=$_GET['hora'];
}
if(isset($_GET['codigo'])){
 $codigo=$_GET['codigo'];
}


if(isset($_POST['codigo'])){
 $codigo=$_POST['codigo'];
}
if(isset($_POST['programa'])){
 $programa=$_POST['programa'];
}
if(isset($_POST['modulo'])){
 $modulo=$_POST['modulo'];
}
if(isset($_POST['sede'])){
 $sede=$_POST['sede'];
}
if(isset($_POST['salon'])){
 $salon=$_POST['salon'];
}
if(isset($_POST['horario'])){
 $horario=$_POST['horario'];
}
if(isset($_POST['cuposminimo'])){
 $cuposminimo=$_POST['cuposminimo'];
}
if(isset($_POST['cuposmaximo'])){
 $cuposmaximo=$_POST['cuposmaximo'];
}
if(isset($_POST['docente'])){
 $docente=$_POST['docente'];
}
if(isset($_POST['codmatricula'])){
 $codmatricula=$_POST['codmatricula'];
}
if(isset($_POST['codprogramacion'])){
 $codprogramacion=$_POST['codprogramacion'];
}
if(isset($_POST['asistencia'])){
 $asistencia=$_POST['asistencia'];
}
if(isset($_POST['mcancelacion'])){
 $mcancelacion=$_POST['mcancelacion'];
}

/*post*/
if(isset($_POST['fecha'])){
 $fecha=$_POST['fecha'];
}

$createtable = array(
  'data' => array()
);


switch ($case) {
  
  case 'listarSemana':
      /*en caso que venga la fecha vacía*/
      if(trim($fecha)==""){
         $fecha =date('Y-m-d');
      }
        $mes = date('n',strtotime ($fecha));

       /*Configuracion*/
       $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
       $dias = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
       $horas = array("06:00","07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00","15:00","16:00","17:00","18:00","19:00","20:00","21:00","22:00");
       $horasQuery = array("06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22");
       /*creo la cabecera*/
       $html ='<nav class="thead center"><a class="page-title">'.$meses[$mes-1].'</a></nav>';
       $html.='<table class="calendario" style="width: 100%">';
       $html.='<thead>';
       $html .='<th width="10%"></th>';
       /*listo los dias de la semana*/
       for ($i=0; $i < count($dias); $i++) {
              $rango = strtotime( '+'.$i.' day' , strtotime ( $fecha ) ) ;
              $w = date ('w',$rango);
              $d = date ('d',$rango);
              if($dias[$w]!="Domingo"){
                $html .='<th width="15%">'.$dias[$w].' '.$d.'</th>';
              }

       }
      $html .='</thead>';
      $html .='<tbody>';
    /*listo las horas del día (filas)*/
    for ($i=0; $i <count($horas); $i++) {
        $html .='<tr>';
        $html .='<td class="horas">'.$horas[$i].'</td>';
        /*listo los días de la semana (columnas)*/
        for ($j=0; $j < count($dias); $j++) {
             $rango = strtotime( '+'.$j.' day' , strtotime ( $fecha ) ) ;
             $w = date ('w',$rango);
             $fech =date('Y-m-d',$rango);
             if($dias[$w]!="Domingo"){
                  $col= $c->listarDia($fech,$horasQuery[$i]);
                  $html.='<td>';                   
                  if ($col != null) {
                    $html.='<a onclick="tableInfo(\''.$fech.'\',\''.$horasQuery[$i].'\')">Ver agenda <i class="fa fa-info-circle"></i></a>';
                  }
                    $html.='</td>';
             }
        }
        $html .='</tr>';
        $html .='</tbody>';
    }
    
    $json = $html;
  break;

  case 'listarclases':
      $table = $c->listarClases($fecha, $hora);

      $i = 1;
      foreach ($table as $datarow => $data) {

        if($data["estado"] == 'on'){
          $estado = 'Habilitado';
          $inf = '';
        }else{
          $inf = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Ver asistencia" onclick="infCancelacion('.$data["motivo_cancelacion"].')"><i class="fa fa-info"></i></a>';
          $estado = 'Inhabilitado';
          
        }

        $see = '<b>'.$data["asistencia"].'</b>&nbsp;<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Ver motivo de cancelación." onclick="infoAsistencia('.$data["codprogramamodulo"].')"><i class="fa fa-search"></i></a>&nbsp;';

        array_push($createtable['data'], array($i, $data["programa"], $data["modulo"], $data["nombre_largo"], $data["inicio"]." - ".$data["fin"], $data["salon"], $data["nombre_sede"], $estado, $see.$inf));

        $i++;

      }
      $json = json_encode($createtable);
  break;

  case 'listarasistencia':
      $table = $c->listarAsistencia($codigo);

      $i = 1;
      foreach ($table as $datarow => $data) {
        if ($data["estado"] == 'on') {
         $estado = 'confirmada';
        }else{
          $estado = 'cancelada';
        }
        array_push($createtable['data'], array($i, $data["identificacion"], $data["nombre_largo"], $estado));

        $i++;

      }
      $json = json_encode($createtable);
  break;

/***************************** PROCESOS CALENDARIO ESTUDIANTE *******************************************/

  case 'listarSemanaEstudiante':
      /*en caso que venga la fecha vacía*/
      if(trim($fecha)==""){
         $fecha =date('Y-m-d');
      }
        $mes = date('n',strtotime ($fecha));

       /*Configuracion*/
       $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
       $dias = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
       $horas = array("06:00","07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00","15:00","16:00","17:00","18:00","19:00","20:00","21:00","22:00");
       $horasQuery = array("06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22");
       /*creo la cabecera*/
       $html ='<nav class="thead center"><a class="page-title">'.$meses[$mes-1].'</a></nav>';
       $html.='<table class="calendario" style="width: 100%">';
       $html.='<thead>';
       $html .='<th width="10%"></th>';
       /*listo los dias de la semana*/
       for ($i=0; $i < count($dias); $i++) {
              $rango = strtotime( '+'.$i.' day' , strtotime ( $fecha ) ) ;
              $w = date ('w',$rango);
              $d = date ('d',$rango);
              if($dias[$w]!="Domingo"){
                $html .='<th width="15%">'.$dias[$w].' '.$d.'</th>';
              }
       }
      $html .='</thead>';
      $html .='<tbody>';
    /*listo las horas del día (filas)*/
    for ($i=0; $i <count($horas); $i++) {
        $html .='<tr>';
        $html .='<td class="horas">'.$horas[$i].'</td>';
        /*listo los días de la semana (columnas)*/
        for ($j=0; $j < count($dias); $j++) {
             $rango = strtotime( '+'.$j.' day' , strtotime ( $fecha ) ) ;
             $w = date ('w',$rango);
             $fech =date('Y-m-d',$rango);
             if($dias[$w]!="Domingo"){
                  $col= $c->listarHorario($fech,$horasQuery[$i],$_SESSION['codigo_usu']);
                  if ($col !=  null) {
                    if($col["estado"]=='on'){
                      $row = $c->estadoAsistencia($col['codmatricula'], $col['codprogramacion']);
                      if ($row != null) {
                        if ($row["estado"] == 'on') {
                          $div = '<div style="background: #75dd67;padding: 5px;">';
                        }else{
                          $div = '<div style="background: #ef5555e6;padding: 5px;">'; 
                        }
                      }else{
                          $div = '<div style="background: #ffdf70;padding: 5px;">'; 
                      }
$a = '<a onclick="infoClase(\''.$col["programa"].'\', \''.$col["modulo"].'\', \''.$col["nombre_sede"].'\', \''.$col["salon"].'\', \''.$col["horario"].'\', '.$col["cuposminimo"].', '.$col["cuposmaximo"].', \''.$col["docente"].'\', '.$col["codmatricula"].', '.$col["codprogramacion"].', '.$col["asistencia"].', \''.$col["fecha"].'\')">';
                    }else{
                      $div = '<div style="background: #bfbfbfe6;padding: 5px;">';
                      $a = '<a onclick="infCancelacion(\''.$col["motivo_cancelacion"].'\')">';
                    }
                  }

                  $html.= '<td>';                 
                  if ($col != null) { 
                    $html.= $div;
                    $html.= $a;
                    if ($col["estado"]=='on'){
                      $html.= '- '.$col["programa"].'<br>';
                      $html.= '- '.$col["modulo"].'<br>';
                      $html.= '- Sede: '.$col["nombre_sede"].'<br>';
                      $html.= '- Salon: '.$col["salon"].'<br>';
                      $html.= '- Docente: '.$col["docente"];
                    }else{
                      $html.= '- '.$col["programa"].'<br>';
                      $html.= '- '.$col["modulo"].'<br>';
                      $html.= '- Docente: '.$col["docente"].'<br>';
                      $html.= '<b>¡CLASE CANCELADA!</b>';
                    }
                    
                    $html.= '</a>';
                    
                    $html.='</div>';
                  }else{
                    $html .= '';
                  }
                  $html.='</td>';
             }
        }
        $html .='</tr>';
        $html .='</tbody>';
    }
    
    $json = $html;
  break;

  case 'informacionclase':
      $html="";
      $button = '';
      $row = $c->estadoAsistencia($codmatricula, $codprogramacion);
      if ($row != '') {
          if ($row["estado"] == 'on') {
            $estado = '<tr><th style="background: #5b7ab4;color: white;">Asistencia</th><td style="background: #75dd67;"><b>Confirmada</b></td></tr>';
            $button = '<button type="button" class="btn" onclick="cancelarasistencia('.$row["codigo"].')" style="background: #ef5555e6; color: white;">Cancelar Asistencia</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>'; 
          }else{
            $estado = '<tr><th style="background: #5b7ab4;color: white;">Asistencia</th><td style="background: #ef5555e6;"><b>Cancelada</b></td></tr>';
            $button = '<button type="button" class="btn btn-success" onclick="confirmarasistencia2('.$row["codigo"].', '.$cuposmaximo.', '.$asistencia.');">Confirmar Asistencia</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>'; 
          }
      }else{
          $estado = '<tr><th style="background: #5b7ab4;color: white;">Asistencia</th><td style="background: #ffdf70;"><b>Pendiente</b></td></tr>';
          $button = '<button type="button" class="btn btn-success" onclick="confirmarasistencia('.$codmatricula.', '.$codprogramacion.', '.$cuposmaximo.', '.$asistencia.');">Confirmar Asistencia</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>'; 
      }
        $html.='<table class="table table-bordered table-hover">';
        $html.='<tr><th style="background: #5b7ab4; color: white;">Programa</th><td>'.$programa.'</td></tr>';
        $html.='<tr><th style="background: #5b7ab4; color: white;">Modulo</th><td>'.$modulo.'</td></tr>';
        $html.='<tr><th style="background: #5b7ab4; color: white;">Sede</th><td>'.$sede.'</td></tr>';
        $html.='<tr><th style="background: #5b7ab4; color: white;">Salon</th><td>'.$salon.'</td></tr>';
        $html.='<tr><th style="background: #5b7ab4; color: white;">Horario</th><td>'.$horario.'</td></tr>';
        $html.='<tr><th style="background: #5b7ab4; color: white;">Cupo Minimo</th><td>'.$cuposminimo.'</td></tr>';
        $html.='<tr><th style="background: #5b7ab4; color: white;">Cupo Maximo</th><td>'.$cuposmaximo.'</td></tr>';
        $html.='<tr><th style="background: #5b7ab4; color: white;">Docente</th><td>'.$docente.'</td></tr>';
        $html.='<tr><th style="background: #5b7ab4; color: white;">Inscritos</th><td>'.$asistencia.'</td></tr>';
        $html.='<tr><th style="background: #5b7ab4; color: white;">Fecha</th><td>'.$fecha.'</td></tr>';
        $html.=$estado;
        $html.='</table>';

      $json = json_encode(array('html' =>$html,'button'=>$button));
  break;
  case 'confirmarasistencia':
    $i = 1;
    $row = $c->confirmarAsistencia($codmatricula, $codprogramacion);

    if ($cuposmaximo>$asistencia) {
        if($i != -1){
          $json = json_encode(array("success" => true)); 
        }else{
          $json = json_encode(array("success" => false));
        }
    }else{
        $json = json_encode(array("success" => false, "message" =>"No hay cupos disponibles.")); 
    }


  break;
  case 'cancelarasistencia':
    $i = 1;
    $row = $c->cancelarAsistencia($codigo);

    if($i != -1){
      $json = json_encode(array("success" => true)); 
    }else{
      $json = json_encode(array("success" => false));
    }
  break;
  case 'confirmarasistencia2':
    $i = 1;
    if ($cuposmaximo>$asistencia) {
      $row = $c->confirmarAsistencia2($codigo);
      if($i != -1){
        $json = json_encode(array("success" =>true)); 
      }else{
        $json = json_encode(array("success" =>false, "success" =>false));
      }
    }else{
      $json = json_encode(array("success" => false, "message" =>"No hay cupos disponibles.")); 
    }
  break;
  
/***************************** PROCESOS CALENDARIO DOCENTE *******************************************/

  case 'listarSemanaDocente':
      /*en caso que venga la fecha vacía*/
      if(trim($fecha)==""){
         $fecha =date('Y-m-d');
      }
        $mes = date('n',strtotime ($fecha));

       /*Configuracion*/
       $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
       $dias = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
       $horas = array("06:00","07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00","15:00","16:00","17:00","18:00","19:00","20:00","21:00","22:00");
       $horasQuery = array("06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22");
       /*creo la cabecera*/
       $html ='<nav class="thead center"><a class="page-title">'.$meses[$mes-1].'</a></nav>';
       $html.='<table class="calendario" style="width: 100%">';
       $html.='<thead>';
       $html .='<th width="10%"></th>';
       /*listo los dias de la semana*/
       for ($i=0; $i < count($dias); $i++) {
              $rango = strtotime( '+'.$i.' day' , strtotime ( $fecha ) ) ;
              $w = date ('w',$rango);
              $d = date ('d',$rango);
              if($dias[$w]!="Domingo"){
                $html .='<th width="15%">'.$dias[$w].' '.$d.'</th>';
              }
       }
      $html .='</thead>';
      $html .='<tbody>';
    /*listo las horas del día (filas)*/
    for ($i=0; $i <count($horas); $i++) {
        $html .='<tr>';
        $html .='<td class="horas">'.$horas[$i].'</td>';
        /*listo los días de la semana (columnas)*/
        for ($j=0; $j < count($dias); $j++) {
             $rango = strtotime( '+'.$j.' day' , strtotime ( $fecha ) ) ;
             $w = date ('w',$rango);
             $fech =date('Y-m-d',$rango);
             if($dias[$w]!="Domingo"){
                  $col= $c->listarHorarioDocente($fech,$horasQuery[$i],$_SESSION['codigo_usu']);
                  $html.= '<td>';  
                  if ($col["estado"] == 'on') {
                    $div = '<div style="background: #75dd67;padding: 5px;">';
                    $a = '<a onclick="infoClasDocente('.$col["codprogramacion"].', \''.$col["fecha"].'\', \''.$col["horario"].'\', '.$col["cuposminimo"].', '.$col["cuposmaximo"].', \''.$col["horainicio"].'\')">';
                   }else{
                    $div = '<div style="background: #bfbfbfe6; padding: 5px;">';
                    $a = '<a onclick="infCancelacion(\''.$col["motivo_cancelacion"].'\')">';
                   }               
                  if ($col != null) { 
                    $html.= $div;
                    $html.= $a;
                    $html.= '- '.$col["programa"].'<br>';
                    $html.= '- '.$col["modulo"].'<br>';
                    $html.= '- Sede: '.$col["nombre_sede"].'<br>';
                    $html.= '- Salon: '.$col["salon"].'<br>';
                    $html.= '- Asistencia: '.$col["asistencia"].'<br>';
                    $html.=  '</a>';

                    $html.='</div>';
                  }else{
                    $html .= '';
                  }
                  $html.='</td>';
             }
        }
        $html .='</tr>';
        $html .='</tbody>';
    }
    
    $json = $html;
  break;

  case 'insertCancelacion':
    if(strtotime($fecha)>strtotime(date('Y-m-d'))){
      $row = $c->insertCancelacion($codigo, $mcancelacion);

      if ($row == true) {
        $json = json_encode(array("success"=>true, "message"=>"Cancelación guardada."));
      }else{
        $json = json_encode(array("success"=>false, "message"=>"Error"));
      }
    }else{
      list($h, $m) = explode(':', $horario);
      $h = $h-2;
      $h = ($h < 10 ? '0'.$h : $h);
      $hora = $h.':'.$m;

      if($hora>date('H:i')){
        $row = $c->insertCancelacion($codigo, $mcancelacion);
          
        if ($row == true) {
          $json = json_encode(array("success"=>true, "message"=>"Cancelación guardada."));
        }else{
          $json = json_encode(array("success"=>false, "message"=>"Error"));
        }
      }else{
        $json = json_encode(array("success"=>false, "message"=>"Ha pasado el tiempo minímo para cancelar la clase."));
      }
    } 
  break;

  default :
      $json = "Acceso Denegado :(";
  break;

}

echo $json;

?>
