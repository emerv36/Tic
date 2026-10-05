<?php
require_once __DIR__ . '/../Config/PDOconn.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
try {
    $db = new PDO(connstring, user, pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch (Exception $ex) {
    echo '<div style="top:150px" class="col-md-12 text-center alert alert-danger">Error de conexión a la base de datos</div>';
    exit;
}
$valor = trim(strtoupper($_POST['b'] ?? $_GET['b'] ?? ''));
$usuario = $_SESSION['IN_codigo_usuCA'] ?? 0;

function resaltar($string, $frase, $taga = '<span style="background-color:#ffcc00">', $tagb = '</span>')
{
    return ($string !== '' && $frase !== '')
        ? preg_replace('/(' . preg_quote($frase, '/') . ')/i' . ('true' ? 'u' : ''), $taga . '\\1' . $tagb, $string)
        : $string;
}


///consulta de estudiantes inscripiciones ////
$query = $db->prepare("SELECT id_inscripcion,
                              identificacion,
                              tipo_identificacionfk,
                              nombre_identidad,
                              nombre_estudiante,
                              apellido_estudiante,
                              celular_estudiante,
                              email_estudiante,
                              tipo_sangre,
                              id_sede_inscripcionfk,
                              nombre_sede,
                              id_programa_inscripcionfk,
                              nombre_programa,
                              id_lote_inscripcionfk,
                              codigo,
                              etapa,
                              estado_inscripcion AS valor_registro,
                              CASE  
                                  WHEN estado_inscripcion='1' THEN 'EN PROCESO' 
                                  WHEN estado_inscripcion='2' THEN 'REALIZADO'
                                  WHEN estado_inscripcion='3' THEN 'ENTREGADO'                                  
                              END AS estado_inscripcion,
                              tipo_carnet AS valor_carnet,
                              CASE  
                                WHEN tipo_carnet='1' THEN 'NUEVO' 
                                WHEN tipo_carnet='2' THEN 'RENOVACION' 
                              END AS tipo_carnet,
                              categoria_carnet AS valor_categoria,
                              CASE  
                                WHEN categoria_carnet='1' THEN 'ESTUDIANTE' 
                                WHEN categoria_carnet='2' THEN 'FUNCIONARIO' 
                                WHEN categoria_carnet='3' THEN 'PRACTICANTE' 
                              END AS categoria_carnet,
                              fecha_inscripcion,
                              usuario_registra_inscripcion,
                              fecha_entrega_carnet,
                              fecha_recibido_carnet,
                              observacion_correccion,
                              codigo_foto,
                              indicador_foto,
                              obscarnetfk,
                              observacion_novedad,
                              chip_carnet,
                              lugar_reclamo,
                              usuario_entrega_carnet,
                              id_usuario_carnetfk
                              FROM inscripcion i
                                     LEFT JOIN tipo_identidad t  		          	ON i.tipo_identificacionfk=t.id_tipo
                                     LEFT JOIN programa p 		          	        ON i.id_programa_inscripcionfk=p.id_programa
                                     LEFT JOIN sede s    		          	        ON i.id_sede_inscripcionfk=s.id_sede
                                     LEFT JOIN lotecarnet l    		       	ON i.id_lote_inscripcionfk=l.id_lote
                              WHERE (identificacion LIKE :b  OR nombre_estudiante LIKE :b OR apellido_estudiante LIKE :b  OR email_estudiante LIKE :b OR CONCAT(nombre_estudiante,' ',apellido_estudiante) LIKE :b) 
                             
                              LIMIT 1");

$query->execute(array(':b' => '%' . $valor . '%'));
$row_count = $query->rowCount();

if ($row_count == 0) {
    echo '<div style="top:150px" class="col-md-12 text-center alert alert-danger">No se encontraron resultados con ese criterio de busqueda</div>';
    exit;
} else {

    while ($row = $query->fetch()) {
        $identificacion = resaltar($row['identificacion'], $valor);
        $nombre_estudiante = resaltar(strtoupper($row['nombre_estudiante']), $valor);
        $apellido_estudiante = resaltar(strtoupper($row['apellido_estudiante']), $valor);
        $celular_estudiante = resaltar($row['celular_estudiante'], $valor);
        $email_estudiante = resaltar($row['email_estudiante'], $valor);
        $tipo_sangre = resaltar(strtoupper($row['tipo_sangre']), $valor);

        $valor_registro = $row['valor_registro'];
        if ($valor_registro == 1) {
            $valor_registro = '<span title="Sede" class="label label label-warning">' . $row['estado_inscripcion'] . '</span>';
        } else if ($valor_registro == 2) {
            $valor_registro = '<span title="Sede" class="label label label-info">' . $row['estado_inscripcion'] . '</span>';
        } else if ($valor_registro == 3) {
            $valor_registro = '<span title="Sede" class="label label label-success">' . $row['estado_inscripcion'] . '</span>';
        } else if ($valor_registro == 4) {
            $valor_registro = '<span title="Sede" class="label label label-danger">' . $row['estado_inscripcion'] . '</span>';
        }

        // Acciones de botones
        $recibido = '';
        if ($row['valor_registro'] == 1) {
            $recibido = '<button class="btn btn-success" title="Confirmar como realizado"  onclick="modalcambiarrecibido(' . $row['id_inscripcion'] . ',' . "'" . strtoupper($row['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($row['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i> Confirmar Realizado</button>';
        } else {
            $recibido = '<button  disabled class="btn btn-success" title="Confirmar como realizado"  onclick="modalcambiarrecibido(' . $row['id_inscripcion'] . ',' . "'" . strtoupper($row['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($row['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i> Confirmar Realizado</button>';
        }

        $editar = '';
        if ($row['valor_registro'] == 1 || $row['valor_registro'] == 2 || $row['valor_registro'] == 4) {
            $editar = '<button class="btn btn-primary" title="Editar informacio de estudiante"  onclick="modaleditarinscripcion(' . $row['id_inscripcion'] . ')"> Editar Registro <i class="fa fa-edit"></i></button>';
        } else {
            $editar = '<button disabled class="btn btn-primary" title="Editar informacio de estudiante"  onclick="modaleditarinscripcion(' . $row['id_inscripcion'] . ')"> Editar Registro <i class="fa fa-edit"></i></button>';
        }

        $entrega = '';
        if ($row['valor_registro'] == 2) {
            $entrega = '<button class="btn btn-primary" title="Entrega carnet"  onclick="ModalEntregaCarnet(' . $row['id_inscripcion'] . ')"> Entregar carnet <i class="fa fa-handshake-o"></i></button>';
        } else {
            $entrega = '<button disabled class="btn btn-primary" title="Entrega carnet"  onclick="ModalEntregaCarnet(' . $row['id_inscripcion'] . ')"> Entregar carnet <i class="fa fa-handshake-o"></i></button>';
        }

        $lote = '';
        if ($row['valor_registro'] == 1) {
            $lote = '<button class="btn btn-primary" title="Cambiar Lote"  onclick="modalcambiarLote(' . $row['id_inscripcion'] . ')"> Cambiar de Lote <i class="fa fa-vcard-o"></i></button>';
        } else {
            $lote = '<button disabled class="btn btn-primary" title="Cambiar Lote"  onclick="modalcambiarLote(' . $row['id_inscripcion'] . ')"> Cambiar de Lote <i class="fa fa-vcard-o"></i></button>';
        }

        $cambios = '';
        if ($row['valor_registro'] == 1 || $row['valor_registro'] == 2) {
            $cambios = '<button class="btn btn-primary" title="Editar informacio de estudiante"  onclick="modalrealizarcambios(' . $row['id_inscripcion'] . ')"> Realizar cambio <i class="fa fa-wrench"></i></button>';
        } else {
            $cambios = '<button disabled class="btn btn-primary" title="Editar informacio de estudiante"  onclick="modalrealizarcambios(' . $row['id_inscripcion'] . ')"> Realizar cambio <i class="fa fa-wrench"></i></button>';
        }

        $programa = '';
        if ($row['valor_registro'] == 1) {
            $programa = '<button class="btn btn-primary" title="Editar programa"  onclick="modalcambiarprograma(' . $row['id_inscripcion'] . ')"> Cambiar programa <i class="fa fa-briefcase"></i></button>';
        } else {
            $programa = '<button disabled class="btn btn-primary" title="Editar programa"  onclick="modalcambiarprograma(' . $row['id_inscripcion'] . ')"> Cambiar programa <i class="fa fa-briefcase"></i></button>';
        }

        $chip = '';
        if ($row['chip_carnet'] == 'SI') {
            $chip = '<span title="Chip Carnet" class="label label-success">SI</span>';
        } else if ($row['chip_carnet'] == 'NO') {
            $chip = '<span title="Chip Carnet" class="label label-danger">NO</span>';
        }

?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Información de Registro</a></li>
                            <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">Información Corrección Carnet</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab_1">
                                <div class="box-body">

                                    <div class="row">

                                        <div class="col-md-2">
                                            <label for="nombres">Nombre:</label><br>
                                            <span title="Nombre del estudiante"><?= $nombre_estudiante ?></span>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="apellidos">Apellidos:</label><br>
                                            <span title="Apellidos del estudiante"><?= $apellido_estudiante ?></span>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="email">Email:</label><br>
                                            <span title="Email del estudiante"><?= $email_estudiante ?></span>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="identificacion">No Identidad:</label><br>
                                            <span title="Identificacion"><?= '(' . $row['nombre_identidad'] . ')' . $identificacion ?></span>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="celular">No Celular:</label><br>
                                            <span title="Celular del estudiante"><?= $celular_estudiante ?></span>
                                        </div>

                                        <div class="col-md-1">
                                            <label for="rh">Rh:</label><br>
                                            <span title="Tipo de sangre"><?= $tipo_sangre ?></span>
                                        </div>

                                    </div>

                                    <br>
                                    <div class="row">

                                        <div class="col-md-2">
                                            <label for="sede">Sede:</label><br>
                                            <span title="Sede"><?= strtoupper($row['nombre_sede']) ?></span>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="categoria">Categoría:</label><br>

                                            <?php if ($row['valor_categoria'] == 1) { ?>
                                                <span title="categoria" class="label label-danger"><?= strtoupper($row['categoria_carnet']) ?></span>
                                            <?php } else if ($row['valor_categoria'] == 2) { ?>
                                                <span title="categoria" class="label label-primary"><?= strtoupper($row['categoria_carnet']) ?></span>
                                            <?php } else if ($row['valor_categoria'] == 3) { ?>
                                                <span title="categoria" class="label label-warning"><?= strtoupper($row['categoria_carnet']) ?></span>
                                            <?php }  ?>

                                        </div>

                                        <div class="col-md-3">
                                            <?php if ($row['valor_categoria'] == 1) { ?>
                                                <label for="categoria">Programa:</label><br>
                                                <span title="categoria"><?= strtoupper($row['nombre_programa']) ?></span>
                                            <?php } else if ($row['valor_categoria'] == 2) { ?>
                                                <label for="categoria">Cargo:</label><br>
                                                <span title="categoria"><?= strtoupper($row['nombre_programa']) ?></span>
                                            <?php } else if ($row['valor_categoria'] == 3) { ?>
                                                <label for="categoria">Practicante de:</label><br>
                                                <span title="categoria"><?= strtoupper($row['nombre_programa']) ?></span>
                                            <?php }  ?>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="Lote">Código Lote:</label><br>
                                            <span title="Sede" class="label label label-success"><?= strtoupper($row['etapa'] . $row['codigo']) ?></span>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="Lote">Estado Carnet:</label><br>
                                            <span title="Sede"><?= $valor_registro ?></span>
                                        </div>

                                        <div class="col-md-1">
                                            <label for="Lote">Tipo Carnet:</label><br>
                                            <span title="Sede" class="label label label-success"><?= strtoupper($row['tipo_carnet']) ?></span>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <hr>
                                        <div class="col-md-2">
                                            <label for="fecha_registro">Fecha de Registro:</label><br>
                                            <span title="fecha"><?= (!empty($row['fecha_inscripcion']) && $row['fecha_inscripcion'] != '0000-00-00 00:00:00') ? $row['fecha_inscripcion'] : ($row['fecha_recibido_carnet'] ?? '') ?></span>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="creado_por">Indicador Fotografía:</label><br>
                                            <span title="Indicador foto"><?= strtoupper($row['indicador_foto']) ?></span>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="creado_por">Código Fotografía:</label><br>
                                            <span title="Codigo foto"><?= strtoupper($row['codigo_foto']) ?></span>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="creado_por">Observación Novedad:</label><br>
                                            <span title="Tipo de Novedad"><?= strtoupper($row['observacion_novedad'] ?? 'NINGUNA') ?></span>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="creado_por">Usuario Registra:</label><br>
                                            <span title="Usuario que registra"><?= strtoupper($row['usuario_registra_inscripcion']) ?></span>
                                        </div>

                                        <div class="col-md-1">
                                            <label for="creado_por">¿Chip?:</label><br>
                                            <span><?= $chip ?></span>
                                        </div>
                                    </div>

                                    <br>
                                    <div class="row">

                                        <div class="col-md-12">
                                            <p>
                                                <?= $editar ?>
                                                <button class="btn btn-info" title="Ver Fotografia de carnet" onclick="modalverfoto(<?= $row['id_inscripcion'] ?>)"><i class="fa fa-vcard-o"></i></button>
                                                <button class="btn btn-success" title="Renovación de Carnet" onclick="modalrenovacioncarnet(<?= $row['id_inscripcion'] ?>)"><i class="fa fa-refresh"></i> Renovación </button>
                                                <button class="btn btn-primary" title="Imprimir PDF" onclick="reportecarnetporid(<?= $row['id_inscripcion'] ?>)"><i class="fa fa-file-pdf-o"></i></button>
                                                <?= $entrega ?>
                                                <?= $lote ?>
                                                <?= $cambios ?>
                                                <?= $programa ?>
                                            </p>
                                            <p><?= $recibido ?></p>
                                        </div>

                                    </div>

                                </div>

                            </div>
                            <!-- /.tab-pane -->
                            <div class="tab-pane" id="tab_2">

                                <div class="box-body">

                                    <div class="row">

                                        <div class="col-md-12">
                                            <label for="nombres">Observación Novedad:</label><br>
                                            <span title="Nombre del estudiante"><?= strtoupper($row['observacion_correccion']) ?></span>
                                        </div>

                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

<?php

    }
}

?>