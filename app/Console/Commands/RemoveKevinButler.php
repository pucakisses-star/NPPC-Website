<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Removes the Kevin C. Butler prisoner record (also known as Kevin A. Butler)
 * and its associated case(s). The prisoner_cases foreign key cascades on
 * delete, but the cases are removed explicitly first to match the project's
 * existing pattern. Idempotent: if the record is already gone, it reports so
 * and exits successfully. Matches by slug, then by name/AKA.
 */
final class RemoveKevinButler extends Command
{
    protected $signature = 'prisoners:remove-kevin-butler';

    protected $description = 'Remove the Kevin C. Butler (Kevin A. Butler) prisoner record';

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'kevin-c-butler')->first()
            ?? Prisoner::withoutGlobalScopes()
                ->where('name', 'like', '%Kevin%Butler%')
                ->where(function ($q) {
                    $q->where('name', 'like', '%Kevin C. Butler%')
                        ->orWhere('name', 'like', '%Kevin A. Butler%')
                        ->orWhere('aka', 'like', '%Kevin A. Butler%');
                })
                ->first();

        if (! $prisoner) {
            $this->info('No Kevin C. Butler / Kevin A. Butler record found — nothing to remove.');

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
