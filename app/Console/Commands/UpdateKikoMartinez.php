<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Enriches Francisco "Kiko" Martínez's record from James Barrera's NACCS paper
 * "The Political Repression of a Chicano Movement Activist" (2004): adds his
 * birthdate (Nov 26, 1946, Alamosa, CO), flags his roughly seven-year exile in
 * Mexico (October 1973 – September 3, 1980), and fills the case timeline — the
 * January 15, 1973 Scottsbluff arrest, the October 1973 Denver "package bomb"
 * indictment, the September 3, 1980 capture at Nogales under an alias, the
 * October 24, 1980 release on bond, and the 1983 clearing of the bombing charges
 * after judicial misconduct. Upsert/idempotent — finds the existing record and
 * sets these fields authoritatively.
 */
final class UpdateKikoMartinez extends Command
{
    protected $signature = 'prisoners:update-kiko-martinez';

    protected $description = 'Update Francisco "Kiko" Martínez from the Barrera NACCS paper (birthdate, exile, case dates)';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'like', '%Kiko%')
            ->where('name', 'like', '%Mart%nez%')
            ->first();

        if (! $prisoner) {
            $this->warn('Kiko Martínez record not found, skipping.');

            return self::SUCCESS;
        }

        $description = 'Francisco Eugenio "Kiko" Martínez (born November 26, 1946 in Alamosa, Colorado) was a '
            .'Chicano-movement attorney from southern Colorado who became the target of one of the era\'s most '
            .'protracted federal prosecutions. A lawyer for students, workers, prisoners, and Crusade for Justice '
            .'activists, he was arrested near Scottsbluff in January 1973 and then indicted in Denver in October 1973 '
            .'for allegedly mailing package bombs to a Denver policewoman, Carol Hogue — charges his supporters and '
            .'later court findings characterized as a political frame-up. Fearing an unjust prosecution, he exiled '
            .'himself to Mexico and lived underground for roughly seven years. He was captured re-entering the United '
            .'States at Nogales, Arizona on September 3, 1980 under the alias José Reynoso Díaz, and released on a '
            .'$400,000 bond that October. Across a series of early-1980s trials — his first collapsing into a mistrial '
            .'after the judge was found to have secretly met with prosecutors — he was cleared of the bombing charges '
            .'by 1983 and convicted only on a document offense, after which he resumed the practice of law. His ordeal '
            .'became a touchstone of Chicano-movement charges of political repression.';

        DB::transaction(function () use ($prisoner, $description) {
            $prisoner->birthdate = '1946-11-26';
            $prisoner->description = $description;
            $prisoner->in_exile = true;
            $prisoner->currently_in_exile = false;
            $prisoner->in_custody = false;
            $prisoner->released = true;
            $prisoner->save();

            $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->charges = 'Arrested January 15, 1973 near Scottsbluff for an alleged explosive device, then indicted in Denver in October 1973 for allegedly mailing three package bombs to Denver policewoman Carol Hogue — charges widely held to be a political frame-up amid the repression of the Crusade for Justice. On returning from exile he also faced passport/document charges for re-entering under an alias.';
            $case->convicted = 'Cleared of the bombing charges by 1983 — his first trial collapsed in a mistrial after the judge was found to have secretly met with prosecutors and government witnesses, and the remaining counts were dismissed or ended in acquittal. He was convicted only on a document offense.';
            $case->arrest_date = '1973-01-15';
            $case->in_exile_since = '1973-10-01';
            $case->end_of_exile = '1980-09-03';
            $case->sentence = 'Self-exiled to Mexico in 1973 and lived underground for about seven years; captured re-entering the U.S. at Nogales, Arizona on September 3, 1980 under the alias José Reynoso Díaz, and released on a $400,000 bond on October 24, 1980. He served time on the document conviction before resuming the practice of law.';
            $case->save();
        });

        $case = $prisoner->cases()->first();
        $this->info("Updated {$prisoner->name}: birthdate set, exile flagged (in_exile_for_days={$case->in_exile_for_days}).");

        return self::SUCCESS;
    }
}
