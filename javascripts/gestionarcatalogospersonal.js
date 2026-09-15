(function ($) {
    'use strict';

    var $app = $('#personal-catalog-app');
    if (!$app.length) return;
    var csrf = String($app.data('csrf') || '');

    function message(xhr, fallback) {
        return xhr && xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : fallback;
    }

    function loadCatalog(catalog) {
        var $body = $('table[data-catalog-table="' + catalog + '"] tbody');
        $body.empty().append($('<tr>').append($('<td>', { colspan: 4, text: 'Cargando…' })));
        $.ajax({
            url: 'Personal/Catalogos/Listar',
            method: 'GET',
            dataType: 'json',
            data: { catalogo: catalog, incluir_inactivos: '1' }
        }).done(function (response) {
            $body.empty();
            if (!response.data.length) {
                $body.append($('<tr>').append($('<td>', { colspan: 4, text: 'Sin registros.' })));
                return;
            }
            $.each(response.data, function (_, item) {
                var $button = $('<button>', {
                    type: 'button',
                    class: 'btn btn-warning btn-xs personal-catalog-edit',
                    text: 'Editar'
                }).data({ catalog: catalog, item: item });
                $body.append($('<tr>')
                    .append($('<td>').text(item.nombre))
                    .append($('<td>').text(item.version))
                    .append($('<td>').text(item.estado === 'ACTIVO' ? 'Activo' : 'Inactivo'))
                    .append($('<td>').append($button)));
            });
        }).fail(function (xhr) {
            $body.empty().append($('<tr>').append($('<td>', { colspan: 4, text: message(xhr, 'No fue posible cargar el catálogo.') })));
        });
    }

    function openModal(catalog, item) {
        $('#personal-catalog-type').val(catalog);
        $('#personal-catalog-id').val(item ? item.id : '');
        $('#personal-catalog-version').val(item ? item.version : '');
        $('#personal-catalog-name').val(item ? item.nombre : '');
        $('#personal-catalog-state').val(item ? item.estado : 'ACTIVO');
        $('#personal-catalog-state-group').toggle(!!item);
        $('#personal-catalog-modal').modal('show');
    }

    $app.off('.personalCatalog')
        .on('click.personalCatalog', '.personal-catalog-new', function () {
            openModal($(this).data('catalog'), null);
        })
        .on('click.personalCatalog', '.personal-catalog-edit', function () {
            var data = $(this).data();
            openModal(data.catalog, data.item);
        });

    $('#personal-catalog-save').off('.personalCatalog').on('click.personalCatalog', function () {
        var catalog = $('#personal-catalog-type').val();
        var id = $('#personal-catalog-id').val();
        var name = $.trim($('#personal-catalog-name').val());
        if (!name) {
            toastr.error('Ingrese un nombre.');
            $('#personal-catalog-name').focus();
            return;
        }
        var payload = { catalogo: catalog, nombre: name, csrf_token: csrf };
        var operation = id ? 'Actualizar' : 'Crear';
        if (id) {
            payload.id = id;
            payload.expected_version = $('#personal-catalog-version').val();
            payload.estado = $('#personal-catalog-state').val();
        }
        $('#personal-catalog-save').prop('disabled', true);
        $.ajax({
            url: 'Personal/Catalogos/' + operation,
            method: 'POST',
            dataType: 'json',
            data: payload
        }).done(function () {
            $('#personal-catalog-modal').modal('hide');
            toastr.success('Catálogo actualizado correctamente.');
            loadCatalog(catalog);
        }).fail(function (xhr) {
            toastr.error(message(xhr, 'No fue posible guardar.'));
            if (xhr.status === 409) loadCatalog(catalog);
        }).always(function () {
            $('#personal-catalog-save').prop('disabled', false);
        });
    });

    loadCatalog('CARGO');
    loadCatalog('DEPENDENCIA');
})(jQuery);
