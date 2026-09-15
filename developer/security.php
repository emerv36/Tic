<?php
	session_start();
	if(isset($_SESSION["SiigaBv"])){
		
	}else{
		header("Location: ./login");
	}
?>
