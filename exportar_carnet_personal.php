<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once "developer/Config/config.php";
require_once "developer/Config/PDOconn.php";
require_once "developer/Config/sige_config.php";
require_once "developer/Config/personal_ui_config.php";
require_once "developer/Services/PersonalCatalogAuthorization.php";
require_once "plugins/fpdf/fpdf.php";

if (!TIC_SIGE_PERSONAL_UI_ENABLED) {
    die("Módulo deshabilitado");
}

try {
    $authorization = new PersonalCatalogAuthorization();
    $authorization->autorizar($_SESSION);
} catch (Exception $e) {
    if (empty($_SESSION['SiigaBv']) && empty($_SESSION['USERID']) && empty($_SESSION['IN_codigo_usuCA'])) {
        die("No autorizado.");
    }
}

$uuid = $_GET['uuid'] ?? '';
if (!preg_match('/^[0-9a-f-]{32,36}$/i', $uuid)) {
    die("UUID inválido: " . htmlspecialchars($uuid));
}

$template_front = "plantillas/front_personal.png";
$template_back = "plantillas/back_personal.jpeg";

if (!file_exists($template_front) || !file_exists($template_back)) {
    die("ERROR: Las plantillas de imagen no están instaladas en el servidor.");
}

$db = new db();
// Mapeo seguro contra inyección, ya que el UUID pasó la validación regex
$sql = "SELECT p.* 
        FROM sige_personal_proyeccion p 
        WHERE p.persona_uuid = :uuid";
$persona = $db->row($sql, array(':uuid' => $uuid));

if (!$persona) {
    die("Persona no encontrada en la proyección.");
}

if ($persona['estado_persona'] !== 'ACTIVA') { //  || $persona['tiene_foto'] != 1 || !$persona['uid_rfid']
    die("La persona no es elegible para impresión (inactiva).");
}

$vinculos = json_decode($persona['vinculos_json'] ?? '[]', true) ?: [];
$es_docente = false;
$es_administrativo = false;
$es_contratista = false;
$cargo_nombre = "FUNCIONARIO";

foreach ($vinculos as $vinculo) {
    $tipo_vinculo = strtoupper($vinculo['tipo'] ?? 'FUNCIONARIO');
    if (strpos($tipo_vinculo, 'DOCENTE') !== false) {
        $es_docente = true;
        $cargo_nombre = $tipo_vinculo;
        break;
    } else if (strpos($tipo_vinculo, 'CONTRATISTA') !== false) {
        $es_contratista = true;
        $cargo_nombre = $tipo_vinculo;
    } else {
        $es_administrativo = true;
        if ($cargo_nombre === "FUNCIONARIO") $cargo_nombre = $tipo_vinculo;
    }
}

class PDF_Carnet_Personal extends FPDF {
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
    
    $mime = mime_content_type($source_path);
    $source = null;
    if ($mime == 'image/jpeg') $source = imagecreatefromjpeg($source_path);
    else if ($mime == 'image/png') $source = imagecreatefrompng($source_path);
    
    if (!$source) return false;

    $width = imagesx($source);
    $height = imagesy($source);
    $min_size = min($width, $height);
    
    // Crop to square
    $cropped = imagecrop($source, [
        'x' => ($width - $min_size) / 2,
        'y' => ($height - $min_size) / 2,
        'width' => $min_size,
        'height' => $min_size
    ]);
    
    // Resize
    $resized = imagecreatetruecolor($size, $size);
    imagecopyresampled($resized, $cropped, 0, 0, 0, 0, $size, $size, $min_size, $min_size);
    
    // Circular mask
    $mask = imagecreatetruecolor($size, $size);
    $transparent = imagecolorallocate($mask, 255, 0, 0);
    imagecolortransparent($mask, $transparent);
    imagefilledellipse($mask, $size/2, $size/2, $size, $size, $transparent);
    
    $final = imagecreatetruecolor($size, $size);
    imagealphablending($final, true);
    imagesavealpha($final, true);
    $bg = imagecolorallocatealpha($final, 0, 0, 0, 127);
    imagefill($final, 0, 0, $bg);
    
    for($x = 0; $x < $size; $x++) {
        for($y = 0; $y < $size; $y++) {
            $c = imagecolorat($mask, $x, $y);
            if($c == $transparent) {
                $color = imagecolorsforindex($resized, imagecolorat($resized, $x, $y));
                imagesetpixel($final, $x, $y, imagecolorallocatealpha($final, $color['red'], $color['green'], $color['blue'], 0));
            }
        }
    }
    
    imagepng($final, $dest_path);
    imagedestroy($source);
    imagedestroy($cropped);
    imagedestroy($resized);
    imagedestroy($mask);
    imagedestroy($final);
    return true;
}

$pdf = new PDF_Carnet_Personal();
$pdf->SetAutoPageBreak(false);

$pdf->AddPage('P', array(54, 85));
// Insertar imagen de fondo para el frente
$pdf->Image($template_front, 0, 0, 54, 85);

// Procesar foto autoritativa desde el repositorio SIGE con fallbacks locales
$foto_jpg = null;
$sigeStorageRoot = getenv('SIGE_PERSONAL_PHOTO_STORAGE_ROOT') ?: 'C:/xampp/sige-storage/personas/fotos';

try {
    $fotoRow = $db->row(
        "SELECT pf.ruta_relativa 
         FROM sige_db.persona_fotos pf 
         JOIN sige_db.personas p ON pf.persona_id = p.id 
         WHERE p.persona_uuid = :uuid LIMIT 1",
        array(':uuid' => $uuid)
    );
    if (!empty($fotoRow['ruta_relativa'])) {
        $candidataSige = rtrim($sigeStorageRoot, '/\\') . '/' . $fotoRow['ruta_relativa'];
        if (file_exists($candidataSige)) {
            $foto_jpg = $candidataSige;
        }
    }
} catch (Exception $e) {
    error_log("Error buscando foto en SIGE: " . $e->getMessage());
}

if (!$foto_jpg || !file_exists($foto_jpg)) {
    if (file_exists("assets/fotoperfil/p_" . $uuid . ".jpg")) {
        $foto_jpg = "assets/fotoperfil/p_" . $uuid . ".jpg";
    } elseif (file_exists("assets/fotoperfil/" . $persona['numero_documento'] . ".jpg")) {
        $foto_jpg = "assets/fotoperfil/" . $persona['numero_documento'] . ".jpg";
    } elseif (file_exists("assets/images/fotoperfil/" . $persona['numero_documento'] . ".jpg")) {
        $foto_jpg = "assets/images/fotoperfil/" . $persona['numero_documento'] . ".jpg";
    }
}

if ($foto_jpg && file_exists($foto_jpg)) {
    $tmpDir = sys_get_temp_dir();
    $foto_png = $tmpDir . "/tmp_carnet_" . preg_replace('/[^a-zA-Z0-9_-]/', '', $uuid) . ".png";
    if (cropCircularImage($foto_jpg, $foto_png, 300)) {
        // Coordenadas ajustadas para el nuevo círculo
        $pdf->Image($foto_png, 15, 17, 24, 24);
        if (file_exists($foto_png)) {
            unlink($foto_png);
        }
    }
}

// Textos Frente
$nombres = utf8_decode(strtoupper($persona['nombres']));
$apellidos = utf8_decode(strtoupper($persona['apellidos']));

// Nombres
$pdf->SetTextColor(25, 45, 95); 
$pdf->SetXY(0, 47);
$fs = 10; $pdf->SetFont('Arial', 'B', $fs);
while($pdf->GetStringWidth($nombres) > 50 && $fs > 6) { $fs -= 0.5; $pdf->SetFont('Arial', 'B', $fs); }
$pdf->Cell(54, 4, $nombres, 0, 1, 'C');

// Apellidos
$pdf->SetTextColor(0, 153, 204);
$pdf->SetXY(0, 51);
$fs = 10; $pdf->SetFont('Arial', 'B', $fs);
while($pdf->GetStringWidth($apellidos) > 50 && $fs > 6) { $fs -= 0.5; $pdf->SetFont('Arial', 'B', $fs); }
$pdf->Cell(54, 4, $apellidos, 0, 1, 'C');

// ID (Dentro de la píldora)
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(25, 45, 95);
$pdf->SetXY(0, 58.2);
$pdf->Cell(54, 4, utf8_decode('ID: ') . $persona['numero_documento'], 0, 1, 'C');

// RH
$rh_val = trim(str_ireplace('RH', '', $persona['rh'] ?? 'N/A'));
$pdf->SetXY(0, 65.5);
$pdf->Cell(54, 4, 'RH: ' . $rh_val, 0, 1, 'C');

// Cargo (En la franja inferior blanca, o azul dependiendo del template)
$pdf->SetFont('Arial', 'B', 6);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(2, 77.5); 
$pdf->MultiCell(50, 3, utf8_decode(strtoupper($cargo_nombre)), 0, 'C');

// REVERSO
$pdf->AddPage('P', array(54, 85));
$pdf->Image($template_back, 0, 0, 54, 85);

$pdf->SetFont('Arial', '', 6);
$pdf->SetTextColor(150, 150, 150);
$pdf->SetXY(0, 80);
$pdf->Cell(54, 3, substr($persona['carnet_uuid'] ?? $persona['persona_uuid'], 0, 8), 0, 1, 'C');

$pdf->Output('I', 'Carnet_' . $persona['numero_documento'] . '.pdf');
?>
