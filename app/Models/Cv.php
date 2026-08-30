<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\CvDefaults;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cv extends Model
{
    protected $fillable = [
        'template',
        'theme',
        'fonts',
        'content',
        'photo_variants',
        'is_public',
        'allow_indexing',
    ];

    protected $hidden = ['edit_token'];

    protected function casts(): array
    {
        return [
            'theme' => 'array',
            'fonts' => 'array',
            'content' => 'array',
            'photo_variants' => 'array',
            'is_public' => 'boolean',
            'allow_indexing' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * La cle primaire reste un entier auto-incremente ; `public_id` est un
     * identifiant public non devinable, genere ici plutot que via HasUlids qui
     * basculerait toute la cle primaire en chaine.
     */
    protected static function booted(): void
    {
        static::creating(function (self $cv): void {
            $cv->public_id ??= (string) Str::ulid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * Cree un CV vierge et retourne le jeton d'edition en clair.
     *
     * Le jeton n'est visible qu'ici : seul son hachage est conserve. C'est au
     * client de le stocker (localStorage), il n'existe aucun moyen de le
     * retrouver ensuite.
     *
     * @return array{0: self, 1: string}
     */
    public static function createAnonymous(): array
    {
        $plainToken = Str::random(48);

        $cv = new self();
        $cv->forceFill([
            'template' => 'classic',
            'theme' => CvDefaults::theme(),
            'fonts' => CvDefaults::fonts(),
            'content' => CvDefaults::content(),
            'is_public' => true,
            'allow_indexing' => false,
            'edit_token' => self::hashToken($plainToken),
            'last_seen_at' => now(),
        ])->save();

        return [$cv, $plainToken];
    }

    /**
     * SHA-256 et non bcrypt : le jeton fait 48 caracteres aleatoires, son
     * entropie rend une attaque par force brute sans objet. Un hachage lent
     * penaliserait chaque sauvegarde automatique (une toutes les 700 ms).
     */
    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    public function tokenMatches(string $plainToken): bool
    {
        return $plainToken !== ''
            && hash_equals($this->edit_token, self::hashToken($plainToken));
    }

    public function touchLastSeen(): void
    {
        $this->forceFill(['last_seen_at' => now()])->saveQuietly();
    }

    /** Dossier de stockage des photos, sur le disque `public`. */
    public function photoDirectory(): string
    {
        return 'cv-photos/'.$this->public_id;
    }
}
