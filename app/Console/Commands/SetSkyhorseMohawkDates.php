<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backfills the arrest, incarceration and release dates on the existing case
 * records for Paul Skyhorse (Paul Durant) and Richard Mohawk (Richard Billings),
 * the two American Indian Movement activists charged with the October 10, 1974
 * murder of cab driver George Aird at the Box Canyon AIM camp in Ventura County,
 * California. Arrested in Phoenix about ten days after the killing, they were
 * held without bail in the Ventura County Jail for roughly three and a half
 * years until a jury acquitted them on May 24, 1978 — the close of one of the
 * longest criminal trials in California history, widely seen as an FBI-era
 * frame-up to discredit the movement.
 *
 * Both share the same timeline. Fills only blank fields (idempotent) so it never
 * clobbers existing data; imprisoned_for_days is recomputed by the model on save.
 */
final class SetSkyhorseMohawkDates extends Command
{
    protected $signature = 'prisoners:set-skyhorse-mohawk-dates';

    protected $description = 'Backfill arrest/incarceration/release dates for Paul Skyhorse and Richard Mohawk (AIM, George Aird case)';

    public function handle(): int
    {
        // Arrested in Phoenix ~10 days after the Oct 10, 1974 murder; held
        // without bail in Ventura; acquitted and freed May 24, 1978.
        $dates = [
            'arrest_date' => '1974-10-20',
            'incarceration_date' => '1974-10-20',
            'release_date' => '1978-05-24',
        ];

        DB::transaction(function () use ($dates) {
            $institution = Institution::firstOrCreate(
                ['name' => 'Ventura County Jail'],
                ['city' => 'Ventura', 'state' => 'California']
            );

            foreach (['Paul Skyhorse', 'Richard Mohawk'] as $name) {
                $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();
                if (! $prisoner) {
                    $this->warn("Not found, skipping: {$name}");

                    continue;
                }

                $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);

                $fill = [];
                foreach ($dates as $key => $value) {
                    if ($case->getAttribute($key) === null) {
                        $fill[$key] = $value;
                    }
                }
                if (! $case->institution_id) {
                    $fill['institution_id'] = $institution->id;
                }

                if ($fill === []) {
                    $this->line("  Already set, no change: {$name}");

                    continue;
                }

                $case->fill($fill)->save();
                $this->info("  {$name}: set ".implode(', ', array_keys($fill)));
            }
        });

        return self::SUCCESS;
    }
}
