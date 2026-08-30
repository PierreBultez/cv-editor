<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Cv;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Seul garde-fou en ecriture : il n'y a pas de comptes, l'autorisation repose
 * entierement sur la detention du jeton d'edition remis a la creation du CV.
 */
class EnsureCvEditToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $cv = $request->route('cv');

        $provided = (string) ($request->header('X-Cv-Token') ?? $request->input('edit_token', ''));

        abort_unless($cv instanceof Cv && $cv->tokenMatches($provided), 403, 'Jeton d\'édition invalide.');

        return $next($request);
    }
}
