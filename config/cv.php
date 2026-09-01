<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pilote de traitement d'image
    |--------------------------------------------------------------------------
    |
    | « gd » ou « imagick ». Les deux savent produire du JPEG et du WebP ; l'AVIF
    | depend de la compilation de l'hote — libavif pour GD, libheif pour Imagick.
    | Quand GD ne sait pas encoder l'AVIF alors qu'Imagick est disponible,
    | basculer ici suffit a retrouver la variante.
    |
    | PhotoProcessor detecte de toute facon le support a l'execution : une
    | absence d'AVIF n'empeche jamais l'envoi d'une photo.
    |
    */

    'image_driver' => env('CV_IMAGE_DRIVER', 'gd'),

    /*
    |--------------------------------------------------------------------------
    | Duree de conservation
    |--------------------------------------------------------------------------
    |
    | Le service est anonyme : personne ne peut revenir supprimer son CV des
    | annees plus tard. Passe ce delai sans consultation ni modification, la
    | commande `cv:purge` supprime le CV et sa photo.
    |
    */

    'retention_months' => (int) env('CV_RETENTION_MONTHS', 12),

];
