<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once "developer/Config/personal_ui_config.php";
require_once "developer/Services/PersonalCatalogAuthorization.php";

if (!TIC_SIGE_PERSONAL_UI_ENABLED) {
    echo "<h1>El módulo de personal se encuentra deshabilitado.</h1>";
    exit;
}

try {
    $authorization = new PersonalCatalogAuthorization();
    $actor = $authorization->autorizar($_SESSION);
} catch (Exception $e) {
    http_response_code(403);
    echo '<div class="alert alert-danger">No fue posible validar el acceso.</div>';
    exit;
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Gestión de Carnés <small>Personal Docente y Administrativo</small>
        </h1>
    </section>
    
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <strong>Información:</strong> Aquí se listan únicamente las personas cuya elegibilidad fue confirmada y proyectada por SIGE. Utilice esta pantalla para asignar, bloquear o imprimir carnés.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Listado de Personal Elegible</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="tablaCarnetsPersonal">
                                <thead>
                                    <tr>
                                        <th style="width: 50px; text-align: center;">Foto</th>
                                        <th>Documento</th>
                                        <th>Nombres y Apellidos</th>
                                        <th>Vínculos</th>
                                        <th>Estado Institucional</th>
                                        <th>Estado Carné</th>
                                        <th>UID RFID</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Cargado por AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Asignar/Reemplazar Chip -->
<div class="modal fade" id="ModalAsignarChip" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title" id="tituloModalChip">Asignar Chip RFID</h4>
            </div>
            <div class="modal-body text-center">
                <p>Por favor, pase el carné por el lector RFID:</p>
                <div class="form-group">
                    <input type="text" id="txtAsignarChipRfid" class="form-control text-center" style="font-size: 20px;" autocomplete="off" placeholder="Escaneando...">
                </div>
                <input type="hidden" id="hdnPersonaUuid">
                <input type="hidden" id="hdnPersonaVersion">
                <input type="hidden" id="hdnChipAccion"> <!-- ASIGNACION o REEMPLAZO -->
                <div id="divVigenciaHasta" style="text-align: left; margin-top: 10px;">
                    <label>Vigencia de Acceso / Fecha Fin Convenio (Opcional):</label>
                    <input type="date" id="txtVigenciaHasta" class="form-control">
                </div>
                <div id="chipReemplazoMotivoDiv" style="display:none; text-align: left; margin-top: 15px;">
                    <label>Motivo del reemplazo:</label>
                    <select id="selReemplazoMotivo" class="form-control">
                        <option value="PERDIDA">Pérdida</option>
                        <option value="ROBO">Robo</option>
                        <option value="DETERIORO">Deterioro</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bloquear Chip -->
<div class="modal fade" id="ModalBloquearChip" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title">Bloquear Carné</h4>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea vetar permanentemente el carné actual?</p>
                <div class="form-group">
                    <label>Motivo:</label>
                    <select id="selBloqueoMotivo" class="form-control">
                        <option value="PERDIDA">Pérdida</option>
                        <option value="ROBO">Robo</option>
                        <option value="DETERIORO">Deterioro</option>
                        <option value="BLOQUEO_OPERATIVO">Bloqueo Operativo</option>
                    </select>
                </div>
                <input type="hidden" id="hdnBloqueoPersonaUuid">
                <input type="hidden" id="hdnBloqueoPersonaVersion">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarBloqueo">Bloquear Definitivamente</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Prorrogar Convenio Practicante (Requerimiento E) -->
<div class="modal fade" id="ModalProrrogarConvenio" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title">Prorrogar / Extender Convenio</h4>
            </div>
            <div class="modal-body">
                <p id="lblProrrogarPersonaNombre" style="font-weight: bold;"></p>
                <div class="form-group">
                    <label>Nueva Fecha de Finalización de Convenio:</label>
                    <input type="date" id="txtProrrogarVigenciaHasta" class="form-control" required>
                </div>
                <input type="hidden" id="hdnProrrogarPersonaUuid">
                <input type="hidden" id="hdnProrrogarPersonaVersion">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarProrroga">Prorrogar Acceso</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.csrfToken = "<?= PersonalCatalogAuthorization::asegurarToken($_SESSION) ?>";
    window.templatesReady = <?= (TIC_SIGE_PERSONAL_CARNET_TEMPLATES_READY || (file_exists('plantillas/front_personal.png') && file_exists('plantillas/back_personal.jpeg'))) ? 'true' : 'false' ?>;
</script>
<script src="javascripts/gestionarcarnetpersonal.js?v=<?= time() ?>"></script>
