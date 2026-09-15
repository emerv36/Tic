<?php
include 'developer/security.php';
?>
<!DOCTYPE html>
<html lang="es-ES">

<head>
    <meta Access-Control-Allow-Origin: *>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Siiga | Sistema Gestión Académica</title>
    <link rel="shortcut icon" type="image/png" href="assets\images\nuevoLogo-32x32.png" />
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->

    <!-- Font Awesome -->
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->

    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="assets/css/toastr.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <!-- jvectormap -->
    <!-- <link rel="stylesheet" href="build\bootstrap-less\mixins\pagination.less">-->
    <!--paginacion-->
    <link rel="stylesheet" href="bower_components/jvectormap/jquery-jvectormap.css">
    <!-- Date Picker -->
    <link rel="stylesheet" href="bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">

    <!-- Daterange picker -->
    <link rel="stylesheet" href="bower_components/bootstrap-daterangepicker/daterangepicker.css">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="bower_components/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <!--Icons-->

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <!--nuevos-->
    <!--cnd para el select2 buscador-->
    <link rel="stylesheet" href="assets/css/select2.min.css">

    <style>
        .select2-selection.select2-selection--single{
            height: 34px !important;
        }
        
        .select2{
            width: 100% !important;
        }
        
        .centerV-cells>thead>tr>th, .centerV-cells>tbody>tr>td {
            vertical-align: middle !important;
            content: center;
        }
       
        .swal-text {
            text-align: center;
            color: black;
        }

        .swal-overlay {
            background-color: rgba(128, 128, 128, 0.90);
        }

        .swal-text {
            background-color: #FEFAE3;
            padding: 17px;
            border: 1px solid #F0E1A1;
            display: block;
            margin: 22px;
            text-align: center;
        }

        #contenido {
            height: calc(100vh - 50px);
            overflow: auto;
            background-color: #ecf0f5;

        }

        .main-sidebar {

            border-right: 1px solid #337ab7;


        }

        .box {
            padding: 10px, 10px, 10px, 10px;
            border-radius: 5px;
            box-shadow: none !important;
            border: 1px solid #E7E9EB;




        }

        .box-body {
            padding: 10px, 10px, 10px, 10px;
        }

        .small-box {
            border-radius: 5px;
        }

        .content-wrapper {
            padding-top: 10px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .titulo {
            background-color: #337ab7;
            color: white;
        }
        .titulo tr th {
            background-color: #337ab7;
            color: white;
        }
        .titulo th {
            background-color: #337ab7;
            color: white;
        }

        .required {
            color: red;
        }

        .primary {
            background-color: #08298A;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        /* .dataTables_filter {
            display: none;
        } */
    </style>
</head>

<body class="hold-transition skin-blue">
    <!--header-->
    <?php include 'component/header.php' ?>
    <!-- fin del header-->
    <!--Menu-->
    <?php include 'component/menu2.php' ?>
    <!--FinMenu-->
    <div class="desactivarC" style="height: 100%;width: 100%;position: fixed;top: 0;left: 0;background: rgba(0,0,0,0.5); z-index: 100;display:none;">
        <img src="assets/images/loading.gif" style="margin-left:50%; margin-top:300px;width: 60px;">
    </div>
    <div id="contenido">
        <center>
            <div class="col-xs-12 col-md-12">
                <img class=" img-responsive" src="assets/images/nuevoLogo-400x82.png" style="margin-top: 15%; width: 400px; height: 82px;">
            </div>
        </center>
    </div>
    <!--footer-->
    <!--findel footer-->
</body>

<script src="bower_components/jquery/dist/jquery.min.js"></script>

<!-- jQuery UI 1.11.4 -->
<script src="bower_components/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->

<script>
    $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap 3.3.7 -->
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<!-- <script src="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css"></script> -->
<!-- Sparkline -->
<script src="bower_components/jquery-sparkline/dist/jquery.sparkline.min.js"></script>
<!-- jvectormap -->
<script src="plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
<!-- jQuery Knob Chart -->
<script src="bower_components/jquery-knob/dist/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="bower_components/moment/min/moment.min.js"></script>
<script src="bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- datepicker -->

<script src="bower_components/transition/transition.min.js"></script>
<!-- Bootstrap WYSIHTML5 -->
<script src="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<!-- Slimscroll -->
<script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<script src="assets/js/toastr.min.js"></script>
<script src="assets/js/jquery.inputmask.js"></script>
<script src="assets/js/bootstrap-tooltip.js"></script>
<script src="assets/js/custom-scripts.js"></script>
<script src="assets/js/select2.min.js"></script>
<!--<script src="assets/js/sweetalert.min.js"></script>-->
<!--<script src="assets/js/jquery.dataTables.min.js"></script>-->
<script src="assets/js/dataTables.bootstrap.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script src="javascripts/mensaje.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>

<!--<script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
<script src="assets/js/bootstrap-datetimepicker.min.js"></script> -->

<!--Data table con todo || Bootstrap 3|| jQuery 3||AutoFill||Buttons||Column visibility||HTML5 export||JSZip||pdfmake||Print view||ColReorder||DateTime||FixedColumns||FixedHeader||KeyTable||Responsive||RowGroup||RowReorder||Scroller||SearchBuilder||SearchPanes||Select-->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs/dt-1.11.3/af-2.3.7/b-2.0.1/b-colvis-2.0.1/b-html5-2.0.1/b-print-2.0.1/cr-1.5.4/date-1.1.1/fc-4.0.0/fh-3.2.0/kt-2.6.4/r-2.2.9/rg-1.1.3/rr-1.2.8/sc-2.0.5/sb-1.2.2/sp-1.4.0/sl-1.3.3/datatables.min.css"/>
<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.3.3/css/select.bootstrap.min.css"/>
 
<script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

<!-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script> -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>


<script type="text/javascript" src="https://cdn.datatables.net/v/bs/dt-1.11.3/af-2.3.7/b-2.0.1/b-colvis-2.0.1/b-html5-2.0.1/b-print-2.0.1/cr-1.5.4/date-1.1.1/fc-4.0.0/fh-3.2.0/kt-2.6.4/r-2.2.9/rg-1.1.3/rr-1.2.8/sc-2.0.5/sb-1.2.2/sp-1.4.0/sl-1.3.3/datatables.min.js"></script>

<!--Data table con todo https://datatables.net/download/-->

<!--<link rel="stylesheet" href="assets/css/dataTables.bootstrap.min.css">-->
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<!--<link rel="stylesheet" href="assets/css/sweetalert.min.css">-->

<!-- sheetjs use version 0.19.3 https://docs.sheetjs.com/docs/getting-started/example/ -->
<script lang="javascript" src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>

<script>
    $(document).ready(function() {
        cargarMenuDelUsuario()
        //  loadpag(localStorage.pagina);


    })
</script>

<script type="text/javascript">
    toastr.options.positionClass = 'toast-top-center';
</script>

<script type="text/javascript">
    $('[data-toggle="tooltip"]').tooltip();
</script>
<script src="javascripts/miperfil.js"></script>
</html>