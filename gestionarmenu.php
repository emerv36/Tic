<div class="content-wrapper">
    <section class="content">
        <div class="row">
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="row">
                        <div class="col-md-11">

                            <div class="box-header">
                                <h3 class="box-title"><B>Listado de Menús:</B></h3>
                            </div>
                        </div>

                       </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div
                            style="display: flex; justify-content: space-between; margin-bottom: 18px; padding: 12px 24px;">
                            <div class="input-group input-group-sm hidden-xs" style="width: 250px;">
                            </div>
                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                data-target="#modal-menuNew" onclick="fmodalNuevo();">
                                <B>Nuevo Menú</B>
                            </button>
                        </div>
                        <table id="tbl_menu" class="table table-hover">
                            <thead class="titulo">
                                <tr>
                                    <th>No.</th>
                                    <th>Icono</th>
                                    <th>Menú</th>
                                    <th>Nivel</th>
                                    <th>Orden</th>
                                    <th>Padre</th>
                                    <th>Link</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
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
</div>
<!--modal editar-->
<div class="modal fade" id="modal-menuNew">
    <div class="modal-dialog" style="width:40vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><B>Nuevo Menu</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="frmNuevo">
                        <div class="input-item col-xs-12" style="text-align: left;">
                            <label for="ntxtIcono">Icono
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="text" id="ntxtIcono" name="ntxtIcono">
                        </div>
                        <div class="input-item col-xs-12" style="text-align: left;">
                            <label for="ntxtNombreMenu">Nombre Menu
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="text" id="ntxtNombreMenu" name="ntxtNombreMenu">
                        </div>
                        <div class="input-item col-xs-12" style="text-align: left;">
                            <label for="nnivel">Nivel
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <select onchange="change(this.value)" class="form-control" id="nnivel" name="nnivel">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                        <div class="bdivpadre" style="display: none;">
                            <div class="input-item col-xs-12" style="text-align: left;">
                                <label for="npadre">Padre:
                                    <span class="required">*</span>
                                </label>
                            </div>
                            <div class="input-item col-xs-12" style="">
                                <select class="form-control" id="npadre" name="npadre">
                                </select>
                            </div>
                        </div>
                        <div class="input-item col-xs-12" style="text-align: left;">
                            <label for="ntxtLink">Link
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="text" id="ntxtLink" name="ntxtLink">
                        </div>
                        <div class="input-item col-xs-12" style="text-align: left;">
                            <label for="nestado">Estado:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <select class="form-control" id="nestado" name="nestado">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="on">Habilitado</option>
                                <option value="off">Deshabilitado</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="fNuevoItem();">Registrar Información</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-menuEdit">
    <div class="modal-dialog" style="width:40vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Editar Menú<B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="frmEdit" method="POST">
                        <div class="input-item col-xs-12">
                            <input type="hidden" name="txtCodmenu" id="txtCodmenu">
                            <label for="txtIcono">Icono
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="text" id="txtIcono" name="txtIcono">
                        </div>
                        <div class="input-item col-xs-12">
                            <div class="input item">
                                <label for="txtNombreMenu">Nombre Menu
                                    <span class="required">*</span>
                                </label>
                            </div>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="text" id="txtNombreMenu" name="txtNombreMenu">
                        </div>
                        <div class="input-item col-xs-12">
                            <div class="input-item">
                                <label for="txtLink">Link
                                    <span class="required">*</span>
                                </label>
                            </div>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="text" id="txtLink" name="txtLink">
                        </div>
                        <div class="input-item col-xs-12">
                            <div class="input-item">
                                <label for="estado">Estado:
                                    <span class="required">*</span>
                                </label>
                            </div>
                        </div>
                        <div class="input-item col-xs-12">
                            <div class="input-item">
                                <select class="form-control" id="estado" name="estado">
                                    <option value="" disabled selected>Seleccione</option>
                                    <option value="on">Habilitado</option>
                                    <option value="off">Deshabilitado</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="fEditarItem();">Actualizar Información</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>
<!--fin modal editar-->

<!-- Modal para el manual degestion de Menús-->
<script src="javascripts/gestionarmenu.js"></script>