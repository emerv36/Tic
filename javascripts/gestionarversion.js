$(document).ready(function() {
    ListarVersion();
    $('[data-toggle="tooltip"]').tooltip();
});

// datatable para lista de versiones
function ListarVersion() {
    $('#tbl_version').dataTable({
        ajax: "Version/Version/ListarVersion",
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
        "searching": true,
        "aaSorting": [
            [0, 'asc']
        ],
        "aLengthMenu": [
            [5, 10, 15, 20, -1],
            [5, 10, 15, 20, "Todos"] // change per page values here
        ],
        "columns": [ //agregar configuraciones a cada una de las columnas de las tablas
            {}, //column 1
            { "class": "center", "orderable": false }, //column 2
            { "class": "center", "orderable": false }, //column 9
            { "class": "center", "orderable": false }, //column 9

        ],
        initComplete: function(oSettings, json) {
            // $('[data-rel="tooltip"]').tooltip(); 
        },
        "iDisplayLength": 5
    });
}
//fin datatable

//función para cargar el modal editar versiones
function ModalEditarVersion(id_version, nombre_version, estado_version) {
    $("#id_version").val(id_version);
    $("#nombre_version").val(nombre_version);
    $("#estado_version").val(estado_version);
    $("#editar-version").modal("show");
}
//fin funcion

//funcion para editar versiones
function EditarVersion() {
    var id_version = $("#id_version").val();
    var nombre_version = $("#nombre_version").val();
    var estado_version = $("#estado_version").val();

    if ($.trim(nombre_version) == '') {
        toastr.error("Ingrese version de pensum");
        $("#nombre_version").focus();
    } else if ($.trim(estado_version) == '') {
        toastr.error("Seleccione versión ");
        $("#estado_version").focus();
    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Version/Version/EditarVersion",
            type: "POST",
            data: {
                'id_version': id_version,
                'nombre_version': nombre_version,
                'estado_version': estado_version,
            },
            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (json.success == true) {
                    toastr.success("Información actualizada correctamente.");
                    $("#editar-version").modal("hide");
                    $('#tbl_version').DataTable().ajax.reload();
                    $("#Editar-Version")[0].reset();
                } else {
                    toastr.error(json.mensaje);
                }

            }
        })
    }
}
//fin funcion para la actualizacion de sedes

// función para cargar el modal de registro de sedes
function ModalRegistrarVersion() {
    $("#registrar-version").modal("show");
}
//fin función para cargar el modal de registro de sedes

// función para el registro de versiones
function RegistrarVersion() {
    var Rnombre_version = $("#Rnombre_version").val();
    var Restado_version = $("#Restado_version").val();

    if ($.trim(Rnombre_version) == '') {
        toastr.error("Ingresa nombre version.");
        $("#Rnombre_version").focus();
    } else if ($trim(Restado_version) == '') {
        toastr.error("Seleccione estado version.");
        $("#Restado_version").focus();
    } else {
        $(".desactivarC").fadeIn(500);

        $.ajax({
            url: "Version/Version/RegistrarVersion",
            type: "POST",
            data: {
                'Rnombre_version': Rnombre_version,
                'Restado_version': Restado_version
            },
            dataType: "JSON",

            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (version.success == true) {
                    toastr.success("Versión registrada.");
                    $("#registrar-version").modal("hide");
                    $('#tbl_version').DataTable().ajax.reload();
                    $("#Registrar-Version")[0].reset();
                } else {
                    toastr.error(json.mensaje);
                }
            }
        });

    }
}
//fin función 

/// funcion para cambiar estado de las versiones
function FeEditEstado(codigo, est) {
    var Mensaje;

    if (est == 'off') {
        Mensaje = "¿Está seguro de Deshabilitar esta sede?";
    } else {
        Mensaje = "¿Está seguro de Habilitar esta sede?";
    }
    $('.mensajeconfirm').html(Mensaje);

    $("#confirmar-ok").attr("onclick", "confirmar(" + codigo + ",'" + est + "')");
    $("#modalconfirmar").modal("show");
}

//funcion para confirmar estado
function confirmar(codigo, est) {
    $('.desactivarC').fadeIn(500);
    $.ajax({
            url: "Version/Version/EditarEstadoVersion",
            type: "POST",
            data: {
                codUsuario: codigo,
                estado: est
            }
        })
        //fin funcion para confirmar estado

    .done(function(json) {
            $('#tbl_version').DataTable().ajax.reload();
            $('.desactivarC').fadeOut(500);
            if (json.success == true) {
                toastr.success("Acción realizada exitosamente");
                $('#modalconfirmar').modal("hide");
                $('#tbl_version').DataTable().ajax.reload();

            } else {
                //toastr.success("Acción realizada exitosamente", "Señor de Usuarios");
                toastr.error("Request failed: " + json.mensaje);

            }
        })
        .fail(function(jqXHR, textStatus) {
            $('.desactivarC').fadeOut(500);
            toastr.error("Request failed: " + jqXHR.responseText);
        });
    $('#modalconfirmar').modal("hide");
}
//fin funcion