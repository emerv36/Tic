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
                                     LEFT JOIN sede           s  		          	ON i.id_sede_inscripcionfk=s.id_sede
                                     LEFT JOIN programa       p  		          	ON i.id_programa_inscripcionfk=p.id_programa
                                     LEFT JOIN lotecarnet     l  		          	ON i.id_lote_inscripcionfk=l.id_lote
                                     LEFT JOIN obscarnet     ob 		          	ON i.obscarnetfk=ob.id_obs
           
                    WHERE (nombre_estudiante LIKE :valor OR apellido_estudiante LIKE :valor OR CONCAT(apellido_estudiante,' ',nombre_estudiante) LIKE :valor OR CONCAT(nombre_estudiante,' ',apellido_estudiante) LIKE :valor OR identificacion LIKE :valor OR celular_estudiante LIKE :valor) ORDER BY id_inscripcion DESC limit 15");
$query->bindValue(':valor', '%' . $valor . '%', PDO::PARAM_STR);
$query->execute();
$estudiantes_res = $query->fetchAll(PDO::FETCH_ASSOC);
////consulta de estudiantes matriculados en un programa ////
?>

<div class="row">
    <div class="col-md-12">
        <?php
        $i = 1;
        $x = 1;
        $z = 1;
        $editar = '';
        if (count($estudiantes_res) > 0) { ?>

            <?php foreach ($estudiantes_res as $row) { ?>

                <?php
                $entrega = '';
                $eliminar = '';
                $disabled = 'disabled';

                if ($row['valor_registro'] == '1') {
                    $editar = '<button  class="btn btn-primary" title="Editar Registro"  onclick="CargarEditarInscripcion(' . $row['id_inscripcion'] . ','
                        . "'" . $row['identificacion'] . "'" . ','
                        . "'" . $row['tipo_identificacionfk'] . "'" . ','
                        . "'" . $row['nombre_estudiante'] . "'" . ','
                        . "'" . $row['apellido_estudiante'] . "'" . ','
                        . "'" . $row['email_estudiante'] . "'" . ','
                        . "'" . $row['celular_estudiante'] . "'" . ','
                        . "'" . $row['tipo_sangre'] . "'" . ','
                        . $row['id_lote_inscripcionfk'] . ','
                        . "'" . $row['indicador_foto'] . "'" . ','
                        . "'" . $row['codigo_foto'] . "'" . ','
                        . "'" . $row['chip_carnet'] . "'" . ','
                        . $row['obscarnetfk'] . ')"> Editar Registro <i class="fa fa-check-square-o"></i></button>';
                } else {
                    $editar = '<button disabled  class="btn btn-primary" title="Editar Registro"> Editar Registro <i class="fa fa-check-square-o"></i></button>';
                }

                if ($row['valor_registro'] == '1') {
                    $cambio = '<button   class="btn btn-primary" title="Registrar Cambio"  onclick="ModalRelizarCambios(' . $row['id_inscripcion'] . ')">Realizar cambio <i class=" 	fa fa-gavel"></i></button>';
                    $eliminar = '<button class="btn btn-danger" title="Eliminar Registro"  onclick="modalEliminarRegistro(' . $row['id_inscripcion'] . ')">Me equivoque, Quiero Eliminar!  <i class="fa fa-trash"></i></button>';
                    $recibido = '<button class="btn btn-success" title="Confirmar como realizado"  onclick="modalcambiarrecibido(' . $row['id_inscripcion'] . ',' . "'" . strtoupper($row['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($row['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i> Confirmar Realizado</button>';
                } else {
                    $cambio = '<button disabled  class="btn btn-primary" title="Registrar Cambio"  onclick="ModalRelizarCambios(' . $row['id_inscripcion'] . ')">Realizar cambio <i class=" 	fa fa-gavel"></i></button>';
                    $recibido = '<button  disabled class="btn btn-success" title="Confirmar como realizado"  onclick="modalcambiarrecibido(' . $row['id_inscripcion'] . ',' . "'" . strtoupper($row['nombre_estudiante']) . "'" . ',' . "'" . strtoupper($row['apellido_estudiante']) . "'" . ')"><i class="fa fa-check-circle"></i> Confirmar Realizado</button>';
                }
                if ($row['valor_registro'] == '1' || $row['valor_registro'] == '4') {
                    $lote = '<button   class="btn btn-primary" title="Cambiar de lote"  onclick="ModalCambiarLote(' . $row['id_inscripcion'] . ',' . $row['id_lote_inscripcionfk'] . ')"> Cambiar de Lote <i class="fa fa-id-card"></i></button>';
                } else {
                    $lote = '<button disabled  class="btn btn-primary" title="Cambiar de lote"> Cambiar de Lote <i class="fa fa-id-card"></i></button>';
                }

                if ($row['valor_registro'] == '2') {
                    $entrega = '<button class="btn btn-primary" title="Entrega carnet"  onclick="ModalEntregaCarnet(' . $row['id_inscripcion'] . ')"> Entregar carnet <i class="fa fa-handshake-o"></i></button>';
                } else {
                    $entrega = '<button disabled class="btn btn-primary" title="Entrega carnet"  onclick="ModalEntregaCarnet(' . $row['id_inscripcion'] . ')"> Entregar carnet <i class="fa fa-handshake-o"></i></button>';
                }

                if ($row['valor_registro'] == '1') {
                    $programa = '<button class="btn btn-primary" title="Cambiar programa"  onclick="ModalCambiarPrograma(' . $row['id_inscripcion'] . ',' . "'" . $row['nombre_sede'] . "'" . ',' . "'" . $row['nombre_programa'] . "'" . ',' . "'" . $row['categoria_carnet'] . "'" . ')">Cambiar programa <i class="fa fa-briefcase"></i></button>';
                } else {
                    $programa = '<button disabled class="btn btn-primary" title="Cambiar programa"> Cambiar programa <i class="fa fa-briefcase"></i></button>';
                }

                //cambiar colores de estado

                if ($row['valor_registro'] == '1') {

                    $valor_registro = "<span class='label label-primary'>EN PROCESO</span>";
                } else if ($row['valor_registro'] == '2') {
                    $valor_registro = "<span class='label label-warning'>RECIBIDO</span>";
                } else if ($row['valor_registro'] == '3') {
                    $valor_registro = "<span class='label label-success'>ENTREGADO</span>";
                } else if ($row['valor_registro'] == '4') {
                    $valor_registro = "<span class='label label-danger'>CORRECCION</span>";
                }

                if ($row['chip_carnet'] == 'SI') {
                    $valor_chip = "<span class='label label-success'>SI</span>";
                } else {
                    $valor_chip = "<span class='label label-danger'>NO</span>";
                }

                $idSige = (int) $row['id_inscripcion'];
                $documentoSige = htmlspecialchars(
                    json_encode((string) $row['identificacion'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                    ENT_QUOTES,
                    'UTF-8'
                );
                $chip = '<button class="btn btn-info" title="Asignar / Vincular Tarjeta RFID" onclick="ModalActivarChip(' . $row['id_inscripcion'] . ',' . "'" . $row['identificacion'] . "'" . ')"><i class="fa fa-id-card-o"></i></button>';
                
                $btnRenovacion = '<button type="button" class="btn btn-success btn-xs" title="Renovación de vigencia (mismo carné)" onclick="abrirModalRenovacionSige(' . $idSige . ', ' . $documentoSige . ')"><i class="fa fa-refresh"></i> Renovación</button>';

                $exportarPdf = '<a href="exportar_carnet.php?id='.$row['id_inscripcion'].'" target="_blank" class="btn btn-primary" title="Exportar Carnet PDF"><i class="fa fa-file-pdf-o"></i></a>';
                $opciones = $editar . ' ' . $chip . ' ' . $btnRenovacion . ' ' . $exportarPdf . ' ' . $entrega . ' ' . $lote . ' ' . $cambio . ' ' . $programa . ' ' . $recibido . ' ' . $eliminar;
                ?>


                <div class="box">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#<?= $i . $row['id_inscripcion'] ?>">Información de Registro</a></li>
                        <li><a data-toggle="tab" href="#ide<?= $i . $row['id_inscripcion'] ?>">Información Corrección Carnet</a></li>

                    </ul>
                    <div class="tab-content">
                        <div id="<?= $i . $row['id_inscripcion'] ?>" class="tab-pane fade in active">
                            <div class="panel panel-primary">
                                <div class="panel-body">

                                    <div class="row">

                                        <div class="col-md-2" style="word-break: break-all;">
                                            <label for="Nombre">Nombre:</label><br>
                                            <span title="tipo"><?= strtoupper(strtolower(resaltar($row['nombre_estudiante'], $valor))) ?></span>
                                        </div>

                                        <div class="col-md-2" style="word-break: break-all;">
                                            <label for="apellido">Apellidos:</label><br>
                                            <span title="Apellidos"><?= strtoupper(strtolower(resaltar($row['apellido_estudiante'], $valor))) ?></span>
                                        </div>


                                        <div class="col-md-3" style="word-break: break-all;">
                                            <label for="email">Email:</label><br>
                                            <span title="Email"><?= strtolower($row['email_estudiante']) ?></span>
                                        </div>
                                        <div class="col-md-2" style="word-break: break-all;">
                                            <label for="Identidad">No Identidad:</label><br>
                                            <span title="Identidad"><?= resaltar('(' . $row['nombre_identidad'] . ')' . $row['identificacion'], $valor) ?></span>
                                        </div>

                                        <div class="col-md-2" style="word-break: break-all;">
                                            <label for="celular">No Celular:</label><br>
                                            <span title="celular"><?= resaltar($row['celular_estudiante'], $valor) ?></span>
                                        </div>

                                        <div class="col-md-1" style="word-break: break-all;">
                                            <label for="tipo_sangre">Rh:</label><br>
                                            <span title="Tipo de sangre"><?= strtoupper($row['tipo_sangre']) ?></span>
                                        </div>

                                    </div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <label for="Sede">Sede:</label><br>
                                            <span title="Sede"><?= strtoupper($row['nombre_sede']) ?></span>
                                        </div>

                                        <div class="col-md-2">
                                            <?php if ($row['valor_categoria'] == 1) { ?>
                                                <label for="Programa">Categoría:</label><br>
                                                <span title="categoria" class="label label-danger"><?= strtoupper($row['categoria_carnet']) ?></span>
                                            <?php } else if ($row['valor_categoria'] == 2) { ?>
                                                <label for="Programa">Categoría:</label><br>
                                                <span title="categoria" class="label label-success"><?= strtoupper($row['categoria_carnet']) ?></span>
                                            <?php } else if ($row['valor_categoria'] == 3) { ?>
                                                <label for="Programa">Categoría:</label><br>
                                                <span title="categoria" class="label label-warning"><?= strtoupper($row['categoria_carnet']) ?></span>
                                            <?php } ?>
                                        </div>

                                        <div class="col-md-2">
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

                                        <div class="col-md-2">
                                            <label for="Lote">Tipo Carnet:</label><br>
                                            <span title="Sede" class="label label label-success"><?= strtoupper($row['tipo_carnet']) ?></span>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <hr>
                                        <div class="col-md-2">
                                            <label for="fecha_registro">Fecha de Registro:</label><br>
                                            <span title="fecha"><?= $row['fecha_inscripcion'] ?></span>
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
                                            <span title="Observación"><?= strtoupper($row['observacion_novedad']) ?></span>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="creado_por">Usuario Registra:</label><br>
                                            <span title="Usuario"><?= strtoupper($row['usuario_registra_inscripcion']) ?></span>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="creado_por">¿Chip?:</label><br>
                                            <span title="Chip"><?= $valor_chip ?></span>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <hr>
                                        <div class="col-md-12">
                                            <?php echo $opciones; ?>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                        <div id="ide<?= $i . $row['id_inscripcion'] ?>" class="tab-pane fade">
                            <div class="panel panel-primary">
                                <div class="panel-body"> 
                                    <div class="row">

                                        <div class="col-md-6">
                                            <label for="creado_por">Observación corrección:</label><br>
                                            <span title="Usuario"><?= strtoupper($row['observacion_correccion']) ?></span>
                                        </div>
                                        <?php 
                                        if($row['lugar_reclamo'] != ""){
                                        ?>
                                        <div class="col-md-3">
                                            <label for="creado_por">Lugar reclamo:</label><br>
                                            <span title="Usuario"><?= strtoupper($row['lugar_reclamo']) ?></span>
                                        </div>
                                        <?php
                                        }
                                        ?>
                                        <div class="col-md-3">
                                            <label for="creado_por">Estado carnet:</label><br>
                                            <span title="Usuario"><?= $valor_registro ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <tbody class="buscar">

            <?php
                $i++;
                $z++;
            }
        } else {
            echo '<div style="top:150px" class="col-md-12 text-center">¡No hay resultados!</div>';
        }
            ?>
    </div>
</div>