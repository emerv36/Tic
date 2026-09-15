<?php
$im = imagecreatefrompng('assets/pdf/frente_1.png');
// The image is 1004 x 638 pixels (aprox, since 85x54 at 300dpi is 1004x638)
$width = imagesx($im);
$height = imagesy($im);
// Let's sample a pixel in the bottom left area where the program name is
// roughly 15% from left, 85% from top
$x = intval($width * 0.15);
$y = intval($height * 0.85);

$rgb = imagecolorat($im, $x, $y);
$colors = imagecolorsforindex($im, $rgb);
echo "Color at ($x, $y): R=" . $colors['red'] . " G=" . $colors['green'] . " B=" . $colors['blue'] . "\n";
?>
