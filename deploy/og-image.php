<?php

declare(strict_types=1);

/**
 * Fabrique l'image de partage a partir de assets/branding/og-image.png.
 *
 *     php deploy/og-image.php              produit public/og-image.jpg
 *     php deploy/og-image.php --compare    ecrit aussi les candidats ecartes
 *
 * Pourquoi ni WebP ni AVIF : la balise og:image ne designe qu'une seule URL,
 * sans negociation de format. Or les robots de Facebook, LinkedIn et X lisent
 * JPEG et PNG de facon fiable, WebP inegalement, AVIF pas du tout. Un gain de
 * poids ne vaut rien si l'apercu disparait.
 *
 * Pourquoi JPEG plutot que PNG quantifie, a poids egal (80 Ko) : le visuel
 * comporte de grands aplats violets et jaunes. La quantification de GD ne sait
 * pas representer ces teintes exactes dans une palette reduite et les dithere,
 * ce qui constelle les aplats d'un bruit tres visible. Le JPEG les rend nets,
 * et sur le texte fin de la maquette les deux se valent. Verifie a 3x sur une
 * zone de texte et sur une zone d'aplat avant de trancher.
 */
const SOURCE = 'assets/branding/og-image.png';
const OUTPUT = 'public/og-image.jpg';
const WIDTH = 1200;
const HEIGHT = 630;
const QUALITY = 82;

$compare = in_array('--compare', $argv, true);

if (! file_exists(SOURCE)) {
    fwrite(STDERR, 'Source absente : '.SOURCE."\n");
    exit(1);
}

[$sw, $sh] = getimagesize(SOURCE);
printf("Source : %dx%d, %d Ko\n", $sw, $sh, filesize(SOURCE) / 1024);

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

imagejpeg($canvas, OUTPUT, QUALITY);
printf("%s : %dx%d, %d Ko (qualite %d)\n", OUTPUT, WIDTH, HEIGHT, filesize(OUTPUT) / 1024, QUALITY);

if ($compare) {
    imagejpeg($canvas, 'public/og-image-q88.jpg', 88);

    foreach ([256, 128] as $colors) {
        $copy = imagecreatetruecolor(WIDTH, HEIGHT);
        imagecopy($copy, $canvas, 0, 0, 0, 0, WIDTH, HEIGHT);
        imagetruecolortopalette($copy, true, $colors);
        imagepng($copy, "public/og-image-{$colors}c.png", 9);
        imagedestroy($copy);
    }

    imagepng($canvas, 'public/og-image-full.png', 9);
    echo "Candidats ecrits a cote, a supprimer une fois compares.\n";
}

imagedestroy($canvas);
imagedestroy($source);
