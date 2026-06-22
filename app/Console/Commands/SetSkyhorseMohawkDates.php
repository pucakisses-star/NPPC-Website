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
 * California. Arrested in Phoenix on October 17, 1974 — a week after the killing
 * — they were held without bail in the Ventura County Jail for roughly three and
 * a half years until a jury acquitted them on May 24, 1978, the close of one of
 * the longest criminal trials in California history, widely seen as an FBI-era
 * frame-up to discredit the movement.
 *
 * Both share the same timeline. The arrest/incarceration date is set
 * authoritatively (so a re-run corrects any earlier value); the release date and
 * institution are filled only when blank so they are never clobbered.
 * imprisoned_for_days is recomputed by the model on save.
 */
final class SetSkyhorseMohawkDates extends Command
{
    protected $signature = 'prisoners:set-skyhorse-mohawk-dates';

    protected $description = 'Backfill arrest/incarceration/release dates for Paul Skyhorse and Richard Mohawk (AIM, George Aird case)';

    public function handle(): int
    {
        // Arrested in Phoenix on October 17, 1974 (a week after the Oct 10
        // murder); held without bail in Ventura; acquitted and freed May 24, 1978.
        $arrestDate = '1974-10-17';

        // Filled only when blank, so existing values are never clobbered.
        $fillIfBlank = [
            'release_date' => '1978-05-24',
        ];

        DB::transaction(function () use ($arrestDate, $fillIfBlank) {
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

                // Authoritative: corrects any earlier arrest/incarceration date.
                $case->arrest_date = $arrestDate;
                $case->incarceration_date = $arrestDate;

                foreach ($fillIfBlank as $key => $value) {
                    if ($case->getAttribute($key) === null) {
                        $case->setAttribute($key, $value);
                    }
                }
                if (! $case->institution_id) {
                    $case->institution_id = $institution->id;
                }

                $case->save();
                $this->info("  {$name}: arrest/incarceration = {$arrestDate}");
            }
        });

        return self::SUCCESS;
    }
}
