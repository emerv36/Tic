<?php
@ini_set('memory_limit', '512M');
@set_time_limit(180);
require_once "developer/Config/config.php";
require_once "developer/Config/PDOconn.php";
require_once "plugins/fpdf/fpdf.php";
require_once "plugins/fpdi/FPDI-1.6.2/fpdi.php";

class PDF_Carnet extends FPDF {
    var $angle = 0;

    function Rotate($angle, $x=-1, $y=-1) {
        if($x==-1) $x=$this->x;
        if($y==-1) $y=$this->y;
        if($this->angle!=0) $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0) {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }

    function _endpage() {
        if($this->angle!=0) {
            $this->angle=0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    function RotatedText($x, $y, $txt, $angle) {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }
}

function cropCircularImage($source_path, $dest_path, $size = 300) {
    if (!file_exists($source_path)) return false;
    $raw_data = @file_get_contents($source_path);
    if ($raw_data === false) return false;
    
    $source = @imagecreatefromstring($raw_data);
    if (!$source) return false;

    $width = imagesx($source);
    $height = imagesy($source);
    $min_size = min($width, $height);

    
    // Crop to square first
    $cropped = imagecrop($source, [
        'x' => ($width - $min_size) / 2,
        'y' => ($height - $min_size) / 2,
        'width' => $min_size,
        'height' => $min_size
    ]);
    if (!$cropped) $cropped = $source;
    
    // Resize to target size with white background
    $resized = imagecreatetruecolor($size, $size);
    $white = imagecolorallocate($resized, 255, 255, 255);
    imagefill($resized, 0, 0, $white);
    imagecopyresampled($resized, $cropped, 0, 0, 0, 0, $size, $size, $min_size, $min_size);
    
    // Create circular mask
    $mask = imagecreatetruecolor($size, $size);
    $transparent = imagecolorallocate($mask, 255, 0, 0);
    imagecolortransparent($mask, $transparent);
    imagefilledellipse($mask, $size/2, $size/2, $size, $size, $transparent);
    
    // Final image with solid white background (prevents FPDF black alpha background)
    $final = imagecreatetruecolor($size, $size);
    $white_final = imagecolorallocate($final, 255, 255, 255);
    imagefill($final, 0, 0, $white_final);
    
    for($x = 0; $x < $size; $x++) {
        for($y = 0; $y < $size; $y++) {
            $c = imagecolorat($mask, $x, $y);
            if($c == $transparent) {
                imagesetpixel($final, $x, $y, imagecolorat($resized, $x, $y));
            }
        }
    }
    
    imagepng($final, $dest_path);
    imagedestroy($source);
    if ($cropped !== $source) imagedestroy($cropped);
    imagedestroy($resized);
    imagedestroy($mask);
    imagedestroy($final);
    return true;
}

$db = new db();

if (isset($_GET['lote'])) {
    $sql = "SELECT i.*, p.nombre_programa, l.codigo AS codigo_lote FROM inscripcion i 
            LEFT JOIN programa p ON i.id_programa_inscripcionfk = p.id_programa 
            LEFT JOIN lotecarnet l ON i.id_lote_inscripcionfk = l.id_lote
            WHERE i.id_lote_inscripcionfk = :lote AND i.estado_inscripcion != 4"; // 4 es corrección/anulado
    $estudiantes = $db->table($sql, array(':lote' => $_GET['lote']));
    if (empty($estudiantes)) {
        die("No hay estudiantes en este lote");
    }
} else if (isset($_GET['id'])) {
    $sql = "SELECT i.*, p.nombre_programa, l.codigo AS codigo_lote FROM inscripcion i 
            LEFT JOIN programa p ON i.id_programa_inscripcionfk = p.id_programa 
            LEFT JOIN lotecarnet l ON i.id_lote_inscripcionfk = l.id_lote
            WHERE i.id_inscripcion = :id";
    $est = $db->row($sql, array(':id' => $_GET['id']));
    if (!$est) {
        die("Estudiante no encontrado");
    }
    $estudiantes = array($est);
} else {
    die("Especifique un id de estudiante o id de lote");
}

$pdf = new PDF_Carnet();
$pdf->SetAutoPageBreak(false);

$ultimo_es_practicante = false;
$ultimo_lote_id = "";
$ultimo_lote_codigo = "";

foreach ($estudiantes as $estudiante) {
    // Determinar plantilla según categoría si existe, por defecto Estudiante
    $frente_tpl = 'plantillas/front_estudiante.jpeg';
    $es_practicante = false;
    
    // Categoría 3 es Practicante
    if (isset($estudiante['categoria_carnet']) && $estudiante['categoria_carnet'] == '3') {
        $frente_tpl = 'plantillas/front_pract.jpeg';
        $es_practicante = true;
    }
    
    // Guardar para la página de reverso al final
    $ultimo_es_practicante = $es_practicante;
    $ultimo_lote_id = $estudiante['id_lote_inscripcionfk'];
    $ultimo_lote_codigo = isset($estudiante['codigo_lote']) && $estudiante['codigo_lote'] != '' ? $estudiante['codigo_lote'] : $estudiante['id_lote_inscripcionfk'];

    // ---- PÁGINA 1 (FRENTE) ----
    if ($es_practicante) {
        $pdf->AddPage('P', array(54, 85)); // Tamaño tarjeta CR80 mm (aprox 54x85)
        $pdf->Image($frente_tpl, 0, 0, 54, 85);
        
        // Foto vertical
        $foto_jpg = "assets/fotoperfil/" . $estudiante['identificacion'] . ".jpg";
        $foto_png = sys_get_temp_dir() . "/tmp_carnet_" . $estudiante['identificacion'] . ".png";
        if (file_exists($foto_jpg)) {
            if (cropCircularImage($foto_jpg, $foto_png, 300)) {
                // Centrado en el ancho (54), foto de 30x30, x = (54-30)/2 = 12. Y = 13.8 mm
                $pdf->Image($foto_png, 12, 13.8, 30, 30);
                unlink($foto_png);
            }
        }
        
        // Textos Frente
        $nombre_txt = utf8_decode(strtoupper($estudiante['nombre_estudiante']));
        $apellido_txt = utf8_decode(strtoupper($estudiante['apellido_estudiante']));

        $pdf->SetTextColor(255, 255, 255); // Blanco
        $pdf->SetXY(0, 46);
        $fs = 10; $pdf->SetFont('Arial', 'B', $fs);
        while($pdf->GetStringWidth($nombre_txt) > 52 && $fs > 6) { $fs -= 0.5; $pdf->SetFont('Arial', 'B', $fs); }
        $pdf->Cell(54, 5, $nombre_txt, 0, 1, 'C');
        
        $pdf->SetTextColor(0, 200, 200); // Celeste (Aprox)
        $pdf->SetXY(0, 51);
        $fs = 10; $pdf->SetFont('Arial', 'B', $fs);
        while($pdf->GetStringWidth($apellido_txt) > 52 && $fs > 6) { $fs -= 0.5; $pdf->SetFont('Arial', 'B', $fs); }
        $pdf->Cell(54, 5, $apellido_txt, 0, 1, 'C');
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY(0, 58);
        $pdf->Cell(54, 5, $estudiante['identificacion'], 0, 1, 'C');
        
        $pdf->SetXY(0, 63);
        $pdf->Cell(54, 5, $estudiante['tipo_sangre'], 0, 1, 'C');
        
        // Programa
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY(2, 75); // Más abajo
        $pdf->MultiCell(50, 3, utf8_decode(strtoupper($estudiante['nombre_programa'])), 0, 'C');

    } else {
        $pdf->AddPage('L', array(54, 85)); // Tamaño tarjeta CR80 mm (aprox 85x54 pero rotado)
        $pdf->Image($frente_tpl, 0, 0, 85, 54);

        // Textos Frente
        $nombre_txt = utf8_decode(strtoupper($estudiante['nombre_estudiante']));
        $apellido_txt = utf8_decode(strtoupper($estudiante['apellido_estudiante']));

        $pdf->SetTextColor(25, 45, 95); // Azul oscuro
        $pdf->SetXY(2, 14);
        $fs = 10; $pdf->SetFont('Arial', 'B', $fs);
        while($pdf->GetStringWidth($nombre_txt) > 42 && $fs > 5.5) { $fs -= 0.5; $pdf->SetFont('Arial', 'B', $fs); }
        $pdf->Cell(44, 5, $nombre_txt, 0, 1, 'C');
        
        $pdf->SetXY(2, 19);
        $pdf->SetTextColor(0, 153, 204); // Celeste
        $fs = 10; $pdf->SetFont('Arial', 'B', $fs);
        while($pdf->GetStringWidth($apellido_txt) > 42 && $fs > 5.5) { $fs -= 0.5; $pdf->SetFont('Arial', 'B', $fs); }
        $pdf->Cell(44, 5, $apellido_txt, 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(25, 45, 95);
        $pdf->SetXY(2, 26);
        $pdf->Cell(20, 4, utf8_decode('Código: '), 0, 0, 'R');
        $pdf->SetTextColor(0, 153, 204);
        $pdf->Cell(24, 4, $estudiante['identificacion'], 0, 1, 'L');

        $pdf->SetTextColor(25, 45, 95);
        $pdf->SetXY(2, 30);
        $pdf->Cell(20, 4, 'RH: ', 0, 0, 'R');
        $pdf->SetTextColor(0, 153, 204);
        // Quitar la palabra "RH" si ya viene en el valor de la base de datos
        $rh_val = trim(str_ireplace('RH', '', $estudiante['tipo_sangre']));
        $pdf->Cell(24, 4, $rh_val, 0, 1, 'L');

        // Programa
        $pdf->SetFont('Arial', 'B', 6.5);
        $pdf->SetTextColor(255, 255, 255);
        $prog_txt = utf8_decode(strtoupper($estudiante['nombre_programa']));
        
        // Ajuste dinámico de Y si el texto es muy largo y hace salto de línea
        $y_prog = (strlen($prog_txt) > 28) ? 46 : 47.5; 
        $pdf->SetXY(2, $y_prog);
        $pdf->MultiCell(44, 3, $prog_txt, 0, 'C');

        // Foto
        $foto_jpg = "assets/fotoperfil/" . $estudiante['identificacion'] . ".jpg";
        $foto_png = sys_get_temp_dir() . "/tmp_carnet_" . $estudiante['identificacion'] . ".png";

        if (file_exists($foto_jpg)) {
            if (cropCircularImage($foto_jpg, $foto_png, 300)) {
                // Coordenadas aproximadas para la foto en el círculo de la derecha
                $pdf->Image($foto_png, 51, 12, 29, 29);
                unlink($foto_png);
            }
        }
    }
}

// ---- PÁGINA FINAL (ÚNICO REVERSO POR LOTE) ----
if ($ultimo_es_practicante) {
    $pdf->AddPage('P', array(54, 85));
    $pdf->Image('plantillas/back_pract.jpeg', 0, 0, 54, 85);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY(0, 75);
    $pdf->Cell(54, 5, utf8_decode($ultimo_lote_codigo), 0, 1, 'C');
} else {
    $pdf->AddPage('L', array(54, 85));
    $pdf->Image('plantillas/back_estudiante.jpeg', 0, 0, 85, 54);

    // Lote (Vertical a la derecha)
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->RotatedText(80, 48, utf8_decode($ultimo_lote_codigo), 90);
}

if (isset($_GET['lote'])) {
    $pdf->Output('I', 'Lote_' . $_GET['lote'] . '.pdf');
} else {
    $pdf->Output('I', 'Carnet_' . $estudiantes[0]['identificacion'] . '.pdf');
}
?>
