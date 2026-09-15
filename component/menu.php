<?php
    require_once 'developer/PDOConn.php';
    $html = '';
    $sql = "SELECT codmenu, imagen, nombre_menu, nivel, orden, codsuperior, link, estado, target FROM menu WHERE estado = :estado AND nivel = :nivel ORDER BY orden ASC";
    $params = array(':estado' => 'on', ':nivel' => 1);

    $table = table($sql, $params);
    $i = 1;
    foreach ($table as $datarow => $data) {

        $submenu = table("SELECT codmenu, nombre_menu, link FROM menu WHERE codsuperior = :codsuperior ORDER BY orden ASC", array(':codsuperior' => $data["codmenu"]));
        if(count($submenu) != 0){
            $html .= '<li>';

            $html .= '<a><i class="fa fa-'.$data["imagen"].'"></i>'.$data["nombre_menu"].'<span class="fa arrow"></span></a>';
            $html .= '<ul class="nav nav-second-level collapse">';
                foreach ($submenu as $datarow1 => $data1) {
                    $html .= '<li>';
                    $html .= '<a  onclick="loadpag(\''.$data1["link"].'\',\''.$data1["codmenu"].'\');" id="'.$data1["codmenu"].'">'.$data1["nombre_menu"].'</a>';
                    $html .= '</li>';
                }
            $html .= '</ul>';
            $html .= '</li>';
        }else{
            $html .= '<li>';
            $html .= '<a onclick="loadpag(\''.$data["link"].'\',\''.$data["codmenu"].'\');" id="'.$data["codmenu"].'"><i '.$data["imagen"].'"></i> '.$data["nombre_menu"].'</a>';
            $html .= '</li>'; //class="active-menu" 
        }
        $i++;
    }
    $imp = '<nav class="navbar-default navbar-side" role="navigation"><div class="sidebar-collapse" style="cursor:pointer"><ul class="nav" id="main-menu">'.$html.'</ul></div></nav>';
    echo trim($imp);
?>

