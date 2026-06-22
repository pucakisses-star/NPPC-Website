<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Records Donald "DC" Cox's exile using the case's dedicated exile fields. Cox
 * was named a co-conspirator in the Baltimore Black Panther case (the 1969
 * torture-killing of suspected informant Eugene Leroy Anderson) and fled the
 * United States in April 1970 to avoid trial, living in Algeria and then southern
 * France until his death on February 19, 2011 — so his exile runs April 1970 →
 * his death.
 *
 * This uses in_exile_since / end_of_exile (which feed the "In Exile" badge and the
 * "Time in Exile" counter and are NOT rendered as literal dates), so the start is
 * anchored to April 1970 without displaying a fabricated day — the exact day he
 * fled is not documented (sources, and The Black Panther itself, give only "April
 * 1970"). It also clears the case release_date, since Cox was never released from
 * U.S. custody (he fled before trial), and flags the prisoner as having been in
 * exile. Idempotent.
 */
final class SetDonaldCoxExile extends Command
{
    protected $signature = 'prisoners:set-donald-cox-exile';

    protected $description = 'Record Donald "DC" Cox\'s April 1970 → 2011 exile via the case exile fields';

    /** April 1970 (exact day undocumented); only the duration is shown, never this date. */
    private const EXILE_SINCE = '1970-04-01';

    /** His death — the end of his exile. */
    private const EXILE_END = '2011-02-19';

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

        // "Was ever in exile" flag; not currently, since he is deceased.
        $prisonerDirty = false;
        if (! $prisoner->in_exile) {
            $prisoner->in_exile = true;
            $prisonerDirty = true;
        }
        if ($prisoner->currently_in_exile) {
            $prisoner->currently_in_exile = false;
            $prisonerDirty = true;
        }
        if ($prisonerDirty) {
            $prisoner->save();
        }

        // Reuse his existing case (the conspiracy charge he fled) if present.
        $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);

        $alreadySet = $case->in_exile_since
            && $case->in_exile_since->format('Y-m-d') === self::EXILE_SINCE
            && $case->end_of_exile
            && $case->end_of_exile->format('Y-m-d') === self::EXILE_END
            && ! $case->release_date;

        if ($alreadySet && ! $prisonerDirty) {
            $this->line("Exile already recorded for {$prisoner->name}, nothing to do.");

            return self::SUCCESS;
        }

        $case->prisoner_id = $prisoner->id;
        $case->charges = self::CHARGES;
        $case->convicted = self::CONVICTED;
        $case->in_exile_since = self::EXILE_SINCE;
        $case->end_of_exile = self::EXILE_END;
        $case->release_date = null; // he fled before trial — never released from U.S. custody
        $case->save();

        $this->info("Recorded exile for {$prisoner->name}: in exile April 1970 → {$case->end_of_exile->format('M j, Y')} (his death).");

        return self::SUCCESS;
    }
}
