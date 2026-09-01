<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Cv;
use App\Services\PhotoProcessor;
use App\Support\CvDefaults;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * CV de demonstration, entierement fictif.
 *
 * Il alimente le lien « Voir un exemple » de la page d'accueil et sert de
 * reference visuelle pour verifier le rendu des templates.
 *
 * Le jeton d'edition est tire au hasard a chaque passage et affiche une seule
 * fois : un jeton ecrit en clair dans le depot aurait laisse n'importe qui
 * modifier l'exemple en production.
 */
class DemoCvSeeder extends Seeder
{
    /** Identifiant fixe pour que l'URL de demonstration reste stable. */
    public const PUBLIC_ID = '01K0DEMXCV0000000000000000';

    public function __construct(private readonly PhotoProcessor $photos) {}

    public function run(): void
    {
        $content = json_decode(
            File::get(database_path('seeders/data/demo-cv.json')),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        Cv::where('public_id', self::PUBLIC_ID)->delete();

        $plainToken = Str::random(48);

        $cv = new Cv;
        $cv->forceFill([
            'public_id' => self::PUBLIC_ID,
            'edit_token' => Cv::hashToken($plainToken),
            'template' => 'classic',
            'theme' => CvDefaults::theme(),
            'fonts' => ['title' => 'satoshi', 'body' => 'satoshi'],
            'content' => $content,
            'is_public' => true,
            'allow_indexing' => false,
            'last_seen_at' => now(),
        ])->save();

        $source = database_path('seeders/assets/demo-photo.jpg');

        if (File::exists($source)) {
            $cv->forceFill([
                'photo_variants' => $this->photos->process($source, $cv->photoDirectory()),
            ])->save();
        }

        $this->command?->info('CV de démonstration : /cv/'.self::PUBLIC_ID);
        $this->command?->line('Lien de modification (affiché une seule fois, conservez-le si besoin) :');
        $this->command?->line('  /cv/'.self::PUBLIC_ID.'/edit#t='.$plainToken);
    }
}
