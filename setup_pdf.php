<?php
// Script para descargar y configurar FPDF y FPDI
$fpdf_url = "http://www.fpdf.org/en/dl.php?v=186&f=zip";
$fpdi_url = "https://github.com/Setasign/FPDI/archive/refs/tags/1.6.2.zip";

$plugins_dir = __DIR__ . "/plugins";
if (!file_exists($plugins_dir)) { mkdir($plugins_dir); }

// Descargar FPDF
file_put_contents("$plugins_dir/fpdf.zip", file_get_contents($fpdf_url));
$zip = new ZipArchive;
if ($zip->open("$plugins_dir/fpdf.zip") === TRUE) {
    $zip->extractTo("$plugins_dir/fpdf");
    $zip->close();
    echo "FPDF Extraido.\n";
}

// Descargar FPDI
file_put_contents("$plugins_dir/fpdi.zip", file_get_contents($fpdi_url));
if ($zip->open("$plugins_dir/fpdi.zip") === TRUE) {
    $zip->extractTo("$plugins_dir/fpdi");
    $zip->close();
    echo "FPDI Extraido.\n";
}

unlink("$plugins_dir/fpdf.zip");
unlink("$plugins_dir/fpdi.zip");
echo "Terminado.";
?>
