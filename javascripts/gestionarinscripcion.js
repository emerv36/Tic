$(document).ready(function() {
    combobox('REmpresaPracticante', 'Willie/Programas/CargarEmpresas', 'Seleccione una opción.');
    
    combobox('txtRegistroTipoIdentificacion', 'Marge/Inscripcion/CargarTipoIdentificacion', 'Seleccione Tipo..');
    combobox('txttipoidentificacion', 'Marge/Inscripcion/CargarTipoIdentificacion', 'Seleccione Tipo..');
    combobox('txtidentidad', 'Marge/Inscripcion/CargarTipoIdentificacion', 'Seleccione Tipo..');
    
    combobox('txtsede', 'Marge/Inscripcion/CargarSedeInscripcion', 'Seleccione sede...');
    combobox('RSelectSedePrograma', 'Marge/Inscripcion/CargarSedeInscripcion', 'Seleccione sede...');
    combobox('RSelectSedeCargo', 'Marge/Inscripcion/CargarSedeInscripcion', 'Seleccione sede...');
    combobox('txtRegistroSede', 'Marge/Inscripcion/CargarSedeInscripcion', 'Seleccione sede...');
    combobox('txtpsede', 'Marge/Inscripcion/CargarSedeInscripcion', 'Seleccione sede...');
    
    combobox('txtRegistroNovedad', 'Marge/Inscripcion/CargarObservacion', 'Seleccione observación...');
    combobox('txtnovedad', 'Marge/Inscripcion/CargarObservacion', 'Seleccione observación...');
    combobox('txtlote', 'Marge/Inscripcion/CargarLotes', 'Seleccione lote...');
    combobox('txtclote', 'Marge/Inscripcion/CargarLotes', 'Seleccione lote...');

    $("#BtnRecidibo").attr("Disabled", "Disabled");
    
    //mascar de entrada para los telefonos
    $('[data-mask]').inputmask();

    $('[data-toggle="tooltip"]').tooltip();

    // Actualización en tiempo real del mockup del carnet
    function actualizarPreview() {
        var nombre = $('#txtRegistroNombreEstudiante').val() + ' ' + $('#txtRegistroApellidoEstudiante').val();
        $('#preview-nombre').text(nombre.trim() !== '' ? nombre : '---');
        var id = $('#txtRegistroIdentificacion').val();
        $('#preview-identificacion').text(id !== '' ? id : '---');
        
        var programa = $('#txtRegistroPrograma').val() ? $('#txtRegistroPrograma option:selected').text() : '---';
        $('#preview-programa').text(programa);
        
        var sede = $('#txtRegistroSede').val() ? $('#txtRegistroSede option:selected').text() : '---';
        $('#preview-sede').text(sede);
        
        var rh = $('#txtRegistroTipoSangre').val() ? $('#txtRegistroTipoSangre option:selected').text() : '---';
        $('#preview-rh').text(rh);

        // Ajuste de mockup según categoría
        var categoria = $('#txtRegistroCategoriaCarnet').val();
        var mockup = $('#preview-carnet-mockup');
        if (categoria == '3') { // Practicante
            mockup.removeClass('estudiantil-bg').addClass('practicante-bg vertical');
        } else {
            mockup.removeClass('practicante-bg vertical').addClass('estudiantil-bg');
        }
    }

    $('#txtRegistroNombreEstudiante, #txtRegistroApellidoEstudiante, #txtRegistroIdentificacion').on('keyup change', actualizarPreview);
    $('#txtRegistroPrograma, #txtRegistroSede, #txtRegistroTipoSangre, #txtRegistroCategoriaCarnet').on('change', actualizarPreview);

}); // cierre del document
function eliminarRegistro(){
    var id_EliminarRegistro = $("#id_EliminarRegistro").val()
    console.log(id_EliminarRegistro)
    $.ajax({
        url: "Marge/Inscripcion/eliminarRegistro",
        type: "POST",
        data: {
            'id_eliminar': id_EliminarRegistro
        },
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                toastr.success(json.mensaje);
                BusquedaEstudiantes();
                $("#modalEliminarRegistro").modal("hide");
            } else {
                mensaje("¡Advertencia!", json.mensaje, "error");
    
            }
        }
    });
}

function modalEliminarRegistro(id){
    $("#id_EliminarRegistro").val(id);
    $("#modalEliminarRegistro").modal("show");
}

function modalPrograma(){
    $("#modalNUevoPrograma").modal("show");
    $("#Registrar-Programas")[0].reset();
    $("#RSelectSedePrograma").val();
    
    if($("#txtpsede").val() != ''){
        $("#RSelectSedePrograma").val($("#txtpsede").val());
    } else {
        if($("#txtRegistroSede").val() != ''){
            $("#RSelectSedePrograma").val($("#txtRegistroSede").val());
        }
    }
}

function modalCargo(){
    $("#modalNUevoCargo").modal("show");
    $("#Registrar-Cargos")[0].reset();
    
    if($("#txtpsede").val() != ''){
        $("#RSelectSedeCargo").val($("#txtpsede").val());
    } else {
        if($("#txtRegistroSede").val() != ''){
            $("#RSelectSedeCargo").val($("#txtRegistroSede").val());
        }
    }
}

function RegistrarInfoProgramas() {
    var RnombrePrograma = $("#RnombrePrograma").val();
    var RcodigoPrograma = $("#RcodigoPrograma").val();
    var RSelectSedePrograma = $("#RSelectSedePrograma").val();
    var RestadoPrograma = $("#RestadoPrograma").val();
    var categoria = $("#txtpcategoria").val();

    if ($.trim(RnombrePrograma) == '') {
        toastr.error("Ingrese nombre del programa.");
        $("#RnombrePrograma").focus();
    } else if ($.trim(RcodigoPrograma) == '') {
        toastr.error("Ingrese código del programa.");
        $("#RcodigoPrograma").focus();

    } else if ($.trim(RSelectSedePrograma) == '') {
        toastr.error("Seleccione sede");
        $("#RSelectSedePrograma").focus();

    } else if ($.trim(RestadoPrograma) == '') {
        toastr.error("Seleccione el estado");
        $("#RestadoPrograma").focus();
    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Willie/Programas/RegistrarInfoProgramas",
            type: "POST",
            data: {
                'RnombrePrograma': RnombrePrograma,
                'RcodigoPrograma': RcodigoPrograma,
                'RSelectSedePrograma': RSelectSedePrograma,
                'RestadoPrograma': RestadoPrograma,
            },
            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (json.success == true) {
                    toastr.success("Información registrada");
                    $("#modalNUevoPrograma").modal("hide");
                    $("#Registrar-Programas")[0].reset();
                    
                    combobox('txtpprograma', 'Marge/Inscripcion/CargarProgramas?sedes=' + RSelectSedePrograma + '&categoria=' + categoria + '&tiposede=1', 'Seleccione ...');
                    combobox('txtRegistroPrograma', 'Marge/Inscripcion/CargarProgramas?sedes=' + RSelectSedePrograma + '&categoria=' + categoria + '&tiposede=1', 'Seleccione ...');
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "error");

                }
            }
        });
    }
}

function RegistrarCargo() {
    var RnombreCargo = $("#RnombreCargo").val();
    var RSelectSedeCargo = $("#RSelectSedeCargo").val();
    var categoria = $("#txtpcategoria").val();

    if ($.trim(RnombreCargo) == '') {
        toastr.error("Ingrese nombre del argo.");
        $("#RnombreCargo").focus();
    } else if ($.trim(RSelectSedeCargo) == '') {
        toastr.error("Seleccione sede.");
        $("#RSelectSedeCargo").focus();

    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Willie/Programas/RegistrarCargos",
            type: "POST",
            data: {
                'RnombreCargo': RnombreCargo,
                'RSelectSedeCargo': RSelectSedeCargo,
            },
            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (json.success == true) {
                    toastr.success("Información registrada");
                    $("#modalNUevoCargo").modal("hide");
                    $("#Registrar-Cargos")[0].reset();
                    combobox('txtpprograma', 'Marge/Inscripcion/CargarProgramas?sedes=' + RSelectSedeCargo + '&categoria=' + categoria + '&tiposede=1', 'Seleccione ...');
                    combobox('txtRegistroPrograma', 'Marge/Inscripcion/CargarProgramas?sedes=' + RSelectSedeCargo + '&categoria=' + categoria + '&tiposede=1', 'Seleccione ...');
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "error");
                }
            }
        });
    }
}


$("#txtRegistroSede").change(function() {
    var sedes = $("#txtRegistroSede").val();
    var categoria = $("#txtRegistroCategoriaCarnet").val(); 
    if (categoria == '1' || categoria == '2'){
        tiposede = '1';
        $("#btn_agregar_programa_cargo").show();
    } else {
        tiposede = '1';
        sedes = '1';
        categoria = '1';
        $("#btn_agregar_programa_cargo").hide();
    }
    combobox('txtRegistroPrograma', 'Marge/Inscripcion/CargarProgramas?sedes=' + sedes + '&categoria=' + categoria + '&tiposede=' + tiposede, 'Seleccione ...');
});

$("#txtpsede").change(function() {
    var sedes = $("#txtpsede").val();
    var categoria = $("#txtpcategoria").val();
    if (categoria == '1' || categoria == '2'){
        $("#btn_agregar_programa_cargo").show();
        tiposede = '1';
    } else {
        tiposede = '1';
        sedes = '1';
        categoria = '1';
        $("#btn_agregar_programa_cargo").hide();
    }
    combobox('txtpprograma', 'Marge/Inscripcion/CargarProgramas?sedes=' + sedes + '&categoria=' + categoria + '&tiposede=' + tiposede, 'Seleccione ...');
});

$("#txtRegistroCategoriaCarnet").change(function() {
    var valor = $("#txtRegistroCategoriaCarnet").val();
    if (valor == 1) {
        $("#RegistroSede").html("Sede:");
        $("#LblRprograma").html("Programa:");
        $("#btn_agregar_programa_cargo").attr('onclick','modalPrograma()');
        combobox('txtRegistroSede', 'Marge/Inscripcion/CargarSedeInscripcion', 'Seleccione sede...');
    } else if (valor == 2) {
        $("#RegistroSede").html("Sede:");
        $("#LblRprograma").html("Cargo:");
        $("#btn_agregar_programa_cargo").attr('onclick','modalCargo()');
        combobox('txtRegistroSede', 'Marge/Inscripcion/CargarSedeInscripcion', 'Seleccione sede...');
    } else if (valor == 3) {
        $("#RegistroSede").html("Empresa:");
        $("#LblRprograma").html("Programa practicante:");
        $("#btn_agregar_programa_cargo").attr('onclick','');
        combobox('txtRegistroSede', 'Marge/Inscripcion/CargarEmpresaInscripcion', 'Seleccione empresa...');
    }
});

function modalCargoPracticante(){
    $("#form_registrar_cargo_practicante")[0].reset();
    $("#modal_registrar_cargo_practicante").modal("show");
}

function registrarCargoPracticante() {
    var RCargoPracticante = $("#RCargoPracticante").val();
    var REmpresaPracticante = $("#REmpresaPracticante").val();

    if ($.trim(RCargoPracticante) == '') {
        toastr.error("Ingrese nombre del argo.");
        $("#RCargoPracticante").focus();
    } else if ($.trim(REmpresaPracticante) == '') {
        toastr.error("Seleccione sede.");
        $("#REmpresaPracticante").focus();

    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Willie/Programas/RegistrarCargoPracticante",
            type: "POST",
            data: {
                'cargo_practicante': RCargoPracticante,
                'empresa_practicante': REmpresaPracticante,
            },
            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (json.success == true) {
                    toastr.success("Información registrada");
                    $("#modal_registrar_cargo_practicante").modal("hide");
                    $("#form_registrar_cargo_practicante")[0].reset();
                    combobox('txtRegistroSede', 'Marge/Inscripcion/CargarEmpresaInscripcion', 'Seleccione empresa...');
                    combobox('txtpsede', 'Marge/Inscripcion/CargarEmpresaInscripcion', 'Seleccione empresa...');
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "error");
                }
            }
        });
    }
}

$("#txtpcategoria").change(function() {
    var valor = $("#txtpcategoria").val();
    if (valor == 1) {
        $("#txtsede").html("Sede:");
        $("#LblEprograma").html("Programa:");
        $("#btn_agregar_programa_cargo__").attr('onclick','modalPrograma()');
        combobox('txtpsede', 'Marge/Inscripcion/CargarSedeInscripcion', 'Seleccione sede...');
    } else if (valor == 2) {
        $("#txtsede").html("Sede:");
        $("#LblEprograma").html("Cargo:");
        $("#btn_agregar_programa_cargo__").attr('onclick','modalCargo()');
        combobox('txtpsede', 'Marge/Inscripcion/CargarSedeInscripcion', 'Seleccione sede...');
    } else if (valor == 3) {
        $("#txtsede").html("Empresa:");
        $("#LblEprograma").html("Programa practicante:");
        $("#btn_agregar_programa_cargo__").attr('onclick','');
        combobox('txtpsede', 'Marge/Inscripcion/CargarEmpresaInscripcion', 'Seleccione empresa...');
    }
});

$("#txtRegistroPrograma").change(function() {
    combobox('txtRegistroLote', 'Marge/Inscripcion/CargarLotes', 'Seleccione lote...');
});

$("#txtcmblote").change(function() {
    $("#Cresultado").hide();
    $("#BtnRecidibo").attr("Disabled", "Disabled");
});

// funcion para la carga de lotes seguin el tipo de etapa
$("#txtcmbetapa").change(function() {
    var etapa = $("#txtcmbetapa").val();
    if (etapa == 1) {
        combobox('txtcmblote', 'Marge/Inscripcion/CargarLotesEtapaPractica', 'Seleccione lote...');

        document.getElementById("txtcmblote").disabled = false;
    } else if (etapa == 2) {
        combobox('txtcmblote', 'Marge/Inscripcion/CargarLotesEtapaLectiva', 'Seleccione lote...');
        document.getElementById("txtcmblote").disabled = false;
    } else if (etapa == 3) {
        combobox('txtcmblote', 'Marge/Inscripcion/CargarMisLotes', 'Seleccione lote...');
        document.getElementById("txtcmblote").disabled = false;
    } else if (etapa == 4) {
        document.getElementById("txtcmblote").disabled = true;

    }


});

$("#txtRegistroIdentificacion").change(function() {
    var txtRegistroIdentificacion = $("#txtRegistroIdentificacion").val();
    var swemail = $("#swemail").val();
    if (txtRegistroIdentificacion.length > 0) {
        $.ajax({
            url: "Marge/Inscripcion/BuscarInfoEstudiantes",
            type: "POST",
            data: { 'txtRegistroIdentificacion': txtRegistroIdentificacion },
            dataType: "JSON",
            success: function(json) {
                if (json.success === true) {

                    swal({
                        title: "Información",
                        text: "Estudiante" + " " + json.nombre_estudiante + " " + json.apellido_estudiante + " " + ", se encuentra registrado(a) en nuestra base de datos, ¿Desea realizar una nueva Inscripcion?",
                        icon: "success",
                        closeOnClickOutside: false,
                        closeOnEsc: false,
                        allowOutsideClick: false,
                        dangerMode: true,
                        buttons: [
                            'No',
                            'Si'
                        ],
                        dangerMode: true,
                    }).then(function(isConfirm) {
                        if (isConfirm) {
                            $("#swemail").val('1') //1 valor estaudiante antiguo
                            $("#txtRegistroNombreEstudiante").val(json.nombre_estudiante);
                            $("#txtRegistroApellidoEstudiante").val(json.apellido_estudiante);
                            $("#txtRegistroEmail").val(json.email_estudiante);
                            $("#txtRegistroConfirmarEmail").val(json.email_estudiante);
                            $("#txtRegistroTipoIdentificacion").val(json.tipo_identidad);
                            $("#txtRegistroCelular").val(json.celular_estudiante);
                            $("#txtRegistroTelefono").val(json.telefono_estudiante);
                            $("#txtRegistroBarrio").val(json.barrio_estudiante);
                            $("#txtRegistroFechaNacimiento").val(json.fechanacimiento);
                            $("#txtRegistroDireccion").val(json.direccion_estudiante);
                            $("#txtRegistroCiudad").val(json.ciudad_estudiante);
                            $("#txtRegistroColegio").val(json.id_colegiofk);
                            $("#txtRegistroEmail").val(json.email_estudiante);
                            $("#txtRegistroConfirmarEmail").val(json.email_estudiante);
                            $("#txtRegistroNivel").val(json.nivel_academico);
                            //    $("#txtRegistroGraduado").val(json.graduado);
                            //     $("#txtRegistroUltimoA").val(json.ultimoanio);
                            //        $("#txtRegistroUltimoNivel").val(json.ultimo_nivel_aprobado);
                            $("#txtRegistroEstadoCivil").val(json.estado_civil);
                            $("#txtRegistroDiscapacidad").val(json.discapacidad);
                            $("#txtRegistroMulticultura").val(json.multicultura);
                            $("#txtRegistroTipoSangre").val(json.tipo_sangre);
                            $("#txtRegistroHijos").val(json.numero_hijo);
                            $("#txtRegistroEstrato").val(json.estrato);
                            $("#txtRegistroTransporte").val(json.transporte);
                            $("#txtregistroZona").val(json.zona);
                            $("#txtRegistroOcupacion").val(json.ocupacion);
                            $("#txtRegistroEps").val(json.eps);
                        } else {
                            $("#Registrar-Inscripcion")[0].reset();
                            $("#txtRegistroIdentificacion").focus();
                        }
                    });

                } else {
                    $("#swemail").val('2') //1 valor estudiante nuevo
                    toastr.success(json.mensaje);
                }
            }
        });
    }
});

$("#txtRegistroEmail").blur(function() {
    var mail = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
    var txtRegistroEmail = $("#txtRegistroEmail").val();

    if (txtRegistroEmail.length !== 0) {
        if (!mail.test($.trim(txtRegistroEmail))) {
            toastr.error("Ingrese un correo electrónico valido");
            $("#txtRegistroEmail").focus();

        } else {

            $.ajax({
                url: "Marge/Inscripcion/ValidarEmailEstudiante",
                type: "POST",
                data: { 'txtRegistroEmail': txtRegistroEmail },
                dataType: "JSON",
                success: function(json) {
                    if (json.success === true) {
                        mensaje("¡Advertencia!", json.mensaje, "warning");
                        $("#txtRegistroEmail").val('');
                        $("#txtRegistroEmail").focus();

                    } else {

                        toastr.success(json.mensaje);
                        $("#txtRegistroConfirmarEmail").focus();
                    }
                }
            });
        }
    }
});


//función para cargar el modal de registro de estudiantes
function ModalRegistrarInscripcion() {
    if ($("#txtRegistroPerfil").length === 0) {
        swal("¡Advertencia!", "Antes de crear los registros de inscripciones de estudiantes, es necesario crear el perdil de ESTUDIANTE y los ROLES correspondinte, consulte con el administrador del sistema", "warning");
    } else {
        $("#Registrar-Inscripcion")[0].reset();
        $("#registrar-inscripcion").modal("show");
    }
}
//fin función para cargar el modal de registro de estudiantes


function CargarEditarInscripcion(idinscripcion, identificacion, tipo_identificacion, nombre, apellido, email, celular, tipo_sangre, lote, indicador, codigo_foto, chip, observacion) {
    $("#Editar-Inscripcion")[0].reset();
    $("#txtidinscripcion").val(idinscripcion);
    $("#txtidentificacion").val(identificacion);
    $("#txttipoidentificacion").val(tipo_identificacion);
    $("#txtnombre").val(nombre);
    $("#txtapellido").val(apellido);
    $("#txtemail").val(email);
    $("#txtcelular").val(celular);
    $("#txttiposangre").val(tipo_sangre);
    $("#txtlote").val(lote);
    $("#txtindicador").val(indicador);
    $("#txtcodigofoto").val(codigo_foto);
    $("#txtchip").val(chip);
    $("#txtnovedad").val(observacion);
    $("#ModalEditarInscipcion").modal("show");
}
//fin funcion 

function ModalActivarChip(id, identificacion) {
    $("#ACidinsripcion").val(id);
    $("#ACidentificacion").val(identificacion);
    $("#txtActivarChipEscaneo").val('');
    $("#ModalActivarChip").modal("show");
    
    setTimeout(function() {
        $("#txtActivarChipEscaneo").focus();
    }, 500);
}

$(document).on('keypress', '#txtActivarChipEscaneo', function(e) {
    if(e.which == 13) {
        GuardarActivacionChip();
    }
});

function GuardarActivacionChip() {
    var id = $("#ACidinsripcion").val();
    var rfid = $.trim($("#txtActivarChipEscaneo").val());

    if (!id || $.trim(id) === '') {
        toastr.error("Error interno: no se identificó la inscripción. Cierre el modal y vuelva a intentarlo.");
        return;
    }

    if (rfid === '') {
        toastr.error("Por favor escanee o escriba el código de la tarjeta primero.");
        $("#txtActivarChipEscaneo").focus();
        return;
    }

    var $btn = $("#btn-activar-chip");
    $btn.prop('disabled', true).text('Guardando...');

    $.ajax({
        url: "Marge/Inscripcion/ActivarChip",
        type: "POST",
        data: {
            'id_inscripcion': id,
            'rfid': rfid
        },
        dataType: "JSON",
        success: function(json) {
            $btn.prop('disabled', false).text('Vincular Chip');
            if (json.success == true) {
                toastr.success(json.mensaje);
                BusquedaEstudiantes();
                $("#ModalActivarChip").modal("hide");
            } else {
                mensaje("¡Advertencia!", json.mensaje || 'No fue posible vincular el chip.', "error");
                $("#txtActivarChipEscaneo").val('').focus();
            }
        },
        error: function(xhr) {
            $btn.prop('disabled', false).text('Vincular Chip');
            var msg = 'Error de conexión al guardar el chip.';
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp && resp.mensaje) msg = resp.mensaje;
            } catch(e) {}
            mensaje("¡Error!", msg + ' (HTTP ' + xhr.status + ')', "error");
        }
    });
}

//funcion cargar el modal de entrega de carnet
function ModalEntregaCarnet(idInscripcion) {
    $("#ECidinsripcion").val(idInscripcion);
    $('#ModalEntregaCarnet').modal("show");
}


//funcion modal para realizar cambios en el carnet
function ModalRelizarCambios(idInscripcion) {
    $("#RCidinscripcion").val(idInscripcion);
    $('#ModalRelizarCambios').modal("show");
}

//funcion modal para realizar cambios en el carnet
function ModalCambiarLote(idInscripcion, lote) {
    $("#txtclidinscripcion").val(idInscripcion);
    $("#txtclote").val(lote);
    $('#ModalCambiarLote').modal("show");
}


//funcion para cambiar de programa y sede
function ModalCambiarPrograma(idinscricion, nsede, nprograma, ncategoria) {
    $("#txtPidinscripcion").val(idinscricion);
    $("#txtnsede").html(nsede)
    $("#txtnprograma").html(nprograma);
    $("#txtncategoria").html(ncategoria);
    $('#ModalCambiarPrograma').modal("show");
}


//funcion para cambiar de programa y sede
function modalcambiarrecibido(id, nombre, apellido) {
    $("#Linscripcion").val(id);
    $('#mensajeconfirma').html("¿Desea cambiar al estado del carnet como REALIZADO. ?<br><strong>" + apellido + ' ' + nombre + "</strong><br> Enviaremos un mensaje de notificación a su cuenta de correo electrónico")
    $('#modalcambiarrecibido').modal("show");
}


//funcion para cambiar de programa y sede
function modalcambiarentregado(id, nombre, apellido) {
    $("#Linscripcion").val(id);
    $('#mensajeconfirmaentregado').html("¿Desea cambiar al estado del carnet como ENTREGADO.?<br><strong>" + apellido + ' ' + nombre + "</strong><br>Recuerde firmar la planilla de registro como entregado <br> Enviaremos un mensaje de notificación a su cuenta de correo electrónico")
    $('#modalcambiarentregado').modal("show");
}


//función para la actualizacion de la informacion del estudiante
function GuardarInscripcion() {
    var mail = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
    var txtRegistroIdentificacion = $("#txtRegistroIdentificacion").val();
    var txtRegistroTipoIdentificacion = $("#txtRegistroTipoIdentificacion").val();
    var txtRegistroNombreEstudiante = $("#txtRegistroNombreEstudiante").val();
    var txtRegistroApellidoEstudiante = $("#txtRegistroApellidoEstudiante").val();
    var txtRegistroEmail = $("#txtRegistroEmail").val();
    var txtRegistroCelular = $("#txtRegistroCelular").val();
    var txtRegistroTipoSangre = $("#txtRegistroTipoSangre").val();
    var txtRegistroSede = $("#txtRegistroSede").val();
    var txtRegistroPrograma = $("#txtRegistroPrograma").val();
    var txtRegistroLote = $("#txtRegistroLote").val();
    var txtRegistroIndicador = $("#txtRegistroIndicador").val();
    var txtRegistroNovedad = $("#txtRegistroNovedad").val();
    var txtRegistroCategoriaCarnet = $("#txtRegistroCategoriaCarnet").val();
    var txtLugarReclamo = $("#txtLugarReclamo").val();

    if ($.trim(txtRegistroIdentificacion) == '') {
        toastr.error("Ingrese identificación.");
        $("#txtRegistroIdentificacion").focus();

    } else
    if ($.trim(txtRegistroTipoIdentificacion) == '') {
        toastr.error("Seleccione el tipo de identificación.");
        $("#txtRegistroTipoIdentificacion").focus();

    } else if ($.trim(txtRegistroNombreEstudiante) == '') {
        toastr.error("Ingrese Nombre.");
        $("#txtRegistroNombreEstudiante").focus();

    } else if ($.trim(txtRegistroApellidoEstudiante) == '') {
        toastr.error("Ingrese apellido.");
        $("#txtRegistroApellidoEstudiante").focus();

    } else if ($.trim(txtRegistroEmail) == '') {
        toastr.error("Ingrese email estudiante.");
        $("#txtRegistroEmail").focus();

    } else if (!mail.test($.trim(txtRegistroEmail))) {
        toastr.error("Ingrese un correo electrónico valido");
        $("#txtRegistroEmail").focus();

    } else if ($.trim(txtRegistroCelular) == '') {
        toastr.error("Ingrese número celular.");
        $("#txtRegistroCelular").focus();

    } else if ($.trim(txtRegistroTipoSangre) == '') {
        toastr.error("Seleccione tipo de sangre .");
        $("#txtRegistroTipoSangre").focus();


    } else if ($.trim(txtRegistroCategoriaCarnet) == '') {
        toastr.error("Seleccione categoria.");
        $("#txtRegistroCategoriaCarnet").focus();

    } else if ($.trim(txtRegistroSede) == '') {
        toastr.error("Seleccione sede .");
        $("#txtRegistroSede").focus();

    } else if ($.trim(txtRegistroPrograma) == '') {
        toastr.error("Seleccione programa .");
        $("#txtRegistroPrograma").focus();


    } else if ($.trim(txtRegistroLote) == '') {
        toastr.error("Seleccione lote.");
        $("#txtRegistroLote").focus();


    } else if ($.trim(txtRegistroIndicador) == '') {
        toastr.error("Seleccione indicador fotografía.");
        $("#txtRegistroIndicador").focus();

    } else if ($.trim(txtRegistroNovedad) == '') {
        toastr.error("Seleccione novedad.");
        $("#txtRegistroNovedad").focus();
    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Marge/Inscripcion/GuardarInscripcion",
            type: "POST",
            data: {
                'txtRegistroIdentificacion': txtRegistroIdentificacion,
                'txtRegistroTipoIdentificacion': txtRegistroTipoIdentificacion,
                'txtRegistroNombreEstudiante': txtRegistroNombreEstudiante,
                'txtRegistroApellidoEstudiante': txtRegistroApellidoEstudiante,
                'txtRegistroEmail': txtRegistroEmail,
                'txtRegistroCelular': txtRegistroCelular,
                'txtRegistroTipoSangre': txtRegistroTipoSangre,
                'txtRegistroCategoriaCarnet': txtRegistroCategoriaCarnet,
                'txtRegistroSede': txtRegistroSede,
                'txtRegistroPrograma': txtRegistroPrograma,
                'txtRegistroLote': txtRegistroLote,
                'txtRegistroIndicador': txtRegistroIndicador,
                'txtRegistroNovedad': txtRegistroNovedad,
                'txtLugarReclamo': txtLugarReclamo,
                'foto_base64': $("#foto_base64").val()
            },
            dataType: "JSON",
            success: function(json) {
                if (json.success == true) {
                    toastr.success("Registro existoso.");
                    $("#NuevaInscripcion").hide();
                    $("#Registrar-Inscripcion")[0].reset();
                    //   $('#tbl_inscritos').DataTable().ajax.reload();
                    $("#registrar-inscripcion").modal("hide");
                    BusquedaEstudiantes();
                    $(".desactivarC").fadeOut(500);
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "warning");
                    $(".desactivarC").fadeOut(500);
                }

            }
        });
    }
}
//fin //función para la actualizacion de la informacion del estudiante
// funcion editar inscripcion de estdiantes

function EditarInscripcion() {
    var mail = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;

    var txtidinscripcion = $("#txtidinscripcion").val();
    var txtidentificacion = $("#txtidentificacion").val();
    var txttipoidentificacion = $("#txttipoidentificacion").val();
    var txtnombre = $("#txtnombre").val();
    var txtapellido = $("#txtapellido").val();
    var txtemail = $("#txtemail").val();
    var txtcelular = $("#txtcelular").val();
    var txttiposangre = $("#txttiposangre").val();
    var txtlote = $("#txtlote").val();
    var txtnovedad = $("#txtnovedad").val();

    if ($.trim(txtidentificacion) == '') {
        toastr.error("Ingrese identificación.");
        $("#txtidentificacion").focus();

    } else
    if ($.trim(txttipoidentificacion) == '') {
        toastr.error("Seleccione el tipo de identificación.");
        $("#txttipoidentificacion").focus();

    } else if ($.trim(txtnombre) == '') {
        toastr.error("Ingrese Nombre.");
        $("#txtnombre").focus();

    } else if ($.trim(txtapellido) == '') {
        toastr.error("Ingrese apellido.");
        $("#txtapellido").focus();

    } else if ($.trim(txtemail) == '') {
        toastr.error("Ingrese email estudiante.");
        $("#txtemail").focus();

    } else if (!mail.test($.trim(txtemail))) {
        toastr.error("Ingrese un correo electrónico valido");
        $("#txtemail").focus();

    } else if ($.trim(txtcelular) == '') {
        toastr.error("Ingrese número celular.");
        $("#txtcelular").focus();

    } else if ($.trim(txttiposangre) == '') {
        toastr.error("Seleccione tipo sangre.");
        $("#txttiposangre").focus();

    } else if ($.trim(txtlote) == '') {
        toastr.error("Seleccioen lote.");
        $("#txtlote").focus();

    } else if ($.trim(txtnovedad) == '') {
        toastr.error("Seleccone observación.");
        $("#txtnovedad").focus();

    } else {
        // Extraer imagen si se tomó foto nueva
        var base64image = "";
        var canvasEdit = document.getElementById('canvas-foto-edit');
        if (canvasEdit && $("#contenedor-foto-edit").is(":visible")) {
            base64image = canvasEdit.toDataURL('image/jpeg', 0.9);
        }

        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Marge/Inscripcion/EditarInscripcion",
            type: "POST",
            data: {
                'txtidinscripcion': txtidinscripcion,
                'txtidentificacion': txtidentificacion,
                'txttipoidentificacion': txttipoidentificacion,
                'txtnombre': txtnombre,
                'txtapellido': txtapellido,
                'txtemail': txtemail,
                'txtcelular': txtcelular,
                'txttiposangre': txttiposangre,
                'txtlote': txtlote,
                'txtnovedad': txtnovedad,
                'foto_base64': base64image
            },
            dataType: "JSON",
            success: function(editar) {
                $(".desactivarC").fadeOut(500);

                if (editar.success == true) {
                    toastr.success("Información actualizada exitosamente.");

                    $("#ModalEditarInscipcion").modal("hide");
                    $("#Editar-Inscripcion")[0].reset();
                    BusquedaEstudiantes();

                } else {
                    mensaje("¡Advertencia!", editar.mensaje, "warning");

                }
            }
        });
    }
}
// funcion 




//funcion para cargar la camara del navegador
// Función para sincronizar datos del formulario con el preview del carnet
function SincronizarPreview() {
    // Si estamos en el modal de Nuevo Registro
    if ($("#registrar-inscripcion").is(":visible")) {
        var nombre = $("#txtRegistroNombreEstudiante").val() + " " + $("#txtRegistroApellidoEstudiante").val();
        var id = $("#txtRegistroIdentificacion").val();
        var programa = $("#txtRegistroPrograma option:selected").text();
        var sede = $("#txtRegistroSede option:selected").text();
        var categoria = $("#txtRegistroCategoriaCarnet option:selected").text();

        $("#preview-nombre").text(nombre || "---");
        $("#preview-identificacion").text(id || "---");
        $("#preview-programa").text(programa != "Seleccione ..." ? programa : "---");
        $("#preview-sede").text(sede != "Seleccione sede..." ? sede : "---");
        $("#preview-tipo-carnet").text(categoria == "PRACTICANTE" ? "PRACTICANTE" : "ESTUDIANTE");

        // Cambiar fondo del mockup
        var mockup = $("#preview-carnet-mockup");
        if (categoria == "PRACTICANTE") {
            mockup.removeClass("estudiantil-bg").addClass("practicante-bg vertical");
        } else {
            mockup.removeClass("practicante-bg vertical").addClass("estudiantil-bg");
        }
    } 
    // Si estamos en el modal de Edición
    else if ($("#ModalEditarInscipcion").is(":visible")) {
        var nombre = $("#txtnombre").val() + " " + $("#txtapellido").val();
        var id = $("#txtidentificacion").val();
        
        $("#preview-nombre").text(nombre || "---");
        $("#preview-identificacion").text(id || "---");
        // Para edición, se podría añadir lógica similar si se habilitan más campos
    }
}

// Escuchadores para actualización en tiempo real
$("#txtRegistroNombreEstudiante, #txtRegistroApellidoEstudiante, #txtRegistroIdentificacion, #txtRegistroSede, #txtRegistroPrograma, #txtRegistroCategoriaCarnet").on("input change", function() {
    SincronizarPreview();
});

function CargarCamara(id_inscripcion) {
    $("#Fid_inscripcion").val(id_inscripcion);
    SincronizarPreview(); // Sincronizar antes de mostrar
    $("#ModalCamara").modal("show");
}

// Lógica de cámara integrada en el modal de inscripción
var videoStream = window.videoStream || null;

// Iniciar cámara cuando el modal se abre (usando delegación de eventos por si el DOM carga dinámicamente)
$(document).on('shown.bs.modal', '#registrar-inscripcion', function () {
    const video = document.getElementById('video');
    const constraints = {
        video: { 
            width: { ideal: 640 }, 
            height: { ideal: 480 }, 
            facingMode: "user" 
        } 
    };

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia(constraints)
            .then((stream) => {
                videoStream = stream;
                video.srcObject = stream;
                video.onloadedmetadata = function(e) {
                    video.play();
                };
            })
            .catch((err) => {
                console.error("Error al acceder a la cámara: ", err);
                toastr.warning("No se pudo acceder a la cámara automáticamente.");
                alert("Error: El navegador bloqueó el acceso a la cámara. Asegúrese de dar permisos y de estar navegando mediante 'localhost' o usando HTTPS.");
            });
    } else {
        console.warn("navigator.mediaDevices no soportado o sitio no seguro (requiere HTTPS o localhost).");
        toastr.error("La cámara requiere acceso por HTTPS o localhost.");
        alert("Atención: Para usar la cámara, debe acceder al sistema a través de HTTPS o desde 'localhost'. Es una restricción de seguridad del navegador.");
    }
        
    // Resetear foto oculta al abrir el modal
    $("#foto_base64").val("");
    $("#canvas").hide();
    $("#video").show();
    $("#snap").show();
    $("#tomarotra-confirmar").hide();
});

// Detener cámara cuando el modal se cierra
$(document).on('hidden.bs.modal', '#registrar-inscripcion', function () {
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
    }
});

// Sincronización en vivo del carnet mockup
$("#txtRegistroNombreEstudiante, #txtRegistroApellidoEstudiante").on('keyup change', function() {
    let nombre = $("#txtRegistroNombreEstudiante").val().trim();
    let apellido = $("#txtRegistroApellidoEstudiante").val().trim();
    let completo = (nombre + " " + apellido).trim();
    $("#preview-nombre").text(completo !== "" ? completo : "---");
});

$("#txtRegistroIdentificacion").on('keyup change', function() {
    let ide = $(this).val().trim();
    $("#preview-identificacion").text(ide !== "" ? ide : "---");
});

$("#txtRegistroPrograma").on('change', function() {
    let prog = $(this).find("option:selected").text();
    if(prog.includes("Seleccione")) prog = "---";
    $("#preview-programa").text(prog);
});

$("#txtRegistroSede").on('change', function() {
    let sede = $(this).find("option:selected").text();
    if(sede.includes("Seleccione")) sede = "---";
    $("#preview-sede").text(sede);
});

$("#txtRegistroCategoriaCarnet").on('change', function() {
    let cat = $(this).find("option:selected").text();
    if(cat.includes("Seleccione")) cat = "ESTUDIANTE";
    $("#preview-tipo-carnet").text(cat);
    
    let mockup = $("#preview-carnet-mockup");
    mockup.removeClass("estudiantil-bg practicante-bg");
    if(cat === "PRACTICANTE") {
        mockup.addClass("practicante-bg");
    } else {
        mockup.addClass("estudiantil-bg");
    }
});

function tomarFoto() {
    if (!videoStream || !videoStream.active) {
        toastr.warning("No hay cámara activa para capturar.");
        return;
    }
    
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    
    // Resolución optimizada para balance entre calidad y tamaño de red
    canvas.width = 350;
    canvas.height = 350;
    
    const context = canvas.getContext('2d');
    
    // Lógica de recorte centrado
    const vWidth = video.videoWidth;
    const vHeight = video.videoHeight;
    const targetRatio = canvas.width / canvas.height;
    const videoRatio = vWidth / vHeight;
    
    let sx, sy, sWidth, sHeight;
    
    if (videoRatio > targetRatio) {
        sHeight = vHeight;
        sWidth = vHeight * targetRatio;
        sx = (vWidth - sWidth) / 2;
        sy = 0;
    } else {
        sWidth = vWidth;
        sHeight = vWidth / targetRatio;
        sx = 0;
        sy = (vHeight - sHeight) / 2;
    }

    // Espejar el contexto para que coincida con la cámara frontal
    context.translate(canvas.width, 0);
    context.scale(-1, 1);

    context.drawImage(video, sx, sy, sWidth, sHeight, 0, 0, canvas.width, canvas.height);
    
    $("#snap").hide();
    $("#tomarotra-confirmar").show();
    $("#video").hide();
    $("#canvas").show();
    
    // Guardar el base64 en el input oculto
    const fotoData = canvas.toDataURL('image/jpeg', 0.6);
    $("#foto_base64").val(fotoData);
}

function tomarOtraFoto() {
    $("#snap").show();
    $("#tomarotra-confirmar").hide();
    $("#video").show();
    $("#canvas").hide();
    $("#foto_base64").val("");
}

function FotoOk() {
    const canvas = document.getElementById('canvas');
    // JPEG con compresión 0.6 para optimizar tamaño de payload
    const fotoData = canvas.toDataURL('image/jpeg', 0.6);
    const id_inscripcion = $("#Fid_inscripcion").val();
    
    $(".desactivarC").fadeIn(500);
    $.ajax({
        url: "Marge/Inscripcion/GuardarFoto",
        type: "POST",
        data: {
            'id_inscripcion': id_inscripcion,
            'foto': fotoData
        },
        dataType: "JSON",
        success: function(json) {
            $(".desactivarC").fadeOut(500);
            if (json.success == true) {
                toastr.success(json.mensaje);
                $("#ModalCamara").modal("hide");
                stopCamera();
                BusquedaEstudiantes();
            } else {
                toastr.error(json.mensaje);
            }
        },
        error: function() {
            $(".desactivarC").fadeOut(500);
            toastr.error("Error de conexión al guardar la foto.");
        }
    });
}

function stopCamera() {
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
        videoStream = null;
    }
}

// ---- Lógica de Recorte y Descarga de Fotos ----
let cropper = null;

$("#foto_upload").on('change', function(e) {
    const files = e.target.files;
    if (files && files.length > 0) {
        const reader = new FileReader();
        reader.onload = function(event) {
            $("#image-to-crop").attr("src", event.target.result);
            $("#modalCropper").modal('show');
        };
        reader.readAsDataURL(files[0]);
    }
});

$("#modalCropper").on('shown.bs.modal', function() {
    if (cropper) { cropper.destroy(); }
    cropper = new Cropper(document.getElementById('image-to-crop'), {
        aspectRatio: 1,
        viewMode: 1,
        autoCropArea: 0.8
    });
}).on('hidden.bs.modal', function() {
    if (cropper) { cropper.destroy(); cropper = null; }
    $("#foto_upload").val("");
});

function guardarRecorte() {
    if (!cropper) return;
    
    // Obtener el canvas recortado a un tamaño fijo para no sobrecargar el servidor
    const canvasRecortado = cropper.getCroppedCanvas({
        width: 350,
        height: 350
    });
    
    // Convertir a base64
    const fotoData = canvasRecortado.toDataURL('image/jpeg', 0.6);
    
    // Guardar en el input oculto
    $("#foto_base64").val(fotoData);
    
    // Ocultar video, mostrar canvas y pintar la imagen ahí para que el usuario la vea en el mockup
    $("#snap").hide();
    $("#tomarotra-confirmar").show();
    $("#video").hide();
    
    const canvas = document.getElementById('canvas');
    canvas.width = 350;
    canvas.height = 350;
    const ctx = canvas.getContext('2d');
    
    const img = new Image();
    img.onload = function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);
        $("#canvas").show();
    };
    img.src = fotoData;
    
    $("#modalCropper").modal('hide');
}

function descargarFoto() {
    const fotoData = $("#foto_base64").val();
    if (!fotoData) {
        toastr.warning("No hay foto para descargar.");
        return;
    }
    const a = document.createElement("a");
    a.href = fotoData;
    a.download = "foto_carnet.jpg";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}





function ListarMatriculados() {
    $('#tbl_matriculados').dataTable().fnDestroy();
    $('#tbl_matriculados').dataTable({
        ajax: "Marge/Inscripcion/ListarMatriculados",
        "aoColumnDefs": [{
            "aTargets": [0]
        }],
        "oLanguage": {
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "No hay Datos registrados en el sistema",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _END_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sLoadingRecords": "Cargando Datos...",
            "sSearch": "Buscar",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
        },

        "paging": true,
        "aLengthMenu": [
            [10, 20, 30, 40, -1],
            [10, 20, 30, 40, "Todos"] // change per page values here
        ],


        "columns": [ //agregar configuraciones a cada una de las columnas de las tablas
            {}, //column 1
            { "class": "center", "orderable": false }, //column 2
            { "class": "center", "orderable": false }, //column 3
            { "class": "center", "orderable": false }, //column 4
            { "class": "center", "orderable": false }, //column 5
            { "class": "center", "orderable": false }, //column 6
            { "class": "center", "orderable": false }, //column 7


        ],
        initComplete: function(oSettings, json) {
            // $('[data-rel="tooltip"]').tooltip(); 
        },
        "iDisplayLength": 10,
    });
}


//funcion cambiar estado de carnet
function EntregaCarnet() {
    var ECidinsripcion = $("#ECidinsripcion").val();

    $(".desactivarC").fadeIn(500);
    $.ajax({
        url: "Marge/Inscripcion/EntregaCarnet",
        type: "POST",
        data: {
            'ECidinsripcion': ECidinsripcion
        },
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                $("#ModalEntregaCarnet").modal("hide");
                toastr.success(json.mensaje);
                BusquedaEstudiantes();
            } else {
                toastr.error(json.mensaje);
                $("#ModalEntregaCarnet").modal("hide");
            }
            $(".desactivarC").fadeOut(500);
        }
    });

}

//funcion oafra correcciones de carnet
function RealizarCambios() {

    var RCidinscripcion = $("#RCidinscripcion").val();
    var Cobservacion = $("#Cobservacion").val();
    if ($.trim(Cobservacion) == '') {
        toastr.error("Ingrese observación de correcciones.");
        $("#Cobservacion").focus();
    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Marge/Inscripcion/RealizarCambios",
            type: "POST",
            data: {
                'RCidinscripcion': RCidinscripcion,
                'Cobservacion': Cobservacion,
            },
            dataType: "JSON",
            success: function(editar) {
                $(".desactivarC").fadeOut(500);

                if (editar.success == true) {
                    toastr.success("Cambios Registrados..!.");

                    $("#ModalRelizarCambios").modal("hide");
                    $("#Realizar-Cambios")[0].reset();
                    BusquedaEstudiantes();
                } else {
                    mensaje("¡Advertencia!", editar.mensaje, "warning");
                }
            }
        });
    }
}

//funcion para el cambio de lote
function CambiarLote() {
    var txtclidinscripcion = $("#txtclidinscripcion").val();
    var txtclote = $("#txtclote").val();

    if ($.trim(txtclote) == '') {
        toastr.error("Seleccione lote.");
        $("#txtclote").focus();
    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Marge/Inscripcion/CambiarLote",
            type: "POST",
            data: {
                'txtclidinscripcion': txtclidinscripcion,
                'txtclote': txtclote,
            },
            dataType: "JSON",
            success: function(editar) {
                $(".desactivarC").fadeOut(500);
                if (editar.success == true) {
                    toastr.success("Cambios realizados..!.");
                    $("#ModalCambiarLote").modal("hide");
                    $("#Cambiar-Lote")[0].reset();
                    BusquedaEstudiantes();
                } else {
                    mensaje("¡Advertencia!", editar.mensaje, "warning");
                }
            }
        });
    }
}

//funcion para el cambio de proghrama
function CambiarPrograma() {
    var txtPidinscripcion = $("#txtPidinscripcion").val();
    var txtpsede = $("#txtpsede").val();
    var txtpprograma = $("#txtpprograma").val();

    if ($.trim(txtpsede) == '') {
        toastr.error("Seleccione sede.");
        $("#txtpsede").focus();
    } else if ($.trim(txtpprograma) == '') {
        toastr.error("Seleccione programa.");
        $("#txtpprograma").focus();
    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Marge/Inscripcion/CambiarPrograma",
            type: "POST",
            data: {
                'txtPidinscripcion': txtPidinscripcion,
                'txtpsede': txtpsede,
                'txtpprograma': txtpprograma,
            },
            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (json.success == true) {
                    $("#ModalCambiarPrograma").modal("hide");
                    $("#Cambiar-Programa")[0].reset();
                    BusquedaEstudiantes();
                    toastr.success("Cambios realizados..!.");
                } else {

                }
            }
        });
    }
}

function ListarCarnets() {
    var lote = $("#txtcmbetapa").val();
    var etapa = $("#txtcmbetapa").val();

    if ($.trim(etapa) == '') {
        toastr.error("Seleccione etapa.");
        $("#txtcmbetapa").focus();
    } else if ($.trim(lote) == '') {
        toastr.error("Seleccione lote.");
        $("#txtcmblote").focus();
    } else {
        if (lote == 4) {
            ListarTodos();
        } else {
            ListarCarnet();
        }
    }
}


function ListarCarnet() {
    var lote = $("#txtcmblote").val();

    if ($.trim(lote) == '') {
        toastr.error("Seleccione lote.");
        $("#txtcmblote").focus();
    } else {
        $("#BtnRecidibo").removeAttr("Disabled");
        $("#Cresultado").show();
        $(".desactivarC").fadeIn(500);
        var combo = document.getElementById("txtcmblote");
        var selected = ' ' + combo.options[combo.selectedIndex].text;
        $('#tbl_carnet').dataTable().fnDestroy();
        $('#tbl_carnet').dataTable({
            ajax: "Marge/Inscripcion/ConsultaCarnet?lote=" + lote,
            "aoColumnDefs": [{
                "aTargets": [0]
            }],
            dom: 'Bfrtip',
            /* dom: 'Qfrtip',
            search: {
                return: true
            }, */
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i>',
                    titleAttr: 'Exportar a excel',
                    className: 'btn btn-success',
                    title: 'Lote_carnet' + selected + '',
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i>',
                    titleAttr: 'Exportar a pdf',
                    className: 'btn btn-danger',
                    orientation: 'landscape',
                    title: 'Lote_carnet' + selected + '',
                },
                {
                    extend: 'csv',
                    text: '<i>.CSV</i>',
                    titleAttr: 'Exportar a csv',
                    className: 'btn btn-warning',
                    title: 'Lote_carnet' + selected + '',
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i>',
                    titleAttr: 'Imprimir',
                    className: 'btn btn-info',
                    title: 'Lote_carnet' + selected + '',
                },
                {
                    extend: 'copy',
                    text: '<i class="fa fa-clone"></i>',
                    titleAttr: 'Copiar al portapapeles',
                    className: 'btn btn-secondary',
                    //title: 'Lote_carnet' + selected + '',
                }
            ],
            "paging": true,
            "ordering": true,
            "searching": true,
            "filter": true,

            "oLanguage": {
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "No hay Datos registrados en el sistema",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _END_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sLoadingRecords": "Cargando Datos...",
                "sSearch": "Buscar",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
            },

            "aLengthMenu": [
                [10, 20, 30, 40, -1],
                [10, 20, 30, 40, "Todos"] // change per page values here
            ],

            "columns": [ //agregar configuraciones a cada una de las columnas de las tablas
                {}, //column 1
                { "class": "center", "orderable": true }, //column 2
                { "class": "center", "orderable": true }, //column 3
                { "class": "center", "orderable": true }, //column 4
                { "class": "center", "orderable": true }, //column 5
                { "class": "center", "orderable": true }, //column 6
                { "class": "center", "orderable": true }, //column 7
                { "class": "center", "orderable": true }, //column 8
                { "class": "center", "orderable": true }, //column 9
                { "class": "center", "orderable": true }, //column 10
                { "class": "center", "orderable": true }, //column 11
                { "class": "center", "orderable": true }, //column 12
            ],

            initComplete: function(oSettings, json) {
                // $('[data-rel="tooltip"]').tooltip(); 
            },
            "iDisplayLength": 20
        });
        $(".desactivarC").fadeOut(500);

    }
}



function ListarTodos() {
    $("#Cresultado").show();
    $(".desactivarC").fadeIn(500);
    $('#tbl_carnet').dataTable().fnDestroy();
    $('#tbl_carnet').dataTable({
        ajax: "Marge/Inscripcion/ListarTodos",
        "aoColumnDefs": [{
            "aTargets": [0]
        }],
        dom: 'Bfrtip',
        /* dom: 'Qfrtip',
        search: {
            return: true
        }, */
        buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel-o"></i>',
                titleAttr: 'Exportar a excel',
                className: 'btn btn-success',
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa fa-file-pdf-o"></i>',
                titleAttr: 'Exportar a pdf',
                className: 'btn btn-danger',
                orientation: 'landscape',
            },
            {
                extend: 'csv',
                text: '<i>.CSV</i>',
                titleAttr: 'Exportar a csv',
                className: 'btn btn-warning',
            },
            {
                extend: 'print',
                text: '<i class="fa fa-print"></i>',
                titleAttr: 'Imprimir',
                className: 'btn btn-info',
            },
            {
                extend: 'copy',
                text: '<i class="fa fa-clone"></i>',
                titleAttr: 'Copiar al portapapeles',
                className: 'btn btn-secondary',
                //title: 'Lote_carnet' + selected + '',
            }
        ],
        "paging": true,
        "ordering": true,
        "searching": true,
        "filter": true,

        "oLanguage": {
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "No hay Datos registrados en el sistema",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _END_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sLoadingRecords": "Cargando Datos...",
            "sSearch": "Buscar",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
        },

        "aLengthMenu": [
            [10, 20, 30, 40, -1],
            [10, 20, 30, 40, "Todos"] // change per page values here
        ],

        "columns": [ //agregar configuraciones a cada una de las columnas de las tablas
            {}, //column 1
            { "class": "center", "orderable": true }, //column 2
            { "class": "center", "orderable": true }, //column 3
            { "class": "center", "orderable": true }, //column 4
            { "class": "center", "orderable": true }, //column 5
            { "class": "center", "orderable": true }, //column 6
            { "class": "center", "orderable": true }, //column 7
            { "class": "center", "orderable": true }, //column 8
            { "class": "center", "orderable": true }, //column 9
            { "class": "center", "orderable": true }, //column 10
            { "class": "center", "orderable": true }, //column 11
            { "class": "center", "orderable": true }, //column 12
        ],

        initComplete: function(oSettings, json) {
            // $('[data-rel="tooltip"]').tooltip(); 
        },
        "iDisplayLength": 20
    });
    $(".desactivarC").fadeOut(500);

}


//funcion para la busqueda de estudiantes
function BusquedaEstudiantes() {
    var consulta;
    $("#buscar").focus();
    $("buscarMatricula").val('');
    consulta = $("#buscar").val();

    if (consulta.length == 0) {
        $("#resultado").empty();
    } else {
        $.ajax({
            type: "POST",
            url: 'developer/Models/BusquedaEstudiantes.php',
            data: "b=" + consulta,
            dataType: "html",
            error: function() { alert("error petición ajax"); },
            success: function(data) {
                $("#resultado").show();
                $("#NuevaInscripcion").hide();
                $("#resultado").empty();
                $("#resultado").append(data).fadeIn(5000);
                $('#EditarInscripcion').hide();

            }

        });
    }
}


//funcion para el cambio a recibido

function CambiarRecibido() {

    var Linscripcion = $("#Linscripcion").val();

    $(".desactivarC").fadeIn(500);
    $.ajax({
        url: "Marge/Inscripcion/CambiarRecibido",
        type: "POST",
        data: {
            'Linscripcion': Linscripcion,
        },
        dataType: "JSON",
        success: function(json) {
            $(".desactivarC").fadeOut(500);
            if (json.success == true) {
                toastr.success(json.mensaje);
                $("#modalcambiarrecibido").modal("hide");
                ListarCarnet();
                BusquedaEstudiantes();
            } else {
                toastr.error(json.mensaje);

            }
        }
    });

}

//funcion para cambiar a entregado
function CambiarEntregado() {

    var ECidinsripcion = $("#ECidinsripcion").val() || $("#Linscripcion").val();

    $(".desactivarC").fadeIn(500);
    $.ajax({
        url: "Marge/Inscripcion/CambiarEntregado",
        type: "POST",
        data: {
            'ECidinsripcion': ECidinsripcion,
            'Linscripcion': ECidinsripcion,
            'Einscripcion': ECidinsripcion
        },
        dataType: "JSON",
        success: function(json) {
            $(".desactivarC").fadeOut(500);
            if (json.success == true) {
                toastr.success(json.mensaje);
                $("#modalcambiarentregado").modal("hide");
                ListarCarnet();
                BusquedaEstudiantes();
            } else {
                toastr.error(json.mensaje);

            }
        }
    });

}

// -------------------------------------------------------------
// LÓGICA DE CÁMARA PARA MODAL DE EDICIÓN (TIEMPOS MUERTOS)
// -------------------------------------------------------------
let camaraStreamEdit = null;

$(document).on('click', '#btn-iniciar-camara-edit', function() {
    var video = document.getElementById('video-camara-edit');
    var btnIniciar = $(this);
    var btnCapturar = $('#btn-capturar-foto-edit');
    var contenedorCam = $('#contenedor-camara-edit');
    var contenedorFoto = $('#contenedor-foto-edit');

    // Ocultar canvas si había foto anterior
    contenedorFoto.hide();

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: true }).then(function(stream) {
            camaraStreamEdit = stream;
            video.srcObject = stream;
            video.play();
            
            contenedorCam.show();
            btnIniciar.hide();
            btnCapturar.show();
        }).catch(function(error) {
            toastr.error("No se pudo acceder a la cámara.");
            console.error(error);
        });
    } else {
        toastr.error("Tu navegador no soporta acceso a la cámara.");
    }
});

$(document).on('click', '#btn-capturar-foto-edit', function() {
    var video = document.getElementById('video-camara-edit');
    var canvas = document.getElementById('canvas-foto-edit');
    const context = canvas.getContext('2d');
    
    // Configuramos el canvas como un cuadrado perfecto de alta calidad (300x300)
    canvas.width = 300;
    canvas.height = 300;

    // Calculamos el recorte perfecto al centro (1:1) independientemente si la webcam es 16:9 o 4:3
    const vWidth = video.videoWidth;
    const vHeight = video.videoHeight;
    const minDim = Math.min(vWidth, vHeight);
    
    const sx = (vWidth - minDim) / 2;
    const sy = (vHeight - minDim) / 2;

    // Espejar el contexto para que coincida con la cámara frontal
    context.translate(300, 0);
    context.scale(-1, 1);

    // Dibujar el fotograma en el canvas
    context.drawImage(video, sx, sy, minDim, minDim, 0, 0, 300, 300);

    // Detener la cámara
    if (camaraStreamEdit) {
        camaraStreamEdit.getTracks().forEach(track => track.stop());
    }

    // Cambiar UI
    $('#contenedor-camara-edit').hide();
    $('#contenedor-foto-edit').show();
    $('#btn-capturar-foto-edit').hide();
    $('#btn-iniciar-camara-edit').html('<i class="fa fa-refresh"></i> Volver a Intentar').show();
});

// Detener cámara al cerrar el modal de edición
$('#ModalEditarInscipcion').on('hidden.bs.modal', function () {
    if (camaraStreamEdit) {
        camaraStreamEdit.getTracks().forEach(track => track.stop());
    }
    $('#contenedor-camara-edit').hide();
    $('#contenedor-foto-edit').hide();
    $('#btn-capturar-foto-edit').hide();
    $('#btn-iniciar-camara-edit').html('<i class="fa fa-camera"></i> Iniciar Cámara').show();
});
function abrirGestionCarnetSige(idInscripcion, documento) {
    $('#sige-id-inscripcion').val(idInscripcion);
    $('#sige-documento').text(documento);
    $('#sige-error').hide().text('');
    $('#sige-confirmado').prop('checked', false);
    $('#sige-uid-rfid').val('');
    $('#sige-estado-actual').text('Consultando…');
    $('#sige-orden-pendiente').hide();
    $('#sige-btn-enviar').prop('disabled', true);
    $('#ModalGestionCarnetSige').modal('show');
    $.ajax({
        url: 'Marge/Inscripcion/EstadoCarnetSige',
        type: 'POST',
        dataType: 'json',
        data: {id_inscripcion: idInscripcion},
        success: function (response) {
            if (!response.success) {
                $('#sige-error').text(response.mensaje || 'No fue posible consultar el carnet.').show();
                return;
            }
            var carnet = response.data.carnet;
            if (carnet) {
                $('#sige-estado-actual').text(
                    'UID: ' + (carnet.uid_rfid || 'Sin UID') +
                    ' | Estado: ' + carnet.estado_operativo +
                    ' | Motivo: ' + carnet.motivo_inactivacion +
                    ' | Vigencia: ' + (carnet.vigencia_hasta || 'Sin fecha') +
                    ' | Versión: ' + carnet.version_estado +
                    ((parseInt(carnet.requiere_reactivacion, 10) === 1) ? ' | REACTIVACIÓN REQUERIDA' : '')
                );
                var motivoInactivacion = String(carnet.motivo_inactivacion || '').toUpperCase();
                var exigeReemplazo = motivoInactivacion === 'PERDIDA' || motivoInactivacion === 'ROBO';
                var requiereReactivacion = parseInt(carnet.requiere_reactivacion, 10) === 1;
                $('#sige-tipo-operacion').val(
                    exigeReemplazo ? 'REEMPLAZO' :
                        (requiereReactivacion ? 'REACTIVACION_AUTORIZADA' : 'REEMPLAZO')
                );
            } else {
                $('#sige-estado-actual').text('El estudiante aún no tiene un carnet proyectado en TIC.');
                $('#sige-tipo-operacion').val('ASIGNACION');
            }
            if (response.data.orden_pendiente) {
                $('#sige-orden-pendiente').text(
                    'Operación pendiente: ' + response.data.orden_pendiente.tipo +
                    ' (' + response.data.orden_pendiente.id_operacion + ')'
                ).show();
                $('#sige-btn-enviar').prop('disabled', true);
            } else {
                $('#sige-btn-enviar').prop('disabled', false);
            }
            actualizarFormularioSige();
        },
        error: function () {
            $('#sige-error').text('No fue posible consultar el estado del carnet.').show();
        }
    });
}

function actualizarFormularioSige() {
    var tipo = $('#sige-tipo-operacion').val();
    $('#sige-grupo-uid').toggle(tipo === 'ASIGNACION' || tipo === 'REEMPLAZO');
    $('#sige-grupo-motivo').toggle(tipo === 'REEMPLAZO' || tipo === 'BLOQUEO');
}

function crearOrdenCarnetSige() {
    var confirmado = $('#sige-confirmado').is(':checked');
    if (!confirmado) {
        $('#sige-error').text('Debe confirmar explícitamente la operación.').show();
        return;
    }
    var tipo = $('#sige-tipo-operacion').val();
    var uid = $.trim($('#sige-uid-rfid').val());
    if ((tipo === 'ASIGNACION' || tipo === 'REEMPLAZO') && uid === '') {
        $('#sige-error').text('Lea o escriba el nuevo código RFID.').show();
        return;
    }
    $('#sige-btn-enviar').prop('disabled', true);
    $.ajax({
        url: 'Marge/Inscripcion/CrearOrdenCarnet',
        type: 'POST',
        dataType: 'json',
        data: {
            csrf_token: window.SIGE_CSRF_TOKEN,
            id_inscripcion: $('#sige-id-inscripcion').val(),
            tipo: tipo,
            uid_rfid: uid,
            motivo: (tipo === 'REACTIVACION_AUTORIZADA') ? 'PAGO_VERIFICADO_EXTERNAMENTE' : $('#sige-motivo').val(),
            confirmado: '1'
        },
        success: function (response) {
            if (!response.success) {
                $('#sige-error').text(response.mensaje || 'La orden fue rechazada.').show();
                $('#sige-btn-enviar').prop('disabled', false);
                return;
            }
            $('#sige-orden-pendiente').text('Operación pendiente: ' + response.id_operacion).show();
            toastr.success('Orden registrada. El estado cambiará cuando SIGE la confirme.');
        },
        error: function (xhr) {
            var response = xhr.responseJSON || {};
            $('#sige-error').text(response.mensaje || 'No fue posible crear la orden.').show();
            $('#sige-btn-enviar').prop('disabled', false);
        }
    });
}
