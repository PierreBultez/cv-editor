<?php

declare(strict_types=1);

use App\Models\Cv;
use App\Services\PhotoProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    [$this->cv, $this->token] = Cv::createAnonymous();
});

it('produit les variantes a partir d un upload', function () {
    $response = $this->withHeader('X-Cv-Token', $this->token)
        ->postJson("/cv/{$this->cv->public_id}/photo", [
            'photo' => UploadedFile::fake()->image('portrait.jpg', 900, 1200),
        ]);

    $response->assertOk();

    $variants = $this->cv->fresh()->photo_variants;

    // JPEG est le repli garanti, la miniature sert la liste « Mes CV ».
    expect($variants)->toHaveKeys(['jpg', 'thumb', 'width', 'height'])
        ->and($variants['width'])->toBe(512);

    Storage::disk('public')->assertExists($variants['jpg']);
    Storage::disk('public')->assertExists($variants['thumb']);
});

it('n ecrit la variante avif que si l hote sait l encoder', function () {
    $this->withHeader('X-Cv-Token', $this->token)
        ->postJson("/cv/{$this->cv->public_id}/photo", [
            'photo' => UploadedFile::fake()->image('portrait.jpg', 800, 800),
        ])->assertOk();

    $variants = $this->cv->fresh()->photo_variants;

    // Le pipeline ne doit jamais echouer faute d'AVIF : la cle est simplement
    // absente quand GD n'a pas ete compile avec libavif.
    expect(array_key_exists('avif', $variants))->toBe(PhotoProcessor::supportsAvif());
});

it('refuse un fichier qui n est pas une image', function () {
    $this->withHeader('X-Cv-Token', $this->token)
        ->postJson("/cv/{$this->cv->public_id}/photo", [
            'photo' => UploadedFile::fake()->create('cv.pdf', 40, 'application/pdf'),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('photo');
});

it('refuse un upload sans jeton', function () {
    $this->postJson("/cv/{$this->cv->public_id}/photo", [
        'photo' => UploadedFile::fake()->image('portrait.jpg'),
    ])->assertForbidden();
});

it('supprime la photo et ses fichiers', function () {
    $this->withHeader('X-Cv-Token', $this->token)
        ->postJson("/cv/{$this->cv->public_id}/photo", [
            'photo' => UploadedFile::fake()->image('portrait.jpg'),
        ])->assertOk();

    $jpg = $this->cv->fresh()->photo_variants['jpg'];

    $this->withHeader('X-Cv-Token', $this->token)
        ->deleteJson("/cv/{$this->cv->public_id}/photo")
        ->assertOk();

    expect($this->cv->fresh()->photo_variants)->toBeNull();
    Storage::disk('public')->assertMissing($jpg);
});
