<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Removes entries for people who were arrested or indicted but never actually
 * incarcerated, so they don't fit a political-PRISONER archive:
 *
 *  - Jay Carey — arrested for burning a flag near the White House (Aug 2025),
 *    held a few hours and released the same night; the fire-misdemeanor charges
 *    were dropped in 2026. Never jailed, never convicted.
 *  - Kyrle Elkin, Abbott Simon, Sylvia Soloff — W. E. B. Du Bois's Peace
 *    Information Center co-defendants in the 1951 FARA prosecution. Released on
 *    bail and acquitted from the bench in November 1951 (Soloff dismissed
 *    mid-trial). None served time.
 *
 * Deletes each record and its cases, calendar entries, podcast episodes, and
 * photo. Idempotent — skips anyone already gone.
 */
final class RemoveNeverJailedEntries extends Command
{
    protected $signature = 'prisoners:remove-never-jailed';

    protected $description = 'Remove entries for people who were never actually incarcerated (arrest/indictment only)';

    public function handle(): int
    {
        $names = ['Jay Carey', 'Kyrle Elkin', 'Abbott Simon', 'Sylvia Soloff'];
        $removed = 0;

        foreach ($names as $name) {
            $prisoner = Prisoner::withoutGlobalScopes()
                ->where('slug', Str::slug($name))
                ->orWhere('name', $name)
                ->first();

            if (! $prisoner) {
                $this->line("Not found (already removed?): {$name}");

                continue;
            }

            if ($prisoner->photo && Storage::disk('public')->exists($prisoner->photo)) {
                Storage::disk('public')->delete($prisoner->photo);
            }

            $cases = $prisoner->cases()->count();
            $prisoner->cases()->delete();
            $prisoner->calendarEntries()->delete();
            $prisoner->podcastEpisodes()->delete();
            $prisoner->delete();
            $removed++;

            $this->info("Removed: {$name} ({$cases} case(s)).");
        }

        $this->info("\nDone. Removed {$removed} record(s).");

        return self::SUCCESS;
    }
}
