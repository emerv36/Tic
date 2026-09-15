$(document).ready(function() {

    combobox('bRol', 'LibiaBrito/Roles/loadRol', 'Todos');
    combobox('nRol', 'LibiaBrito/Roles/loadRol', 'Seleccione...');

    floadRoles();

});

function floadRoles() {

    $('#tbl_roles').dataTable({
        ajax: "LibiaBrito/Roles/loadRoles",
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
            { "orderable": false }, //column 4
            { "class": "center", "orderable": false } //column 5
     //       { "class": "center", "orderable": false } //column 6
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

$("#frmEditar").submit(function(event) {
    event.preventDefault();
});

function buscar() {
    var nombre_rol = $("#bNombreRol").val();
    $('#tbl_roles tbody').html('<tr class="odd"><td colspan="6" class="dataTables_empty" valign="top">Cargando datos...</td></tr>');
    var url = "LibiaBrito/Roles/buscarRol&nombre_rol=" + nombre_rol;
    $('#tbl_roles').DataTable().ajax.url(url).load();
}

function fmodalEditar(cod, nombre, estado) {
    $("#txtCodrol").val(cod);
    $("#txtNombreRol").val(nombre);
    $("#nestado").val(estado);
    $('#modal-rolEdit').modal("focus");

}

function fEditarItem() {
    var txtCodrol = $("#txtCodrol").val();
    var txtNombreRol = $("#txtNombreRol").val();
    var estado = $("#nestado").val();

    if ($.trim(txtNombreRol) == '') {
        toastr.error('Por favor, ingrese Nombre de Perfil.');
        $("#txtNombreRol").focus();

    } else if ($.trim(estado) == '') {
        toastr.error('Por favor, seleccione Estado de Perfil.');
        $("#nestado").focus();

    } else {
        $('.desactivarC').fadeIn(500);
        $.ajax({
            url: "LibiaBrito/Roles/editItemRol",
            type: "POST",
            data: {
                'codrol': txtCodrol,
                'nombre_rol': txtNombreRol,
                'estado_rol': estado
            },
            dataType: "JSON",
            success: function(json) {
                if (json.success == true) {
                    toastr.success("Item Actualizado exitosamente");
                    $("#frmEdit")[0].reset();
                    $("#modal-rolEdit").modal("hide");
                    $('#tbl_roles').DataTable().ajax.reload();
                    $('.desactivarC').fadeIn(500);
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

function feditEstado(cod, est) {
    var palabreo;

    if (est == 'off') {
        palabreo = "¿Está seguro de Inhabilitar este Rol?";
    } else {
        palabreo = "¿Está seguro de Habilitar este Rol?";
    }
    $('.mensajeconfirm').html(palabreo);

    $("#btn-confirm-ok").attr("onclick", "acceptConfirm(" + cod + ",'" + est + "')");

    //$('#dialog-confirm').modal("hide");

}

function acceptConfirm(cod, est) {
    $('.desactivarC').fadeIn(500);
    $.ajax({
            url: "LibiaBrito/Roles/editEstadoRoles",
            type: "POST",
            data: {
                'codusuario': cod,
                'estado': est
            }
        })
        .done(function(data) {
            $('.desactivarC').fadeOut(500);
            if (data.success == true) {
                
                $('#tbl_roles').DataTable().ajax.reload();
                toastr.success("Acción realizada exitosamente", "Maestro de Usuarios");

            } else {
                toastr.error("Request failed: " + data.mensaje);
            }
        })
        .fail(function(jqXHR, textStatus) {
            $('.desactivarC').fadeOut(500);
            toastr.error("Request failed: " + jqXHR.responseText);
        });
            $('#dialog-confirm').modal("hide");
}

//funcion para mostrar el modal del manual de gestión de roles
    function infoGrupos(){
        $("#modalInformacion").modal("show");
    }
//funcion para mostrar el modal del manual de gestión de roles