<?php

    include 'developer/security.php';
?>
<div class="row" >
    <div class="col-md-12">
        <h1 class="page-header">
            Mi perfil <small></small>
        </h1>
    </div>

    <style>
        .azul, .azul:hover{
            background: #225081;
            border: medium none;
            color: #fff;
        }

        .rojo, .rojo:hover{
            background: rgb(217, 83, 79);
            border: medium none;
            color: #fff;
        }
    </style>

    <!-- comienza contenido -->
    <div class="col-md-12">

        <form role="form" id="frm-imagen">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Imagen de Usuario
                </div>
                <div class="panel-body">
                    <div style="margin: 0 auto;width: 30%;display: block;overflow: hidden;">
                        <img src="<?php echo $_SESSION['foto']; ?>" class="img-circle" id="imagen" style="width: 115px;">
                        <div class="form-group"><br>
                            <input type="file" id="img-perfil" name="img-perfil"  accept="image/jpeg, image/png" style="float:left;" >
                        </div>
                        <div id="buttons" style="text-align: center; overflow: hidden;float: left;display:hidden"><br>
                            <button type="button" class="btn btn-primary" onclick="uploadPhoto();">Guardar foto</button>
                            <button type="button" id="elifoto" name="elifoto" class="btn">Cancelar</button>
                        </div>

                    </div>
                </div>
            </div>
        </form>
        <div class="panel panel-default">
            <div class="panel-heading">
                Datos Usuario
            </div>
            <div class="panel-bodyB">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email: </label>
                            <input class="form-control" id="txtUsuario" name="txtUsuario" value="<?php echo $_SESSION['usuario']; ?>" disabled/>
                        </div>
                        <a onclick="$('#mContrasena').modal('show');"> Cambiar contraseña</a>
                    </div>
                </div>
            </div>
            <div class="panel-heading">
                Datos Personales
            </div>
            <div class="panel-bodyB">
                <div class="row">
                    <div class="col-md-6">
                        <input type="hidden" id="codusuario" name="codusuario" value="<?php echo $_SESSION['codigo_usu']; ?>">
                        <div class="form-group" >
                            <label>Nombre: </label>
                            <input class="form-control" id="txtNombre" name="txtNombre" value="<?php echo $_SESSION['nombre_usu']; ?>"/>
                        </div>
                    </div>
                    <div class="col-md-12" style="text-align:center;">
                        <button type="button" class="btn btn-primary" style="margin-top: 16px;" onclick="fEditarItem();">Guardar cambios</button>
                    </div>
                </div>
            </div>   
        </div>
    </div>

    <!-- MODAL -->

    <div class="modal fade" id="mContrasena" tabindex="-1" role="dialog" aria-labelledby="mContrasena" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><strong>Cambiar contraseña</strong></h4>
                </div>
                <div class="modal-body">
                    <!-- BEGIN FORM-->
                    <form id="frmChangePassword" method="POST" class="form-horizontal">
                        <div class="form-body rowB">
                            <div class="form-group">
                                <label class="control-label col-md-4">Contraseña actual:
                                    <span class="required">
                                         *
                                    </span>
                                </label>
                                <div class="col-md-6">
                                    <input type="password" name="contraActual" id="contraActual" class="form-control"  required="true" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-4">Nueva contraseña: 
                                    <span class="required">
                                         *
                                    </span>
                                </label>
                                <div class="col-md-6">
                                    <input type="password" name="nuevaContra" id="nuevaContra" class="form-control"  required="true" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-4">Confirmar nueva contraseña: 
                                    <span class="required">
                                         *
                                    </span>
                                </label>
                                <div class="col-md-6">
                                    <input type="password" name="confirmContra" id="confirmContra" class="form-control"  required="true" />
                                </div>
                            </div>
                            
                        </div>
                    </form>
                    <!-- END FORM-->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="fContrasena();" id="btn-contrasena">Guardar</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- FIN MODALS -->
</div>



<script>
    $(document).ready(function () {
        $("#buttons").hide();
        
        /*/////////////////////////////////////////////////////////////////*/
        $("input[name='img-perfil']").on("change",function(event){
            if($("#img-perfil").val()!=''){
                $("#buttons").show();
            }else{
                $("#buttons").hide();
            }
            fotografiav = $(this).val();
            var tmppath = URL.createObjectURL(event.target.files[0]);
            $("#imagen").attr('src', tmppath);
        });
    });

    /*////////////////////////////////////////////////////////////////*/
    $("#elifoto").on("click",function(){
        $("#img-perfil").val('');
        $("#buttons").hide();
        $("#imagen").attr('src','<?php echo $_SESSION["foto"]; ?>');
    });
    
    /*/////////////////////////////////////////////////////////////////*/
    function fEditarItem(){
        var codpersona = $("#codpersona").val();
        var codusuario = $("#codusuario").val();
        var txtNombre = $("#txtNombre").val();

        if($.trim(txtNombre) == ''){
            toastr.error('Por favor, ingrese Nombre.');
            $("#txtNombre").focus();

        }else{

            $.ajax({
                url : "developer/Lopersa/editUsuario",
                type : "POST",
                data : {
                    'codusuario': codusuario,
                    'nombre' : txtNombre
                },
                dataType : "JSON",
                success : function (json){
                    if(json.success == true){
                        toastr.success("Item Actualizado exitosamente");
                        $("#nombre-usuario").html(txtNombre);
                    }else{
                        toastr.error(json.mensaje);
                    }
                }
            });
        }

    }

    /*/////////////////////////////////////////////////////////////////*/
    function uploadPhoto(){
        var formData= new FormData($("#frm-imagen")[0]);
        // alert("otroform:"+formData);
        // alert("foto "+$("#fotografia").val());

        $.ajax({
            url: "developer/Lopersa/uploadFotoPerfil?nombphoto="+$("#codusuario").val()+'_'+$("#txtUsuario").val(),
            type:"POST",
            data:formData,
            contentType: false,
            processData: false,
            dataType:"JSON",
            success: function(json){
                if(json.success==true){
                    $("#img-perfil").val('');
                    $("#buttons").hide();
                    toastr.success("Foto actualizada exitosamente");
                    location.reload();
                }else{
                    toastr.error(json.mensaje);
                }
            },
            error: function(json){
                console.log(json);
            }
        }); 
    }

    /*/////////////////////////////////////////////////////////////////*/
    function fContrasena(){

        var codusuario = $("#codusuario").val();
        var contraActual = $("#contraActual").val();
        var nuevaContra = $("#nuevaContra").val();
        var confirmContra = $("#confirmContra").val();
        if($.trim(contraActual) == ''){
            toastr.error('Por favor, ingrese Contraseña actual.');
            $("#contraActual").focus();

        }else if($.trim(nuevaContra) == ''){
            toastr.error('Por favor, ingrese Nueva contraseña.');
            $("#nuevaContra").focus();

        }else if($.trim(confirmContra) == ''){
            toastr.error('Por favor, confirme Nueva contraseña.');
            $("#confirmContra").focus();

        }else if($.trim(nuevaContra) != $.trim(confirmContra)){
            toastr.error('Las contraseñas ingresadas no coinciden');
            $("#txtTelefono").focus();

        }else{
            $("#btn-contrasena").attr('disabled',true);
            $.ajax({
                url : "developer/Lopersa/editContrasena",
                type : "POST",
                data : {
                    'codusuario': codusuario,
                    'contraActual' : contraActual,
                    'nuevaContra' :  nuevaContra
                },
                dataType : "JSON",
                success : function (json){
                    if(json.success == true){
                        $("#frmChangePassword")[0].reset();
                        $('#mContrasena').modal('hide');
                        toastr.success("Contraseña Actualizada exitosamente");
                        $("#btn-contrasena").attr('disabled',false);
                    }else{
                        toastr.error(json.mensaje);
                        $("#btn-contrasena").attr('disabled',false);
                    }
                }
            });
        }
    }

</script>

