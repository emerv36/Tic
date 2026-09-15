
  <!DOCTYPE html>
  <html lang="en">

  <head>
  <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Siiga | Siiga Control</title>
    <link rel="shortcut icon" type="image/png" href="assets\images\logo-system-08.png" />
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
 
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="assets/css/toastr.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="bower_components/jvectormap/jquery-jvectormap.css">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="bower_components/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
    <!-- Google Font -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
        <!--Icons-->

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <style>

input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
   -webkit-appearance: none; 
    margin: 0; 
   }
input[type=number] { -moz-appearance:textfield; }
  /* Firefox */
input[type=number] {
-moz-appearance: textfield;
}

#BotonReg{
    background: -webkit-linear-gradient(to right, #2B32B2, #1488CC); 
background: linear-gradient(to right, #2B32B2, #1488CC);  
display:block;
  margin-left: auto;
  margin-right: auto;
  border-radius: 6px;
}

body {
margin: 2;
font-family: Lato, sans-serif;
background: -webkit-linear-gradient(to right, #fff, #fff); 
background: linear-gradient(to right, #1488CC, #00AAFF);  

}

#contenedor{
top: 5%;
margin-left: 15%;
margin-right: 15%;


}

#form{
  border-color:#1488CC;
border-width: 2px;
border-bottom:  2px;
}


.select2-selection__rendered {
  line-height: 31px !important;
}
.select2-container .select2-selection--single {
  height: 34px !important;
}
.select2-selection__arrow {
  height: 34px !important;
}
</style>
  </head>
  <body>
  <div id="contenedor">
  <div>
  <center><img class="img-responsive" src="assets/img/imgEnlace.png" width="550px" height="150px"></center>
  </div> 

       <div class="col-md-12">
        <div class="box" id="form">
            <div class="row">
                <div class="col-md-12">
                    <div class="box-header bg-primary">
                        <h1 class="box-title"><B>REGISTRE SU EMPRESA</B></h1>
                    </div><!--Fin div box-header-->
                </div><!--Fin div col-md-11 -->
            </div><!--Fin div row-->
            <div class="box-body">
                <form id="CrearEmpresa" method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="regnit_empresa">Nit sin digito de verificación:</label>
                                <input type="number" name="regnit_empresa" id="regnit_empresa" class="form-control" value="" maxlength="20" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">
                        </div><!--col-md-6-->
                        <div class="col-md-6">
                          <label>Nombre</label>
                                <input type="text" name="regnombre_empresa" id="regnombre_empresa" class="form-control text-uppercase" value="" autocomplete="off">
                        </div><!--col-md-6-->
                   
                        <div class="col-md-6">
                            <label>Correo Empresa</label>
                                <input type="text" name="regcorreo_empresa" id="regcorreo_empresa" class="form-control" value="" autocomplete="off">
                        </div><!--col-md-6-->
                        <div class="col-md-6">
                            <label>Teléfono:</label>
                            <input type="number" name="regtelefono_empresa" id="regtelefono_empresa" class="form-control"value="" maxlength="10" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"> 
                        </div><!--col-md-6-->
                             <input type="hidden" name="txtfecha" id="txtfecha" class="form-control text-uppercase"value="<?php echo  date("Y-m-d"); ?>">
                  
                    </div><!--row-->
                    <div class="row">
                      <div class="col-md-12">
                        <hr>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-12">
                        <label class="text-muted">
                          Selecciona las opciones correspondiente el sistema genera automaticamente la dirección 
                          
                        </label>
                      </div>
                    </div>


                    <div class="row">
                        <div class="col-md-3">
                                <label>Dirección: </br> 
                                Tipo de via:</label>
                                    <Select name="regtipo_via" id="regtipo_via" class="form-control" onchange="DireccionEmpresa2()">
                                    <option value="">Ninguna</option>
                                    <option value="Calle">Calle</option>
                                    <option value="Carrera">Carrera</option>
                                    <option value="Diagonal">Diagonal</option>
                                    <option value="Transversal">Transversal</option>
                                    <option value="Avenida calle">Avenida calle</option>
                                    <option value="Avenida carrera">Avenida carrera</option>
                                    </Select>  
                        </div><!--12-->
                        <div class="col-md-1">
                            <label></br>N°:</label>
                                <input type="number" name="regnumero_dir1" id="regnumero_dir1" class="form-control" value="" onkeyup="DireccionEmpresa2()" maxlength="4" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">  
                        </div><!--13-->
                        <div class="col-md-1">
                            <label></br>Letra:</label>
                            <Select name="regletra_dir1" id="regletra_dir1"  class="form-control" onchange="DireccionEmpresa2()">
                                    <option value="">Ning.</option><option value="A">A</option> <option value="B">B</option><option value="C">C</option>
                                    <option value="D">D</option><option value="E">E</option><option value="F">F</option> <option value="G">G</option><option value="H">H</option>
                                    <option value="I">I</option><option value="J">J</option> <option value="K">K</option><option value="L">L</option>
                                    <option value="M">M</option><option value="N">N</option> <option value="O">O</option><option value="P">P</option>
                                    <option value="R">R</option><option value="S">S</option> <option value="T">T</option><option value="U">U</option>
                                    <option value="V">V</option><option value="W">W</option> <option value="X">X</option><option value="Y">Y</option><option value="Z">Z</option>                        
                            </Select>     
                                                    
                        </div><!--14-->
                    <div class="col-md-2">
                      <label></br>Zona:</label>
                      <Select class="form-control" name="regzona_dir1" id="regzona_dir1" onchange="DireccionEmpresa2()">
                              <option value="">Ning.</option>
                              <option value="Norte">Norte</option>
                              <option value="Sur">Sur</option>
                              <option value="Este">Este</option>
                              <option value="Oeste">Oeste</option>
                      </Select>     
                             
                    </div><!--14-->
                   
                    <div class="col-md-1">
                      <label></br> #&nbsp;&nbsp;&nbsp;&nbsp;N°:</label>
                          <input type="number" name="regnumero_dir2" id="regnumero_dir2" class="form-control" value="" onkeyup="DireccionEmpresa2()" maxlength="4" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">  
                  </div><!--13-->
                  <div class="col-md-1">
                      <label></br>Letra:</label>
                      <Select class="form-control" name="regletra_dir2" id="regletra_dir2" onchange="DireccionEmpresa2()">
                              <option value="">Ning.</option><option value="A">A</option> <option value="B">B</option><option value="C">C</option>
                              <option value="D">D</option><option value="E">E</option><option value="F">F</option> <option value="G">G</option><option value="H">H</option>
                              <option value="I">I</option><option value="J">J</option> <option value="K">K</option><option value="L">L</option>
                              <option value="M">M</option><option value="N">N</option> <option value="O">O</option><option value="P">P</option>
                              <option value="R">R</option><option value="S">S</option> <option value="T">T</option><option value="U">U</option>
                              <option value="V">V</option><option value="W">W</option> <option value="X">X</option><option value="Y">Y</option><option value="Z">Z</option>                        
                      </Select>     
                                             
                    </div><!--14-->
                    <div class="col-md-2">
                      <label></br>Zona:</label>
                      <Select name="regzona_dir2" id="regzona_dir2"  class="form-control" onchange="DireccionEmpresa2()">
                              <option value="">Ning.</option>
                              <option value="Norte">Norte</option>
                              <option value="Sur">Sur</option>
                              <option value="Este">Este</option>
                              <option value="Oeste">Oeste</option>
                      </Select>     
                      </div><!--14-->
                      <div class="col-md-1">
                      <label></br>N°:</label>
                          <input type="number" name="regnumero_dir3" id="regnumero_dir3" class="form-control" value="" onkeyup="DireccionEmpresa2()" maxlength="4" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">  
                  </div><!--13-->
                  </div>
                 
                  <div class="row">
                  
                  <div class="col-md-12"><br>
                      <h4>Revisa tu direccion</h4>
                      <p>Aqui aparece tu dirección mientras llenas los campos, verifica que sea correcta:</p>
                      <input type="text" name="regdireccion_final" id="regdireccion_final" class="form-control" value="" disabled>  
                      </div>   
                  </div>
                    <div clas="row">
                    <div class="col-md-12">
                        <div class="panel-heading">
                           <br><button type="button" id="BotonReg" class="btn btn-primary" onclick="RegistrarEmpresa();">Registrar Empresa</button>
                        </div><!--19-->
                    </div>
                    </div> <!--row-->
                </form> <!--form-->
            </div><!--Fin div box-body-->
        </div><!--Fin div box-->
    </div><!--Fin div col-xs-12-->
  </div>  
</body>
</html>
<script src="javascripts/gestionarempresa.js"></script>
<script src="javascripts/mensaje.js"></script>
<script src="assets/js/toastr.min.js"></script>
<script type="text/javascript">
toastr.options.positionClass = 'toast-top-center';
</script>
