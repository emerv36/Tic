<?php
session_start();
require('../Models/Usuarios.php');
require_once('../Config/PDOconn.php');
$db = new db(); 
$u= new Usuarios();

if(isset($_GET['case'])){ $case=$_GET['case'];}

//configuración y llamado de la libreria phpmailer, para el envio de correos
require("../phpmailer/class.phpmailer.php");
require("../phpmailer/class.smtp.php");

//fin configuración y llamado de la libreria phpmailer, para el envio de correos

$mail=new PHPMailer();
$mail->setLanguage('es');
$from='info@siiga.com.co';
$fromName='REGISTRO DE USUARIO SIIGA';
$host='mail.siiga.com.co ';
$username='info';
$password='info@2020..';
$port='465';
$secure='false';
$emailadmin='josegaitan123@gmail.com';
$mail->Host=$host;
$mail->SMTauth=true;
$mail->UserName=$username;
$mail->Password=$password;
$mail->Port=$port;
$mail->SMTPSecure=$secure;

$mail->from =$from;
$mail->fromName =$fromName;
$mail->addReplyTo($from,$fromName);

$mail->setFrom('info@siiga.com.co','Nuevo registro de usuario');
$mail->addBCC($emailadmin,'Registro Usuario Siiga');
$mail->isHTML(true);
$mail->Charset='utf-8';
$mail->WordWrap=50;
$mail->AltBody = "";



//variables para cambiar la contraseña
    if(isset($_POST['contraActual'])){ $contraActual=$_POST['contraActual'];}
    if(isset($_POST['nuevaContra'])){  $nuevaContra=$_POST['nuevaContra']; }
//fin variables para cambiar la contraseña

//variables editar estado
    if(isset($_POST['codusuario'])){   $codusuario = $_POST['codusuario']; }
    if(isset($_POST['estado'])){       $estado = $_POST['estado'];}
//fin variables editar estado

// variables para editar usuario

    if(isset($_POST['telefono'])){         $telefono    = $_POST['telefono']; }
    if(isset($_POST['direccion'])){        $direccion   = $_POST['direccion'];  }
    if(isset($_POST['fecha'])){            $fecha       = $_POST['fecha'];  }
    if(isset($_POST['perfil'])){           $perfil      = $_POST['perfil'];  }
    if(isset($_POST['estado'])){           $estado      = $_POST['estado'];  }
    if(isset($_POST['codigo'])){           $codigo      = $_POST['codigo'];  }
    if(isset($_POST['nombre'])){           $nombre      = $_POST['nombre'];  }
//fin variables para editar usuario

// variables para insertar usuario
    if(isset($_POST['txtid'])){             $txtid      =$_POST['txtid'];  }
    if(isset($_POST['txtemail'])){          $txtcorreo  =$_POST['txtemail']; }
    if(isset($_POST['txtnombre'])){         $txtnom     =$_POST['txtnombre'];}
    if(isset($_POST['txttelefono'])){       $txttel     =$_POST['txttelefono'];}
    if(isset($_POST['txtdir'])){            $txtdir     =$_POST['txtdir'];}
    if(isset($_POST['txtfecha'])){          $txtfecha   =$_POST['txtfecha'];}
    if(isset($_POST['txtperfil'])){         $txtperfil  =$_POST['txtperfil']; }
    if(isset($_POST['txtestado'])){         $txtestado  =$_POST['txtestado']; }
// fin variables para insertar usuario

$table = array(
'data' => array()
);

     
switch($case){
    
//case listar usuarios
    case'ListarUsuarios':
       $tabla=$u->VerUsuarios();
       $i=1;
          foreach ($tabla as $datarow => $data) {

            if($data['estado']=='on'){
                $estado = 'Habilitado';
            }else if($data['estado']=='off'){
                $estado = 'deshabilitado';
            }else{
                $estado = 'Error';
            }
             
            $edit = '<div class="btn btn-sm btn-warning fa fa-pencil-square-o" data-toggle="modal"  data-target="#editar-usuario" onclick="ModalEditarUsuarios('.$data['codigo_usu'].',\''.$data['usuario'].'\', \''.$data['email'].'\', \''.$data['nombres_usuario'].'\', '.$data['codperfil_fk'].',\''.$data['telefono'].'\',\''.$data['dir_usuario'].'\', \''.$data['fecha_nacimiento_usuario'].'\', \''.$data['estado'].'\')"></div>';

            $delete = '<div class="btn btn-sm btn-danger fa fa-trash-o"  data-toggle="modal" data-target="#modalconfirmar" onclick="FeEditEstado('.$data["codigo_usu"].',\'off\')"></div>';

            $restore = '<div class="btn btn-sm btn-success glyphicon glyphicon-check"  data-toggle="modal" data-target="#modalconfirmar" onclick="FeEditEstado('.$data["codigo_usu"].', \'on\')"></div>';
           

            if($data["estado"] == 'on'){
                 $opcion = $edit.' '.$delete;
              }else{
                 $opcion = $restore;
              }
              array_push($table['data'], array($i,
                                              $data['usuario'],
                                              strtoupper($data['nombres_usuario']),
                                              strtolower($data['email']),
                                              $data['telefono'],
                                              $data['fechacreado_usu'],
                                              strtoupper($data['nombre_perfil']),
                                              $estado,
                                              $opcion));

            $i++; 
          }

          $json= json_encode($table);
    break;
//fin listar usuarios
 
//case editar estado usuario
    case'EditarEstadoUsuario':
      $query = $u->EditarEstadoUsuario($codusuario, $estado);
        
         if($query){
             $json = json_encode(array("success"=>true));
         }else{
             $json = json_encode(array("success" => false,"mensaje" => "No se ha podido actualizar la información. Por favor intentelo de nuevo"));
         }

    break;
//fin case editar estado usuario


//case editar usuario

    case'EditarUsuarios':


        if(isset($_POST['telefono'])){         $telefono    = $_POST['telefono']; }
        if(isset($_POST['direccion'])){        $direccion   =$_POST['direccion'];  }
        if(isset($_POST['fecha'])){            $fecha       = $_POST['fecha'];  }
        if(isset($_POST['perfil'])){           $perfil      = $_POST['perfil'];  }
        if(isset($_POST['estado'])){           $estado      = $_POST['estado'];  }
        if(isset($_POST['codigo'])){           $codigo      = $_POST['codigo'];  }
        if(isset($_POST['nombre'])){           $nombre      = $_POST['nombre'];  }




      
       $fechaactual = date("Y-m-d");
       $anoactual = date("Y");
       $resultado = substr($fecha,0,4);
       $fechanacimiento = $anoactual - $resultado;

            if($fechaactual <= $fecha){
                
                $json = json_encode(array("success" => false, "mensaje" => "la fecha de nacimiento no puede ser igual o mayor a la fecha actual, verifique la fecha seleccionada"));
            
            
            }else{

                $query = $u->EditarUsuario($codigo, $nombre, $telefono, $direccion, $perfil, $fecha, $estado);

                if($query){

                   $json = json_encode(array("success"=>true));
                
               }else{

                   $json = json_encode(array("success"=> false,"mensaje" => "No de ha podido actualizar la información, intelelo de nuevo."));

                }

            }

             
       

    break;

//fin case editar usuario

// case InsertarUsuarios
    case'InsertarUsuarios':
        $url="http://siiga.scv.edu.co/";
        $mail->Subject = utf8_decode("Siiga - Sistema Integral de Gestión Académica");
         set_time_limit(300);
        $validar = $u->ValidarIdentificacion($txtid);
        $fechaactual = date("Y-m-d");
        $anoactual = date("Y");
        $resultado = substr("$txtfecha",0,4);
        $fechanacimiento = $anoactual - $resultado;
     
        if($validar == ''){
        $correoss = $u->ValidarCorreo(strtolower(trim($txtcorreo)));
        if($correoss==''){
               $timestamp = date("Y-m-d-H-i-s");
               $token = $u->generatecod();
               $txtpass  = sha1($txtid);
               $query = $u->InsertarUsuarios($txtid,$txtpass,$txtcorreo, $txtperfil, $txtnom, $txtdir, $txttel, $txtestado, $txtfecha, $token);
                   if($query !=''){
                          
                        ///INSTRUCCIONES PARA ENVIO DE CORREO
                 $json = json_encode(array("success" => true, "registro de usuario fue creado satisfactoriamente"));

                      /*  $urlconfirmar = $db->urlservidor()."ValidarEmailReal/".$db->base64url_encode($txtcorreo.','.$query["codigo_usu"].','.$token);*/
                                $html = "<!DOCTYPE html>";
                                $html .= "<html>";
                                $html .= "<head>";
                                $html .= "<title>Siiga Correo de confirmación usuario</title>";
                                $html .= "<meta charset='UTF-8'>";
                                $html .= "<style> 
                                    body{
                                        text-align:center;
                                    }
                                
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
                                $html .= '<table style="width:100%;max-width:600px" width="100%" cellspacing="0" cellpadding="0" border="1" align="center">';
                                $html .= '<tbody>';
                                $html .= '<tr>';
                                $html .= '<td role="modules-container" style="padding:0px 0px 0px 0px;color:#000000;text-align:left" width="100%" bgcolor="#ffffff" align="left">';
                                $html .= '<table style="table-layout:fixed" width="100%" cellspacing="0" cellpadding="0" border="0">';
                                $html .= '<tbody>';
                                $html .= '<tr>';
                                $html .= '<td style="font-size:6px;line-height:10px;padding:0px 0px 0px 0px" valign="top" align="center">';
                                //$html .= '<img src="https://scv.edu.co/portal/wp-content/uploads/2023/03/Mesa-de-trabajo-2.png" style="height: 75px"/>';
                                $html .= '<img src="../../assets/images/logo-system-06.png" alt="" style="height: 75px" />';
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
                                $html .= '<strong>Hola, '.$txtnom.'</strong>';
                                $html .= '</span></div>';
                                $html .= '</td>';
                                $html .= '</tr>';                    
                                $html .= '<tr>';

                                $html .= '<td style="padding:0px 0px 10px 20px;line-height:25px;text-align:inherit" valign="top" height="100%">';
                                $html .= '<p font-style:normal;font-variant-ligatures:normal;font-variant-caps:normal;font-weight:400;font-family:Georgia,
                                "Times New Roman","Bitstream Charter",Times,serif;font-size:16px;text-align:justify;color:rgb(51,51,51)>';
                                $html .= '<span style="font-family:arial,helvetica,sans-serif;font-size:16px">';
                                $html .= 'Te damos la bienvenida a <b>SIIGA (Sistema de Gestión Académica)</b> <br>';
                                $html .= 'Tus datos de acceso a nuestra plataforma <br>';
                                $html .= 'Usuario: <b>'.$txtcorreo.'</b> <br>';
                                $html .= 'Contraseña: <b>'.$txtid.'</b> <br> <br>';
                                $html .= 'Url: <b>'.$url.'</b> <br> <br>';
                                $html .= '<b style="font-size:14px;">Nota: Por razones de seguridad recomendamos realizar el cambio de contraseña al momento de realizar sesión por primera vez, el opción editar pérfil en la esquina superior derecha de la pantalla principal, solo tu eres el responsable de la privacidad de tus datos de accesso. <br> ¡Buena suerte! </b>';
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
                                $mail->AddAddress($txtcorreo,"Datos de acceso SIIGA");
                                if ($txtperfil==11){
                                    $mail->addBCC('enlace.empresarial.scv.edu.co','Registro Nueva empresa');
                                }
                    
                                $mail->IsHTML(true);
                               /* $mail->smtpConnect(array("ssl" => array(
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                                    "allow_self_signed" => true
                                )
                                )
                                );*/

                    
                            if ($mail->Send()) {
                                   $json = json_encode(array("success" => true, "codigo_usu"=>$query["codigo_usu"])); 
    
                                    }else{
                                    
                                        $json=json_encode(array("success"=>false,"mensaje"=>$txtcorreo->ErrorInfo));
    
                                    }
                              // FIN ENVIO DE CORREP
                                               
                            }else {
    
                            $json = json_encode(array("success"=>false, "mensaje"=>"No se ha podidó insertar el Usuario ($txtnom)"));
                        
                            }
    
                   }else{
    
                        $json = json_encode(array("success"=>false, "mensaje"=>"El email ingresado ($txtcorreo), se encuentra registrado en la base de datos, verifique por favor"));
    
                    }
    
            }else {
    
                $json = json_encode(array("warning"=>false, "mensaje"=>"La identificación o nit ($txtid), se encuentra registrada en la base de datos, verifique por favor."));
            
            }
    
         
    break;
// fin insertarUsuarios

//case para cargar perfiles en el combo box
    case'cargarperfiles':
        $table =$u->cargarperfiles();
        $json = json_encode($table);
    break;
// fin case para cargar perfiles en el combo box

//case para cambiar la contraseña del usuario
case 'editContrasena':
    $row = $u->BuscarUsuario($_SESSION['IN_codigo_usuCA']);

    if($row != ''){
    if($row['password']==sha1($contraActual)){
        $query = $u->EditarContraseña(sha1($nuevaContra), $_SESSION['IN_codigo_usuCA']);
        if($query){
        $json = json_encode(array("success"=>true));
        }else{
        $json = json_encode(array("success"=>false,"mensaje" => "No se Actualizó la información. Por favor, intentelo de nuevo"));
        }
    }else{
        $json = json_encode(array("success"=>false,"mensaje" => "La contraseña actual ingresada no es correcta"));
    }
    }
    break;
//fin case para cambiar la contraseña del usuario

//case para cambiar la foto de perfil del usuario
    case 'Cambiarfoto':
           $foto1 = $_GET['nombphoto'];
          if($_FILES['idfotos']['tmp_name']!=""){
           $nombre = $db->urls_amigables($_GET['nombphoto']);
           $file=$_FILES["idfotos"]['name'];
           $extension= explode(".",$file);
           $foto = $extension[0];
           $url="../../assets/images/fotoperfil/".$foto. ".".$extension[1];
           $urlFoto=$foto.".".$extension[1];

        if (move_uploaded_file($_FILES['idfotos']['tmp_name'],$url)) {
            $query = $u->uploadFotoPerfil($urlFoto, $_SESSION['IN_codigo_usuCA']);
            $_SESSION['foto']=$urlFoto;
            $json = json_encode(array("success" => true)); 
        }

        }else{
        $json = json_encode(array("success" =>false,"message"=>"Campo vacio"));
        }
    break;
//fin case para cambiar la foto de perfil del usuario
}
echo $json;
?>