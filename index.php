<?php
session_start();
if(isset($_SESSION['Siigacenter'])){
    if($_SESSION["Siigacenter"]==true){
      header('Location:./dashboard');
     }else{
    header('Location:./login');
     }
}else{
    header("location:./login");
}


?>