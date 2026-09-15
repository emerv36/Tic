<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="row">
                        <div class="col-md-11">
                            <div class="box-header">
                                <h1 class="box-title"><B>Gestionar cargos</B></h1>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-header -->
                        <div id="regisprog" class="tab-pane fade in active">
                            <div
                                style="display: flex; justify-content: space-between; margin-bottom: 18px; padding: 12px 24px;">
                                <div class="input-group input-group-sm hidden-xs" style="width: 250px;">
                                </div>
                                <button type="button" class="btn btn-primary" data-toggle="modal" onclick="ModalRegistroCargos();">Nuevo Cargo</button>
                            </div>
                            <div class="box-body">
                                <table class="table table-hover table-striped table-condesed table-sm" id="tbl_cargos" width="100%">
                                    <thead class="titulo">
                                        <tr>
                                            <th>No.</th>
                                            <th>Cargo</th>
                                            <th>Sede</th>
                                            <th>Estado</th>
                                            <th style="width: 8%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody >
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    <!-- /.box -->
                    <!-- /.box -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
    </section>
    <!-- /.content -->
</div>


<div class="modal fade" id="registrar-cargos">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Nuevo Cargo</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="Registrar-Cargos" method="POST">
                    
                        <div class="input-item col-xs-12">
                            <label for="RnombreCargo">Nombre Cargo
                                <span class="required">*</span>
                           </label>
                            <input class="form-control" type="text" id="RnombreCargo" name="RnombreCargo" autocomplete="off" data-toggle="tooltip" title="Nombre del cargo">
                        </div>

       

                        <div class="input-item col-xs-12">
                            <label for="RSelectSedePrograma">Sede:
                                <span class="required">*</span>
                            </label>
                            <select class="form-control" id="RSelectSedeCargo" name="RSelectSedeCargo" data-toggle="tooltip" title="Seleccione sede" >
                            </select>
                        </div>
                   
                   </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardarNuevo" name="btn-guardarNuevo" onclick="RegistrarCargo();">Guardar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="editar-cargos">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Editar Cargo</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="Editar-Cargos" method="POST">
                    <input type="hidden" name="txtIdcargo" id="txtIdcargo" value="">
                        <div class="input-item col-xs-12">
                            <label for="txtnombreCargo">Nombre Cargo
                                <span class="required">*</span>
                            </label>
                          <input class="form-control" type="text" id="txtnombreCargo" name="txtnombreCargo">
                        </div>

                    
                         <div class="input-item col-xs-12">
                            <label for="txtSelectSedecargo">Sede
                                <span class="required">*</span>
                            </label>
                        <select class="form-control" id="txtSelectSedeCargo" name="txtSelectSedeCargo">
                            </select>
                        </div>

                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="EditarCargos();">Guardar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- Modal editar estado programas-->
<div class="modal fade" id="modalconfirmar">
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
                        <p class="mensajeconfirm" style="text-align:center;">
                            ¿Está seguro de deshabilitar este Cargo?</p>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn-confirm-ok" class="btn btn-primary" onclick="corfirmar();">Aceptar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>

<!-- fin  Modal crear pensum o planes de estudios por programas academicos en sedes -->
<script src="javascripts/gestionarprogramas.js"></script>