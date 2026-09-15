$(document).ready(function() {
    combobox('txtperfil', 'Lisa/Usuarios/cargarperfiles', 'Seleccione un Perfil...');
    combobox('perfil', 'Lisa/Usuarios/cargarperfiles', 'Seleccione un Perfil...');
    ListarUsuarios();

});

// Datatable para el listado de usuarios
function ListarUsuarios() {
    $('#tbl_usuarios').dataTable({
        ajax: "Lisa/Usuarios/ListarUsuarios", //ruta del httacess
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
        "searching": true,
        "aaSorting": [
            [0, 'asc']
        ],
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
            { "class": "center", "orderable": true }, //column 7
            { "class": "center", "orderable": true }, //column 7

        ],
        initComplete: function(oSettings, json) {

        },
        "iDisplayLength": 5
    });
}
// fin listado de usuarios

/// funcion para editar el estado del usuario
function FeEditEstado(cod, est) {
    var palabreo;

    if (est == 'off') {
        palabreo = "¿Está seguro de Deshabilitar este Usuario?";
    } else {
        palabreo = "¿Está seguro de Habilitar este Usuario?";
    }
    $('.mensajeconfirm').html(palabreo);

    $("#confirmar-ok").attr("onclick", "acceptConfirm(" + cod + ",'" + est + "')");

    //$('#dialog-confirm').modal("hide");

}

function acceptConfirm(cod, est) {
    $('.desactivarC').fadeIn(500);
    $.ajax({
            url: "Lisa/Usuarios/EditarEstadoUsuario",
            type: "POST",
            data: {
                'codusuario': cod,
                'estado': est
            }
        })
        .done(function(data) {
            $('.desactivarC').fadeOut(500);
            if (data.success == true) {
                toastr.success("Acción realizada exitosamente", "Maestro de Usuarios");
                $('#modalconfirmar').modal("hide");

                $('#tbl_usuarios').DataTable().ajax.reload();

            } else {
                //toastr.error("Request failed: " + data.mensaje);
                toastr.success("Acción realizada exitosamente", "Maestro de Usuarios");
                $('#tbl_usuarios').DataTable().ajax.reload();
            }
        })
        .fail(function(jqXHR, textStatus) {
            $('.desactivarC').fadeOut(500);
            toastr.error("Request failed: " + jqXHR.responseText);

        });
    $('#modalconfirmar').modal("hide");
}
//fin editar estado del usuarios

// funciónn para cargar el modal de editar usuarios, con sus respectivos valores
function ModalEditarUsuarios(cod, id, email, nombre, perfil, telefono, direccion, fecha, estado) {
    $("#codigo").val(cod);
    $("#identificacion").val(id);
    $("#email").val(email);
    $("#nombre").val(nombre);
    $("#perfil").val(perfil);
    $("#telefono").val(telefono);
    $("#direccion").val(direccion);
    $("#fecha").val(fecha);
    $("#estado").val(estado);
    $("#editar-usuario").modal("hide");

}
//fin función para cargar el modal de editar usuarios, con sus respectivos valores

//función Editar datos del usuario

function EditarUsuario() {
    var mail = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
    var numeros = /^[0-9]*$/;
    var codigo = $("#codigo").val();
    var nombre = $("#nombre").val();
    var telefono = $("#telefono").val();
    var direccion = $("#direccion").val();
    var perfil = $("#perfil").val();
    var fecha = $("#fecha").val();
    var estado = $("#estado").val();

    if ($.trim(nombre) == '') {
        toastr.error("Ingrese nombre.");
        $("#nombre").focus();

    } else if ($.trim(telefono) == '') {
        toastr.error("Ingrese teléfono");
        $("#telefono").focus();

    } else if (!numeros.test($.trim($("#telefono").val()))) {
        toastr.error("Ingrese solo números");
        $("#telefono").focus();

    } else if ($.trim(direccion) == '') {
        toastr.error("Ingrese dirección.");
        $("#direccion").focus();

    } else if ($.trim(perfil) == '') {
        toastr.error("Seleccioen pérfil");
        $("#perfil").focus();

    } else if ($.trim(fecha) == '') {
        toastr.error("Seleccione fecha de nacimiento");
        $("#fecha").focus();

    } else if ($.trim(estado) == '') {
        toastr.error("Seleccione estado");
        $("#fecha").focus();

    } else {
        $('.desactivarC').fadeIn(500);

        $.ajax({
            url: "Lisa/Usuarios/EditarUsuarios",
            type: "POST",
            data: {
                'codigo': codigo,
                'nombre': nombre,
                'telefono': telefono,
                'direccion': direccion,
                'perfil': perfil,
                'fecha': fecha,
                'estado': estado,

            },
            dataType: "JSON",

            success: function(json) {
                $('.desactivarC').fadeOut(500);

                if (json.success == true) {

                    toastr.success("Datos actualizados correctamente.");
                    $("#frmEditar")[0].reset();
                    $("#editar-usuario").modal("hide");
                    $('#tbl_usuarios').DataTable().ajax.reload();

                } else {

                    mensaje("¡Advertencia!", json.mensaje, "error");
                }
            }

        })
    }

}
//fin función Editar datos del usuario

//funcion para el modal de crar un nuevo usuario
function fModalNuevoUsuario() {
    $('#registrar-usuario').modal("show");
}
//fin funcion para el modal de crar un nuevo usuario

//funcion para registrar a un nuevo usuario
function RegistrarUsuarios() {
    var mail = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
    var num = /^[0-9]*$/;
    var txtid = $("#txtid").val();
    var txtemail = $("#txtemail").val();
    var txtnombre = $("#txtnombre").val();
    var txtperfil = $("#txtperfil").val();
    var txttelefono = $("#txttelefono").val();
    var txtdir = $("#txtdir").val();
    var txtfecha = $("#txtfecha").val();
    var txtestado = $("#txtestado").val();

    if ($.trim(txtid) == '') {
        toastr.error("Por favor ingrese la Identificación.");
        $("#txtid").focus();

    } else if (!num.test($.trim($("#txtid").val()))) {
        toastr.error("Ingrese solo números en el campo (Identificación.)");
        $("#txtid").focus();

    } else if ($.trim(txtemail) == '') {
        toastr.error("Por favor Ingrese el Email.");
        $("#txtemail").focus();

    } else if (!mail.test($.trim(txtemail))) {
        toastr.error("Email-Invalido");
        $("#txtemail").focus();

    } else if ($.trim(txtnombre) == '') {
        toastr.error("Por favor. ingrese el nombre del Usuario.");
        $("#txtnombre").focus();

    } else if ($.trim(txtperfil) == '') {
        toastr.error("Por favor, seleccione un Perfil.");
        $("#txtPerfil").focus();

    } else if ($.trim(txttelefono) == '') {
        toastr.error("Por favor. ingrese el telefono del Usuario.");
        $("#txttelefono").focus();

    } else if (!num.test($.trim(txttelefono))) {
        toastr.error("Ingrese solo números en el campo (Telefono.)");
        $("#txttelefono").focus();

    } else if ($.trim(txtdir) == '') {
        toastr.error("Por favor. ingrese la dirección del Usuario.");
        $("#txtdir").focus();

    } else if ($.trim(txtfecha) == '') {
        toastr.error("Por favor, ingrese la fecha de nacimiento del Usuario");
        $("#txtfecha").focus();

    } else if ($.trim(txtestado) == '') {
        toastr.error("Por favor, Seleccione estado");
        $("#txtestado").focus();

    } else {
        $('.desactivarC').fadeIn(900);
        $.ajax({
            url: "Lisa/Usuarios/InsertarUsuarios",
            type: "POST",
            data: {
                'txtid': txtid,
                'txtemail': txtemail,
                'txtnombre': txtnombre,
                'txtperfil': txtperfil,
                'txttelefono': txttelefono,
                'txtdir': txtdir,
                'txtfecha': txtfecha,
                'txtestado': txtestado,
            },
            dataType: "JSON",
            success: function(data) {
                $('.desactivarC').fadeOut(500);
                if (data.success == true) {
                    mensaje("¡Información!", "Usuario creado exitosamente.Se le ha enviado un correo de confirmación al usuario creado, para la confirmación de su correo.", "success");
                    $("#frmNuevo")[0].reset();
                    $("#registrar-usuario").modal("hide")
                    $('#tbl_usuarios').DataTable().ajax.reload();
                } else {
                    mensaje("¡Advertencia!", data.mensaje, "error");
                }
            }
        });

    }
}
//fin funcion para registrar a un nuevo usuario