
<div class="content-wrapper">
 <br>
  <div class="col-md-12">
   <div class="box">

<ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#H1">Reporte</a></li>
  <li><a data-toggle="tab" href="#H2">Versión Reporte</a></li>
</ul>

<div class="tab-content">
  <div id="H1" class="tab-pane fade in active">
      <div class="row">
                <div class="col-md-11">
                    <div class="box-header">
                        <h3 class="box-title"><B>Gestionar Reportes:</B></h3>
                    </div>
                </div>    
            </div>
        <div class="box-body">
                <div style="display: flex; justify-content: space-between; margin-bottom: 18px; padding: 12px 24px;">
                  
                    <button type="button" class="btn btn-primary" data-toggle="modal"
                        onclick="ModalRegistrarReportes();">
                        Nuevo reporte
                    </button>
                </div>
            </div>


            <!-- /.box-header -->
            <div class="box-body table-responsive">
                <table class="table table-hover table-striped table-condesed" id="tbl_reportes" width="100%">
                    <!--fin modal para editar el estado de los periodos -->
                    <thead class="titulo">
                        <tr>
                            <th>No.</th>
                            <th>Codigo</th>
                            <th>Nombre Reporte</th>
                            <th>Nombre Archivo</th>
                            <th>Versión</th>
                            <th>Fecha Vigencia</th>
                            <th>Estado</th>
                            <th >Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <!-- /.box-body -->


  </div>
  <div id="H2" class="tab-pane fade">

  <div class="row">
                <div class="col-md-11">
                    <div class="box-header">
                        <h3 class="box-title"><B>Versión Reportes:</B></h3>
                    </div>
                </div>
   </div>

  <div class="box-body">
                <div style="display: flex; justify-content: space-between; margin-bottom: 18px; padding: 12px 24px;">
                    <div class="input-group input-group-sm hidden-xs" style="width: 250px;">
                    </div>
                    <button type="button" class="btn btn-primary" data-toggle="modal"
                        onclick="ModalVersionReportes();">
                        Nueva Versión
                    </button>
                </div>
  </div>
 
    <div class="box-body table-responsive">
     
                <table class="table table-hover table-striped table-condesed" id="tbl_version_reporte" width="100%">
                    <!--fin modal para editar el estado de los periodos -->
                    <thead class="titulo">
                        <tr>
                            <th>No.</th>
                            <th>Versión</th>
                            <th style="width:100%;">Fecha Versión</th>
                            <th >Acciones</th>
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
    <!-- /.content -->
</div>
<div class="modal fade" id="registrar-reportes">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Nuevo Reporte</B></h4>
            </div>
            <!-- modal registrar periodos-->
            <div class="modal-body">
                <div class="row">
                    <form id="Registrar-Reportes" method="POST">

                            <div class="input-item col-xs-12">
                            <label for="RcodigoReporte">Código de Reporte:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="text" id="RcodigoReporte" name="RcodigoReporte">
                        </div>

                        <div class="input-item col-xs-12">
                            <label for="RnombreReporte">Nombre de Reporte:
                                <span class="required">*</span>
                            </label>
                        </div>
                        
                        <div class="input-item col-xs-12">
                            <input class="form-control" placeholder="" type="text" id="RnombreReporte"
                                name="RnombreReporte" >
                        </div>

                        <div class="input-item col-xs-12">
                            <label for="RnombreArchivo">Nombre de Archivo:
                                <span class="required">*</span>
                            </label>
                        </div>
                        
                        <div class="input-item col-xs-12">
                            <input class="form-control" placeholder="" type="text" id="RnombreArchivo"
                                name="RnombreArchivo" >
                        </div>
                    
                    
                        <div class="input-item col-xs-12">
                            <label for="RVersionReporte">Versión de Reporte:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                              <select class="form-control" id="RversionReporte" name="RversionReporte">
                                <option value=""  selected>Seleccione</option>
                            </select>
                        </div>
                    
                        <div class="input-item col-xs-12">
                            <label for="RPlantelReporte">Institución Educativa:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <select class="form-control" id="RplantelReporte" name="RplantelReporte">
                                <option value=""  selected>Seleccione</option>
                            </select>
                        </div>

                    

                        <div class="input-item col-xs-12">
                            <label for="RestadoReporte">Estado:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <select class="form-control" id="RestadoReporte" name="RestadoReportes">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="on">Habilitado</option>
                                <option value="off">Deshabilitado</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="RegistrarReportes();">Guardar</button>
            </div>
        </div>
        <!--fin modal registrar periodos-->
    </div>
    <!-- /.modal-dialog -->
</div>



<!--modal editar periodos-->
<div class="modal fade" id="editar-reportes">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Editar Reportes</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="Editar-Reportes" method="POST">
                        <input class="form-control" type="hidden" id="EidReporte" name="EidReporte">
                        <div class="input-item col-xs-12">
                            <label for="EcodigoReporte">Código:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                    <input class="form-control" placeholder="" type="text" id="EcodigoReporte"
                                name="EcodigoReporte">
                        </div>
                        <div class="input-item col-xs-12">
                            <label for="EnombreReporte">Nombre Reporte:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                          <input class="form-control" placeholder="" type="text" id="EnombreReporte" name="EnombreReporte">
                        </div>

                        <div class="input-item col-xs-12">
                            <label for="EnombreArchivo">Nombre Archivo:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                          <input class="form-control" placeholder="" type="text" id="EnombreArchivo" name="EnombreArchivo">
                        </div>
                        <div class="input-item col-xs-12">
                            <label for="EVersionReporte">Versión de Reporte:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <select class="form-control" id="EversionReporte" name="EversionReporte">
                            </select>
                        </div>
                        <div class="input-item col-xs-12">
                            <label for="EPlantelReporte">Institución Educativa:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                        <select class="form-control" id="EplantelReporte" name="EplantelReporte">
                        </select>
                        </div>
                  
                        <div class="input-item col-xs-12">
                            <label for="EestadoReporte">Estado:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <select class="form-control" id="EestadoReporte" name="EestadoReporte">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="on">Habilitado</option>
                                <option value="off">Deshabilitado</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="actualizarReportes();">Guardar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>

<!--modal para editar el estado del informe -->
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
                <p class="mensajeconfirm" style="text-align:center"><B>¿Está seguro de  Deshabilitar este informe?</B></p>
                </section>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
               <button type="button" class="btn btn-primary" id="confirmar-ok" onclick="acceptConfirm()">Aceptar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>

<!--modal para editar el estado del informe -->
<div class="modal fade" id="modalestadoversion">
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
                <p class="mensajeconfirm" style="text-align:center"><B>¿Está seguro de  Deshabilitar este registro?</B></p>
                </section>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
               <button type="button" class="btn btn-primary" id="confirmar-ok" onclick="acceptConfirmacion()">Aceptar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>


<!-- Modal para el registro de las versiones de los reportes-->

<div class="modal fade" id="registrar-version">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Nueva version</B></h4>
            </div>
            <!-- modal registrar periodos-->
            <div class="modal-body">
                <div class="row">
                    <form id="Registrar-Version" method="POST">

                            <div class="input-item col-xs-12">
                            <label for="RnombreVersion">Nombre Versión
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="text" id="RnombreVersion" name="RnombreVersion">
                        </div>

                        <div class="input-item col-xs-12">
                            <label for="FechaVersión">Fecha Vigencia
                                <span class="required">*</span>
                            </label>
                        </div>
                         <div class="input-item col-xs-12">
                      <input class="form-control" type="date" id="RfechaVersion" name="RfechaVersion">
                       </div>
                    
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="RegistrarVersion();">Guardar</button>
            </div>
        </div>
        <!--fin modal registrar periodos-->
    </div>
    <!-- /.modal-dialog -->
</div>


<!-- Modal para editar las versiones de los reportes-->
<div class="modal fade" id="editar-version">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Editar version</B></h4>
            </div>
            <!-- modal registrar periodos-->
            <div class="modal-body">
                <div class="row">
                    <form id="Editar-Version" method="POST">
                      <div class="input-item col-xs-12">
                        <label for="EnombreVersion">Nombre Versión
                             <span class="required">*</span>
                        </label>
                        </div>
                     <div class="input-item col-xs-12">
                       <input  type="hidden" id="Eideversion" name="Eideversion"> 
                       <input class="form-control" type="text" id="EnombreVersion" name="EnombreVersion">
                       </div>

                   <div class="input-item col-xs-12">
                            <label for="FechaVersión">Fecha Vigencia
                                <span class="required">*</span>
                            </label>
                        </div>
                         <div class="input-item col-xs-12">
                    <input class="form-control" type="date" id="EfechaVersion" name="EfechaVersion">
                       </div>
                    
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="ActualizarVersion();">Guardar</button>
            </div>
        </div>
        <!--fin modal registrar periodos-->
    </div>
    <!-- /.modal-dialog -->
</div>

<script src="javascripts/gestionarreportes.js"></script>