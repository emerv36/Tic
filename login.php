<?php
session_start();
if (isset($_SESSION["SiigaBv"])) {
    header('Location:./dashboard');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Iniciar Sesión | Control Académico</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="shortcut icon" href="assets\images\nuevoLogo-32x32.png" />
    <link rel="stylesheet" href="assets/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/toastr.min.css">

    <!-- plugins:js -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
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
            <div class="login" style="width:350px">
                <form class="px-4 py-3 col-xs-12" id="frmLogin">
                <div style="width:100%">
                    <img class="logo img-responsive" src="assets/images/nuevoLogo-400x82.png">
                </div><br>
                    <div class="form-group">
                        <label for="txtUsuario">Usuario</label>
                        <input type="text" class="form-control" id="txtUsuario" placeholder="Usuario" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="txtPassword">Clave</label>
                        <input type="password" class="form-control" id="txtPassword" placeholder="Clave" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            // var tfBoxtxtUsuarioLeading = new mdc.textField.MDCTextField(document.getElementById('tf-txtUsuario'));
            // var tfBoxtxtPasswordLeading = new mdc.textField.MDCTextField(document.getElementById(
            //     'tf-txtPassword'));
            $("#frmRecuperarCuenta").submit(function(event) {
                event.preventDefault();
                fRecuperacionDeCuenta();
            });
        });

        // Recuperar cuenta
        function fmodalCambiarPass() {
            var dialog = new mdc.dialog.MDCDialog(document.querySelector('#dialog-fortpass'));
            dialog.show();
        }

        function fRecuperacionDeCuenta() {
            var txtRecuperarCuenta = $('#txtRecuperarCuenta').val();

            if ($.trim(txtRecuperarCuenta) == '') {
                toastr.error('Por favor, ingrese su correo electrónico.');
                $("#txtRecuperarCuenta").focus();
            } else {
                $('.desactivarC').fadeIn(500);
                $.ajax({
                    url: "Lopersa/Login/recuperarCuentaUsuario",
                    type: "POST",
                    data: {
                        'RecuperarCuenta': txtRecuperarCuenta
                    },
                    dataType: "JSON",
                    success: function(json) {
                        if (json.success == true) {
                            toastr.success(json.mensaje);
                            $("#frmRecuperarCuenta")[0].reset();
                            var dialog = new mdc.dialog.MDCDialog(document.querySelector('#dialog-fortpass'));
                            dialog.close();
                        } else {
                            toastr.error(json.mensaje);
                        }
                    },
                    complete: function() {
                        $('.desactivarC').fadeOut(500);
                    }
                });
            }
        }
        //Fin recuperar Cuenta

        //login


        $("#frmLogin").submit(function(event) {
            event.preventDefault();
            var usuario = $("#txtUsuario").val();
            var password = $("#txtPassword").val();
            if ($.trim(usuario) == '') {
                toastr.error('Por favor, ingrese Usuario.');
                $("#txtUsuario").focus();
            } else if ($.trim(password) == '') {
                toastr.error('Por favor, ingrese Contraseña.');
                $("#txtPassword").focus();
            } else {
                $.ajax({
                    url: "Lopersa/Login/iniciarsesion",
                    type: "POST",
                    data: {
                        'usuario': usuario,
                        'password': password
                    },
                    dataType: "JSON",
                    success: function(json) {
                        if (json.success == true) {
                            toastr.success(json.mensaje);
                            window.location = 'dashboard';
                        } else {
                            window.location = 'dashboard';
                            toastr.error(json.mensaje);

                        }
                    }
                });
            }
        });
        // fin login
    </script>


</body>

</html>