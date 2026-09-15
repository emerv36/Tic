$(document).ready(function() {
    combobox('RversionReporte', 'Tony/Reportes/CargarVersion', 'Seleccione una opción...');
    combobox('RplantelReporte', 'Tony/Reportes/CargarPlantel', 'Seleccione una opción...');
    ///combobox de edición de registro
    combobox('EversionReporte', 'Tony/Reportes/CargarVersion', 'Seleccione una opción...');
    combobox('EplantelReporte', 'Tony/Reportes/CargarPlantel', 'Seleccione una opción...');

    ListarReportes();
    ListarVersion();
});
// datatable para listar los Reportes
function ListarReportes() {
    $('#tbl_reportes').dataTable({
        ajax: "Tony/Reportes/ListarReportes",
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
        responsive: true,
        dom: 'lBfrtip',
        buttons: [{
            extend: 'excelHtml5',
            text: '<i class="fa fa-file-excel-o"></i>',
            titleAttr: 'Exportar Excel',
            className: 'btn btn-success',
        }],
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
            { "class": "center", "orderable": false }, //column 1
            { "class": "center", "orderable": false }, //column 2
            { "class": "center", "orderable": false }, //column 3
            { "class": "center", "orderable": false }, //column 4
            { "class": "center", "orderable": false }, //column 5
            { "class": "center", "orderable": false }, //column 6
            { "class": "center", "orderable": false }, //column 7


        ],
        initComplete: function(oSettings, json) {
            //  $('[data-rel="tooltip"]').tooltip();
        },
        "iDisplayLength": 6
    });
}
//fin datatable para listar los Reportes



function ListarVersion() {
    $('#tbl_version_reporte').dataTable({
        ajax: "Tony/Reportes/ListarVersion",
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
            { "class": "center", "orderable": false }, //column 1
            { "class": "center", "orderable": false }, //column 2
            { "class": "center", "orderable": false }, //column 3
        ],
        initComplete: function(oSettings, json) {
            //  $('[data-rel="tooltip"]').tooltip();
        },
        "iDisplayLength": 6
    });
}
//fin datatable para listar la version de los reportes


/// funcion para etidar el estado de los reportes
function ModalEditEstadoReportes(codigo, est) {
    var Mensaje;
    if (est == 'off') {
        Mensaje = "¿Está seguro de Deshabilitar este reporte?";
    } else {
        Mensaje = "¿Está seguro de Habilitar este reporte?";
    }
    $('.mensajeconfirm').html(Mensaje);
    $("#confirmar-ok").attr("onclick", "corfirmar(" + codigo + ",'" + est + "')");
    $('#modalconfirmar').modal("show");
}
//fin funcion



//funcion para confirmar estado de los reportes
function corfirmar(codigo, est) {

    $('.desactivarC').fadeIn(500);
    $.ajax({
            url: "Tony/Reportes/EditarEstadoReportes",
            type: "POST",
            data: {
                codreporte: codigo,
                est: est
            }
        })
        //fin funcion para confirmar estado
        .done(function(json) {
            $('#tbl_reportes').DataTable().ajax.reload();
            $('.desactivarC').fadeOut(500);
            if (json.success == true) {
                toastr.success("Acción realizada exitosamente", "Señor de Usuarios");
                $('#tbl_reportes').DataTable().ajax.reload();
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
} //fin funcion




//función para cargar el modal de editar reportes
function ModalEditarReportes(EcodigoReporte, EnombreReporte, EnombreArchivo, EversionReporte, EplantelReporte, EestadoReporte, EidReporte) {
    $("#EcodigoReporte").val(EcodigoReporte);
    $("#EnombreReporte").val(EnombreReporte);
    $("#EnombreArchivo").val(EnombreArchivo);
    $("#EversionReporte").val(EversionReporte);
    $("#EplantelReporte").val(EplantelReporte);
    $("#EestadoReporte").val(EestadoReporte);
    $("#EidReporte").val(EidReporte);
    $("#editar-reportes").modal("show");
}
//fin función para cargar el modal de editar reportes


// función ajax para actualizar info de reportes
function actualizarReportes() {
    var EcodigoReporte = $("#EcodigoReporte").val();
    var EnombreReporte = $("#EnombreReporte").val();
    var EnombreArchivo = $("#EnombreArchivo").val();
    var EversionReporte = $("#EversionReporte").val();
    var EplantelReporte = $("#EplantelReporte").val();
    var EestadoReporte = $("#EestadoReporte").val();
    var EidReporte = $("#EidReporte").val();

    if ($.trim(EcodigoReporte) == '') {
        toastr.error("Por favor, asignele código al reporte ");
        $("#EcodigoReporte").focus();
    } else if ($.trim(EnombreReporte) == '') {
        toastr.error("Por favor, asignele nombre al reporte");
        $("#EnombreReporte").focus();
    } else if ($.trim(EnombreArchivo) == '') {
        toastr.error("Por favor, asignele nombre al archivo");
        $("#EnombreArchivo").focus();
    } else if ($.trim(EversionReporte) == '') {
        toastr.error("Por favor seleccione versión al reporte.");
        $("#EversionReporte").focus();
    } else if ($.trim(EplantelReporte) == '') {
        toastr.error("Por favor seleccione institución educativa.");
        $("#EplantelReporte").focus();
    } else if ($.trim(EestadoReporte) == '') {
        toastr.error("Por favor seleccione, el estado del reporte.");
        $("#EestadoReporte").focus();
    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Tony/Reportes/ActualizarReportes",
            type: "POST",
            data: {
                'EcodigoReporte': EcodigoReporte,
                'EnombreReporte': EnombreReporte,
                'EnombreArchivo': EnombreArchivo,
                'EversionReporte': EversionReporte,
                'EplantelReporte': EplantelReporte,
                'EestadoReporte': EestadoReporte,
                'EidReporte': EidReporte,
            },
            dataType: "JSON",
            success: function(response) {
                $(".desactivarC").fadeOut();
                if (response.success == true) {
                    toastr.success("Información actualizada correctamente.");
                    $("#editar-reportes").modal("hide");
                    $("#Editar-Reportes")[0].reset();
                    $('#tbl_reportes').DataTable().ajax.reload();
                } else {
                    toastr.error(response.mensaje);
                }
            }
        });
    }
}
//fin función 

//función para cargar el modal de resgistrar reportes
function ModalRegistrarReportes() {
    $("#registrar-reportes").modal("show");
}
// fin función cargar modal para registrar periodos



//función para cargar el modal de resgistrar versiones de reportes
function ModalVersionReportes() {
    $("#registrar-version").modal("show");
}
// fin función 


//función para cargar el modal de resgistrar versiones de reportes
function ModalEditarVersion(id, nombre, fecha) {
    $("#Eideversion").val(id);
    $("#EnombreVersion").val(nombre);
    $("#EfechaVersion").val(fecha);
    $("#editar-version").modal("show");
}
// fin función 


//funcion para registrar las versiones de os reportes//

function RegistrarVersion() {
    var RnombreVersion = $("#RnombreVersion").val();
    var RfechaVersion = $("#RfechaVersion").val();
    if ($.trim(RnombreVersion) == '') {
        toastr.error("Por favor, Ingrese el nombre de la version .");
        $("#RnombreVersion").focus();
    } else if ($.trim(RfechaVersion) == '') {
        toastr.error("Por favor, ingrese fecha de vigencia.");
        $("#RfechaVersion").focus();
    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Tony/Reportes/RegistrarVersion",
            type: "POST",
            data: {
                'RnombreVersion': RnombreVersion,
                'RfechaVersion': RfechaVersion
            },
            dataType: "JSON",
            success: function(response) {
                $(".desactivarC").fadeOut();
                if (response.success == true) {
                    toastr.success("Información registrada exitosamente.");
                    $("#registrar-version").modal("hide");
                    $("#Registrar-Version")[0].reset();
                    $('#tbl_version_reporte').DataTable().ajax.reload();
                } else {
                    toastr.error(response.mensaje);
                }
            }
        });
    }
}

//fin funcion

//funcion para la actualizacion de los veriones 
function ActualizarVersion() {
    var Eideversion = $("#Eideversion").val();
    var EnombreVersion = $("#EnombreVersion").val();
    var EfechaVersion = $("#EfechaVersion").val();
    if ($.trim(EnombreVersion) == '') {
        toastr.error("Por favor, Ingrese el nombre de la version .");
        $("#EnombreVersion").focus();
    } else if ($.trim(EfechaVersion) == '') {
        toastr.error("Por favor, ingrese fecha de vigencia.");
        $("#EfechaVersion").focus();
    } else {
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Tony/Reportes/ActualizarVersion",
            type: "POST",
            data: {
                'Eideversion': Eideversion,
                'EnombreVersion': EnombreVersion,
                'EfechaVersion': EfechaVersion,
            },
            dataType: "JSON",
            success: function(response) {
                $(".desactivarC").fadeOut();
                if (response.success == true) {
                    toastr.success(response.mensaje);
                    $("#editar-version").modal("hide");
                    $("#Editar-Version")[0].reset();
                    $('#tbl_version_reporte').DataTable().ajax.reload();
                } else {
                    toastr.error(response.mensaje);
                }
            }
        });
    }
}




// función ajax para registrar info de reportes
function RegistrarReportes() {
    var RcodigoReporte = $("#RcodigoReporte").val();
    var RnombreReporte = $("#RnombreReporte").val();
    var RnombreArchivo = $("#RnombreArchivo").val();
    var RversionReporte = $("#RversionReporte").val();
    var RplantelReporte = $("#RplantelReporte").val();
    var RestadoReporte = $("#RestadoReporte").val();
    if ($.trim(RcodigoReporte) == '') {
        toastr.error("Por favor, ingrese el codigo para el reporte .");
        $("#RcodigoReporte").focus();

    } else if ($.trim(RnombreReporte) == '') {
        toastr.error("Por favor, ingrese nombre para el Reporte.");
        $("#RnombreReporte").focus();

    } else if ($.trim(RnombreArchivo) == '') {
        toastr.error("Por favor, ingrese nombre para el archivo.");
        $("#RnombreArchivo").focus();

    } else if ($.trim(RversionReporte) == '') {
        toastr.error("Por favor, seleccione la versión para el reporte.");
        $("#RversionReporte").focus();

    } else if ($.trim(RplantelReporte) == '') {
        toastr.error("Por favor, seleccione la versión del reporte.");
        $("#RplantelReporte").focus();


    } else if ($.trim(RestadoReporte) == '') {
        toastr.error("Por favor, seleccione estado.");
        $("#RestadoReporte").focus();
    } else {

        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Tony/Reportes/RegistrarReportes",
            type: "POST",
            data: {
                'RcodigoReporte': RcodigoReporte,
                'RnombreReporte': RnombreReporte,
                'RnombreArchivo': RnombreArchivo,
                'RversionReporte': RversionReporte,
                'RplantelReporte': RplantelReporte,
                'RestadoReporte': RestadoReporte,
            },
            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut();
                if (json.success == true) {
                    toastr.success("Información registrada exitosamente.");
                    $("#registrar-reportes").modal("hide");
                    $("#Registrar-Reportes")[0].reset();
                    $('#tbl_reportes').DataTable().ajax.reload();
                } else {
                    toastr.error(jsonreportes.mensaje);
                }
            }
        });
    }
}
//fin función ajax para actualizar info de reportes