var refreshTime;
$(document).ready(function () {
  $.ajaxSetup({ cache: false });
  if (localStorage.pagina) {
    loadpag(localStorage.pagina, localStorage.idpagina, localStorage.codsuppag);
    fLoadNotificaciones();
  } else {
    loadpag('intro.php', 6);
    fLoadNotificaciones();
  }
  refreshTime = 600000; // every 10 minutes in milliseconds 
  window.setInterval(function () {
    $.ajax({
      cache: false,
      type: "GET",
      url: "refreshSession.php",
      success: function (data) {}
    });
  }, refreshTime);
});
function fLoadNotificaciones() {
  $.ajax({
    url: "Lopersa/loadNotificaciones",
    type: "POST",
    data: {},
    dataType: "JSON",
    success: function (json) {
      var html = '';
      var icono = '';
      if (json.notificaciones.length != 0) {
        $(".noviewnotification").show().html(json.total);
        $.each(json.notificaciones, function (key, data) {
          if (data.tipo_ar == 'file') {
            icono = 'attach_file';
          } else {
            icono = 'folder_shared';
          }
          html += '<li class="mdc-list-item" role="menuitem" tabindex="0">';
          html += '<i class="material-icons mdc-theme--primary mr-1">' + icono + '</i>';
          html += '<b>' + data.usuario + ' </b> &nbsp; ' + data.texto + ':&nbsp; <b>' + data.nombre + '</b>';
          html += '</li>';
        });
      } else {
        $(".noviewnotification").hide().html('0');
        html += '<li class="mdc-list-item" role="menuitem" tabindex="0">No hay notificaciones nuevas</li>';
      }
      $("#notification-menu ul").html(html);
    }
  });
}
function fViewNoti() {
  $.ajax({
    url: "Lopersa/viewNoti",
    type: "POST",
    data: {},
    dataType: "JSON",
    success: function (json) {
      var html = '';
      var icono = '';
      if (json.success == true) {
        $(".noviewnotification").hide().html('0');
      }
    }
  });
}