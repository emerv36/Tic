// gestionarpersonal.js
// Lógica frontend del Asistente TIC-SIGE para Registro de Personal (Single Page Layout)

$(document).ready(function() {
    var csrfToken = $('#personal-ui-app').data('csrf');
    var appState = {
        identidad: {
            persona_uuid: null,
            tipo_documento: '',
            numero_documento: '',
            nombres: '',
            apellidos: '',
            correo: '',
            celular: '',
            rh: '',
            genero: '',
            es_nuevo: true
        },
        vinculo: {
            tipo: '',
            cargo: null,
            dependencia: null
        },
        foto: {
            archivo: null,
            metadata: null
        },
        vinculos: []
    };

    var camaraStream = null;

    // ----- BUSQUEDA -----
    $('#pers-btn-buscar').click(function() {
        var tipo = $('#pers-tipo-doc').val();
        var num = $('#pers-num-doc').val();
        if (!tipo || !num) {
            alert('Ingrese tipo y número de documento');
            return;
        }
        
        $(this).prop('disabled', true).text('Buscando...');
        
        $.ajax({
            url: 'developer/Controller/personalController.php?case=buscarSige',
            method: 'POST',
            data: { tipo_documento: tipo, numero_documento: num, csrf: csrfToken },
            success: function(response) {
                var data = JSON.parse(response);
                $('#pers-btn-buscar').prop('disabled', false).text('Buscar');
                
                if (data.status === 'OK' || data.existe !== undefined) {
                    appState.identidad.es_nuevo = !data.existe;
                    
                    if (data.existe) {
                        $('#pers-badge-estado').text('SIGE').removeClass('label-default').addClass('label-success');
                        $('#pers-nombres').val(data.persona.nombres).prop('disabled', true);
                        $('#pers-apellidos').val(data.persona.apellidos).prop('disabled', true);
                        $('#pers-correo').val(data.persona.correo).prop('disabled', true);
                        $('#pers-celular').val(data.persona.celular).prop('disabled', true);
                        $('#pers-rh').val(data.persona.rh).prop('disabled', true);
                        $('#pers-genero').val(data.persona.genero).prop('disabled', true);
                        
                        appState.identidad.persona_uuid = data.persona.persona_uuid;
                        appState.identidad.persona_version = data.persona.persona_version;
                        appState.vinculos = data.persona.vinculos || [];
                        
                        // Set preview data
                        $('#preview-nombres').text(data.persona.nombres);
                        $('#preview-apellidos').text(data.persona.apellidos);
                        $('#preview-identificacion').text(num);
                        $('#preview-rh').text(data.persona.rh || '---');
                    } else {
                        $('#pers-badge-estado').text('NUEVO').removeClass('label-success').addClass('label-default');
                        $('.master-field').val('').prop('disabled', false);
                        appState.identidad.persona_uuid = null;
                        appState.identidad.persona_version = null;
                        appState.vinculos = [];
                        
                        $('#preview-nombres').text('---');
                        $('#preview-apellidos').text('---');
                        $('#preview-identificacion').text(num);
                        $('#preview-rh').text('---');
                    }
                    
                    $('#pers-form-identidad').slideDown();
                    
                    // Activar camara automaticamente al encontrar a la persona
                    $('#pers-btn-camara-iniciar').click();
                    
                } else {
                    alert('Error en SIGE: ' + data.message);
                }
            },
            error: function() {
                alert('Error de conexión con el controlador');
                $('#pers-btn-buscar').prop('disabled', false).text('Buscar');
            }
        });
    });

    // Validar Enter en la búsqueda
    $('#pers-num-doc').keypress(function(e) {
        if(e.which == 13) {
            $('#pers-btn-buscar').click();
        }
    });

    // Update Mockup as user types (only if they are new)
    $('#pers-nombres').on('input', function() {
        if (appState.identidad.es_nuevo) {
            $('#preview-nombres').text($(this).val());
        }
    });
    $('#pers-apellidos').on('input', function() {
        if (appState.identidad.es_nuevo) {
            $('#preview-apellidos').text($(this).val());
        }
    });
    
    $('#pers-rh').change(function() {
        $('#preview-rh').text($(this).val() || '---');
    });

    function updateCargoPreview() {
        var tipo = $('#pers-tipo-vinculo').val();
        if (tipo === 'ADMINISTRATIVO') {
            var cargo = $('#pers-cargo').val() ? $('#pers-cargo option:selected').text() : '';
            var dep = $('#pers-dependencia').val() ? $('#pers-dependencia option:selected').text() : '';
            var textParts = [];
            if (cargo && cargo !== 'Cargando...') textParts.push(cargo);
            if (dep && dep !== 'Cargando...') textParts.push(dep);
            $('#preview-cargo').text(textParts.join(' - ') || 'ADMINISTRATIVO');
        } else {
            $('#preview-cargo').text(tipo || '---');
        }
    }

    $('#pers-tipo-vinculo').change(function() {
        var tipo = $(this).val();
        $('#preview-letra').text(tipo ? tipo.charAt(0).toUpperCase() : '');
        updateCargoPreview();
        if (tipo === 'ADMINISTRATIVO') {
            $('#pers-form-catalogos').slideDown();
            cargarCatalogos();
        } else {
            $('#pers-form-catalogos').slideUp();
        }
    });
    
    $('#pers-cargo, #pers-dependencia').change(updateCargoPreview);

    function cargarCatalogos() {
        if ($('#pers-cargo option').length > 1) return; // Ya cargados
        
        $.get('developer/Controller/personalController.php?case=catalogos', function(res) {
            var data = JSON.parse(res);
            var options = '<option value="">Seleccione...</option>';
            data.cargos.forEach(function(c) {
                options += `<option value="${c.id}" data-nombre="${c.nombre}" data-version="${c.version}">${c.nombre}</option>`;
            });
            $('#pers-cargo').html(options);
            
            options = '<option value="">Seleccione...</option>';
            data.dependencias.forEach(function(d) {
                options += `<option value="${d.id}" data-nombre="${d.nombre}" data-version="${d.version}">${d.nombre}</option>`;
            });
            $('#pers-dependencia').html(options);
            $('.select2').select2();
        });
    }

    // ----- CAMARA Y FOTO -----
    var $video = $('#pers-camara-video');
    var $canvas = $('#pers-camara-canvas');
    var $previewImg = $('#pers-foto-preview');
    var $btnIniciar = $('#pers-btn-camara-iniciar');
    var $btnCapturar = $('#pers-btn-camara-capturar');

    $btnIniciar.click(function() {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { width: { ideal: 640 }, height: { ideal: 640 }, facingMode: "user" } }).then(function(stream) {
                camaraStream = stream;
                $video[0].srcObject = stream;
                $video.show();
                $previewImg.hide();
                $canvas.hide();
                $btnIniciar.hide();
                $btnCapturar.show();
            }).catch(function(error) {
                console.error(error);
                alert("No se pudo acceder a la cámara. Asegúrese de tener permisos y HTTPS/localhost.");
            });
        } else {
            alert("Tu navegador no soporta el acceso a la cámara o requieres contexto HTTPS.");
        }
    });

    $btnCapturar.click(function() {
        var context = $canvas[0].getContext('2d');
        var size = Math.min($video[0].videoWidth, $video[0].videoHeight);
        var x = ($video[0].videoWidth - size) / 2;
        var y = ($video[0].videoHeight - size) / 2;
        
        $canvas.attr('width', size);
        $canvas.attr('height', size);
        context.drawImage($video[0], x, y, size, size, 0, 0, size, size);
        
        var dataUrl = $canvas[0].toDataURL('image/png');
        
        if (camaraStream) {
            camaraStream.getTracks().forEach(track => track.stop());
        }
        
        $video.hide();
        $previewImg.attr('src', dataUrl).show();
        $btnCapturar.hide();
        $btnIniciar.html('<i class="fa fa-camera"></i> Tomar Otra').show();
        
        fetch(dataUrl)
            .then(res => res.blob())
            .then(blob => {
                var file = new File([blob], "captura.png", { type: "image/png" });
                appState.foto.archivo = file;
                validarFormulario();
            });
    });

    $('#pers-foto-file').change(function() {
        var file = this.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) {
                alert('El archivo no puede exceder 5MB');
                this.value = '';
                return;
            }
            if (camaraStream) {
                camaraStream.getTracks().forEach(track => track.stop());
                $video.hide();
                $btnCapturar.hide();
                $btnIniciar.html('<i class="fa fa-camera"></i> Activar Cámara').show();
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                $previewImg.attr('src', e.target.result).show();
                appState.foto.archivo = file;
                validarFormulario();
            }
            reader.readAsDataURL(file);
        }
    });

    // ----- ENVIO FINAL -----
    
    // Activa el botón de envío solo si todo es válido
    function validarFormulario() {
        var ok = true;
        
        if (!$('#pers-tipo-doc').val() || !$('#pers-num-doc').val()) ok = false;
        if (appState.identidad.es_nuevo) {
            if (!$('#pers-nombres').val() || !$('#pers-apellidos').val() || !$('#pers-correo').val()) ok = false;
        }
        
        var vinculo = $('#pers-tipo-vinculo').val();
        if (!vinculo) ok = false;
        if (vinculo === 'ADMINISTRATIVO') {
            if (!$('#pers-cargo').val() || !$('#pers-dependencia').val()) ok = false;
        }
        
        if (!appState.foto.archivo) ok = false;
        
        $('#pers-btn-submit').prop('disabled', !ok);
    }
    
    $('input, select').on('change input', validarFormulario);

    $('#pers-btn-submit').click(function() {
        // Collect state
        appState.identidad.tipo_documento = $('#pers-tipo-doc').val();
        appState.identidad.numero_documento = $('#pers-num-doc').val();
        appState.identidad.nombres = $('#pers-nombres').val();
        appState.identidad.apellidos = $('#pers-apellidos').val();
        appState.identidad.correo = $('#pers-correo').val();
        appState.identidad.celular = $('#pers-celular').val();
        appState.identidad.rh = $('#pers-rh').val();
        appState.identidad.genero = $('#pers-genero').val();
        
        appState.vinculo.tipo = $('#pers-tipo-vinculo').val();
        
        $('#pers-confirm-status').removeClass('alert-success alert-warning alert-danger').addClass('alert-info')
            .text('Enviando datos a SIGE...').show();
        $(this).prop('disabled', true);
        
        // Extraer base64 puro (sin data:image/png;base64,)
        var rawBase64 = '';
        var src = $previewImg.attr('src');
        if (src && src.indexOf('base64,') !== -1) {
            rawBase64 = src.split('base64,')[1];
        }
        
        var isDocente = appState.vinculo.tipo === 'DOCENTE';
        
        var payload = {
            csrf: csrfToken,
            identidad: appState.identidad,
            vinculo: appState.vinculo,
            cargo_id: isDocente ? null : parseInt($('#pers-cargo').val(), 10),
            dependencia_id: isDocente ? null : parseInt($('#pers-dependencia').val(), 10),
            foto_base64: rawBase64,
            fecha_inicio: new Date().toISOString().split('T')[0] // Hoy (YYYY-MM-DD)
        };
        
        $.ajax({
            url: 'developer/Controller/personalController.php?case=finalizar',
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(res) {
                var resData = JSON.parse(res);
                if (resData.status === 'OK') {
                    $('#pers-confirm-status').removeClass('alert-info alert-warning alert-danger').addClass('alert-success').text('Operación guardada correctamente.').show();
                    setTimeout(function() {
                        window.location.reload();
                    }, 1200);
                } else {
                    alert('Error al guardar: ' + resData.message);
                    $('#pers-btn-submit').prop('disabled', false);
                    $('#pers-confirm-status').hide();
                }
            },
            error: function(jqXHR) {
                var msg = 'Error de conexión al finalizar.';
                try { var data2 = JSON.parse(jqXHR.responseText); if (data2.message) msg = data2.message; } catch(e) {}
                alert(msg);
                $('#pers-btn-submit').prop('disabled', false);
                $('#pers-confirm-status').hide();
            }
        });
    });

});
