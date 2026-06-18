<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Adds Ciaron O'Reilly's U.S. case — the 1991 ANZUS Plowshares disarmament
 * action at Griffiss Air Force Base, for which he was imprisoned in the
 * United States as a co-defendant of Moana Cole, Susan Frankel and Bill
 * Streit. Modeled as two distinct custody periods (pre-trial detention, then
 * the sentence) so the months he was free on bail are not counted or shown
 * as imprisonment, matching how his co-defendant Moana Cole is recorded.
 *
 * Leaves his existing biography untouched (it also covers the later 2003
 * Pitstop Ploughshares action in Ireland, which is not a U.S. case).
 * Idempotent: rebuilds the two cases on each run.
 */
final class AddCiaronOReillyCase extends Command
{
    protected $signature = 'prisoners:add-ciaron-oreilly-case';

    protected $description = "Add Ciaron O'Reilly's ANZUS Plowshares case (two custody periods)";

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where(function ($q) {
                $q->where('slug', 'ciaron-oreilly')->orWhere('name', "Ciaron O'Reilly");
            })
            ->first();

        if (! $prisoner) {
            $this->error('Ciaron O\'Reilly not found (slug "ciaron-oreilly" / name "Ciaron O\'Reilly").');

            return self::FAILURE;
        }

        $prisoner->gender = 'Male';
        $prisoner->released = true;
        $prisoner->in_custody = false;
        $prisoner->save();

        // Two distinct custody periods (same prosecution as co-defendant Moana
        // Cole) so the months free on bail between pre-trial release and
        // serving the sentence are not counted/shown as imprisonment.
        $prisoner->cases()->delete();

        $bop = Institution::firstOrCreate(['name' => 'Federal Bureau of Prisons']);

        // 1) Pre-trial detention: arrested Jan 1, 1991; released on bail Mar 6, 1991.
        PrisonerCase::create([
            'prisoner_id' => $prisoner->id,
            'charges' => 'Detained pending trial — the ANZUS Plowshares disarmament action at Griffiss Air Force Base, Rome, New York',
            'arrest_date' => '1991-01-01',
            'incarceration_date' => '1991-01-01',
            'release_date' => '1991-03-06',
            'sentence' => 'Held about two months after the action, then released pre-trial on bail on March 6, 1991',
        ]);

        // 2) Conviction & sentence: began serving ~Aug 20, 1991; out of BOP custody June 1992.
        PrisonerCase::create([
            'prisoner_id' => $prisoner->id,
            'institution_id' => $bop->id,
            'charges' => 'Conspiracy and destruction of U.S. government property (federal sabotage / depredation) — the ANZUS Plowshares disarmament action at Griffiss Air Force Base, Rome, New York',
            'sentenced_date' => '1991-08-20',
            'incarceration_date' => '1991-08-20',
            'release_date' => '1992-06-15',
            'convicted' => 'Yes — convicted by a jury in Syracuse, May 1991',
            'sentence' => 'Twelve months in prison and $1,800 restitution; served about ten months and released from federal (BOP) custody in June 1992, then freed on bail pending a deportation hearing and deported to Australia',
        ]);

        $this->info("Added Ciaron O'Reilly's case (2 custody periods).");

        return self::SUCCESS;
    }
}
