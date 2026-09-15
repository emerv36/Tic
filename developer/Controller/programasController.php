<?php
session_start();
date_default_timezone_set("America/Bogota");
header('content-type: application/json; charset=utf-8');
require('../Models/Programas.php');
$p = new Programas();
if (isset($_GET['case'])) {
    $case = $_GET['case'];
}
//variables editar estado de los programas
if (isset($_POST['codUsuario'])) {
    $codigo = $_POST['codUsuario'];
}
if (isset($_POST['estado'])) {
    $estado = $_POST['estado'];
}
//fin variables editar estado de los programas
if (isset($_POST['sedeorigen'])) {
    $sedeorigen          = $_POST['sedeorigen'];
}
if (isset($_POST['sededestmod'])) {
    $sededest            = $_POST['sededestmod'];
}
if (isset($_POST['programamod'])) {
    $programamod         = $_POST['programamod'];
}

//variables para actualizar la información de los programas
if (isset($_POST['txtIdPrograma'])) {
    $txtIdPrograma         = $_POST['txtIdPrograma'];
}
if (isset($_POST['txtcodigoPrograma'])) {
    $txtcodigoPrograma     = $_POST['txtcodigoPrograma'];
}
if (isset($_POST['txtnombrePrograma'])) {
    $txtnombrePrograma     = $_POST['txtnombrePrograma'];
}
if (isset($_POST['txtSelectSedePrograma'])) {
    $txtSelectSedePrograma = $_POST['txtSelectSedePrograma'];
}
if (isset($_POST['txtEstadoPrograma'])) {
    $txtEstadoPrograma     = $_POST['txtEstadoPrograma'];
}
//fin variables

//variables para registrar la información de los programas
if (isset($_POST['RnombrePrograma'])) {
    $RnombrePrograma = strtoupper($_POST['RnombrePrograma']);
}
if (isset($_POST['RSelectSedePrograma'])) {
    $RSelectSedePrograma = $_POST['RSelectSedePrograma'];
}
if (isset($_POST['RcodigoPrograma'])) {
    $RcodigoPrograma = $_POST['RcodigoPrograma'];
}
if (isset($_POST['RestadoPrograma'])) {
    $RestadoPrograma = $_POST['RestadoPrograma'];
}

if (isset($_POST['empresa_practicante'])) {
    $empresa_practicante = strtoupper($_POST['empresa_practicante']);
}
if (isset($_POST['cargo_practicante'])) {
    $cargo_practicante = strtoupper(trim($_POST['cargo_practicante']));
}
if (isset($_POST['id_cargo_practicante'])) {
    $id_cargo_practicante = strtoupper($_POST['id_cargo_practicante']);
}
//fin variables

$createtable = array('data' => array());
switch ($case) {
    
    case 'ListarCargoPracticante':
        $table = $p->ListarCargoPracticante();
        $j = 1;
        foreach ($table as $datarow => $info) {
            if ($info['estado_programa'] == 'on') {
                $estado = 'Habilitado';
            } else if ($info['estado_programa'] == 'off') {
                $estado = 'Deshabilitado';
            } else {
                $estado = 'Error';
            }
            $edit = '<div class="btn btn-sm btn-warning fa fa-pencil-square-o" data-toggle="modal"  data-target="editar-programas"
            onclick="ModalEditarCargoPracticante(' . $info['id_programa'] . ',\'' . $info['nombre_programa'] . '\',' . $info['id_sedefk'] . ')"></div>';


            // modal eliminar registros/
            $delete = '<div class="btn btn-sm btn-danger fa fa-trash-o"  data-toggle="modal" data-target="" 
            onClick="EditarEstadoCargoPracticante(' . $info["id_programa"] . ',\'off\')"></div>';

            ///modal recuperar registro///
            $restore = '<div class="btn btn-sm btn-success glyphicon glyphicon-check" data-toggle="modal" data-target=""
             onClick="EditarEstadoCargoPracticante(' . $info["id_programa"] . ', \'on\')"></div>';

            if ($info["estado_programa"] == 'on') {
                $opcion = $edit . ' ' . $delete;
            } else {
                $opcion = $restore;
            }
            array_push($createtable['data'], array(
                $j,
                ucwords(strtoupper($info['nombre_programa']) . ' ' . '<span class="badge badge-secondary">' . $info['codigo_programa'] . '</span>'),
                ucwords(strtoupper($info['nombre_sede'])),
                $estado,
                $opcion
            ));
            $j++;
        }
        $response = json_encode($createtable);
        break;
    
    case 'CargarEmpresas':
        $sedes = $p->CargarEmpresas();
        $response = json_encode($sedes);
        break;
    
    case 'RegistrarCargoPracticante':
        $validarCargo = $p->ValidarCargoPracticante($cargo_practicante, $empresa_practicante);

        if ($validarCargo == '') {
            $query = $p->RegistrarCargoPracticante($cargo_practicante, $empresa_practicante);
            if ($query) {
                $response = json_encode(array("success" => true));
            } else {
                $response = json_encode(array("success" => false, "mensaje" => "No se pudo registrar la información. Intentelo de nuevo"));
            }
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "El cargo que intenta crear se encuentra registrado en la base de datos."));
        }
        break;
        
    case 'EditarCargoPracticante':
        $validarCargo = $p->ValidarCargoPracticanteId($id_cargo_practicante, $cargo_practicante, $empresa_practicante);

        if ($validarCargo == '') {
            $query = $p->EditarCargoPracticante($id_cargo_practicante, $cargo_practicante, $empresa_practicante);
            if ($query) {
                $response = json_encode(array("success" => true));
            } else {
                $response = json_encode(array("success" => false, "mensaje" => "No se ha podido actualizar la información.Intentelo de nuevo."));
            }
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "El cargo que intenta crear se encuentra registrado en la base de datos."));
        }
        break;
    
    case 'EditarEstadoCargoPracticante':
        // estamos aqui
        $query = $p->EditarEstadoProgramas($id_cargo_practicante, $estado);
        if ($query) {
            $response = json_encode(array("success" => true));
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "No se ha podido actualizar la información. Por favor intentelo de nuevo"));
        }
        break;
    
    //case para listar los programas
    case 'ListarProgramas':
        $table = $p->ListarProgramas();
        $j = 1;
        foreach ($table as $datarow => $info) {
            if ($info['estado_programa'] == 'on') {
                $estado = 'Habilitado';
            } else if ($info['estado_programa'] == 'off') {
                $estado = 'Deshabilitado';
            } else {
                $estado = 'Error';
            }
            // modal editar programas ////
            $edit = '<div class="btn btn-sm btn-warning fa fa-pencil-square-o" data-toggle="modal"  data-target="editar-programas"
            onclick="ModalEditarProgramas(' . $info['id_programa'] . ',
                                          \'' . $info['codigo_programa'] . '\',
                                          \'' . $info['nombre_programa'] . '\',
                                          ' . $info['id_sedefk'] . ',
                                           \'' . $info['estado_programa'] . '\')"></div>';


            // modal eliminar registros/
            $delete = '<div class="btn btn-sm btn-danger fa fa-trash-o"  data-toggle="modal" data-target="" 
            onClick="EditarEstadoProgramas(' . $info["id_programa"] . ',\'off\')"></div>';

            ///modal recuperar registro///
            $restore = '<div class="btn btn-sm btn-success glyphicon glyphicon-check" data-toggle="modal" data-target=""
             onClick="EditarEstadoProgramas(' . $info["id_programa"] . ', \'on\')"></div>';

            if ($info["estado_programa"] == 'on') {
                $opcion = $edit . ' ' . $delete;
            } else {
                $opcion = $restore;
            }
            array_push($createtable['data'], array(
                $j,
                ucwords(strtoupper($info['nombre_programa']) . ' ' . '<span class="badge badge-secondary">' . $info['codigo_programa'] . '</span>'),
                ucwords(strtoupper($info['nombre_sede'])),
                $estado,
                $opcion
            ));
            $j++;
        }
        $response = json_encode($createtable);
        break;
        //case para listar los programas
        // case para editar el estado de los Programas
    case 'EditarEstadoProgramas':
        $query = $p->EditarEstadoProgramas($codigo, $estado);
        if ($query) {
            $response = json_encode(array("success" => true));
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "No se ha podido actualizar la información. Por favor intentelo de nuevo"));
        }
        break;
        //fin case para editar el estado de los Programas
        //case para cargar las sedes
    case 'CargarSedes':
        $sedes = $p->CargarSedes();
        $response = json_encode($sedes);
        break;
        //fin case para cargar sedes



        //case para actualizar la info de los programas
    case 'ActualizarInfoProgramas':
        $query = $p->ActualizarInfoProgramas(
            $txtIdPrograma,
            $txtcodigoPrograma,
            $txtnombrePrograma,
            $txtSelectSedePrograma,
            $txtEstadoPrograma
        );
        if ($query) {
            $response = json_encode(array("success" => true));
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "No se ha podido actualizar la información.Intentelo de nuevo."));
        }

        break;


        //fin case para actualizar la info de los programas

    case 'ValidarNombrePrograma':

        $programa = trim($_POST['RnombrePrograma']);
        $sede = $_POST['RSelectSedePrograma'];

        $programa = $p->ValidarNombrePrograma($programa, $sede);

        if ($programa == '') {
            $response = json_encode(array("success" => true, "mensaje" => "¡El programa es valido!"));
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "El programa que intenta agregar se encuentra registrada en la sede seleccionada, verifique porfavor"));
        }

        break;

        //case para registrar información de los programas
    case 'RegistrarInfoProgramas':

        if (isset($_POST['RnombrePrograma'])) {
            $RnombrePrograma = strtoupper($_POST['RnombrePrograma']);
        }
        if (isset($_POST['RresolucionPrograma'])) {
            $RresolucionPrograma = strtoupper($_POST['RresolucionPrograma']);
        }
        if (isset($_POST['RcodigoPrograma'])) {
            $RcodigoPrograma = $_POST['RcodigoPrograma'];
        }
        if (isset($_POST['RSelectSedePrograma'])) {
            $RSelectSedePrograma = $_POST['RSelectSedePrograma'];
        }
        if (isset($_POST['RestadoPrograma'])) {
            $RestadoPrograma = $_POST['RestadoPrograma'];
        }

        $RcodigoPrograma = trim($RcodigoPrograma);
        $validarPrograma = $p->ValidarPrograma($RcodigoPrograma, $RSelectSedePrograma);
        if ($validarPrograma == '') {

            $query = $p->RegistrarInfoProgramas(
                $RcodigoPrograma,
                $RnombrePrograma,
                $RSelectSedePrograma,
                $RestadoPrograma
            );
            if ($query) {
                $response = json_encode(array("success" => true));
            } else {
                $response = json_encode(array("success" => false, "mensaje" => "No se pudo registrar la información. Intentelo de nuevo"));
            }
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "Sr(a)" . ' ' . $_SESSION['nombres'] . ' ' . "el programa que intenta crear se encuentra registrado en la base de datos en la sede seleccionada, verifique por favor los valores de entrada."));
        }
        break;

        //case para registrar información de los programas

    case 'ListarCargos':
        $table = $p->ListarCargos();
        $j = 1;
        foreach ($table as $datarow => $info) {
            if ($info['estado_programa'] == 'on') {
                $estado = 'Habilitado';
            } else if ($info['estado_programa'] == 'off') {
                $estado = 'Deshabilitado';
            } else {
                $estado = 'Error';
            }
            // modal editar programas ////
            $edit = '<div class="btn btn-sm btn-warning fa fa-pencil-square-o" data-toggle="modal"  data-target="editar-cargos"
       onclick="ModalEditarCargos(' . $info['id_programa'] . ',
                                      \'' . $info['nombre_programa'] . '\',
                                     ' . $info['id_sedefk'] . ')"></div>';


            // modal eliminar registros/
            $delete = '<div class="btn btn-sm btn-danger fa fa-trash-o"  data-toggle="modal" data-target="" 
       onClick="EditarEstadoProgramas(' . $info["id_programa"] . ',\'off\')"></div>';

            ///modal recuperar registro///
            $restore = '<div class="btn btn-sm btn-success glyphicon glyphicon-check" data-toggle="modal" data-target=""
        onClick="EditarEstadoProgramas (' . $info["id_programa"] . ', \'on\')"></div>';

            if ($info["estado_programa"] == 'on') {
                $opcion = $edit . ' ' . $delete;
            } else {
                $opcion = $restore;
            }

            array_push($createtable['data'], array(
                $j,
                ucwords(strtoupper($info['nombre_programa'])),
                ucwords(strtoupper($info['nombre_sede'])),
                $estado,
                $opcion
            ));
            $j++;
        }
        $response = json_encode($createtable);
        break;



        //funcion para guardar los cargos

        //case para registrar información de los programas
    case 'RegistrarCargos':

        if (isset($_POST['RnombreCargo'])) {
            $RnombreCargo = strtoupper($_POST['RnombreCargo']);
        }
        if (isset($_POST['RSelectSedeCargo'])) {
            $RSelectSedeCargo = strtoupper($_POST['RSelectSedeCargo']);
        }

        $RnombreCargo = trim($RnombreCargo);
        $validarCargo = $p->ValidarCargo($RnombreCargo, $RSelectSedeCargo);

        if ($validarCargo == '') {

            $query = $p->RegistrarCargos($RnombreCargo, $RSelectSedeCargo);
            if ($query) {
                $response = json_encode(array("success" => true));
            } else {
                $response = json_encode(array("success" => false, "mensaje" => "No se pudo registrar la información. Intentelo de nuevo"));
            }
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "El cargo que intenta crear se encuentra registrado en la base de datos."));
        }
        break;


        //case para actualizar cargos
    case 'EditarCargos':

        if (isset($_POST['txtIdcargo'])) {
            $txtIdcargo = strtoupper($_POST['txtIdcargo']);
        }
        if (isset($_POST['txtnombreCargo'])) {
            $txtnombreCargo = strtoupper($_POST['txtnombreCargo']);
        }
        if (isset($_POST['txtSelectSedeCargo'])) {
            $txtSelectSedeCargo = strtoupper($_POST['txtSelectSedeCargo']);
        }


        $txtnombreCargo = trim($txtnombreCargo);
        $validarCargo = $p->ValidarCargo($txtnombreCargo, $txtSelectSedeCargo);

        if ($validarCargo == '') {

            $query = $p->EditarCargos(
                $txtIdcargo,
                $txtnombreCargo,
                $txtSelectSedeCargo
            );
            if ($query) {
                $response = json_encode(array("success" => true));
            } else {
                $response = json_encode(array("success" => false, "mensaje" => "No se ha podido actualizar la información.Intentelo de nuevo."));
            }
        } else {
            $response = json_encode(array("success" => false, "mensaje" => "El cargo que intenta crear se encuentra registrado en la base de datos."));
        }

        break;
}
echo $response;
