$(document).ready(function() {
    combobox('bRol', 'LibiaBrito/Perfiles/loadRol', 'Todos');

    fLoadPerfiles();

});

function fLoadPerfiles() {

    $('#tbl_perfiles').dataTable({
        ajax: "LibiaBrito/Perfiles/loadPerfiles",
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
            [10, 15, 20, -1],
            [10, 15, 20, "Todos"] // change per page values here
        ],
        "columns": [ //agregar configuraciones a cada una de las columnas de las tablas
            {}, //column 1
            { "orderable": false }, //column 2
            { "orderable": false }, //column 3
            { "orderable": false }, //column 4
            { "orderable": false }//colum 5
      
        ],
        initComplete: function(oSettings, json) {
            // $('[data-rel="tooltip"]').tooltip(); 
        },
        "iDisplayLength": 10
    });

}

$("#frmBusqueda").submit(function(event) {
    event.preventDefault();
});
$("#frmNuevo").submit(function(event) {
    event.preventDefault();
});
$("#frmEditar").submit(function(event) {
    event.preventDefault();
});

function buscar() {
    var nombre_menu = $("#bNombremenu").val();
    $('#tbl_menu tbody').html('<tr class="odd"><td colspan="6" class="dataTables_empty" valign="top">Cargando datos...</td></tr>');

    var url = "Hefesto/buscarMenu&nombre_menu=" + nombre_menu + "&nivel=" + nivel + "&padre=" + padre;
    $('#tbl_perfiles').DataTable().ajax.url(url).load();
}

function fmodalNuevo() {
    $('#modal-perfilNuevo').modal("show");
}

function fNuevoItem() {
    var nNombrePerfil = $("#nNombrePerfil").val();
    var nestado = $("#nestado").val();

    if ($.trim(nNombrePerfil) == '') {
        toastr.error('Por favor, ingrese Nombre de Perfil.');
        $("#nNombrePerfil").focus();

    } else if ($.trim(nestado) == '') {
        toastr.error('Por favor, seleccione Estado de Perfil.');
        $("#nestado").focus();

    } else {
        $('.desactivarC').fadeIn(500);
        $.ajax({
            url: " LibiaBrito/Perfiles/insertItemPerfil",

            type: "POST",
            data: {
                'nombre_perfil': nNombrePerfil,
                'estado_perfil': nestado
            },
            dataType: "JSON",
            success: function(json) {
                if (json.success == true) {
                    toastr.success("Item agregado exitosamente");
                    $("#frmNuevo")[0].reset();
                    $("#modal-perfilNuevo").modal("hide")

                    $('#tbl_perfiles').DataTable().ajax.reload();
                } else {
                    toastr.error(json.mensaje);
                }
            },
            complete: function() {
                $('.desactivarC').fadeOut(500);
            }
        });
    }
}

function fmodalEditar(cod,estado) {
    $("#txtCodPerfil").val(cod);
    $("#estado").val(estado);
    $("#modal-EditPerfil").modal("show");
}

function fEditarItem() {
    var txtCodPerfil = $("#txtCodPerfil").val();
    var estado = $("#estado").val();

if ($.trim(estado) == '') {
        toastr.error('Por favor, seleccione Estado de Perfil.');
        $("#estado").focus();

    } else {
        $('.desactivarC').fadeIn(500);
        $.ajax({
            url: " LibiaBrito/Perfiles/editItemPerfil",
            type: "POST",
            data: {
                'codperfil': txtCodPerfil,
                'estado_perfil': estado
            },
            dataType: "JSON",
            success: function(json) {
                if (json.success == true) {
                    toastr.success("Item Actualizado exitosamente");
                    $("#frmEditar")[0].reset();

                    $("#modal-EditPerfil").modal("hide");
                    $('#tbl_perfiles').DataTable().ajax.reload();

                } else {
                    toastr.error(json.mensaje);
                }
            },
            complete: function() {
                $('.desactivarC').fadeOut(500);
            }
        });
    }
}

//funcion para mostrar el modal del manual de gestión de perfiles
    function infoGrupos(){
        $("#modalInformacion").modal("show");
    }
//funcion para mostrar el modal del manual de gestión de perfiles