<div class="content-wrapper">
    <div class="col-xs-12">
        <div id="opciones">
            <div class="box">
                <div class="row">
                    <div class="col-md-11">
                        <div class="box-header">
                            <h1 class="box-title">
                                <B>Reportes Carnetización:</B>
                            </h1>
                        </div>
                    </div>

                </div>

                <div class="box-body">
                    <section>
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-block btn-primary text-left" onclick="cambioVista('#opciones', '#ModalFormReporteEtapas', '#FormReporte-Etapas');">
                                    <i class="fa fa-file-text-o"> </i> Reporte resumen carnetización por etapas

                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-block btn-primary text-left" onclick="cambioVista('#opciones', '#ModalReporteEstado', '#FormReporteEstado');">
                                    <i class="fa fa-file-text-o"></i> Reporte resumen por estado
                                </button>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="box-body">
                    <section>
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-block btn-primary text-left" onclick="cambioVista('#opciones', '#ModalFormReporteFechas', '#FormReporte');">
                                    <i class="fa fa-file-text-o"> </i> Listado de carnetización por fechas

                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-block btn-primary text-left" onclick="cambioVista('#opciones', '#ModalReporteTipo', '#FormReporteTipo');">
                                    <i class="fa fa-file-text-o"></i> Listado de carnetización por Tipo
                                </button>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="box-body">
                    <section>
                        <div class="row">
                            <div class="col-md-12">
                                <button class="btn btn-block btn-success text-left" style="background-color: #00a65a; border-color: #008d4c;" onclick="cambioVista('#opciones', '#ModalReporteAcuses', '#FormReporteAcuses'); cargarTablaAcuses();">
                                    <i class="fa fa-envelope-o"></i> <b>Historial de Envíos de Correos y Acuses de Recibo</b> (Conf. Expresa / Silencio Positivo)
                                </button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>


    <div id="ModalFormReporteEtapas" style="display:none">
        <div class="col-xs-12">
            <div class="box">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box-header bg-default">
                            <h2 class="box-title">
                                <B>Reporte resumen carnetización por etapas </B>
                            </h2>
                        </div>
                    </div>

                </div>
                <form id="FormReporte-Etapas"><!--  method="POST" action="developer/ReportPdf/ReporteAgrupadoLote.php" -->
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Lote:</label>
                                <select id="lote_etapas" class="form-control select2" title="Seleccione lote.."></select>
                            </div>
                            <div class="col-md-2" style="top:25px">
                                <label style="cursor:pointer"><input style="cursor:pointer" type="Checkbox" name="chk" id="chk"> Todos los lotes</label>
                            </div>
                            <div class="col-md-3" style="top: 25px;">
                                <button type="button" class="btn btn-danger" onclick="cambioVista('#ModalFormReporteEtapas', '#opciones', '#FormReporte-Etapas');">Regresar</button>
                                <button type="button" title="Generar reporte" class="btn btn-success" onclick="ReporteAgrupadoLote()">Reporte</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div id="ModalFormReporteFechas" style="display:none">
        <div class="col-xs-12">
            <div class="box">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box-header bg-default">
                            <h2 class="box-title">
                                <B>Lista de carnetización por fechas </B>
                            </h2>
                        </div>
                    </div>

                </div>
                <form id="FormReporte"><!-- method="POST" action="developer/ReportPdf/ReporteporFecha.php"-->
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Desde la fecha:</label>
                                <input type=date name="fecha1" id="fecha1" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>Hasta la fecha:</label>
                                <input type=date name="fecha2" id="fecha2" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha2">Estado:</label>
                                <select id="estado_fecha" class="form-control" required="" title="Seleccione estado..">
                                    <option value="">Seleccione..</option>
                                    <option value="1">EN PROCESO</option>
                                    <option value="2">RECIBIDO</option>
                                    <option value="3">ENTREGADO</option>
                                </select>
                            </div>

                            <div class="col-md-3" style="top: 25px;">
                                <button type="button" class="btn btn-danger" onclick="cambioVista('#ModalFormReporteFechas', '#opciones', '#FormReporte');">Regresar</button>
                                <button type="button" title="Generar reporte" onclick="reporteRangoFecha()" class="btn btn-success">Reporte</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div id="ModalReporteEstado" style="display:none">
        <div class="col-xs-12">
            <div class="box">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box-header bg-default">
                            <h2 class="box-title">
                                <B>Reporte resumen de carnetización por estado</B>
                            </h2>
                        </div>
                    </div>

                </div>
                <form id="FormReporteEstado"><!-- method="POST" action="developer/ReportPdf/ReporteAgrupadoEstado.php"-->
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="estado">Estado carnet:</label>
                                <select id="estadoR_estado" class="form-control" required="" title="Seleccione estado..">
                                    <option value="">Seleccione..</option>
                                    <option value="1">EN PROCESO</option>
                                    <option value="2">RECIBIDO</option>
                                    <option value="3">ENTREGADO</option>
                                </select>
                            </div>
                            <div class="col-md-2" style="top:25px">
                                <label><input type="Checkbox" name="chkestado" id="chkestado" value="1"> Todos los estados:</label>
                                
                            </div>

                            <div class="col-md-3" style="top: 25px;">
                                <button type="button" class="btn btn-danger" onclick="cambioVista('#ModalReporteEstado', '#opciones', '#FormReporteEstado');">Regresar</button>
                                <button type="button" class="btn btn-success" title="Generar reporte" onclick="reporteEstado()">Reporte</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="ModalReporteTipo" style="display:none">
        <div class="col-xs-12">
            <div class="box">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box-header bg-default">
                            <h2 class="box-title">
                                <B>Lista de carnetización por tipo</B>
                            </h2>
                        </div>
                    </div>

                </div>
                <form id="FormReporteTipo"><!-- method="POST" action="developer/ReportPdf/ReporteAgrupadoEstado.php"-->
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="tipo">Tipo carnet:</label>
                                <select id="tipo_carnet" class="form-control" required="" title="Seleccione estado..">
                                    <option value="">Seleccione..</option>
                                    <option value="1">ESTUDIANTE</option>
                                    <option value="2">FUNCIONARIO</option>
                                    <option value="3">PRACTICANTE</option>
                                </select>
                            </div>
                            <div class="col-md-2" style="top:25px">
                                <label><input type="Checkbox" name="chkestado_tipo" id="chkestado_tipo" value="1"> Todos los tipos:</label>
                            </div>

                            <div class="col-md-3" style="top: 25px;">
                                <button type="button" class="btn btn-danger" onclick="cambioVista('#ModalReporteTipo', '#opciones', '#FormReporteTipo');">Regresar</button>
                                <button type="button" class="btn btn-success" onclick="reporteTipo()">Reporte</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="ModalReporteAcuses" style="display:none">
        <div class="col-xs-12">
            <div class="box">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box-header bg-default">
                            <h2 class="box-title">
                                <b>Historial de Envíos y Trazabilidad de Acuses de Recibo</b>
                            </h2>
                        </div>
                    </div>
                </div>
                <form id="FormReporteAcuses">
                    <div class="modal-body">
                        <div class="row" style="margin-bottom: 15px;">
                            <div class="col-md-3">
                                <label>Filtrar por Estado:</label>
                                <select id="filtro_estado_acuse" class="form-control" onchange="cargarTablaAcuses();">
                                    <option value="">TODOS</option>
                                    <option value="PENDIENTE">PENDIENTE</option>
                                    <option value="CONFIRMADO_EXPRESO">CONFIRMADO EXPRESO (Clic)</option>
                                    <option value="CONFIRMADO_TACITO">CONFIRMADO TÁCITO (Silencio Positivo)</option>
                                </select>
                            </div>
                            <div class="col-md-6" style="top: 25px;">
                                <button type="button" class="btn btn-default" onclick="cargarTablaAcuses();"><i class="fa fa-refresh"></i> Actualizar</button>
                                <button type="button" class="btn btn-danger" onclick="cambioVista('#ModalReporteAcuses', '#opciones', '#FormReporteAcuses');">Regresar</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tabla_acuses_recibo" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr class="bg-primary">
                                        <th>ID</th>
                                        <th>Destinatario</th>
                                        <th>Asunto</th>
                                        <th>Fecha Envío</th>
                                        <th>Estado Acuse</th>
                                        <th>Fecha Confirmación</th>
                                        <th>Detalles / IP</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_acuses_recibo">
                                    <tr>
                                        <td colspan="7" class="text-center">Cargando trazabilidad de correos...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <table id="tabla_reporte" class="table table-bordered" style="display:none">
        <thead></thead>
        <tbody></tbody>
    </table>

    <!-- Fin modal estudiantes  matriculados por programas academicos



    <script src="https://raw.githack.com/eKoopmans/html2pdf/master/dist/html2pdf.bundle.js"></script>-->
    <script src="javascripts/gestionarreportecarnetizacion.js?d=sdgfjhsgfjatfdhgafsdhghfdgafdadgfsjshgasdgafdhgfsahdfasdhfasdfhgfahgdsfhafdshgfshgdfhgsfdghafsdhgsfdsdfsfsfdsffgdfgdfdfdfsgfdsfdkfgsasd"></script>
</div>