$(document).ready(function() {
    combobox('lote_etapas', 'Marge/Inscripcion/CargarMisLotes', 'Seleccione lote...');
    $(".select2").select2();
});

// Cosas/Utiles/

function cambioVista(origen, destino, form){
    $(".desactivarC").slideDown();
    $(origen).hide();
    $(destino).show();
    $(form)[0].reset();
    $(".desactivarC").fadeOut(500);
    $(".select2").trigger("change");
}

function guardarExcel(nombre_archivo) {
    //$(".desactivarC").fadeIn(500);
    //$("#tabla_reporte").show();
    element = document.getElementById("tabla_reporte");
    setTimeout(() => {
        var wb = XLSX.utils.table_to_book(element);
        XLSX.writeFile(wb, nombre_archivo + '.xlsx', { compression: true });
    }, 200);
    setTimeout(() => {
        //$("#tabla_reporte").hide();
        //$(".desactivarC").fadeOut(500);
    }, 1000);
}

function ReporteAgrupadoLote() {
    //var check_todos = $("#chk").val();
    var lote_etapas = $("#lote_etapas").val();
    if($("#chk").is(":checked")){
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Cosas/Utiles/reporteEtapa",
            type: "POST",
            dataType: "JSON",
            success: function (json) {
                if (json.success == true) {
                    toastr.success(json.mensaje);
                    $("#tabla_reporte thead").html(json.cabecera);
                    $("#tabla_reporte tbody").html(json.cuerpo);
                    setTimeout(() => {
                        guardarExcel(json.nombre_archivo);
                    }, 200);
                } else {
                    swal({
                        title: "¡Error!",
                        text: json.mensaje,
                        icon: "error"
                    });
                }
                $(".desactivarC").fadeOut(500);
            }
        });
    } else {
        if(lote_etapas != ''){
            $(".desactivarC").fadeIn(500);
            $.ajax({
                url: "Cosas/Utiles/reporteEtapaPorLote",
                type: "POST",
                data: {
                    'lote': lote_etapas
                },
                dataType: "JSON",
                success: function (json) {
                    if (json.success == true) {
                        toastr.success(json.mensaje);
                        $("#tabla_reporte thead").html(json.cabecera);
                        $("#tabla_reporte tbody").html(json.cuerpo);
                        setTimeout(() => {
                            guardarExcel(json.nombre_archivo);
                        }, 200);
                    } else {
                        swal({
                            title: "¡Error!",
                            text: json.mensaje,
                            icon: "error"
                        });
                    }
                    $(".desactivarC").fadeOut(500);
                }
            });
        } else {
            toastr.error("Elige lote");
        }
        
    }
}

function reporteRangoFecha() {
    var fecha1 = $("#fecha1").val();
    var fecha2 = $("#fecha2").val();
    var estado = $("#estado_fecha").val();
    if(fecha1 == ''){
        toastr.error("Elija una fecha");
        $("#fecha1").focus();
    } else if(fecha2 == ''){
        toastr.error("Elija una fecha");
        $("#fecha2").focus();
    } else {
        if (estado != ""){
            $(".desactivarC").fadeIn(500);
            $.ajax({
                url: "Cosas/Utiles/reporteRangoFechaEstado",
                type: "POST",
                data: {
                    'fecha1': fecha1,
                    'fecha2': fecha2,
                    'estado': estado
                },
                dataType: "JSON",
                success: function (json) {
                    if (json.success == true) {
                        toastr.success(json.mensaje);
                        $("#tabla_reporte thead").html(json.cabecera);
                        $("#tabla_reporte tbody").html(json.cuerpo);
                        setTimeout(() => {
                            guardarExcel(json.nombre_archivo);
                        }, 200);
                    } else {
                        swal({
                            title: "¡Error!",
                            text: json.mensaje,
                            icon: "error"
                        });
                    }
                    $(".desactivarC").fadeOut(500);
                }
            });
        } else {
            $(".desactivarC").fadeIn(500);
            $.ajax({
                url: "Cosas/Utiles/reporteRangoFecha",
                type: "POST",
                data: {
                    'fecha1': fecha1,
                    'fecha2': fecha2
                },
                dataType: "JSON",
                success: function (json) {
                    if (json.success == true) {
                        toastr.success(json.mensaje);
                        $("#tabla_reporte thead").html(json.cabecera);
                        $("#tabla_reporte tbody").html(json.cuerpo);
                        setTimeout(() => {
                            guardarExcel(json.nombre_archivo);
                        }, 200);
                    } else {
                        swal({
                            title: "¡Error!",
                            text: json.mensaje,
                            icon: "error"
                        });
                    }
                    $(".desactivarC").fadeOut(500);
                }
            });
        }
    }
    
}

function reporteEstado(){
    var estado = $("#estadoR_estado").val();
    if($("#chkestado").is(":checked")){
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Cosas/Utiles/reporteEstadoTodos",
            type: "POST",
            dataType: "JSON",
            success: function (json) {
                if (json.success == true) {
                    toastr.success(json.mensaje);
                    $("#tabla_reporte thead").html(json.cabecera);
                    $("#tabla_reporte tbody").html(json.cuerpo);
                    setTimeout(() => {
                        guardarExcel(json.nombre_archivo);
                    }, 200);
                } else {
                    swal({
                        title: "¡Error!",
                        text: json.mensaje,
                        icon: "error"
                    });
                }
                $(".desactivarC").fadeOut(500);
            }
        });
    } else {
        if(estado != ''){
            $(".desactivarC").fadeIn(500);
            $.ajax({
                url: "Cosas/Utiles/reporteEstado",
                type: "POST",
                data: {
                    'estado': estado
                },
                dataType: "JSON",
                success: function (json) {
                    if (json.success == true) {
                        toastr.success(json.mensaje);
                        $("#tabla_reporte thead").html(json.cabecera);
                        $("#tabla_reporte tbody").html(json.cuerpo);
                        setTimeout(() => {
                            guardarExcel(json.nombre_archivo);
                        }, 200);
                    } else {
                        swal({
                            title: "¡Error!",
                            text: json.mensaje,
                            icon: "error"
                        });
                    }
                    $(".desactivarC").fadeOut(500);
                }
            });
        } else {
            toastr.error("Elige estado");
        }
        
    }
}

function reporteTipo(){
    var tipo_carnet = $("#tipo_carnet").val();
    if($("#chkestado_tipo").is(":checked")){
        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Cosas/Utiles/reporteTipoTodos",
            type: "POST",
            dataType: "JSON",
            success: function (json) {
                if (json.success == true) {
                    toastr.success(json.mensaje);
                    $("#tabla_reporte thead").html(json.cabecera);
                    $("#tabla_reporte tbody").html(json.cuerpo);
                    setTimeout(() => {
                        guardarExcel(json.nombre_archivo);
                    }, 200);
                } else {
                    swal({
                        title: "¡Error!",
                        text: json.mensaje,
                        icon: "error"
                    });
                }
                $(".desactivarC").fadeOut(500);
            }
        });
    } else {
        if(tipo_carnet != ''){
            $(".desactivarC").fadeIn(500);
            $.ajax({
                url: "Cosas/Utiles/reporteTipo",
                type: "POST",
                data: {
                    'tipo': tipo_carnet
                },
                dataType: "JSON",
                success: function (json) {
                    if (json.success == true) {
                        toastr.success(json.mensaje);
                        $("#tabla_reporte thead").html(json.cabecera);
                        $("#tabla_reporte tbody").html(json.cuerpo);
                        setTimeout(() => {
                            guardarExcel(json.nombre_archivo);
                        }, 200);
                    } else {
                        swal({
                            title: "¡Error!",
                            text: json.mensaje,
                            icon: "error"
                        });
                    }
                    $(".desactivarC").fadeOut(500);
                }
            });
        } else {
            toastr.error("Elige tipo de carnet");
        }
        
    }
}