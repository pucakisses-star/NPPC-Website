<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Merges the duplicate Ángel Rodríguez Cristóbal records that exist on
 * production — one spelled "Ángel" (with the accent, carrying his portrait and
 * the detailed bio) and one spelled "Angel" (the older "Vieques 4 / PFOC
 * Breakthrough" record). The accent-broad name match ("ngel" + "Rodr" +
 * "Crist") catches both regardless of accent encoding.
 *
 * The accented record (slug angel-rodriguez-cristobal, which has the photo) is
 * kept; the others are merged into it — the union of ideology tags and
 * affiliations is preserved, any blank field on the keeper is filled from a
 * duplicate, his birth/death dates and full Vieques case dates are set
 * authoritatively, and the duplicate records (and their cases) are deleted.
 *
 * Idempotent: if only one record exists (e.g. in a snapshot without the
 * duplicate) it simply normalises that record and deletes nothing.
 */
final class MergeRodriguezCristobal extends Command
{
    protected $signature = 'prisoners:merge-rodriguez-cristobal';

    protected $description = 'Merge the duplicate Ángel Rodríguez Cristóbal records into one';

    public function handle(): int
    {
        $matches = Prisoner::withUnderReview()
            ->where('name', 'like', '%ngel%')
            ->where('name', 'like', '%Rodr%')
            ->where('name', 'like', '%Crist%')
            ->get();

        if ($matches->isEmpty()) {
            $this->warn('No Ángel Rodríguez Cristóbal record found.');

            return self::SUCCESS;
        }

        // Choose the record to keep: the canonical accented slug, else one with
        // a photo, else the accented spelling, else the first.
        $keeper = $matches->firstWhere('slug', 'angel-rodriguez-cristobal')
            ?? $matches->first(fn (Prisoner $p) => filled($p->photo))
            ?? $matches->first(fn (Prisoner $p) => str_contains($p->name, 'Ángel'))
            ?? $matches->first();

        $dups = $matches->reject(fn (Prisoner $p) => $p->id === $keeper->id)->values();

        DB::transaction(function () use ($keeper, $dups) {
            $ideologies = collect($keeper->ideologies ?? []);
            $affiliation = collect($keeper->affiliation ?? []);

            foreach ($dups as $dup) {
                $ideologies = $ideologies->merge($dup->ideologies ?? []);
                $affiliation = $affiliation->merge($dup->affiliation ?? []);
                foreach (['photo', 'description', 'aka', 'race', 'gender', 'state', 'era', 'address'] as $field) {
                    if (blank($keeper->getAttribute($field)) && filled($dup->getAttribute($field))) {
                        $keeper->setAttribute($field, $dup->getAttribute($field));
                    }
                }
            }

            $keeper->name = 'Ángel Rodríguez Cristóbal';
            $keeper->ideologies = $ideologies->unique()->values()->all();
            $keeper->affiliation = $affiliation->unique()->values()->all();
            $keeper->birthdate = '1946-04-02';
            $keeper->death_date = '1979-11-11';
            $keeper->in_custody = false;
            $keeper->released = false;
            $keeper->save();

            // Ensure the keeper has one complete Vieques case.
            $case = $keeper->cases()->first() ?? new PrisonerCase(['prisoner_id' => $keeper->id]);
            $case->arrest_date = '1979-05-19';
            $case->sentenced_date = '1979-09-26';
            $case->incarceration_date = '1979-09-28';
            $case->death_in_custody_date = '1979-11-11';
            if (blank($case->charges)) {
                $case->charges = 'Trespassing on the U.S. Navy\'s restricted bombing range in Vieques (arrested May 19, 1979 with about twenty other protesters).';
            }
            $case->save();

            // Delete the duplicate records and their cases.
            foreach ($dups as $dup) {
                $dup->cases()->delete();
                $dup->delete();
            }
        });

        $this->info("Kept: {$keeper->name} (slug={$keeper->slug}); merged and deleted {$dups->count()} duplicate(s).");
        $this->line('  ideologies: '.json_encode($keeper->ideologies));
        $this->line('  affiliation: '.json_encode($keeper->affiliation));

        return self::SUCCESS;
    }
}
