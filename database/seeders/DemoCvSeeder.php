<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Cv;
use App\Services\PhotoProcessor;
use App\Support\CvDefaults;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * CV de demonstration, repris du CV statique d'origine (`legacy/index.html`).
 *
 * Il sert de reference visuelle pour verifier que le template Vue rend la meme
 * chose que la page HTML de depart, et de page publique d'exemple.
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

        $cv = new Cv;
        $cv->forceFill([
            'public_id' => self::PUBLIC_ID,
            'edit_token' => Cv::hashToken('demo-token-non-secret'),
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
    }
}
