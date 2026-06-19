<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Merges four duplicate prisoner records surfaced while filling inmate numbers.
 * Each survivor keeps the clean (non-"-2") slug; the richer data is pulled in
 * from whichever record has it: the longer biography wins, empty scalar fields
 * are filled from the duplicate, and the duplicate's cases / podcast / calendar
 * links are moved over before it is deleted.
 *
 *   dhoruba-bin-wahad      <- dhoruba-bin-wahad-2      (BLA, NY)
 *   alberto-rodriguez      <- alberto-rodriguez-2      (FALN)
 *   larry-giddings         <- larry-w-giddings         (North American anti-imperialist)
 *   albert-nuh-washington  <- albert-nuh-washington-2  (BLA, NY)
 *
 * NOT merged: "geronimo" is the Apache leader Geronimo (1829–1909), a separate
 * person from "elmer-geronimo-pratt" (Geronimo ji-Jaga Pratt, BPP) — left alone.
 *
 * Idempotent: a pair whose duplicate is already gone is skipped.
 */
final class MergeInsurgentDuplicates extends Command
{
    protected $signature = 'prisoners:merge-insurgent-duplicates';

    protected $description = 'Merge 4 duplicate prisoner records (Dhoruba, Alberto Rodríguez, Giddings, Nuh Washington)';

    /** [keeper slug (survivor), duplicate slug (folded in & deleted)] */
    private const MERGES = [
        ['dhoruba-bin-wahad', 'dhoruba-bin-wahad-2'],
        ['alberto-rodriguez', 'alberto-rodriguez-2'],
        ['larry-giddings', 'larry-w-giddings'],
        ['albert-nuh-washington', 'albert-nuh-washington-2'],
    ];

    private const FILL_FIELDS = [
        'birthdate', 'death_date', 'race', 'gender', 'state', 'address', 'lat', 'lng',
        'era', 'photo', 'inmate_number', 'first_name', 'middle_name', 'last_name', 'aka',
        'website', 'twitter', 'facebook', 'instagram',
    ];

    public function handle(): int
    {
        foreach (self::MERGES as [$keeperSlug, $dupSlug]) {
            $keeper = Prisoner::withUnderReview()->where('slug', $keeperSlug)->first();
            $dup = Prisoner::withUnderReview()->where('slug', $dupSlug)->first();

            if (! $keeper) {
                $this->error("Keeper {$keeperSlug} not found — skipping.");

                continue;
            }
            if (! $dup) {
                $this->line("{$dupSlug} already gone — skipping.");

                continue;
            }

            DB::transaction(function () use ($keeper, $dup) {
                // Longer biography/body wins.
                foreach (['description', 'body'] as $f) {
                    if (strlen((string) $dup->{$f}) > strlen((string) $keeper->{$f})) {
                        $keeper->{$f} = $dup->{$f};
                    }
                }
                // Fill only the keeper's empty scalar fields from the duplicate.
                foreach (self::FILL_FIELDS as $f) {
                    if (empty($keeper->{$f}) && ! empty($dup->{$f})) {
                        $keeper->{$f} = $dup->{$f};
                    }
                }
                if (empty($keeper->ideologies) && ! empty($dup->ideologies)) {
                    $keeper->ideologies = $dup->ideologies;
                }
                if (empty($keeper->affiliation) && ! empty($dup->affiliation)) {
                    $keeper->affiliation = $dup->affiliation;
                }
                $keeper->save();

                // Move the duplicate's non-redundant cases onto the keeper.
                $keeper->load('cases');
                $existing = $keeper->cases->map(fn ($c) => trim((string) $c->charges))->all();
                foreach ($dup->cases as $case) {
                    if (in_array(trim((string) $case->charges), $existing, true)) {
                        $case->delete();

                        continue;
                    }
                    $case->prisoner_id = $keeper->id;
                    $case->save();
                    $existing[] = trim((string) $case->charges);
                }

                $dup->podcastEpisodes()->update(['prisoner_id' => $keeper->id]);
                $dup->calendarEntries()->update(['prisoner_id' => $keeper->id]);

                $dup->delete();
            });

            $this->info("Merged {$dupSlug} -> {$keeperSlug}");
        }

        return self::SUCCESS;
    }
}
