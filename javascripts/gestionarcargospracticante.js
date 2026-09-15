$(document).ready(function() {
    ListarCargoPracticante();
    combobox('REmpresaPracticante', 'Willie/Programas/CargarEmpresas', 'Seleccione una opción.');
    combobox('EEmpresaPracticante', 'Willie/Programas/CargarEmpresas', 'Seleccione una opción.');
    $('.dataTables_filter input[type="search"]').css({ 'width': '350px', 'display': 'inline-block' });
    $('[data-toggle="tooltip"]').tooltip();
});

function ModalRegistroCargosPracticate() {
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
                    $('#tbl_cargos_practicante').DataTable().ajax.reload();
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "error");
                }
            }
        });
    }
}

function ModalEditarCargoPracticante(id_programa, nombre_programa, sede) {
    $("#idCargoPracticante").val(id_programa);
    $("#ECargoPracticante").val(nombre_programa);
    $("#EEmpresaPracticante").val(sede);
    $("#modal_editar_cargo_practicante").modal("show");
}

function editarCargoPracticante() {
    var idCargoPracticante = $("#idCargoPracticante").val();
    var ECargoPracticante = $("#ECargoPracticante").val();
    var EEmpresaPracticante = $("#EEmpresaPracticante").val();
    if ($.trim(ECargoPracticante) == '') {
        toastr.error("Ingrese nombre del cargo.");
        $("#ECargoPracticante").focus();

    } else if ($.trim(EEmpresaPracticante) == '') {
        toastr.error("Seleccione Sede");
        $("#EEmpresaPracticante").focus();

    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Willie/Programas/EditarCargoPracticante",
            type: "POST",
            data: {
                'id_cargo_practicante': idCargoPracticante,
                'cargo_practicante': ECargoPracticante,
                'empresa_practicante': EEmpresaPracticante,
            },
            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (json.success == true) {
                    toastr.success("Información actualizada");
                    $("#modal_editar_cargo_practicante").modal("hide");
                    $("#form_editar_cargo_practicante")[0].reset();
                    $('#tbl_cargos_practicante').DataTable().ajax.reload();
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "error");
                }
            }
        });
    }
}

function ListarCargoPracticante() {
    $('#tbl_cargos_practicante').dataTable({
        ajax: "Willie/Programas/ListarCargoPracticante",
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

function EditarEstadoCargoPracticante(codigo, est) {
    var Mensaje;
    if (est == 'off') {
        Mensaje = "¿Está seguro de Deshabilitar este programa?";
    } else {
        Mensaje = "¿Está seguro de Habilitar este programa?";
    }
    $('.mensajeconfirm').html(Mensaje);
    $("#btn_estado").attr("onclick", "confirmarCambiarEstado(" + codigo + ",'" + est + "')");
    $("#modaCambiarEstado").modal("show");
}

function confirmarCambiarEstado(codigo, est) {
    $('.desactivarC').fadeIn(500);
    $.ajax({
            url: "Willie/Programas/EditarEstadoCargoPracticante",
            type: "POST",
            data: {
                'id_cargo_practicante': codigo,
                'estado': est
            }
        })
        //fin funcion para confirmar estado de los salones
        .done(function(json) {
            $('#tbl_cargos_practicante').DataTable().ajax.reload();
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
    $("#modaCambiarEstado").modal("hide");
}