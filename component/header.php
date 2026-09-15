<header class="main-header">
    <!-- Logo -->
    <a href="index.php" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini">
            <img src="assets/images/nuevoLogo-32x32.png">
        </span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg">
            <img src="assets/images/logoNuevo-negativo.png">
        </span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">


                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="assets/images/fotoperfil/user.png"
                            class="user-image" alt="User Image" id="imagen">
                        <span class="hidden-xs"><?php echo ucwords(strtolower($_SESSION['nombres']))?></span>
                    </a>
                    <ul class="dropdown-menu">

                        <li class="user-header">

                            <div class="row">
                                <img src="assets/images/fotoperfil/user.png"
                                    class="img-circle btn btn-primary" alt="User Image" id="EfotoPerfil"
                                    style="width: 120px; height:100px;" data-toggle="modal" style="cursor:pointer"
                                    title="Subir una foto de perfil." data-target="#editar-perfil"
                                    onclick="EditarFoto()">
                                <p id="nombre-usuario"> <?php echo ucwords(strtolower($_SESSION['nombres']))?> </p>
                                <p id="nombre-usuario"> <?php echo ucwords(strtolower($_SESSION['IN_nombre_rol']))?> </p>
                            </div>


                        </li>
                        <!-- Menu Body -->
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <button type="button" class="btn btn-primary" data-toggle="modal"
                                    title="Editar contraseña." onclick="fModalContrasena();">
                                    <B>Editar perfil</B>
                                </button>
                            </div>
                            <div class="pull-right">
                                <a href="developer/logout.php" class="btn btn-default btn-flat">Cerrar sesion</a>
                            </div>
                        </li>
                    </ul>
                </li>
                <!-- Control Sidebar Toggle Button -->

            </ul>
        </div>
    </nav>
</header>

<!--modal para cambiar la contraseña del usuario -->
<div class="modal fade" id="dialog-cambiarcontrasena">
    <div class="modal-dialog" style="width:32vw;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Cambiar contraseña</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="frmEditar" method="POST">
                        <div class="input-item col-xs-12">

                            <input type="hidden" id="codusuario" name="codusuario"
                                value="<?php echo $_SESSION['IN_codigo_usuCA']; ?>">
                            <input type="hidden" id="txtUsuario" name="txtUsuario"
                                value="<?php echo  $_SESSION['IN_email']; ?>">


                            <label for="contraActual">Contraseña actual
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="password" id="contraActual" name="contraActual">
                        </div>
                        <div class="input-item col-xs-12">
                            <label for="nuevaContra">Contraseña nueva
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="password" id="nuevaContra" name="nuevaContra">
                        </div>
                        <div class="input-item col-xs-12">
                            <label for="confirmContra">Confirmar contraseña
                                <span class="required">*</span>
                            </label>
                        </div>
                        <div class="input-item col-xs-12">
                            <input class="form-control" type="password" id="confirmContra" name="confirmContra">
                        </div>

                        <div class="input-item col-xs-12" style="margin-right:80px;">
                            <br>
                            <input style="float: right" type="checkbox" id="cbox1"><a style="float: right">Ver
                                contraseña</a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="CambiarContrasena();">Actualizar
                    Contraseña</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!--fin modal para cambiar la contraseña del usuario -->

<!--modal para cambiar foto del usuario -->
<div class="modal fade" id="dialog-cambiarfoto">
    <div class="modal-dialog" style="width:32vw;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><B>Subir foto de perfil</B></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form id="frmEditarFoto" method="POST">

                        <div class="row">
                            <div class="col-md-8">
                                <div class="custom-file" style="text-align:center;">

                                    <label class="custom-file-label" for="idfoto">Seleccionar Foto<span
                                            class="required">*</span></label>
                                    <CENTER> <input type="file" class="custom-file-input" name="idfotos" id="idfotos"
                                            accept="image/jpeg, image/png">
                                    </CENTER>
                                </div>

                            </div>

                            <div class="col-md-4">
                                <img src="<?php 
                                if(isset($_SESSION['foto'])){ 
                                echo $_SESSION['server'].'./assets/images/fotoperfil/'.$_SESSION['foto']; 
                                } else { 
                                echo $_SESSION['server'].'./assets/images/fotoperfil/user.png'; 
                                } ?>"
                                    class="img-circle btn btn-primary" alt="User Image" id="EfotoPerfiles"
                                    style="width: 120px; height:100px;" data-toggle="modal" style="cursor:pointer"
                                    title="Subir una foto de perfil." data-target="#editar-docentes">

                            </div>

                        </div>

                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal" id="elifoto"
                    name="elifoto">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="CambiarFoto();">Subir
                    Foto</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!--fin modal para cambiar foto del usuario -->