  <!DOCTYPE html>
  <html lang="es">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

      <title>Verifica tu carnet</title>
      <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
      <link rel="shortcut icon" href="assets\images\nuevoLogo-32x32.png" />
      <link rel="stylesheet" href="assets/css/materialdesignicons.min.css">
      <link rel="stylesheet" href="assets/css/style2.css">
      <link rel="stylesheet" href="assets/css/toastr.min.css">

      <!-- plugins:js -->
      <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
          integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
      </script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
          integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
      </script>
      <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
          integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
      </script>
      <script src="assets/node_modules/material-components-web/material-components-web.min.js"></script>
      <script src="assets/node_modules/jquery/jquery.min.js"></script>
      <script src="assets/js/misc.js"></script>
      <script src="assets/js/material.js"></script>
      <script src="assets/js/toastr.min.js"></script>
      <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
       <style>
      </style>

  </head>

  <body>
				<div class="auth-screen">
					<div class="overlay">	
                        
         
              <div class="login">
                  <div style="width:100%">
                      <img class="logo img-responsive" src="assets/images/nuevoLogo-400x82.png">
                  </div>
                  <div class="px-4 py-3 col-xs-12">
              
                      <div class="form-group">
                          <label for="txtidentidad">Digite Número Identidad:</label>
                          <input type="text" class="form-control" id="txtidentidad" name="txtidentidad"  placeholder="Identidad si puntos" autocomplete="off">
                      </div>   
                           
     
                      <button class="btn  btn-block - btn-primary"  onclick="VerificarCarnet();">Verificar Carnet</button>
            </div>
              </div>
					</div>
				</div>

                
      <script>
  


    function VerificarCarnet(){
     
          var txtidentidad = $("#txtidentidad").val();
          if ($.trim(txtidentidad) == '') {
              toastr.error('Ingrese el número de identidad');
              $("#txtidentidad").focus();
         
            } else {
              $.ajax({
                  url: "Lopersa/Login/VerificarCarnet",
                  type: "POST",
                  data: {'txtidentidad': txtidentidad,},
                  dataType: "JSON",
                  success: function(json) {
                      if (json.success == true) {
                          mensaje("¡Información!", json.mensaje, "success");
                       } else {
                        toastr.success(json.mensaje);
                        
                 
                      }
                      $("#txtidentidad").val('');
                        $("#txtidentidad").focus();
                  }
              });
          }
        }
      // fin login
      </script>
<script src="javascripts/mensaje.js"></script>

  </body>

  </html>