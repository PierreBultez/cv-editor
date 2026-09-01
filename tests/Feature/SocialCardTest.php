<?php

declare(strict_types=1);

use App\Models\Cv;
use App\Support\SocialCard;

/**
 * Ce que Facebook, LinkedIn ou X affichent quand on colle un lien.
 *
 * Le point sensible n'est pas l'esthetique : les robots de ces plateformes
 * moissonnent les pages comme les moteurs de recherche et gardent leurs
 * vignettes en cache. Exposer le nom d'une personne qui a refuse l'indexation
 * reviendrait a contourner son choix par une autre porte.
 */
it('rend la page d accueil référençable et décrite', function () {
    $this->get('/')
        ->assertOk()
        ->assertDontSee('noindex', escape: false)
        ->assertSee('og:title', escape: false)
        ->assertSee('Faites votre CV, pas votre mise en page.', escape: false)
        ->assertSee('summary_large_image', escape: false);
});

it('tait le nom sur un CV qui refuse l indexation', function () {
    [$cv] = Cv::createAnonymous();

    $content = $cv->content;
    $content['identity']['fullName'] = 'Camille Moreau';
    $content['identity']['jobTitle'] = 'Chargée de communication';
    $cv->forceFill(['content' => $content, 'allow_indexing' => false])->save();

    $response = $this->get("/cv/{$cv->public_id}");

    $response->assertOk()
        ->assertSee('noindex', escape: false)
        ->assertSee('Un CV créé avec Civi', escape: false);

    // Le nom ne doit apparaitre ni en titre de partage, ni en description.
    expect($response->getContent())->not->toContain('og:title" content="Camille Moreau');
});

it('expose le nom quand l auteur a autorisé l indexation', function () {
    [$cv] = Cv::createAnonymous();

    $content = $cv->content;
    $content['identity']['fullName'] = 'Camille Moreau';
    $content['identity']['jobTitle'] = 'Chargée de communication';
    $cv->forceFill(['content' => $content, 'allow_indexing' => true])->save();

    $this->get("/cv/{$cv->public_id}")
        ->assertOk()
        ->assertDontSee('noindex', escape: false)
        ->assertSee('Camille Moreau — Chargée de communication', escape: false);
});

it('sert une image de partage aux bonnes dimensions', function () {
    $this->get('/')
        ->assertSee('og:image:width" content="1200', escape: false)
        ->assertSee('og:image:height" content="630', escape: false);

    $path = public_path(ltrim(SocialCard::IMAGE, '/'));

    expect($path)->toBeReadableFile();

    // Les plateformes rognent une image d'un autre ratio, chacune a sa maniere.
    [$width, $height, $type] = getimagesize($path);
    expect([$width, $height])->toBe([1200, 630]);

    // JPEG ou PNG : les robots sociaux ne lisent pas WebP de facon fiable.
    expect($type)->toBeIn([IMAGETYPE_JPEG, IMAGETYPE_PNG]);
});
