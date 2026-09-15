$(document).ready(function() {
    ListarLotes();
    $('[data-mask]').inputmask();
});

// datatable para listar lotes
function ListarLotes() {
    $('#tbl_lotes').dataTable({
        ajax: "Melon/Lotes/ListarLotes",
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
        "paging": true,
        "aaSorting": [
            [0, 'asc']
        ],
        "aLengthMenu": [
            [10, 20, 30, 40, -1],
            [10, 20, 30, 40, "Todos"] // change per page values here
        ],
        "columns": [ //agregar configuraciones a cada una de las columnas de las tablas
            {}, //column 1
            { "class": "center", "orderable": false }, //column 1
            { "class": "center", "orderable": false }, //column 2
            { "class": "center", "orderable": false }, //column 3
            { "class": "center", "orderable": false }, //column 4
            { "class": "center", "orderable": false }, //column 5
            { "class": "center", "orderable": false }, //column 6
         ],
        initComplete: function(oSettings, json) {
            // $('[data-rel="tooltip"]').tooltip(); 
        },
        "iDisplayLength": 10
    });
}
/// funcion para editar el estado de los lotes
function FeEditEstado(codigo, est) {
    var Mensaje;
    if (est == 'off') {
        Mensaje = "¿Está seguro de Deshabilitar este registro?";
    } else {
        Mensaje = "¿Está seguro de Habilitar este registro?";
    }
    $('.mensajeconfirm').html(Mensaje);
    $("#btn-confirm-ok").attr("onclick", "corfirmar(" + codigo + ",'" + est + "')");
    $("#modalconfirmar").modal("show");
}
//funcion para confirmar estado de los salones
function corfirmar(codigo, est) {
    $('.desactivarC').fadeIn(500);
    $.ajax({
            url: "Melon/Lotes/EditarEstadoLote",
            type: "POST",
            data: {
                'codUsuario': codigo,
                'estado': est
            }
        })
        //fin funcion para confirmar estado de los salones
        .done(function(json) {
            $('#tbl_lotes').DataTable().ajax.reload();
            $('.desactivarC').fadeOut(500);
            if (json.success === true) {
                toastr.success("Acción realizada exitosamente");
                $('#tbl_horario').DataTable().ajax.reload();
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
//fin editar estado de los salones


//función ajax para actualizar los lotes
function GuardarLote() {
    var Rcodigolote = $("#Rcodigolote").val();
    var RtipoLote = $("#RtipoLote").val();
    var RestadoLote = $("#RestadoLote").val();


    if ($.trim(Rcodigolote) === '') {
        toastr.error("Ingrese código");
        $("#Rcodigolote").focus();

    } else if ($.trim(RtipoLote) === '') {
        toastr.error("Seleccione tipo");
        $("#RtipoLote").focus()

    } else if (RestadoLote.length === 0) {
        toastr.error("Seleccione estado");
        $("#RestadoLote").focus();

    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Melon/Lotes/RegistrarLote",
            type: "POST",
            data: {
                'Rcodigolote': Rcodigolote,
                'RtipoLote': RtipoLote,
                'RestadoLote': RestadoLote,
            },
            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (json.success === true) {
                    toastr.success("Información exitosamente.");
                    $('#tbl_lotes').DataTable().ajax.reload();
                    $("#registrar-lotes").modal("hide");
                    $("#Registrar-Lotes")[0].reset();
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "warning");
                }
            }
        });
    }
}
//fin función 

//función para cargar el modal de registrar lotes
function ModalRegistroLote() {
    $("#registrar-lotes").modal("show");
    $("#Registrar-Lotes")[0].reset();
}
//fin función 


//función para cargar el modal de editar lotes
function ModalEditarLote(id_lote, etapa, codigo, estado) {
    $("#txtidlote").val(id_lote);
    $("#txttipoLote").val(etapa);
    $("#txtCodigoLote").val(codigo);
    $("#txtEstadoLote").val(estado);
    $("#editar-lotes").modal("show");

}
//fin función 



//función ajax para registrar los programas
function EditarLotes() {
    var txtidlote = $("#txtidlote").val();
    var txtCodigoLote = $("#txtCodigoLote").val();
    var txttipoLote = $("#txttipoLote").val();
    var txtEstadoLote = $("#txtEstadoLote").val();

    if ($.trim(txtCodigoLote) === '') {
        toastr.error("Código lote.");
        $("#txtCodigoLote").focus();

    }
    if ($.trim(txttipoLote) === '') {
        toastr.error("Seleccione tipo.");
        $("#txttipoLote").focus();

    } else if (txtEstadoLote.length === 0) {
        toastr.error("Seleccione estado.");
        $("#txtEstadoLote").focus();

    } else {

        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Melon/Lotes/EditarLotes",
            type: "POST",
            data: {
                'txtidlote': txtidlote,
                'txtCodigoLote': txtCodigoLote,
                'txttipoLote': txttipoLote,
                'txtEstadoLote': txtEstadoLote,

            },
            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (json.success === true) {
                    toastr.success("Información exitosamente.");
                    $('#tbl_lotes').DataTable().ajax.reload();
                    $("#editar-lotes").modal("hide");
                    $("#Editar-Lotes")[0].reset();
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "warning");
                    $(".desactivarC").fadeOut(500);
                }
            }
        });
    }

}