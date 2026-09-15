$(document).ready(function() {

  $("#frm-imagen").submit(function(event) {
    event.preventDefault();
  });

  $("#frmEditarFoto").submit(function(event){
    event.preventDefault();
  });

  $(function(){
    $("#cbox1").change(function(){
      var checkbox = document.getElementById('cbox1');
      var passField = document.getElementById('contraActual');
      var passField2 = document.getElementById('nuevaContra');
      var passField3 = document.getElementById('confirmContra');
      if(checkbox.checked==true){
        passField.type = "text";
        passField2.type = "text";
        passField3.type = "text";
      }else{
        passField.type = "password";    
        passField2.type = "password"; 
        passField3.type = "password";   
      }
    });
  });
});

function fModalContrasena() {
  $("#dialog-cambiarcontrasena").modal("show");
}

function EditarFoto(){
  $("#dialog-cambiarfoto").modal("show");
}

function CambiarContrasena(){
  var codusuario = $("#codusuario").val();
  var contraActual = $("#contraActual").val();
  var nuevaContra = $("#nuevaContra").val();
  var confirmContra = $("#confirmContra").val();

  if($.trim(contraActual) == ''){
    toastr.error('Por favor, ingrese Contraseña actual.');
    $("#contraActual").focus();
  }else if($.trim(nuevaContra) == ''){
    toastr.error('Por favor, ingrese Nueva contraseña.');
    $("#nuevaContra").focus();
  }else if($.trim(confirmContra) == ''){
    toastr.error('Por favor, confirme Nueva contraseña.');
    $("#confirmContra").focus();
  }else if($.trim(nuevaContra) != $.trim(confirmContra)){
    toastr.error('Las contraseñas ingresadas no coinciden');
    $("#txtTelefono").focus();
  }else{
    $('.desactivarC').fadeIn(500);       
    $.ajax({
      url : "Lisa/Usuarios/editContrasena",
      type : "POST",
      data : {
        'codusuario': codusuario,
        'contraActual' : contraActual,
        'nuevaContra' :  nuevaContra
      },
      dataType : "JSON",
      success : function (json){
        if(json.success == true){
          toastr.success("Contraseña Actualizada exitosamente");
          $("#dialog-cambiarcontrasena").modal("hide");
        }else{
          toastr.error(json.mensaje);
        }
      }, complete: function(){
        $("#frmEditar")[0].reset();
        $('.desactivarC').fadeOut(500); 
      }
    });
  }
}


$("input[name='idfotos']").on("change",function(event){
  var tmppath = URL.createObjectURL(event.target.files[0]);
  $("#EfotoPerfiles").attr('src',tmppath);
});

//función para subir la foto de perfil del usuario
function CambiarFoto(){
  var foto =  $("#idfotos").val()
  var formData= new FormData($("#frmEditarFoto")[0]);
  if($.trim(foto)==''){
    toastr.error("Por favor, seleccione la imagen a subir.");
  }else{
    $('.desactivarC').fadeIn(500);
    $.ajax({
      url: "Lisa/Usuarios/Cambiarfoto?nombphoto="+$("#codusuario").val()+'_'+$("#txtUsuario").val(),
      type:"POST",
      data:formData,
      contentType: false,
      processData: false,
      dataType:"JSON",
      success: function(json){
        if(json.success==true){
          toastr.success("Foto actualizada exitosamente.");
          $("#idfotos").val('');
          $("#dialog-cambiarfoto").modal("hide");
        }else{
          toastr.error(json.mensaje);
        }
      }, 
      complete: function(){
        $('.desactivarC').fadeOut(500);
        location.reload();
      }
    }); 
  }
}
//fin función para subir la foto de perfil del usuario