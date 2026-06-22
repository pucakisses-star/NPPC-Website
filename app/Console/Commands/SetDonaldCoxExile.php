<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Records Donald "DC" Cox's exile as the resolution of his case. Cox was named a
 * co-conspirator in the Baltimore Black Panther case (the 1969 torture-killing of
 * suspected informant Eugene Leroy Anderson) and fled the United States into
 * exile in April 1970 to avoid trial, living in Algeria and then southern France
 * until his death on February 19, 2011 — so the end of his exile is his death
 * date, which is used as the case's release_date.
 *
 * The exact DAY he fled is not documented (sources, and The Black Panther
 * newspaper itself, give only "April 1970"), so no fabricated start day is
 * stored; "April 1970" is recorded in the charge text instead.
 *
 * Idempotent: only updates if the exile resolution is not already recorded.
 */
final class SetDonaldCoxExile extends Command
{
    protected $signature = 'prisoners:set-donald-cox-exile';

    protected $description = 'Record Donald "DC" Cox\'s April 1970 exile, ending at his 2011 death (release_date)';

    private const RELEASE = '2011-02-19';

    private const CHARGES = 'Named a co-conspirator in the Baltimore Black Panther case — the 1969 torture-killing of suspected informant Eugene Leroy Anderson. Cox fled the United States into exile in April 1970 to avoid trial, living first in Algeria (Eldridge Cleaver\'s International Section of the Black Panther Party) and, from 1977, in southern France.';

    private const CONVICTED = 'Never tried — fled into exile in April 1970 to avoid trial and remained in exile until his death on February 19, 2011.';

    public function handle(): int
    {
        $prisoner = null;
        foreach (['Donald Cox', 'Don Cox'] as $fragment) {
            $prisoner = Prisoner::withUnderReview()->where('name', 'like', '%'.$fragment.'%')->first();
            if ($prisoner) {
                break;
            }
        }

        if (! $prisoner) {
            $this->warn('Donald Cox not found, nothing to do.');

            return self::SUCCESS;
        }

        // Reuse his existing case (the conspiracy charge he fled) if present.
        $case = $prisoner->cases()->first();

        if ($case && $case->release_date && $case->release_date->format('Y-m-d') === self::RELEASE) {
            $this->line("Exile already recorded for {$prisoner->name}, nothing to do.");

            return self::SUCCESS;
        }

        if (! $case) {
            $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
        }

        $case->charges = self::CHARGES;
        $case->convicted = self::CONVICTED;
        $case->release_date = self::RELEASE;
        $case->prisoner_id = $prisoner->id;
        $case->save();

        $this->info("Recorded exile for {$prisoner->name}: fled April 1970, exile ended at death (release_date ".self::RELEASE.').');

        return self::SUCCESS;
    }
}
