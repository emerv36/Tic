   
 $(function(){
    var tfBoxtxtUsuarioLeading = new mdc.textField.MDCTextField(document.getElementById('tf-txtUsuario'));
    var tfBoxtxtPasswordLeading = new mdc.textField.MDCTextField(document.getElementById('tf-txtPassword'));
    $("#frmRecuperarCuenta").submit(function(event){
      event.preventDefault();
      fRecuperacionDeCuenta();
    });
});

// Recuperar cuenta
function fmodalCambiarPass(){
  var dialog = new mdc.dialog.MDCDialog(document.querySelector('#dialog-fortpass'));
  dialog.show();
}

      function fRecuperacionDeCuenta(){
          var txtRecuperarCuenta = $('#txtRecuperarCuenta').val();

          if($.trim(txtRecuperarCuenta) == ''){
            toastr.error('Por favor, ingrese su correo electrónico.');
            $("#txtRecuperarCuenta").focus();
          }else{
              $('.desactivarC').fadeIn(500);
              $.ajax({
                  url : "Lopersa/Login/recuperarCuentaUsuario",
                  type : "POST",
                  data : {
                    'RecuperarCuenta' : txtRecuperarCuenta
                  },
                  dataType : "JSON",
                  success : function (json){
                      if(json.success == true){
                          toastr.success(json.mensaje);
                          $("#frmRecuperarCuenta")[0].reset();
                          var dialog = new mdc.dialog.MDCDialog(document.querySelector('#dialog-fortpass'));
                          dialog.close();
                      }else{
                          toastr.error(json.mensaje);
                      }
                  }, complete : function(){
                    $('.desactivarC').fadeOut(500);
                  }
              });
          }
      }
  //Fin recuperar Cuenta

   //login
      

      $("#frmLogin").submit(function( event ) {
          event.preventDefault();
          
          var usuario = $("#txtUsuario").val();
          var password = $("#txtPassword").val();
          if($.trim(usuario) == ''){
              toastr.error('Por favor, ingrese Usuario.');
              $("#txtUsuario").focus();
          }else if($.trim(password) == ''){
              toastr.error('Por favor, ingrese Contraseña.');
              $("#txtPassword").focus();
          }else{
              $.ajax({
                  url : "Lopersa/Login/iniciarsesion",
                  type : "POST",
                  data : {
                      'usuario' : usuario,
                      'password' : password
                  },
                  dataType : "JSON",
                  success : function (json){
                      if(json.success == true){
                          toastr.success("Logueado exitosamente!");
                          window.location='./dashboard';
                      }else{
                          toastr.error(json.mensaje);
                      }
                  }
              });
          }
      });
      // fin login

      