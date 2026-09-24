<?php
session_start();
date_default_timezone_set("America/Bogota");
header('content-type: application/json; charset=utf-8');
ob_start();
require('../Models/Inscripcion.php');
require("../Config/confiemail.php");
require('../Services/SigeStudentOutbox.php');
require_once('../Services/SigeWebhook.php');
require_once('../Services/SigeOrdenDispatcher.php');
require_once('../Models/SigeCarnetCompat.php');
//envio de email
//require("../../class/html2pdf_v4.03/html2pdf.class.php");

$i = new Inscripcion();
$sigeWebhook = new SigeStudentOutbox();
$sigeCarnetCompat = new SigeCarnetCompat();

if (isset($_GET['case'])) {
   $case = $_GET['case'];
}

if (isset($_POST['codUsuario'])) {
   $codigo = $_POST['codUsuario'];
}

if (isset($_POST['estado'])) {
   $estado = $_POST['estado'];
}

if (isset($_POST['txtbuscar'])) {
   $txtbuscar = $_POST['txtbuscar'];
}

if (isset($_POST['txtRegistroPerfil'])) {
   $txtRegistroPerfil = $_POST['txtRegistroPerfil'];
}
//fin variables para registrar estudiantes

//parametros de envio de email
$createtable = array('data' => array());
$table = array('data' => array());

switch ($case) {
            case 'EstadoCarnetSige':
                try {
                    if (empty($_SESSION['IN_codigo_usuCA'])) {
                        http_response_code(401);
                        $json = json_encode(array('success' => false, 'status' => 'NO_AUTORIZADO'));
                        break;
                    }
                    $estadoCarnet = $sigeCarnetCompat->obtenerEstadoCarnet($_POST['id_inscripcion'] ?? 0);
                    $json = json_encode(array('success' => true, 'data' => $estadoCarnet));
                } catch (DomainException $ex) {
                    http_response_code(422);
                    $json = json_encode(array('success' => false, 'mensaje' => $ex->getMessage()));
                } catch (Throwable $ex) {
                    error_log('EstadoCarnetSige: ' . $ex->getMessage());
                    http_response_code(500);
                    $json = json_encode(array('success' => false, 'status' => 'ERROR_INTERNO'));
                }
                break;
            case 'CrearOrdenCarnet':
                try {
                    $csrf = (string) ($_POST['csrf_token'] ?? '');
                    if (empty($_SESSION['SIGE_CSRF_TOKEN']) || !hash_equals((string) $_SESSION['SIGE_CSRF_TOKEN'], $csrf)) {
                        http_response_code(403);
                        $json = json_encode(array('success' => false, 'status' => 'CSRF_INVALIDO', 'mensaje' => 'La sesión de confirmación no es válida.'));
                        break;
                    }
                    if (empty($_SESSION['IN_codigo_usuCA']) || empty($_SESSION['IN_codrol'])) {
                        http_response_code(401);
                        $json = json_encode(array('success' => false, 'status' => 'NO_AUTORIZADO'));
                        break;
                    }
                    $confirmado = isset($_POST['confirmado']) && in_array(
                        strtolower(trim((string) $_POST['confirmado'])),
                        array('1', 'true', 'si'),
                        true
                    );
                    $usuarioOrden = array(
                        'id' => (string) $_SESSION['IN_codigo_usuCA'],
                        'nombre' => trim((string) ($_SESSION['nombres'] ?? '')),
                        'codigo_rol' => (int) $_SESSION['IN_codrol']
                    );
                    $idInscripcion = (int) ($_POST['id_inscripcion'] ?? 0);

                    // Auto-sincronización a SIGE: Garantiza que la ficha del estudiante exista en SIGE
                    // antes de emitir la orden de carné (resuelve alumnos históricos en estado ENTREGADO).
                    if ($idInscripcion > 0) {
                        try {
                            (new SigeWebhook())->enviarEstudiante($idInscripcion, 'ACTUALIZAR');
                        } catch (Throwable $syncEx) {
                            error_log('CrearOrdenCarnet - AutoSync Estudiante SIGE: ' . $syncEx->getMessage());
                        }
                    }

                    $orden = $sigeCarnetCompat->crearOrdenCarnet(
                        $_POST['tipo'] ?? '',
                        $idInscripcion,
                        $_POST['uid_rfid'] ?? null,
                        $_POST['motivo'] ?? null,
                        $usuarioOrden,
                        $confirmado
                    );
                    try {
                        (new SigeOrdenDispatcher())->procesarPendientes(1);
                    } catch (Throwable $dispatchError) {
                        error_log('Despacho inmediato SIGE pendiente de reintento: ' . $dispatchError->getMessage());
                    }
                    $json = json_encode(array('success' => true, 'status' => 'PENDIENTE') + $orden);
                } catch (DomainException $ex) {
                    http_response_code(422);
                    $json = json_encode(array('success' => false, 'status' => 'RECHAZADA', 'mensaje' => $ex->getMessage()));
                } catch (Throwable $ex) {
                    error_log('CrearOrdenCarnet: ' . $ex->getMessage());
                    http_response_code(500);
                    $json = json_encode(array('success' => false, 'status' => 'ERROR_INTERNO'));
                }
                break;
    case 'ActivarChip':
        if (isset($_POST['id_inscripcion']) && isset($_POST['rfid'])) {
            $id = (int)$_POST['id_inscripcion'];
            $rfid = trim($_POST['rfid']);
            $query = $i->ActivarChip($id, $rfid);
            if ($query) {
                // PASO 1: Sincronizar PRIMERO la ficha del estudiante a SIGE (con su fecha_registro)
                try {
                    (new SigeWebhook())->enviarEstudiante($id, 'ACTUALIZAR');
                } catch (Throwable $e) {
                    error_log('SIGE Outbox Error (ActivarChip - Sync): ' . $e->getMessage());
                }

                // PASO 2: Emitir y despachar la orden de carnet físico a la API de Comandos de SIGE
                try {
                    $usuarioOrden = array(
                        'id' => (string)($_SESSION['IN_codigo_usuCA'] ?? '1'),
                        'nombre' => trim((string)($_SESSION['nombres'] ?? 'ADMINISTRADOR')),
                        'codigo_rol' => (int)($_SESSION['IN_codrol'] ?? 1)
                    );
                    $sigeCarnetCompat->crearOrdenCarnet('ASIGNACION', $id, $rfid, 'NO_APLICA', $usuarioOrden, true);
                    (new SigeOrdenDispatcher())->procesarPendientes(1);
                } catch (Throwable $e) {
                    error_log('SIGE Orden Outbox Error (ActivarChip - Orden): ' . $e->getMessage());
                }

                $json = json_encode(array("success" => true, "mensaje" => "Chip RFID vinculado exitosamente."));
            } else {
                $json = json_encode(array("success" => false, "mensaje" => "Error al vincular el Chip."));
            }
        } else {
            $json = json_encode(array("success" => false, "mensaje" => "Datos incompletos."));
        }
        echo $json;
        break;

    case 'eliminarRegistro':
        if (isset($_POST['id_eliminar'])) {
           $id_eliminar = $_POST['id_eliminar'];
        }
        
        $query = $i->eliminarRegistro($id_eliminar);
        
        if ($query) {
            $json = json_encode(array("success" => true, "mensaje" => 'Registro Eliminado'));
            
        } else {
            $json = json_encode(array("success" => false, "mensaje" => "Hubo un error al editar informacion del estudiante"));
            
        }
        break;
        
    case 'ListarInscritos':
        $tabla = $i->ListarInscritos();
        $j = 1;
        foreach ($tabla as $datos => $data) {
            $matricular = '<button class="btn-success btn-sm fa fa-legal" data-toggle="modal" title="Matricular"
            onclick="CargarMatriculaEstudiante(' . $data['id_inscripcion'] . ',
            ' . $data['identificacion'] . ', 
            \'' . $data['nombre_grado_anterior'] . '\',
            \'' . $data['grado_anterior_aprobado'] . '\',
            \'' . $data['nombre_jornada'] . '\',
            \'' . $data['nombre_grado'] . '\',
            \'' . $data['grado_aplicafk'] . '\',
            \'' . $data['id_periodo_inscripcionfk'] . '\',
            \'' . $data['nombre_estudiante'] . '\',
            \'' . $data['apellido_estudiante'] . '\',
            \'' . $data['email_estudiante'] . '\',
            \'' . $data['celular_estudiante'] . '\')"> </button>';
            $opcion = $matricular;
            array_push($table['data'], array(
                $j,
                $data['nombre_periodo_anio'],
                "(" . $data['codigo_identidad'] . ")" . $data['identificacion'],
                $data['nombre_estudiante'],
                $data['apellido_estudiante'],
                strtoupper($data['nombre_grado'] . '/' . $data['nombre_jornada'] . '/' . $data['nombre_horario']),
                $data['telefono_estudiante'],
                $opcion
            ));
            $j++;
        }
        $json = json_encode($table);
        break;
        //case listar estudiantes matriculados
        
    case 'ListarMatriculados':
        $tabla = $i->ListarMatriculados();
        $j = 1;
        foreach ($tabla as $datos => $data) {
            array_push($table['data'], array(
                $j,
                $data['nombre_identidad'] . ' ' . $data['identificacion'],
                $data['apellido_estudiante'] . ' ' . $data['nombre_estudiante'],
                $data['periodo'],
                $data['nombre_programa'],
                $data['celular_estudiante'],
                strtoupper($data['nombre_ciudad'])
            ));
            $j++;
        }
        $json = json_encode($table);
        break;
        //case para cargar los tipos de identificacion en el combobox para insertar
    
    case 'CargarTipoIdentificacion':
        $CargarTipoIdentificacion = $i->CargarTipoIdentificacion();
        $json = json_encode($CargarTipoIdentificacion);
        break;
    
    case 'CargarEmpresaInscripcion':
        $CargarEmpresaInscripcion = $i->CargarEmpresaInscripcion();
        $json = json_encode($CargarEmpresaInscripcion);
        break;
    
    //case para inscribir estudiantes
    case 'GuardarInscripcion':
        if (isset($_POST['txtRegistroIdentificacion'])) {
            $txtRegistroIdentificacion = $_POST['txtRegistroIdentificacion'];
        }
        
        if (isset($_POST['txtRegistroTipoIdentificacion'])) {
            $txtRegistroTipoIdentificacion = $_POST['txtRegistroTipoIdentificacion'];
        }
        
        if (isset($_POST['txtRegistroNombreEstudiante'])) {
            $txtRegistroNombreEstudiante = strtoupper($_POST['txtRegistroNombreEstudiante']);
        }
        
        if (isset($_POST['txtRegistroApellidoEstudiante'])) {
            $txtRegistroApellidoEstudiante = strtoupper($_POST['txtRegistroApellidoEstudiante']);
        }
        
        if (isset($_POST['txtRegistroEmail'])) {
            $txtRegistroEmail = $_POST['txtRegistroEmail'];
        }
        
        if (isset($_POST['txtRegistroCelular'])) {
            $txtRegistroCelular = $_POST['txtRegistroCelular'];
        }
        
        if (isset($_POST['txtRegistroTipoSangre'])) {
            $txtRegistroTipoSangre = $_POST['txtRegistroTipoSangre'];
        }
        
        if (isset($_POST['txtRegistroSede'])) {
            $txtRegistroSede = $_POST['txtRegistroSede'];
        }
        
        if (isset($_POST['txtRegistroPrograma'])) {
            $txtRegistroPrograma = $_POST['txtRegistroPrograma'];
        }
        
        if (isset($_POST['txtRegistroLote'])) {
            $txtRegistroLote = $_POST['txtRegistroLote'];
        }
        
        if (isset($_POST['txtRegistroIndicador'])) {
            $txtRegistroIndicador = $_POST['txtRegistroIndicador'];
        }
        
        if (isset($_POST['txtRegistroCodigoFoto'])) {
            $txtRegistroCodigoFoto = $_POST['txtRegistroCodigoFoto'];
        } else {
            // Automatización: El código de la foto ahora será el mismo número de identidad
            $txtRegistroCodigoFoto = isset($_POST['txtRegistroIdentificacion']) ? $_POST['txtRegistroIdentificacion'] : '';
        }
        
        if (isset($_POST['txtRegistroNovedad'])) {
            $txtRegistroNovedad = $_POST['txtRegistroNovedad'];
        }
        
        if (isset($_POST['txtRegistrochip'])) {
            $txtRegistrochip = $_POST['txtRegistrochip'];
        } else {
            // El chip ya no se asigna al registrar, sino al entregar
            $txtRegistrochip = 'NO'; 
        }
        
        if (isset($_POST['txtRegistroCategoriaCarnet'])) {
            $txtRegistroCategoriaCarnet = $_POST['txtRegistroCategoriaCarnet'];
        }
        if (isset($_POST['txtLugarReclamo'])) {
            $txtLugarReclamo = $_POST['txtLugarReclamo'];
        }
        
        $validar = $i->ValidarRegistroCarnet($txtRegistroIdentificacion);
        $txtpassword_estudiante = sha1($txtRegistroIdentificacion);
        $txtusuario = $_SESSION['IN_codigo_usuCA'];
        
        // Guardar foto si viene en base64
        if (isset($_POST['foto_base64']) && !empty($_POST['foto_base64'])) {
            $foto_base64 = $_POST['foto_base64'];
            $image_parts = explode(";base64,", $foto_base64);
            if (count($image_parts) == 2) {
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $file = "../../assets/fotoperfil/" . $txtRegistroIdentificacion . ".jpg";
                file_put_contents($file, $image_base64);
            }
        }
        
        /*if ($validar == '') {
        } else {
            $json = json_encode(array("success" => false, "mensaje" => "Sr(a)" . ' ' . $_SESSION['nombres'] . ' ' . " el estudiante que intenta inscribir se encuentra registrado en nuestra base de datos, verifique por favor el programa que ha seleccionado!"));
        }*/
        $query = $i->GuardarInscripcion(
            $txtRegistroIdentificacion,
            $txtRegistroTipoIdentificacion,
            $txtRegistroNombreEstudiante, 
            $txtRegistroApellidoEstudiante,
            $txtRegistroEmail,
            $txtRegistroCelular,
            $txtRegistroTipoSangre,
            $txtRegistroCategoriaCarnet,
            $txtRegistroSede,
            $txtRegistroPrograma,
            $txtRegistroLote,
            $txtRegistroIndicador,
            $txtRegistroCodigoFoto,
            $txtRegistroNovedad,
            $txtpassword_estudiante,
            $txtRegistrochip,
            $txtusuario,
            $txtLugarReclamo
        );
        
        if ($query) {
            $buscar = $i->BuscarInfoEstudiantes($txtRegistroIdentificacion);
            $correo = $buscar['email_estudiante'];
            
            // Preparación de correo (silencioso para no bloquear el éxito del registro)
            $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>";
            $html .= '<img src="https://scv.edu.co/portal/wp-content/uploads/2020/11/logo-hotizontal.png"/><br>';
            $html .= "<b><h2>¡Proceso de carnetización exitoso!</h2></b>";
            $html .= "Estimado(a) <b>" . $buscar['nombre_estudiante'] . " " . $buscar['apellido_estudiante'] . "</b>,<br>";
            $html .= "Informamos que tu carnet se encuentra en estado <strong>PROCESO</strong>. Estará listo en un plazo de 15 días en la sede <b>" . ucwords(strtolower($buscar['lugar_reclamo'])) . "</b>.<br>";
            $html .= "<hr><ul><li><b>Nombre:</b> " . $buscar['nombre_estudiante'] . " " . $buscar['apellido_estudiante'] . "</li>";
            $html .= "<li><b>Fecha:</b> " . $buscar['fecha_inscripcion'] . "</li></ul><hr>";
            $html .= "Consulta el estado en: <a href='http://tic.scv.edu.co/verificacion'>http://tic.scv.edu.co/verificacion</a></body></html>";
            
            $mail->MsgHTML($html);
            $mail->SetFrom('info@scv.edu.co', utf8_decode('Registro de Carnet - System Center'));
            $mail->Subject = utf8_decode("Registro Carnet - " . $txtRegistroIdentificacion);
            $mail->AddAddress($correo);
            $mail->IsHTML(true);
            $mail->smtpConnect(array("ssl" => array("verify_peer" => false, "verify_peer_name" => false, "allow_self_signed" => true)));
            
            $mail_enviado = $mail->Send();
            
            // El INSERT ya encoló CREAR mediante trigger; intentar despacho inmediato.
            try {
                $sigeWebhook->procesarPendientes(1);
            } catch (Throwable $e) { error_log('SIGE Outbox Error (GuardarInscripcion): ' . $e->getMessage()); }

            // El éxito del registro es lo primordial
            $json = json_encode(array(
                "success" => true, 
                "mensaje" => $mail_enviado ? "Estudiante registrado y notificación enviada." : "Estudiante registrado, pero hubo un error con el correo de notificación."
            ));
        } else {
            $json = json_encode(array("success" => false, "mensaje" => "Hubo un error al registrar al estudiante $txtRegistroNombreEstudiante"));
        }
        break;

    case 'GuardarFoto':
        $id_inscripcion = null;
        $foto = null;

        if (isset($_POST['id_inscripcion'])) {
            $id_inscripcion = $_POST['id_inscripcion'];
        }
        if (isset($_POST['foto'])) {
            $foto = $_POST['foto'];
        }
        
        if ($id_inscripcion && $foto) {
            $query = $i->GuardarFoto($id_inscripcion, $foto);
            if ($query) {
                $json = json_encode(array("success" => true, "mensaje" => '¡Foto guardada correctamente!'));
            } else {
                $json = json_encode(array("success" => false, "mensaje" => "No se pudo guardar la foto en la base de datos."));
            }
        } else {
            $json = json_encode(array("success" => false, "mensaje" => "Datos incompletos para guardar la foto."));
        }
        break;

        
    case 'EditarInscripcion':
        if (isset($_POST['txtidinscripcion'])) {
            $txtidinscripcion = $_POST['txtidinscripcion'];
        }
        
        if (isset($_POST['txtidentificacion'])) {
            $txtidentificacion = $_POST['txtidentificacion'];
        }
        
        if (isset($_POST['txttipoidentificacion'])) {
            $txttipoidentificacion = $_POST['txttipoidentificacion'];
        }
        
        if (isset($_POST['txtnombre'])) {
            $txtnombre = strtoupper($_POST['txtnombre']);
        }
        
        if (isset($_POST['txtapellido'])) {
            $txtapellido = strtoupper($_POST['txtapellido']);
        }
        
        if (isset($_POST['txtemail'])) {
            $txtemail = $_POST['txtemail'];
        }
        
        if (isset($_POST['txtcelular'])) {
            $txtcelular = $_POST['txtcelular'];
        }
        
        if (isset($_POST['txttiposangre'])) {
            $txttiposangre = $_POST['txttiposangre'];
        }
        
        if (isset($_POST['txtlote'])) {
            $txtlote = $_POST['txtlote'];
        }
        
        if (isset($_POST['txtindicador'])) {
            $txtindicador = $_POST['txtindicador'];
        } else { $txtindicador = ""; }
        
        if (isset($_POST['txtcodigofoto'])) {
            $txtcodigofoto = $_POST['txtcodigofoto'];
        } else { $txtcodigofoto = ""; }
        
        if (isset($_POST['txtnovedad'])) {
            $txtnovedad = $_POST['txtnovedad'];
        } else { $txtnovedad = ""; }
        
        if (isset($_POST['txtchip'])) {
            $txtchip = $_POST['txtchip'];
        } else { $txtchip = ""; }
        
        $query = $i->EditarInscripcion(
            $txtidinscripcion,
            $txtidentificacion,
            $txttipoidentificacion,
            $txtnombre,
            $txtapellido,
            $txtcelular,
            $txtemail,
            $txttiposangre,
            $txtlote,
            $txtindicador,
            $txtcodigofoto,
            $txtnovedad,
            $txtchip
        );
        
        if ($query) {
            // Guardar nueva foto si se proporciona
            if (isset($_POST['foto_base64']) && !empty($_POST['foto_base64'])) {
                $base64_string = $_POST['foto_base64'];
                $data = explode(',', $base64_string);
                if (count($data) > 1) {
                    $content = base64_decode($data[1]);
                    $path = "../../assets/fotoperfil/" . $txtidentificacion . ".jpg";
                    file_put_contents($path, $content);
                }
            }
            // El UPDATE ya encoló el evento mediante trigger; intentar despacho inmediato.
            try { $sigeWebhook->procesarPendientes(1); } catch (Throwable $e) { error_log('SIGE Outbox Error (EditarInscripcion): ' . $e->getMessage()); }
            $json = json_encode(array("success" => true));
            
        } else {
            $json = json_encode(array("success" => false, "mensaje" => "Hubo un error al editar informacion del estudiante"));
            
        }
        break;
    case 'CargarSedes':
        $sedes = $i->CargarSedes();
        $json = json_encode($sedes);
        break;
        //caso cargar todos los pramas 
    
    case 'CargarTodosProgramas':
        $programas = $i->CargarTodosProgramas();
        $json = json_encode($programas);
        break;
        //caso cargar programas por parametro de sede
        
    case 'CargarProgramas':
        if (isset($_GET['sedes'])) {
            $sedes = $_GET['sedes'];
        }
        
        if (isset($_GET['categoria'])) {
            $categoria = $_GET['categoria'];
        }
        
        if (isset($_GET['tiposede'])) {
            $tipo_sede = $_GET['tiposede'];
        }
        $programas = $i->CargarProgramas($sedes, $categoria, $tipo_sede);
        $json = json_encode($programas);
        break;
        //caso buscar estudiantes registrados
        
    case 'BuscarInfoEstudiantes':
        if (isset($_POST['txtRegistroIdentificacion'])) {
            $txtRegistroIdentificacion = $_POST['txtRegistroIdentificacion'];
        }
        
        $infoBus = $i->BuscarInfoEstudiantes($txtRegistroIdentificacion);
        
        if ($infoBus != '') {
            $nombre_estudiante = $infoBus['nombre_estudiante'];
            $apellido_estudiante = $infoBus['apellido_estudiante'];
            $tipo_identidad = $infoBus['tipo_identificacionfk'];
            $telefono_estudiante = $infoBus['telefono_estudiante'];
            $celular_estudiante = $infoBus['celular_estudiante'];
            $fechanacimiento = $infoBus['fecha_nacimiento'];
            $barrio_estudiante = $infoBus['barrio_estudiante'];
            $direccion_estudiante = $infoBus['direccion_estudiante'];
            $ciudad_estudiante = $infoBus['nombre_ciudad'];
            $id_colegiofk = $infoBus['id_colegiofk'];
            $nombre_colegio = $infoBus['nombre_colegio'];
            $email_estudiante = $infoBus['email_estudiante'];
            $nivel_academico = $infoBus['nivel_academico'];
            $ultimo_anio = $infoBus['ultimo_anio'];
            $graduado = $infoBus['graduado'];
            $ultimo_nivel_aprobado = $infoBus['ultimo_nivel_aprobado'];
            $estado_civil = $infoBus['estado_civil'];
            $discapacidad = $infoBus['discapacidad'];
            $multicultura = $infoBus['multicultura'];
            $tipo_sangre = $infoBus['tipo_sangre'];
            $numero_hijo = $infoBus['numero_hijo'];
            $estrato = $infoBus['estrato'];
            $medio_transporte = $infoBus['medio_transporte'];
            $zona = $infoBus['zona'];
            $ocupacion = $infoBus['ocupacion'];
            $eps = $infoBus['id_epsfk'];
            
            $json = json_encode(array(
                "success" => true,
                "nombre_estudiante" => "$nombre_estudiante",
                "apellido_estudiante" => "$apellido_estudiante",
                "tipo_identidad" => "$tipo_identidad",
                "telefono_estudiante" => "$telefono_estudiante",
                "celular_estudiante" => "$celular_estudiante",
                "fechanacimiento" => "$fechanacimiento",
                "barrio_estudiante" => "$barrio_estudiante",
                "direccion_estudiante" => "$direccion_estudiante",
                "ciudad_estudiante" => "$nombre_ciudad",
                "id_colegiofk" => "$id_colegiofk",
                "email_estudiante" => "$email_estudiante",
                "nivel_academico" => "$nivel_academico",
                "graduado" => "$graduado",
                "ultimo_anio" => "$ultimo_anio",
                "ultimo_nivel_aprobado" => "$ultimo_nivel_aprobado",
                "estado_civil" => "$estado_civil",
                "discapacidad" => "$discapacidad",
                "multicultura" => "$multicultura",
                "tipo_sangre" => "$tipo_sangre",
                "numero_hijo" => "$numero_hijo",
                "estrato" => "$estrato",
                "medio_transporte" => "$medio_transporte",
                "zona" => "$zona",
                "eps" => "$eps"
             ));
        } else {
            $json = json_encode(array("success" => false, "mensaje" => "!identidad es valida!"));
        }
        break;
        //caso validar email estudiante si ya se encuentra registrado en la base de datos de inscripcion
    case 'ValidarEmailEstudiante':
        if (isset($_POST['txtRegistroEmail'])) {
            $txtRegistroEmail = trim($_POST['txtRegistroEmail']);
        }
        
        if (isset($_POST['txtemail'])) {
            $txtRegistroEmail = trim($_POST['txtemail']);
        }
        $validar = $i->ValidarEmailEstudiante(strtolower($txtRegistroEmail));
        if ($validar != '') {
            $json = json_encode(array("success" => true, "mensaje" => "¡Email se encuentra registrado en la base de datos, ingrese un email diferente!"));
        } else {
            $json = json_encode(array("success" => false, "mensaje" => "!Email valido!"));
        }
        break;
        //fin case
    case 'CargarSedeInscripcion':
        $resultado = $i->CargarSedeInscripcion();
        $json = json_encode($resultado);
        break;
        
    case 'CargarLotes':
        $txtusuario = $_SESSION['IN_codigo_usuCA'];
        $role = $_SESSION['IN_codrol'];
        $resultado = $i->CargarLotes($txtusuario, $role);
        $json = json_encode($resultado);
        break;
    
    case 'CargarMisLotes':
        $txtusuario = $_SESSION['IN_codigo_usuCA'];
        $role = $_SESSION['IN_codrol'];
        $resultado = $i->CargarMisLotes($txtusuario, $role);
        $json = json_encode($resultado);
        break;
    
    case 'CargarLotesEtapaPractica':
        $txtusuario = $_SESSION['IN_codigo_usuCA'];
        $role = $_SESSION['IN_codrol'];
        $resultado = $i->CargarLotesEtapaPractica($txtusuario, $role);
        $json = json_encode($resultado);
        break;
    
    case 'CargarLotesEtapaLectiva':
        $txtusuario = $_SESSION['IN_codigo_usuCA'];
        $role = $_SESSION['IN_codrol'];
        $resultado = $i->CargarLotesEtapaLectiva($txtusuario, $role);
        $json = json_encode($resultado);
        break;
    
    case 'CargarObservacion':
        $resultado = $i->CargarObservacion();
        $json = json_encode($resultado);
        break;
    
    case 'EntregaCarnet':
        if (isset($_POST['ECidinsripcion'])) {
            $ECidinsripcion = $_POST['ECidinsripcion'];
        }
        $resultado = $i->EntregaCarnet($ECidinsripcion);
        if ($resultado) {
            // El UPDATE ya encoló ENTREGA mediante trigger; intentar despacho inmediato.
            try { $sigeWebhook->procesarPendientes(1); } catch (Throwable $e) { error_log('SIGE Outbox Error (EntregaCarnet): ' . $e->getMessage()); }
            $json = json_encode(array("success" => true, "mensaje" => "¡Carnet entregado.!"));
        } else {
            $json = json_encode(array("success" => false, "mensaje" => "¡Carnet ya fue entregado.!"));
        }
        break;
        
    case 'RealizarCambios':
        if (isset($_POST['RCidinscripcion'])) {
            $RCidinscripcion = $_POST['RCidinscripcion'];
        }
        
        if (isset($_POST['Cobservacion'])) {
            $Cobservacion = $_POST['Cobservacion'];
        }
        $resultado = $i->RealizarCambios($RCidinscripcion, $Cobservacion);
        if ($resultado) {
            $json = json_encode(array("success" => true, "mensaje" => "¡Cambios registrados!"));
        } else {
            $json = json_encode(array("success" => false, "mensaje" => "¡No se pueden registrar los cambios.!"));
        }
        break;
    
    case 'CambiarLote':
        if (isset($_POST['txtclote'])) {
            $txtclote = $_POST['txtclote'];
        }
        
        if (isset($_POST['txtclidinscripcion'])) {
            $txtclidinscripcion = $_POST['txtclidinscripcion'];
        }
        
        $resultado = $i->CambiarLote($txtclidinscripcion, $txtclote);
        if ($resultado) {
            $json = json_encode(array("success" => true, "mensaje" => "¡Cambios registrados!"));
        } else {
            $json = json_encode(array("success" => false, "mensaje" => "¡No se pueden registrar los cambios.!"));
        }
        break;
        
    case 'CambiarPrograma':
        if (isset($_POST['txtpprograma'])) {
            $txtpprograma = $_POST['txtpprograma'];
        }
        
        if (isset($_POST['txtpsede'])) {
            $txtpsede = $_POST['txtpsede'];
        }
        
        if (isset($_POST['txtPidinscripcion'])) {
            $txtPidinscripcion = $_POST['txtPidinscripcion'];
        }
        $resultado = $i->CambiarPrograma($txtPidinscripcion, $txtpsede, $txtpprograma);
        if ($resultado != "") {
            $json = json_encode(array("success" => true, "mensaje" => "¡Cambios registrados!"));
        } else {
            $json = json_encode(array("success" => false, "mensaje" => "¡No se pueden registrar los cambios.!"));
        }
        break;
        //consulta carnet
        
    case 'ConsultaCarnet':
        $createtable = array('data' => array());
        if (isset($_GET['lote'])) {
            $txtlote = $_GET['lote'];
            
        }
        
        $table = $i->ConsultaCarnet($txtlote);
        $j = 1;
        foreach ($table as $datarow => $info) {
            if ($info['valor_registro'] == '1') {
                $valor_registro = "<span class='label label-primary'>EN PROCESO</span>";
                $recibido = '<button class="btn btn-primary" title="Confirmar como realizado" onclick="modalcambiarrecibido(' . $info['id_inscripcion'] . ',' . "'" . strtoupper($info['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($info['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i></button>';
            } else if ($info['valor_registro'] == '2') {
                $recibido = '<button class="btn btn-primary" title="Confirmar como realizado" onclick="modalcambiarrecibido(' . $info['id_inscripcion'] . ',' . "'" . strtoupper($info['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($info['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i></button>';
                $valor_registro = "<span class='label label-warning'>REALIZADO</span>";
            } else if ($info['valor_registro'] == '3') {
                $recibido = '<button disabled="Disabled" class="btn btn-primary" title="Confirmar como realizado" onclick="modalcambiarrecibido(' . $info['id_inscripcion'] . ',' . "'" . strtoupper($info['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($info['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i></button>';
                $valor_registro = "<span class='label label-success'>ENTREGADO</span>";
            } else if ($info['valor_registro'] == '4') {
                $recibido = '<button class="btn btn-primary" title="Confirmar como realizado" onclick="modalcambiarrecibido(' . $info['id_inscripcion'] . ',' . "'" . strtoupper($info['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($info['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i></button>';
                $valor_registro = "<span class='label label-danger'>CORRECCION</span>";
            }

            if ($info['valor_registro'] == '2') {
                $entregado = '<button class="btn btn-success" title="Confirmar como entregado" onclick="modalcambiarentregado(' . $info['id_inscripcion'] . ',' . "'" . strtoupper($info['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($info['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i></button>';
            } else {
                $entregado = '<button disabled="Disabled" class="btn btn-success" title="Confirmar como realizado" onclick="modalcambiarentregado(' . $info['id_inscripcion'] . ',' . "'" . strtoupper($info['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($info['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i></button>';
            }
    
            if ($info['chip_carnet'] == 'SI') {
                $valor_chip = "<span class='label label-success'>SI</span>";
            } else {
                $valor_chip = "<span class='label label-danger'>NO</span>";
            }
            
            $idSige = (int) $info['id_inscripcion'];
            $docSige = htmlspecialchars(
                json_encode((string) $info['identificacion'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                ENT_QUOTES,
                'UTF-8'
            );
            $gestionSige = '<button class="btn btn-info" title="Estado y operaciones SIGE" '
                . 'onclick="abrirGestionCarnetSige(' . $idSige . ', ' . $docSige . ')">'
                . '<i class="fa fa-id-card"></i></button>';
            $renovacionSige = '<button type="button" class="btn btn-success btn-xs" title="Renovación de vigencia (mismo carné)" '
                . 'onclick="abrirModalRenovacionSige(' . $idSige . ', ' . $docSige . ')">'
                . '<i class="fa fa-refresh"></i> Renovación</button>';
            array_push($createtable['data'], array(
                $j,
                $info['identificacion'],
                $info['apellido_estudiante'],
                $info['nombre_estudiante'],
                $info['nombre_programa'],
                $info['etapa'] . '-' . $info['codigo'],
                $info['tipo_sangre'],
                $valor_registro,
                $valor_chip,
                $info['codigo_foto'],
                $info['fecha_inscripcion'],
                $recibido . ' ' . $entregado . ' ' . $gestionSige . ' ' . $renovacionSige
            ));
            $j++;
            
        }
        $json = json_encode($createtable);
        break;
        
    //caso para cambiar al estado de recibido
    case 'CambiarRecibido':
        if (isset($_POST['Linscripcion'])) {
            $txtid = $_POST['Linscripcion'];
        }
        $resultado = $i->CambiarRecibido($txtid);
        
        if ($resultado) {
            // El UPDATE ya encoló el cambio mediante trigger; intentar despacho inmediato.
            try { $sigeWebhook->procesarPendientes(1); } catch (Throwable $e) { error_log('SIGE Outbox Error (CambiarRecibido): ' . $e->getMessage()); }
            $info = $i->EnviarEmailRecibidoLote($txtid);
            $txtnombre = $info['nombre_estudiante'] . ' ' . $info['apellido_estudiante'];
            $txtcorreo = $info['email_estudiante'];
            $txtprograma = $info['nombre_programa'];
            $tiposangre = $info['tipo_sangre'];
            $identidad = $info['identificacion'];
            $nombre_identidad = $info['nombre_identidad'];
            $html = "<!DOCTYPE html>";
            $html .= "<html>";
            $html .= "<head>";
            $html .= "<body>";
            //$html .= '<img src="https://scv.edu.co/portal/wp-content/uploads/2023/03/Mesa-de-trabajo-2.png"/><br>';
            $html .= '<img src="https://scv.edu.co/portal/wp-content/uploads/2020/11/logo-hotizontal.png"/><br>';
            $html .= 'Hola:<strong>,' . ucwords(strtolower($txtnombre)) . ' ' . '</strong>El departamento de nuevas tecnologias<strong>SYSTEM CENTER</strong><br>';
            $html .= 'Informa que tú carnet estudiantil ya se encuentra <strong>LISTO</strong> para reclamar puedes dirigirte a la instalación departamento de nuevas tecnologias en la sede <b>' . ucwords(strtolower($info['lugar_reclamo'])) . '</b><br>';
            $html .= 'Horario de LUNES DE VIENES 8:00 am - 4:00 pm<br>';
            $html .= '<br>';
            $html .= '<hr>';
            $html .= '<ul>';
            $html .= '<li><b>Nombre:</b>' . ' ' . ucwords(strtolower($txtnombre));
            $html .= '<li><b>Programa académico:</b>' . ' ' . ucwords(strtolower($info['nombre_programa']));
            $html .= '<li><b>Fecha notificación:</b>' . ' ' . $info['fecha_recibido_carnet'];
            $html .= '</ul>';
            $html .= '<hr>';
            $html .= 'Puedes consultar el estdo de tu carnet en la siguiente direccion<br>';
            $html .= 'http://tic.scv.edu.co/verificacion<br>';
            $html .= '</body>';
            $html .= '</html>';
            $mail->MsgHTML($html);
            $mail->SetFrom('info@scv.edu.co', utf8_decode('System Center - Nuevas tecnologias'));
            $mail->Subject = utf8_decode("Tu carnét listo para entrega" . " - " . $identidad);
            $mail->AddAddress($txtcorreo);
            $mail->IsHTML(true);
            $mail->smtpConnect(array("ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )));
            
            if ($mail->Send()) {
                $json = json_encode(array("success" => true, "mensaje" => "Correo enviado satisfactoriamente"));
                
            } else {
                // El cambio en DB fue exitoso, pero fallo el envio de correo
                $json = json_encode(array("success" => true, "mensaje" => "Estado actualizado a REALIZADO. Nota: no se pudo enviar el correo (" . $mail->ErrorInfo . ")"));
                
            }
            
        } else {
            $json = json_encode(array("success" => false, "mensaje" => "¡No se pueden registrar los cambios.!"));
            
        }
        break;
        //caso para cambiar al estado de recibido
    case 'CambiarEntregado':
        $txtid = $_POST['Einscripcion'] ?? $_POST['ECidinsripcion'] ?? $_POST['Linscripcion'] ?? null;
        $resultado = $i->CambiarEntregado($txtid);
        if ($resultado) {
            $info = $i->EnviarEmailRecibidoLote($txtid);
            $txtnombre = $info['nombre_estudiante'] . ' ' . $info['apellido_estudiante'];
            $txtcorreo = $info['email_estudiante'];
            $txtprograma = $info['nombre_programa'];
            $tiposangre = $info['tipo_sangre'];
            $identidad = $info['identificacion'];
            $nombre_identidad = $info['nombre_identidad'];
            $fecha = $info['fecha_recibido_carnet'];
            $html = "<!DOCTYPE html>";
            $html .= "<html>";
            $html .= "<head>";
            $html .= "<body>";
            //$html .= '<img src="https://scv.edu.co/portal/wp-content/uploads/2023/03/Mesa-de-trabajo-2.png"/><br>';
            $html .= '<img src="https://scv.edu.co/portal/wp-content/uploads/2020/11/logo-hotizontal.png"/><br>';
            $html .= 'Felicitaciones :<strong>' . ucwords(strtolower($txtnombre)) . ' ' . '</strong>El departamento de tecnologias de la información<strong> SYSTEM CENTER</strong><br>';
            $html .= 'Informa que tú carnet estudiantil ha sido entregado exitosamente!<br>';
            $html .= 'Tenga en cuenta las siguientes recomendaciones:<br>';
            $html .= '<hr>';
            $html .= '<ul>';
            $html .= '<li>Debes portar el carnet en un sitio visible en nuestra instalación,recuerda que es tú identificación SYSTEMISTA';
            $html .= '<li>En caso de pérdida debes realizar el proceso de RENOVACION este tiene un costo de $10.000';
            $html .= '<li>Si eres estudiante en etapa PRODUCTIVA (realizando prácticas) el costo de renovación es de $22.600';
            $html .= '<li>Fecha notificación:' . ' ' . $fecha;
            $html .= '</ul>';
            $html .= '<hr>';
            $html .= '</body>';
            $html .= '</html>';
            $mail->MsgHTML($html);
            $mail->SetFrom('info@scv.edu.co', utf8_decode('System Center - Tecnologias de la información'));
            $mail->Subject = utf8_decode("¡Hemos entregado tú carnet proceso finalizado!" . " - " . $identidad);
            $mail->AddAddress($txtcorreo);
            $mail->IsHTML(true);
            $mail->smtpConnect(array("ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )));
            
            if ($mail->Send()) {
                $json = json_encode(array("success" => true, "mensaje" => "Correo enviado satisfactoriamente"));
                
            } else {
                // El cambio en DB fue exitoso, pero fallo el envio de correo
                $json = json_encode(array("success" => true, "mensaje" => "Estado actualizado a ENTREGADO. Nota: no se pudo enviar el correo (" . $mail->ErrorInfo . ")"));
                
            }
            
        } else {
            $json = json_encode(array("success" => false, "mensaje" => "¡No se pueden registrar los cambios.!"));
            
        }
        break;
        //listar todos los carnets
    
    case 'ListarTodos':
        $createtable = array('data' => array());
        $table = $i->ListarTodos();
        $j = 1;
        foreach ($table as $datarow => $info) {
            if ($info['valor_registro'] == '1') {
                $valor_registro = "<span class='label label-primary'>EN PROCESO</span>";
                $recibido = '<button class="btn btn-primary" title="Confirmar como realizado" onclick="modalcambiarrecibido(' . $info['id_inscripcion'] . ',' . "'" . strtoupper($info['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($info['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i></button>';
                
            } else if ($info['valor_registro'] == '2') {
                $recibido = '<button class="btn btn-primary" title="Confirmar como realizado" onclick="modalcambiarrecibido(' . $info['id_inscripcion'] . ',' . "'" . strtoupper($info['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($info['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i></button>';
                $valor_registro = "<span class='label label-warning'>REALIZADO</span>";
                
            } else if ($info['valor_registro'] == '3') {
                $recibido = '<button disabled="Disabled" class="btn btn-primary" title="Confirmar como realizado" onclick="modalcambiarrecibido(' . $info['id_inscripcion'] . ',' . "'" . strtoupper($info['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($info['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i></button>';
                $valor_registro = "<span class='label label-success'>ENTREGADO</span>";
                
            } else if ($info['valor_registro'] == '4') {
                $recibido = '<button class="btn btn-primary" title="Confirmar como realizado" onclick="modalcambiarrecibido(' . $info['id_inscripcion'] . ',' . "'" . strtoupper($info['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($info['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i></button>';
                $valor_registro = "<span class='label label-danger'>CORRECCION</span>";
                
            }
            
            if ($info['valor_registro'] == '2') {
                $entregado = '<button class="btn btn-success" title="Confirmar como entregado" onclick="modalcambiarentregado(' . $info['id_inscripcion'] . ',' . "'" . strtoupper($info['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($info['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i></button>';
            } else {
                $entregado = '<button disabled="Disabled" class="btn btn-success" title="Confirmar como realizado" onclick="modalcambiarentregado(' . $info['id_inscripcion'] . ',' . "'" . strtoupper($info['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($info['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i></button>';
            }
            
            if ($info['chip_carnet'] == 'SI') {
                $valor_chip = "<span class='label label-success'>SI</span>";
            } else {
                $valor_chip = "<span class='label label-danger'>NO</span>";
            }
            
            array_push($createtable['data'], array(
                $j,
                $info['identificacion'],
                $info['apellido_estudiante'],
                $info['nombre_estudiante'],
                $info['nombre_programa'],
                $info['etapa'] . '-' . $info['codigo'],
                $info['tipo_sangre'],
                $valor_registro,
                $valor_chip,
                $info['codigo_foto'],
                $info['fecha_inscripcion'],
                $recibido . ' ' . $entregado
            ));
            $j++;
        }
        $json = json_encode($createtable);
        break;
    case 'ExportarLoteExcel':
        $txtlote = $_GET['lote'];
        $table = $i->ConsultaCarnet($txtlote);
        
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=Datos_Lote_" . $txtlote . "_" . date('Y-m-d') . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        echo '<table border="1">';
        echo '<tr>
                <th style="background-color: #337ab7; color: white;">ID</th>
                <th style="background-color: #337ab7; color: white;">IDENTIFICACION</th>
                <th style="background-color: #337ab7; color: white;">TIPO ID</th>
                <th style="background-color: #337ab7; color: white;">APELLIDOS</th>
                <th style="background-color: #337ab7; color: white;">NOMBRES</th>
                <th style="background-color: #337ab7; color: white;">CELULAR</th>
                <th style="background-color: #337ab7; color: white;">EMAIL</th>
                <th style="background-color: #337ab7; color: white;">RH</th>
                <th style="background-color: #337ab7; color: white;">SEDE</th>
                <th style="background-color: #337ab7; color: white;">PROGRAMA</th>
                <th style="background-color: #337ab7; color: white;">ETAPA</th>
                <th style="background-color: #337ab7; color: white;">ESTADO</th>
                <th style="background-color: #337ab7; color: white;">CHIP</th>
                <th style="background-color: #337ab7; color: white;">COD FOTO</th>
              </tr>';
        
        foreach ($table as $fila) {
            echo '<tr>';
            echo '<td>' . $fila['id_inscripcion'] . '</td>';
            echo '<td>' . $fila['identificacion'] . '</td>';
            echo '<td>' . $fila['nombre_identidad'] . '</td>';
            echo '<td>' . utf8_decode($fila['apellido_estudiante']) . '</td>';
            echo '<td>' . utf8_decode($fila['nombre_estudiante']) . '</td>';
            echo '<td>' . $fila['celular_estudiante'] . '</td>';
            echo '<td>' . $fila['email_estudiante'] . '</td>';
            echo '<td>' . $fila['tipo_sangre'] . '</td>';
            echo '<td>' . utf8_decode($fila['nombre_sede']) . '</td>';
            echo '<td>' . utf8_decode($fila['nombre_programa']) . '</td>';
            echo '<td>' . $fila['etapa'] . '</td>';
            echo '<td>' . $fila['estado_inscripcion'] . '</td>';
            echo '<td>' . $fila['chip_carnet'] . '</td>';
            echo '<td>' . $fila['codigo_foto'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        exit;

    case 'ExportarCarnetsPDF':
        $txtlote = $_GET['lote'];
        $estudiantes = $i->ConsultaCarnet($txtlote);
        
        if (empty($estudiantes)) {
            die("No hay estudiantes en este lote.");
        }

        require('../../class/fpdf/fpdf.php'); // Usamos la librería existente
        
        $pdf = new FPDF('P', 'mm', 'letter');
        $pdf->SetAutoPageBreak(true, 10);
        
        // Dimensiones del carnet (86mm x 54mm aproximado)
        $ancho = 86;
        $alto = 54;
        
        foreach ($estudiantes as $est) {
            $pdf->AddPage();
            
            // Determinar plantilla
            $esPracticante = (strpos(strtoupper($est['nombre_programa']), 'PRACTICANTE') !== false || $est['obscarnetfk'] == 3);
            $template = $esPracticante ? '../../assets/images/Practicante.png' : '../../assets/images/estudiantil.png';
            
            if (file_exists($template)) {
                $pdf->Image($template, 10, 10, $ancho, $alto);
            } else {
                // Fallback (Diseño básico)
                $pdf->SetFillColor(240, 240, 240);
                $pdf->Rect(10, 10, $ancho, $alto, 'DF');
                $pdf->SetFillColor(51, 122, 183); 
                $pdf->Rect(10, 10, $ancho, 10, 'F');
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetXY(12, 12);
                $pdf->Cell(0, 5, $esPracticante ? 'PRACTICANTE' : 'ESTUDIANTE', 0, 1);
                
                $pdf->SetFillColor(51, 122, 183);
                $pdf->Rect(10, 10 + $alto - 5, $ancho, 5, 'F');
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('Arial', 'B', 6);
                $pdf->SetXY(10, 10 + $alto - 4);
                $pdf->Cell($ancho, 3, 'SYSTEM CENTER - VIGILADO MINEDUCACION', 0, 0, 'C');
            }
            
            // Foto (Posición ajustada para el lado derecho del template)
            $fotoPath = '../../assets/fotoperfil/' . $est['identificacion'] . '.jpg';
            if (file_exists($fotoPath)) {
                $pdf->Image($fotoPath, 64, 25, 22, 22);
            } else {
                $pdf->SetFillColor(200, 200, 200);
                $pdf->Rect(64, 25, 22, 22, 'DF');
                $pdf->SetTextColor(100, 100, 100);
                $pdf->SetFont('Arial', '', 6);
                $pdf->Text(66, 36, 'SIN FOTO');
            }
            
            // Datos
            $pdf->SetTextColor(0, 0, 0);
            $xData = 12;
            $yData = 25;
            
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY($xData, $yData);
            $pdf->Cell(0, 3, 'NOMBRE Y APELLIDO:', 0, 1);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetX($xData);
            $pdf->Cell(0, 4, utf8_decode($est['nombre_estudiante'] . ' ' . $est['apellido_estudiante']), 0, 1);
            
            $pdf->Ln(1);
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetX($xData);
            $pdf->Cell(0, 3, utf8_decode('IDENTIFICACIÓN:'), 0, 1);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetX($xData);
            $pdf->Cell(0, 4, $est['identificacion'], 0, 1);
            
            $pdf->Ln(1);
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetX($xData);
            $pdf->Cell(0, 3, 'PROGRAMA:', 0, 1);
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetX($xData);
            $pdf->MultiCell(55, 3, utf8_decode($est['nombre_programa']), 0, 'L');
        }
        
        $pdf->Output('I', 'Carnets_Lote_' . $txtlote . '.pdf');
        exit;

    default:
        $json = json_encode(array("success" => false, "mensaje" => "$case, metodo no definido"));
        break;
}
ob_clean();
echo $json;
