<?php

declare(strict_types=1);

use App\Models\Cv;
use App\Support\CvDefaults;

/**
 * Le jeton d'edition est le seul controle d'acces en ecriture : il n'y a ni
 * comptes ni sessions authentifiees. Ces tests verrouillent ce comportement.
 */
function validPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'template' => 'classic',
        'theme' => CvDefaults::theme(),
        'fonts' => CvDefaults::fonts(),
        'is_public' => true,
        'allow_indexing' => false,
        'content' => CvDefaults::content(),
    ], $overrides);
}

it('cree un CV anonyme et redirige vers son editeur', function () {
    $response = $this->post('/cv');

    $cv = Cv::sole();

    $response->assertRedirect("/cv/{$cv->public_id}/edit");
    expect($cv->public_id)->toHaveLength(26)
        ->and($cv->edit_token)->toHaveLength(64)
        ->and($cv->content['schema_version'])->toBe(CvDefaults::SCHEMA_VERSION);
});

it('remet le jeton en clair une seule fois, via la session', function () {
    $this->post('/cv')->assertSessionHas('editToken');

    $plain = session('editToken');
    $cv = Cv::sole();

    // Seul le hachage est conserve : le jeton en clair n'est nulle part en base.
    expect($cv->edit_token)->not->toBe($plain)
        ->and($cv->tokenMatches($plain))->toBeTrue();
});

it('refuse une ecriture sans jeton', function () {
    $cv = Cv::createAnonymous()[0];

    $this->patchJson("/cv/{$cv->public_id}", validPayload())->assertForbidden();
});

it('refuse une ecriture avec un mauvais jeton', function () {
    $cv = Cv::createAnonymous()[0];

    $this->withHeader('X-Cv-Token', str_repeat('a', 48))
        ->patchJson("/cv/{$cv->public_id}", validPayload())
        ->assertForbidden();
});

it('accepte une ecriture avec le bon jeton', function () {
    [$cv, $token] = Cv::createAnonymous();

    $this->withHeader('X-Cv-Token', $token)
        ->patchJson("/cv/{$cv->public_id}", validPayload([
            'content' => ['identity' => ['fullName' => 'Camille Durand']],
        ]))
        ->assertOk();

    expect($cv->fresh()->content['identity']['fullName'])->toBe('Camille Durand');
});

it('laisse consulter la page publique sans jeton', function () {
    $cv = Cv::createAnonymous()[0];

    $this->get("/cv/{$cv->public_id}")->assertOk();
});

it('cache la page publique quand le CV est prive', function () {
    [$cv, $token] = Cv::createAnonymous();

    $this->withHeader('X-Cv-Token', $token)
        ->patchJson("/cv/{$cv->public_id}", validPayload(['is_public' => false]))
        ->assertOk();

    $this->get("/cv/{$cv->public_id}")->assertNotFound();
});

it('supprime le CV avec le bon jeton', function () {
    [$cv, $token] = Cv::createAnonymous();

    $this->withHeader('X-Cv-Token', $token)
        ->delete("/cv/{$cv->public_id}")
        ->assertRedirect('/');

    expect(Cv::count())->toBe(0);
});
