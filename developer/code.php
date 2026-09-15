<?php
session_start();
date_default_timezone_set('America/Bogota');
header('content-type: application/json; charset=utf-8');


require("phpmailer/class.phpmailer.php");
require("phpmailer/class.smtp.php");

$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->SMTPKeepAlive = true; 
$mail->SMTPSecure = "tls";
$mail->SMTPDebug  = 0;
$mail->Host = "smtp.gmail.com";
$mail->Port = 587;

$mail->Username = "ebarreto@coruniamericana.edu.co";
$mail->Password = "1143160684";
$mail->SetFrom('admisiones@coruniamericana.edu.co', utf8_decode('Instituto Centro de Sistemas S.A.S'));
 
$mail->Subject = utf8_decode("Instituto Centro de Sistemas S.A.S | System Center");
$mail->AltBody = "";


if(isset($_GET['case'])){
    $case=$_GET['case'];
}
if(isset($_GET['estado'])){
    $estado=$_GET['estado'];
}

// VARIABLE menu
  if(isset($_POST['codmenu'])){
    $codmenu = $_POST['codmenu'];
  }
  if(isset($_POST['icono'])){
    $icono_menu = trim($_POST['icono']);
  }
  if(isset($_POST['nombre_menu'])){
    $nombre_menu = ucfirst(strtolower(trim($_POST['nombre_menu'])));
  }
  if(isset($_POST['nivel'])){
    $nivel_menu = $_POST['nivel'];
  }
  if(isset($_POST['link'])){
    $link_menu = strtolower($_POST['link']);
  }
  if(isset($_POST['padre'])){
    $padre_menu = $_POST['padre'];
  }
  if(isset($_POST['estado'])){
    $estado_menu = $_POST['estado'];
  }
// variables para busqueda
  if(isset($_GET['nombre_menu'])){
    $nombre_menu = ucfirst(strtolower(trim($_GET['nombre_menu'])));
  }
  if(isset($_GET['nivel'])){
    $nivel_menu = $_GET['nivel'];
  }
  if(isset($_GET['padre'])){
    $padre_menu = $_GET['padre'];
  }

//variables perfiles
  if(isset($_POST['codperfil'])){
    $codperfil = $_POST['codperfil'];
  }
  if(isset($_POST['nombre_perfil'])){
    $nombre_perfil = $_POST['nombre_perfil'];
  }
  if(isset($_POST['rol'])){
    $rol_perfil = $_POST['rol'];
  }
  if(isset($_POST['estado_perfil'])){
    $estado_perfil = $_POST['estado_perfil'];
  }

//VARIABLES ASIGNARMENU PERFILES
  if(isset($_POST['codrol'])){
    $codrol = $_POST['codrol'];
  }
  if(isset($_POST['menus'])){
    $menus = $_POST['menus'];
  }

// variables para mi perfil y para registro empresarial
  if(isset($_GET['nombphoto'])){
    $nombphoto = $_GET['nombphoto'];
  }
  if(isset($_POST['identificacion'])){
    $identificacion = $_POST['identificacion'];
  }
  if(isset($_POST['nombre'])){
    $nombre = $_POST['nombre'];
  }
  if(isset($_POST['apellido'])){
    $apellido = $_POST['apellido'];
  }
  if(isset($_POST['direccion'])){
    $direccion = $_POST['direccion'];
  }
  if(isset($_POST['celular'])){
    $celular = $_POST['celular'];
  }
  if(isset($_POST['contraActual'])){
    $contraActual = $_POST['contraActual'];
  }
  if(isset($_POST['nuevaContra'])){
    $nuevaContra = $_POST['nuevaContra'];
  }

// variables clientes.php
  if(isset($_POST['codusuario'])){
    $codusuario = $_POST['codusuario'];
  }


//variables de sedes.php
  if(isset($_POST['nombre_sede'])){
    $nombre_sede = $_POST['nombre_sede'];
  }

  if(isset($_POST['estado_sede'])){
    $estado_sede = $_POST['estado_sede'];
  }
  if(isset($_POST['codigo_sede'])){
    $codigo_sede = $_POST['codigo_sede'];
  }

// variables usuario.php
  if(isset($_POST['identificacion'])){
    $identificacion = $_POST['identificacion'];
  }

  if(isset($_POST['email'])){
    $email = $_POST['email'];
  }

//Variables Programas
  if(isset($_POST['nombre'])){
    $nombre = $_POST['nombre'];
  }

  if(isset($_POST['estado'])){
    $estado = $_POST['estado'];
  }

  if(isset($_POST['codigo'])){
    $codigo = $_POST['codigo'];
  }

  if(isset($_POST['usuario'])){
    $usuario = $_POST['usuario'];
  }
//Variables Estudiante
  if(isset($_POST['fecha'])){
    $fecha = $_POST['fecha'];
  }

  if(isset($_POST['telefono'])){
    $telefono = $_POST['telefono'];
  }

  if(isset($_POST['pnombre'])){
    $pnombre = $_POST['pnombre'];
  }

  if(isset($_POST['papellido'])){
    $papellido = $_POST['papellido'];
  }

  if(isset($_POST['snombre'])){
    $snombre = $_POST['snombre'];
  }

  if(isset($_POST['sapellido'])){
    $sapellido = $_POST['sapellido'];
  }

  if(isset($_POST['nombre'])){
    $nombre = $_POST['nombre'];
  }

  if(isset($_POST['genero'])){
    $genero = $_POST['genero'];
  }

  if(isset($_POST['codeps'])){
    $codeps = $_POST['codeps'];
  }

  if(isset($_POST['codtiposangre'])){
    $codtiposangre = $_POST['codtiposangre'];
  }

  if(isset($_POST['coestrato'])){
    $coestrato = $_POST['coestrato'];
  }

//Variables Modulos
  if(isset($_POST['descripcion'])){
    $descripcion = $_POST['descripcion'];
  }

//Variables MatrÃ­cula
  if(isset($_POST['sede'])){
      $sede=$_POST['sede'];
  }
  if(isset($_POST['anolect'])){
      $anolect=$_POST['anolect'];
  }

  if(isset($_POST['prog'])){
      $prog=$_POST['prog'];
  }

  if(isset($_POST['codes'])){
      $codes=$_POST['codes'];
  }
  if(isset($_POST['valor'])){
      $valor=$_POST['valor'];
  }
  if(isset($_POST['codprograma'])){
      $codprograma=$_POST['codprograma'];
  }
  if(isset($_POST['codestudiante'])){
      $codestudiante=$_POST['codestudiante'];
  }
  if(isset($_POST['codestu'])){
      $codestu=$_POST['codestu'];
  }
  if(isset($_POST['pReferencia'])){
      $pReferencia=$_POST['pReferencia'];
  }
  if(isset($_POST['pMetodoPago'])){
      $pMetodoPago=$_POST['pMetodoPago'];
  }
  if(isset($_POST['pCantidad'])){
      $pCantidad=$_POST['pCantidad'];
  }

//Variables Gestion Temas
  if (isset($_GET['codigotema'])) {
    $codigotema = $_GET['codigotema'];
  }

  if (isset($_POST['codigotema'])) {
    $codigotema = $_POST['codigotema'];
  }

  if(isset($_GET['gtsede'])){
      $gtsede=$_GET['gtsede'];
  }

  if(isset($_GET['gtanio'])){
      $gtanio=$_GET['gtanio'];
  }

  if(isset($_GET['gtmod'])){
      $gtmod=$_GET['gtmod'];
  }

  if(isset($_GET['gtprog'])){
      $gtprog=$_GET['gtprog'];
  }
  if(isset($_GET['codprogmodulo'])){
      $codprogmodulo=$_GET['codprogmodulo'];
  }
  if (isset($_POST['codprogramamod'])) {
      $codprogramamod = $_POST['codprogramamod'];
  }
  if (isset($_POST['nombretema'])) {
      $nombretema = $_POST['nombretema'];
  }
  if (isset($_POST['estadotema'])) {
      $estadotema = $_POST['estadotema'];
  }


  if (isset($_POST['estadoactividad'])) {
      $estadoactividad = $_POST['estadoactividad'];
  }

  if (isset($_POST['nombreactividad'])) {
      $nombreactividad = $_POST['nombreactividad'];
  }

  if (isset($_POST['codigoactividad'])) {
      $codigoactividad = $_POST['codigoactividad'];
  }

//Variables Programacion Modulo

  if(isset($_POST['programa'])){
    $programa = $_POST['programa'];
  }
  if(isset($_POST['modulo'])){
    $modulo = $_POST['modulo'];
  }
  if(isset($_POST['sedepm'])){
    $sedepm = $_POST['sedepm'];
  }
  if(isset($_POST['fechaini'])){
    $fechaini = $_POST['fechaini'];
  }
  if(isset($_POST['fechafin'])){
    $fechafin = $_POST['fechafin'];
  }
  if(isset($_POST['anolectivo'])){
    $anolectivo = $_POST['anolectivo'];
  }
  if(isset($_POST['valor'])){
    $valor = $_POST['valor'];
  }
  if(isset($_POST['estadopm'])){
    $estadopm = $_POST['estadopm'];
  }

//Variables programaciÃ³n modulos
  if(isset($_POST['codprograma_modulo'])){
      $codprograma_modulo=$_POST['codprograma_modulo'];
  }

  if(isset($_POST['horainicio'])){
      $horainicio=$_POST['horainicio'];
  }

  if(isset($_POST['horafin'])){
      $horafin=$_POST['horafin'];
  }

  if(isset($_POST['cuposminimo'])){
      $cuposminimo=$_POST['cuposminimo'];
  }

  if(isset($_POST['cuposmaximo'])){
      $cuposmaximo=$_POST['cuposmaximo'];
  }

  if(isset($_POST['salon'])){
      $salon=$_POST['salon'];
  }

  if(isset($_POST['codmodulodocente'])){
      $codmodulodocente=$_POST['codmodulodocente'];
  }

  if(isset($_POST['codigo'])){
      $cod=$_POST['codigo'];
  }

  if (isset($_GET['codprograma'])) {
      $codp=$_GET['codprograma'];
  }


  if (isset($_GET['codequery'])) {
    $codequery=$_GET['codequery'];
  }
  if (isset($_GET['codequery1'])) {
    $codequery1=$_GET['codequery1'];
  }
  if (isset($_GET['codequery2'])) {
    $codequery2=$_GET['codequery2'];
  }
  if (isset($_GET['codequery3'])) {
    $codequery3=$_GET['codequery3'];
  }

//Variables docente
 if (isset($_POST['docente'])) {
    $docente=$_POST['docente'];
  }

  //Variables roles
if(isset($_POST['nombre_rol'])){
$nombre_rol=$_POST['nombre_rol'];
}

if(isset($_POST['codigo_rol'])){
$codigo_rol=$_POST['codigo_rol'];
}

if(isset($_POST['estado_rol'])){
$estado_rol=$_POST['estado_rol'];
}

//Recuperar correo
  if(isset($_POST['EmailRecuperar'])){
    $EmailRecuperar = strtolower($_POST['EmailRecuperar']);
  }

  if(isset($_POST['pass'])){
    $pass = $_POST['pass'];
  }


$createtable = array(
  'data' => array()
);


//Llamados de clases
require_once '../classes/Menu.php';
require_once '../classes/Usuarios.php';
require_once '../classes/Perfiles.php';
require_once '../classes/Sedes.php';
require_once '../classes/Programas.php';
require_once '../classes/AnioLectivo.php';
require_once '../classes/Estudiante.php';
require_once '../classes/Modulo.php';
require_once '../classes/Matricula.php';
require_once '../classes/Temas.php';
require_once '../classes/ProgramaModulo.php';
require_once '../classes/ModuloProgramacion.php';
require_once '../classes/InfoDocente.php';
require_once '../classes/Docente.php';
require_once '../classes/Roles.php';
require_once '../classes/MiPerfil.php';

//CreaciÃ³n de objetos
$m = new Menu();
$u = new Usuarios();
$p = new Perfiles();
$Se = new Sedes();
$Pro = new Programas();
$al = new AnioLectivo();
$e =  new Estudiante();
$mo = new Modulo();
$ma = new Matricula();
$t = new Temas();
$pm = new ProgramaModulo();
$mp = new ModuloProgramacion();
$di = new InfoDocente();
$d =  new Docente();
$r =  new Roles();
$mpp = new MiPerfil();

switch ($case) {   
  /************************ procesos para recuperar correo **************************/
    case 'recuperarUsuario':
      $mail->Subject = utf8_decode("Recuperación de contraseña.");
    
      $query = $mpp->validarCorreoRecuperacion($EmailRecuperar);
      if($query){ 
        $token = $u->generatecod();
        $query2 = $mpp->insertarToken($EmailRecuperar, $token);
        
          if($query2){
           
            $urlconfirmar = $u->urlservidor()."ForgotPassword/".$u->base64url_encode($EmailRecuperar.','.$token);

            $html = "<!DOCTYPE html>";
            $html .= "<html>";
            $html .= "<head>";
            $html .= "<title>Correo de confirmacion | Correo de confirmacion</title>";
            $html .= "<meta charset='UTF-8'>";
            $html .= "<style> 
                body{
                    text-align:center;
                }
              /*button*/
                .button{
                  display: inline-block;
                  padding:0 10px;
                  color: #4c4c4c;
                  height: 40px;
                  text-align: center;
                  line-height: 35px;
                  white-space: nowrap;
                   cursor: pointer;
                  box-sizing: border-box; 
                  border: 1px solid #CCCCCC;
                  margin-bottom:10px;
                  border-radius:3px;
                  font-weight: bold;
                  outline: 0;
                  text-decoration: none;
                }
                .primary-button{
                  color: #fff !important;
                  background: #1b3357 !important;
                  border: 1px solid #1b3357;
                }
            </style>";
            $html .="</head>";
            $html .="<body>";
            $html .= '<table style="width:100%;max-width:600px" width="100%" cellspacing="0" cellpadding="0" border="0" align="center">';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<td role="modules-container" style="padding:0px 0px 0px 0px;color:#000000;text-align:left" width="100%" bgcolor="#ffffff" align="left">';
            $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<td style="font-size:6px;line-height:10px;padding:0px 0px 0px 0px" valign="top" align="center">';
            // $html .= '<img style="display:block;max-width:100%!important;width:100%;height:auto!important" src="../assets/img/header-email.jpg" width="600" border="0">';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</tbody>';
            $html .= '</table>';
      
            $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<td style="padding:0px 0px 20px 0px">';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</tbody>';
            $html .= '</table>';

            $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<td style="padding:18px 0px 10px 0px;line-height:22px;text-align:inherit" valign="top" height="100%">';
            $html .= '<div style="text-align:center">';
            $html .= '<span style="font-size:22px";font-family:arial,helvetica,sans-serif">';
            $html .= '<strong>Hola, '.$query['nombre_usu'].'</strong>';
            $html .= '</span></div>';
            $html .= '</td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td style="padding:0px 0px 0px 0px;line-height:22px;text-align:inherit" valign="top" height="100%">';
            $html .= '<p font-style:normal;font-variant-ligatures:normal;font-variant-caps:normal;font-weight:400;font-family:Georgia,"Times New Roman","Bitstream Charter",Times,serif;font-size:16px;text-align:justify;color:rgb(51,51,51)>';
            $html .= '<span style="font-family:arial,helvetica,sans-serif;font-size:16px">';    
            $html .= 'Recibimos una solicitud para acceder a tu cuenta<br>';
            $html .= '<b>'.$EmailRecuperar.'</b><br>';
            $html .= '<i>Si no solicitaste restablecer tu contraseña, es posible que otra persona esté intentando acceder a tu cuenta de <b>System Center</b>, haz caso omiso a este correo.</i>';

            $html .= '</span></p>';
            $html .= '</td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td valign="top" height="100%">';
            $html .= '<p style="text-align: center;">';
            $html .= '<a class="button primary-button" style="text-align:center" href="'.$urlconfirmar.'">CAMBIAR CONTRASEÑA</a>';
            $html .= '</span></p>';
            $html .= '</td>';
            $html .= '</tr>';

            $html .= '</tbody>';
            $html .= '</table>';
      
            $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<td style="font-size:6px;line-height:10px;padding:0px 0px 0px 0px" valign="top" align="center">';
            // $html .= '<img style="display:block;max-width:100%!important;width:100%;height:auto!important" src="../assets/img/footer-email.jpg" alt="" tabindex="0" width="600" border="0">';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</tbody></table>';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</body>';
            $html .= '</html>';
            $mail->MsgHTML($html);
            $mail->AddAddress($EmailRecuperar,"");

            $mail->IsHTML(true);
            $mail->smtpConnect(
            array(
              "ssl" => array(
                  "verify_peer" => false,
                  "verify_peer_name" => false,
                  "allow_self_signed" => true
              )
            )
            );

            if ($mail->Send()) {
              $json = json_encode(array("success" => true, "mensaje"=>"Correo enviado exitosamente.")); 
            }else{
              $json=json_encode(array("success"=>true,"mensaje"=>$mail->ErrorInfo));
            }
            
          }else{
            $json = json_encode(array("success" =>false,"mensaje"=>"No se insertó la Usuario"));
          }
      }else{
        $json = json_encode(array("success"=>false,"mensaje" => "Correo electrónico invalido."));
      }
    break;

    case 'changePass':
      $row = $mpp->BuscarCorreo($EmailRecuperar);

      if($row != ''){
        if($row['password']!=sha1($pass)){
          $update = $mpp->updatePassword($pass, $row['codigo_usu']);
          if($update){
            $json = json_encode(array("success"=>true));
          }else{
            $json = json_encode(array("success"=>false,"mensaje" => "No se Actualizó la información. Por favor, intentelo de nuevo"));
          }
        }else{
          $json = json_encode(array("success"=>false,"mensaje" => "Su nueva contraseña coincide con la anterior"));
        }
      }else{
          $json = json_encode(array("success"=>false,"mensaje" => "Error"));
      }
    break;



    case 'loadPerfiles2':

      $sql = "SELECT codperfil as cod, nombre_perfil as nombre, estado_perfil FROM perfil2 WHERE estado_perfil = 'on'";
      $table = table($sql);
      $json = json_encode($table);
    break;

  /************************  procesos para miperfil.php ***********************************/
    case 'uploadFotoPerfil':
       if($_FILES['img-perfil']['tmp_name']!=""){
          $file=$_FILES["img-perfil"]['name'];
          $extension= explode(".",$file) ;
          $url="../assets/fotoperfil/".$_SESSION['usuario'].".".$extension[1];                       
          $urlFoto='assets/fotoperfil/'.$_SESSION['usuario'].".".$extension[1];
          if ($_FILES['img-perfil']['size'] < 40000) {
            if (move_uploaded_file($_FILES['img-perfil']['tmp_name'],$url)) {
              $query = $mpp->changeImgProfile($urlFoto);
              $_SESSION['foto']=$urlFoto;
              $json = json_encode(array("success" => true, "mensaje"=>"Foto de perfil actualizada exitosamente.")); 
            }else{
              $json = json_encode(array("success" =>false,"mensaje"=>"Campo vacÃ­o"));
            }   
          }else{
            $json = json_encode(array("success" =>false,"mensaje"=>"El peso de la imagÃ©n supera el mÃ¡ximo permitido."));
          }  
       }
    break;

    case 'editUsuario':
      
      $row = $mpp->changeNameProfile($nombre);

        if($row){
          $_SESSION['nombre_usu']=$nombre;
          $json = json_encode(array("success"=>true));
        }else{
          $json = json_encode(array("success"=>false,"mensaje" => "No se ActualizÃ³ la informaciÃ³n. Por favor, intentelo de nuevo"));
        }
    break;

    case 'editContrasena':
      
    $row = $mpp->editContrasena();

      if($row != ''){
        if($row['password']==sha1($contraActual)){
          
          $update = $mpp->updatePassword($nuevaContra, $row['codigo_usu']);

          if($update){
            $json = json_encode(array("success"=>true));
          }else{
            $json = json_encode(array("success"=>false,"mensaje" => "No se ActualizÃ³ la informaciÃ³n. Por favor, intentelo de nuevo"));
          }
        }else{
          $json = json_encode(array("success"=>false,"mensaje" => "La contraseÃ±a actual ingresada no es correcta"));
        }
      }
    break;
  /************************  FIN procesos para miperfil.php ******************************/



  /************************  procesos para gestionarmenu.php *********************************/
    case 'loadMenu':

      $table = $m->loadMenu();
      $i = 1;
      foreach ($table as $datarow => $data) {

        $icono = '<i class="fa '.$data["imagen"].'"></i>';

        $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar MenÃº" onclick="fmodalEditar('.$data["codigo_menu"].', \''.$data["imagen"].'\',  \''.$data["nombre_menu"].'\', \''.$data["link"].'\', \''.$data["estado"].'\')"><i class="fa fa-edit"></i></a>';

        if($data["codsuperior"] == 0){
          $codsuperior = 'Sin menÃº superior';
        } else{

          $rowmenu = $m->loadMenuImagen($data["codsuperior"]);
          if($rowmenu != '')
          {
            $codsuperior = '<b><i class="fa '.$rowmenu["imagen"].'"></i> '.$rowmenu["nombre_menu"].'</b>';
          }
        }

        $options = $edit;
          
        array_push($createtable['data'], array($i, $icono, $data["nombre_menu"],$data["nivel"],$data["orden"], $codsuperior, $data["link"], $data["estado"], $options));

        $i++;

      }
      $json = json_encode($createtable);

    break;

    case 'buscarMenu':
      // estado = :estado AND
      $where ="";
      if($nivel_menu != ''){
        $where .= " AND nivel = ".$nivel_menu;
      }
      if($padre_menu != ''){
        $where .= " AND codsuperior = ".$padre_menu;
      }


      $table = $m->buscarMenu($nombre_menu, $where);


      $i = 1;
      foreach ($table as $datarow => $data) {

        $icono = '<i class="fa '.$data["imagen"].'"></i>';

        $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar MenÃº" onclick="fmodalEditar('.$data["codigo_menu"].', \''.$data["imagen"].'\',  \''.$data["nombre_menu"].'\', \''.$data["link"].'\', \''.$data["estado"].'\')"><i class="fa fa-edit"></i></a>';

        if($data["codsuperior"] == 0){
          $codsuperior = 'Sin menÃº superior';
        } else{

          $rowmenu = $m->loadMenuImagen($data["codsuperior"]);
          if($rowmenu != '')
          {
            $codsuperior = '<b><i class="fa '.$rowmenu["imagen"].'"></i> '.$rowmenu["nombre_menu"].'</b>';
          }
        }

        $options = $edit;
          
        array_push($createtable['data'], array($i, $icono, $data["nombre_menu"],$data["nivel"],$data["orden"], $codsuperior, $data["link"], $data["estado"], $options));

        $i++;

      }
      $json = json_encode($createtable);
    break;

    case 'loadPadresMenu':

      $table = $m->loadPadresMenus();

      if(count($table)>=1){
        $json = json_encode(array("success"=>true,"menu" =>$table));
      }else{
        $json = json_encode(array("success"=>false,"mensaje" => "No hay MenÃº disponibles"));
      }
      break;

    case 'insertItemMenu':

      if($nivel_menu == '1'){
        
        $rowOrden = $m->MenuOrdenMax($nivel_menu);
       
        if($rowOrden == ''){
          $orden = 1;
        }else{
          $orden = ($rowOrden["nivelmax"] + 1);
        }
        
        $insert = $m->insertarMenu($nombre_menu, $nivel_menu, $orden, 0, $link_menu, $icono_menu, '_self', $estado_menu);
        $json = json_encode(array("success"=>true,"orden" =>$orden));
        
      }else if($nivel_menu == '2'){
       
        $rowOrden2 = $m->MenuOrdenMaxNivel2($nivel_menu, $padre_menu);

        $orden2 = ($rowOrden2["nivelmax"] + 1);

        $insert = $m->insertarMenu($nombre_menu, $nivel_menu, $orden2, $padre_menu, $link_menu, $icono_menu, '_self', $estado_menu);
        $json = json_encode(array("success"=>true,"orden" =>$orden2));
       
      }
    break;

    case 'editItemMenu':

      $update = $m->editItemMenu($icono_menu, $nombre_menu, $link_menu, $estado_menu, $codmenu);
      $json = json_encode(array("success"=>true));

    break;
  /************************  FIN procesos para gestionarmenu.php ****************************/


  /************************  procesos para gestionarperfiles.php *****************************/
    case 'loadPerfiles':
      $table = $p->loadPerfiles();//, $params
      $i = 1;
      foreach ($table as $datarow => $data) {

        if($data["estado_perfil"] == 'on'){
          $estado = 'Habilitado';
        }else if($data["estado_perfil"] == 'off'){
          $estado = 'Inhabilitado';
        }else{
          $estado = 'Error';
        }

        $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar Perfil" onclick="fmodalEditar('.$data["codigo_perfil"].', \''.$data["nombre_perfil"].'\', \''.$data["estado_perfil"].'\')"><i class="fa fa-edit"></i></a>';
        $options = $edit;
          
        array_push($createtable['data'], array($i, $data["nombre_perfil"], $data["nombre_rol"], $data["menus"], $estado, $options));

        $i++;

      }
      $json = json_encode($createtable);
    break;

    case 'editItemPerfil':
      $table = $p->editItemPerfil($nombre_perfil, $estado_perfil, $codperfil);
      $json = json_encode(array("success"=>true));
    break;

    case 'loadRol':
      $table = $p->loadRol();
      $json = json_encode($table);

    break;

    case 'loadPerfilesSelect':
      $sql = "SELECT codigo_perfil as cod, nombre_perfil as nombre FROM perfiles WHERE estado_perfil = 'on'";
      // $params = array(':estado' => $estado_rol);,$params

      $table = table($sql);

      $json = json_encode($table);

    break;

    case 'insertItemPerfil':

        $table = $p-> insertItemPerfil($nombre_perfil, $rol_perfil, $estado_perfil);
        $json = json_encode(array("success"=>true));

    break;
  /************************  FIN procesos para gestionarmenu.php ****************************/


  /************************  procesos para asignarmenuperfiles.php ****************************/
    case 'loadmodulos':
      $tablemenu = $p->loadmodulos();


      $rowrolesmenu = $p->loadmodulosS($codrol);


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
      
     $table = $p-> updateConfigRolmenu($menus, $codrol);
     $json = json_encode(array("success"=>true));

    break;
  /************************  FIN procesos para asignarmenuperfiles.php ***********************/

    
  /************************  procesos para usuarios.php  ***********************************/
    case 'loadeps':
          $table = $u->loadeps();
          $json = json_encode($table);
    break;

    case 'loadtiposangre':
          $table = $u->loadtiposangre();
          $json = json_encode($table);
    break;

    case 'loadUsuarios':
     
     if($estado != ''){
          $whe = "WHERE estado = '".$estado."'";
      }else{
        $whe = "";
      }
      
      $table = $u->loadUsuarios($whe);


      $i = 1;
      foreach ($table as $datarow => $data) {

        if($data["estado"] == 'on'){
          $estado = 'Habilitado';
        }else if($data["estado"] == 'off'){
          $estado = 'Inhabilitado';
        }else{
          $estado = 'Error';
        }
        if($data["email_confirmado"] == 'TRUE'){
          $confirmado = 'Si';
        }else{
          $confirmado = 'No';
        }


        $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar Usuario" onclick="fmodalEditar('.$data["codigo_usu"].', \''.$data["identificacion"].'\', \''.$data["nombre_usu"].'\', \''.$data["email"].'\', \''.$data["email_confirmado"].'\')"><i class="fa fa-edit"></i></a>&nbsp';
        $delete = '<a class="btn btn-danger btn-sm purple tooltips" data-original-title="Eliminar" data-rel="tooltip" title="Eliminar" onClick="feditEstado('.$data["codigo_usu"].',\'off\')"><i class="fa fa-trash-o"></i></a>&nbsp';
        $restore = '<a class="btn btn-success btn-sm purple tooltips" data-original-title="Restaurar" data-rel="tooltip" title="Restaurar" onClick="feditEstado('.$data["codigo_usu"].', \'on\')""><i class="fa fa-undo"></i></a>';
        $options = $edit.$delete;
          
        if ($data["estado"] == 'on') $options = $edit.$delete;
        else $options = $restore;
        array_push($createtable['data'], array($i, '<img src="'.$data["foto"].'" alt="" class="img-circle img-responsive" style="width: 50px;margin: 0px auto;" >', $data["identificacion"], $data["email"], $data["nombre_usu"], $data["nombre_perfil"], $estado, $confirmado, $options));

        $i++;

      }
      $json = json_encode($createtable);
    break;
    case 'loadPerfilesS':
        $table = $u->loadPerfilesS();

        if(count($table)>=1){
          $json = json_encode(array("success"=>true,"perfiles" =>$table));
        }else{
          $json = json_encode(array("success"=>false,"mensaje" => "No hay Perfiles disponibles"));
        }
    break;

    case 'editEstadoCliente':
        $table = $u->editEstadoCliente($codusuario, $estado);
        $json = json_encode(array("success"=>true));
    break;

    case 'editModalUsuario':
        $table = $u->editModalUsuario($codusuario, $nombre, $identificacion, $email);

        if(count($table)>=1){
          $json = json_encode(array("success"=>true,"area" =>$table));
        }else{
          $json = json_encode(array("success"=>false,"mensaje" => "No hay Ã¡reas disponibles"));
        }
        $json = json_encode(array("success"=>true));
       break;

    case 'insertUsuario':
        $validarCorreo = $u->ValidarCorreo($email);
        if(count($validarCorreo) == 0){
          $buscar = $u->buscarUsuarioID($identificacion);

          if($buscar == ''){
            $token = $u->generatecod();
            $datarow = $u->insertUsuario($identificacion, $identificacion, $codperfil, $estado, $nombre, $email, $token);
            if($datarow != -1){          

                $urlconfirmar = $u->urlservidor()."ValidateEmailIsReal/".$u->base64url_encode($email.','.$datarow["codigo_usu"].','.$token);
              $html = "<!DOCTYPE html>";
              $html .= "<html>";
              $html .= "<head>";
              $html .= "<title>Instituto Centro de Sistemas S.A.S | System Center</title>";
              $html .= '<link rel="shortcut icon" type="image/png" href="http://201.219.220.123/systemcenter/assets/img/system_ico.ico"/>';
              $html .= '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">';
              $html .= '<link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css" />';
              $html .= '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';
              $html .= '<link rel="stylesheet" href="http://201.219.220.123/systemcenter/assets/css/custom-styles.css" rel="stylesheet">';
              $html .= "<meta charset='UTF-8'>";
              $html .= "<style> 
                    body{
                        text-align:center;
                    }
                  /*button*/
                    .button{
                      display: inline-block;
                      padding:0 10px;
                      color: #4c4c4c;
                      height: 40px;
                      text-align: center;
                      line-height: 35px;
                      white-space: nowrap;
                       cursor: pointer;
                      box-sizing: border-box; 
                      border: 1px solid #CCCCCC;
                      margin-bottom:10px;
                      border-radius:3px;
                      font-weight: bold;
                      outline: 0;
                      text-decoration: none;
                    }
                    .primary-button{
                      color: #fff !important;
                      background: #1c4697!important;
                      border: 1px solid #000971;
                    }
                    .rowY {
                          width: 780px;
                          margin-right: 80px;
                          margin-left: 154px;
                    }
                </style>"; 
              $html .="</head>";
              $html .="<body>";
              $html .= '<div class="container" style="margin-top: 20px;">';
              $html .= '<div class="row">';
              $html .= '<div class="row rowY" style="border: solid 2px #214a81;border-radius: 26px">';
              $html .= '<div class="section-title" style="justify-content: center;display: flex;">';
              $html .= '<div style="float: left;">';
              $html .= '<img src="http://201.219.220.123/systemcenter/assets/img/mail.png" style="margin-left: -1%; margin: -3px;">';
              $html .= '</div>';
              $html .= '<div style="float: left;">';
              $html .= '<table style="width:100%;max-width:600px" width="100%" cellspacing="0" cellpadding="0" border="0" align="center">';
              $html .= '<tbody>';
              $html .= '<tr>';
              $html .= '<td role="modules-container" style="padding:0px 0px 0px 0px;color:#000000;text-align:left" width="100%" bgcolor="#ffffff" align="left">';
              $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
              $html .= '<tbody>';
              $html .= '<tr>';
              $html .= '<td style="font-size:6px;line-height:10px;padding:0px 0px 0px 0px" valign="top" align="center">';
              // $html .= '<img style="display:block;max-width:100%!important;width:100%;height:auto!important" src="../assets/img/header-email.jpg" width="600" border="0">';
              $html .= '</td>';
              $html .= '</tr>';
              $html .= '</tbody>';
              $html .= '</table>';
        
              $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
              $html .= '<tbody>';
              $html .= '<tr>';
              $html .= '<td style="padding:0px 0px 20px 0px">';
              $html .= '</td>';
              $html .= '</tr>';
              $html .= '</tbody>';
              $html .= '</table>';

              $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
              $html .= '<tbody>';
              $html .= '<tr>';
              $html .= '<td style="padding:18px 0px 10px 0px;line-height:22px;text-align:inherit" valign="top" height="100%">';
              $html .= '<div style="text-align:center">';
              $html .= '<span style="font-size:22px";font-family:arial,helvetica,sans-serif">';
              $html .= '<strong>Hola, '.$nombre.'</strong>';
              $html .= '</span></div>';
              $html .= '</td>';
              $html .= '</tr>';

              $html .= '<tr>';
              $html .= '<td style="padding:0px 0px 0px 0px;line-height:22px;text-align:inherit" valign="top" height="100%">';
              $html .= '<p font-style:normal;font-variant-ligatures:normal;font-variant-caps:normal;font-weight:400;font-family:Georgia,"Times New Roman","Bitstream Charter",Times,serif;font-size:16px;text-align:justify;color:rgb(51,51,51)>';
              $html .= '<span style="font-family:arial,helvetica,sans-serif;font-size:16px">';
              $html .= 'Te damos la bienvenida a nuestra Institución Educativa <b>System Center</b> <br>';
              $html .= 'Tu usuario es: <b>'.$email.'</b> y tu contraseña: <b>'.$identificacion.'</b> <br>';
              $html .= '<i>Por razones de seguridad recomendamos realizar el cambio de contraseña al momento de iniciar sesión por primera vez.</i>';
              $html .= '</span></p>';
              $html .= '</td>';
              $html .= '</tr>';


              $html .= '<tr>';
              $html .= '<td style="padding:0px 0px 0px 0px;line-height:22px;text-align:inherit" valign="top" height="100%">';
              $html .= '<p font-style:normal;font-variant-ligatures:normal;font-variant-caps:normal;font-weight:400;font-family:Georgia,"Times New Roman","Bitstream Charter",Times,serif;font-size:16px;text-align:justify;color:rgb(51,51,51)>';
              $html .= '<span style="font-family:arial,helvetica,sans-serif;font-size:16px">';
              $html .= 'Antes de comenzar a realizar tus cursos, te solicitamos amablemente que confirmes tu <b>Correo electrónico</b> presionando en el siguiente botón:';
              $html .= '</span></p>';
              $html .= '</td>';
              $html .= '</tr>';

              $html .= '<tr>';
              $html .= '<td valign="top" height="100%">';
              $html .= '<p style="text-align: center;">';
              $html .= '<a class="button primary-button" style="text-align:center" href="'.$urlconfirmar.'">CONFIRMAR CORREO ELECTRÓNICO</a>';
              $html .= '</span></p>';
              $html .= '</td>';
              $html .= '</tr>';

              $html .= '</tbody>';
              $html .= '</table>';
        
              $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
              $html .= '<tbody>';
              $html .= '<tr>';
              $html .= '<td style="font-size:6px;line-height:10px;padding:0px 0px 0px 0px" valign="top" align="center">';
              // $html .= '<img style="display:block;max-width:100%!important;width:100%;height:auto!important" src="../assets/img/footer-email.jpg" alt="" tabindex="0" width="600" border="0">';
              $html .= '</td>';
              $html .= '</tr>';
              $html .= '</tbody></table>';
              $html .= '</td>';
              $html .= '</tr>';
              $html .= '</tbody>';
              $html .= '</table>';
              $html .= '</body>';
              $html .= '</div>';
              $html .= '</div>';
              $html .= '</div>';
              $html .= '</div>';
              $html .= '</div>';
              $html .= '</html>';
              $mail->MsgHTML($html);
              $mail->AddAddress($email,"");

              $mail->IsHTML(true);
              $mail->smtpConnect(
              array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                    "allow_self_signed" => true
                )
              )
              );

              if ($mail->Send()) {
                $json = json_encode(array("success" => true, "codigo_usu"=>$datarow["codigo_usu"])); 
              }else{
                $json=json_encode(array("success"=>true,"mensaje"=>$mail->ErrorInfo));
              }

          }else{
            $json = json_encode(array("success" =>false,"mensaje"=>"No se insertó el docente."));
          }
        }else{
          $json = json_encode(array("success" =>false,"mensaje"=>"El numero de identificación ya existe."));
        }
      }else{
        $json = json_encode(array("success" =>false,"mensaje"=>"El E-Mail ingresado ya existe!"));
      }
    break;
   /************************  FIN procesos para usuario.php ********************************/


  /************************  procesos para sedes.php *************************************/
    case 'loadSedes':
      
      if($estado != ''){
          $whe = "WHERE estado_sede = '".$estado."'";
      }else{
        $whe = "";
      }

      $table = $Se->loadSedes($whe);

      $i = 1;
      foreach ($table as $datarow => $data) {

        if($data["estado_sede"] == 'on'){
          $estado = 'Habilitado';
        }else if($data["estado_sede"] == 'off'){
          $estado = 'Inhabilitado';
        }else{
          $estado = 'Error';
        }

        $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar Usuario" onclick="fmodalEditar('.$data["codigo_sede"].', \''.$data["nombre_sede"].'\', \''.$data["estado_sede"].'\')"><i class="fa fa-edit"></i></a>&nbsp';
        $delete = '<a class="btn btn-danger btn-sm purple tooltips" data-original-title="Eliminar" data-rel="tooltip" title="Eliminar" onClick="feditEstado('.$data["codigo_sede"].',\'off\')"><i class="fa fa-trash-o"></i></a>';
        $restore = '<a class="btn btn-success btn-sm purple tooltips" data-original-title="Restaurar" data-rel="tooltip" title="Restaurar" onClick="feditEstado('.$data["codigo_sede"].', \'on\')""><i class="fa fa-undo"></i></a>';
        $options = $edit.$delete;
          
        if ($data["estado_sede"] == 'on') $options = $edit.$delete;
        else $options = $restore;
        array_push($createtable['data'], array($i, $data["nombre_sede"], $estado, $options));

        $i++;

      }
      $json = json_encode($createtable);
    break;

    case 'insertSede':

      $table = $Se->insertSede($nombre_sede, $estado_sede);
      
      $i = 1;
            
      if($i != -1){
        $json = json_encode(array("success" => true, "codigo_sede"=>$i["codigo_sede"])); 
      }else{
        $json = json_encode(array("success" =>false,"message"=>"No se insertÃ³ la usuario"));
      }
    break;

    
    case 'editItemSede':

      $table = $Se->editItemSede($nombre_sede, $codigo_sede);

      if($table){
        $json = json_encode(array("success"=>true));
      }else{
        $json = json_encode(array("success"=>false,"mensaje" =>'Error al editar'));
      }

    break;

    case 'editEstadoSede':
        $table = $Se->editEstadoSede($codigo_sede, $estado);
        $json = json_encode(array("success"=>true));
    break;
  /************************  FIN procesos para sedes.php ********************************/
 

  /************************  procesos para programas.php **********************************/
    case 'loadProgramas':
      
      if($estado != ''){
          $whe = "WHERE estado = '".$estado."'";
      }else{
        $whe = "";
      }

      $table = $Pro->loadProgramas($whe);

      $i = 1;
      foreach ($table as $datarow => $data) {

        if($data["estado"] == 'on'){
          $estado = 'Habilitado';
        }else if($data["estado"] == 'off'){
          $estado = 'Inhabilitado';
        }else{
          $estado = 'Error';
        }

        $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar Programa" onclick="fmodalEditar('.$data["codigo"].', \''.$data["nombre"].'\', \''.$data["estado"].'\')"><i class="fa fa-edit"></i></a>&nbsp;';
        $delete = '<a class="btn btn-danger btn-sm purple tooltips" data-original-title="Eliminar" data-rel="tooltip" title="Eliminar" onClick="feditEstado('.$data["codigo"].',\'off\')"><i class="fa fa-trash-o"></i></a>';
        $restore = '<a class="btn btn-success btn-sm purple tooltips" data-original-title="Restaurar" data-rel="tooltip" title="Restaurar" onClick="feditEstado('.$data["codigo"].', \'on\')""><i class="fa fa-undo"></i></a>';
        $options = $edit.$delete;
          
        if ($data["estado"] == 'on') $options = $edit.$delete;
        else $options = $restore;
        array_push($createtable['data'], array($i, $data["nombre"], $estado, $options));

        $i++;

      }
      $json = json_encode($createtable);
      break;

    case 'insertPrograma':

      $table = $Pro->insertPrograma($nombre, $estado);
      
      $i = 1;
            
      if($i != -1){
        $json = json_encode(array("success" => true, "codigo"=>$i["codigo"])); 
      }else{
        $json = json_encode(array("success" =>false,"message"=>"No se insertÃ³ la usuario"));
      }
    break;

    case 'editItemPrograma':

    $table = $Pro->editItemPrograma($nombre, $codigo);

      if($table){
        $json = json_encode(array("success"=>true));
      }else{
        $json = json_encode(array("success"=>false,"mensaje" =>'Error al editar'));
      }

    break;


    case 'editEstadoPrograma':

        $table = $Pro->editEstadoPrograma($codigo, $estado);
        $json = json_encode(array("success"=>true));

    break;


    case 'loadPerfilesS':
        $table = $Pro->loadPerfilesS();

        if(count($table)>=1){
          $json = json_encode(array("success"=>true,"perfiles" =>$table));
        }else{
          $json = json_encode(array("success"=>false,"mensaje" => "No hay Perfiles disponibles"));
        }
      break;
  /************************  FIN procesos para programas.php *****************************/


  /************************  procesos para aniolectivo.php *********************************/
    case 'loadLectivo':
      
      if($estado != ''){
          $whe = "WHERE estado = '".$estado."'";
      }else{
        $whe = "";
      }

      $table = $al->loadLectivo($whe);

      $i = 1;
      foreach ($table as $datarow => $data) {

        if($data["estado"] == 'on'){
          $estado = 'Habilitado';
        }else if($data["estado"] == 'off'){
          $estado = 'Inhabilitado';
        }else{
          $estado = 'Error';
        }

        $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar AÃ±o Lectivo" onclick="fmodalEditar('.$data["codigo"].', \''.$data["nombre"].'\', \''.$data["estado"].'\')"><i class="fa fa-edit"></i></a>&nbsp;';
        $delete = '<a class="btn btn-danger btn-sm purple tooltips" data-original-title="Eliminar" data-rel="tooltip" title="Eliminar" onClick="feditEstado('.$data["codigo"].',\'off\')"><i class="fa fa-trash-o"></i></a>';
        $restore = '<a class="btn btn-success btn-sm purple tooltips" data-original-title="Restaurar" data-rel="tooltip" title="Restaurar" onClick="feditEstado('.$data["codigo"].', \'on\')""><i class="fa fa-undo"></i></a>';
        $options = $edit.$delete;
          
        if ($data["estado"] == 'on') $options = $edit.$delete;
        else $options = $restore;
        array_push($createtable['data'], array($i, $data["nombre"], $estado, $options));

        $i++;

      }
      $json = json_encode($createtable);
      break;

    case 'insertLectivo':

      $table = $al->insertLectivo($nombre, $estado);
      
      $i = 1;
            
      if($i != -1){
        $json = json_encode(array("success" => true, "codigo"=>$i["codigo"])); 
      }else{
        $json = json_encode(array("success" =>false,"message"=>"No se insertÃ³ la usuario"));
      }
    break;

    case 'editItemLectivo':

    $table = $al->editItemLectivo($nombre, $codigo);

      if($table){
        $json = json_encode(array("success"=>true));
      }else{
        $json = json_encode(array("success"=>false,"mensaje" =>'Error al editar'));
      }

    break;


    case 'editEstadoLectivo':

        $table = $al->editEstadoLectivo($codusuario, $estado);
        $json = json_encode(array("success"=>true));

    break;        
  /************************  FIN procesos para aniolectivo.php ****************************/


  /************************  procesos para estudiante.php *********************************/
    case 'loadEstudiante':
      
      if($estado != ''){
          $whe = "WHERE usu.estado = '".$estado."'";
      }else{
        $whe = "";
      }

      $table = $e->loadEstudiante($whe);

      $i = 1;
      foreach ($table as $datarow => $data) {

        $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar Estudiante" onclick="fmodalEditar('.$data["codigo"].', \''.$data["identificacion"].'\', \''.$data["pnombre"].'\', \''.$data["snombre"].'\', \''.$data["papellido"].'\', \''.$data["sapellido"].'\', \''.$data["genero"].'\', \''.$data["fechanacimiento"].'\', \''.$data["direccion"].'\', \''.$data["telefono"].'\', \''.$data["celular"].'\', \''.$data["email"].'\', \''.$data["codeps"].'\', \''.$data["codtiposangre"].'\', \''.$data["coestrato"].'\')"><i class="fa fa-edit"></i></a>&nbsp';
        $delete = '<a class="btn btn-danger btn-sm purple tooltips" data-original-title="Eliminar" data-rel="tooltip" title="Eliminar" onClick="feditEstado('.$data["codigo_usu"].',\'off\')"><i class="fa fa-trash-o"></i></a>&nbsp';
        $restore = '<a class="btn btn-success btn-sm purple tooltips" data-original-title="Restaurar" data-rel="tooltip" title="Restaurar" onClick="feditEstado('.$data["codigo_usu"].', \'on\')""><i class="fa fa-undo"></i></a>&nbsp';
        $options = $edit;
          
        if ($data["estado"] == 'on') 
          $options = $edit.$delete;
        else 
          $options = $restore;

  if ($data["telefono"] != null){
    $telefono = $data["telefono"];
  }else{
    $telefono = "Sin telÃ©fono";
  }
        array_push($createtable['data'], array($i, $data["identificacion"], $data["nombre_largo"], $data["fechanacimiento"], $data["direccion"], $telefono, $data["celular"], $data["email"], $options));
        $i++;
      }
      $json = json_encode($createtable);

    break;

    case 'insertEstudiante':
      $validarCorreo = $u->ValidarCorreo($email);
      if(count($validarCorreo) == 0){
        $buscar = $u->buscarUsuarioID($identificacion);
        $codperfil = 3;
        $nombre = $pnombre.' '.$snombre.' '.$papellido.' '.$sapellido;
        $estado = 'on';
        if($buscar == ''){
          $token = $u->generatecod();
          $datarow = $u->insertUsuario($email, $identificacion, $identificacion, $codperfil, $estado, $nombre, $email, $token);
          if($datarow != -1){      
              $datarow1 = $e->insertEstudiante($identificacion, $pnombre, $snombre, $papellido, $sapellido, $nombre, $genero, $fecha, $direccion, $telefono, $celular, $email, $codeps, $codtiposangre, $coestrato);
              $urlconfirmar = $u->urlservidor()."ValidateEmailIsReal/".$u->base64url_encode($email.','.$datarow["codigo_usu"].','.$token);

              $html = "<!DOCTYPE html>";
              $html .= "<html>";
              $html .= "<head>";
              $html .= "<title>Instituto Centro de Sistemas S.A.S | System Center</title>";
              $html .= '<link rel="shortcut icon" type="image/png" href="http://201.219.220.123/systemcenter/assets/img/system_ico.ico"/>';
              $html .= '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">';
              $html .= '<link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css" />';
              $html .= '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';
              $html .= '<link rel="stylesheet" href="http://201.219.220.123/systemcenter/assets/css/custom-styles.css" rel="stylesheet">';
              $html .= "<meta charset='UTF-8'>";
              $html .= "<style> 
                    body{
                        text-align:center;
                    }
                  /*button*/
                    .button{
                      display: inline-block;
                      padding:0 10px;
                      color: #4c4c4c;
                      height: 40px;
                      text-align: center;
                      line-height: 35px;
                      white-space: nowrap;
                       cursor: pointer;
                      box-sizing: border-box; 
                      border: 1px solid #CCCCCC;
                      margin-bottom:10px;
                      border-radius:3px;
                      font-weight: bold;
                      outline: 0;
                      text-decoration: none;
                    }
                    .primary-button{
                      color: #fff !important;
                      background: #1c4697!important;
                      border: 1px solid #000971;
                    }
                    .rowY {
                          width: 780px;
                          margin-right: 80px;
                          margin-left: 154px;
                    }
                </style>"; 
              $html .="</head>";
              $html .="<body>";
              $html .= '<div class="container" style="margin-top: 20px;">';
              $html .= '<div class="row">';
              $html .= '<div class="row rowY" style="border: solid 2px #214a81;border-radius: 26px">';
              $html .= '<div class="section-title" style="justify-content: center;display: flex;">';
              $html .= '<div style="float: left;">';
              $html .= '<img src="ban.png" style="margin-left: -1%; margin-top: -1px;">';
              $html .= '</div>';
              $html .= '<div style="float: left;">';
              $html .= '<table style="width:100%;max-width:600px" width="100%" cellspacing="0" cellpadding="0" border="0" align="center">';
              $html .= '<tbody>';
              $html .= '<tr>';
              $html .= '<td role="modules-container" style="padding:0px 0px 0px 0px;color:#000000;text-align:left" width="100%" bgcolor="#ffffff" align="left">';
              $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
              $html .= '<tbody>';
              $html .= '<tr>';
              $html .= '<td style="font-size:6px;line-height:10px;padding:0px 0px 0px 0px" valign="top" align="center">';
              // $html .= '<img style="display:block;max-width:100%!important;width:100%;height:auto!important" src="../assets/img/header-email.jpg" width="600" border="0">';
              $html .= '</td>';
              $html .= '</tr>';
              $html .= '</tbody>';
              $html .= '</table>';
        
              $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
              $html .= '<tbody>';
              $html .= '<tr>';
              $html .= '<td style="padding:0px 0px 20px 0px">';
              $html .= '</td>';
              $html .= '</tr>';
              $html .= '</tbody>';
              $html .= '</table>';

              $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
              $html .= '<tbody>';
              $html .= '<tr>';
              $html .= '<td style="padding:18px 0px 10px 0px;line-height:22px;text-align:inherit" valign="top" height="100%">';
              $html .= '<div style="text-align:center">';
              $html .= '<span style="font-size:22px";font-family:arial,helvetica,sans-serif">';
              $html .= '<strong>Hola, '.$nombre.'</strong>';
              $html .= '</span></div>';
              $html .= '</td>';
              $html .= '</tr>';

              $html .= '<tr>';
              $html .= '<td style="padding:0px 0px 0px 0px;line-height:22px;text-align:inherit" valign="top" height="100%">';
              $html .= '<p font-style:normal;font-variant-ligatures:normal;font-variant-caps:normal;font-weight:400;font-family:Georgia,"Times New Roman","Bitstream Charter",Times,serif;font-size:16px;text-align:justify;color:rgb(51,51,51)>';
              $html .= '<span style="font-family:arial,helvetica,sans-serif;font-size:16px">';
              $html .= 'Te damos la bienvenida a nuestra Institución Educativa <b>System Center</b> <br>';
              $html .= 'Tu usuario es: <b>'.$email.'</b> y tu contraseña: <b>'.$identificacion.'</b> <br>';
              $html .= '<i>Por razones de seguridad recomendamos realizar el cambio de contraseña al momento de realizar sesión por primera vez.</i>';
              $html .= '</span></p>';
              $html .= '</td>';
              $html .= '</tr>';


              $html .= '<tr>';
              $html .= '<td style="padding:0px 0px 0px 0px;line-height:22px;text-align:inherit" valign="top" height="100%">';
              $html .= '<p font-style:normal;font-variant-ligatures:normal;font-variant-caps:normal;font-weight:400;font-family:Georgia,"Times New Roman","Bitstream Charter",Times,serif;font-size:16px;text-align:justify;color:rgb(51,51,51)>';
              $html .= '<span style="font-family:arial,helvetica,sans-serif;font-size:16px">';
              $html .= 'Antes de comenzar a realizar tus cursos, te solicitamos amablemente que confirmes tu <b>Correo electrónico</b> presionando en el siguiente botón:';
              $html .= '</span></p>';
              $html .= '</td>';
              $html .= '</tr>';

              $html .= '<tr>';
              $html .= '<td valign="top" height="100%">';
              $html .= '<p style="text-align: center;">';
              $html .= '<a class="button primary-button" style="text-align:center" href="'.$urlconfirmar.'">CONFIRMAR CORREO ELECTRÓNICO</a>';
              $html .= '</span></p>';
              $html .= '</td>';
              $html .= '</tr>';

              $html .= '</tbody>';
              $html .= '</table>';
        
              $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
              $html .= '<tbody>';
              $html .= '<tr>';
              $html .= '<td style="font-size:6px;line-height:10px;padding:0px 0px 0px 0px" valign="top" align="center">';
              // $html .= '<img style="display:block;max-width:100%!important;width:100%;height:auto!important" src="../assets/img/footer-email.jpg" alt="" tabindex="0" width="600" border="0">';
              $html .= '</td>';
              $html .= '</tr>';
              $html .= '</tbody></table>';
              $html .= '</td>';
              $html .= '</tr>';
              $html .= '</tbody>';
              $html .= '</table>';
              $html .= '</body>';
              $html .= '</div>';
              $html .= '</div>';
              $html .= '</div>';
              $html .= '</div>';
              $html .= '</div>';
              $html .= '</html>';
              $mail->MsgHTML($html);
              $mail->AddAddress($email,"");

              $mail->IsHTML(true);
              $mail->smtpConnect(
              array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                    "allow_self_signed" => true
                )
              )
              );

              if ($mail->Send()) {
                $json = json_encode(array("success" => true, "codigo_usu"=>$datarow["codigo_usu"])); 
              }else{
                $json=json_encode(array("success"=>true,"mensaje"=>$mail->ErrorInfo));
              }

          }else{
            $json = json_encode(array("success" =>false,"mensaje"=>"No se insertó el docente."));
          }
        }else{
          $json = json_encode(array("success" =>false,"mensaje"=>"El numero de identificación ya existe."));
        }
      }else{
        $json = json_encode(array("success" =>false,"mensaje"=>"El E-Mail ingresado ya existe!"));
      }
    break;

    case 'editItemEstudiante':

      $nombre = $pnombre.' '.$snombre.' '.$papellido.' '.$sapellido;


      $buscar = $e->buscarEstudianteIDxCodigo($codigo);

      if($buscar != ''){

          $editar = $u->editUsuarioNombre($buscar["identificacion"], $identificacion, $nombre);

          if($editar == 1){

            $table = $e->editItemEstudiante($identificacion, $pnombre, $papellido, $snombre, $sapellido, $nombre, $genero, $fecha, $direccion, $telefono, $celular, $email, $codeps, $codtiposangre, $coestrato, $codigo);

            if($table == 1){
              $json = json_encode(array("success"=>true,"Mensaje"=>"Datos editados correctamente"));    
            }
            else{
              $json = json_encode(array("success"=>true,"Mensaje"=>"Datos del usurio editados correctamente pero no se pudo editar los datos del estudiante"));     
            }
          }
          else
          {
             $json = json_encode(array("success"=>false,"mensaje" =>'Error al editar'));
          }
         
      }else{
        $json = json_encode(array("success"=>false,"mensaje" =>'Error al editar'));
      }
    break;

    case 'editEstadoEstudiante':
      $table = $e->editEstadoEstudiante($codigo, $estado);
      $json = json_encode(array("success"=>true));
    break;
  /************************  FIN procesos para estudiante.php ****************************/


  /************************  procesos para modulos.php **********************************/
    case 'loadSModulos':
      
      if($estado != ''){
          $whe = "WHERE estado = '".$estado."'";
      }else{
        $whe = "";
      }

      $table = $mo->loadSModulos($whe);

      $i = 1;
      foreach ($table as $datarow => $data) {

        if($data["estado"] == 'on'){
          $estado = 'Habilitado';
        }else if($data["estado"] == 'off'){
          $estado = 'Inhabilitado';
        }else{
          $estado = 'Error';
        }

        $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar Modulo" onclick="fmodalEditar('.$data["code"].', \''.$data["modulo"].'\', \''.$data["descripcion"].'\', '.$data["codprograma"].')"><i class="fa fa-edit"></i></a>&nbsp;';
        $delete = '<a class="btn btn-danger btn-sm purple tooltips" data-original-title="Eliminar" data-rel="tooltip" title="Eliminar" onClick="feditEstado('.$data["code"].',\'off\')"><i class="fa fa-trash-o"></i></a>';
        $restore = '<a class="btn btn-success btn-sm purple tooltips" data-original-title="Restaurar" data-rel="tooltip" title="Restaurar" onClick="feditEstado('.$data["code"].', \'on\')""><i class="fa fa-undo"></i></a>';
        $options = $edit.$delete;
          
        if ($data["estado"] == 'on') $options = $edit.$delete;
        else $options = $restore;
        array_push($createtable['data'], array($i, $data["modulo"], $data["descripcion"], $data["programa"], $estado, $options));

        $i++;

      }
      $json = json_encode($createtable);
      break;

    case 'insertModulo':

      $table = $mo->insertModulo($nombre, $descripcion, $programa, $estado);
      
      $i = 1;
            
      if($i != -1){
        $json = json_encode(array("success" => true, "codigo"=>$i["codigo"])); 
      }else{
        $json = json_encode(array("success" =>false,"message"=>"No se insertÃ³ la usuario"));
      }
      break;

    
    case 'editItemModulo':

    $table = $mo->editItemModulo($nombre, $descripcion, $codigo);

      if($table){
        $json = json_encode(array("success"=>true));
      }else{
        $json = json_encode(array("success"=>false,"mensaje" =>'Error al editar'));
      }

    break;

    case 'editEstadoModulo':
        $table = $mo->editEstadoModulo($codigo, $estado);
        $json = json_encode(array("success"=>true));
    break;
  /************************  procesos para modulos.php *********************************/


  /************************  procesos para matriculas.php ******************************/
      
    case 'loadSedeM':
      $table = $ma->loadSedeM();
      $json = json_encode($table);
    break;

    case 'loadLectivoM':
      $table = $ma->loadLectivoM();
      $json = json_encode($table);
    break;

    case 'loadProgramaM':
      $table = $ma->loadProgramaM();
      $json = json_encode($table);
    break;

    case 'loadmpago':
      $table = $ma->loadMetodoPago();
      $json = json_encode($table);
    break;

    case 'loadDatosM':   
      $whe = '';
      $html = '';
      $i = 1;
      if($prog != ''){
        $whe = " AND pro.codigo = '".$prog."'";
        if ($sede != '') {
            $whe = " AND pro.codigo = '".$prog."' AND sed.codigo_sede = '".$sede."'";
            if ($anolect != '') {
              $whe = " AND pro.codigo = '".$prog."' AND sed.codigo_sede = '".$sede."' AND ani.codigo = '".$anolect."'";
            }
          }
      }    

      $table = $ma->loadDatosM($whe);
  if ($table != null){
  foreach ($table as $datarow => $data) {
            $html = '<tr><td>'.$i.'</td>';
            $html .=     '<td style="display:none">'.$data["codigo"].'</td>';
            $html .=     '<td >'.$data["modulo"].'</td>';
            $html .=     '<td>'.$data["programa"].'</td>';
            $html .=     '<td>'.$data["fechainicio"].'</td>';
            $html .=     '<td>'.$data["fechafin"].'</td>';
            $html .=     '<td>'.$data["valor"].'</td>';
            $html .=     '<td><input name="modulo" id="'.$data["codigo"].'" value="'.$data["codigo"].'" type="checkbox"></td>';
            $html .= '</tr>';
	$i++;
  }      
                    
      }else{
  $html = '<tr><td colspan="8">No existe.</td></tr>'; 
  }
      
      $json = json_encode(array("success"=>true, "html" => $html));
    break;
  
    case 'loadEstudianteM':

      $whe = "";

      $table = $ma->loadEstudianteM($whe);
        
      $i = 1;
  
      foreach ($table as $datarow => $data) {
        array_push($createtable['data'], array($i, $data["identificacion"], $data["nombre_largo"], $data["direccion"], $data["telefono"], $data["celular"], $data["email"], '<input name="estudiante" id="'.$data["codigo"].'" value="'.$data["codigo"].'"type="text">'.'" type="checkbox">'));
        $i++;
      }

      $json = json_encode($createtable);

    break;

    case 'insertMatricula':
      $datarow = $ma->insertMatricula($codes, $codprograma, $valor);
      $json = json_encode(array("success" => true, "codigo" => $datarow["codigo"])); 
    break;

    case 'buscarEstudiantexID':
      $html = '';
      $i =
      $table = $ma->buscarEstudiantexID($identificacion);

      
        foreach ($table as $datarow => $data) {
            $html .=   '<tr><td style="display:none">'.$data["codigo"].'</td>';
            $html .=     '<td>'.$data["identificacion"].'</td>';
            $html .=     '<td>'.$data["nombre_largo"].'</td>';
            $html .=     '<td>'.$data["direccion"].'</td>';
            $html .=     '<td>'.$data["telefono"].'</td>';
            $html .=     '<td>'.$data["celular"].'</td>';
            $html .=     '<td>'.$data["email"].'</td>';
            $html .=     '<td><input type="text" name="valorPago" class="form-control"></td>';
            $html .=     '<td><input type="checkbox" name="matricula"></td>';
            $html .=  '</tr>';
            $i++;
        }
        

      if ($html != '') {
        $json = json_encode(array("success"=>true, "html" => $html));
      }else{
        $json = json_encode(array("success"=>false, "html" => $html));
      }
      

    break;

    case 'insertpago':
      $table = $ma->insertPago($codestudiante,$pCantidad, $pMetodoPago, $pReferencia);
      $json = json_encode(array("success" => true)); 
    break;

    case 'notRepetMatricula':
      $query = $ma->notRepetMatricula($codes, $codprograma);
      if ($query) {
        $json = json_encode(array("success" => true)); 
      }else{
        $json = json_encode(array("success" => false)); 
      }
      
    break;
  /************************  fin procesos para matriculas.php *************************/

  /************************  procesos para gestionartemas.php *****************************/
    case 'loadProgr':
      $table = $t->loadPrograma();
      $json = json_encode($table);
    break;

    case 'loadMod':
      $table = $t->loadModulo();
      $json = json_encode($table);
    break;

    case 'loadSed':
      $table = $t->loadSede();
      $json = json_encode($table);
    break;

    case 'loadAnioLec':
      $table = $t->loadAnoLect();
      $json = json_encode($table);
    break;

    case 'loadGestionarTemas':
      
      if($gtprog != ''){
          $whe = "WHERE prm.codprograma = '".$gtprog."'";
          if ($gtmod != '') {
            $whe = "WHERE prm.codprograma = '".$gtprog."' AND prm.codmodulo = '".$gtmod."'";     
            if ($gtsede != '') {
              $whe = "WHERE prm.codprograma = '".$gtprog."' AND prm.codmodulo = '".$gtmod."' AND prm.codsede = '".$gtsede."'";     
            }
              if ($gtanio != '') {
                $whe = "WHERE prm.codprograma = '".$gtprog."' AND prm.codmodulo = '".$gtmod."' AND prm.codsede = '".$gtsede."' AND prm.codanio ='".$gtanio."'";     
              }
          }
      }else{
          $whe = "";
      }
     
      $table = $t->loadGestionarTemas($whe);

      $i = 1;
      foreach ($table as $datarow => $data) {

        $nuevo = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Nuevo Tema" onclick="fmodalNuevoTema('.$data["codigo"].')"><i class="fa fa-plus"></i></a>';

        
        array_push($createtable['data'], array($i, $data["programa"], $data["modulo"], $data["sede"], $data["fechainicio"], $data["fechafin"], $data["ano"], $nuevo));

        $i++;

      }
      $json = json_encode($createtable);
    break;

    case 'loadAgregarTemas':
      $table = $t->loadAgregarTemas($codprogmodulo);

      $i = 1;
      foreach ($table as $datarow => $data) {

        if($data["estado"] == 'on'){
          $estado = 'Habilitado';
        }else if($data["estado"] == 'off'){
          $estado = 'Inhabilitado';
        }else{
          $estado = 'Error';
        }

        $agregar = '<a class="btn btn-success btn-sm tooltips" data-rel="tooltip" data-placement="tooltip" title="Agregar Actividades" onclick="fmodalnuevaActividad('.$data["codigo"].')"><i class="fa fa-plus"></i></a>';

        $delete = '<a class="btn btn-danger btn-sm purple tooltips" data-rel="tooltip" data-original-title="Eliminar" title="Eliminar" onClick="editEstadoTemas('.$data["codigo"].',\'off\')"><i class="fa fa-trash-o"></i></a>&nbsp;';

        $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar Tema" onclick="fmodalEditar('.$data["codigo"].', \''.$data["nombre"].'\')"><i class="fa fa-edit"></i></a>&nbsp;';

        $restore = '<a class="btn btn-success btn-sm purple tooltips" data-original-title="Restaurar" data-rel="tooltip" title="Restaurar" onClick="editEstadoTemas('.$data["codigo"].', \'on\')""><i class="fa fa-undo"></i></a>';

        if($data["estado"] == 'on'){
          $options = $delete;
        }else if($data["estado"] == 'off'){
          $options = $restore;
        }else{
          $options = 'Error';
        }

        if ($data["estado"] == 'on') $options = $edit.$delete.$agregar;
        else $options = $restore;

        array_push($createtable['data'], array($i, $data["nombre"], $estado, $options));
        $i++;
      }

      $json = json_encode($createtable);
    break;

    case 'loadActividades':
      $table = $t->loadActividades($codigotema);

      $i = 1;
      foreach ($table as $datarow => $data) {

        if($data["estado"] == 'on'){
          $estado = 'Habilitado';
        }else if($data["estado"] == 'off'){
          $estado = 'Inhabilitado';
        }else{
          $estado = 'Error';
        }

        $delete = '<a class="btn btn-danger btn-sm purple tooltips" data-rel="tooltip" data-original-title="Eliminar" title="Eliminar" onClick="editEstadoActividades('.$data["codigo"].',\'off\')"><i class="fa fa-trash-o"></i></a>';

        $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar Tema" onclick="fmodalEditarActividad('.$data["codigo"].', \''.$data["nombre"].'\')"><i class="fa fa-edit"></i></a>';

        $restore = '<a class="btn btn-success btn-sm purple tooltips" data-original-title="Restaurar" data-rel="tooltip" title="Restaurar" onClick="editEstadoActividades('.$data["codigo"].', \'on\')""><i class="fa fa-undo"></i></a>';

        if($data["estado"] == 'on'){
          $options = $delete;
        }else if($data["estado"] == 'off'){
          $options = $restore;
        }else{
          $options = 'Error';
        }

        if ($data["estado"] == 'on') $options = $edit.$delete;
        else $options = $restore;

        array_push($createtable['data'], array($i, $data["nombre"], $estado, $options));
        $i++;
      }

      $json = json_encode($createtable);
    break;

    case 'editEstadoTemas':
      $table = $t->editEstadoTemas($codigo, $estado);
      $json = json_encode(array("success"=>true));
    break;

    case 'editEstadoActividad':
      $table = $t->editEstadoActividad($codigo, $estado);
      $json = json_encode(array("success"=>true));
    break;

    case 'insertTemas':
        $table = $t-> insertTemas($nombretema, $codprogramamod, $estadotema);
        $json = json_encode(array("success"=>true));
    break;

    case 'editTema':
      $table = $t->editTema($nombre, $codigo);

      if($table){
        $json = json_encode(array("success"=>true));
      }else{
        $json = json_encode(array("success"=>false,"mensaje" =>'Error al editar'));
      }
    break;

    case 'insertActividad':
        $table = $t-> insertActividad($nombreactividad, $codigotema, $estadoactividad);
        $json = json_encode(array("success"=>true));
    break;

    case'editActividad':
      $table = $t->editActividad($codigoactividad, $nombreactividad);

      if($table){
        $json = json_encode(array("success"=>true));
      }else{
        $json = json_encode(array("success"=>false,"mensaje" =>'Error al editar'));
      }
    break;
  /************************  fin procesos para gestionartemas.php ************************/


  /************************  procesos para programamodulo.php *******************************/
    case 'loadProgramaModulo':
      
      if($estado != ''){
          $whe = "WHERE prm.estado = '".$estado."'";
      }else{
        $whe = "";
      }

      $table = $pm->loadProgramaModulo($whe);

      $i = 1;
      foreach ($table as $datarow => $data) {

        if($data["estado"] == 'on'){
          $estado = 'Habilitado';
        }else if($data["estado"] == 'off'){
          $estado = 'Inhabilitado';
        }else{
          $estado = 'Error';
        }

        $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar ProgramaciÃ³n Modulos" onclick="fmodalEditar('.$data["codigo"].', '.$data["codpro"].', '.$data["codmod"].', '.$data["codsed"].', \''.$data["fechainicio"].'\', \''.$data["fechafin"].'\', '.$data["codani"].', \''.$data["estado"].'\', '.$data["valor"].')"><i class="fa fa-edit"></i></a>&nbsp';

        $delete = '<a class="btn btn-danger btn-sm purple tooltips" data-original-title="Eliminar" data-rel="tooltip" title="Eliminar" onClick="feditEstado('.$data["codigo"].',\'off\')"><i class="fa fa-trash-o"></i></a>';

        $restore = '<a class="btn btn-success btn-sm purple tooltips" data-original-title="Restaurar" data-rel="tooltip" title="Restaurar" onClick="feditEstado('.$data["codigo"].', \'on\')""><i class="fa fa-undo"></i></a>';
        $options = $edit.$delete;
          
        if ($data["estado"] == 'on') $options = $edit.$delete;
        else $options = $restore;
        array_push($createtable['data'], array($i, $data["programa"], $data["modulo"], $data["sede"], $data["fechainicio"], $data["fechafin"], $data["ano"], $estado, "$ ".number_format($data["valor"]), $options));

        $i++;

      }
      $json = json_encode($createtable);
    break;

    case 'loadPrograma':
      $table = $pm->loadPrograma();
      $json = json_encode($table);
    break;

    case 'loadModulo':
      $table = $pm->loadModulo($gtprog);
      $json = json_encode($table);
    break;

    case 'loadSede':
      $table = $pm->loadSede();
      $json = json_encode($table);
    break;

    case 'loadAnoLect':
      $table = $pm->loadAnoLect();
      $json = json_encode($table);
    break;

    case 'editEstadoProgrModulo':
        $table = $pm->editEstadoProgrModulo($codigo, $estado);
        $json = json_encode(array("success"=>true));
    break;

    case 'insertProgrModulo':
      $table = $pm->insertPrograModulo($programa, $modulo, $sedepm, $fechaini, $fechafin, $anolectivo, $estadopm, $valor);
      $i = 1;
            
      if($i != -1){
        $json = json_encode(array("success" => true, "codigo"=>$i["codigo"])); 
      }else{
        $json = json_encode(array("success" =>false,"message"=>"No se insertÃ³ el registro."));
      }
    break;

    case 'editItemProgrModulo':
      $table = $pm->editItemProgrModulo($codigo, $programa, $modulo, $sedepm, $fechaini, $fechafin, $anolectivo, $estadopm, $valor);

      if($table){
        $json = json_encode(array("success"=>true));
      }else{
        $json = json_encode(array("success"=>false,"mensaje" =>'Error al editar'));
      }
    break;
  /************************  FIN procesos para programamodulo.php **************************/


  /************************  procesos para moduloprogramacion.php ****************************/

      case 'loadPmodulos':
        if($estado != ''){
            $whe = " WHERE pmp.estado = '".$estado."' ";
        }else{
          $whe = "";
        }
                
        $table = $mp->loadPmodulos($whe);

        $i = 1;
        foreach ($table as $datarow => $data) {
          
          if($data["estado"] == 'on'){
            $estado = 'Habilitado';
          }else if($data["estado"] == 'off'){
            $estado = 'Inhabilitado';
          }else{
            $estado = 'Error';
          }
          
          $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar Programa" onclick="fmodalEditar('.$data["codigo"].', \''.$data["nombre"].'\', \''.$data["fecha"].'\', \''. $data["horainicio"].'\', \''.$data["horafin"].'\', '.$data["cuposminimo"].', '.$data["cuposmaximo"].', \''. $data["salon"].'\', \''.$data["nombre_largo"].'\')"><i class="fa fa-edit"></i></a>&nbsp';

          $delete = '<a class="btn btn-danger btn-sm purple tooltips" data-original-title="Eliminar" data-rel="tooltip" title="Eliminar" onClick="feditEstado('.$data["codigo"].',\'off\')"><i class="fa fa-trash-o"></i></a>';

          $restore = '<a class="btn btn-success btn-sm purple tooltips" data-original-title="Restaurar" data-rel="tooltip" title="Restaurar" onClick="feditEstado('.$data["codigo"].', \'on\')""><i class="fa fa-undo"></i></a>';
        
          $options = $edit.$delete;


          if ($data["estado"] == 'on') $options = $edit.$delete;
          else $options = $restore;


          array_push($createtable['data'], array($i, $data["nombre"], $data["fecha"], $data["horainicio"], $data["horafin"], $data["cuposminimo"], $data["cuposmaximo"], $data["salon"], $data["nombre_largo"], $options));

          $i++;

        }
        $json = json_encode($createtable);
      break;

      case 'cbLoadAnio':
        $row = $mp->CodAnioLectivo();
        $html = '<option value='.$row["codigo"].'>'.$row["nombre"].'</option>'; 
        $json = json_encode(array("success"=>true, "html" => $html)); 
      break;

      case 'cbLoadProgramas':
          $table = $mp->LoadProgramas($codequery);
          if($table != null){
            $query = "IN (";
            foreach ($table as $datarow => $data) {
                $query .= $data["codprograma"].","; 
            }
            $query = substr($query, 0, -1);
            $query .= ")";

            $table2 = $mp->LoadProgramas2($query);

            $json = json_encode($table2);
          }else{
            $json = json_encode($table);
          }
      break;

      case 'cbLoadModulos':
          $table = $mp->LoadModulos($codequery, $codequery1);

          if($table != null){
            $query = "IN (";
            foreach ($table as $datarow => $data) {
                $query .= $data["codmodulo"].","; 
            }
            $query = substr($query, 0, -1);
            $query .= ")";

            $table2 = $mp->LoadModulos2($query);

            $json = json_encode($table2);
          }else{
            $json = json_encode($table);
          }
      break;

      case 'cbLoadSedes':
          $table = $mp->LoadSedes($codequery, $codequery1, $codequery2);

          if($table != null){
            $query = "IN (";
            foreach ($table as $datarow => $data) {
                $query .= $data["codsede"].","; 
            }
            $query = substr($query, 0, -1);
            $query .= ")";

            $table2 = $mp->LoadSedes2($query);

            $json = json_encode($table2);
          }else{
            $json = json_encode($table);
          }
      break;


      case 'cbLoadDisponibilidad':
          $table = $mp->LoadDisponibilidad($codequery, $codequery1, $codequery2, $codequery3);
    
          $html = '<option value=\'\'>Seleccione...</option>';
          foreach ($table as $datarow => $data) {
              $fecha = $data["fechainicio"].'_'.$data["fechafin"];
              $html .= '<option value="'.$data["codigo"].'">'.$fecha.'</option>'; 
          }
          
          $json = json_encode(array("success"=>true, "html" => $html)); 
      break;

      case 'cbLoadDocente':
          $row = $mp->LoadDocente($codequery);
        if ($row != null) {
          $html = '<option value="'.$row["cod"].'">'.$row["nombre"].'</option>'; 
        }else{
          $html = '<option value=\'\'>No hay docente asignado.</option>';
        }         
            
          $json = json_encode(array("success"=>true, "html" => $html)); 
      break;

      case 'loadModulosDocente':
          $table = $mp->loadModulosDocente($codp);
          $json = json_encode($table);
      break;

      case 'EditLoadModulosPrograma':
          $table = $mp->EditLoadModulosPrograma();
          $json = json_encode($table);
      break;

      case 'EditloadModulosDocente':
          $table = $mp->EditloadModulosDocente($codp);
          $json = json_encode($table);
      break;

      case 'insertPmodulos':

        $i = 1;

        $query = $mp->insertPmodulos($codprograma_modulo, $fecha, $horainicio, $horafin, $cuposminimo, $cuposmaximo, $salon, $codmodulodocente, $estado);
        $json = json_encode($query);
    
        if($i != -1){
          $json = json_encode(array("success" => true)); 
        }else{
          $json = json_encode(array("success" =>false));
        }
      break;

      case 'editEstadoPmodulo':
        $query = $mp->editEstadoPmodulo($cod, $estado);
        if ($query) {
          $json = json_encode(array("success"=>true));  
        }else{
          $json = json_encode(array("success"=>false, "mensaje"=>'No se pudo actualizar el estado'));
        }
      break;

      case 'editModalModuloP':

        $query = $mp->editModalModuloP($codigo, $fecha, $horainicio, $horafin, $cuposminimo, $cuposmaximo, $salon);

        if($query){
          $json = json_encode(array("success"=>true));
        }else{
          $json = json_encode(array("success"=>false,"mensaje" => "No se pudo actualizar el registro: Editar Modal Modulo."));
        }
      break;


      case 'validarfecha':

        list($fechaini, $fechafin) = explode('_', $codequery);

        $start_ts  = strtotime($fechaini);
        $end_ts  = strtotime($fechafin);
        $user_ts = strtotime($codequery1);

        if ($user_ts >= $start_ts && $user_ts <= $end_ts) {
          $json = json_encode(array("success"=>true));
        } else {
          $json = json_encode(array("success"=>false));
        }
      break;
  /************************  fin procesos para moduloprogramacion.php ************************/
  

  /************************  procesos para informaciondocente.php *******************************/

      case 'loadInfoDocente':
        
        if($estado != ''){
            $whe = " WHERE estado = '".$estado."' ";
        }else{
          $whe = "";
        }

        $table = $di->loadInfoDocente($whe);

        $i = 1;
        foreach ($table as $datarow => $data) {

          if($data["estado"] == 'on'){
            $estado = 'Habilitado';
          }else if($data["estado"] == 'off'){
            $estado = 'Inhabilitado';
          }else{
            $estado = 'Error';
          }

          $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar Modulo" onclick="fmodalEditar('.$data["codigo"].', \''.$data["identificacion"].'\', \''.$data["nombre"].'\',  \''.$data["apellido"].'\', \''.$data["telefono"].'\', \''.$data["celular"].'\', \''.$data["email"].'\', \''.$data["estado"].'\')"><i class="fa fa-edit"></i></a>&nbsp;';
          $delete = '<a class="btn btn-danger btn-sm purple tooltips" data-original-title="Eliminar" data-rel="tooltip" title="Eliminar" onClick="feditEstado('.$data["codigo"].',\'off\')"><i class="fa fa-trash-o"></i></a>';
          $restore = '<a class="btn btn-success btn-sm purple tooltips" data-original-title="Restaurar" data-rel="tooltip" title="Restaurar" onClick="feditEstado('.$data["codigo"].', \'on\')""><i class="fa fa-undo"></i></a>';
          $options = $edit.$delete;
            
          if ($data["estado"] == 'on') $options = $edit.$delete;
          else $options = $restore;
          array_push($createtable['data'], array($i, $data["identificacion"], $data["nombre_largo"], $data["telefono"], $data["celular"], $data["email"], $estado, $options));

          $i++;

        }
        $json = json_encode($createtable);
      break;


      case 'insertDocente':
      $validarCorreo = $u->ValidarCorreo($email);
        if(count($validarCorreo) == 0){
          $buscar = $u->buscarUsuarioID($identificacion);
          $nombre_largo = $nombre.' '.$apellido;
          $codperfil = 4;
          if($buscar == ''){
            $token = $u->generatecod();
            $datarow = $u->insertUsuario($email, $identificacion, $identificacion, $codperfil, $estado, $nombre_largo, $email, $token);
            if($datarow != -1){      
                $datarow1 = $di->insertDocente($identificacion, $nombre, $apellido, $nombre_largo, $telefono, $celular, $email, $estado);    

                $urlconfirmar = $u->urlservidor()."ValidateEmailIsReal/".$u->base64url_encode($email.','.$datarow["codigo_usu"].','.$token);

                $html = "<!DOCTYPE html>";
                $html .= "<html>";
                $html .= "<head>";
                $html .= "<title>Instituto Centro de Sistemas S.A.S | System Center</title>";
                $html .= '<link rel="shortcut icon" type="image/png" href="assets/img/system_ico.ico"/>';
                $html .= "<meta charset='UTF-8'>";
                $html .= "<style> 
                    body{
                        text-align:center;
                    }
                  /*button*/
                    .button{
                      display: inline-block;
                      padding:0 10px;
                      color: #4c4c4c;
                      height: 40px;
                      text-align: center;
                      line-height: 35px;
                      white-space: nowrap;
                       cursor: pointer;
                      box-sizing: border-box; 
                      border: 1px solid #CCCCCC;
                      margin-bottom:10px;
                      border-radius:3px;
                      font-weight: bold;
                      outline: 0;
                      text-decoration: none;
                    }
                    .primary-button{
                      color: #fff !important;
                      background: #1c4697!important;
                      border: 1px solid #000971;
                    }
                </style>";
                $html .="</head>";
                $html .="<body>";
                $html .= '<table style="width:100%;max-width:600px" width="100%" cellspacing="0" cellpadding="0" border="0" align="center">';
                $html .= '<tbody>';
                $html .= '<tr>';
                $html .= '<td role="modules-container" style="padding:0px 0px 0px 0px;color:#000000;text-align:left" width="100%" bgcolor="#ffffff" align="left">';
                $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
                $html .= '<tbody>';
                $html .= '<tr>';
                $html .= '<td style="font-size:6px;line-height:10px;padding:0px 0px 0px 0px" valign="top" align="center">';
                // $html .= '<img style="display:block;max-width:100%!important;width:100%;height:auto!important" src="../assets/img/header-email.jpg" width="600" border="0">';
                $html .= '</td>';
                $html .= '</tr>';
                $html .= '</tbody>';
                $html .= '</table>';
          
                $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
                $html .= '<tbody>';
                $html .= '<tr>';
                $html .= '<td style="padding:0px 0px 20px 0px">';
                $html .= '</td>';
                $html .= '</tr>';
                $html .= '</tbody>';
                $html .= '</table>';

                $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
                $html .= '<tbody>';
                $html .= '<tr>';
                $html .= '<td style="padding:18px 0px 10px 0px;line-height:22px;text-align:inherit" valign="top" height="100%">';
                $html .= '<div style="text-align:center">';
                $html .= '<span style="font-size:22px";font-family:arial,helvetica,sans-serif">';
                $html .= '<strong>Hola, '.$nombre_largo.'</strong>';
                $html .= '</span></div>';
                $html .= '</td>';
                $html .= '</tr>';

                $html .= '<tr>';
                $html .= '<td style="padding:0px 0px 0px 0px;line-height:22px;text-align:inherit" valign="top" height="100%">';
                $html .= '<p font-style:normal;font-variant-ligatures:normal;font-variant-caps:normal;font-weight:400;font-family:Georgia,"Times New Roman","Bitstream Charter",Times,serif;font-size:16px;text-align:justify;color:rgb(51,51,51)>';
                $html .= '<span style="font-family:arial,helvetica,sans-serif;font-size:16px">';
                $html .= 'Te damos la bienvenida a nuestra Institución Educativa <b>System Center</b> <br>';
                $html .= 'Tu usuario es: <b>'.$email.'</b> y tu contraseña: <b>'.$identificacion.'</b> <br>';
                $html .= '<i>Por razones de seguridad recomendamos realizar el cambio de contraseña al momento de realizar sesión por primera vez.</i>';
                $html .= '</span></p>';
                $html .= '</td>';
                $html .= '</tr>';


                $html .= '<tr>';
                $html .= '<td style="padding:0px 0px 0px 0px;line-height:22px;text-align:inherit" valign="top" height="100%">';
                $html .= '<p font-style:normal;font-variant-ligatures:normal;font-variant-caps:normal;font-weight:400;font-family:Georgia,"Times New Roman","Bitstream Charter",Times,serif;font-size:16px;text-align:justify;color:rgb(51,51,51)>';
                $html .= '<span style="font-family:arial,helvetica,sans-serif;font-size:16px">';
                $html .= 'Antes de comenzar a realizar tus cursos, te solicitamos amablemente que confirmes tu <b>Correo electrónico</b> presionando en el siguiente botón:';
                $html .= '</span></p>';
                $html .= '</td>';
                $html .= '</tr>';

                $html .= '<tr>';
                $html .= '<td valign="top" height="100%">';
                $html .= '<p style="text-align: center;">';
                $html .= '<a class="button primary-button" style="text-align:center" href="'.$urlconfirmar.'">CONFIRMAR CORREO ELECTRÓNICO</a>';
                $html .= '</span></p>';
                $html .= '</td>';
                $html .= '</tr>';

                $html .= '</tbody>';
                $html .= '</table>';
          
                $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
                $html .= '<tbody>';
                $html .= '<tr>';
                $html .= '<td style="font-size:6px;line-height:10px;padding:0px 0px 0px 0px" valign="top" align="center">';
                // $html .= '<img style="display:block;max-width:100%!important;width:100%;height:auto!important" src="../assets/img/footer-email.jpg" alt="" tabindex="0" width="600" border="0">';
                $html .= '</td>';
                $html .= '</tr>';
                $html .= '</tbody></table>';
                $html .= '</td>';
                $html .= '</tr>';
                $html .= '</tbody>';
                $html .= '</table>';
                $html .= '</body>';
                $html .= '</html>';
                $mail->MsgHTML($html);
                $mail->AddAddress($email,"");

                $mail->IsHTML(true);
                $mail->smtpConnect(
                array(
                  "ssl" => array(
                      "verify_peer" => false,
                      "verify_peer_name" => false,
                      "allow_self_signed" => true
                  )
                )
                );

                if ($mail->Send()) {
                  $json = json_encode(array("success" => true, "codigo_usu"=>$datarow["codigo_usu"])); 
                }else{
                  $json=json_encode(array("success"=>true,"mensaje"=>$mail->ErrorInfo));
                }

            }else{
              $json = json_encode(array("success" =>false,"mensaje"=>"No se insertó el docente."));
            }
          }else{
            $json = json_encode(array("success" =>false,"mensaje"=>"El numero de identificación ya existe."));
          }
        }else{
          $json = json_encode(array("success" =>false,"mensaje"=>"El E-Mail ingresado ya existe!"));
        }
      break;

      case 'editEstadoDocente':
          $query = $di->editEstadoDocente($cod, $estado);
          if ($query) {
            $json = json_encode(array("success"=>true));  
          }else{
            $json = json_encode(array("success"=>false, "mensaje"=>'No se pudo actualizar el estado'));
          }
      break;


      case 'editItemDocente':

          $nombre_largo = $nombre.' '.$apellido;

          $query = $di->editItemDocente($identificacion, $nombre, $apellido, $nombre_largo, $telefono, $celular, $email, $codigo);

          if($query){
            $json = json_encode(array("success"=>true));
          }else{
            $json = json_encode(array("success"=>false,"mensaje" => "No se pudo actualizar el registro: Editar Modal Modulo."));
          }
      break;
  /************************  fin procesos para informaciondocente.php **************************/

 /*****************************procesos para asignaciondocente.php*******************************/

      case 'loadDocente':
        
        if($estado != ''){
            $whe = " WHERE pmd.estado = '".$estado."' ";
        }else{
          $whe = "";
        }

        $table = $d->loadDocente($whe);

        $i = 1;
        foreach ($table as $datarow => $data) {

          if($data["estado"] == 'on'){
            $estado = 'Habilitado';
          }else if($data["estado"] == 'off'){
            $estado = 'Inhabilitado';
          }else{
            $estado = 'Error';
          }

          // $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar Docente" onclick="fmodalEditar('.$data["codigo"].', \''.$data["coddocente"].'\', \''.$data["codprograma_modulo"].'\', \''.$data["estado"].'\')"><i class="fa fa-edit"></i></a>&nbsp;';
          $delete = '<a class="btn btn-danger btn-sm purple tooltips" data-original-title="Eliminar" data-rel="tooltip" title="Eliminar" onClick="feditEstado('.$data["codigo"].',\'off\')"><i class="fa fa-trash-o"></i></a>';
          $restore = '<a class="btn btn-success btn-sm purple tooltips" data-original-title="Restaurar" data-rel="tooltip" title="Restaurar" onClick="feditEstado('.$data["codigo"].', \'on\')""><i class="fa fa-undo"></i></a>';
          $options = $delete;
            
          if ($data["estado"] == 'on') $options = $delete;
          else $options = $restore;
          array_push($createtable['data'], array($i, $data["nombre_largo"], $data["nombre"], $estado, $options));

          $i++;

        }
        $json = json_encode($createtable);
     break;

     case 'EstadoDocente':
        $query = $d->EstadoDocente($codigo, $estado);
        $json = json_encode(array("success"=>true));
     break;

      case 'loadDocentes':
          $table = $d->loadDocentes();
          $json = json_encode($table);
      break;

      case 'cbLoadAnioDocente':
        $row = $d->CodAnioLectivo();
        $html = '<option value='.$row["codigo"].'>'.$row["nombre"].'</option>'; 
        $json = json_encode(array("success"=>true, "html" => $html)); 
      break;

      case 'cbLoadProgramasDocente':
          $table = $d->LoadProgramas($codequery);
          if($table != null){
            $query = "IN (";
            foreach ($table as $datarow => $data) {
                $query .= $data["codprograma"].","; 
            }
            $query = substr($query, 0, -1);
            $query .= ")";

            $table2 = $d->LoadProgramas2($query);

            $json = json_encode($table2);
          }else{
            $json = json_encode($table);
          }
      break;

      case 'cbLoadModulosDocente':
          $table = $d->LoadModulos($codequery, $codequery1);

          if($table != null){
            $query = "IN (";
            foreach ($table as $datarow => $data) {
                $query .= $data["codmodulo"].","; 
            }
            $query = substr($query, 0, -1);
            $query .= ")";

            $table2 = $d->LoadModulos2($query);

            $json = json_encode($table2);
          }else{
            $json = json_encode($table);
          }
      break;

      case 'cbLoadSedesDocente':
          $table = $d->LoadSedes($codequery, $codequery1, $codequery2);

          if($table != null){
            $query = "IN (";
            foreach ($table as $datarow => $data) {
                $query .= $data["codsede"].","; 
            }
            $query = substr($query, 0, -1);
            $query .= ")";

            $table2 = $d->LoadSedes2($query);

            $json = json_encode($table2);
          }else{
            $json = json_encode($table);
          }
      break;

      case 'cbLoadDisponibilidadDocente':
          $table = $d->LoadDisponibilidad($codequery, $codequery1, $codequery2, $codequery3);
    
          $html = '<option value=\'\'>Seleccione...</option>';
          foreach ($table as $datarow => $data) {
              $fecha = $data["fechainicio"].'_'.$data["fechafin"];
              $html .= '<option value="'.$data["codigo"].'">'.$fecha.'</option>'; 
          }
          
          $json = json_encode(array("success"=>true, "html" => $html)); 
      break;


    case 'insertAsignacionDocente':

        $table = $d-> insertAsignacionDocente($codigo, $docente, $estado);
        $json = json_encode(array("success"=>true));

    break;

  /************************  fin procesos para docente.php **************************/

  /************************  inicio procesos para roles.php **************************/

      case 'loadRoles':
        
        if($estado != ''){
            $whe = " WHERE estado_rol = '".$estado."' ";
        }else{
          $whe = "";
        }

        $table = $r->loadRoles($whe);

        $i = 1;
        foreach ($table as $datarow => $data) {

          if($data["estado_rol"] == 'on'){
            $estado = 'Habilitado';
          }else if($data["estado_rol"] == 'off'){
            $estado = 'Inhabilitado';
          }else{
            $estado = 'Error';
          }

          $edit = '<a class="btn btn-sm btn-primary tooltips" data-rel="tooltip" data-placement="bottom" title="Editar Rol" onclick="fmodalEditar('.$data["codigo_rol"].', \''.$data["nombre_rol"].'\', \''.$data["estado_rol"].'\')"><i class="fa fa-edit"></i></a>&nbsp;';
          $delete = '<a class="btn btn-danger btn-sm purple tooltips" data-original-title="Eliminar" data-rel="tooltip" title="Eliminar" onClick="feditEstado('.$data["codigo_rol"].',\'off\')"><i class="fa fa-trash-o"></i></a>';
          $restore = '<a class="btn btn-success btn-sm purple tooltips" data-original-title="Restaurar" data-rel="tooltip" title="Restaurar" onClick="feditEstado('.$data["codigo_rol"].', \'on\')""><i class="fa fa-undo"></i></a>';
          $options = $delete;
            
          if ($data["estado_rol"] == 'on') $options = $edit.$delete;
          else $options = $restore;
          array_push($createtable['data'], array($i, $data["nombre_rol"], $estado, $options));

          $i++;

        }
        $json = json_encode($createtable);
      break;


      case 'editItemRol':

        $table = $r->editItemRol($nombre_rol, $codigo_rol);

          if($table){
            $json = json_encode(array("success"=>true));
          }else{
            $json = json_encode(array("success"=>false,"mensaje" =>'Error al editar rol'));
          }
      break;


      case 'insertRol':

        $query = $r->insertRol($nombre_rol, $estado_rol);

          if($query){
            $json = json_encode(array("success" => true)); 
          }else{
            $json = json_encode(array("success" =>false,"message"=>"No se insertÃ³ rol"));
          }
      break;


      case 'editEstadoRol':
        $query = $r->editEstadoRol($codigo_rol, $estado_rol);
        if ($query) {
          $json = json_encode(array("success"=>true));  
        }else{
          $json = json_encode(array("success"=>false, "mensaje"=>'No se pudo actualizar rol'));
        }
      break;
  /************************  fin procesos para roles.php **************************/


}

echo $json;   
?>