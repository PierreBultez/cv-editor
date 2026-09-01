<?php

declare(strict_types=1);

/**
 * Fabrique l'image de partage a partir de assets/branding/og-source.png.
 *
 *     php deploy/og-image.php
 *
 * Pourquoi ni WebP ni AVIF : la balise og:image ne designe qu'une seule URL,
 * sans negociation de format. Or les robots de Facebook, LinkedIn et X lisent
 * JPEG et PNG de facon fiable, WebP inegalement, AVIF pas du tout. Le gain de
 * poids ne vaut rien si l'apercu disparait.
 *
 * Le script produit les deux candidats serieux et affiche leur poids : au PNG
 * quantifie de conserver les aplats et le texte fin, au JPEG d'etre plus leger.
 * On garde celui qui tient la promesse a l'oeil pour le moins d'octets.
 */
const SOURCE = 'assets/branding/og-source.png';
const WIDTH = 1200;
const HEIGHT = 630;

if (! file_exists(SOURCE)) {
    fwrite(STDERR, 'Source absente : '.SOURCE."\n");
    exit(1);
}

[$sw, $sh] = getimagesize(SOURCE);
printf("Source : %dx%d, %d Ko\n\n", $sw, $sh, filesize(SOURCE) / 1024);

$source = imagecreatefrompng(SOURCE);

/*
 * Recadrage centre au ratio 1.91:1 attendu par Open Graph. Une image d'un autre
 * ratio serait rognee par les plateformes, chacune a sa maniere : autant
 * decider nous-memes ce qui disparait.
 */
$targetRatio = WIDTH / HEIGHT;
$sourceRatio = $sw / $sh;

if (abs($sourceRatio - $targetRatio) < 0.01) {
    [$cx, $cy, $cw, $ch] = [0, 0, $sw, $sh];
} elseif ($sourceRatio > $targetRatio) {
    $cw = (int) round($sh * $targetRatio);
    [$cx, $cy, $ch] = [(int) round(($sw - $cw) / 2), 0, $sh];
} else {
    $ch = (int) round($sw / $targetRatio);
    [$cx, $cy, $cw] = [0, (int) round(($sh - $ch) / 2), $sw];
}

$canvas = imagecreatetruecolor(WIDTH, HEIGHT);

// Fond ecru : une source transparente ne doit pas virer au noir en JPEG.
imagefilledrectangle($canvas, 0, 0, WIDTH, HEIGHT, imagecolorallocate($canvas, 0xF7, 0xF7, 0xFB));
imagecopyresampled($canvas, $source, 0, 0, $cx, $cy, WIDTH, HEIGHT, $cw, $ch);

$results = [];

// JPEG : le plus leger, au prix d'artefacts sur le texte fin de la maquette.
foreach ([82, 88] as $quality) {
    $path = "public/og-image-q{$quality}.jpg";
    imagejpeg($canvas, $path, $quality);
    $results["JPEG q{$quality}"] = $path;
}

// PNG quantifie : l'image est faite d'aplats, une palette reduite y coute peu.
foreach ([256, 128] as $colors) {
    $copy = imagecreatetruecolor(WIDTH, HEIGHT);
    imagecopy($copy, $canvas, 0, 0, 0, 0, WIDTH, HEIGHT);
    imagetruecolortopalette($copy, true, $colors);
    $path = "public/og-image-{$colors}c.png";
    imagepng($copy, $path, 9);
    imagedestroy($copy);
    $results["PNG {$colors} couleurs"] = $path;
}

// PNG pleine profondeur, pour mesurer ce que la quantification fait gagner.
imagepng($canvas, 'public/og-image-full.png', 9);
$results['PNG 24 bits'] = 'public/og-image-full.png';

printf("%-22s %10s\n", 'Candidat', 'Poids');
printf("%s\n", str_repeat('-', 34));

foreach ($results as $label => $path) {
    printf("%-22s %7d Ko\n", $label, filesize($path) / 1024);
}

imagedestroy($canvas);
imagedestroy($source);

echo "\nInspectez les fichiers, gardez le meilleur compromis sous public/og-image.png\n";
echo "ou .jpg, puis ajustez SocialCard::IMAGE si l'extension change.\n";
