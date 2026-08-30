<?php

use Illuminate\Support\Facades\Schedule;

/**
 * Politique de conservation : sans comptes, personne ne peut venir supprimer
 * son CV des annees plus tard. La purge tient donc lieu de garde-fou.
 */
Schedule::command('cv:purge')->weeklyOn(1, '03:00');
