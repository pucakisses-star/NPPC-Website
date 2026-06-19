<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Merges the duplicate United Freedom Front records into one apiece, keeping
 * the most complete record in each set and folding the others' data and cases
 * into it before deleting them:
 *
 *   - Thomas Manning  -> keep "thomas-william-manning" (full name, 2 cases,
 *                        DOB, photo); merge thomas-manning, tom-manning.
 *   - Carol Manning   -> keep "carol-saucier-manning" (only one with a DOB,
 *                        richest bio); merge carol-manning, carole-manning.
 *   - Richard Williams-> keep "richard-williams" (curated: DOB, Nov 4 1984
 *                        capture, photo); merge richard-c-williams.
 *
 * The keeper wins on every field it already has; only its EMPTY fields are
 * filled from a duplicate. Cases (and any podcast/calendar links) are moved
 * onto the keeper, skipping cases whose charges duplicate one it already has.
 * The keeper's name/slug are never changed. Idempotent: duplicates already
 * gone are skipped.
 */
final class MergeUffDuplicates extends Command
{
    protected $signature = 'prisoners:merge-uff-duplicates';

    protected $description = 'Merge duplicate UFF records (Thomas Manning, Carol Manning, Richard Williams)';

    /** keeper slug => [duplicate slugs to fold in and delete] */
    private const MERGES = [
        'thomas-william-manning' => ['thomas-manning', 'tom-manning'],
        'carol-saucier-manning' => ['carol-manning', 'carole-manning'],
        'richard-williams' => ['richard-c-williams'],
    ];

    private const FILL_FIELDS = [
        'description', 'body', 'birthdate', 'death_date', 'race', 'gender', 'state',
        'address', 'lat', 'lng', 'era', 'photo', 'inmate_number',
        'first_name', 'middle_name', 'last_name', 'aka',
        'website', 'twitter', 'facebook', 'instagram',
    ];

    public function handle(): int
    {
        foreach (self::MERGES as $keeperSlug => $dupSlugs) {
            $this->mergeOne($keeperSlug, $dupSlugs);
        }

        return self::SUCCESS;
    }

    private function mergeOne(string $keeperSlug, array $dupSlugs): void
    {
        $keeper = Prisoner::withUnderReview()->where('slug', $keeperSlug)->first();
        if (! $keeper) {
            $this->error("Keeper {$keeperSlug} not found — skipping this set.");

            return;
        }

        foreach ($dupSlugs as $dupSlug) {
            $dup = Prisoner::withUnderReview()->where('slug', $dupSlug)->first();
            if (! $dup) {
                $this->line("  {$dupSlug} already gone.");

                continue;
            }

            DB::transaction(function () use ($keeper, $dup) {
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

                // Move cases, skipping ones that duplicate an existing charge.
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

                // Move any podcast episodes / calendar entries off the dup.
                $dup->podcastEpisodes()->update(['prisoner_id' => $keeper->id]);
                $dup->calendarEntries()->update(['prisoner_id' => $keeper->id]);

                $dup->delete();
            });

            $this->info("  Merged {$dupSlug} -> {$keeperSlug}");
        }
    }
}
