$(document).ready(function() {

    $('[data-toggle="tooltip"]').tooltip();

    //funcion para listar la informacion del plantel
    $.ajax({
        url: "Nikki/Plantel/ListarPlantel",
        success: function(json) {

            $("#id_plantel").val(json.id_plantel);
            $("#nit_plantel").val(json.nit_plantel);
            $("#nombre_plantel").val(json.nombre_plantel);
            $("#nombre_corto").val(json.nombre_corto);
            $("#direccion_plantel").val(json.direccion_plantel);
            $("#ciudad_plantel").val(json.ciudad_plantel);
            $("#Pnit").html(json.nit_plantel);
            $("#Pnombre").html(json.nombre_plantel);
            $("#Pnombre_corto").html(json.nombre_corto);
            $("#Pciudad").html(json.ciudad_plantel);
            $("#Pciudad").html(json.ciudad_plantel);
            $("#Desempeno").val(json.concepto_des);
            $("#Conocimiento").val(json.concepto_con);
            $("#Producto").val(json.concepto_pro);
            $("#Pdes").val(json.porcentaje_des);
            $("#Pcon").val(json.porcentaje_con);
            $("#Ppro").val(json.porcentaje_pro);
            $("#Pdese").val(json.pordesequi);
            $("#Pcone").val(json.porconequi);
            $("#Pproe").val(json.porproequi);
            $("#Dnota").val(json.notadesequi);
            $("#Cnota").val(json.notaconequi);
            $("#Pnota").val(json.notaproequi);
            $("#cantidad_minima").val(json.cantidad_minima);

        }
    });

    //buscar porcentajes

    $("#btn-concepto").click(function() {
        $.ajax({
            url: "Nikki/Plantel/BuscarPorcentaje",
            success: function(json) {

                if (json.suma_por.length == 0) {
                    $("#sumpor").val(100);
                } else {
                    $("#sumpor").val(json.suma_por);
                }
            }

        });

        $.ajax({
            url: "Nikki/Plantel/ContarRegistros",
            success: function(json) {

                $("#cancon").val(json.total);

            }

        });

    });

    ListarConceptos();

    ///validar la nota minima 
    $("#plantel_nota_aprobada").blur(function() {
        var plantel_nota_maxima = $("#plantel_nota_maxima").val();
        var nota_aprobada = $("#plantel_nota_aprobada").val();
        if (nota_aprobada > plantel_nota_maxima) {
            toastr.error("error la nota minima no puede ser mayor");
        } else {
            var porcentaje;
            porcentaje = parseFloat(nota_aprobada) / parseFloat(plantel_nota_maxima);
            var new_por = porcentaje.toFixed(2);
            $("#porcentaje_aprobado").val(new_por);
        }
    });

    $("#plantel_nota_maxima").blur(function() {
        var plantel_nota_maxima = $("#plantel_nota_maxima").val();
        var nota_aprobada = $("#plantel_nota_aprobada").val();
        if (plantel_nota_maxima.length > 0) {
            var porcentaje;
            porcentaje = parseFloat(nota_aprobada) / parseFloat(plantel_nota_maxima);
            var new_por = porcentaje.toFixed(2);
            $("#porcentaje_aprobado").val(new_por);
        }
    });

    $("#Pdes").change(function() {
        var Pdes = $("#Pdes").val();
        var plantel_nota_maxima = $("#plantel_nota_maxima").val();

        if ($("#Pdes").length > 0) {
            var porcentaje, nota;
            porcentaje = parseInt(Pdes) / 100;
            nota = (plantel_nota_maxima * parseInt(Pdes)) / 100;
            var new_por = porcentaje.toFixed(2);
            var new_nota = nota.toFixed(2);
            $("#Pdese").val(new_por);
            $("#Dnota").val(new_nota);
        }
    });

    $("#Ppro").change(function() {
        var Ppro = $("#Ppro").val();
        var Pdes = $("#Pdes").val();
        var plantel_nota_maxima = $("#plantel_nota_maxima").val();

        var pordis, portot;

        portot = parseInt(Ppro) + parseInt(Pdes);
        pordis = 100 - portot;

        if (pordis <= 0) {
            mensaje("¡Advertencia!", "Debes ingresar un porcentaje menor", "warning");

            $("#Ppro").focus();
            $("#Ppro").val('');
            $("#Pproe").val('');

        } else {

            var porcentaje;
            porcentaje = parseFloat(Ppro) / 100;
            var new_por = porcentaje.toFixed(2);

            nota = (plantel_nota_maxima * parseInt(Ppro)) / 100;
            var new_nota = nota.toFixed(2);

            $("#Pproe").val(new_por);
            $("#Pnota").val(new_nota);

        }

    });

    $("#Pcon").change(function() {
        var Ppro = $("#Ppro").val();
        var Pdes = $("#Pdes").val();
        var Pcon = $("#Pcon").val();
        var plantel_nota_maxima = $("#plantel_nota_maxima").val();

        var pordis, portot;

        portot = parseInt(Ppro) + parseInt(Pdes) + parseInt(Pcon);
        pordis = 100 - portot;

        if (pordis > 0) {
            swal("¡Advertencia!", "Debes ingresar un porcentaje mayor", "warning");
            $("#Pcon").focus();
            $("#Pcon").val('');
            $("#Pcone").val('');

        } else if (pordis < 0) {
            swal("¡Advertencia!", "Debes ingresar un porcentaje menor", "warning");
            $("#Pcon").focus();
            $("#Pcon").val('');
            $("#Pcone").val('');

        } else {

            var porcentaje;
            porcentaje = parseFloat(Pcon) / 100;
            var new_por = porcentaje.toFixed(2);

            nota = (plantel_nota_maxima * parseInt(Pcon)) / 100;
            var new_nota = nota.toFixed(2);

            $("#Pcone").val(new_por);
            $("#Cnota").val(new_nota);

        }

    });

});

//fin función para el registro de sedes

// función para el registro del plantel
function RegistrarPlantel() {
    var mail = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
    var id_plantel = $("#id_plantel").val()
    var nit_plantel = $("#nit_plantel").val()
    var nombre_plantel = $("#nombre_plantel").val();
    var nombre_corto = $("#nombre_corto").val();
    var direccion_plantel = $("#direccion_plantel").val();
    var ciudad_plantel = $("#ciudad_plantel").val();

    if ($.trim(nit_plantel) == '') {
        toastr.error("Ingresa Nit es obligatorio.");
        $("#nit_plantel").focus();

    } else if ($.trim(nombre_plantel) == '') {
        toastr.error("Ingresa nombre institución educativa");
        $("#nombre_plantel").focus();

    } else if ($.trim(nombre_corto) == '') {
        toastr.error("Ingresa nombre corto");
        $("#nombre_corto").focus();

    } else if ($.trim(direccion_plantel) == '') {
        toastr.error("Ingresa dirección");
        $("#direccion_plantel").focus();

    } else if ($.trim(ciudad_plantel) == '') {
        toastr.error("Ingresa ciudad");
        $("#ciudad_plantel").focus();

    } else {
        $(".desactivarC").fadeIn(500);

        $.ajax({
            url: "Nikki/Plantel/RegistrarPlantel",
            type: "POST",
            data: {
                'id_plantel': id_plantel,
                'nit_plantel': nit_plantel,
                'nombre_plantel': nombre_plantel,
                'nombre_corto': nombre_corto,
                'direccion_plantel': direccion_plantel,
                'ciudad_plantel': ciudad_plantel,


            },
            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (json.success == true) {
                    toastr.success("¡Información registrada!");
                } else {
                    mensaje("¡Advertencia!", json.mensaje, "error");

                }
            }
        });

    }
}

//funcion registrar conceptos de evaluación

function RegistrarParametros() {

    var id_plantel = $("#id_plantel").val();
    var plantel_nota_maxima = $("#plantel_nota_maxima").val();
    var plantel_nota_aprobada = $("#plantel_nota_aprobada").val();
    var porcentaje_aprobado = $("#porcentaje_aprobado").val();
    var Desempeno = $("#Desempeno").val();
    var Pdes = $("#Pdes").val();
    var Pdese = $("#Pdese").val();
    var Dnota = $("#Dnota").val();
    var Producto = $("#Producto").val();
    var Ppro = $("#Ppro").val();
    var Pproe = $("#Pproe").val();
    var Pnota = $("#Pnota").val();
    var Conocimiento = $("#Conocimiento").val();
    var Pcon = $("#Pcon").val();
    var Pcone = $("#Pcone").val();
    var Cnota = $("#Cnota").val();
    var cantidad_minima = $("#cantidad_minima").val();

    if ($.trim(plantel_nota_maxima) == '') {
        toastr.error("Ingresa Nota máxima.");
        $("#plantel_nota_maxima").focus();

    } else if ($.trim(plantel_nota_aprobada) == '') {
        toastr.error("Ingrese nota minima");
        $("#plantel_nota_aprobada").focus();

    } else if ($.trim(cantidad_minima) == '') {
        toastr.error("Ingrese cantidad minima de estudiantes por curso");
        $("#cantidad_minima").focus();

    } else if ($.trim(Desempeno) == '') {
        toastr.error("Seleccione concepto de evaluación");
        $("#Desempeno").focus();

    } else if ($.trim(Pdes) == '') {
        toastr.error("ingrese porcentaje");
        $("#Pdes").focus();

    } else if ($.trim(Producto) == '') {
        toastr.error("Seleccione concepto de evaluacón");
        $("#Producto").focus();

    } else if ($.trim(Ppro) == '') {
        toastr.error("Ingrese porcentaje");
        $("#Ppro").focus();

    } else if ($.trim(Conocimiento) == '') {
        toastr.error("Seleccione Concepto de evaluación");
        $("#Conocimiento").focus();

    } else if ($.trim(Pcon) == '') {
        toastr.error("Ingrese porcentaje");
        $("#Pcon").focus();

    } else {
        $(".desactivarC").fadeIn(500);

        $.ajax({
            url: "Nikki/Plantel/RegistrarParametros",
            type: "POST",
            data: {
                'id_plantel': id_plantel,
                'plantel_nota_maxima': plantel_nota_maxima,
                'plantel_nota_aprobada': plantel_nota_aprobada,
                'porcentaje_aprobado': porcentaje_aprobado,
                'cantidad_minima': cantidad_minima,
                'Desempeno': Desempeno,
                'Pdes': Pdes,
                'Conocimiento': Conocimiento,
                'Pcon': Pcon,
                'Producto': Producto,
                'Ppro': Ppro,
                'Dnota': Dnota,
                'Cnota': Cnota,
                'Pnota': Pnota,
                'Pdese': Pdese,
                'Pproe': Pproe,
                'Pcone': Pcone
            },

            dataType: "JSON",
            success: function(json) {
                $(".desactivarC").fadeOut(500);
                if (json.success == true) {
                    toastr.success("¡Información registrada!");

                } else {

                    mensaje("¡Advertencia!", json.mensaje, "error");

                }
            }
        });

    }
}

//funcion listar los conceptos de evaluacion

function ListarConceptos() {
    $.ajax({
        url: "Nikki/Plantel/ListarConceptos",
        data: {},
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {

                $("#tbl_concepto tbody").html('<tr><td colspan="4" class="center">Cargado...</td></tr>');
                lista = '';
                var i = 1;
                var totpor = 0;
                totnota = 0;

                if (json.lista.length != 0) {
                    $.each(json.lista, function(key, data) {
                        lista += '<tr>';
                        lista += '<td>' + i + '</td>';
                        lista += '<td>' + data.concepto + '</td>';
                        lista += '<td>' + data.porcentaje + '%' + '</td>';
                        lista += '<td>' + data.nota_equivalente + '</td>';
                        lista += '<td>' + 'Editar' + '</td>';

                        lista += '</tr>';
                        i++;
                        totpor = totpor + data.porcentaje;
                        totnota = totnota + data.nota_equivalente;

                    });

                } else {
                    lista = '<tr><td colspan="4" class="center">Sin resultados</td></tr>';
                }
                $("#tbl_concepto tbody").html(lista);
            } else {
                $("#tbl_concepto tbody").html('<tr><td colspan="4" style="text-align:center">¡No se encontraron conceptos!</td></tr>');

            }
        },
    });
}