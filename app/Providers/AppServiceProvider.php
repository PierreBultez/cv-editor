<?php

namespace App\Providers;

use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->allowFileUploadsUnderArtisanServe();
    }

    /**
     * `artisan serve` ne transmet au processus `php -S` qu'une liste blanche de
     * variables d'environnement. Sous Windows, `TMP` et `TEMP` n'en font pas
     * partie : PHP se retrouve sans repertoire temporaire et *tout* upload
     * echoue des le demarrage de la requete, avec un simple avertissement
     * « unable to create a temporary file » avant la reponse.
     *
     * Sans photo, l'outil perd une de ses fonctions principales en
     * developpement ; on complete donc la liste. Aucun effet en production, ou
     * le site est servi par nginx/FPM.
     */
    private function allowFileUploadsUnderArtisanServe(): void
    {
        if (! $this->app->runningInConsole() || ! class_exists(ServeCommand::class)) {
            return;
        }

        foreach (['TMP', 'TEMP'] as $variable) {
            if (! in_array($variable, ServeCommand::$passthroughVariables, true)) {
                ServeCommand::$passthroughVariables[] = $variable;
            }
        }
    }
}
