<div class="content-wrapper">
    <div class="col-xs-12">
        <div class="box">
            <div class="row">
                <div class="col-md-11">
                    <div class="box-header">
                        <h1 class="box-title"><B>Gestionar versión  pensúm:</B></h1>
                    </div>
                </div>

               </div>
            <div class="box-body">

                <div style="display: flex; justify-content: space-between; margin-bottom: 18px; padding: 12px 24px;">
                    <div class="input-group input-group-sm hidden-xs" style="width: 250px;">
                    </div>
                    <button type="button" class="btn btn-primary" data-toggle="modal" onclick="ModalRegistrarVersion();">
                        Nueva Versión
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive ">

                <table class="table table-hover table-striped table-condesed" id="tbl_version" width="100%">

                    <thead class="titulo">
                        <tr>
                            <th>No.</th>
                            <th>Versión Pensúm</th>
                            <th>Estado</th>
                            <th style="width: 10%">Acciones</th> 
                    </thead>
                    <tbody>
                    </tbody>
        
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.content -->
</div>

<div class="modal fade" id="editar-version">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Editar Versión</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="Editar-Version" method="POST">
                        <input class="form-control" type="hidden" id="id_version" name="id_version">
                    
                        <div class="input-item col-xs-12">
                            <label for="txtnombreversion">Nombre Versión
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="text" id="nombre_version" name="nombre_version">
                        </div>
                           <div class="input-item col-xs-12">
                            <label for="estado_version">Estado:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <select class="form-control" id="estado_version" name="estado_version">
                                <option value="">Seleccione  </option>
                                <option value="on">Habilitado</option>
                                <option value="off">Deshabilitado</option>
                            </select>
                        </div>
                     </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="EditarVersion();">Guardar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>
<!--fin modal editar perfil -->
<div class="modal fade" id="registrar-version">
    <div class="modal-dialog">
        <div class="modal-content modal-sm">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Nueva Versión</B></h4>
            </div>
            <!-- modal registrar perfil-->
            <div class="modal-body">
                <div class="row">
                    <form id="Registrar-Version" method="POST">
                  
                        <div class="input-item col-xs-12">
                            <label for="txtRegistraVersion">Nombre Versión:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="text" id="Rnombre_version" name="Rnombre_version">
                        </div>

                        <div class="input-item col-xs-12">
                            <label for="Restadoversion">Estado:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <select class="form-control" id="Restado_version" name="Restado_version">
                                <option value="">Seleccione  </option>
                                <option value="on">Habilitado</option>
                                <option value="off">Deshabilitado</option>
                            </select>
                        </div>
                                      
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="RegistrarVersion();">Guardar</button>
            </div>
        </div>
    </div>
</div>
<!--modal para editar el estado de la inscripción del estudiante -->
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
                        <p class="mensajeconfirm" style="text-align:center"><B>¿Está seguro de deshabilitar
                                esta
                                Sede?</B></p>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmar-ok" onclick="acceptConfirm()">OK</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>
<!--fin modal para editar el estado de la inscripción del estudiante -->

<script src="javascripts/gestionarversion.js"></script>