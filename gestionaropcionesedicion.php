<div class="content-wrapper">
  <div class="content">
  <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="box" style="padding-bottom:20px; padding-left: 15px; padding-right: 10px; padding-top: 10px;">
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <div class="box-header">
                            <h1 class="box-title"><B>Opciones de edición</B></h1>
                        </div>
                    </div>
                </div>
             <div class="row" style="padding-left: 10px;">


             <ul class="nav nav-tabs nav nav-pills">
                <li class="active"><a data-toggle="tab" href="#op1">Cambiar de Programa / Horario <i class="fa fa-file" style="font-size:24px"></i></a></li>
                <li><a data-toggle="tab" href="#op2">Cambiar périodo inicio <i class="fa fa-calendar-check-o" style="font-size:24px"></i></a></li>
              </ul>

             <div class="tab-content">
                <div id="op1" class="tab-pane fade in active"><br>
               <div class="row">
                   <div class="col-md-12">
                       <span class="text-muted">Consulte la identidad del estudiante  que desea realizar el cambio de programa u horario de clases</span>
                   </div>
               </div>
                <div class="row">
               <div class="col-md-4 col-xs-12">
                    <label for="identidad">Identidad:</label><span style="color:red">*</span>
                    <input type="text" name="txtidentidad" id="txtidentidad" class="form-control" autocomplete="off"> 
               </div>
               <div class="col-md-2 col-xs-12" style="margin-top: 25px;">
                <button class="btn btn-primary btn-block" onclick="ConsultarEstudianteMatriculado();">Búscar</button>
                </div>
               </div>

                          <div class="col-md-12 col-xs-12">
                    <div class="row">  
                    <div id="resultado"  style="padding-bottom:20px; padding-left: 10px; padding-right: 10px; padding-top: 10px; display:none">
                    <table id="tbl_estudiantes_registrados" class="table table-hover table-striped table-condesed" width="100%">
                                                    <thead class="titulo">
                                                    <tr>
                                                    <th>No</th>
                                                    <th>Identidad</th>
                                                    <th>Nombre estudiante</th>
                                                    <th>Email</th>
                                                    <th>Programa académico</th>
                                                    <th>Périodo/inicio</th>
                                                    <th>Sede/Horario</th>  
                                                    <th>Fecha inscripción</th>    
                                                    <th>Estado</th>   
                                                    <th>Acción</th>                                
                                                    </tr>
                                                    </thead> 
                                                    <tbody>
                                                </tbody>                           
                                                </table>

                    </div>
                    </div>
                    </div>

                </div>
                <div id="op2" class="tab-pane fade"><br>
                <div class="row">
                   <div class="col-md-12">
                       <span class="text-muted">Consulte la identidad del estudiante que desea realizar el cambio de périodo de inicio, solo estudiantes en estado <strong> INSCRITO </strong></span>
                   </div>
               </div>
                <div class="row">
               <div class="col-md-4 col-xs-12">
                      <label for="identidad">Identidad:</label>
                    <input type="text" name="Pidentidad" id="Pidentidad" class="form-control"> 
               </div>
               <div class="col-md-2 col-xs-12" style="margin-top: 25px;">
                <button class="btn btn-primary btn-block" onclick="ConsultaEstudianteInscritos();">Búscar</button>
                </div>
               </div>
               <div class="row">
                   <div class="col-md-12">
                   <div id="resultado_estudiantes" style="display:none">
                     <table id="tbl_estudiantes" class="table table-hover table-striped table-condesed" width="100%">
                           <thead>
                              <th>No</th>   
                              <th>Identidad</th>
                              <th>Nombre</th>
                              <th>Email</th>
                              <th>Sede/Horario</th>
                              <th>Programa</th>
                              <th>Fecha Inscripcion</th>
                              <th>Périodo</th>
                              <th>Estado</th>
                              <th>Acción</th>
                           </thead>
                           <tbody>
                               
                           </tbody>
                       </table>
                   </div>
                   </div>
               </div>  
                </div>
                </div>
             </div>
          </div>
       </div>
     </div>
</div>
</div>



<!-- Modal para la editar la información de inscripción -->
<div id="ModalCambiarProgramaMatricula" class="modal fade" role="dialog">
    <div class="modal-dialog">
    <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Cambiar programa académico</h4>
            </div>
            <div class="modal-body">
    
           <form id="Actualizar-informacion-matricula" method="POST"> 
             <div class="row">
                     <input type="hidden" name="txtPidinscripcion" id="txtPidinscripcion">
                     <input type="hidden" name="txtPidperiodo" id="txtPidperiodo">
                     
                     
                      <div class="input-item col-md-4 col-xs-12">
                      <label for="RegistroConvenio">Identidad:</label>
                      <span  class="form-control input-sm" id="txtPidentidad" style="background-color: #ffcc;" ></span>                         
                      </div>

                      <div class="input-item col-md-8 col-xs-12">
                      <label for="RegistroConvenio">Nombre:</label>
                      <span  class="form-control input-sm" id="txtPnombre" style="background-color: #ffcc;" ></span>                         
                      </div>

                      <div class="input-item col-md-4 col-xs-12">
                      <label for="RegistroConvenio">Périodo Inicio:</label>
                      <span  class="form-control input-sm" id="txtPperiodo" style="background-color: #ffcc;" ></span>                         
                      </div>
                  
                      <div class="input-item col-md-8 col-xs-12">
                      <label for="RegistroConvenio">Programa:</label>
                      <span  class="form-control input-sm" id="txtPprograma" style="background-color: #ffcc;" ></span>                         
                      </div>

                      <div class="input-item col-md-4 col-xs-12">
                      <label for="RegistroConvenio">Curso:</label>
                      <span  class="form-control input-sm" id="txtPcurso" style="background-color: #ffcc;" ></span>                         
                      </div>

                      <div class="input-item col-md-8 col-xs-12">
                      <label for="RegistroConvenio">Sede/Horario:</label>
                      <span  class="form-control input-sm" id="txtPsede" style="background-color: #ffcc;" ></span>                         
                      </div>

                    
                   
                      <div class="input-item col-md-12 col-xs-12">
                      <label for="RegistroConvenio">Sede:<span class="required">*</span></label>
                      <select class="form-control input-sm"  id="txtcmbsede" name="txtcmbsede" data-toggle="tooltip" title="Seleccione sede" style="width:100%">
                      </select>
                      </div>

               
                      <div class="input-item col-md-12 col-xs-12">
                      <label for="RegistroConvenio">Programa:<span class="required">*</span></label>
                      <select class="form-control input-sm"  id="txtcmbprograma" name="txtcmbprograma" data-toggle="tooltip" title="Seleccione sede" style="width:100%">
                      </select>
                      </div>

                      <div class="input-item col-md-12 col-xs-12">
                      <label for="RegistroConvenio">Horario:<span class="required">*</span></label>
                      <select class="form-control input-sm"  id="txtcmbhorario" name="txtcmbhorario" data-toggle="tooltip" title="Seleccione sede" style="width:100%">
                      </select>
                     </div>


                     <div class="input-item col-md-12 col-xs-12">
                      <label for="RegistroConvenio">Curso:<span class="required">*</span></label>
                      <select class="form-control input-sm"  id="txtcmbcurso" name="txtcmbcurso" data-toggle="tooltip" title="Seleccione sede" style="width:100%">
                      </select>
                     </div>
                </div>
            </form>
                <div class="modal-footer">
                  <button type="button" class="btn btn-danger" data-dismiss="modal" >Cancelar</button>
                  <button type="button" class="btn btn-primary" onclick="GuardarCambioProgramaMatriculado();">Guardar</button>
                </div>
      
       
    </div>
    </div>
       
        </div>
    </div>
</div>




<!-- Modal para el cambio de programa estudiante incsrito -->
<div id="ModalCambiarProgramaInscrito" class="modal fade" role="dialog">
    <div class="modal-dialog">
    <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Cambiar programa académico</h4>
            </div>
            <div class="modal-body">
    
           <form id="Actualizar-informacion-inscrito" method="POST"> 
             <div class="row">
                     <input type="hidden" name="idinscripcion"  id="idinscripcion">
                     <input type="hidden" name="idperiodo"      id="idperiodo">
                     
                     
                      <div class="input-item col-md-4 col-xs-12">
                      <label for="RegistroConvenio">Identidad:</label>
                      <span  class="form-control input-sm" id="txtNumeroidentidad" style="background-color: #ffcc;" ></span>                         
                      </div>

                      <div class="input-item col-md-8 col-xs-12">
                      <label for="RegistroConvenio">Nombre:</label>
                      <span  class="form-control input-sm" id="txtnombre" style="background-color: #ffcc;" ></span>                         
                      </div>

                      <div class="input-item col-md-4 col-xs-12">
                      <label for="RegistroConvenio">Périodo Inicio:</label>
                      <span  class="form-control input-sm" id="txtperiodo" style="background-color: #ffcc;" ></span>                         
                      </div>
                  
                      <div class="input-item col-md-8 col-xs-12">
                      <label for="RegistroConvenio">Programa:</label>
                      <span  class="form-control input-sm" id="txtprograma" style="background-color: #ffcc;" ></span>                         
                      </div>

                      <div class="input-item col-md-12 col-xs-12">
                      <label for="RegistroConvenio">Sede/Horario:</label>
                      <span  class="form-control input-sm" id="txtsede" style="background-color: #ffcc;" ></span>                         
                      </div>
                   
                      <div class="input-item col-md-12 col-xs-12">
                      <label for="RegistroConvenio">Sede:<span class="required">*</span></label>
                      <select class="form-control input-sm"  id="txtcmbsedeinscrito" name="txtcmbsedeinscrito" data-toggle="tooltip" title="Seleccione sede" style="width:100%">
                      </select>
                      </div>

               
                      <div class="input-item col-md-12 col-xs-12">
                      <label for="RegistroConvenio">Programa:<span class="required">*</span></label>
                      <select class="form-control input-sm"  id="txtcmbprogramainscrito" name="txtcmbprogramainscrito" data-toggle="tooltip" title="Seleccione sede" style="width:100%">
                      </select>
                      </div>

                      <div class="input-item col-md-12 col-xs-12">
                      <label for="RegistroConvenio">Horario:<span class="required">*</span></label>
                      <select class="form-control input-sm"  id="txtcmbhorarioinscrito" name="txtcmbhorarioinscrito" data-toggle="tooltip" title="Seleccione sede" style="width:100%">
                      </select>
                     </div>

                </div>
            </form>
                <div class="modal-footer">
                  <button type="button" class="btn btn-danger" data-dismiss="modal" >Cancelar</button>
                  <button type="button" class="btn btn-primary" onclick="GuardarCambioProgramaInscrito();">Guardar</button>
                </div>
      
       
    </div>
    </div>
       
        </div>
    </div>
</div>



<!-- Modal para cambiar periodo de inicio la inscripcion de estudiante -->
<div id="ModalCambiarPeriodoInicio" class="modal fade" role="dialog">
    <div class="modal-dialog">
    <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Cambiar périodo de inicio</h4>
            </div>
            <div class="modal-body">
    
           <form id="Actualizar-informacion-periodo" method="POST"> 
             <div class="row">
                     <input type="hidden" name="Peidinscripcion"  id="Peidinscripcion">
              
                     
                     
                     
                      <div class="input-item col-md-4 col-xs-12">
                      <label for="RegistroConvenio">Identidad:</label>
                      <span  class="form-control input-sm" id="txtpeidentidad" style="background-color: #ffcc;" ></span>                         
                      </div>

                      <div class="input-item col-md-8 col-xs-12">
                      <label for="RegistroConvenio">Nombre:</label>
                      <span  class="form-control input-sm" id="txtpenombre" style="background-color: #ffcc;" ></span>                         
                      </div>

                      <div class="input-item col-md-4 col-xs-12">
                      <label for="RegistroConvenio">Périodo Inicio:</label>
                      <span  class="form-control input-sm" id="txtpeperiodo" style="background-color: #ffcc;" ></span>                         
                      </div>
                  
                      <div class="input-item col-md-8 col-xs-12">
                      <label for="RegistroConvenio">Programa:</label>
                      <span  class="form-control input-sm" id="txtpeprograma" style="background-color: #ffcc;" ></span>                         
                      </div>

                      <div class="input-item col-md-12 col-xs-12">
                      <label for="RegistroConvenio">Sede/Horario:</label>
                      <span  class="form-control input-sm" id="txtpesede" style="background-color: #ffcc;" ></span>                         
                      </div>

                      <div class="input-item col-md-12 col-xs-12">
                      <label for="RegistroConvenio">Nuevo périodo de inicio:<span class="required">*</span>
                      </label><br>
                      <span class="text-muted">Se cargan los périodos de acuerdo a la oferta en el horario y programa de inscripción</span>
                      <select class="form-control input-sm"  id="txtcmbpeperiodo" name="txtcmbpeperiodo" data-toggle="tooltip" title="Seleccione sede" style="width:100%">
                      </select>
                      </div>
                  
                     </div>

                </div>
            </form>
                <div class="modal-footer">
                  <button type="button" class="btn btn-danger" data-dismiss="modal" >Cancelar</button>
                  <button type="button" class="btn btn-primary" onclick="UpdatePeriodoInicio();">Guardar</button>
                </div>
      
       
    </div>
    </div>
       
        </div>
    </div>
</div>

<script src="javascripts/gestionaropcionesedicion.js"></script>

