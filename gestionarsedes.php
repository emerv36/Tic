<div class="content-wrapper">
    <div class="col-xs-12">
        <div class="box">
            <div class="row">
                <div class="col-md-11">
                    <div class="box-header">
                        <h1 class="box-title">Registro de Sedes:</h1>
                    </div>
                </div>

               </div>
            <div class="box-body">

                <div style="display: flex; justify-content: space-between; margin-bottom: 18px; padding: 12px 24px;">
                    <div class="input-group input-group-sm hidden-xs" style="width: 250px;">
                    </div>
                    <button type="button" class="btn btn-primary" data-toggle="modal" onclick="ModalRegistrarSedes();">
                        Nueva Sede
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive ">

                <table class="table table-hover table-striped table-condesed" id="tbl_sedes" width="100%">

                    <thead class="titulo">
                        <tr>
                            <th>No.</th>
                            <th>Nombre sede</th>
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

<!--modal editar perfil-->
<div class="modal fade" id="editar-sedes">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Editar Sede</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="Editar-Sedes" method="POST">
                        <input class="form-control" type="hidden" id="id_sede" name="id_sede">
                    
                        <div class="input-item col-xs-12">
                            <label for="txtnombresede">Nombre Sede
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="text" id="nombre_sede" name="nombre_sede">
                        </div>
                  
                     
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="EditarSedes();">Guardar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>
<!--fin modal editar perfil -->
<div class="modal fade" id="registrar-sede">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Nueva sede</h4>
            </div>
            <!-- modal registrar perfil-->
            <div class="modal-body">
                <div class="row">
                    <form id="Registrar-Sedes" method="POST">
                  
                        <div class="input-item col-xs-12">
                            <label for="txtRegistraSede">Nombre Sede:
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="text" id="Rnombre_sede" name="Rnombre_sede">
                        </div>                    
                                         
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="RegistrarSedes();">Guardar</button>
            </div>
        </div>
        <!--fin modal registrar perfil-->
    </div>
    <!-- /.modal-dialog -->
</div>
<!--modal para editar el estado de la inscripción del estudiante -->
<div class="modal fade" id="modalconfirmar">
    <div class="modal-dialog">
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
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmar-ok" onclick="acceptConfirm()">Aceptar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>

<script src="javascripts/gestionarsedes.js"></script>