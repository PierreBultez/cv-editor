<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `withoutVite()` neutralise la resolution du manifeste d'assets.
 *
 * Les tests exercent la couche HTTP et la charge utile Inertia, pas la chaine
 * de compilation : sans cela, ils exigeraient un `npm run build` prealable et
 * echoueraient sur tout clone frais — ce qui passait inapercu sur un poste ou
 * public/build existait deja.
 */
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(fn () => $this->withoutVite())
    ->in('Feature');
