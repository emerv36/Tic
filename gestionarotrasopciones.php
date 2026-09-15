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
                            <li class="active"><a data-toggle="tab" href="#op1">Record módulos estudiante <i class="fa fa-file" style="font-size:24px"></i></a></li>
                            <li><a data-toggle="tab" href="#op2">Matricular módulo adicional <i class="fa fa-files-o" style="font-size:24px"></i></a></li>
                            <li><a data-toggle="tab" href="#op3">Estudiantes sin curso <i class="fa fa-user-times" style="font-size:24px"></i> <span id="Ccruzados" class="badge badge-danger"></span> </a></li>


                        </ul>

                        <div class="tab-content">
                            <div id="op1" class="tab-pane fade in active"><br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <span class="text-muted">Consulte la identidad del estudiante para retirar del módulo programado, los módulos visible serán los módulos en estado <b> ABIERTO </b> en la carga académica del curso en cuestión</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 col-xs-12">
                                        <label for="identidad">Identidad:</label><span style="color:red">*</span>
                                        <input type="text" name="txtidentidad" id="txtidentidad" class="form-control" autocomplete="off">
                                    </div>
                                    <div class="col-md-2 col-xs-12" style="margin-top: 25px;">
                                        <button class="btn btn-primary btn-block" onclick="ConsultaRecordModuloEstudiante();">Búscar</button>
                                    </div>
                                </div>

                                <div class="col-md-12 col-xs-12">
                                    <div class="row">
                                        <div id="resultado" style="padding-bottom:20px; padding-left: 10px; padding-right: 10px; padding-top: 10px; display:none">
                                            <table id="tbl_modulos_estudiante" class="table table-hover table-striped table-condesed" width="100%">
                                                <thead class="titulo">
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Identidad</th>
                                                        <th>Nombre estudiante</th>
                                                        <th>Módulo/Curso</th>
                                                        <th>Périodo</th>
                                                        <th>Programa académico</th>
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
                            <div id="infobuscar">
                                <div class="row">
                                    <div class="col-md-12">
                                        <span class="text-muted">Consulte la identidad del estudiante que desea matricular módulo adicional, tenga en cuanta que el módulo a matricular debe estar programado en el périodo actual</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 col-xs-12">
                                        <label for="identidad">Identidad:</label>
                                        <input type="text" name="txtidentidadbuscar" id="txtidentidadbuscar" class="form-control">
                                    </div>
                                    <div class="col-md-2 col-xs-12" style="margin-top: 25px;">
                                        <button class="btn btn-primary btn-block" onclick="ConsultaEstudiantesModuloAdicional();">Búscar</button>
                                    </div>
                                </div>
                            </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div id="resultado_estudiantes" style="display:none">
                                            <table id="tbl_estudiantes_modulo_adicional" class="table table-hover table-striped table-condesed" width="100%">
                                                <thead class="titulo">
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Identidad</th>
                                                        <th>Nombre estudiante</th>
                                                        <th>Curso</th>
                                                        <th>Último módulo programado</th>
                                                        <th>Périodo</th>
                                                        <th>Sede/Horario</th>
                                                        <th>Docente</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>



                                    <div id="matriculamoduloadiciona" style="display:none;">
                                    <div class="row" style="padding-bottom: 10px;padding-left: 20px;">
                                  
                                        <div class="col-md-9">
                                            <span class="text-mute">Seleccione el módulo del plan de estudio que desea matricular adicional, el sistema buscará los curso que tenga enl módulo programado en el périodo reciente </span>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-block btn-primary" title="Regresar" onclick="VolverBusqueda();"><i class="fa fa-mail-reply"></i> Regresar </button>
                                        </div>
                                     
                                    </div>
                                    <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <div class="box">
                                                        <div class="modal-header bg-primary">
                                                            <h4 class="modal-title"><B>Resultado de búsqueda de módulos</B></h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">  
                                                            <div id="resultadobusqueda" style="display: none;">  
                                                            <table class="table table-responsive table-striped table-condesed table-sm" id="tbl_modulos_encontrados">
                                                                 <thead class="titulo">
                                                                           <tr>
                                                                            <th>No</th>
                                                                            <th>Curso</th>
                                                                            <th>Módulo/Docente</th>
                                                                            <th>Programa académico/Sede/Horario</th>
                                                                            <th>Périodo</th>
                                                                            <th>Acción</th>                                                                           
                                                                      </tr>
                                                                  </thead>
                                                                                <tbody>
                                                                                </tbody>
                                                            </table>  
                                                            </div> 
                                                            <div id="infobusqueda">
                                                            <div class="jumbotron">
                                                                         <div class="container">
                                                                             <h4><strong>Observaciones:</strong></h4>
                                                                             <span >¡Presion clic en el botón <b> búscar módulo </b> del plan de estudio del estudiante, se realizará la busqueda de la programacíón del último périodo en todos los cursos, tenga en cuenta que los cursos deben tener la programación del último périodo antes de realizar este proceso!</span>
                                                                          </div>
                                                                     </div>

                                                            </div>                                              
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="box">
                                                        <div class="modal-header bg-primary">
                                                            <h4 class="modal-title"><B>Información estudiante</B></h4>
                                                        </div>
                                                        <div class="modal-body">
                                                             <input type="hidden"  id="infoiddetalle"       name="infoiddetalle">
                                                             <input type="hidden"  id="infoidestudiante"    name="infoidestudiante">
                                                             <input type="hidden"  id="infoidperiodo"       name="infoidperiodo">
                                                             <input type="hidden"  id="infoidprograma"      name="infoidprograma">
                                                             <input type="hidden"  id="infoidsede"          name="infoidsede">
                                                             <input type="hidden"  id="infomodulo"          name="infomodulo">

                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <label for="lblcurso">Curso:</label>
                                                                    <input type="text" name="Infocurso" id="Infocurso" class="form-control" readonly="" data-toggle="tooltip" title="Curso">
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <label for="lbHorario">Sede/Horario Matriculado:</label>
                                                                    <input type="text" name="InfoHorario" id="InfoHorario" class="form-control" readonly="" data-toggle="tooltip" title="Horario">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <label for="lblcurso">Programa académico matriculado:</label>
                                                                    <input type="text" name="InfoPrograma" id="InfoPrograma" class="form-control" readonly="" data-toggle="tooltip" title="programa">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <label for="LblNombre">Nombre estudiante :</label>
                                                                    <input type="text" name="InfoNombre" id="InfoNombre" class="form-control" readonly="" data-toggle="tooltip" title="Nombre estudiante">
                                                                </div>
                                                        </div>

                                                            <br>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <ul class="nav nav-tabs">
                                                                        <li class="active"><a data-toggle="tab" href="#InfoOp1">Plan de
                                                                                estudio</a></li>
                                                                        <li><a data-toggle="tab" href="#InfoOp2">Record Módulos</a></li>
                                                                    </ul>

                                                                    <div class="tab-content">
                                                                        <style>
                                                                           #InfoOp1{
                                                                               height: 300px;
                                                                               overflow: auto;


                                                                           } 
                                                                               </style>

                                                                        <div id="InfoOp1" class="tab-pane fade in active">

                                                                            <table class="table table-responsive table-striped table-condesed table-sm" id="tbl_plan_estudio">
                                                                                <thead class="titulo">
                                                                                    <tr>
                                                                                        <th>Id</th>
                                                                                        <th>Módulo</th>
                                                                                        <th>Horas</th>
                                                                                        <th>Acción</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                        <div id="InfoOp2" class="tab-pane fade">
                                                                            <table class="table table-responsive table-striped table-condesed table-sm" id="tbl_historial_recordmodulos">
                                                                                <thead class="titulo">
                                                                                    <tr>
                                                                                        <th>Id</th>
                                                                                        <th>Módulo</th>
                                                                                        <th>Périodo</th>
                                                                                        <th>Curso</th>
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
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                                </div>
                            </div>

                            <div id="op3" class="tab-pane fade"><br>

                                <div class="col-md-12">
                                    <div class="box-body" id="resultado_estudiante_cruzado">
                                        <table id="tbl_lista_estudiantes_cruzados" class="table table-hover table-striped table-condesed" width="100%">
                                            <thead class="titulo">
                                                <tr>
                                                    <th>Identidad</th>
                                                    <th>Apellidos/Nombre</th>
                                                    <th>Convenio</th>
                                                    <th>Programa académico/Sede/Horario/</th>
                                                    <th>Curso</th>
                                                    <th>Périodo/Inicio</th>
                                                    <th>Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div id="moverestudiantescurso" style="display:none;">
                                    <div class="row" style="padding-bottom: 10px;">
                                        <div class="col-md-9">
                                            <span class="text-mute">Búsque el curso en donde desea ubicar el estudiante a mover, tenga en
                                                cuenta que el curso destino debe tener un módulo programado en el périodo reciente </span>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-block btn-primary" title="Regresar" onclick="VolverListaCruzados();"><i class="fa fa-mail-reply"></i> Regresar </button>
                                        </div>
                                        <div class="col-md-1">
                                        </div>
                                    </div>

                                 

                                    <div class="row">
                                         
                                       <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <div class="box">
                                                        <div class="modal-header bg-primary">
                                                            <h4 class="modal-title"><B>Búscar Cursos</B></h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">

                                                           <ul class="nav nav-tabs">
                                                                <li class="active"><a data-toggle="tab" href="#C1">Cursos sugueridos</a></li>
                                                                <li><a data-toggle="tab" href="#C2">Opciones de Cambio</a></li>
                                                                </ul>

                                                                <div class="tab-content">
                                                                <div id="C1" class="tab-pane fade in active">
                                                                    <br>
                                                                 <div class="row" id="BotonesFiltro" style="display: none;">
                                                                     <div class="col-md-4">
                                                                         <button class="btn btn-warning btn-block " onclick="CargarModulosProgramadosTransversales();"><i class="fa fa-check-circle"></i> Módulos Transversales</button>
                                                                     </div>
                                                                     <div class="col-md-4">
                                                                         <button class="btn btn-warning btn-block" onclick="CargarModulosProgramadosEspecificos();"><i class="fa fa-check-circle"></i> Módulos Específicos</button>
                                                                     </div>
                                                                     <div class="col-md-4">
                                                                         <button class="btn btn-warning btn-block" onclick="CargarUltimoPeriodoProgramado();"><i class="fa fa-check-circle"></i> Todos los Módulos</button>
                                                                     </div>
                                                                 </div>
                                                                 <br>

                                                                 <div class="row">
                                                                 <div class="col-md-12">
                                                                   <table class="table table-responsive table-striped table-condesed table-sm" id="tbl_cursos_sugeridos">
                                                                                    <thead class="titulo">
                                                                                        <tr>
                                                                                            <th>Curso</th>
                                                                                            <th>módulo</th>
                                                                                            <th>Sede/Horario/Programa</th>
                                                                                            <th>Périodo</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                    </tbody>
                                                                        </table>
                                                                   </div>
                                                                   </div>
                                                                </div>
                                                                <div id="C2" class="tab-pane fade">
                                                              
                                                                <div class="col-md-6">
                                                                    <label for="lblperiodomover">Périodo/inicio iguales:</label>
                                                                    <select name="Fmperiodoinicio" id="Fmperiodoinicio" class="form-control">
                                                                        <option value=" ">Seleccione...</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="lblcursomover">Curso:</label>
                                                                    <select name="Fmcursobuscar" id="Fmcursobuscar" class="form-control">
                                                                        <option value="">Seleccione...</option>
                                                                    </select>
                                                                    <input type="hidden" name="Fmsedecurso" id="Fmsedecurso">
                                                                    <input type="hidden" name="Fmprogramacurso" id="Fmprogramacurso">
                                                                    <input type="hidden" name="Fmcoincidencias" id="Fmcoincidencias">
                                                                    <input type="hidden" name="Fmcoincidenciaspensum" id="Fmcoincidenciaspensum">
                                                                    <input type="hidden" name="Fmidhorariobuscar" id="Fmidhorariobuscar">
                                                                    <input type="hidden" name="Fmcursom" id="Fmcursom">
                                                                    <input type="hidden" name="Fmcoincidenciasvistos" id="Fmcoincidenciasvistos">
                                                                    <input type="hidden" name="Fmcursocambio" id="Fmcursocambio">
                                                                    <input type="hidden" name="Fmperiodocruce" id="Fmperiodocruce">
                                                                </div>
                                                                <div id="resultado_buscar_curso_mover" style="display: none;">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <label for="lblsedeprograma">Programa:</label>
                                                                        <span class="form-control" id="Fmsedeprograma"></span>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-8">
                                                                        <label for="lblsedejoranda">Sede/Jornada:</label>
                                                                        <span class="form-control" id="Fmsedejornada"></span>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-4" style="padding-top: 25px;">
                                                                        <button id="btn_plan_mover" class="btn btn-primary btn-block" title="Comparar plan de estudio">Comparar</button>

                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <br>
                                                                <div class="row">
                                                                 <div class="col-md-12">
                                                                    <ul class="nav nav-tabs">
                                                                        <li class="active"><a data-toggle="tab" href="#FMopcion1">Estudiantes
                                                                                del curso</a></li>
                                                                        <li><a data-toggle="tab" href="#FMopcion2">Record módulos</a></li>
                                                                        <li><a data-toggle="tab" href="#FMopcion3">Plan de Estudio</a></li>
                                                                    </ul>
                                                         
                                                                    <div class="tab-content">
                                                                         <div id="FMopcion1" class="tab-pane fade in active">
                                                                                <table class="table table-responsive table-striped table-condesed table-sm" id="tbl_estudiantes_buscar_cruzado">
                                                                                    <thead class="titulo">
                                                                                        <tr>
                                                                                            <th>No</th>
                                                                                            <th>Identidad</th>
                                                                                            <th>Apellidos - Nombres</th>
                                                                                            <th>Périodo/Inicio</th>

                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                    </tbody>
                                                                                </table>
                                                                        </div>

                                                                        <div id="FMopcion2" class="tab-pane fade">
                                                                            <input type="hidden" id="RecordProgramado">
                                                                            <input type="hidden" id="RecordVisto">
                                                                            <table class="table table-responsive table-striped table-condesed table-sm" id="tbl_ultimo_modulo_buscar_cruzado">
                                                                                <thead class="titulo">
                                                                                    <tr>
                                                                                        <th>Código</th>
                                                                                        <th>Módulo</th>
                                                                                        <th>Périodo</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                </tbody>
                                                                            </table>

                                                                        </div>
                                                                        <div id="FMopcion3" class="tab-pane fade">
                                                                            <table class="table table-responsive table-striped table-condesed table-sm" id="tbl_plan_buscar_cruzados">
                                                                                <thead class="titulo">
                                                                                    <tr>
                                                                                        <th>Código</th>
                                                                                        <th>Módulo</th>
                                                                                        <th>horas</th>
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
                                                        
                                                                </div>
                                                       
                                                                </div>





                                               
                                                            </div>
                                                      
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="box">
                                                        <div class="modal-header bg-primary">
                                                            <h4 class="modal-title"><B>Mover estudiantes</B></h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="Fmcurso" id="Fmcurso">
                                                            <input type="hidden" name="Fmcursocambio" id="Fmcursocambio">
                                                            <input type="hidden" name="Fmidsede" id="Fmidsede">
                                                            <input type="hidden" name="Fmidprograma" id="Fmidprograma">
                                                            <input type="hidden" name="Fmidhorario" id="Fmidhorario">
                                                            <input type="hidden" name="Fmidperiodo" id="Fmidperiodo">
                                                            <input type="hidden" name="Fmidestudiante" id="Fmidestudiante">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <label for="lblcurso">Curso:</label>
                                                                    <input type="text" name="FmCodigoCurso" id="FmCodigoCurso" class="form-control" readonly="" data-toggle="tooltip" title="Curso">
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <label for="lbHorario">Sede/Horario matriculado:</label>
                                                                    <input type="text" name="Fmhorario" id="Fmhorario" class="form-control" readonly="" data-toggle="tooltip" title="Horario">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <label for="lblcurso">Programa académico matriculado:</label>
                                                                    <input type="text" name="Fmprograma" id="Fmprograma" class="form-control" readonly="" data-toggle="tooltip" title="programa">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-8">
                                                                    <label for="LblNombre">Nombre estudiante :</label>
                                                                    <input type="text" name="Fmnombre" id="Fmnombre" class="form-control" readonly="" data-toggle="tooltip" title="Nombre estudiante">
                                                                </div>
                                                                <div class="col-md-4" style="top:25px;">
                                                                    <button id="btn_mover_estudiante" class="btn btn-success btn-sm btn-block" title="Mover estudiante" onclick="MoverEstudianteCruzado();">Mover</button>
                                                                </div>
                                                            </div>

                                                            <br>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <ul class="nav nav-tabs">
                                                                        <li class="active"><a data-toggle="tab" href="#Fmopcion1">Plan de
                                                                                estudio</a></li>
                                                                        <li><a data-toggle="tab" href="#Fmopcion2">Record Módulos</a></li>
                                                                    </ul>

                                                                    <div class="tab-content">
                                                                        <div id="Fmopcion1" class="tab-pane fade in active">
                                                                            <table class="table table-responsive table-striped table-condesed table-sm" id="tbl_plan_estudio_cruzados">
                                                                                <thead class="titulo">
                                                                                    <tr>
                                                                                        <th>Id</th>
                                                                                        <th>Módulo</th>
                                                                                        <th>Horas</th>

                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                        <div id="Fmopcion2" class="tab-pane fade">
                                                                            <table class="table table-responsive table-striped table-condesed table-sm" id="tbl_record_modulo_estudiante_cruzados">
                                                                                <thead class="titulo">
                                                                                    <tr>
                                                                                        <th>Id</th>
                                                                                        <th>Módulo</th>
                                                                                        <th>Périodo</th>
                                                                                        <th>Curso</th>
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
        </div>
    </div>
</div>

<!--modal para editar el estado de la inscripción del estudiante -->
<div class="modal fade" id="ModalEliminarModulo">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Retirar Módulo</B></h4>
            </div>
            <div class="modal-body">
                <form id="Eliminar-modulo" method="POST">
                    <div class="row">
                        <input type="hidden" id="txtidetalle" name="txtidetalle">

                        <section>
                            <p class="mensajeconfirm" id="mensajeconfirmacion" style="text-align:center"></p>
                        </section>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="EliminarModulo();">Aceptar</button>
            </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
</div>

<!-- modal para mover los estudiantes cruzados a un curso valiados -->
<div class="modal fade" id="ModalMoverEstudianteCruzado">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Mover estudiante de curso</B></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="FMestudiante" id="FMestudiante">
                <input type="hidden" name="FMcurso" id="FMcurso">
                <input type="hidden" name="FMcarga" id="FMcarga">
                <input type="hidden" name="FMdocente" id="FMdocente">
                <input type="hidden" name="FMmodulo" id="FMmodulo">
                <input type="hidden" name="FMencontrado" id="FMencontrado">
                <input type="hidden" name="FMnombreModulo" id="FMnombreModulo">
                <input type="hidden" name="FMperiodo" id="FMperiodo">

                <div class="row">
                    <section>
                        <p class="mensajeconfirm" id="MensajeMovercruzado" style="text-align:center">
                    </section>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label for="lblmodulo">Módulo programado curso destino:</label>
                        <select name="moduloprogramado" id="moduloprogramado" class="form-control">
                            <option value="">Seleccione módulo programado</option>
                        </select>
                    </div>
                </div>


                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="GuardarMoverEstudianteCruzado();">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- modal para la confirmar la matricula del modulo adicional-->
<div class="modal fade" id="ModalMatriculaModuloAdicional">
    <div class="modal-dialog" style="width:38vw">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Confirmar</B></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="MMestudiante" id="MMestudiante">
                <input type="hidden" name="MMmodulo"     id="MMmodulo">
                <input type="hidden" name="MMcurso"      id="MMcurso">
                <input type="hidden" name="MMperiodo"    id="MMperiodo">
                <input type="hidden" name="MMcarga"      id="MMcarga">
                <input type="hidden" name="MMdocente"    id="MMdocente">
             

                <div class="row">
                    <section>
                        <p class="mensajeconfirm" id="MensajeMatriculaModulo" style="text-align:center">
                    </section>
                </div>



                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="GuardarModuloAdicional();">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
</div>




    <script src="javascripts/gestionarotrasopciones.js"></script>
    <script src="javascripts/gestionarestudiantescruzados.js"></script>