<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Models Larry Mack (Panther 21) as an exile rather than a released prisoner.
 * Mack evaded the April 2, 1969 Panther 21 raids, went underground, and fled
 * to Algiers, joining Eldridge Cleaver's Black Panther International Section;
 * he was acquitted in absentia with the other defendants on May 13, 1971 and
 * was never in U.S. custody. His return to the United States (or death abroad)
 * is not documented, so the exile is recorded as open-ended: in_exile_since
 * 1969 (year precision) with no end_of_exile — which, under the model guard,
 * leaves the duration unknown rather than counting to the present.
 *
 * Also re-saves every other case with an open-ended exile (in_exile_since set,
 * no end_of_exile) so the new guard recomputes in_exile_for_days for them:
 * genuinely still-exiled prisoners keep their count-to-today, historical
 * unknowns go to null.
 *
 * Idempotent.
 */
final class SetMackExile extends Command
{
    protected $signature = 'prisoners:set-mack-exile';

    protected $description = 'Model Larry Mack (Panther 21) as an exile; recompute open-ended exile durations';

    public function handle(): int
    {
        // ---- Larry Mack ----
        $p = Prisoner::withoutGlobalScopes()->where('slug', 'larry-mack')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'Larry Mack')->first();

        if ($p) {
            $p->in_exile = true;
            $p->currently_in_exile = false; // status after 1971 acquittal undocumented — keep off "currently active" lists
            $p->released = false;           // never imprisoned, so not "released"
            $p->in_custody = false;
            $p->description = "Larry Mack was the section leader of the Queens chapter of the Black Panther Party at the time of the April 2, 1969 Panther 21 raids. He went underground rather than surrender and fled the United States, joining Eldridge Cleaver's Black Panther International Section in Algiers alongside Donald \"D.C.\" Cox. Tried in absentia as a fugitive defendant, he was acquitted with all the Panther 21 defendants on May 13, 1971, after the jury deliberated less than an hour. He was never in U.S. custody; whether or when he returned to the United States is not documented.";
            $p->save();

            $case = $p->cases()->first();
            if ($case) {
                // He was never incarcerated — the stored date was the raid date.
                $case->incarceration_date = null;
                $case->release_date = null;
                $case->setPartialDate('in_exile_since', 1969); // fled after the April 1969 raid; in Algiers by ~1970
                $case->end_of_exile = null;                    // return/death abroad undocumented
                $case->save();
                $this->info('Larry Mack: in exile since 1969, end unknown (duration not counted).');
            }
        } else {
            $this->warn('No Larry Mack record found.');
        }

        // ---- Recompute all other open-ended exile durations under the new guard ----
        $fixed = 0;
        PrisonerCase::whereNotNull('in_exile_since')
            ->whereNull('end_of_exile')
            ->get()
            ->each(function (PrisonerCase $case) use (&$fixed) {
                $before = $case->in_exile_for_days;
                $case->save(); // hook recomputes under the currently_in_exile guard
                if ($case->refresh()->in_exile_for_days !== $before) {
                    $fixed++;
                }
            });

        $this->info("Recomputed open-ended exile durations ({$fixed} changed).");

        return self::SUCCESS;
    }
}
