<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Cv;

/**
 * Metadonnees de partage (Open Graph et Twitter Card).
 *
 * Ce que Facebook, LinkedIn, WhatsApp ou X affichent quand on colle un lien.
 * L'image porte l'identite ; le titre et la description portent le message,
 * puisque ces plateformes les affichent en clair a cote de la vignette.
 */
final class SocialCard
{
    public const IMAGE = '/og-image.png';

    /**
     * URL de l'image, suffixee de la date du fichier.
     *
     * Facebook, LinkedIn et X conservent longtemps la vignette associee a une
     * URL. Remplacer le fichier sans changer son adresse laisserait donc
     * l'ancienne image circuler pendant des semaines ; l'empreinte fait de
     * chaque version une adresse distincte.
     */
    public static function imageUrl(): string
    {
        $path = public_path(ltrim(self::IMAGE, '/'));
        $version = is_file($path) ? filemtime($path) : null;

        return url(self::IMAGE).($version ? "?v={$version}" : '');
    }

    /** @return array<string, string|bool> */
    public static function landing(): array
    {
        return [
            'ogTitle' => 'Civi — Faites votre CV, pas votre mise en page.',
            'ogDescription' => "Remplissez vos expériences, Civi s'occupe de la mise en page. Aperçu A4 en temps réel, PDF prêt à envoyer. Gratuit, sans compte, sans filigrane.",
            'ogType' => 'website',
            // La seule page du site qui doit etre referencee : elle ne porte
            // aucune donnee personnelle et c'est par elle qu'on arrive.
            'allowIndexing' => true,
        ];
    }

    /**
     * Carte d'un CV public.
     *
     * Le nom et l'intitule ne sont exposes que si l'auteur a autorise
     * l'indexation. Refuser d'etre reference dans un moteur de recherche puis
     * voir son nom s'afficher dans chaque apercu de lien partage serait
     * contradictoire — les robots des reseaux sociaux moissonnent ces pages
     * exactement comme les moteurs, et gardent leurs vignettes en cache.
     *
     * @return array<string, string>
     */
    public static function forCv(Cv $cv): array
    {
        if (! $cv->allow_indexing) {
            return [
                'ogTitle' => 'Un CV créé avec Civi',
                'ogDescription' => 'Faites votre CV, pas votre mise en page. Gratuit, sans compte.',
                'ogType' => 'profile',
            ];
        }

        $identity = $cv->content['identity'] ?? [];
        $name = trim((string) ($identity['fullName'] ?? '')) ?: 'CV';
        $jobTitle = trim((string) ($identity['jobTitle'] ?? ''));

        return [
            'ogTitle' => $jobTitle !== '' ? "{$name} — {$jobTitle}" : $name,
            'ogDescription' => trim((string) ($identity['tagline'] ?? ''))
                ?: "Le CV de {$name}, mis en page avec Civi.",
            'ogType' => 'profile',
        ];
    }
}
