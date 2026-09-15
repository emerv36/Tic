$(document).ready(function() {

});

//funcion para la consulta de la carga academica por docente y periodo
function ConsultaRecordModuloEstudiante() {
    var txtidentidad = $("#txtidentidad").val();
    if ($.trim(txtidentidad) == "") {
        toastr.error("¡Ingrese identidad!");
        $("#txtidentidad").focus();
    } else {
        $(".desactivarC").fadeIn(500);
        $("#resultado").show();
        $.ajax({
            url: "Sonico/OtrasOpciones/ConsultaRecordModuloEstudiante",
            type: "POST",
            data: {
                'txtidentidad': txtidentidad,
            },
            dataType: "JSON",
            success: function(json) {
                if (json.success === true) {

                    $("#tbl_modulos_estudiante tbody").html('<tr><td colspan="8" class="center">Cargado...</td></tr>');
                    lista = '';
                    var j = 1;
                    icono = '<i class="fa fa-trash-o" aria-hidden="true" title="Eliminar"></i>';
                    if (json.lista.length !== 0) {
                        $.each(json.lista, function(key, data) {

                            horario = '<span style="font-size:10px;color:red;">' + data.nombre_horario + '</span>';
                            dia = '<span style="font-size:10px;color:red;">' + data.dia_horario + '</span>';
                            sede = '<span style="font-size:10px;color:red;">' + data.nombre_sede + '</span>';
                            docente = '<span style="font-size:10px;color:red;">' + data.nombre_docente + ' ' + data.apellido_docente + '</span>';
                            curso = '<span style="font-size:12px;color:red;">' + data.codigo_curso + '</span>';

                            lista += '<tr>';
                            lista += '<td>' + j + '</td>';
                            lista += '<td>' + data.identificacion + '</td>';
                            lista += '<td>' + data.apellido_estudiante + ' ' + data.nombre_estudiante + '</td>';
                            lista += '<td>' + data.nombre_modulo + '<br>' + curso + '/' + docente + '</td > ';
                            lista += '<td>' + data.periodo + '</td>';
                            lista += '<td>' + data.nombre_programa + '<br>' + sede + '/' + horario + '/' + dia + '</td>';
                            lista += '<td><button data-toggle="tooltip" title="Click eliminar Modulo "     class="btn btn-danger btn-sm" id="btnDocente" data-toggle="modal"  data-target="ModalEliminarModulo"    onclick="ModalEliminarModulo(' + data.id_detalle + ',' + "'" + data.nombre_modulo + "'" + ',' + "'" + data.codigo_curso + "'" + ',' + "'" + data.periodo + "'" + ',' + "'" + data.nombre_estudiante + "'" + ',' + "'" + data.apellido_estudiante + "'" + ' )">' + icono + '</td>';
                            lista += '</tr>';
                            j++;

                        });

                    } else {
                        lista = '<tr><td colspan="10" class="center">Sin resultados</td></tr>';
                        $("#tbl_modulos_estudiante tbody").html('');

                    }
                    $("#tbl_modulos_estudiante tbody").html(lista);

                } else {
                    $("#tbl_modulos_estudiante tbody").html('<tr><td colspan="10" style="text-align:center">¡No se encontraron resultados!</td></tr>');
                }
                $(".desactivarC").fadeOut(500);
            },
        });
    }
}

//funcion para la carga del moda
function ModalEliminarModulo(id_detalle, nombre_modulo, codigo_curso, nombre_periodo, nombre_estudiante, apellido_estudiante) {
    $("#txtidetalle").val(id_detalle);
    $("#mensajeconfirmacion").html('¿Desea retirar el módulo <br> <b>' + ' ' + nombre_modulo + ' </b> <br> ' + 'Programado al estudiante <br>  <b>' + ' ' + apellido_estudiante + ' ' + nombre_estudiante + '</b><br>' + 'en el curso  <b>' + ' ' + codigo_curso + ' ' + '</b> <br> périodo académico <br> <b> ' + nombre_periodo + '</b>?');
    $("#ModalEliminarModulo").modal("show");

}
//funcion para la eliminación de la carga academica
function EliminarModulo() {
    var txtidetalle = $("#txtidetalle").val();

    $(".desactivarC").fadeIn(500);
    $.ajax({
        url: "Sonico/OtrasOpciones/EliminarModulo",
        type: "POST",
        data: {
            'txtidetalle': txtidetalle,
        },
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                $("#ModalEliminarModulo").modal("hide");
                mensaje("!Información¡", json.mensaje, "warning");
                ConsultaRecordModuloEstudiante();
                $(".desactivarC").fadeOut();

            } else {
                mensaje("!Información¡", json.mensaje, "warning");
                $("#ModalEliminarModulo").modal("hide");
            }
        }
    });
}

//funcion para la consulta de estudiantes para matricula de modulos adicionales
function ConsultaEstudiantesModuloAdicional() {
    var txtidentidadbuscar = $("#txtidentidadbuscar").val();
    if ($.trim(txtidentidadbuscar) == "") {
        toastr.error("¡Ingrese identidad!");
        $("#txtidentidadbuscar").focus();
    } else {
        $(".desactivarC").fadeIn(500);
        $("#resultado_estudiantes").show();
        $.ajax({
            url: "Sonico/OtrasOpciones/ConsultaEstudiantesModuloAdicional",
            type: "POST",
            data: {
                'txtidentidadbuscar': txtidentidadbuscar,
            },
            dataType: "JSON",
            success: function(json) {
                if (json.success === true) {

                    $("#tbl_estudiantes_modulo_adicional tbody").html('<tr><td colspan="8" class="center">Cargado...</td></tr>');
                    lista = '';
                    var j = 1;
                    icono = '<i class="	fa fa-legal" aria-hidden="true" title="Procesar módulo adicional"></i>';
                    if (json.lista.length !== 0) {
                        $.each(json.lista, function(key, data) {
                            lista += '<tr>';
                            lista += '<td>' + j + '</td>';
                            lista += '<td>' + data.identificacion + '</td>';
                            lista += '<td>' + data.apellido_estudiante + ' ' + data.nombre_estudiante + '</td>';
                            lista += '<td>' + data.codigo_curso + '</td>';
                            lista += '<td>' + data.nombre_modulo + '</td>';
                            lista += '<td>' + data.periodo + '</td>';
                            lista += '<td>' + data.nombre_sede + '/' + data.nombre_horario + '/' + data.dia_horario + '</td>';
                            lista += '<td>' + data.nombre_docente + ' ' + data.apellido_docente + '</td>';
                            lista += '<td><button data-toggle="tooltip" title="Procesar módulo adicional"     class="btn btn-primary btn-sm"  data-toggle="modal"  data-target="ModalInformacionEstudiante"    onclick="ModalInformacionEstudiante(' + data.id_detalle + ',' + "'" + data.nombre_modulo + "'" + ',' + "'" + data.codigo_curso + "'" + ',' + "'" + data.periodo + "'" + ',' + "'" + data.nombre_estudiante + "'" + ',' + "'" + data.apellido_estudiante + "'" + ',' + data.id_detalle_periodofk + ',' + data.id_inscripcion + ',' + "'" + data.nombre_sede + "'" + ',' + "'" + data.nombre_horario + "'" + ',' + "'" + data.dia_horario + "'" + ',' + data.id_programa_inscripcionfk + ',' + "'" + data.nombre_programa + "'" + ',' + data.id_sede_inscripcionfk + ')">' + icono + '</td>';
                            lista += '</tr>';
                            j++;
                        });
                    } else {
                        lista = '<tr><td colspan="10" class="center">Sin resultados</td></tr>';
                        $("#tbl_estudiantes_modulo_adicional tbody").html('');
                    }
                    $("#tbl_estudiantes_modulo_adicional tbody").html(lista);
                } else {
                    $("#tbl_estudiantes_modulo_adicional tbody").html('<tr><td colspan="10" style="text-align:center">¡No se encontraron resultados!</td></tr>');
                }
                $(".desactivarC").fadeOut(500);
            },
        });
    }
}

//funcion ver informacion de estudiante matricula modulo adicional
function ModalInformacionEstudiante(id_detalle, modulo, curso, periodo, nombre_estudiante, apellido_estudiante, id_periodo, id_estudiante, sede, horario, dia, idprogama, nombre_programa, idsede) {
    $(".desactivarC").fadeIn(500);
    $("#resultado_estudiantes").hide();
    $("#infobuscar").hide();
    $("#infoiddetalle").val(id_detalle);
    $("#infoidperiodo").val(id_periodo);
    $("#infoidestudiante").val(id_estudiante);
    $("#Infomodulo").val(modulo);
    $("#Infocurso").val(curso);
    $("#Infoperiodo").val(periodo);
    $("#InfoHorario").val(sede + '/' + horario + '/' + dia);
    $("#InfoNombre").val(nombre_estudiante + ' ' + apellido_estudiante);
    $("#infoidprograma").val(idprogama);
    $("#InfoPrograma").val(nombre_programa);
    $("#infoidsede").val(idsede);
    CargarRecordModulosEstudianteModuloAdicional();
    CargarPensumModuloAdicional();
    CompararRecordModulos();
    $("#matriculamoduloadiciona").show();
    $("#infobusqueda").show();
    $("#resultadobusqueda").hide();
    $(".desactivarC").fadeOut(500);
}

//funcion volver busqueda de estudiantes

function VolverBusqueda() {
    $(".desactivarC").fadeIn(500);
    $("#txtidentidadbuscar").val('');
    $("#resultado_estudiantes").hide();
    $("#infobuscar").show();
    $("#matriculamoduloadiciona").hide();
    $(".desactivarC").fadeOut(500);
}

//funcion para la carga de pensum de estudiante matricula modulo adiciona
function CargarPensumModuloAdicional() {
    var infoidsede = $("#infoidsede").val();
    var infoidprograma = $("#infoidprograma").val();
    $.ajax({
        url: "Sonico/OtrasOpciones/CargarPensumEstudiantes",
        type: "POST",
        data: {
            'infoidsede': infoidsede,
            'infoidprograma': infoidprograma,
        },
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {

                $("#tbl_plan_estudio tbody").html('<tr><td colspan="4" class="center">Cargado...</td></tr>');
                pensum = '';
                validar = '';
                var j = 1;
                var idestudiante = $("#infoidestudiante").val();
                if (json.pensum.length != 0) {
                    $.each(json.pensum, function(key, data) {
                        validar = '<button  id="btnvalidar' + j + '" title="Click buscar módulo" class="btn btn-primary sm fa fa-check-circle" Onclick="ValidarModuloAdicional(' + data.id_pensum_modulofk + ',' + idestudiante + ')"></button>';
                        pensum += '<tr>';
                        pensum += '<td>' + data.id_pensum_modulofk + '</td>';
                        pensum += '<td>' + data.nombre_modulo + '</td>';
                        pensum += '<td>' + data.int_horas_pensum + '</td>';
                        pensum += '<td>' + validar + '</td>';
                        pensum += '</tr>';
                        j++;
                    });

                } else {
                    pensum = '<tr><td colspan="4" class="center">Sin resultados</td></tr>';
                }
                $("#tbl_plan_estudio tbody").html(pensum);
            } else {
                $("#tbl_plan_estudio tbody").html('<tr><td colspan="4" style="text-align:center">' + json.mensaje + '</td></tr>');
            }
        },
    });

}

//funcion para la carga de modulos de estudiantes cruzados
function CargarRecordModulosEstudianteModuloAdicional() {
    var infoidestudiante = $("#infoidestudiante").val();
    var infoidprograma = $("#infoidprograma").val();
    $("#tbl_historial_recordmodulos tbody").html('');
    $.ajax({
        url: "Sonico/OtrasOpciones/CargarRecordModulosEstudianteModuloAdicional",
        type: "POST",
        data: {
            'infoidestudiante': infoidestudiante,
            'infoidprograma': infoidprograma,

        },
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                $("#tbl_historial_recordmodulos tbody").html('<tr><td colspan="4" class="center">Cargado...</td></tr>');
                lista = '';
                var j = 0;
                if (json.lista.length != 0) {
                    $.each(json.lista, function(key, data) {

                        lista += '<tr>';
                        lista += '<td>' + data.id_modulo_cargafk + '</td>';
                        lista += '<td>' + data.nombre_modulo + '</td>';
                        lista += '<td>' + data.periodo + '</td>';
                        lista += '<td>' + data.codigo_curso + '</td>';
                        lista += '</tr>';
                        j++;
                    });
                    $("#RecordVisto").val(j);

                } else {
                    lista = '<tr><td colspan="4" class="center">Sin resultados</td></tr>';
                }
                $("#tbl_historial_recordmodulos tbody").html(lista);
            } else {
                $("#tbl_historial_recordmodulos tbody").html('<tr><td colspan="4" style="text-align:center">' + json.mensaje + '</td></tr>');

            }
        },
    });
}

//funcion para compara los modulos programdos, plan de estudio y recor modulos
function CompararRecordModulos() {
    $('#tbl_historial_recordmodulos tbody tr').each(function() {
        var id = $(this).find('td').eq(0).text();
        $('#tbl_plan_estudio tbody tr').each(function() {

            if (id == $(this).find('td').eq(0).text()) {
                $(this).css({ "background-color": "#7FB3D5" });

            }

        });

    });

    /* */
    let i = 1;
    $('#tbl_plan_estudio tbody tr').each(function() {
        var color = $(this).css('background-color');

        if (color != 'rgb(127, 179, 213)') {
            $('#btnvalidar' + i).removeAttr('disabled');
        }
        i++;
    });

}

//funcion para validar el modulo adicional
function ValidarModuloAdicional(modulo, estudiante) {

    $.ajax({
        url: "Sonico/OtrasOpciones/ValidarModuloAdicional?modulos=" + modulo + "&estudiantes=" + estudiante,
        type: "GET",
        data: {},
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                mensaje("!Información¡", json.mensaje, "warning");
            } else {
                $("#infomodulo").val(modulo);
                BuscarModuloAdicional();
            }
        }
    });
}

//funcion para la busqueda de modulos adicional

//funcion para la carga de modulos de estudiantes cruzados
function BuscarModuloAdicional() {
    var infoidperiodo = $("#infoidperiodo").val();
    var infomodulo = $("#infomodulo").val();
    $("#infobusqueda").hide();
    $("#resultadobusqueda").show();

    $(".desactivarC").fadeIn(500);
    $("#tbl_modulos_encontrados tbody").html('');
    $.ajax({
        url: "Sonico/OtrasOpciones/BuscarModuloAdicional?infomodulo=" + infomodulo + "&infoperiodo=" + infoidperiodo,
        type: "GET",
        data: {},
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {

                $("#tbl_modulos_encontrados tbody").html('<tr><td colspan="7" class="center">Cargado...</td></tr>');
                lista = '';
                matricula = ''
                var j = 1;
                var idestudiante = $("#infoidestudiante").val();
                if (json.lista.length != 0) {
                    $.each(json.lista, function(key, data) {
                        matricula = '<button   title="Click matricula módulo" class="btn btn-primary sm fa fa-clipboard" Onclick="ModalMatriculaModuloAdicional(' + data.id_curso_cargafk + ',' + data.id_modulo_cargafk + ',' + data.id_periodo_cargafk + ',' + idestudiante + ',' + data.id_carga + ',' + data.id_docente_cargafk + ',' + "'" + data.nombre_modulo + "'" + ',' + "'" + data.periodo + "'" + ')"></button>';

                        horario = '<span style="font-size:10px;color:red;">' + data.nombre_horario + '</span>';
                        dia = '<span style="font-size:10px;color:red;">' + data.dia_horario + '</span>';
                        sede = '<span style="font-size:10px;color:red;">' + data.nombre_sede + '</span>';
                        docente = '<span style="font-size:10px;color:red;">' + data.nombre_docente + ' ' + data.apellido_docente + '</span>';
                        lista += '<tr>';

                        lista += '<tr>';
                        lista += '<td>' + j + '</td>';
                        lista += '<td>' + data.codigo_curso + '</td>';
                        lista += '<td>' + data.nombre_modulo + ' <br>' + docente + '</td>';
                        lista += '<td >' + data.nombre_programa + '<br>' + sede + '/' + horario + '/' + dia + ' </td>';

                        lista += '<td>' + data.periodo + '</td>';
                        lista += '<td>' + matricula + '</td>';
                        lista += '</tr>';
                        j++;
                    });

                } else {
                    lista = '<tr><td colspan="7" class="center">Sin resultados</td></tr>';
                }
                $("#tbl_modulos_encontrados tbody").html(lista);

            } else {
                $("#tbl_modulos_encontrados tbody").html('<tr><td colspan="7" style="text-align:center">' + json.mensaje + '</td></tr>');

            }
            $(".desactivarC").fadeOut(500);
        },
    });
}

//funcion para la matricula del modulo adicional
function ModalMatriculaModuloAdicional(curso, modulo, periodo, estudiante, carga, docente, nombre_modulo, nombre_periodo) {
    $("#MMcurso").val(curso);
    $("#MMmodulo").val(modulo);
    $("#MMperiodo").val(periodo);
    $("#MMcarga").val(carga);
    $("#MMdocente").val(docente);
    $("#MMestudiante").val(estudiante);
    $("#MensajeMatriculaModulo").html('¿Desea realizar la matricula adicional del módulo <b><br>' + ' ' + nombre_modulo + ' ' + '<br></b> en el périodo académico <b>' + ' ' + nombre_periodo + '' + '</b> ?')
    $("#ModalMatriculaModuloAdicional").modal("show");
}

//funcion para guardar el modulo adiciona del estudiante
function GuardarModuloAdicional() {
    var MMcurso = $("#MMcurso").val();
    var MMmodulo = $("#MMmodulo").val();
    var MMperiodo = $("#MMperiodo").val();
    var MMcarga = $("#MMcarga").val();
    var MMestudiante = $("#MMestudiante").val();
    var MMdocente = $("#MMdocente").val();

    $(".desactivarC").fadeIn(500);
    //ajax para guardar el modulo adicional
    $.ajax({
        url: "Sonico/OtrasOpciones/GuardarModuloAdicional",
        type: "POST",
        data: {
            'MMcurso': MMcurso,
            'MMmodulo': MMmodulo,
            'MMperiodo': MMperiodo,
            'MMcarga': MMcarga,
            'MMestudiante': MMestudiante,
            'MMdocente': MMdocente,

        },
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                $("#ModalMatriculaModuloAdicional").modal("hide");
                mensaje("!Información¡", json.mensaje, "warning");
                CargarRecordModulosEstudianteModuloAdicional();
                CargarPensumModuloAdicional();
                BuscarModuloAdicional();
                EnviarEmailMatriculaModuloAdicional();
                $("#infobusqueda").show();
                $("#resultadobusqueda").hide();

            } else {
                mensaje("!Información¡", json.mensaje, "warning");
                $("#ModalMatriculaModuloAdicional").modal("hide");
            }
        }
    });

    $(".desactivarC").fadeOut(500);
}

//funcion enviar enmail modulo adicional

//ajax para el envio del email al momento de la matricula del modulo adicinal

function EnviarEmailMatriculaModuloAdicional() {
    var MMmodulo = $("#MMmodulo").val();
    var MMperiodo = $("#MMperiodo").val();
    var MMestudiante = $("#MMestudiante").val();
    $.ajax({
        url: "Sonico/OtrasOpciones/EnviarEmailMatriculaModuloAdicional",
        type: "POST",
        data: {
            'MMmodulo': MMmodulo,
            'MMperiodo': MMperiodo,
            'MMestudiante': MMestudiante,

        },
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                $("#ModalMatriculaModuloAdicional").modal("hide");
                toastr.success(json.mensaje);
            } else {
                toastr.error(json.mensaje);
            }
        }
    });
}