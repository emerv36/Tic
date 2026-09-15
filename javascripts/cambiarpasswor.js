$(function(){
    $("#cbox1").change(function(){
        var checkbox = document.getElementById('cbox1');
        var passField = document.getElementById('txtContraseña');
        var passField2 = document.getElementById('txtRepContraseña');
        if(checkbox.checked==true){
            passField.type = "text";
            passField2.type = "text";
        }else{
            passField.type = "password";    
            passField2.type = "password";    
        }
    });


});
function ConfirmarPass() {

    var Email = $('#txtEmail').val();
    var Contra = $('#txtContraseña').val();
    var ReContra = $('#txtRepContraseña').val();

    if($.trim(Contra) == ''){
        toastr.error('Por favor, ingrese su nueva contraseña.');
        $("#txtContraseña").focus();

    }else if($.trim(ReContra) == ''){
        toastr.error('Por favor, repita su nueva contraseña.');
        $("#txtRepContraseña").focus();

    }else if($.trim(Contra) != $.trim(ReContra)){
        toastr.error('Las contraseñas no coinciden.');
        $("#txtContraseña").focus();

    }else{
      $('.desactivarC').fadeIn(500);
      $.ajax({
        url : "../Lopersa/Login/CambiarPassword",
        type : "POST",
        data : {
          'PasswordCambiar' : Contra,
          'RecuperarPasswor' : Email,
        },
        dataType : "JSON",
        success : function (json){
          if(json.success == true){
            toastr.success("Se ha guardado su nueva contraseña.");
            $("#frmCambiarContraseña")[0].reset();
            window.location='../index.php';
          }else{
              toastr.error(json.mensaje);
          }
        }, complete : function(){
          $('.desactivarC').fadeOut(500);
        }
      });
    }
}