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
