<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCvRequest;
use App\Models\Cv;
use App\Services\PhotoProcessor;
use App\Support\CvDefaults;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CvController extends Controller
{
    public function __construct(private readonly PhotoProcessor $photos) {}

    public function landing(): Response
    {
        return Inertia::render('Landing', [
            'fonts' => CvDefaults::FONTS,
            'templates' => CvDefaults::TEMPLATES,
        ]);
    }

    /**
     * Cree un CV vierge. Le jeton d'edition en clair transite une seule fois,
     * par la session, jusqu'a l'editeur qui le range dans le localStorage.
     */
    public function store(): RedirectResponse
    {
        [$cv, $plainToken] = Cv::createAnonymous();

        return to_route('cv.edit', $cv)->with('editToken', $plainToken);
    }

    /**
     * L'editeur est servi a quiconque connait l'URL : `public_id` est un ULID
     * non devinable, et rien ne peut etre modifie sans le jeton. Le front bascule
     * en lecture seule s'il ne le trouve pas dans le localStorage.
     */
    public function edit(Cv $cv): Response
    {
        $cv->touchLastSeen();

        return Inertia::render('Editor', [
            'cv' => $this->present($cv),
            'issuedToken' => session('editToken'),
            'supportsAvif' => PhotoProcessor::supportsAvif(),
        ]);
    }

    /** Sauvegarde automatique : appelee en fetch, pas en visite Inertia. */
    public function update(UpdateCvRequest $request, Cv $cv): JsonResponse
    {
        $cv->fill($request->validated());
        $cv->last_seen_at = now();
        $cv->save();

        return response()->json([
            'saved_at' => $cv->updated_at?->toIso8601String(),
        ]);
    }

    public function show(Cv $cv): Response
    {
        abort_unless($cv->is_public, 404);

        $cv->touchLastSeen();

        return Inertia::render('PublicCv', [
            'cv' => $this->present($cv),
        ])->withViewData(['allowIndexing' => $cv->allow_indexing]);
    }

    public function destroy(Cv $cv): RedirectResponse
    {
        $this->photos->delete($cv->photoDirectory());
        $cv->delete();

        return to_route('landing')->with('status', 'Le CV a été supprimé définitivement.');
    }

    /** Forme envoyee au front. `edit_token` est masque par le modele. */
    private function present(Cv $cv): array
    {
        return [
            'public_id' => $cv->public_id,
            'template' => $cv->template,
            'theme' => $cv->theme,
            'fonts' => $cv->fonts,
            'content' => $cv->content,
            'photo' => $cv->photo_variants
                ? $this->photoUrls($cv->photo_variants)
                : null,
            'is_public' => $cv->is_public,
            'allow_indexing' => $cv->allow_indexing,
            'updated_at' => $cv->updated_at?->toIso8601String(),
        ];
    }

    private function photoUrls(array $variants): array
    {
        $disk = Storage::disk('public');

        return collect($variants)
            ->map(fn ($value, $key) => in_array($key, ['width', 'height'], true) ? $value : $disk->url($value))
            ->all();
    }
}
