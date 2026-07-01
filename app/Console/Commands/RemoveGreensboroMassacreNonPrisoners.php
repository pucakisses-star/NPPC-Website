<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Removes the Greensboro Massacre (November 3, 1979) figures who were never
 * actually imprisoned. In the massacre, Klansmen and American Nazis shot and
 * killed five Communist Workers Party demonstrators at a "Death to the Klan"
 * rally; the gunmen were acquitted. None of the people below served a prison
 * term — the five were killed, and the demonstrators' charges were dropped or
 * never led to incarceration — so they do not belong in a database of
 * political prisoners.
 *
 * This is scoped strictly to the 1979 massacre. It does NOT touch the unrelated
 * "Greensboro" records (the 2011 Greensboro Latin Kings RICO defendants, David
 * Gilbert, Junius Scales, or the NASSCO 3).
 *
 * Idempotent: skips any slug already gone.
 */
final class RemoveGreensboroMassacreNonPrisoners extends Command
{
    protected $signature = 'prisoners:remove-greensboro-massacre-non-prisoners';

    protected $description = 'Remove Greensboro Massacre (1979) figures who were never imprisoned';

    /** slug => reason (none of these served a prison term). */
    private const REMOVE = [
        // The five CWP demonstrators shot dead — killed, not imprisoned.
        'james-waller' => 'Killed in the massacre (martyr) — never imprisoned',
        'cesar-cauce' => 'Killed in the massacre (martyr) — never imprisoned',
        'michael-nathan' => 'Killed in the massacre (martyr) — never imprisoned',
        'sandra-neely-smith' => 'Killed in the massacre (martyr) — never imprisoned',
        'william-evan-sampson' => 'Killed in the massacre (martyr) — never imprisoned',
        // Demonstrators charged after the massacre but never imprisoned.
        'allen-blitz' => 'Charged with rioting after the massacre; no sentence — never imprisoned',
        'dorothy-blitz' => 'Charged with rioting after the massacre; no sentence — never imprisoned',
        'nelson-johnson' => 'Jailed briefly at the scene; charges dropped — never imprisoned',
    ];

    public function handle(): int
    {
        $removed = 0;

        foreach (self::REMOVE as $slug => $reason) {
            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->line("  already gone: {$slug}");

                continue;
            }

            $prisoner->cases()->delete();
            $prisoner->podcastEpisodes()->delete();
            $prisoner->calendarEntries()->delete();
            $name = $prisoner->name;
            $prisoner->delete();

            $this->info("  removed: {$name} — {$reason}");
            $removed++;
        }

        $this->info("\nDone. Removed {$removed} Greensboro Massacre record(s).");

        return self::SUCCESS;
    }
}
