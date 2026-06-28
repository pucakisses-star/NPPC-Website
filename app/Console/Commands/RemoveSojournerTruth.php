<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Removes the Sojourner Truth prisoner record (born Isabella Baumfree) and its
 * associated case(s). The prisoner_cases foreign key cascades on delete, but
 * the cases are removed explicitly first to match the project's existing
 * pattern. Idempotent: if the record is already gone, it reports so and exits
 * successfully. Matches by slug, then by name/AKA.
 */
final class RemoveSojournerTruth extends Command
{
    protected $signature = 'prisoners:remove-sojourner-truth';

    protected $description = 'Remove the Sojourner Truth (Isabella Baumfree) prisoner record';

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'sojourner-truth')->first()
            ?? Prisoner::withoutGlobalScopes()
                ->where(function ($q) {
                    $q->where('name', 'like', '%Sojourner Truth%')
                        ->orWhere('name', 'like', '%Isabella Baumfree%')
                        ->orWhere('aka', 'like', '%Sojourner Truth%')
                        ->orWhere('aka', 'like', '%Isabella Baumfree%');
                })
                ->first();

        if (! $prisoner) {
            $this->info('No Sojourner Truth / Isabella Baumfree record found — nothing to remove.');

            return self::SUCCESS;
        }

        $name = $prisoner->name;
        $caseCount = $prisoner->cases()->count();

        $prisoner->cases()->delete();
        $prisoner->delete();

        $this->info("Removed {$name} (and {$caseCount} case(s)).");

        return self::SUCCESS;
    }
}
