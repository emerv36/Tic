<div class="content-wrapper">
    <section class="content">
        <!-- Main content -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="row">
                        <div class="col-md-11">
                            <div class="box-header">
                                <h1 class="box-title"><B>Gestión de Usuarios:</B></h1>
                            </div>
                        </div>

             
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body table-responsive ">

                        <div
                            style="display: flex; justify-content: space-between; margin-bottom: 18px; padding: 12px 24px;">
                            <div class="input-group input-group-sm hidden-xs" style="width: 250px;">
                            </div>
                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                onclick=" fModalNuevoUsuario();">
                                <B>Nuevo Usuario</B>
                            </button>
                        </div>

                        <table class="table table-hover" id="tbl_usuarios" width="100%">
                            <thead class="titulo">
                                <tr>
                                    <th>No.</th>
                                    <th>Identidad</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>fecha creado</th>
                                    <th>Pérfil</th>
                                    <th>Estado</th>
                                    <th style="width:80px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>

                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
                <!-- /.content -->
            </div>
        </div>
    </section>
</div>
<!-- ./wrapper -->
<!--modal Registro-->
<div class="modal fade" id="registrar-usuario">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Nuevo Usuario</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="frmNuevo" method="POST">

                      <div class="input-item col-xs-12 col-md-12">
                            <label for="txtId">Identificación:<span style="color:red">*</span>
                            </label>
                            <input class="form-control" type="text" id="txtid" name="txtid" maxlength="11" autocomplete="off">
                        </div>
                       
                       <div class="input-item col-xs-12 col-md-12">
                              <label for="TxtEmail">E-mail:
                                    <span style="color:red">*</span>
                                </label>
                            <input class="form-control" type="text" id="txtemail" name="txtemail" autocomplete="off">
                        </div>

                        <div class="input-item col-xs-12 col-md-12">
                             <label for="txtnombre">Nombre:
                                    <span style="color:red">*</span>
                                </label>
                                <input class="form-control" type="text" id="txtnombre" name="txtnombre" autocomplete="off">
                        </div>
                        
                        <div class="input-item col-xs-12 col-md-12">
                                 <label for="txtPerfil">Perfil:
                                        <span style="color:red">*</span>
                                    </label>
                         <select class="form-control" id="txtperfil" name="txtperfil">
                         </select>
                        </div>
                        

                            <div class="input-item col-xs-12 col-md-12">
                         <label for="txtelefono">Teléfono:
                                    <span style="color:red">*</span>
                                </label>
                                <input class="form-control" type="text" id="txttelefono" name="txttelefono" maxlength="10" autocomplete="off">
                            </div>
                       
                         <div class="input-item col-xs-12 col-md-12">
                            <label for="txtdir">Direccion:
                                    <span style="color:red">*</span>
                                </label>
                            <input class="form-control" type="text" id="txtdir" name="txtdir" autocomplete="off">
                        </div>
                        
                        <div class="input-item col-xs-12 col-md-12">
                             <label for="txtfecha">Fecha de nacimiento:
                                    <span style="color:red">*</span>
                                </label>
                                <input class="form-control" type="date" id="txtfecha" name="txtfecha">
                        </div>

                       <div class="input-item col-xs-12 col-md-12">
                              <label for="txtfecha">Estado:
                                    <span style="color:red">*</span>
                                </label>
                         <select class="form-control" id="txtestado" name="txtestado">
                                <option value="">Seleccione  </option>
                                <option value="on">Habilitado</option>
                                <option value="off">Deshabilitado</option>
                            </select>
                        </div>
                     
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="RegistrarUsuarios();">Guardar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-Refistro -->
</div>

<!--Modal editar-->
<div class="modal fade" id="editar-usuario">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Editar usuario</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="frmEditar" method="POST">
                        <input type="hidden" id="codigo" name="codigo">
                         <div class="input-item col-xs-12 col-md-12">
                                   <label for="txtIdentificacion">Identificación:
                                    <span style="color:red">*</span>
                                </label>
                              
                            <input class="form-control" type="text" id="identificacion" name="identificacion" readonly="">
                        </div>
                        
                         <div class="input-item col-xs-12 col-md-12">
                           <label for="txtemail">E-mail:
                                    <span style="color:red">*</span>
                                </label>
                      
                            <input class="form-control" type="text" id="email" name="email" readonly="">
                        </div>
                         <br>
                         <div class="input-item col-xs-12 col-md-12">
                            <label for="txtNombre">Nombre:
                                    <span style="color:red">*</span>
                                </label>
                               <input class="form-control" type="text" id="nombre" name="nombre">
                        </div>

                         <div class="input-item col-xs-12 col-md-12">
                                <label for="txtPerfil">Perfil:
                                        <span style="color:red">*</span>
                                    </label>
                         <select class="form-control" id="perfil" name="perfil">
                         </select>
                        </div>
                       
                         <div class="input-item col-xs-12 col-md-12">
                           <label for="txtTelefono">Teléfono
                                    <span style="color:red">*</span>
                                </label>
                                              
                        <input class="form-control" type="text" id="telefono" name="telefono" maxlength="10">
                        </div>
                          <div class="input-item col-xs-12 col-md-12">
                           <label for="txtDir">Dirección:
                                    <span style="color:red">*</span>
                                </label>
                                         
                            <input class="form-control" type="text" id="direccion" name="direccion">
                        </div>

                          <div class="input-item col-xs-12 col-md-12">
                           <label for="txtfecha">Fecha de nacimiento:
                                    <span style="color:red">*</span>
                                </label>
                                <input class="form-control" type="date" id="fecha" name="fecha">
                        </div>
                          <div class="input-item col-xs-12 col-md-12">
                           <label for="txtfecha">Estado:
                                    <span style="color:red">*</span>
                                </label>
                         <select class="form-control" id="estado" name="estado">
                                <option value="">Seleccione  </option>
                                <option value="on">Habilitado</option>
                                <option value="off">Deshabilitado</option>
                            </select>
                        </div>

                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="EditarUsuario();">Guardar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!--Fin Modal editar-->
<!--Modal paara editar estado-->
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
                        <p class="mensajeconfirm" style="text-align:center;"><B>¿Está seguro
                                deshabilitar
                                este
                                Usuario?</B></p>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="confirmar-ok" onclick="acceptConfirm();">OK</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>
<!--Fin modal editar estado-->



<script src="javascripts/gestionarusuarios.js"></script>