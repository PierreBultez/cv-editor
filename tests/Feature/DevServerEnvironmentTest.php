<?php

declare(strict_types=1);

use Illuminate\Foundation\Console\ServeCommand;

/**
 * `artisan serve` ne transmet au processus `php -S` qu'une liste blanche de
 * variables d'environnement. Sous Windows, retirer TMP et TEMP prive PHP de
 * repertoire temporaire : tout upload de photo echoue alors des le demarrage
 * de la requete, avec un message qui ne designe pas la cause.
 *
 * Le correctif tient en trois lignes dans AppServiceProvider et se perdrait
 * sans bruit lors d'un nettoyage ; ce test le retient.
 */
it('transmet le répertoire temporaire au serveur de développement', function () {
    expect(ServeCommand::$passthroughVariables)
        ->toContain('TMP')
        ->toContain('TEMP');
});
