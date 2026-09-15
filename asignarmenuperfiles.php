<div class="content-wrapper">
    <div class="row">
        <div class="col-xs-12">
            <div class="box" style="padding-bottom:10px;">

                <div class="row">
                    <div class="col-md-11">
                        <div class="box-header">
                            <h1 class="box-title"><B>Asignar menu > perfiles</B></h1>
                        </div>
                    </div>

           
                </div>

                <div class="box-body">
                    <h5 class="box-title"><B>Seleccione Rol:</B></h5>
                </div>
                <div class="input-group input-group-sm hidden-xs" style="width: 250px; padding-left:12px">
                    <select name="bRol" id="bRol" class="form-control pull-right">
                    </select>

                </div>
            </div>
        </div>
    </div>


    <section class="content">
        <div class="row">
        </div>
        <div class="row">
            <div class="col-xs-6">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Módulos habilitados:</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="tbl_moduloshabilitados" class="table table-bordered table-hover">

                            <thead class="titulo">
                                <tr>
                                    <th>Código</th>
                                    <th>Módulo</th>
                                    <th>Seleccionar</th>

                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Código</th>
                                    <th>Módulo</th>
                                    <th>Seleccionar</th>
                                </tr>
                            </tfoot>
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
                        <h3 class="box-title">Módulos Asignados/Seleccionados al Perfil</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="tbl_modulosasignados" class="table table-bordered table-hover">

                            <thead class="titulo">
                                <tr>
                                    <th>Código</th>
                                    <th>Módulo</th>
                                    <th>Seleccionar</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Código</th>
                                    <th>Módulo</th>
                                    <th>Seleccionar</th>
                                </tr>
                            </tfoot>
                        </table>
                        <!-- /.box-body -->
                    </div>
                </div>
                <!-- /.box -->
                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
        <div class="row">
            <div class="col-xs-6">
                <button type="button" title="Cancelar acción" class="btn btn-default pull-right" onclick="loadmenus();">
                    Cancelar</button>
                <!-- /.row -->
            </div>
            <div class="col-xs-6">
                <button type="button" title="Asignar Módulos" class="btn btn-primary pull-left" data-toggle="modal" id=btn-guardar
                    onclick="fGuardarConfig();">Actualizar Información</button>
            </div>
        </div>
    </section>
</div>


<script src="javascripts/asignarmenuperfiles.js"></script>