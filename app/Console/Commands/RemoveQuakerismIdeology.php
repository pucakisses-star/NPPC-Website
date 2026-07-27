<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Removes "Quakerism" from prisoners' ideologies (also catching the "Quaker"
 * and "Religious Society of Friends" spellings). The value is dropped from
 * each ideologies array; no replacement is added. Idempotent; supports
 * --dry-run.
 */
final class RemoveQuakerismIdeology extends Command
{
    protected $signature = 'prisoners:remove-quakerism-ideology {--dry-run : Show planned changes without writing}';

    protected $description = 'Remove the "Quakerism" ideology from all prisoners';

    private const TARGETS = ['quakerism', 'quaker', 'quakers', 'religious society of friends'];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $prisoners = Prisoner::withoutGlobalScopes()
            ->where(fn ($q) => $q
                ->where('ideologies', 'like', '%uaker%')
                ->orWhere('ideologies', 'like', '%Society of Friends%'))
            ->get();

        $changed = 0;
        $emptied = [];

        foreach ($prisoners as $prisoner) {
            $ideologies = is_array($prisoner->ideologies) ? $prisoner->ideologies : [];

            $kept = array_values(array_filter(
                $ideologies,
                fn ($value) => ! in_array(strtolower(trim((string) $value)), self::TARGETS, true),
            ));

            if ($kept === $ideologies) {
                continue;   // matched the LIKE on some other value
            }

            $this->line("  {$prisoner->slug}: ".implode(', ', $ideologies).'  ->  '.(implode(', ', $kept) ?: '(none)'));
            if (! $kept) {
                $emptied[] = $prisoner->slug;
            }

            if (! $dry) {
                $prisoner->ideologies = $kept;
                $prisoner->save();
            }

            $changed++;
        }

        if ($emptied) {
            $this->newLine();
            $this->warn(count($emptied).' record(s) are left with no ideology at all:');
            foreach ($emptied as $slug) {
                $this->line("  {$slug}");
            }
            $this->line('Those are mostly war resisters, so Pacifism or Anti-Militarism may fit.');
        }

        if (! $dry && $changed > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        $this->newLine();
        if ($dry) {
            $this->warn("Dry run — no changes written. {$changed} record(s) would change.");
        } else {
            $this->info("Done. Removed the Quakerism ideology from {$changed} record(s).");
        }

        return self::SUCCESS;
    }
}
