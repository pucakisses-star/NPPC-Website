<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Merges the two duplicate Richard Williams (United Freedom Front) records
 * into one, keeping "richard-williams". The detailed data lives on the
 * duplicate "richard-charles-williams" (full biography, 45-year Brooklyn
 * sentence, etc.), so that record's descriptive fields and cases are copied
 * onto the keeper, then the duplicate is deleted.
 *
 * The kept record is set to name "Richard Williams" with middle name
 * "Charles", DOB 1947-11-04, and the Nov 4 1984 Cleveland capture; the bio's
 * incorrect "April 1985" arrest date (his co-defendant Thomas Manning's) is
 * corrected. Idempotent: if the duplicate is already gone, it just re-asserts
 * the keeper's name/middle/DOB/dates.
 */
final class MergeRichardWilliams extends Command
{
    protected $signature = 'prisoners:merge-richard-williams';

    protected $description = 'Merge the duplicate Richard Williams (UFF) records into richard-williams';

    private const DOB = '1947-11-04';

    private const CAPTURE = '1984-11-04';

    public function handle(): int
    {
        $keeper = Prisoner::withUnderReview()->where('slug', 'richard-williams')->first();
        $source = Prisoner::withUnderReview()->where('slug', 'richard-charles-williams')->first();

        if (! $keeper) {
            $this->error('Keeper "richard-williams" not found — aborting.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($keeper, $source) {
            // Pull the richer descriptive data from the duplicate (it wins where present).
            if ($source) {
                foreach (['description', 'body', 'state', 'era', 'race', 'gender', 'death_date', 'age', 'photo', 'inmate_number'] as $f) {
                    if (! empty($source->{$f})) {
                        $keeper->{$f} = $source->{$f};
                    }
                }
                if (! empty($source->ideologies)) {
                    $keeper->ideologies = $source->ideologies;
                }
                if (! empty($source->affiliation)) {
                    $keeper->affiliation = $source->affiliation;
                }
                $keeper->in_custody = $source->in_custody;
                $keeper->released = $source->released;
            }

            // Name per request: "Richard Williams", middle name "Charles".
            $keeper->name = 'Richard Williams';
            $keeper->first_name = 'Richard';
            $keeper->middle_name = 'Charles';
            $keeper->last_name = 'Williams';
            $keeper->birthdate = self::DOB;

            // Correct the bio's wrong arrest date (Manning's April 1985 capture).
            if ($keeper->description) {
                $keeper->description = str_replace(
                    ['April 24, 1985', 'April 23, 1985', 'April 1985'],
                    'November 4, 1984',
                    $keeper->description
                );
            }
            $keeper->save();

            // Consolidate cases: drop the keeper's sparse case, move the
            // duplicate's detailed case(s) onto the keeper.
            if ($source) {
                $keeper->cases()->delete();
                foreach ($source->cases as $case) {
                    $case->prisoner_id = $keeper->id;
                    $case->save();
                }
            }

            // Apply the confirmed Nov 4 1984 capture to the keeper's primary case.
            $case = $keeper->cases()->first();
            if ($case) {
                $case->arrest_date = self::CAPTURE;
                $case->incarceration_date = self::CAPTURE;
                $case->save();
            }

            // Remove the now-empty duplicate.
            if ($source) {
                $source->delete();
            }
        });

        $this->info($source
            ? 'Merged richard-charles-williams into richard-williams and deleted the duplicate.'
            : 'Duplicate already gone; re-asserted name/middle/DOB/capture on richard-williams.');
        $this->info('Richard Williams: DOB '.self::DOB.', captured '.self::CAPTURE.', middle name Charles.');

        return self::SUCCESS;
    }
}
