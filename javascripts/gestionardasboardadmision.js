$(document).ready(function() {
    ContarInscritosHoy();
    CarnetizacionAgrupada();
    PoblacionSede();


    TotalProceso();
    TotalRealizado();
    TotalEntregado();
    TotalRegistros();
    TotalConChip();
    TotalSinChip();
    TotalCarnetFuncionario();


    //funcion consulta agrupada de matriculas 
    $("#filtroestado").change(function() {
        var filtroestado = $("#filtroestado").val();
        if (filtroestado != "") {
            $(".desactivarC").fadeIn(500);
            $.ajax({
                url: "Rocky/Dashadmision/CarnetAgrupadosEstado",
                type: "POST",
                data: {
                    'filtroestado': filtroestado,
                },
                dataType: "JSON",
                success: function(json) {
                    if (json.success == true) {

                        $("#tbl_carnet_agrupados tbody").html('<tr><td colspan="6" class="center">Cargado...</td></tr>');
                        agrupado = '';
                        var i = 1,
                            tot = 0;

                        if (json.length != 0) {

                            $.each(json.agrupado, function(key, data) {
                                agrupado += '<tr>';
                                agrupado += '<td>' + i + '</td>';
                                agrupado += '<td>' + data.nombre_sede + '</td>';
                                agrupado += '<td>' + data.nombre_programa + '</td>';
                                agrupado += '<td>' + data.estado_inscripcion + '</td>';
                                agrupado += '<td>' + data.chip_carnet + '</td>';
                                agrupado += '<td>' + data.TotalCarnet + '</td>';
                                agrupado += '</tr>';
                                //  pensum.push(data.id_modulo);
                                i++;
                                tot += parseInt(data.TotalCarnet);
                            });
                            hacerGraficaPeriodo(json.agrupado, "1");

                        } else {
                            agrupado = '<tr><td colspan="6" class="center">Sin resultados</td></tr>';
                        }

                        $("#tbl_carnet_agrupados tbody").html(agrupado);
                        $("#tot").html(tot);

                        $(".desactivarC").fadeOut(500);

                    } else {

                        $("#tbl_carnet_agrupados tbody").html('<tr><td colspan="6" style="text-align:center">¡No se encontraron carnet registrados!</td></tr>');

                    }
                },
            });
        } else {
            CarnetizacionAgrupada();

        }


    });

});

function hacerGraficaPeriodo(ArrayPadre, cant) {
    $("#GraficoMatricula").html("");
    var new_canvas = document.createElement('canvas');
    document.getElementById('GraficoMatricula').appendChild(new_canvas);
    var canvas = new_canvas.getContext("2d");
    var array_labels = [];
    var array_data = [];
    var array_color = [];
    var estado = "";
    for (var i = 0; i < ArrayPadre.length; i++) {
        var element = ArrayPadre[i];
        //console.log(element);
        array_labels.push(element.nombre_programa);
        array_data.push(parseInt(element.TotalCarnet, 10));
        array_color.push(pintar());
        if (cant == "-1") {
            estado = "todos los estados"
        }
        if (cant == "1") {
            estado = "total " + element.estado_inscripcion;
        }
    }
    var data = {
        labels: array_labels,
        datasets: [{
            label: '',
            data: array_data,
            backgroundColor: array_color,
        }]
    };
    var config = {
        type: 'pie',
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        font: {
                            size: 11
                        }
                    },
                    position: 'top', //left, right, bottom, top
                },
                title: {
                    display: true,
                    text: "Grafico de programas " + estado
                }
            }
        },
    };
    var grafica = new Chart(canvas, config);
}

function hacerGrafica(Array) {

    $("#aqui-entrada").html("");
    var new_canvas = document.createElement('canvas');
    document.getElementById('aqui-entrada').appendChild(new_canvas);
    var canvas = new_canvas.getContext("2d");
    var array_labels = [];
    var array_data = [];
    var array_color = [];
    for (var i = 0; i < Array.length; i++) {
        var element = Array[i];
        array_labels.push(element.nombre_sede);
        array_data.push(element.totalprograma)
        array_color.push(pintar())
    }
    var data = {
        labels: array_labels,
        datasets: [{
            label: '',
            data: array_data,
            backgroundColor: array_color,
        }]
    };
    var config = {
        type: 'pie',
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        font: {
                            size: 11
                        }
                    },
                    position: 'top', //left, right, bottom, top
                },
                title: {
                    display: true,
                    text: "Grafico total por sede"
                }
            }
        },
    };
    var grafica = new Chart(canvas, config);
}

function pintar() {
    var COLORS = [
        "#4B0082", "#DC143C", "#8B0000", "#C71585", "#FFA07A", "#FF4500", "#FFA500",
        "#F0E68C", "#FF00FF", "#BA55D3", "#800080", "#00FF00", "#00FA9A", "#006400",
        "#556B2F", "#008B8B", "#00FFFF", "#4682B4", "#0000FF", "#000000", "#2F4F4F", "RED"
        /* "aliceblue", "antiquewhite", "aqua", "aquamarine", "azure",
        "beige", "bisque", "black", "blanchedalmond", "blue", "blueviolet",
        "brown", "burlywood", "cadetblue", "chartreuse", "chocolate", "coral", "cornflowerblue",
        "cornsilk", "crimson", "cyan", "darkblue", "darkcyan", "darkgoldenrod",
        "darkgray", "darkgreen", "darkgrey", "darkkhaki", "darkmagenta", "darkolivegreen",
        "darkorange", "darkorchid", "darkred", "darksalmon", "darkseagreen",
        "darkslateblue", "darkslategray", "darkslategrey", "darkturquoise", "darkviolet",
        "deeppink", "deepskyblue", "dimgray", "dimgrey", "dodgerblue", "firebrick",
        "floralwhite", "forestgreen", "fuchsia", "gainsboro", "ghostwhite",
        "gold", "goldenrod", "gray", "green", "greenyellow", "grey",
        "honeydew", "hotpink", "indianred", "indigo", "ivory", "khaki",
        "lavender", "lavenderblush", "lawngreen", "lemonchiffon", "lightblue",
        "lightcoral", "lightcyan", "lightgoldenrodyellow", "lightgray",
        "lightgreen", "lightgrey", "lightpink", "lightsalmon", "lightseagreen", "lightskyblue",
        "lightslategray", "lightslategrey", "lightsteelblue", "lightyellow", "lime", "limegreen",
        "linen", "magenta", "maroon", "mediumaquamarine", "mediumblue", "mediumorchid", "mediumpurple", "mediumseagreen",
        "mediumslateblue", "mediumspringgreen", "mediumturquoise", "mediumvioletred", "midnightblue",
        "mintcream", "mistyrose", "moccasin", "navajowhite", "navy", "oldlace", "olive", "olivedrab",
        "orange", "orangered", "orchid", "palegoldenrod", "palegreen", "paleturquoise", "palevioletred",
        "papayawhip", "peachpuff", "peru", "pink", "plum", "powderblue", "purple",
        "red", "rosybrown", "royalblue", "saddlebrown", "salmon", "sandybrown", "seagreen",
        "seashell", "sienna", "silver", "skyblue", "slateblue", "slategray", "slategrey", "snow",
        "springgreen", "steelblue", "tan", "teal", "thistle", "tomato", "turquoise", "violet",
        "wheat", "white", "whitesmoke", "yellow", "yellowgreen" */
    ];
    var rand = Math.floor(Math.random() * COLORS.length);
    var rValue = COLORS[rand];
    return rValue;
}

// función para contar las inscripciones hoy
function ContarInscritosHoy() {
    var fecha = $("#fecha").val();

    $.ajax({
        url: "Rocky/Dashadmision/ContarInscritosHoy",
        type: "POST",
        data: {
            'fecha': fecha,
        },
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                var l = json.total;

                $("#Totalinscritos").html(json.total);

            }
        }
    });

}



function Totalinscrito() {
    var fecha = $("#fecha").val();

    $.ajax({
        url: "Rocky/Dashadmision/Totalinscrito",
        type: "POST",
        data: {
            'fecha': fecha,
        },
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {

                $("#TotalI").html(json.total);
            } else {

                $("#TotalI").html(json.total);
            }

        }
    });

}

function TotalProceso() {
    $.ajax({
        url: "Rocky/Dashadmision/TotalProceso",
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                $("#TotalProceso").html(json.TotalProceso);
            } else {
                $("#TotalProceso").html(json.TotalProceso);
            }

        }
    });
}

function TotalRealizado() {
    $.ajax({
        url: "Rocky/Dashadmision/TotalRealizado",
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                $("#TotalRealizado").html(json.totalRealizado);
                var totalR = parseInt($("#TotalRealizado").html());
                var totalP = parseInt($("#TotalProceso").html());
                var totalPorRealizar = totalP - totalR;
                $("#TotalPorRealizar").html(totalPorRealizar);
            } else {
                $("#TotalRealizado").html(json.total);
            }

        }
    });
}

function TotalEntregado() {
    $.ajax({
        url: "Rocky/Dashadmision/TotalEntregado",
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                $("#TotalEntregado").html(json.TotalEntregado);
            } else {
                $("#TotalEntregado").html(json.TotalEntregado);
            }

        }
    });
}

function TotalRegistros() {
    $.ajax({
        url: "Rocky/Dashadmision/TotalRegistros",
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                $("#TotalRegistros").html(json.total);
            } else {
                $("#TotalRegistros").html(json.total);
            }

        }
    });
}

function TotalSinChip() {
    $.ajax({
        url: "Rocky/Dashadmision/TotalSinChip",
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                $("#TotalSinChip").html(json.TotalSinChip);
            } else {
                $("#TotalSinChip").html(json.TotalSinChip);
            }

        }
    });
}

function TotalConChip() {
    $.ajax({
        url: "Rocky/Dashadmision/TotalConChip",
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                $("#TotalConChip").html(json.TotalConChip);
            } else {
                $("#TotalConChip").html(json.TotalConChip);
            }

        }
    });
}


function TotalCarnetFuncionario() {
    $.ajax({
        url: "Rocky/Dashadmision/TotalCarnetFuncionario",
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {
                $("#TotalCarnetFuncionario").html(json.TotalCarnetFuncionario);
            } else {
                $("#TotalCarnetFuncionario").html(json.TotalCarnetFuncionario);
            }

        }
    });
}


//funcion consulta agrupada de matriculas 
function CarnetizacionAgrupada() {
    $("#tbl_carnet_agrupados tbody").html('');
    $(".desactivarC").fadeIn(500);
    $.ajax({
        url: "Rocky/Dashadmision/CarnetizacionAgrupada",
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {

                $("#tbl_carnet_agrupados tbody").html('<tr><td colspan="4" class="center">Cargado...</td></tr>');
                agrupado = '';
                estado = '';
                var i = 1,
                    tot = 0;



                if (json.length != 0) {


                    $.each(json.agrupado, function(key, data) {
                        // estado = data.estado_inscripcion;
                        /*   if (data.valor_registro == "1") {
                               estado = "<span class='label label-primary'>" + data.estado_inscripcion + "</span>";
                           }
                           if (data.valor_registro == "2") {
                               $estado = "<span class='label label-warning'>" + data.estado_inscripcion + "</span>";
                           }
                           if (data.valor_registro == "3") {
                               $estado = "<span class='label label-success'>" + data.estado_inscripcion + "</span>";
                           }*/
                        agrupado += '<tr>';
                        agrupado += '<td>' + i + '</td>';
                        agrupado += '<td>' + data.nombre_sede + '</td>';
                        agrupado += '<td>' + data.nombre_programa + '</td>';
                        agrupado += '<td>' + data.estado_inscripcion + '</td>';
                        agrupado += '<td>' + data.chip_carnet + '</td>';
                        agrupado += '<td>' + data.TotalCarnet + '</td>';
                        agrupado += '</tr>';
                        i++;
                        tot += parseInt(data.TotalCarnet);
                    });
                    setTimeout(() => {
                        hacerGraficaPeriodo(json.agrupado, "-1");
                    }, 500);

                } else {
                    agrupado = '<tr><td colspan="6" class="center">Sin resultados</td></tr>';
                }

                $("#tbl_carnet_agrupados tbody").html(agrupado);
                $("#tot").html(tot);
                $(".desactivarC").fadeOut(500);

            } else {

                $("#tbl_carnet_agrupados tbody").html('<tr><td colspan="6" style="text-align:center">¡No se encontraron carnet registrados!</td></tr>');

            }
        },
    });
}
//funcion para la poblacion de las sedes

function PoblacionSede() {
    $("#tbl_poblacion_sede tbody").html('');

    $.ajax({
        url: "Rocky/Dashadmision/PoblacionSede",
        dataType: "JSON",
        success: function(json) {
            if (json.success == true) {

                $("#tbl_poblacion_sede tbody").html('<tr><td colspan="3" class="center">Cargado...</td></tr>');
                agrupado = '';
                var i = 1,
                    tot = 0;

                if (json.length != 0) {
                    //   hacerGrafica(json.agrupado);
                    $.each(json.agrupado, function(key, data) {
                        agrupado += '<tr>';
                        agrupado += '<td>' + i + '</td>';
                        agrupado += '<td>' + data.nombre_sede + '</td>';
                        agrupado += '<td>' + data.totalprograma + '</td>';
                        agrupado += '</tr>';
                        //  pensum.push(data.id_modulo);
                        i++;
                        tot += parseInt(data.totalprograma);
                    });

                } else {
                    agrupado = '<tr><td colspan="3" class="center">Sin resultados</td></tr>';
                }

                $("#tbl_poblacion_sede tbody").html(agrupado);
                $("#totsede").html(tot);

            } else {

                $("#tbl_poblacion_sede tbody").html('<tr><td colspan="3" style="text-align:center">¡No se encontraron estudiantes matriculados!</td></tr>');

            }
        },
    });
}

function getRandomColor() {
    var letters = "0123456789ABCDEF".split("");
    var color = "#";
    for (var i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

function grafico() {

    var ctx = document.getElementById('GraficoMatricula').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
            datasets: [{
                label: '# of Votes',
                data: [12, 19, 3, 5, 2, 3],

                backgroundColor: [
                    getRandomColor(), getRandomColor(), getRandomColor(), getRandomColor()
                ],

            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}