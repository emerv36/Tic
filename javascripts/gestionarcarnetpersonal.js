// === gestionarcarnetpersonal.js ===
// Se ejecuta al ser inyectado vía jQuery .load() dentro de #contenido
console.log('[CarnetPersonal] Script cargado correctamente.');

// Destruir DataTable previo si existe (por recarga de la vista)
if ($.fn.DataTable.isDataTable('#tablaCarnetsPersonal')) {
    $('#tablaCarnetsPersonal').DataTable().destroy();
}

var tablaCarnets = $('#tablaCarnetsPersonal').DataTable({
    "ajax": {
        "url": "developer/Controller/personalCarnetController.php?case=listar",
        "type": "POST",
        "data": function(d) {
            d.csrf = window.csrfToken || (typeof csrfToken !== 'undefined' ? csrfToken : '');
        },
        "dataSrc": function(json) {
            if (!json || json.status === 'ERROR') {
                console.error('[CarnetPersonal] Error al cargar listado:', json ? json.message : 'Respuesta vacía');
                return [];
            }
            return json.data || [];
        }
    },
    "columns": [
        {
            "data": null,
            "orderable": false,
            "className": "text-center",
            "render": function(data, type, row) {
                var url = 'developer/Controller/personalFotoController.php?uuid=' + encodeURIComponent(data.persona_uuid);
                return '<img src="' + url + '" class="img-circle" style="width: 38px; height: 38px; object-fit: cover; border: 1px solid #ddd;" alt="Foto" onerror="this.src=\'assets/images/fotoperfil/user.png\';">';
            }
        },
        { 
            "data": null,
            "render": function(data, type, row) {
                return data.tipo_documento + ' ' + data.numero_documento;
            }
        },
        { 
            "data": null,
            "render": function(data, type, row) {
                return data.nombres + ' ' + data.apellidos;
            }
        },
        { 
            "data": "vinculos_json",
            "render": function(data) {
                if (!data) return '';
                try {
                    var vinculos = JSON.parse(data);
                    return vinculos.map(function(v) { return '<span class="label label-default">' + v.tipo + '</span>'; }).join(' ');
                } catch (e) {
                    return '';
                }
            }
        },
        { 
            "data": "estado_persona",
            "render": function(data) {
                var cls = (data === 'ACTIVA') ? 'success' : 'danger';
                return '<span class="label label-' + cls + '">' + data + '</span>';
            }
        },
        { 
            "data": null,
            "render": function(data, type, row) {
                if (!data.carnet_estado) return '<span class="label label-warning">SIN EMITIR</span>';
                var cls = 'info';
                if (data.carnet_estado === 'ENTREGADO') cls = 'success';
                if (data.carnet_estado === 'BLOQUEADO') cls = 'danger';
                return '<span class="label label-' + cls + '">' + data.carnet_estado + '</span> <small class="text-muted">' + (data.carnet_motivo || '') + '</small>';
            }
        },
        { 
            "data": "uid_rfid",
            "render": function(data) {
                return data ? '<code>' + data + '</code>' : '-';
            }
        },
        {
            "data": null,
            "render": function(data, type, row) {
                var html = '';
                var uuid = data.persona_uuid;
                var version = data.version_estado;
                
                if (!data.uid_rfid || data.carnet_estado === 'BLOQUEADO') {
                    html += '<button class="btn btn-sm btn-primary btn-asignar" data-uuid="' + uuid + '" data-version="' + version + '" title="Asignar Chip"><i class="fa fa-id-card"></i> Asignar</button> ';
                } else {
                    html += '<button class="btn btn-sm btn-warning btn-reemplazar" data-uuid="' + uuid + '" data-version="' + version + '" title="Reemplazar Chip"><i class="fa fa-exchange"></i> Reemplazar</button> ';
                    if (data.carnet_estado !== 'ENTREGADO') {
                        html += '<button class="btn btn-sm btn-success btn-entregar" data-uuid="' + uuid + '" data-version="' + version + '" title="Marcar Entregado"><i class="fa fa-check"></i> Entregar</button> ';
                    }
                    html += '<button class="btn btn-sm btn-danger btn-bloquear" data-uuid="' + uuid + '" data-version="' + version + '" title="Bloquear"><i class="fa fa-ban"></i> Bloquear</button> ';
                }
                
                // Imprimir (físico/PDF) - Siempre activo si hay RFID
                if (data.uid_rfid) {
                    if (typeof templatesReady !== 'undefined' && templatesReady) {
                        html += '<a href="exportar_carnet_personal.php?uuid=' + uuid + '" target="_blank" class="btn btn-sm btn-info" title="Imprimir PDF"><i class="fa fa-print"></i> PDF</a> ';
                    } else {
                        html += '<button disabled class="btn btn-sm btn-info" title="Plantillas no disponibles"><i class="fa fa-print"></i> PDF</button> ';
                    }
                    html += '<button class="btn btn-sm btn-warning btn-prorrogar" data-uuid="' + uuid + '" data-version="' + version + '" data-nombre="' + (data.nombres + ' ' + data.apellidos) + '" title="Prorrogar / Extender Convenio"><i class="fa fa-calendar"></i> Prorrogar</button> ';
                }
                
                return html;
            }
        }
    ],
    "language": {
        "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Spanish.json"
    }
});

console.log('[CarnetPersonal] DataTable inicializada.');

// Limpiar eventos previos para evitar duplicados en recargas
$(document).off('click.carnetAsignar click.carnetReemplazar click.carnetEntregar click.carnetBloquear click.carnetProrrogar');
$(document).off('click.carnetConfirmarBloqueo click.carnetConfirmarProrroga');
$(document).off('keypress.carnetChipRfid');

// Acción: Asignar Chip
$(document).on('click.carnetAsignar', '.btn-asignar', function() {
    console.log('[CarnetPersonal] Click en Asignar');
    $('#tituloModalChip').text('Asignar Chip RFID');
    $('#chipReemplazoMotivoDiv').hide();
    $('#hdnChipAccion').val('ASIGNACION');
    $('#hdnPersonaUuid').val($(this).data('uuid'));
    $('#hdnPersonaVersion').val($(this).data('version'));
    $('#txtAsignarChipRfid').val('');
    $('#txtVigenciaHasta').val('');
    $('#ModalAsignarChip').modal('show');
});

// Acción: Reemplazar Chip
$(document).on('click.carnetReemplazar', '.btn-reemplazar', function() {
    console.log('[CarnetPersonal] Click en Reemplazar');
    $('#tituloModalChip').text('Reemplazar Chip RFID');
    $('#chipReemplazoMotivoDiv').show();
    $('#hdnChipAccion').val('REEMPLAZO');
    $('#hdnPersonaUuid').val($(this).data('uuid'));
    $('#hdnPersonaVersion').val($(this).data('version'));
    $('#txtAsignarChipRfid').val('');
    $('#txtVigenciaHasta').val('');
    $('#ModalAsignarChip').modal('show');
});

// Focus automático en el modal
$('#ModalAsignarChip').on('shown.bs.modal', function () {
    $('#txtAsignarChipRfid').focus();
});

// Escaneo de chip (Enter)
$(document).on('keypress.carnetChipRfid', '#txtAsignarChipRfid', function(e) {
    if (e.which === 13) {
        e.preventDefault();
        var rfid = $(this).val().trim();
        if (rfid === "") {
            $(this).focus();
            return false;
        }
        
        var payload = {
            csrf: csrfToken,
            tipo: $('#hdnChipAccion').val(),
            persona_uuid: $('#hdnPersonaUuid').val(),
            uid_rfid: rfid,
            expected_persona_version: $('#hdnPersonaVersion').val(),
            vigencia_hasta: $('#txtVigenciaHasta').val()
        };
        
        if (payload.tipo === 'REEMPLAZO') {
            payload.motivo = $('#selReemplazoMotivo').val();
        }

        $.ajax({
            url: "developer/Controller/personalCarnetController.php?case=comando",
            type: "POST",
            data: JSON.stringify(payload),
            contentType: "application/json",
            success: function(response) {
                var res = (typeof response === 'string') ? JSON.parse(response) : response;
                if (res.status === 'OK') {
                    $('#ModalAsignarChip').modal('hide');
                    tablaCarnets.ajax.reload(null, false);
                    setTimeout(function() { tablaCarnets.ajax.reload(null, false); }, 800);
                    swal("¡Éxito!", "Operación realizada correctamente.", "success");
                } else {
                    swal("Atención", res.message, "warning");
                    $('#txtAsignarChipRfid').val('').focus();
                }
            },
            error: function(xhr) {
                var msg = "Error al procesar.";
                if (xhr.responseText) {
                    try {
                        var r = JSON.parse(xhr.responseText);
                        msg = r.message || msg;
                    } catch(e) {}
                }
                swal("Error", msg, "error");
                $('#txtAsignarChipRfid').val('').focus();
            }
        });
    }
});

// Acción: Prorrogar Convenio (Requerimiento E)
$(document).on('click.carnetProrrogar', '.btn-prorrogar', function() {
    console.log('[CarnetPersonal] Click en Prorrogar');
    $('#hdnProrrogarPersonaUuid').val($(this).data('uuid'));
    $('#hdnProrrogarPersonaVersion').val($(this).data('version'));
    $('#lblProrrogarPersonaNombre').text($(this).data('nombre'));
    $('#txtProrrogarVigenciaHasta').val('');
    $('#ModalProrrogarConvenio').modal('show');
});

$(document).on('click.carnetConfirmarProrroga', '#btnConfirmarProrroga', function() {
    var vigencia = $('#txtProrrogarVigenciaHasta').val();
    if (!vigencia) {
        swal("Atención", "Por favor seleccione la nueva fecha de finalización del convenio.", "warning");
        return;
    }

    var payload = {
        csrf: csrfToken,
        tipo: 'PRORROGA',
        persona_uuid: $('#hdnProrrogarPersonaUuid').val(),
        expected_persona_version: $('#hdnProrrogarPersonaVersion').val(),
        vigencia_hasta: vigencia
    };

    $.ajax({
        url: "developer/Controller/personalCarnetController.php?case=comando",
        type: "POST",
        data: JSON.stringify(payload),
        contentType: "application/json",
        success: function(response) {
            var res = (typeof response === 'string') ? JSON.parse(response) : response;
            if (res.status === 'OK') {
                $('#ModalProrrogarConvenio').modal('hide');
                tablaCarnets.ajax.reload(null, false);
                setTimeout(function() { tablaCarnets.ajax.reload(null, false); }, 800);
                swal("¡Éxito!", "Convenio prorrogado y enviado a SIGE correctamente.", "success");
            } else {
                swal("Atención", res.message, "warning");
            }
        },
        error: function(xhr) {
            swal("Error", "No se pudo registrar la prórroga.", "error");
        }
    });
});

// Acción: Bloquear
$(document).on('click.carnetBloquear', '.btn-bloquear', function() {
    console.log('[CarnetPersonal] Click en Bloquear');
    $('#hdnBloqueoPersonaUuid').val($(this).data('uuid'));
    $('#hdnBloqueoPersonaVersion').val($(this).data('version'));
    $('#ModalBloquearChip').modal('show');
});

$(document).on('click.carnetConfirmarBloqueo', '#btnConfirmarBloqueo', function() {
    var payload = {
        csrf: csrfToken,
        tipo: 'BLOQUEO',
        persona_uuid: $('#hdnBloqueoPersonaUuid').val(),
        motivo: $('#selBloqueoMotivo').val(),
        expected_persona_version: $('#hdnBloqueoPersonaVersion').val()
    };

    $.ajax({
        url: "developer/Controller/personalCarnetController.php?case=comando",
        type: "POST",
        data: JSON.stringify(payload),
        contentType: "application/json",
        success: function(response) {
            var res = (typeof response === 'string') ? JSON.parse(response) : response;
            if (res.status === 'OK') {
                $('#ModalBloquearChip').modal('hide');
                tablaCarnets.ajax.reload(null, false);
                setTimeout(function() { tablaCarnets.ajax.reload(null, false); }, 800);
                swal("¡Éxito!", "Carné bloqueado exitosamente.", "success");
            } else {
                swal("Atención", res.message, "warning");
            }
        },
        error: function(xhr) {
            swal("Error", "Error al bloquear.", "error");
        }
    });
});

// Acción: Entregar
$(document).on('click.carnetEntregar', '.btn-entregar', function() {
    console.log('[CarnetPersonal] Click en Entregar');
    
    var btn = $(this);
    var uuid = btn.data('uuid');
    var version = btn.data('version');
    
    swal({
        title: "¿Confirmar Entrega?",
        text: "¿Confirma que ha entregado físicamente el carné al titular?",
        icon: "warning",
        buttons: ["Cancelar", "Sí, Entregar"],
        dangerMode: false
    }).then(function (isConfirm) {
        if (isConfirm) {
            ejecutarEntrega(btn, uuid, version);
        }
    });
});

function ejecutarEntrega(btn, uuid, version) {
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    
    var payload = {
        csrf: csrfToken,
        tipo: 'ENTREGA_CONFIRMADA',
        persona_uuid: uuid,
        expected_persona_version: version
    };

    console.log('[CarnetPersonal] Enviando ENTREGA_CONFIRMADA:', payload);

    $.ajax({
        url: "developer/Controller/personalCarnetController.php?case=comando",
        type: "POST",
        data: JSON.stringify(payload),
        contentType: "application/json",
        success: function(response) {
            console.log('[CarnetPersonal] Respuesta:', response);
            var res = (typeof response === 'string') ? JSON.parse(response) : response;
            if (res.status === 'OK') {
                tablaCarnets.ajax.reload(null, false);
                setTimeout(function() { tablaCarnets.ajax.reload(null, false); }, 800);
                swal("¡Éxito!", "Carné marcado como ENTREGADO exitosamente.", "success");
            } else {
                swal("Error", res.message || "Error desconocido.", "error");
                btn.prop('disabled', false).html('<i class="fa fa-check"></i> Entregar');
            }
        },
        error: function(xhr) {
            console.log('[CarnetPersonal] Error:', xhr.status, xhr.responseText);
            var msg = "Error al confirmar entrega.";
            if (xhr.responseText) {
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
            }
            swal("Error", msg, "error");
            btn.prop('disabled', false).html('<i class="fa fa-check"></i> Entregar');
        }
    });
}

console.log('[CarnetPersonal] Todos los eventos registrados.');
