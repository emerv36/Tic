$(document).ready(function() {
    ListarInfoProgramas();
    ListarCargos();
    combobox('txtSelectSedePrograma', 'Willie/Programas/CargarSedes', 'Seleccione una opción.');
    combobox('RSelectSedePrograma', 'Willie/Programas/CargarSedes', 'Seleccione una opción.');
    combobox('RSelectSedeCargo', 'Willie/Programas/CargarSedes', 'Seleccione una opción.');
    combobox('txtSelectSedeCargo', 'Willie/Programas/CargarSedes', 'Seleccione una opción.');



    $('.dataTables_filter input[type="search"]').css({ 'width': '350px', 'display': 'inline-block' });

    $('[data-toggle="tooltip"]').tooltip();
    // $('#Rperfil').summernote();
    //  $('#txtperfil').summernote();

    $("#RSelectSedePrograma").change(function() {
        var RSelectSedePrograma = $("#RSelectSedePrograma").val();
        var RnombrePrograma = $("#RnombrePrograma").val();

        if (RSelectSedePrograma.length > 0 && RnombrePrograma.length > 0) {

            $.ajax({
                url: "Willie/Programas/ValidarNombrePrograma",
                type: "POST",
                data: {
                    'RSelectSedePrograma': RSelectSedePrograma,
                    'RnombrePrograma': RnombrePrograma,
                },
                dataType: "JSON",

                success: function(json) {
                    if (json.success == true) {
                        toastr.success(json.mensaje);
                    } else {
                        mensaje("¡Advertencia!", json.mensaje, "warning");
                        $("#RSelectSedePrograma").val('');

                    }
                }

            });
        }

    });



});

// datatable para listar los programas
function ListarInfoProgramas() {
    $('#tbl_programas').dataTable({
        ajax: "Willie/Programas/ListarProgramas",
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

        responsive: true,
        dom: 'lBfrtip',
        buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel-o"></i>',
                titleAttr: 'Exportar Excel',
                className: 'btn btn-success',
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fa fa-file-o"></i>',
                titleAttr: 'Exportar csv',
                className: 'btn btn-info',
            }
        ],

        "columns": [ //agregar configuraciones a cada una de las columnas de las tablas
            {}, //column 1
            { "class": "center", "orderable": true }, //column 2
            { "class": "center", "orderable": false }, //column 3
            { "class": "center", "orderable": false }, //column 4
            { "class": "center", "orderable": false }, //column 5



        ],
        initComplete: function(oSettings, json) {
            // $('[data-rel="tooltip"]').tooltip(); 
        },
        "iDisplayLength": 20
    });
}

/// funcion para cargar el modal de editar el estado de los programas
function EditarEstadoProgramas(codigo, est) {
    var Mensaje;
    if (est == 'off') {
        Mensaje = "¿Está seguro de Deshabilitar este programa?";
    } else {
        Mensaje = "¿Está seguro de Habilitar este programa?";
    }
    $('.mensajeconfirm').html(Mensaje);
    $("#btn-confirm-ok").attr("onclick", "corfirmar(" + codigo + ",'" + est + "')");
    $("#modalconfirmar").modal("show");
}
//funcion para confirmar estado de los salones
function corfirmar(codigo, est) {
    $('.desactivarC').fadeIn(500);
    $.ajax({
            url: "Willie/Programas/EditarEstadoProgramas",
            type: "POST",
            data: {
                'codUsuario': codigo,
                'estado': est
            }
        })
        //fin funcion para confirmar estado de los salones
        .done(function(json) {
            $('#tbl_programas').DataTable().ajax.reload();
            $('#tbl_cargos').DataTable().ajax.reload();
            $('.desactivarC').fadeOut(500);
            if (json.success == true) {
                toastr.success("Acción realizada exitosamente");
            } else {
                toastr.error("Request failed: " + json.mensaje);
            }
        })
        .fail(function(jqXHR, textStatus) {
            $('.desactivarC').fadeOut(500);
            toastr.error("Request failed: " + jqXHR.responseText);
        });
    $("#modalconfirmar").modal("hide");
}

//función para cargar el modal de editar programas
function ModalEditarProgramas(id_programa, codigo_programa, nombre_programa, sede, estado) {
    $("#txtIdPrograma").val(id_programa);
    $("#txtcodigoPrograma").val(codigo_programa);
    $("#txtnombrePrograma").val(nombre_programa);
    $("#txtSelectSedePrograma").val(sede);
    $("#txtEstadoPrograma").val(estado);
    $("#editar-programas").modal("show");
}

//función ajax para actualizar los programas
function ActualizarInfoProgramas() {
    var txtIdPrograma = $("#txtIdPrograma").val();
    var txtcodigoPrograma = $("#txtcodigoPrograma").val();
    var txtnombrePrograma = $("#txtnombrePrograma").val();
    var txtcodigoPrograma = $("#txtcodigoPrograma").val();
    var txtSelectSedePrograma = $("#txtSelectSedePrograma").val();
    var txtEstadoPrograma = $("#txtEstadoPrograma").val();

    var num = /^[0-9.]*$/;
    if ($.trim(txtnombrePrograma) == '') {
        toastr.error("Ingrese nombre del programa.");
        $("#txtnombrePrograma").focus();

    } else if ($.trim(txtcodigoPrograma) == '') {
        toastr.error("Ingrese código del programa.");
        $("#txtcodigoPrograma").focus();


    } else if ($.trim(txtSelectSedePrograma) == '') {
        toastr.error("Seleccione Sede");
        $("#txtSelectSedePrograma").focus();


    } else if ($.trim(RestadoPrograma) == '') {
        toastr.error("Seleccione el estado");
        $("#RestadoPrograma").focus();
    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Willie/Programas/ActualizarInfoProgramas",
            type: "POST",
            data: {
                'txtIdPrograma': txtIdPrograma,
                'txtnombrePrograma': txtnombrePrograma,
                'txtcodigoPrograma': txtcodigoPrograma,
                'txtSelectSedePrograma': txtSelectSedePrograma,
                'txtEstadoPrograma': txtEstadoPrograma,
            },
            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (json.success == true) {
                    toastr.success("Información actualizada");
                    $("#editar-programas").modal("hide");
                    $("#Editar-Programas")[0].reset();
                    $('#tbl_programas').DataTable().ajax.reload();
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "error");
                }
            }
        });
    }
}


//función para cargar el modal de registrar programas
function ModalRegistroProgramas() {
    $("#Registrar-Programas")[0].reset();
    $("#registrar-programa").modal("show");
}

//función ajax para registrar los programas
function RegistrarInfoProgramas() {
    var RnombrePrograma = $("#RnombrePrograma").val();
    var RcodigoPrograma = $("#RcodigoPrograma").val();
    var RSelectSedePrograma = $("#RSelectSedePrograma").val();
    var RestadoPrograma = $("#RestadoPrograma").val();

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
                    $("#registrar-programa").modal("hide");
                    $("#Registrar-Programas")[0].reset();
                    $('#tbl_programas').DataTable().ajax.reload();
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "error");

                }
            }
        });
    }
}

//función para cargar el modal de registrar cargos
function ModalRegistroCargos() {
    $("#Registrar-Cargos")[0].reset();
    $("#registrar-cargos").modal("show");
}

//funcion listar cargos

function ListarCargos() {
    $('#tbl_cargos').dataTable({
        ajax: "Willie/Programas/ListarCargos",
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

        responsive: true,
        dom: 'lBfrtip',
        buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel-o"></i>',
                titleAttr: 'Exportar Excel',
                className: 'btn btn-success',
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fa fa-file-o"></i>',
                titleAttr: 'Exportar csv',
                className: 'btn btn-info',
            }
        ],

        "columns": [ //agregar configuraciones a cada una de las columnas de las tablas
            {}, //column 1
            { "class": "center", "orderable": true }, //column 2
            { "class": "center", "orderable": false }, //column 3
            { "class": "center", "orderable": false }, //column 4
            { "class": "center", "orderable": false }, //column 5



        ],
        initComplete: function(oSettings, json) {
            // $('[data-rel="tooltip"]').tooltip(); 
        },
        "iDisplayLength": 20
    });
}

//funcion para el registro de los cargos para carnet
function RegistrarCargo() {
    var RnombreCargo = $("#RnombreCargo").val();
    var RSelectSedeCargo = $("#RSelectSedeCargo").val();

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
                    $("#registrar-cargos").modal("hide");
                    $("#Registrar-Cargos")[0].reset();
                    $('#tbl_cargos').DataTable().ajax.reload();
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "error");

                }
            }
        });
    }
}

//funcion modal para editar locs cargos
function ModalEditarCargos(id_programa, nombre_programa, sede) {
    $("#txtIdcargo").val(id_programa);
    $("#txtnombreCargo").val(nombre_programa);
    $("#txtSelectSedeCargo").val(sede);
    $("#editar-cargos").modal("show");
}

//funcion para editar los cargos//función ajax para actualizar los programas
function EditarCargos() {
    var txtIdcargo = $("#txtIdcargo").val();
    var txtnombreCargo = $("#txtnombreCargo").val();
    var txtSelectSedeCargo = $("#txtSelectSedeCargo").val();

    if ($.trim(txtnombreCargo) == '') {
        toastr.error("Ingrese nombre del cargo.");
        $("#txtnombreCargo").focus();


    } else if ($.trim(txtSelectSedeCargo) == '') {
        toastr.error("Seleccione Sede");
        $("#txtSelectSedeCargo").focus();

    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Willie/Programas/EditarCargos",
            type: "POST",
            data: {
                'txtIdcargo': txtIdcargo,
                'txtnombreCargo': txtnombreCargo,
                'txtSelectSedeCargo': txtSelectSedeCargo,
            },
            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (json.success == true) {
                    toastr.success("Información actualizada");
                    $("#editar-cargos").modal("hide");
                    $("#Editar-Cargos")[0].reset();
                    $('#tbl_cargos').DataTable().ajax.reload();
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "error");
                }
            }
        });
    }
}