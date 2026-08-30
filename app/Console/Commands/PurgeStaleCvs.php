<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Cv;
use App\Services\PhotoProcessor;
use Illuminate\Console\Command;

/**
 * Un service anonyme conserve des donnees personnelles sans qu'aucun compte ne
 * permette a la personne concernee de revenir les gerer. La duree de
 * conservation est donc bornee : passe 12 mois sans consultation ni
 * modification, le CV et sa photo sont supprimes.
 */
class PurgeStaleCvs extends Command
{
    protected $signature = 'cv:purge {--months=12 : Ancienneté au-delà de laquelle un CV est supprimé}
                                     {--dry-run : Affiche ce qui serait supprimé sans rien effacer}';

    protected $description = 'Supprime les CV inactifs et leurs photos';

    public function handle(PhotoProcessor $photos): int
    {
        $months = max(1, (int) $this->option('months'));
        $threshold = now()->subMonths($months);
        $dryRun = (bool) $this->option('dry-run');

        $query = Cv::query()->where(function ($q) use ($threshold) {
            $q->where('last_seen_at', '<', $threshold)
                ->orWhere(fn ($inner) => $inner->whereNull('last_seen_at')->where('created_at', '<', $threshold));
        });

        $count = 0;

        // Par lots : la table peut etre volumineuse et chaque suppression touche
        // aussi le disque.
        $query->chunkById(200, function ($cvs) use ($photos, $dryRun, &$count) {
            foreach ($cvs as $cv) {
                $count++;

                if ($dryRun) {
                    $this->line("  {$cv->public_id} — inactif depuis {$cv->last_seen_at?->diffForHumans()}");

                    continue;
                }

                $photos->delete($cv->photoDirectory());
                $cv->delete();
            }
        });

        $this->info($dryRun
            ? "{$count} CV seraient supprimés (seuil : {$months} mois)."
            : "{$count} CV supprimés (seuil : {$months} mois).");

        return self::SUCCESS;
    }
}
