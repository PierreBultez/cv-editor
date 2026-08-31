<?php

declare(strict_types=1);

use App\Models\Cv;
use App\Support\CvDefaults;

/**
 * `content` est une colonne JSON : la base n'impose aucune forme. UpdateCvRequest
 * est donc le seul rempart, a la fois contre les donnees incoherentes et contre
 * le stockage abusif sur un service ouvert sans compte.
 */
beforeEach(function () {
    [$this->cv, $this->token] = Cv::createAnonymous();

    $this->patchCv = function (array $overrides = []) {
        return $this->withHeader('X-Cv-Token', $this->token)
            ->patchJson("/cv/{$this->cv->public_id}", array_replace([
                'template' => 'classic',
                'theme' => CvDefaults::theme(),
                'fonts' => CvDefaults::fonts(),
                'is_public' => true,
                'allow_indexing' => false,
                'content' => CvDefaults::content(),
            ], $overrides));
    };
});

/** Remplace les items de la premiere section d'un type donne. */
function withItems(string $type, array $items): array
{
    $content = CvDefaults::content();

    foreach ($content['sections'] as $index => $section) {
        if ($section['type'] === $type) {
            $content['sections'][$index]['items'] = $items;
            break;
        }
    }

    return $content;
}

it('accepte les chaines vides du CV en cours de redaction', function () {
    // Sans l'exception a ConvertEmptyStringsToNull, ces "" arrivent en null
    // cote serveur et sont rejetes comme non-string.
    ($this->patchCv)()->assertOk();
});

it('rejette un template inconnu', function () {
    ($this->patchCv)(['template' => 'nexistepas'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('template');
});

it('rejette une couleur qui n est pas un hexadecimal', function () {
    ($this->patchCv)(['theme' => ['primary' => 'rouge', 'accent' => '#245ea9']])
        ->assertStatus(422)
        ->assertJsonValidationErrors('theme.primary');
});

it('rejette une police hors catalogue', function () {
    ($this->patchCv)(['fonts' => ['title' => 'comic-sans', 'body' => 'satoshi']])
        ->assertStatus(422)
        ->assertJsonValidationErrors('fonts.title');
});

it('rejette un niveau de competence hors bornes', function () {
    ($this->patchCv)(['content' => withItems('skills', [['label' => 'PHP', 'level' => 320]])])
        ->assertStatus(422);
});

/**
 * USlider renvoie un tableau a un element : sans normalisation cote client, le
 * niveau partait sous la forme `[55]` et toute sauvegarde echouait des qu'un
 * curseur etait touche. La normalisation vit dans SectionItemsEditor ; ce test
 * fige la contrepartie serveur, pour que le relachement de la regle ne puisse
 * pas masquer un retour du bug.
 */
it('rejette un niveau envoyé sous forme de tableau', function () {
    ($this->patchCv)(['content' => withItems('skills', [['label' => 'PHP', 'level' => [55]]])])
        ->assertStatus(422);

    ($this->patchCv)(['content' => withItems('languages', [['label' => 'Anglais', 'mention' => 'B1', 'level' => [3]]])])
        ->assertStatus(422);
});

it('rejette une section depassant la limite d items', function () {
    $items = array_fill(0, CvDefaults::MAX_ITEMS_PER_SECTION + 5, 'Marche');

    ($this->patchCv)(['content' => withItems('interests', $items)])
        ->assertStatus(422);
});

it('accepte une experience complete', function () {
    $content = withItems('experiences', [[
        'period' => '2020 — 2022',
        'role' => 'Développeuse',
        'company' => 'Acme',
        'location' => 'Lyon (69)',
        'bullets' => ['Conception', 'Développement'],
    ]]);

    ($this->patchCv)(['content' => $content])->assertOk();

    expect($this->cv->fresh()->content['sections'])
        ->toContain(...array_filter($content['sections'], fn ($s) => $s['type'] === 'experiences'));
});
