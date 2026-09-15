  <div class="content-wrapper">
      <div class="col-xs-12">
          <div class="box">
                <div class="row">
                    <div class="col-md-11">
                    
                        <div class="box-header">
                            <h3 class="box-title"><B>Gestionar Perfiles:</B></h3>
                        </div>
                    </div>
                   </div>
              <div class="box-body">
                  <div style="display: flex; justify-content: space-between; margin-bottom: 18px; padding: 12px 24px;">
                      <div class="input-group input-group-sm hidden-xs" style="width: 250px;">
                      </div>
                      <button type="button" class="btn btn-primary" data-toggle="modal" onclick="fmodalNuevo();">
                         <B> Nuevo Perfil</B>
                      </button>
                  </div>
              </div>
              <!-- /.box-header -->
              <div class="box-body table-responsive ">
                  <table class="table  table-striped table-condesed" id="tbl_perfiles" width="100%">
                      <thead class="titulo">
                          <tr>
                              <th>No.</th>
                              <th>Nombre perfil</th>
                              <th>Rol</th>
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
      </div>
      <!-- /.content -->
  </div>
  <div class="modal fade" id="modal-perfilNuevo">
      <div class="modal-dialog" style="width:40vw">
          <div class="modal-content">
              <div class="modal-header bg-primary">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title"><B>Nuevo Perfil</B></h4>
              </div>
              <div class="modal-body">
                  <div class="row">
                      <form id="frmNuevo" method="POST">
                          <div class="input-item col-xs-12">
                              <label for="nNombrePerfil">Nombre
                                  <span class="required">*</span>
                              </label>
                          </div>
                          <div class="input-item col-xs-12">
                              <input class="form-control text-uppercase" type="text" id="nNombrePerfil" name="nNombrePerfil">
                          </div>
                          <div class="input-item col-xs-12">
                              <label for="nestado">Estado
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
  <!--modal editar-->
  <div class="modal fade" id="modal-EditPerfil">
      <div class="modal-dialog" style="width:40vw">
          <div class="modal-content">
              <div class="modal-header bg-primary">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title"><B>Editar Perfil</B></h4>
              </div>
              <div class="modal-body">
                  <div class="row">
                      <form id="frmEditar" method="POST">
                      
                          <div class="input-item col-xs-12">
                              <label for="estado">Estado
                                  <span class="required">*</span>
                              </label>
                          </div>
                          <div class="input-item col-xs-12">
                              <select class="form-control" id="estado" name="estado">
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
                  <button type="button" class="btn btn-primary" onclick="fEditarItem();">Actualizar Información</button>
              </div>
          </div>
          <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
  </div>

  <script src="javascripts/gestionarperfiles.js"></script>