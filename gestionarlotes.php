<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="row">
                        <div class="col-md-11">
                            <div class="box-header">
                                <h1 class="box-title"><B>Lotes Carnetización</B></h1>
                            </div>
                        </div>
               
                    </div>
                    <div class="box-tools">
                        <div
                            style="display: flex; justify-content: space-between; margin-bottom: 18px; padding: 12px 24px;">
                            <div class="input-group input-group-sm hidden-xs" style="width: 250px;">
                            </div>
                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                onclick="ModalRegistroLote();">
                                Nuevo Registro
                            </button>
                        </div>
                        <div class="box-body">
                            <table class="table table-hover table-striped table-condesed" id="tbl_lotes" width="100%">
                                <thead class="titulo">
                                    <tr>
                                        <th>No.</th>
                                        <th>Código lote</th>
                                        <th>Etapa</th>
                                        <th>Creado Por</th>
                                        <th>Fecha Creación</th>
                                        <th>Estado</th>
                                        <th style="width: 110px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                      </div>
                  </div>
             </div>
    </section>
</div>

<div class="modal fade" id="registrar-lotes">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Nuevo Lote</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="Registrar-Lotes" method="POST">
                  
                    <div class="input-item col-xs-12">
                            <label for="Rcodigo">Código lote:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input style="text-transform:uppercase;" name="Rcodigolote" type="text" class="form-control" id="Rcodigolote" required="" autocomplete="off" data-inputmask="'mask': ['aaa-99-9999']" data-mask>
                        </div>

                        <div class="input-item col-xs-12">
                            <label for="RnombreTipo">Tipo lote:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <select class="form-control" id="RtipoLote" name="RtipoLote">
                                <option value="" disabled selected>Seleccione
                                </option>
                                <option value="EP">EP</option>
                                <option value="EL">EL</option>
                            </select>
                        </div>
             
                        <div class="input-item col-xs-12">
                            <label for="RnombreEstado">Estado Lote:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <select class="form-control" id="RestadoLote" name="RestadoLote">
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
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="GuardarLote();">Guardar</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editar-lotes">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Editar Lote</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="Editar-Lotes" method="POST">
                        <input type="hidden" name="txtidlote" id="txtidlote" value="">
                        <div class="input-item col-xs-12">
                            <input style="text-transform:uppercase;" name="txtCodigoLote" type="text" class="form-control" id="txtCodigoLote" required="" autocomplete="off" data-inputmask="'mask': ['aaa-99-9999']" data-mask>
                        </div>

                        <div class="input-item col-xs-12">
                            <label for="RnombreEstado">Tipo lote:
                                <span class="required">*</span>
                            </label>
                        </div>

                          <div class="input-item col-xs-12">
                            <select class="form-control" id="txttipoLote" name="txttipoLote">
                                <option value="" disabled selected>Seleccione
                                </option>
                                <option value="EP">EP</option>
                                <option value="EL">EL</option>
                            </select>
                        </div>
            
                        <div class="input-item col-xs-12">
                            <label for="RnombreEstado">Estado lote:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <select class="form-control" id="txtEstadoLote" name="txtEstadoLote">
                                <option value="" disabled selected>Seleccione  </option>
                                <option value="on">Habilitado</option>
                                <option value="off">Deshabilitado</option>
                            </select>
                        </div>
    
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="EditarLotes();">Guardar</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalconfirmar">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Cambiar estado</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <section>
                        <p class="mensajeconfirm" style="text-align:center;">
                            ¿Está seguro de cambiar el estado del lote?</p>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn-confirm-ok" class="btn btn-danger" onclick="corfirmar();">Aceptar</button>
            </div>
        </div>
    </div>
</div>
<script src="javascripts/gestionarlotes.js"></script>