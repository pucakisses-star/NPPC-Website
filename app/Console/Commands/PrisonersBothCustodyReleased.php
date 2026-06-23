<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Data-integrity check: counts (and lists) prisoners that have BOTH the
 * "in custody" and "released" status flags checked — a contradictory state,
 * since a prisoner should be one or the other. Read-only; it changes nothing.
 */
final class PrisonersBothCustodyReleased extends Command
{
    protected $signature = 'prisoners:both-custody-released {--no-list : Only print the count, do not list names}';

    protected $description = 'Count prisoners with both "in custody" and "released" checked under status';

    public function handle(): int
    {
        $query = Prisoner::withUnderReview()
            ->where('in_custody', true)
            ->where('released', true);

        $count = (clone $query)->count();

        $this->info("Prisoners with BOTH \"in custody\" and \"released\" checked: {$count}");

        if ($count > 0 && ! $this->option('no-list')) {
            foreach ($query->orderBy('name')->get(['id', 'name', 'slug']) as $p) {
                $this->line("  - {$p->name}  (/{$p->slug}, {$p->id})");
            }
        }

        return self::SUCCESS;
    }
}
