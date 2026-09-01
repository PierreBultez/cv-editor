<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Reference unique des valeurs autorisees et du CV vierge.
 *
 * Toute evolution du schema de `content` passe par ici : les regles de
 * validation (UpdateCvRequest) et le front (resources/js/lib/*) s'y adossent.
 */
final class CvDefaults
{
    public const SCHEMA_VERSION = 1;

    /** Mises en page disponibles. */
    public const TEMPLATES = ['classic', 'compact'];

    /**
     * Alias des polices, alignes sur ceux declares dans vite.config.ts.
     * Chaque alias expose une variable CSS `--font-{alias}`.
     */
    public const FONTS = [
        'satoshi',
        'poppins',
        'inter',
        'outfit',
        'space-grotesk',
        'fraunces',
        'playfair',
        'lora',
    ];

    /**
     * Types de coordonnees, chacun associe a une icone dans le template.
     *
     * Ajouter un type impose de completer CONTACT_ICONS et CONTACT_LABELS dans
     * resources/js/lib/sections.ts, faute de quoi il s'affichera avec l'icone
     * par defaut et sans libelle dans l'editeur.
     */
    public const CONTACT_TYPES = [
        'email',
        'phone',
        'location',
        'website',
        'linkedin',
        'github',
        'gitlab',
        'malt',
        'linktree',
        'behance',
        'dribbble',
        'mastodon',
    ];

    /** Types de sections et forme de leurs items. */
    public const SECTION_TYPES = [
        'experiences',
        'education',
        'certifications',
        'skills',
        'languages',
        'tools',
        'interests',
    ];

    public const COLUMNS = ['sidebar', 'main'];

    /** Garde-fous de taille, pour eviter le stockage abusif sur un service anonyme. */
    public const MAX_SECTIONS = 12;

    public const MAX_ITEMS_PER_SECTION = 40;

    public const MAX_BULLETS_PER_ITEM = 10;

    public const MAX_CONTACTS = 10;

    public static function theme(): array
    {
        return [
            'primary' => '#174d94',
            'accent' => '#245ea9',
        ];
    }

    public static function fonts(): array
    {
        return [
            'title' => 'satoshi',
            'body' => 'satoshi',
        ];
    }

    /**
     * CV vierge propose a la creation : la structure complete est deja en place,
     * l'utilisateur n'a qu'a remplir. Les sections vides restent visibles dans
     * l'editeur mais ne sont pas rendues dans l'apercu tant qu'elles sont vides.
     */
    public static function content(): array
    {
        return [
            'schema_version' => self::SCHEMA_VERSION,
            'identity' => [
                'fullName' => '',
                'jobTitle' => '',
                'techLine' => '',
                'tagline' => '',
            ],
            'profile' => '',
            'contact' => [
                ['type' => 'email', 'value' => ''],
                ['type' => 'phone', 'value' => ''],
                ['type' => 'location', 'value' => ''],
            ],
            'sections' => [
                self::section('skills', 'Compétences', 'sidebar'),
                self::section('languages', 'Langues', 'sidebar'),
                self::section('tools', 'Logiciels / Outils', 'sidebar'),
                self::section('interests', 'Centres d\'intérêt', 'sidebar'),
                self::section('experiences', 'Expériences professionnelles', 'main'),
                self::section('education', 'Formation', 'main'),
                self::section('certifications', 'Certifications', 'main'),
            ],
        ];
    }

    private static function section(string $type, string $title, string $column): array
    {
        return [
            'id' => $type,
            'type' => $type,
            'title' => $title,
            'column' => $column,
            'enabled' => true,
            'items' => [],
        ];
    }
}
