<?php
	include("developer/conexion2.php");
	$params = explode(",", base64_decode($_GET["ema"]));
    $email = $params[0];
    $codigo_usu = trim($params[1]);
    $token = trim($params[2]);
?>

<!DOCTYPE html>
<html class="no-js" lang="zxx">
    <head>
		<!-- Meta -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="keywords" content="SITE KEYWORDS HERE" />
		<meta name="description" content="">
		<meta name='copyright' content=''>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<!-- Title -->
		<title>System Center &minus; Instituto Centro de Sistemas S.A.S</title>

	    <!-- FAVICON-->
	    <link rel="shortcut icon" type="image/png" href="../assets/img/system_ico.ico"/>
	    <!-- Bootstrap Styles-->
	    <link href="../assets/css/bootstrap.css" rel="stylesheet" />
	    <!-- FontAwesome Styles-->
	    <link href="../assets/css/font-awesome.css" rel="stylesheet" />
	    <!-- Morris Chart Styles-->
	    <link href="../assets/js/morris/morris-0.4.3.min.css" rel="stylesheet" />
	    <!-- Custom Styles-->
	    <link href="../assets/css/custom-styles.css" rel="stylesheet" />
	    <!-- Google Fonts-->
	    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
	     <!-- TABLE STYLES-->
	    <link href="../assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
	    <!-- TOAST STYLE -->
	    <link href="../assets/css/toastr.min.css" rel="stylesheet" />
	    <!-- MATERIAL ICONS -->
	    <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
	      rel="stylesheet">
    </head>
    <body>
	
		<!-- Book Preloader -->
        <div class="book_preload">
            <div class="book">
                <div class="book__page"></div>
                <div class="book__page"></div>
                <div class="book__page"></div>
            </div>
        </div>
		<!--/ End Book Preloader -->
	
		
		<!-- Team -->
		<section class="team section">
			<div class="container" style="margin-top: 35px;">
				<div class="row">
					<div class="col-12">
						<div class="section-title" style="justify-content: center;display: flex;">
							<?php
								$html = '';
								
								$select = conectar2("SELECT * FROM usu_usuarios WHERE codigo_usu = :codigo_usu AND codvalidacion = :token");
							    $select->execute(array(':codigo_usu' => $codigo_usu, ':token' => $token));
							    $resultadoUs = $select->fetch();
							    
							    if ($select->rowCount() > 0) {

							    	if($resultadoUs["email_confirmado"] === true){
							    		header('Location: ../index.php');
							    	}else{
									    $updateUs = "UPDATE usu_usuarios SET email_confirmado = :confirm WHERE codigo_usu = :codigo_usu";
										$con = conectar2($updateUs);

  
									    if ($con->execute(array(':confirm' => true, ':codigo_usu' => $codigo_usu))) {
									  		$html = '<div class="row rowY" style="border: solid 2px #214a81;border-radius: 26px;">
											<div style="float: left">
											<img src="../assets/img/bannertt.png" style="height: 175px;margin: -2px;">
											</div>
											<div style="float: left;margin-left: 45px;margin-top: 20px;">
									  		<h2 style="color: #214a81;"><b>Confirmación de Correo electrónico</b></h2>
											<p>Gracias por confirmar tu Correo electrónico.</p>
											<a class="btn" href="../index.php">Ir a inicio</a>
											</div>
											</div>';
									  	}
							    		
							    	}

							    }else{
							    	$html = '<div class="row rowY" style="border: solid 2px #214a81;border-radius: 26px;">
											<div style="float: left">
											<img src="../assets/img/banner.png" style="height: 175px;margin: -2px;">
											</div>
											<div style="float: left;margin-left: 45px;margin-top: 20px;">
									  		<h2 style="color: #214a81;"><b>Confirmación de Correo electrónico</b></h2>
											<p>Error de token.</p>
											<a class="btn" href="../index.php">Ir a inicio.</a>
											</div>
											</div>';
							    }
							    echo $html;

							?>
							
						</div>
					</div>
				</div>
			</div>
		</section>		
		<!--/ End Team -->
		
		<!-- JS Scripts-->
    <!-- jQuery Js -->    
    <script src="../assets/js/jquery-1.10.2.js"></script>
    <!-- Bootstrap Js -->
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/bootbox.min.js"></script>
    <script>
       
    </script>
    <!-- Metis Menu Js -->
    <script src="../assets/js/jquery.metisMenu.js"></script>
    <!-- Morris Chart Js -->
    <script src="../assets/js/morris/raphael-2.1.0.min.js"></script>
    <script src="../assets/js/morris/morris.js"></script>
    <!-- Custom Js -->
    <script src="../assets/js/custom-scripts.js"></script>
     <!-- DATA TABLE SCRIPTS -->
    <script src="../assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="../assets/js/dataTables/dataTables.bootstrap.js"></script>
    <!-- TOAST SCRIPTS -->
    <script src="../assets/js/toastr.min.js"></script>
    
    </body>
</html>