$(document).ready(function() {
    ListarSedes();
    ListarEmpresas();
    $('[data-toggle="tooltip"]').tooltip();
});

// datatable para listar las sedes
function ListarSedes() {
    $('#tbl_sedes').dataTable({
        ajax: "Maggi/Sedes/ListarSedes",
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
        "paging": false,
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
//fin datatable para listar las sedes
function ListarEmpresas() {
    $('#tbl_empresas').dataTable({
        ajax: "Maggi/Sedes/ListarEmpresas",
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
        "paging": false,
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

//función para cargar el modal de editar
function ModalEditarSedes(id_sede, nombre_sede) {
    $("#id_sede").val(id_sede);
    $("#nombre_sede").val(nombre_sede);
    $("#editar-sedes").modal("show");
}
//función para cargar el modal de editar


//funcion para la actualizacion de sedes
function EditarSedes() {
    var id_sede = $("#id_sede").val();
    var nombre_sede = $("#nombre_sede").val();

    if ($.trim(nombre_sede) == '') {
        toastr.error("Por favor, ingrese el nombre.");
        $("#nombre_sede").focus();

    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Maggi/Sedes/EditarSedes",
            type: "POST",
            data: {
                'id_sede': id_sede,
                'nombre_sede': nombre_sede,
            },
            dataType: "JSON",
            success: function(sedes) {
                $(".desactivarC").fadeOut(500);
                if (sedes.success == true) {
                    toastr.success("Información actualizada.");

                    $("#editar-sedes").modal("hide");

                    $('#tbl_sedes').DataTable().ajax.reload();
                    $('#tbl_empresas').DataTable().ajax.reload();
                    $("#Editar-Sedes")[0].reset();
                } else {
                    toastr.error(sedes.mensaje);
                }

            }
        })
    }
}
//fin funcion para la actualizacion de sedes

// función para cargar el modal de registro de sedes
function ModalRegistrarSedes() {
    $("#registrar-sede").modal("show");
}
//fin función para cargar el modal de registro de sedes

// función para el registro de sedes
function RegistrarSedes() {
    var Rnombre_sede = $("#Rnombre_sede").val()

    if ($.trim(Rnombre_sede) == '') {
        toastr.error("Ingresa el nombre de la sede.");
        $("#Rnombre_sede").focus();

    } else {
        $(".desactivarC").fadeIn(500);

        $.ajax({
            url: "Maggi/Sedes/RegistrarSedes",
            type: "POST",
            data: { 'Rnombre_sede': Rnombre_sede },

            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (json.success == true) {
                    toastr.success("Sede Registrada.");

                    $("#registrar-sede").modal("hide");
                    $('#tbl_sedes').DataTable().ajax.reload();
                    $("#Registrar-Sedes")[0].reset();
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "error");
                }
            }
        });

    }
}

function RegistrarEmpresa() {
    var Rnombre_sede = $("#Rnombre_sede").val()

    if ($.trim(Rnombre_sede) == '') {
        toastr.error("Ingresa el nombre de la sede.");
        $("#Rnombre_sede").focus();

    } else {
        $(".desactivarC").fadeIn(500);

        $.ajax({
            url: "Maggi/Sedes/RegistrarEmpresa",
            type: "POST",
            data: { 'Rnombre_sede': Rnombre_sede },

            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (json.success == true) {
                    toastr.success("Sede Registrada.");

                    $("#registrar-sede").modal("hide");
                    $('#tbl_empresas').DataTable().ajax.reload();
                    $("#Registrar-Sedes")[0].reset();
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "error");
                }
            }
        });

    }
}
//fin función para el registro de sedes

/// funcion para editar el estado de las sedes
function FeEditEstado(codigo, est) {
    var Mensaje;

    if (est == 'off') {
        Mensaje = "¿Está seguro de Deshabilitar este registro?";
    } else {
        Mensaje = "¿Está seguro de Habilitar este registro?";
    }
    $('.mensajeconfirm').html(Mensaje);

    $("#confirmar-ok").attr("onclick", "confirmar(" + codigo + ",'" + est + "')");
    $("#modalconfirmar").modal("show");
}

//funcion para confirmar estado
function confirmar(codigo, est) {
    $('.desactivarC').fadeIn(500);
    $.ajax({
            url: "Maggi/Sedes/EditarEstadoSedes",
            type: "POST",
            data: {
                codUsuario: codigo,
                estado: est
            }
        })
        //fin funcion para confirmar estado

    .done(function(json) {
            $('#tbl_sedes').DataTable().ajax.reload();
            $('.desactivarC').fadeOut(500);
            if (json.success == true) {
                toastr.success("Acción realizada");
                $('#modalconfirmar').modal("hide");
                $('#tbl_sedes').DataTable().ajax.reload();
                $('#tbl_empresas').DataTable().ajax.reload();

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
//fin editar estado de las sedes