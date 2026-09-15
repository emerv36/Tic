<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="row">
                        <div class="col-md-11">
                            <div class="box-header">
                                <h1 class="box-title"><B>Gestionar cargos practicante</B></h1>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div id="regisprog" class="tab-pane fade in active">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 18px; padding: 12px 24px;">
                            <div class="input-group input-group-sm hidden-xs" style="width: 250px;">
                            </div>
                            <button type="button" class="btn btn-primary" data-toggle="modal" onclick="ModalRegistroCargosPracticate();">Nuevo Cargo</button><!-- ModalRegistroCargos -->
                        </div>
                        <div class="box-body">
                            <table class="table table-hover table-striped table-condesed table-sm" id="tbl_cargos_practicante" width="100%"><!-- tbl_cargos -->
                                <thead class="titulo">
                                    <tr>
                                        <th>No.</th>
                                        <th>Cargo</th>
                                        <th>Empresa</th>
                                        <th>Estado</th>
                                        <th style="width: 8%">Acciones</th>
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
    </section>
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
                            <input class="form-control" type="text" id="RCargoPracticante" name="RCargoPracticante" autocomplete="off" data-toggle="tooltip" title="Nombre del cargo"><!-- RnombreCargo -->
                        </div>
                        <div class="input-item col-xs-12">
                            <label>Empresa: <span class="required">*</span></label>
                            <select class="form-control" id="REmpresaPracticante" name="REmpresaPracticante" data-toggle="tooltip" title="Seleccione empresa"></select><!-- RSelectSedeCargo -->
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="registrarCargoPracticante();">Guardar</button><!-- RegistrarCargo -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_editar_cargo_practicante"><!-- editar-cargos -->
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Editar Cargo</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="form_editar_cargo_practicante" method="POST"> <!-- Editar-Cargos -->
                        <input type="hidden" name="idCargoPracticante" id="idCargoPracticante" value=""><!-- txtIdcargo -->
                        <div class="input-item col-xs-12">
                            <label>Nombre Cargo <span class="required">*</span></label>
                            <input class="form-control" type="text" id="ECargoPracticante" name="ECargoPracticante"><!-- txtnombreCargo -->
                        </div>
                        <div class="input-item col-xs-12">
                            <label>Sede <span class="required">*</span></label>
                            <select class="form-control" id="EEmpresaPracticante" name="EEmpresaPracticante"></select><!-- txtSelectSedeCargo -->
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="editarCargoPracticante();">Guardar</button><!-- EditarCargos -->
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- Modal editar estado programas-->
<div class="modal fade" id="modaCambiarEstado"><!-- modalconfirmar -->
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Confirmar</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <section>
                        <p class="mensajeConfirmacion" style="text-align:center;">¿Está seguro de deshabilitar este Cargo?</p><!-- .mensajeconfirm -->
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn_estado" onclick="confirmarCambiarEstado();">Aceptar</button><!-- corfirmar -->
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>

<!-- fin  Modal crear pensum o planes de estudios por programas academicos en sedes -->
<script src="javascripts/gestionarcargospracticante.js?v=fijdshfisfihihsihdsighsdifhoasfoajhfoiahfihafuhafihfijafiahfiahijhfijahbfiahbfiahfiajhfijhgv"></script>