<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['SIGE_CSRF_TOKEN'])) $_SESSION['SIGE_CSRF_TOKEN'] = bin2hex(random_bytes(32));
?>
<script>window.SIGE_CSRF_TOKEN = <?php echo json_encode($_SESSION['SIGE_CSRF_TOKEN']); ?>;</script>
<link rel="stylesheet" href="plugins/cropper/cropper.min.css">
<style>
    #titulo {
        background: #fff;
        color: #000;
        width: 100%;
        padding-left: 20px;
        padding-right: 20px;
        padding-top: 5px;
        padding-bottom: 5px;
        margin-top: 5px;
        margin-bottom: 5px;
        border-bottom: 3px solid #337ab7;
    }

    .select2-container .select2-selection--single {
        height: 30px !important;
    }

    .select2-selection__arrow {
        height: 30px !important;
    }

    .select2-selection__arrow {
        height: 30px !important;
    }

    .contenedor-canvas {
        width: 230px;
        height: 300px;
        overflow: hidden;
        margin: 10px;
        position: relative;
    }

    .contenedor-canvas>.recortar-canvas {
        position: absolute;
        left: -100%;
        right: -100%;
        top: -100%;
        bottom: -100%;
        margin: auto;
        min-height: 100%;
        min-width: 100%;
    }

    .contenedor-canvas>.recortar-video {
        position: absolute;
        left: -100%;
        right: -100%;
        top: -100%;
        bottom: -100%;
        margin: auto;
        min-height: 100%;
        min-width: 100%;
    }

    .span {
        background: silver;
        border-color: silver;
    }

    /* Estilos para el Preview del Carnet */
    .carnet-mockup {
        width: 400px;
        /* Agrandado para mejor visibilidad */
        height: 250px;
        background-color: #fff;
        background-size: 100% 100%;
        background-repeat: no-repeat;
        border: 1px solid #ccc;
        border-radius: 12px;
        position: relative;
        margin: 0 auto 20px auto;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        font-family: 'Segoe UI', Roboto, Arial, sans-serif;
        overflow: hidden;
        user-select: none;
    }

    .carnet-mockup.estudiantil-bg {
        background-image: url('assets/images/estudiantil.png');
    }

    .carnet-mockup.practicante-bg {
        background-image: url('assets/images/Practicante.png');
    }

    .carnet-photo-area {
        width: 142px;
        height: 142px;
        background: transparent;
        position: absolute;
        top: 60px;
        right: 30px;
        overflow: hidden;
        z-index: 2;
        border-radius: 50%;
    }

    .carnet-photo-area video,
    .carnet-photo-area canvas {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover;
        display: block;
    }

    .carnet-data {
        position: absolute;
        top: 55px;
        left: 25px;
        right: 170px;
        z-index: 2;
        text-align: left;
    }

    .carnet-field {
        margin-bottom: 5px;
    }

    .carnet-label {
        font-weight: 800;
        color: #003366;
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .carnet-value {
        color: #000;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        max-width: 140px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.1;
    }
    
    /* Variación Vertical para Practicantes */
    .carnet-mockup.vertical {
        width: 250px;
        height: 400px;
    }
    
    .carnet-mockup.vertical .carnet-photo-area {
        width: 120px;
        height: 120px;
        top: 60px;
        left: 50%;
        transform: translateX(-50%);
        right: auto;
    }

    .carnet-mockup.vertical .carnet-data {
        top: 200px;
        left: 10px;
        right: 10px;
        text-align: center;
    }

    .carnet-mockup.vertical .carnet-value {
        max-width: 100%;
        margin: 0 auto;
    }
</style>
<div class="content-wrapper">
    <div class="content">
        <div class="modal fade" id="registrar-inscripcion" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg" style="width: 90%; max-width: 1200px;">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Nuevo Registro Carnet </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <form id="Registrar-Inscripcion" method="POST">
                                <div class="col-md-7" style="border-right: 1px solid #ddd;">
                                    <input type="hidden" name="foto_base64" id="foto_base64">

                                    <div class="input-item  col-md-6 col-xs-12">
                                        <label for="RegistroIdentificacion">Identificación:
                                            <span class="required">*</span>
                                        </label>
                                        <input type="hidden" name="txtRegistroPerfil" id="txtRegistroPerfil"
                                            data-toggle="tooltip" title="Identidad solo números">
                                        <input class="form-control input-sm" type="number"
                                            id="txtRegistroIdentificacion" name="txtRegistroIdentificacion"
                                            autocomplete="off" maxlength="10"
                                            oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                            data-toggle="tooltip"
                                            title="Ingrese número de identidad sin singos puntos o comas"
                                            autofocus="" />
                                        <input type="hidden" name="swemail" id="swemail" value="">
                                    </div>

                                    <div class="input-item col-md-6 col-xs-12">
                                        <label for="RegistroTipoIdentificacion">Tipo Identificación:
                                            <span class="required">*</span>
                                        </label>
                                        <select class="select form-control input-sm" id="txtRegistroTipoIdentificacion"
                                            name="txtRegistroTipoIdentificacion" data-toggle="tooltip"
                                            title="Seleccione tipo de identidad">
                                        </select>
                                    </div>

                                    <div class="input-item  col-md-6 col-xs-12">
                                        <label for="RegistroNombreEstudiante">Nombre:
                                            <span class="required">*</span>
                                        </label>
                                        <input class="form-control input-sm text-uppercase" type="text"
                                            id="txtRegistroNombreEstudiante" name="txtRegistroNombreEstudiante"
                                            autocomplete="off" data-toggle="tooltip" title="Ingrese nombre" />
                                    </div>

                                    <div class="input-item col-md-6 col-xs-12">
                                        <label for="RegistroApellidoEstudiante">Apellido:
                                            <span class="required">*</span>
                                        </label>
                                        <input class="form-control input-sm text-uppercase" type="text"
                                            id="txtRegistroApellidoEstudiante" name="txtRegistroApellidoEstudiante"
                                            autocomplete="off" data-toggle="tooltip" title="Ingrese apellidos" />
                                    </div>

                                    <div class="input-item  col-md-6 col-xs-12">
                                        <label for="RegistroEmailEstudiante">Email:
                                            <span class="required">*</span>
                                        </label>
                                        <input class="form-control input-sm input-sm text-uppercase" type="email"
                                            id="txtRegistroEmail" name="txtRegistroEmail" autocomplete="off"
                                            data-toggle="tooltip"
                                            title="ingrese email valido, recibirás notificaciones por este medio">
                                    </div>

                                    <div class="input-item  col-md-6  col-xs-12">
                                        <label for="RegistroCelular">Celular: </label>
                                        <input class="form-control input-sm input-sm text-uppercase" type="text"
                                            id="txtRegistroCelular" name="txtRegistroCelular" autocomplete="off"
                                            data-inputmask="'mask': ['999-999-99-99']" data-mask data-toggle="tooltip"
                                            title="Ingrese número de celular">
                                    </div>


                                    <div class="input-item col-md-3 col-xs-12">
                                        <label for="RegistroTipoSangre">Tipo Sangre:
                                            <span class="required">*</span>
                                        </label>
                                        <select class="select form-control input-sm" id="txtRegistroTipoSangre"
                                            name="txtRegistroTipoSangre" data-toggle="tooltip" title="Seleccione">
                                            <option value="">Seleccione..</option>
                                            <option value="RH O+">RH O+</option>
                                            <option value="RH O-">RH O-</option>
                                            <option value="RH A+">RH A+</option>
                                            <option value="RH A-">RH A-</option>
                                            <option value="RH AB+">RH AB+</option>
                                            <option value="RH AB-">RH AB-</option>
                                            <option value="RH B+">RH B+</option>
                                            <option value="RH B-">RH B-</option>
                                            <option value="SIN INFORMACION">SIN INFORMACION</option>
                                        </select>
                                    </div>

                                    <div class="input-item col-md-3 col-xs-12">
                                        <label for="RegistroCategoriaCarnet">Categoria:
                                            <span class="required">*</span>
                                        </label>
                                        <select class="select form-control input-sm" id="txtRegistroCategoriaCarnet"
                                            name="txtRegistroCategoriaCarnet" data-toggle="tooltip" title="Seleccione">
                                            <option value="">Seleccione..</option>
                                            <option value="1">ESTUDIANTE</option>
                                            <!--<option value="2">FUNCIONARIO</option>-->
                                            <option value="3">PRACTICANTE</option>
                                        </select>
                                    </div>

                                    <div class="input-item col-md-6 col-xs-12">
                                        <label><span id="RegistroSede">Sede:</span> <span
                                                class="required">*</span></label>
                                        <select class="select form-control input-sm" id="txtRegistroSede"
                                            name="txtRegistroSede" data-toggle="tooltip" title="Seleccione sede">
                                            <option value="">Seleccione..</option>
                                        </select>
                                    </div>

                                    <div class="input-item col-md-6 col-xs-12">
                                        <label for="RegistroPrograma"><span id="LblRprograma">Programa:</span> <span
                                                class="required">*</span> <a id="btn_agregar_programa_cargo"
                                                onclick="modalPrograma()"><small><small>Agregar
                                                        nuevo</small></small></a></label>
                                        <select class="select form-control input-sm" id="txtRegistroPrograma"
                                            name="txtRegistroPrograma" data-toggle="tooltip" title="Seleccione sede">
                                            <option value="">Seleccione..</option>
                                        </select>
                                    </div>

                                    <div class="input-item col-md-6 col-xs-12">
                                        <label><span>Reclamar en:</span></label>
                                        <select class="select form-control input-sm" id="txtLugarReclamo"
                                            name="txtLugarReclamo" data-toggle="tooltip" title="Seleccione sede">
                                            <option value="" disabled>Seleccione...</option>
                                            <option value="PRINCIPAL" selected>PRINCIPAL</option>
                                            <option value="SOLEDAD">SOLEDAD</option>
                                            <option value="PUERTO COLOMBIA">PUERTO COLOMBIA</option>
                                            <option value="COSMOS">COSMOS</option>
                                            <option value="SITIO NUEVO">SITIO NUEVO</option>
                                        </select>
                                    </div>

                                    <div class="input-item col-md-6 col-xs-12">
                                        <label for="RegistroLote">Asignar al Lote:
                                            <span class="required">*</span>
                                        </label>
                                        <select class="select form-control input-sm" id="txtRegistroLote"
                                            name="txtRegistroLote" data-toggle="tooltip" title="Seleccione lote">
                                            <option value="">Seleccione..</option>
                                        </select>
                                    </div>
                                    <div class="input-item col-md-3 col-xs-12">
                                        <label for="RegistroLote">Fotografía:
                                            <span class="required">*</span>
                                        </label>
                                        <select class="select form-control input-sm" id="txtRegistroIndicador"
                                            name="txtRegistroIndicador" data-toggle="tooltip"
                                            title="Seleccione indicador">
                                            <option value="">Seleccione</option>
                                            <option value="CAMARA">CAMARA</option>
                                            <option value="CORREO">CORREO</option>
                                            <option value="WHATSAPP">WHATSAPP</option>
                                        </select>
                                    </div>




                                    <div class="input-item col-md-6 col-xs-12">
                                        <label for="RegistroNovedad">Observación Novedad:
                                            <span class="required">*</span>
                                        </label>
                                        <select class="select form-control input-sm" id="txtRegistroNovedad"
                                            name="txtRegistroNovedad" data-toggle="tooltip" title="Seleccione novedad">
                                            <option value="">Seleccione..</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <h4 class="text-center" style="margin-top: 0; color: #003366; font-weight: bold;">
                                        Previsualización</h4>
                                    <!-- Preview del Carnet con Plantilla PNG -->
                                    <div class="carnet-mockup estudiantil-bg" id="preview-carnet-mockup">

                                        <div class="carnet-photo-area">
                                            <video id="video" playsinline autoplay muted></video>
                                            <canvas id="canvas" style="display:none"></canvas>
                                        </div>

                                        <div class="carnet-data">
                                            <div class="carnet-field">
                                                <div class="carnet-label">NOMBRE Y APELLIDO:</div>
                                                <div class="carnet-value" id="preview-nombre">---</div>
                                            </div>
                                            <div class="carnet-field">
                                                <div class="carnet-label">IDENTIFICACIÓN:</div>
                                                <div class="carnet-value" id="preview-identificacion">---</div>
                                            </div>
                                            <div class="carnet-field">
                                                <div class="carnet-label">PROGRAMA:</div>
                                                <div class="carnet-value" id="preview-programa">---</div>
                                            </div>
                                            <div class="carnet-field">
                                                <div class="carnet-label">SEDE:</div>
                                                <div class="carnet-value" id="preview-sede">---</div>
                                            </div>
                                            <div class="carnet-field">
                                                <div class="carnet-label">RH:</div>
                                                <div class="carnet-value" id="preview-rh">---</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center" style="margin-top: 15px;">
                                        <div id="snap">
                                            <button type="button" class="btn btn-primary" onclick="tomarFoto()">Capturar Foto</button>
                                            <label class="btn btn-info" style="margin: 0; margin-left: 5px; cursor: pointer;">Subir Foto
                                                <input type="file" id="foto_upload" accept="image/*" style="display:none">
                                            </label>
                                        </div>
                                        <div id="tomarotra-confirmar" style="display: none;">
                                            <button type="button" class="btn btn-warning" onclick="tomarOtraFoto()">Tomar Otra</button>
                                            <button type="button" class="btn btn-success" onclick="descargarFoto()" style="margin-left:5px;">Descargar</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btn-guardarNuevo" name="btn-guardarNuevo"
                            onclick="GuardarInscripcion();">Guardar</button>
                    </div>
                </div>
            </div>
        </div>



        <div id="contenedor">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#home">Búscar Carnet <i class="fa fa-child"
                            style="font-size:24px"></i> <span class="badge" id="Cmatricula"></span> </a></li>
                <li><a data-toggle="tab" id="op1" href="#menu1">Consulta de Carnet por Lotes <i
                            class="fa fa-handshake-o" style="font-size:24px"></i><span class="badge"
                            id="Cinscrito"></span></a></li>
            </ul>

            <div class="tab-content">
                <div id="home" class="tab-pane fade in active">
                    <div class="box"
                        style="padding-bottom:20px; padding-left: 15px; padding-right: 10px; padding-top: 10px;">
                        <div class="row">
                            <div class="col-md-8 col-sm-12" style="margin-bottom: 10px;">
                                <p class="help-block">
                                    Digite cualquiera de la siguiente información de búsqueda: nombre, apellidos, número
                                    de celular o número de identidad.
                                </p>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                    <input type="text" id="buscar" name="buscar" class="form-control"
                                        placeholder="Buscar Estudiantes...." autocomplete="off" autofocus=""
                                        data-toggle="tooltip" title="Ingrese información de búsqueda"
                                        onkeyup="BusquedaEstudiantes()">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12 text-right" style="margin-top: 30px;">
                                <button type="button" class="btn btn-primary" data-toggle="tooltip"
                                    title="Clic nueva inscripción" onclick="ModalRegistrarInscripcion();">
                                    <i class="fa fa-plus" aria-hidden="true"></i> Nuevo Registro
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-xs-12" id="resultado">

                    </div>

                </div>
                <div id="menu1" class="tab-pane fade">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="box"
                                style="padding-bottom:20px; padding-left: 15px; padding-right: 10px; padding-top: 10px;">
                                <div class="row">

                                    <div class="input-item col-md-3 col-xs-12">
                                        <select name="txtcmbetapa" id="txtcmbetapa" class="form-control"
                                            title="Seleccione etapa">
                                            <option value="">Seleccione tipo..</option>
                                            <option value="1">ETAPA PRODUCTIVA</option>
                                            <option value="2">ETAPA LECTIVA</option>
                                            <option value="3">TODAS LA ETAPAS</option>
                                            <option value="4">TODOS LOS LOTES</option>

                                        </select>
                                    </div>


                                    <div class="input-item col-md-6 col-xs-12">
                                        <select name="txtcmblote" id="txtcmblote" class="form-control"
                                            title="Seleccione lote">
                                            <option value="">Seleccione lote..</option>
                                        </select>
                                    </div>


                                    <div class="input-item col-md-3 col-xs-12">
                                        <button class="btn-block btn btn-success" title="Clic para consultar"
                                            onclick="ListarCarnets();">Consultar</button>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="Cresultado" style="display:none">
                        <div class="row" style="margin-bottom: 15px;">
                            <div class="col-md-12">
                                <button class="btn btn-danger" onclick="ExportarCarnetsPDF();">
                                    <i class="fa fa-file-pdf-o"></i> Descargar Carnets (PDF)
                                </button>
                                <button class="btn btn-success" onclick="ExportarLoteExcel();">
                                    <i class="fa fa-file-excel-o"></i> Descargar Datos (Excel)
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <table class="table table-hover table-striped table-condesed" id="tbl_carnet" width="100%">
                                <thead class="titulo">
                                    <tr>
                                        <th>No.</th>
                                        <th>Identidad</th>
                                        <th>Apellidos</th>
                                        <th>Nombre</th>
                                        <th>Programa</th>
                                        <th>Lote</th>
                                        <th>Tpo sangre</th>
                                        <th>Estado</th>
                                        <th>Chip</th>
                                        <th>Cód Foto</th>
                                        <th>Fecha Tramite</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!--modal para editar el estado de la inscripción del estudiante -->
<div class="modal fade" id="modalconfirmar" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Confirmar</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="codUsuario" id="codUsuario">
                    <input type="hidden" name="estado" id="estado">

                    <section>
                        <p class="mensajeconfirm" style="text-align:center"><B>¿Está seguro de deshabilitar
                                esta
                                Sede?</B></p>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmar-ok" onclick="acceptConfirm()">OK</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>





<div class="modal fade" id="modalcambiarrecibido" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Confirmar realizado</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <section>
                        <input type="hidden" name="Linscripcion" id="Linscripcion">
                        <p class="mensajeconfirm" style="text-align:center" id="mensajeconfirma"></p>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmar-ok"
                    onclick="CambiarRecibido()">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEliminarRegistro" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Eliminar Registro</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <section>
                        <input type="hidden" name="id_EliminarRegistro" id="id_EliminarRegistro">
                        <p class="mensajeconfirm" style="text-align:center"><b>¿Esta seguro de eliminar este
                                registro?<br>Si lo haces no podras recuperarlo.</b></p>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="eliminarRegistro()">Aceptar</button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="modalcambiarentregado" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-green">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Confirmar entrega carnet</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <section>
                        <input type="hidden" name="Linscripcion" id="Linscripcion">
                        <p class="mensajeconfirm" style="text-align:center" id="mensajeconfirmaentregado"></p>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmar-ok"
                    onclick="CambiarEntregado()">Aceptar</button>
            </div>
        </div>
    </div>
</div>


</div>



<div class="modal fade" id="ModalActivarChip" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Vincular Tarjeta RFID</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="ACidinsripcion" id="ACidinsripcion">
                    <input type="hidden" name="ACidentificacion" id="ACidentificacion">

                    <section style="text-align:center; padding: 20px;">
                        <i class="fa fa-id-card-o" style="font-size: 50px; color: #003366; margin-bottom: 15px;"></i>
                        <p class="mensajeconfirm" style="font-size: 16px;">
                            Pase la tarjeta física por el lector 125KHz para vincularla a este estudiante.
                        </p>
                        <!-- Campo oculto para atrapar el escaneo (el lector actúa como teclado) -->
                        <input type="text" id="txtActivarChipEscaneo" class="form-control text-center"
                            style="margin-top: 15px; font-weight: bold; letter-spacing: 2px;"
                            placeholder="Esperando escaneo..." autocomplete="off">
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <!-- Se podría usar un submit automático al escuchar el enter, pero dejamos el botón por si acaso -->
                <button type="button" class="btn btn-primary" id="btn-activar-chip"
                    onclick="GuardarActivacionChip()">Vincular Chip</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="ModalEntregaCarnet" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Confirmar</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="ECidinsripcion" id="ECidinsripcion">

                    <section>
                        <p class="mensajeconfirm" style="text-align:center">¿Desea cambiar el estado del carnet a
                            entregado.? <br> El estudiante debe firmar formato de entregar como recibido</p>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmar-ok"
                    onclick="EntregaCarnet()">Aceptar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>


<div class="modal fade" id="ModalEditarInscipcion" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Editar Registro</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="Editar-Inscripcion" method="POST">

                        <div class="input-item  col-md-6 col-xs-12">
                            <label for="RegistroIdentificacion">Identificación:
                                <span class="required">*</span>
                            </label>
                            <input type="hidden" name="txtidinscripcion" id="txtidinscripcion">
                            <input class="form-control input-sm" type="number" id="txtidentificacion"
                                name="txtidentificacion" autocomplete="off" maxlength="10"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                data-toggle="tooltip" title="Ingrese número de identidad sin singos puntos o comas"
                                autofocus="" />
                        </div>

                        <div class="input-item col-md-6 col-xs-12">
                            <label for="RegistroTipoIdentificacion">Tipo Identificación:
                                <span class="required">*</span>
                            </label>
                            <select class="select form-control input-sm" id="txttipoidentificacion"
                                name="txttipoidentificacion" data-toggle="tooltip" title="Seleccione tipo de identidad">
                            </select>
                        </div>

                        <div class="input-item  col-md-6 col-xs-12">
                            <label for="RegistroNombreEstudiante">Nombre:
                                <span class="required">*</span>
                            </label>
                            <input class="form-control input-sm text-uppercase" type="text" id="txtnombre"
                                name="txtnombre" autocomplete="off" data-toggle="tooltip" title="Ingrese nombre" />
                        </div>

                        <div class="input-item col-md-6 col-xs-12">
                            <label for="RegistroApellidoEstudiante">Apellido:
                                <span class="required">*</span>
                            </label>
                            <input class="form-control input-sm text-uppercase" type="text" id="txtapellido"
                                name="txtapellido" autocomplete="off" data-toggle="tooltip" title="Ingrese apellidos" />
                        </div>

                        <div class="input-item  col-md-6 col-xs-12">
                            <label for="RegistroEmailEstudiante">Email:
                                <span class="required">*</span>
                            </label>
                            <input class="form-control input-sm input-sm text-uppercase" type="email" id="txtemail"
                                name="txtemail" autocomplete="off" data-toggle="tooltip"
                                title="ingrese email valido, recibirás notificaciones por este medio">
                        </div>

                        <div class="input-item  col-md-6  col-xs-12">
                            <label for="RegistroCelular">Celular: </label>
                            <input class="form-control input-sm input-sm text-uppercase" type="text" id="txtcelular"
                                name="txtcelular" autocomplete="off" data-inputmask="'mask': ['999-999-99-99']"
                                data-mask data-toggle="tooltip" title="Ingrese número de celular">
                        </div>


                        <div class="input-item col-md-6 col-xs-12">
                            <label for="RegistroTipoSangre">Tipo Sangre:
                                <span class="required">*</span>
                            </label>
                            <select class="select form-control input-sm" id="txttiposangre" name="txttiposangre"
                                data-toggle="tooltip" title="Seleccione">
                                <option value="">Seleccione..</option>
                                <option value="RH O+">RH O+</option>
                                <option value="RH O-">RH O-</option>
                                <option value="RH A+">RH A+</option>
                                <option value="RH A-">RH A-</option>
                                <option value="RH AB+">RH AB+</option>
                                <option value="RH AB-">RH AB-</option>
                                <option value="RH B+">RH B+</option>
                                <option value="RH B-">RH B-</option>
                                <option value="SIN INFORMACION">SIN INFORMACION</option>
                            </select>
                        </div>


                        <div class="input-item col-md-6 col-xs-12">
                            <label for="RegistroLote">Asignar al Lote:
                                <span class="required">*</span>
                            </label>
                            <select class="select form-control input-sm" id="txtlote" name="txtlote"
                                data-toggle="tooltip" title="Seleccione lote">
                                <option value="">Seleccione..</option>
                            </select>
                        </div>
                        <div class="input-item col-md-12 col-xs-12" style="margin-top: 15px;">
                            <label>Fotografía (Opcional - Actualizar en tiempos muertos):</label>
                            <div style="display:flex; justify-content:left; gap: 10px; margin-bottom: 10px;">
                                <button type="button" class="btn btn-sm btn-info" id="btn-iniciar-camara-edit"><i class="fa fa-camera"></i> Iniciar Cámara</button>
                                <button type="button" class="btn btn-sm btn-success" id="btn-capturar-foto-edit" style="display:none;"><i class="fa fa-picture-o"></i> Capturar Nueva</button>
                            </div>
                            <div style="display:flex; justify-content:left; align-items:center;">
                                <div class="contenedor-canvas" style="display:none; width:150px; height:150px; border-radius:50%;" id="contenedor-camara-edit">
                                    <video id="video-camara-edit" autoplay playsinline style="width:100%; height:100%; object-fit:cover; transform: scaleX(-1);"></video>
                                    <div style="position: absolute; width: 100%; height: 100%; top:0; left:0; border: 2px dashed rgba(255,255,255,0.7); border-radius: 50%; pointer-events:none;"></div>
                                </div>
                                <div class="contenedor-canvas" style="display:none; width:150px; height:150px; border-radius:50%;" id="contenedor-foto-edit">
                                    <canvas id="canvas-foto-edit" style="width:100%; height:100%; object-fit:cover; transform: scaleX(-1);"></canvas>
                                    <div style="position: absolute; width: 100%; height: 100%; top:0; left:0; border: 2px solid #337ab7; border-radius: 50%; pointer-events:none;"></div>
                                </div>
                            </div>
                        </div>


                        <div class="input-item col-md-12 col-xs-12">
                            <label for="RegistroNovedad">Observación Novedad:
                                <span class="required">*</span>
                            </label>
                            <select class="select form-control input-sm" id="txtnovedad" name="txtnovedad"
                                data-toggle="tooltip" title="Seleccione novedad">
                                <option value="">Seleccione..</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmar-ok"
                    onclick="EditarInscripcion()">Aceptar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>



<div class="modal fade" id="ModalRelizarCambios" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Realizar cambio carnet</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="Realizar-Cambios" method="POST">
                        <input type="hidden" name="RCidinscripcion" id="RCidinscripcion">

                        <div class="input-item  col-md-12 col-xs-12">
                            <label for="Observacion">Observación:
                                <span class="required">*</span>
                            </label>
                            <textarea rows="5" class="form-control input-sm text-uppercase" id="Cobservacion"
                                name="Cobservacion" data-toggle="tooltip" title="Observación"></textarea>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmar-ok"
                    onclick="RealizarCambios()">Aceptar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>



<div class="modal fade" id="ModalCambiarLote" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content modal-sm">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Cambiar de lote</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="Cambiar-Lote" method="POST">
                        <input type="hidden" name="txtclidinscripcion" id="txtclidinscripcion">
                        <div class="input-item  col-md-12 col-xs-12">
                            <label for="txtlote">Lote:
                                <span class="required">*</span>
                            </label>
                            <select class="select form-control input-sm" id="txtclote" name="txtclote"
                                data-toggle="tooltip" title="Seleccione..">
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmar-ok" onclick="CambiarLote()">Aceptar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>




<div class="modal fade" id="ModalCambiarPrograma" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content ">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Cambiar programa o Categoria</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="Cambiar-Programa" method="POST">
                        <input type="hidden" name="txtPidinscripcion" id="txtPidinscripcion">

                        <div class="input-item  col-md-6 col-xs-12">
                            <label>Sede:
                                <span class="required">*</span>
                            </label>
                            <span class="form-control" id="txtnsede"></span>
                        </div>

                        <div class="input-item  col-md-6 col-xs-12">
                            <label>Categoria:
                                <span class="required">*</span>
                            </label>
                            <span class="form-control" id="txtncategoria"></span>
                        </div>

                        <div class="input-item  col-md-12 col-xs-12">
                            <label for="txtprograma">Programa:
                                <span class="required">*</span>
                            </label>
                            <span class="form-control" id="txtnprograma"></span>
                        </div>

                        <div class="input-item  col-md-12 col-xs-12">
                            <label for="txtcategoria">Categoria:
                                <span class="required">*</span>
                            </label>
                            <select class="select form-control input-sm" id="txtpcategoria" name="txtpcategoria"
                                title="Categoria" data-toggle="tooltip" title="Seleccione..">
                                <option value="">Seleccione..</option>
                                <option value="1">ESTUDIANTE</option>
                                <!--<option value="2">FUNCIONARIO</option> -->
                                <option value="3">PRACTICANTE</option>
                            </select>
                        </div>

                        <div class="input-item  col-md-12 col-xs-12">
                            <label><span id="txtsede">Sede:</span>
                                <span class="required">*</span>
                            </label>
                            <select class="select form-control input-sm" id="txtpsede" name="txtpsede" title="Sede"
                                data-toggle="tooltip" title="Seleccione..">
                            </select>
                        </div>


                        <div class="input-item  col-md-12 col-xs-12">
                            <label for="txtprograma"><span id="LblEprograma">Programa:</span><span
                                    class="required">*</span> <a id="btn_agregar_programa_cargo__"
                                    onclick="modalPrograma()"><small><small>Agregar nuevo</small></small></a></label>
                            <select class="select form-control input-sm" id="txtpprograma" name="txtpprograma"
                                title="Programa" data-toggle="tooltip" title="Seleccione.."></select>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmar-ok"
                    onclick="CambiarPrograma()">Aceptar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>

<div class="modal fade" id="modalNUevoCargo" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Nuevo Cargo</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="Registrar-Cargos" method="POST">

                        <div class="input-item col-xs-12">
                            <label for="RnombreCargo">Nombre Cargo
                                <span class="required">*</span>
                            </label>
                            <input class="form-control" type="text" id="RnombreCargo" name="RnombreCargo"
                                autocomplete="off" data-toggle="tooltip" title="" spellcheck="false"
                                data-ms-editor="true" data-original-title="Nombre del cargo">
                        </div>



                        <div class="input-item col-xs-12">
                            <label for="RSelectSedePrograma">Sede:
                                <span class="required">*</span>
                            </label>
                            <select class="form-control" id="RSelectSedeCargo" name="RSelectSedeCargo"
                                data-toggle="tooltip" title="" data-original-title="Seleccione sede">
                                <option value="">Seleccione una opción.</option>
                                <option value="1">PRINCIPAL</option>
                                <option value="2">SOLEDAD </option>
                                <option value="3">PUERTO COLOMBIA</option>
                                <option value="4">COSMOS</option>
                                <option value="5">SITIO NUEVO</option>
                            </select>
                        </div>

                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardarNuevo" name="btn-guardarNuevo"
                    onclick="RegistrarCargo();">Guardar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>

<div class="modal fade" id="modalNUevoPrograma" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Nuevo Programa</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="Registrar-Programas" method="POST">

                        <div class="input-item col-xs-12">
                            <label for="RnombrePrograma">Nombre Programa
                                <span class="required">*</span>
                            </label>
                            <input class="form-control" type="text" id="RnombrePrograma" name="RnombrePrograma"
                                autocomplete="off" data-toggle="tooltip" title="" spellcheck="false"
                                data-ms-editor="true" data-original-title="Nombre del programa académico">
                        </div>

                        <div class="input-item col-xs-6">
                            <label for="RcodigoPrograma">Código Programa
                                <span class="required">*</span>
                            </label>
                            <input class="form-control" type="text" id="RcodigoPrograma" name="RcodigoPrograma"
                                autocomplete="off" data-toggle="tooltip" title="" spellcheck="false"
                                data-ms-editor="true" data-original-title="Código del programa académico">
                        </div>


                        <div class="input-item col-xs-6">
                            <label for="RSelectSedePrograma">Sede:
                                <span class="required">*</span>
                            </label>
                            <select class="form-control" id="RSelectSedePrograma" name="RSelectSedePrograma"
                                data-toggle="tooltip" title="" data-original-title="Seleccione sede">
                                <option value="">Seleccione una opción.</option>
                                <option value="1">PRINCIPAL</option>
                                <option value="2">SOLEDAD </option>
                                <option value="3">PUERTO COLOMBIA</option>
                                <option value="4">COSMOS</option>
                                <option value="5">SITIO NUEVO</option>
                            </select>
                        </div>

                        <div class="input-item col-xs-12">
                            <label for="RestadoPrograma">Estado:
                                <span class="required">*</span>
                            </label>
                            <select class="form-control" id="RestadoPrograma" name="RestadoPrograma">
                                <option value="">Seleccione </option>
                                <option value="on">Habilitado</option>
                                <option value="off">Deshabilitado</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardarNuevo" name="btn-guardarNuevo"
                    onclick="RegistrarInfoProgramas();">Guardar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>

<div class="modal fade" id="modal_registrar_cargo_practicante"><!-- registrar-cargos -->
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Nuevo Cargo</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="form_registrar_cargo_practicante" method="POST"><!-- Registrar-Cargos -->
                        <div class="input-item col-xs-12">
                            <label>Nombre Cargo <span class="required">*</span></label>
                            <input class="form-control" type="text" id="RCargoPracticante" name="RCargoPracticante"
                                autocomplete="off" data-toggle="tooltip" title="Nombre del cargo"><!-- RnombreCargo -->
                        </div>
                        <div class="input-item col-xs-12">
                            <label>Empresa: <span class="required">*</span></label>
                            <select class="form-control" id="REmpresaPracticante" name="REmpresaPracticante"
                                data-toggle="tooltip" title="Seleccione empresa"></select><!-- RSelectSedeCargo -->
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary"
                    onclick="registrarCargoPracticante();">Guardar</button><!-- RegistrarCargo -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCropper" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Recortar Foto</h4>
            </div>
            <div class="modal-body text-center">
                <div style="max-width: 100%; max-height: 400px; display: inline-block;">
                    <img id="image-to-crop" style="max-width: 100%; display: block;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarRecorte()">Recortar y Aplicar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalGestionCarnetSige" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Estado y operación de carnet SIGE</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="sige-id-inscripcion">
                <p><b>Identificación:</b> <span id="sige-documento"></span></p>
                <div id="sige-estado-actual" class="alert alert-info">Consultando…</div>
                <div id="sige-orden-pendiente" class="alert alert-warning" style="display:none"></div>
                <div class="form-group">
                    <label>Operación</label>
                    <select id="sige-tipo-operacion" class="form-control" onchange="actualizarFormularioSige()">
                        <option value="ASIGNACION">Asignar primer carnet</option>
                        <option value="REEMPLAZO">Reemplazar carnet</option>
                        <option value="BLOQUEO">Bloquear carnet</option>
                        <option value="REACTIVACION_AUTORIZADA">Reactivar carnet</option>
                    </select>
                </div>
                <div id="sige-grupo-uid" class="form-group">
                    <label>Nuevo código RFID</label>
                    <input type="text" id="sige-uid-rfid" class="form-control" autocomplete="off"
                           placeholder="Ubique el cursor aquí y lea el carnet físico">
                </div>
                <div id="sige-grupo-motivo" class="form-group">
                    <label>Motivo</label>
                    <select id="sige-motivo" class="form-control">
                        <option value="PERDIDA">Pérdida</option>
                        <option value="ROBO">Robo</option>
                        <option value="DETERIORO">Deterioro</option>
                        <option value="CAMBIO_ROL">Cambio de rol</option>
                        <option value="BLOQUEO_OPERATIVO">Bloqueo operativo</option>
                    </select>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" id="sige-confirmado"> Confirmo la verificación externa y autorizo registrar esta operación.</label>
                </div>
                <div class="alert alert-danger" id="sige-error" style="display:none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" id="sige-btn-enviar" class="btn btn-primary" onclick="crearOrdenCarnetSige()">Confirmar operación</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Renovación de Carnet Estudiantil (Mismo Plástico) -->
<div class="modal fade" id="ModalRenovacionSige" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success" style="color: #fff; background-color: #27ae60;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.9;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-refresh"></i> Renovación de Carnet Estudiantil (Mismo Plástico)</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="renovacion-id-inscripcion">
                <input type="hidden" id="renovacion-uid-rfid">
                
                <div class="panel panel-default">
                    <div class="panel-body">
                        <p><b>Documento:</b> <span id="renovacion-documento">---</span></p>
                        <p><b>Estudiante:</b> <span id="renovacion-nombre-estudiante">---</span></p>
                        <p><b>UID RFID Actual:</b> <span id="renovacion-uid-display" class="label label-info">---</span></p>
                        <p><b>Fecha Vencimiento Anterior:</b> <span id="renovacion-fecha-vencimiento" style="font-weight: bold;">---</span></p>
                        <p><b>Nueva Fecha Vigencia Estimada:</b> <span id="renovacion-fecha-nueva" class="text-success" style="font-weight: bold; font-size: 15px;">---</span> (+6 meses)</p>
                    </div>
                </div>

                <div id="renovacion-orden-pendiente" class="alert alert-warning" style="display:none"></div>
                
                <div class="checkbox">
                    <label style="font-weight: bold;">
                        <input type="checkbox" id="renovacion-confirmado"> Confirmo que el estudiante conserva su carné físico en buen estado y se autoriza prorrogar su vigencia.
                    </label>
                </div>
                <div class="alert alert-danger" id="renovacion-error" style="display:none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" id="renovacion-btn-enviar" class="btn btn-success" onclick="confirmarRenovacionSige()">Confirmar Renovación</button>
            </div>
        </div>
    </div>
</div>

<script src="plugins/cropper/cropper.min.js"></script>
<script src="javascripts/gestionarinscripcion.js?v=<?php echo time(); ?>"></script>
<script src="https://raw.githack.com/eKoopmans/html2pdf/master/dist/html2pdf.bundle.js"></script>