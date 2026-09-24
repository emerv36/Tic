function cargarMenuDelUsuario() {
    $.ajax({
        method: "GET",
        url: "Lopersa/Menu/obtenerMenu",
        success: function(response) {
            // inyectar el menu en el html...
            var menu = JSON.parse(response)
                //console.log(menu)
            var menuOrganizado = menu.map(function(e) {
                e.padre = Object.values(e.padre).pop()
                return e
            })

            var menuParaElHtml = {}

            menuOrganizado.forEach(function(e) {
                var padreId = e.padre.codigo_menu
                if (menuParaElHtml[padreId]) {
                    delete e.padre
                    menuParaElHtml[padreId].children.push(e)
                } else {
                    menuParaElHtml[padreId] = e.padre
                    menuParaElHtml[padreId].children = []
                    delete e.padre
                    menuParaElHtml[padreId].children.push(e)
                }
            })

            var menuTemplate = `<li class="header">Navegacion</li>`

            Object.values(menuParaElHtml).forEach(function(e) {
                menuTemplate += `<li class="treeview">`
                menuTemplate += `
            <a href="${e.link}">
              <i class="fa fa-dashboard"></i> <span>${e.nombre_menu}</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
          `
                menuTemplate += `<ul class="treeview-menu" style="width:auto;">`
                e.children.forEach(function(c) {
                    menuTemplate += `
                <li>
                  <a href="javascript:void(0)"
                    onclick="loadpag('${c.link}', '${c.codigo_menu}', '${c.codsuperior}')">
                    <i class="fa fa-${c.imagen}"></i> ${c.nombre_menu}
                  </a>
                </li>
              `
                })
                menuTemplate += `</ul>`
                menuTemplate += `</li>`
            })

            $(".sidebar-menu").html(menuTemplate)

        },
        error: function(error) {
            console.log(error)
        }
    })
}

function loadpag(data, id, codsup) {
    // $( "#menuu" ).load('component/menu2.php', function() {
    localStorage.setItem('pagina', data);
    localStorage.setItem('idpagina', id);
    localStorage.setItem('codsuppag', codsup);
    $("#contenido").load(data);
    reloadmenu();
    // });
}

function reloadmenu() {
    $("#menuu").load('Menu', function() {
        $(".mdc-expansion-panel").removeClass("expanded");
        $("a#m" + localStorage.idpagina).addClass("active");
        $("#submenu" + localStorage.codsuppag).addClass("expanded");
        // $('#li_'+localStorage.codsuppag).has('ul').children('ul').addClass('collapse in');
        // $('#main-menu li').not('.active').has('ul').children('ul').addClass('collapse');
        if ($("#submenu" + localStorage.codsuppag).hasClass('expanded')) {
            $("#arrow" + localStorage.codsuppag).removeClass("scaleX");
        } else {
            $("#arrow" + localStorage.codsuppag).addClass("scaleX");
        }
        $('[data-toggle="expansionPanel"]').on('click', function() {
            $('#' + $(this).attr("target-panel")).toggleClass('expanded');
            var menuEl = document.querySelector('#' + $(this).attr("target-panel"));
            if ($('#' + $(this).attr("target-panel")).hasClass('expanded')) {
                $("#arrow" + $(this).attr("data-id")).removeClass("scaleX");
            } else {
                $("#arrow" + $(this).attr("data-id")).addClass("scaleX");
            }
        });
        /* Dropdown */
        $('[data-toggle="dropdown"]').on('click', function() {
            var menuEl = document.querySelector('#' + $(this).attr("toggle-dropdown"));
            var menu = new mdc.menu.MDCSimpleMenu(menuEl);
            menu.open = !menu.open;
        });
        mdc.autoInit();
        /* Select menu */
        var MDCSelect = mdc.select.MDCSelect;
        if ($('#hero-js-select').length) {
            var heroSelect = document.getElementById('hero-js-select');
            var heroSelectComponent = new mdc.select.MDCSelect(heroSelect);
        }
        /* text field */
        if ($('#tf-box-example').length) {
            var tfEl = document.getElementById('tf-box-example');
            var tf = new mdc.textField.MDCTextField(tfEl);
        }
        if ($('#demo-tf-box-wrapper').length) {
            var wrapper = document.getElementById('demo-tf-box-wrapper');
        }
        if ($('#tf-box-leading-example').length) {
            var tfBoxLeadingEl = document.getElementById('tf-box-leading-example');
            var tfBoxLeading = new mdc.textField.MDCTextField(tfBoxLeadingEl);
        }
        if ($('#demo-tf-box-leading-wrapper').length) {
            var wrapperBoxLeading = document.getElementById('demo-tf-box-leading-wrapper');
        }
        if ($('#tf-outlined-example').length) {
            var tfEl = document.getElementById('tf-outlined-example');
            var tf = new mdc.textField.MDCTextField(tfEl);
        }
        if ($('#demo-tf-outlined-wrapper').length) {
            var wrapper = document.getElementById('demo-tf-outlined-wrapper');
        }
        $(".mdc-toolbar__menu-icon").on("click", function() {
            $(".body-wrapper .page-wrapper .content-wrapper").toggleClass("drawer-minimized");
        });
    });
}
/*//////////////////////////////////////////////////////////////////////////////*/
function combobox(id, url, inival, params) {
    var localurl = url;
    $.ajax({
        url: localurl,
        type: "POST",
        data: {
            params: params
        },
        dataType: "json",
        success: function(json) {
            var option = "<option value=''>" + inival + "</option>";
            if (json && json.length > 0) {
                $.each(json, function(k, v) {
                    option += "<option value='" + v.cod + "'>" + v.nombre + "</option>";
                });
            }
            $("#" + id).html(option).trigger('change');
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error on " + url, status, error);
            console.error(xhr.responseText);
        }
    });
}
/*//////////////////////////////////////////////////////////////////////////////*/
function doSearch(val, table) {
    /*var tableReg = document.getElementById('table');*/
    var tableReg = document.getElementById("" + table);
    var searchText = $("#" + val).val().toLowerCase();
    /*var searchText = document.getElementById('buscar').value.toLowerCase();*/
    for (var i = 1; i < tableReg.rows.length; i++) {
        var cellsOfRow = tableReg.rows[i].getElementsByTagName('td');
        var found = false;
        for (var j = 0; j < cellsOfRow.length && !found; j++) {
            var compareWith = cellsOfRow[j].innerHTML.toLowerCase();
            if (searchText.length == 0 || (compareWith.indexOf(searchText) > -1)) {
                found = true;
            }
        }
        if (found) {
            tableReg.rows[i].style.display = '';
        } else {
            tableReg.rows[i].style.display = 'none';
        }
    }
}

function change(value) {
    if (value == "1") {
        $(".bdivpadre").hide();
    } else if (value == "2") {
        $.ajax({
            url: "Lopersa/Menu/loadPadresMenu",
            type: "POST",
            data: {},
            dataType: "JSON",
            success: function(json) {
                if (json.success == true) {
                    html = '<option value="" selected>Todos</option>'; //<option value="" disabled selected>Seleccione</option>
                    $.each(json.menu, function(key, data) {
                        html += '<option value="' + data.codigo_menu + '">' + data.nombre_menu + '</td>';
                    });
                    $("#bPadre").html(html);
                }
            },
            complete: function() {
                $(".bdivpadre").show();
            }
        });
    }
}

function ModalModulosDocente(codigo) {

    $('#teachers').fadeOut(500);
    $('#assigned_modules').fadeIn(500);

    console.log($("#teachers"))

    var url = "Krusty/AsignarModuloDocentes/ListarModuloDocentes?codigo=" + codigo;
    var cb = function() { $('.tooltips').tooltip() };
    $('#tbl_modulo_docentes').DataTable().ajax.url(url).load(cb);

}

function ModalModulosDocente(codigo) {

    $('#teachers').fadeOut(500);
    $('#assigned_modules').fadeIn(500);

    console.log($("#teachers"))

    var url = "Krusty/AsignarModuloDocentes/ListarModuloDocentes?codigo=" + codigo;
    var cb = function() { $('.tooltips').tooltip() };
    $('#tbl_modulo_docentes').DataTable().ajax.url(url).load(cb);

}

function ModalRegistrarcurso() {
    $('#ModalRegistrarcurso').modal("show");

}