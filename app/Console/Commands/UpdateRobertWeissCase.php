<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Enriches the case summary for Iraq War resister Robert Weiss from the Courage
 * to Resist report "Objector Robert Weiss Released After 7-Month Sentence" (and
 * the corroborating War Resisters' International coverage). Adds the verified
 * narrative — the early-December 2007 denial of his conscientious-objector
 * application, his non-combatant deployment to FOB Prosperity in Iraq, his
 * refusal to board the December 22, 2007 return flight, the guilty pleas to
 * desertion and missing movement under a pre-trial agreement, the May 14, 2008
 * court-martial at Rose Barracks (Vilseck, Germany) before military judge
 * Masters, the full sentence (7 months' confinement, Bad Conduct Discharge,
 * reduction to the lowest enlisted rank, and forfeiture of $898/month for seven
 * months), confinement at Coleman Barracks (Mannheim), and his November 9, 2008
 * release. Corrects the previously placeholder incarceration_date (2008-02-11) to
 * the verified sentencing date so the time-served figure is accurate. Upsert /
 * idempotent — finds the existing record and sets these fields authoritatively.
 */
final class UpdateRobertWeissCase extends Command
{
    protected $signature = 'prisoners:update-robert-weiss-case';

    protected $description = 'Update Robert Weiss\'s case summary from the Courage to Resist report';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'Robert Weiss')
            ->first();

        if (! $prisoner) {
            $this->warn('Robert Weiss record not found, skipping.');

            return self::SUCCESS;
        }

        $description = 'Robert Weiss was a U.S. Army soldier and conscientious objector who refused to redeploy '
            .'during the Iraq War. While serving on non-combatant duties at Forward Operating Base Prosperity in Iraq, '
            .'he learned in early December 2007 that the Army had denied his application for conscientious-objector '
            .'status and an honorable discharge. Rather than board his December 22, 2007 return flight to Iraq at the '
            .'end of a leave — and the combat-patrol duties he expected to face on arrival — he stayed away. He was '
            .'charged with desertion and missing movement, pleaded guilty under a pre-trial agreement, and was '
            .'court-martialed at Rose Barracks in Vilseck, Germany on May 14, 2008. Military judge Masters sentenced '
            .'him to seven months\' confinement, a Bad Conduct Discharge, reduction to the lowest enlisted rank, and '
            .'forfeiture of $898 per month for seven months. He served his sentence at the U.S. Military Detention '
            .'Facility Europe at Coleman Barracks in Mannheim, Germany, and was released on November 9, 2008.';

        DB::transaction(function () use ($prisoner, $description) {
            $prisoner->description = $description;
            $prisoner->in_custody = false;
            $prisoner->released = true;
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if (! $case) {
                $this->warn('No case found for Robert Weiss; nothing to update.');

                return;
            }

            $case->charges = 'Desertion and missing movement, in violation of the UCMJ. After the Army denied his '
                .'application for conscientious-objector status and an honorable discharge in early December 2007, '
                .'Weiss — then deployed on non-combatant duties at Forward Operating Base Prosperity in Iraq — refused '
                .'to board his December 22, 2007 return flight to Iraq while on leave, declining the combat-patrol '
                .'duties he expected to be assigned on arrival.';
            $case->convicted = 'Pleaded guilty to desertion and missing movement under a pre-trial agreement (guilty '
                .'pleas, no out-of-country witnesses, and sentencing by a military judge rather than a panel). '
                .'Court-martialed at Rose Barracks in Vilseck, Germany on May 14, 2008.';
            $case->sentence = 'Military judge Masters sentenced him to seven months\' confinement, a Bad Conduct '
                .'Discharge, reduction to the lowest enlisted rank, and forfeiture of $898 per month for seven months. '
                .'He served the confinement at the U.S. Military Detention Facility Europe at Coleman Barracks in '
                .'Mannheim, Germany, and was released on November 9, 2008.';
            $case->incarceration_date = '2008-05-14';
            $case->release_date = '2008-11-09';
            $case->save();
        });

        $case = $prisoner->cases()->first();
        $this->info("Updated {$prisoner->name}: case summary enriched (imprisoned_for_days={$case->imprisoned_for_days}).");

        return self::SUCCESS;
    }
}
