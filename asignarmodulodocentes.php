    <div class="content-wrapper">

        <section id="contenido5" style="display:none;overflow-x: hidden">
            <div class="box">
                <input type="hidden" name="id_docente" id="id_docente">

                <div class="box-header">
                    <h3 class="box-title"><B>Asignaturas Habilitadas a Docentes:</B></h3>
                </div>
                <div class="box-body">
                    <table class="table table-hover" id="tbl_modulo_docentes">
                        <div>
                            <button type="button" title="Regresar a la lista de docentes habilitados" class="btn btn-primary " data-toggle="modal" onclick="Regresar();"> <i class="glyphicon glyphicon-repeat"></i>
                                Regresar</button>
                        </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="docente"> Docente: </label>
                            <span class="form-control" id="Mdocente">
                  
                   </div>
                   <div class="col-md-6">
                        <label for="docente"> Buscar mòdulo: </label>
                       <input type="text" name="NombreModulo" id="NombreModulo" class="form-control" placeholder='Modulo a buscar...' autofocus="" autocomplete="off">
                        </div>
                  
                   </div>
           
                </div>
                </div><br>
                <!-- /.box-header -->
                <div class="box-body table-responsive no-padding">

                  
                    <thead class="titulo">
                        <tr>
                            <th>No.</th>
                            <th>Sede/Grado</th>
                            <th>Asignatura</th>                          
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
        </section>

        <div class="box-body table-responsive no-padding">
            <!--modal editar modulos-->
            <div class="modal fade" id="editar-modulos">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><B>Editar Habilitar a asignaturas</B></h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <form id="Editar-Modulos" method="POST">
                                    <div class="input-item col-xs-12">
                                        <input type="hidden" name="EcodigoModuloDocente" id="EcodigoModuloDocente" />
                                        <label for="EidDocente">Docente:
                                            <span class="required">*</span>
                                        </label>
                                    </div>
                                    <div class="input-item col-xs-12">
                                        <select class="form-control" id="EidDocente" name="EidDocente" disabled>
                                        </select>
                                    </div>

                                    <div class="input-item col-xs-12">
                                        <label for="Esedes">Sede:
                                            <span class="required">*</span>
                                        </label>
                                    </div>
                                    <div class="input-item col-xs-12">
                                        <select class="form-control" id="Esedes" name="Esedes" readonly="readonly">

                                        </select>
                                    </div>

                                    <div class="input-item col-xs-12">
                                        <label for="EProgramas">Grado:
                                            <span class="required">*</span>
                                        </label>
                                    </div>
                                    <div class="input-item col-xs-12">
                                        <select class="form-control" id="Eprogramas" name="Eprogramas">

                                        </select>
                                    </div>

                                    <div class="input-item col-xs-12">
                                        <label for="EidModulos">Asignatura:
                                            <span class="required">*</span>
                                        </label>
                                    </div>
                                    <div class="input-item col-xs-12">
                                        <select class="form-control" id="EidModulos" name="EidModulos">

                                        </select>
                                    </div>

                                    <div class="input-item col-xs-12">
                                        <label for="EestadoModuloDocentes">Estado
                                            <span class="required">*</span>
                                        </label>
                                    </div>
                                    <div class="input-item col-xs-12">
                                        <select class="form-control" id="EestadoModuloDocentes"
                                            name="EestadoModuloDocentes">
                                            <option value="" disabled selected>Seleccione</option>
                                            <option value="on">Habilitado</option>
                                            <option value="off">Deshabilitado</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" title="Cancelar acción." class="btn btn-danger pull-left"
                                data-dismiss="modal">Cancelar</button>
                            <button type="button"
                                title="Actualizar la información de este móduloa asignado a este docente. "
                                class="btn btn-primary" onclick="ActualizarInfoModulosDocentes();">Actualizar
                                Información
                            </button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>



            </div>
            <!--fin modal editar modulos -->
            <!--Modal para editar estado de los modulos asignados a los docentes-->
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
                                            esta
                                            asignatura para este docente?</B></p>
                                </section>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" title="Cancelar acción." class="btn btn-default"
                                data-dismiss="modal">Cancelar</button>
                            <button type="button" title="Deshabilitar o Habiltar." class="btn btn-primary"
                                id="confirmar-ok" onclick="acceptConfirm();">OK</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!--Modal para editar estado de los modulos asignados a los docentes-->

                <thead class="titulo">
                    <tr>
                        <th>No.</th>
                        <th>Nombre perfil</th>
                        <th>Rol</th>
                        <th>Menús asignados</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
               </table>
            </div>
            <!--fin div del contenido de el listado de modulos asignados a docentes-->

            <!--div para asignar modulos  a docentes-->
            <section id="volver" style="display:none; overflow-x: hidden">
            <div class="box">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box-header">
                            <h3 class="box-title"><B>Habilitar Asignaturas a Docentes:</B></h3>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <div class="box-header">
                            <button type="button" title="Regresar a la vista, de listado de docentes."
                                class="btn btn-primary" data-toggle="modal" onclick="Volver();">
                                <i class="glyphicon glyphicon-repeat"></i>
                                Volver
                            </button>
                        </div>

                    </div>
                </div>

                <div class="row">
                        <div class="col-xs-4">
                        <form id="modulos">
                                   <label for="SelectDocentes">Docente:
                                    <span class="required">*</span>
                                     </label>
                                      <select class="form-control" id="Rdocente" name="Rdocente">
                                     </select>                                
                        </div>
                        <div class="col-xs-4">
                                  <label for="ESedeProgramaModulo">Sede:
                                        <span class="required">*</span>
                                    </label>

                                    <select class="form-control" id="Rsede" name="Rsede">
                                    </select>                           
                        </div>

                        <div class="col-xs-4">
                             <label for="EProgramaModulo">Grado:
                                  <span class="required">*</span>
                                 </label>
                                <select class="form-control" id="Rprograma" name="Rprograma">
                                </select>
                            </div>
                         </form>
     
                </div>
                <br>
                <div class="row">
               <div class="col-xs-18 col-md-8">
                   <span class="text-muted" style="padding-left: 10px;"> 
                   Seleccione las asignaturas a habilitar al docente y preseione click en guardar</span>
               </div>
                <div class="col-xs-2">
                  <button title="Cancelar acción" class="btn btn-block btn-danger"  id="btn-cancelar" onclick="RelacionModuloDocentes();" data-dismiss="modal">Cancelar</button> 
               </div>
               <div class="col-xs-2">
               <button  title="Asignar módulo a docente." class="btn btn-block btn-primary" id="btn-guardar" onclick="GuardarInformacion();">Guardar</button>
               </div>
              </div>
            </section>
        
            <section class="content" id="contenido2" style="display:none; overflow-x: hidden">
                   <div class="row">
                    <div class="col-xs-6">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title">Asignaturas del plan de estudio:</h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <table id="tbl_modulo_docentes_habilitados" class="table table-hover table-striped table-condesed">

                                    <thead class="titulo">
                                        <tr>
                                           <th>No</th>
                                           <th>Asignatura</th>
                                           <th>Seleccionar</th>
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

                    <div class="col-xs-6">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title">Asignaturas Habilitadas al docente:</h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <table id="tbl_modulo_docentes_asignados" class="table table-hover table-striped table-condesed">

                                    <thead class="titulo">
                                        <tr>
                                            <th>Código</th>
                                            <th>Asignatura</th>
                                            <th>Seleccionar </th>
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

            <!--div del contenido de el listado de modulos asignados a docentes-->
            <div class="col-xs-12" id="conte">
                <div class="box">

                    <div class="row">
                        <div class="box-header col-md-11">
                          <h3 class="box-title" style="padding:2px 4px 5px 10px"><B>Listado de Docentes:</B></h3>

                        </div>

                    </div>

                    <div class="box-body">
                            <div
                                style="display: flex; justify-content: space-between; margin-bottom: 18px; padding: 12px 24px;">
                             
                                <button type="button" title="Ir a asignar módulos a docentes." class="btn btn-primary"
                                    data-toggle="modal" onclick="opencontenido();">
                                    <i class="fa fa-clipboard" style="font-size:24px"></i>
                                    <p>Asignar Asignaturas</p>
                                   </button>
                            </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body table-responsive"  style="overflow-x: hidden">
                    <div class="row">
                        <div class="col-md-12">
                            <label for='docente'>Ingrese el nombre del docente:</label>
                             <input type="text" name="NombreDocente" id="NombreDocente" class="form-control text-upper" placeholder='Docente a buscar...' autocomplete="off">
                        </div>
                    </div>
                    <br>

                    <table class="table table-hover" id="tbl_listado_docentes">
                  
                        <thead class="titulo">
                            <tr>
                                <th>No.</th>
                                <th>Identificación</th>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Contacto</th>
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
            </div>
            <!--fin div del contenido de el listado de modulos asignados a docentes-->

        </div>
    </div>
     <!-- ./wrapper -->
    <script src="javascripts/asignarmodulodocentes.js"></script>