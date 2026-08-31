<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\AvifEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\DriverInterface;
use Intervention\Image\Interfaces\EncoderInterface;
use Intervention\Image\Interfaces\ImageInterface;
use Throwable;

/**
 * Recadre la photo en carre et produit les variantes servies par le
 * `<picture>` du template.
 *
 * JPEG est toujours ecrit : c'est le repli garanti. WebP l'est en pratique
 * partout. AVIF depend de la compilation de GD avec libavif sur l'hote, on ne
 * l'ecrit donc que si l'encodeur repond, sans jamais faire echouer l'upload.
 */
final class PhotoProcessor
{
    private const SIZE = 512;

    private const THUMB_SIZE = 256;

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(self::driver());
    }

    private static function driver(): DriverInterface
    {
        return self::usesImagick() ? new ImagickDriver : new GdDriver;
    }

    /**
     * Pilote reellement utilise, et non celui demande : une configuration
     * pointant vers une extension absente doit priver d'AVIF, jamais mettre
     * l'envoi de photo hors service.
     */
    private static function usesImagick(): bool
    {
        if (config('cv.image_driver') !== 'imagick') {
            return false;
        }

        if (extension_loaded('imagick')) {
            return true;
        }

        Log::warning("Pilote d'image « imagick » demandé mais l'extension est absente : repli sur GD.");

        return false;
    }

    /**
     * Prend un chemin plutot qu'un UploadedFile : le seeder de demonstration
     * alimente le meme pipeline depuis un fichier du depot.
     *
     * @return array{jpg: string, webp?: string, avif?: string, thumb: string, width: int, height: int}
     */
    public function process(string $sourcePath, string $directory): array
    {
        $disk = Storage::disk('public');

        // Une seule photo par CV : on repart d'un dossier propre pour ne pas
        // laisser trainer les variantes de l'image precedente.
        $disk->deleteDirectory($directory);

        $image = $this->manager->decodePath($sourcePath);
        $image->cover(self::SIZE, self::SIZE);

        // `cover()` modifie l'image en place et un clone PHP partagerait la
        // meme ressource GD : la miniature est donc decodee separement.
        $thumb = $this->manager->decodePath($sourcePath)
            ->cover(self::THUMB_SIZE, self::THUMB_SIZE);

        $variants = [
            'jpg' => $this->write($disk, $image, "{$directory}/photo.jpg", new JpegEncoder(quality: 86, strip: true)),
            'thumb' => $this->write($disk, $thumb, "{$directory}/photo-256.jpg", new JpegEncoder(quality: 82, strip: true)),
            'width' => self::SIZE,
            'height' => self::SIZE,
        ];

        if ($webp = $this->tryWrite($disk, $image, "{$directory}/photo.webp", new WebpEncoder(quality: 82, strip: true))) {
            $variants['webp'] = $webp;
        }

        if (self::supportsAvif()
            && $avif = $this->tryWrite($disk, $image, "{$directory}/photo.avif", new AvifEncoder(quality: 55, strip: true))) {
            $variants['avif'] = $avif;
        }

        return $variants;
    }

    public function delete(string $directory): void
    {
        Storage::disk('public')->deleteDirectory($directory);
    }

    /**
     * L'AVIF depend de la compilation de l'hote, pas de la version de PHP : GD
     * doit avoir ete bati avec libavif, Imagick avec libheif. On interroge donc
     * le pilote reellement configure.
     */
    public static function supportsAvif(): bool
    {
        if (self::usesImagick()) {
            return in_array('AVIF', \Imagick::queryFormats('AVIF'), true);
        }

        return function_exists('imageavif');
    }

    private function write(Filesystem $disk, ImageInterface $image, string $path, EncoderInterface $encoder): string
    {
        $disk->put($path, $image->encode($encoder)->toString());

        return $path;
    }

    /** Comme write(), mais un format optionnel absent ne doit pas casser l'upload. */
    private function tryWrite(Filesystem $disk, ImageInterface $image, string $path, EncoderInterface $encoder): ?string
    {
        try {
            return $this->write($disk, $image, $path, $encoder);
        } catch (Throwable $e) {
            Log::warning('Encodage photo ignoré', ['path' => $path, 'message' => $e->getMessage()]);

            return null;
        }
    }
}
