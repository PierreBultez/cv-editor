<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Cv;
use App\Services\PhotoProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CvPhotoController extends Controller
{
    public function __construct(private readonly PhotoProcessor $photos) {}

    public function store(Request $request, Cv $cv): JsonResponse
    {
        // Le client envoie deja un carre de 1024 px maximum : la limite de 8 Mo
        // n'est la que pour le cas ou l'appel arrive hors de l'interface.
        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp,avif', 'max:8192', 'dimensions:max_width=6000,max_height=6000'],
        ], [
            'photo.required' => 'Aucune image reçue.',
            'photo.image' => "Le fichier envoyé n'est pas une image.",
            'photo.mimes' => 'Formats acceptés : JPEG, PNG, WebP et AVIF.',
            'photo.max' => "L'image ne doit pas dépasser 8 Mo.",
            'photo.dimensions' => "L'image ne doit pas dépasser 6000 px de côté.",
            // Declenche quand PHP lui-meme n'a pas pu receptionner le fichier
            // (repertoire temporaire absent, upload_max_filesize depasse).
            'photo.uploaded' => "Le serveur n'a pas pu réceptionner l'image. Vérifiez sa taille, ou réessayez.",
        ]);

        $variants = $this->photos->process($validated['photo']->getRealPath(), $cv->photoDirectory());

        $cv->forceFill([
            'photo_variants' => $variants,
            'last_seen_at' => now(),
        ])->save();

        $disk = Storage::disk('public');

        return response()->json([
            'photo' => collect($variants)
                ->map(fn ($value, $key) => in_array($key, ['width', 'height'], true) ? $value : $disk->url($value))
                ->all(),
        ]);
    }

    public function destroy(Cv $cv): JsonResponse
    {
        $this->photos->delete($cv->photoDirectory());

        $cv->forceFill(['photo_variants' => null])->save();

        return response()->json(['photo' => null]);
    }
}
