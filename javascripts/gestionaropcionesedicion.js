$(document).ready(function() {
    //funcion para la carga de la sede para los estudiantes matriculados
    combobox('txtcmbsede', 'Apu/Modulos/CargarSedes', 'Seleccione una sede...');
    //funcion para la carga de la sede para los estudiantes inscritos
    combobox('txtcmbsedeinscrito', 'Apu/Modulos/CargarSedes', 'Seleccione una sede...');

    //funcion para la carga de la sede de los estudiantes matriculados
    $("#txtcmbsede").change(function() {
        $("#txtcmbprograma").val('');
        $("#txtcmbhorario").val('');
        $("#txtcmbcurso").val('');
        var periodos = $("#txtPidperiodo").val();
        var sedes = $("#txtcmbsede").val();

        combobox('txtcmbprograma', 'Marge/Inscripcion/CargarProgramasOferta?sedes=' + sedes + '&periodos=' + periodos, 'Seleccione un programa...');

    });
    //funcion para la carga de los estudiantes inscritos
    $("#txtcmbsedeinscrito").change(function() {
        $("#txtcmbprogramainscrito").val('');
        $("#txtcmbhorarioinscrito").val('');
        var periodos = $("#idperiodo").val();
        var sedes = $("#txtcmbsedeinscrito").val();
        combobox('txtcmbprogramainscrito', 'Marge/Inscripcion/CargarProgramasOferta?sedes=' + sedes + '&periodos=' + periodos, 'Seleccione un programa...');

    });

    //funcion para la carga de horarios segun el programas seleccionado estudiantes matriculados
    $("#txtcmbprograma").change(function() {
        $("#txtcmbhorario").val('');
        $("#txtcmbcurso").val('');
        var sede = $("#txtcmbsede").val();
        var periodo = $("#txtPidperiodo").val();
        var programa = $("#txtcmbprograma").val();

        combobox('txtcmbhorario', 'Marge/Inscripcion/CargarHorariosOferta?sedes=' + sede + '&periodos=' + periodo + '&programas=' + programa, 'Seleccione...');
    });

    //funcion para la carga de los estudiantes inscritos 
    $("#txtcmbprogramainscrito").change(function() {
        $("#txtcmbhorarioinscrito").val('');
        var sede = $("#txtcmbsedeinscrito").val();
        var periodo = $("#idperiodo").val();
        var programa = $("#txtcmbprogramainscrito").val();
        combobox('txtcmbhorarioinscrito', 'Marge/Inscripcion/CargarHorariosOferta?sedes=' + sede + '&periodos=' + periodo + '&programas=' + programa, 'Seleccione...');
    });

    //funcion para la carga de los curso estudiantes matriculados
    $("#txtcmbhorario").change(function() {
        $("#txtcmbcurso").val('');
        var periodo = $("#txtPidperiodo").val();
        var sede = $("#txtcmbsede").val();
        var programa = $("#txtcmbprograma").val();
        var horario = $("#txtcmbhorario").val();

        combobox('txtcmbcurso', 'Bunny/OpcionesEdicion/CargarCursosCambioPrograma?periodos=' + periodo + '&sedes=' + sede + '&programas=' + programa + '&horarios=' + horario, 'Seleccione curso');
    });

});

//funcion para la consulta de estudiantes para el cambio de programa
function ConsultarEstudianteMatriculado() {
    var txtidentidad = $("#txtidentidad").val();
    if ($.trim(txtidentidad) == "") {
        toastr.error("¡Ingrese identidad!");
        $("#txtidentidad").focus();
    } else {
        $(".desactivarC").fadeIn(500);
        $("#resultado").show();
        $.ajax({
            url: "Bunny/OpcionesEdicion/ConsultaEstudianteMatriculado",
            type: "POST",
            data: {
                'txtidentidad': txtidentidad,
            },
            dataType: "JSON",
            success: function(json) {
                if (json.success === true) {

                    modal = '';

                    $("#tbl_estudiantes_registrados tbody").html('<tr><td colspan="8" class="center">Cargado...</td></tr>');

                    lista = '';
                    var j = 1;
                    if (json.lista.length !== 0) {
                        $.each(json.lista, function(key, data) {

                            if (data.valor_registro == '1') {
                                modal = '<button data-toggle="tooltip" title="Click Cambiar Programa "  class="btn btn-primary btn-sm fa fa-edit" data-toggle="modal"    onclick="ModalCambiarProgramaInscrito(' + data.id_inscripcion + ',' + "'" + data.identificacion + "'" + ',' + "'" + data.nombre_estudiante + "'" + ',' + "'" + data.apellido_estudiante + "'" + ',' + "'" + data.periodo + "'" + ',' + "'" + data.nombre_programa + "'" + ',' + "'" + data.nombre_sede + "'" + ',' + "'" + data.nombre_horario + "'" + ',' + "'" + data.dia_horario + "'" + ',' + data.id_periodo_inscripcionfk + ')"></button>';
                            } else if (data.valor_registro == '2') {
                                modal = '<button data-toggle="tooltip" title="Click Cambiar Programa "  class="btn btn-primary btn-sm fa fa-edit" data-toggle="modal"     onclick="ModalCambiarProgramaMatricula(' + data.id_inscripcion + ',' + "'" + data.identificacion + "'" + ',' + "'" + data.nombre_estudiante + "'" + ',' + "'" + data.apellido_estudiante + "'" + ',' + "'" + data.periodo + "'" + ',' + "'" + data.nombre_programa + "'" + ',' + "'" + data.nombre_sede + "'" + ',' + "'" + data.nombre_horario + "'" + ',' + "'" + data.dia_horario + "'" + ',' + data.id_periodo_inscripcionfk + ')"></button>';

                            }

                            lista += '<tr>';
                            lista += '<td>' + j + '</td>';
                            lista += '<td>' + data.identificacion + '</td>';
                            lista += '<td>' + data.apellido_estudiante + ' ' + data.nombre_estudiante + '</td>';
                            lista += '<td>' + data.email_estudiante + '</td>';
                            lista += '<td>' + data.nombre_programa + '</td>';
                            lista += '<td>' + data.periodo + '</td>';
                            lista += '<td>' + data.nombre_sede + '/' + data.nombre_horario + '/' + data.dia_horario + '</td>';
                            lista += '<td>' + data.fecha_inscripcion + '</td>';
                            lista += '<td>' + data.estado_registro + '</td>';
                            lista += '<td>' + modal + '</td>';

                            lista += '</tr>';
                            j++;

                        });

                    } else {
                        lista = '<tr><td colspan="10" class="center">Sin resultados</td></tr>';
                        $("#tbl_estudiantes_registrados tbody").html('');

                    }
                    $("#tbl_estudiantes_registrados tbody").html(lista);

                } else {
                    $("#tbl_estudiantes_registrados tbody").html('<tr><td colspan="10" style="text-align:center">¡No se encontraron resultados!</td></tr>');
                }
                $(".desactivarC").fadeOut(500);
            },
        });
    }
}

//funcion para la consulta de estudiantes para el cambio de periodo de incico
function ConsultaEstudianteInscritos() {
    var Pidentidad = $("#Pidentidad").val();
    if ($.trim(Pidentidad) == "") {
        toastr.error("¡Ingrese identidad!");
        $("#Pidentidad").focus();
    } else {
        $(".desactivarC").fadeIn(500);
        $("#resultado_estudiantes").show();
        $.ajax({
            url: "Bunny/OpcionesEdicion/ConsultaEstudianteInscritos",
            type: "POST",
            data: {
                'Pidentidad': Pidentidad,
            },
            dataType: "JSON",
            success: function(json) {
                if (json.success === true) {

                    modal = '';

                    $("#tbl_estudiantes tbody").html('<tr><td colspan="8" class="center">Cargado...</td></tr>');

                    lista = '';
                    var j = 1;
                    if (json.lista.length !== 0) {
                        $.each(json.lista, function(key, data) {

                            modal = '<button data-toggle="tooltip" title="Click Cambiar Programa "  class="btn btn-primary btn-sm fa fa-edit" data-toggle="modal"     onclick="ModalCambiarPeriodoInicio(' + data.id_inscripcion + ',' + "'" + data.identificacion + "'" + ',' + "'" + data.nombre_estudiante + "'" + ',' + "'" + data.apellido_estudiante + "'" + ',' + "'" + data.periodo + "'" + ',' + "'" + data.nombre_programa + "'" + ',' + "'" + data.nombre_sede + "'" + ',' + "'" + data.nombre_horario + "'" + ',' + "'" + data.dia_horario + "'" + ',' + data.id_periodo_inscripcionfk + ',' + data.id_sede_inscripcionfk + ',' + data.id_programa_inscripcionfk + ',' + data.id_horario_inscripcionfk + ')"></button>';

                            lista += '<tr>';
                            lista += '<td>' + j + '</td>';
                            lista += '<td>' + data.identificacion + '</td>';
                            lista += '<td>' + data.apellido_estudiante + ' ' + data.nombre_estudiante + '</td>';
                            lista += '<td>' + data.email_estudiante + '</td>';
                            lista += '<td>' + data.nombre_programa + '</td>';
                            lista += '<td>' + data.periodo + '</td>';
                            lista += '<td>' + data.nombre_sede + '/' + data.nombre_horario + '/' + data.dia_horario + '</td>';
                            lista += '<td>' + data.fecha_inscripcion + '</td>';
                            lista += '<td>' + data.estado_registro + '</td>';
                            lista += '<td>' + modal + '</td>';

                            lista += '</tr>';
                            j++;

                        });

                    } else {
                        lista = '<tr><td colspan="10" class="center">Sin resultados</td></tr>';
                        $("#tbl_estudiantes tbody").html('');

                    }
                    $("#tbl_estudiantes tbody").html(lista);

                } else {
                    $("#tbl_estudiantes tbody").html('<tr><td colspan="10" style="text-align:center">¡No se encontraron resultados!</td></tr>');
                }
                $(".desactivarC").fadeOut(500);
            },
        });
    }
}

//funcion para la eliminación de la carga academica
function GuardarCambioProgramaMatriculado() {
    var txtPidinscripcion = $("#txtPidinscripcion").val();
    var txtcmbsede = $("#txtcmbsede").val();
    var txtcmbprograma = $("#txtcmbprograma").val();
    var txtcmbhorario = $("#txtcmbhorario").val();
    var txtcmbcurso = $("#txtcmbcurso").val();

    if ($.trim(txtcmbsede) == '') {
        toastr.error("Seleccion sede");
        $("#txtcmbsede").focus();

    } else
    if ($.trim(txtcmbprograma) == '') {
        toastr.error("Seleccione programa");
        $("#txtcmbprograma").focus();

    } else
    if ($.trim(txtcmbhorario) == '') {
        toastr.error("Seleccione horario");
        $("#txtcmbhorario").focus();

    } else
    if ($.trim(txtcmbcurso) == '') {
        toastr.error("Seleccione curso");
        $("#txtcmbcurso").focus();

    } else {

        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Bunny/OpcionesEdicion/GuardarCambioPrograma",
            type: "POST",
            data: {
                'txtPidinscripcion': txtPidinscripcion,
                'txtcmbsede': txtcmbsede,
                'txtcmbprograma': txtcmbprograma,
                'txtcmbhorario': txtcmbhorario,
                'txtcmbcurso': txtcmbcurso,
            },
            dataType: "JSON",
            success: function(json) {
                if (json.success == true) {

                    $("#ModalCambiarProgramaMatricula").modal("hide");
                    $("#Actualizar-informacion-matricula")[0].reset();
                    toastr.success(json.mensaje);
                    ConsultarEstudianteMatriculado();
                    $(".desactivarC").fadeOut();
                } else {
                    toastr.error(json.mensaje);
                    $("#ModalCambiarProgramaMatricula").modal("hide");
                }
            }

        });
    }
}

//funcion modal cambiar estudiantes de programa academico
function ModalCambiarProgramaMatricula(id, identidad, nombre, apellido, periodo, programa, sede, horario, dia, idperiodo) {
    $("#Actualizar-informacion-matricula")[0].reset();
    $("#txtPidinscripcion").val(id);
    $("#txtPidentidad").html(identidad);
    $("#txtPnombre").html(nombre + ' ' + apellido);
    $("#txtPperiodo").html(periodo);
    $("#txtPprograma").html(programa);
    $("#txtPsede").html(sede + '/' + horario + '/' + dia);
    $("#txtPidperiodo").val(idperiodo);
    BuscarCursoMatriculado(id);
    $("#ModalCambiarProgramaMatricula").modal("show");
}

//funcion modal para estudiantes matriculaso
function ModalCambiarProgramaInscrito(id, identidad, nombre, apellido, periodo, programa, sede, horario, dia, idperiodo) {
    $("#Actualizar-informacion-inscrito")[0].reset();
    $("#idinscripcion").val(id);
    $("#txtNumeroidentidad").html(identidad);
    $("#txtnombre").html(nombre + ' ' + apellido);
    $("#txtperiodo").html(periodo);
    $("#txtprograma").html(programa);
    $("#txtsede").html(sede + '/' + horario + '/' + dia);
    $("#idperiodo").val(idperiodo);
    $("#ModalCambiarProgramaInscrito").modal("show");
}

//funcion modal parael cambio de periodo de inicio en la inscripcion del estudiante
function ModalCambiarPeriodoInicio(id, identidad, nombre, apellido, periodo, programa, sede, horario, dia, idperiodo, idsede, idprograma, idhorario) {
    $("#Actualizar-informacion-periodo")[0].reset();
    $("#Peidinscripcion").val(id);
    $("#txtpeidentidad").html(identidad);
    $("#txtpenombre").html(nombre + ' ' + apellido);
    $("#txtpeperiodo").html(periodo);
    $("#txtpeprograma").html(programa);
    $("#txtpesede").html(sede + '/' + horario + '/' + dia);

    combobox('txtcmbpeperiodo', 'Bunny/OpcionesEdicion/CargarCambioPeriodo?sede=' + idsede + '&periodo=' + idperiodo + '&programa=' + idprograma + '&horario=' + idhorario, 'Seleccione...');

    $("#ModalCambiarPeriodoInicio").modal("show");
}

//funcion para buscar curso matriculado
function BuscarCursoMatriculado() {
    var txtPidinscripcion = $("#txtPidinscripcion").val();

    $.ajax({
        url: "Bunny/OpcionesEdicion/BuscarCursoMatriculado",
        type: "POST",
        data: {
            'txtPidinscripcion': txtPidinscripcion,
        },
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                $("#txtPcurso").html(json.codigo_curso);
                alert(data.codigo_curso);
            } else {
                $("#txtPcurso").html(json.codigo_curso);
            }
        }

    });
}

//funcion para actualizar el programa de inscripcion 

//funcion para la eliminación de la carga academica
function GuardarCambioProgramaInscrito() {
    var idinscripcion = $("#idinscripcion").val();
    var txtcmbsedeinscrito = $("#txtcmbsedeinscrito").val();
    var txtcmbprogramainscrito = $("#txtcmbprogramainscrito").val();
    var txtcmbhorarioinscrito = $("#txtcmbhorarioinscrito").val();

    if ($.trim(txtcmbsedeinscrito) == '') {
        toastr.error("Seleccion sede");
        $("#txtcmbsedeinscrito").focus();

    } else if ($.trim(txtcmbprogramainscrito) == '') {
        toastr.error("Seleccione programa");
        $("#txtcmbprogramainscrito").focus();

    } else if ($.trim(txtcmbhorarioinscrito) == '') {
        toastr.error("Seleccione horario");
        $("#txtcmbhorarioinscrito").focus();

    } else {

        $(".desactivarC").fadeIn(500);
        $.ajax({
            url: "Bunny/OpcionesEdicion/GuardarCambioProgramaInscrito",
            type: "POST",
            data: {
                'idinscripcion': idinscripcion,
                'txtcmbsedeinscrito': txtcmbsedeinscrito,
                'txtcmbprogramainscrito': txtcmbprogramainscrito,
                'txtcmbhorarioinscrito': txtcmbhorarioinscrito,
            },
            dataType: "JSON",
            success: function(json) {
                if (json.success == true) {

                    $("#Actualizar-informacion-inscrito")[0].reset();
                    toastr.success(json.mensaje);
                    ConsultarEstudianteMatriculado();
                    $("#ModalCambiarProgramaInscrito").modal("hide");

                    $(".desactivarC").fadeOut();
                } else {
                    toastr.error(json.mensaje);
                    $("#ModalCambiarProgramaInscrito").modal("hide");
                }
            }

        });
    }
}

//funcion para la actualizacion del periodo de inicio del estudiante inscito
function UpdatePeriodoInicio() {
    var Peidinscripcion = $("#Peidinscripcion").val();
    var txtcmbpeperiodo = $("#txtcmbpeperiodo").val();

    $.ajax({
        url: "Bunny/OpcionesEdicion/UpdatePeriodoInicio",
        type: "POST",
        data: {
            'Peidinscripcion': Peidinscripcion,
            'txtcmbpeperiodo': txtcmbpeperiodo,
        },
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                $("#Actualizar-informacion-periodo")[0].reset();
                toastr.success(json.mensaje);
                ConsultaEstudianteInscritos();
                $("#ModalCambiarPeriodoInicio").modal("hide");

            } else {
                $("#Actualizar-informacion-periodo")[0].reset();
                toastr.error(json.mensaje);
                ConsultaEstudianteInscritos();
                $("#ModalCambiarPeriodoInicio").modal("hide");
            }
        }

    });
}