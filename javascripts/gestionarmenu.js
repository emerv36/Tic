$(document).ready(function() {

    floadMenu();

    $("#bNivel").on("change", function() {
        var nivel = $("#bNivel").val();
        if (nivel == '1') {
            $(".bdivpadre").hide();

        } else if (nivel == '2') {
            $.ajax({
                url: "Lopersa/Menu/loadPadresMenu",
                type: "POST",
                data: {},
                dataType: "JSON",
                success: function(json) {
                    if (json.success == true) {
                        html = '<option value="" selected>Todos</option>'; //<option value="" disabled selected>Seleccione</option>
                        $.each(json.menu, function(key, data) {
                            html += '<option value="' + data.codigo_menu + '">' + data.nombre_menu + '</td>';
                        });
                        $("#bPadre").html(html);
                    }
                },
                complete: function() {
                    $(".bdivpadre").show();
                }
            });
        } else {
            $(".bdivpadre").hide();
        }
    });

    $("#nnivel").on("change", function() {
        var nivel = $("#nnivel").val();
        if (nivel == '1') {
            $("#ntxtLink").val("#");
            $(".dPadre").hide();

        } else if (nivel == '2') {
            $("#ntxtLink").val("");
            $.ajax({
                url: "Lopersa/Menu/loadPadresMenu",
                type: "POST",
                data: {},
                dataType: "JSON",
                success: function(json) {
                    if (json.success == true) {
                        html = ''; //<option value="" disabled selected>Seleccione</option>
                        $.each(json.menu, function(key, data) {
                            html += '<option value="' + data.codigo_menu + '">' + data.nombre_menu + '</td>';
                        });
                        $("#npadre").html(html);
                    }
                },
                complete: function() {
                    $(".dPadre").show();
                }
            });
        } else {
            $(".dPadre").hide();
        }
    });

});

function floadMenu() {

    $('#tbl_menu').DataTable({
        ajax: "Lopersa/Menu/loadMenu",
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
            { "class": "center", "orderable": false }, //column 2
            { "orderable": false }, //column 3
            { "orderable": false }, //column 4
            { "orderable": false }, //column 5
            { "class": "center", "orderable": false }, //column 6
            { "orderable": false }, //column 7
            { "orderable": false }, //column 8
            { "class": "center", "orderable": false } //column 9
        ],
        "iDisplayLength": 10
    });

}

function buscar() {
    var nombre_menu = $("#bNombremenu").val();
    var nivel = $("#bNivel").val();
    var padre = $("#bPadre").val();

    $('#tbl_menu tbody').html('<tr class="odd"><td colspan="9" class="dataTables_empty" valign="top">Cargando datos...</td></tr>');

    var url = "Lopersa/Menu/buscarMenu?nombre_menu=" + nombre_menu + "&nivel=" + nivel + "&padre=" + padre;
    $('#tbl_menu').DataTable().ajax.url(url).load();

}

function fmodalNuevo() {
    $('#modal-menu').on('shown.bs.modal', function() {
        $('#modal-menu').trigger("show");
    })

}

function fNuevoItem() {
    var ntxtIcono = $("#ntxtIcono").val();
    var ntxtNombreMenu = $("#ntxtNombreMenu").val();
    var nnivel = $("#nnivel").val();
    var ntxtLink = $("#ntxtLink").val();
    var npadre = $("#npadre").val();
    var nestado = $("#nestado").val();

    if ($.trim(ntxtIcono) == '') {
        toastr.error('Por favor, ingrese Icono de Menú.');
        $("#ntxtIcono").focus();

    } else if ($.trim(ntxtNombreMenu) == '') {
        toastr.error('Por favor, ingrese Nombre de Menú.');
        $("#ntxtNombreMenu").focus();

    } else if ($.trim(nnivel) == '') {
        toastr.error('Por favor, seleccione Nivel de Menú.');
        $("#nnivel").focus();

    } else if ($.trim(nnivel) == 2 && $.trim(npadre) == '') {
        toastr.error('Por favor, seleccione Padre de Menú.');
        $("#npadre").focus();

    } else if ($.trim(nestado) == '') {
        toastr.error('Por favor, seleccione Estado de Menú.');
        $("#nestado").focus();

    } else {
        $('.desactivarC').fadeIn(500);
        $.ajax({
            url: "Lopersa/Menu/insertItemMenu",
            type: "POST",
            data: {
                'icono': ntxtIcono,
                'nombre_menu': ntxtNombreMenu,
                'nivel': nnivel,
                'link': ntxtLink,
                'padre': npadre,
                'estado': nestado
            },
            dataType: "JSON",
            success: function(json) {
                if (json.success == true) {
                    toastr.success("Item agregado exitosamente");
                    $("#frmNuevo")[0].reset();
                    $(".dPadre").hide();
                    $("#modal-menuNew").modal("hide");

                    $('#tbl_menu').DataTable().ajax.reload();
                    reloadmenu();
                    cargarMenuDelUsuario();
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

function modalEdit(cod, imagen, nombre, link, estado) {
    $("#txtCodmenu").val(cod);
    $("#txtIcono").val(imagen);
    $("#txtNombreMenu").val(nombre);
    $("#txtLink").val(link);
    $("#estado").val(estado);
    $("#modal-menuEdit").modal("show");

}

function fEditarItem() {
    var txtCodmenu = $("#txtCodmenu").val();
    var txtIcono = $("#txtIcono").val();
    var txtNombreMenu = $("#txtNombreMenu").val();
    var txtLink = $("#txtLink").val();
    var estado = $("#estado").val();

    if ($.trim(txtIcono) == '') {
        toastr.error('Por favor, ingrese Icono de Menú.');
        $("#txtIcono").focus();

    } else if ($.trim(txtNombreMenu) == '') {
        toastr.error('Por favor, ingrese Nombre de Menú.');
        $("#txtNombreMenu").focus();

    } else if ($.trim(txtLink) == '') {
        toastr.error('Por favor, seleccione Nivel de Menú.');
        $("#txtLink").focus();

    } else if ($.trim(estado) == '') {
        toastr.error('Por favor, seleccione Estado de Menú.');
        $("#estado").focus();

    } else {
        $('.desactivarC').fadeIn(500);
        $.ajax({
            url: "Lopersa/Menu/editItemMenu",
            type: "POST",
            data: {
                'codmenu': txtCodmenu,
                'icono': txtIcono,
                'nombre_menu': txtNombreMenu,
                'link': txtLink,
                'estado': estado
            },
            dataType: "JSON",
            success: function(json) {
                if (json.success == true) {
                    toastr.success("Item Actualizado exitosamente");
                    $("#modal-menuEdit").modal("hide");
                    $("#frmEdit")[0].reset();
                    $('#tbl_menu').DataTable().ajax.reload();
                    reloadmenu();
                    cargarMenuDelUsuario();
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

