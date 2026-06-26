<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Resolves two lingering custody/case problems:
 *
 *  - Marius Mason has a leftover duplicate record under his former name
 *    ("Marie Mason", slug marie-mason) that still shows "In Custody" with no
 *    case, while the canonical "Marius Mason" record is correct (released, with
 *    a case). This removes the duplicate and re-affirms the canonical record.
 *  - Matthew DePalma still shows "In Custody" on prod with no dated case; he was
 *    released in September 2011 (42-month sentence; arrested Aug 30, 2008). This
 *    sets him released and ensures his case carries those dates.
 *
 * Idempotent.
 */
final class DedupeMasonFixDepalma extends Command
{
    protected $signature = 'prisoners:dedupe-mason-fix-depalma';

    protected $description = 'Remove the duplicate Marie Mason record and fix Matthew DePalma\'s released status/case';

    public function handle(): int
    {
        // 1) Remove the duplicate "Marie Mason" record (the canonical record is
        //    "Marius Mason"). Only delete if a separate marius-mason exists.
        $dup = Prisoner::withoutGlobalScopes()->where('slug', 'marie-mason')->first();
        $canonical = Prisoner::withoutGlobalScopes()->where('slug', 'marius-mason')->first();

        if ($dup && $canonical && $dup->id !== $canonical->id) {
            if ($dup->photo && Storage::disk('public')->exists($dup->photo)) {
                Storage::disk('public')->delete($dup->photo);
            }
            $dup->cases()->delete();
            $dup->calendarEntries()->delete();
            $dup->podcastEpisodes()->delete();
            $dup->delete();
            $this->info('Removed duplicate "Marie Mason" record (kept Marius Mason).');
        } elseif ($dup && ! $canonical) {
            $this->warn('Found marie-mason but no marius-mason — left it in place to avoid data loss.');
        } else {
            $this->line('No duplicate marie-mason record found.');
        }

        // Re-affirm the canonical Marius Mason record (released, with case dates).
        if ($canonical) {
            $canonical->in_custody = false;
            $canonical->released = true;
            $canonical->save();
            $case = $canonical->cases()->first() ?? $canonical->cases()->make([]);
            $case->setPartialDate('incarceration_date', 2008, 3, 10);
            $case->setPartialDate('release_date', 2026, 5, 14); // released to a halfway house
            $case->save();
            $this->info('Re-affirmed Marius Mason: released, case dates set.');
        }

        // 2) Matthew DePalma — released September 2011.
        $dp = Prisoner::withoutGlobalScopes()->where('slug', 'matthew-depalma')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%DePalma%')->first();
        if ($dp) {
            $dp->in_custody = false;
            $dp->released = true;
            $dp->save();
            $case = $dp->cases()->first() ?? $dp->cases()->make([
                'charges' => 'Possession of a destructive device (Molotov cocktails) allegedly intended for the 2008 Republican National Convention in St. Paul; pleaded guilty.',
            ]);
            $case->setPartialDate('incarceration_date', 2008, 8, 30);
            $case->setPartialDate('release_date', 2011, 9);
            $case->save();
            $this->info("Fixed Matthew DePalma: released, case dates set ({$case->imprisoned_for_days} days).");
        } else {
            $this->warn('No Matthew DePalma record found.');
        }

        return self::SUCCESS;
    }
}
