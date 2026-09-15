<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="row">
                        <div class="col-md-11">
                            <div class="box-header">
                                <h1 class="box-title"><B>Gestión de Roles:</B></h1>
                            </div>
                        </div>

                    </div>
                    <!-- /.box-header -->
                    <div class="box-tools">
                        <div
                            style="display: flex; justify-content: space-between; margin-bottom: 18px; padding: 12px 24px;">
                            <div class="input-group input-group-sm hidden-xs" style="width: 250px;">
                            </div>
                        </div>
                        <div class="box-body">
                            <table id="tbl_roles" class="table table-striped table-hover" width="100%">

                                <!-- /modal editar estado-->
                                <thead class="titulo">
                                    <tr>
                                        <th>No.</th>
                                        <th>Nombre rol</th>
                                        <th>Estado</th>
                                        <th style="width: 10%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                       
                            </table>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                    <!-- /.box -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
    </section>
    <!-- /.content -->
</div>
<div class="modal fade" id="modal-rolEdit">
    <div class="modal-dialog" style="width:40vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Editar Roles</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="frmEdit" method="POST">
                        <div class="input-item col-xs-12">
                            <label for="txtNombreRol">Nombre Rol
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input type="hidden" name="txtCodrol" id="txtCodrol" />
                            <input class="form-control" type="text" id="txtNombreRol" name="txtNombreRol">
                        </div>
                        <div class="input-item col-xs-12">
                            <label for="nestado">Estado:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <select class="form-control" id="nestado" name="nestado">
                                <option value="" disabled selected>Seleccione
                                </option>
                                <option value="on">Habilitado</option>
                                <option value="off">Deshabilitado</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="fEditarItem();">Actualizar Información</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- Modal editar estado -->
<div class="modal fade" id="dialog-confirm">
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
                        <p class="mensajeconfirm" style="text-align:center;">¿Está seguro de deshabilitar
                            este
                            Rol?</p>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn-confirm-ok" class="btn btn-primary" onclick="acceptConfirm()">OK</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>


<!-- Modal para el manual de asignar menu>perfiles-->
<script src="javascripts/gestionarroles.js"></script>