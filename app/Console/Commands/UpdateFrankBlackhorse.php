<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Models Frank Blackhorse's timeline as exile → brief incarceration → exile,
 * built from primary sources (8th Cir. opinion 568 F.2d 555; attorney Michael
 * Kuzma's sworn 2012 FOIA complaint) and the Leonard Peltier Defense Committee
 * record:
 *
 *   - Exile #1: fled into Canada around November 1975 (a fugitive since failing
 *     to appear for his Wounded Knee trial on May 19, 1975, and sought after the
 *     June 26, 1975 Pine Ridge shootout) until his February 6, 1976 capture there
 *     — i.e. his time in Canada from the border crossing to the arrest.
 *   - Incarceration: arrested with Leonard Peltier near Hinton, Alberta on
 *     February 6, 1976 and held at the Oakalla Prison Farm. Unlike Peltier (who
 *     was extradited Dec 18, 1976), Blackhorse was held only briefly — reportedly
 *     booked on a minor charge and released — and was never extradited. His exact
 *     release date is undocumented; ~February 9, 1976 (the reported dropping of
 *     his Canadian holding charges) is used as an approximate boundary.
 *   - Exile #2: remained a free man in Canada under the standing U.S. indictment
 *     from his 1976 release to the present (reported "free after lengthy court
 *     battles" by January 1978; last known in Edmonton, Alberta).
 *
 * Two cases carry the two exile periods (one also carries the brief detention).
 * in_exile_since is set explicitly on both so the saving hook never auto-derives
 * it from a release_date. Idempotent: rebuilds his cases from scratch each run.
 */
final class UpdateFrankBlackhorse extends Command
{
    protected $signature = 'prisoners:update-frank-blackhorse';

    protected $description = 'Model Frank Blackhorse as exile → brief incarceration → ongoing exile';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'Frank Blackhorse')
            ->first();

        if (! $prisoner) {
            $this->warn('Frank Blackhorse record not found, skipping.');

            return self::SUCCESS;
        }

        $description = 'Frank Blackhorse — born Frank Leonard DeLuca and also known as Frank Black Horse — was an '
            .'American Indian Movement (AIM) figure prosecuted over the 1973 occupation of Wounded Knee, where he was '
            .'charged with wounding FBI Special Agent Curtis Fitzgerald on March 11, 1973 and released on a $10,000 '
            .'bond. After a federal grand jury in Sioux Falls indicted him (August 29, 1974) and he failed to appear '
            .'for trial (May 19, 1975), he became a fugitive; in the wake of the June 26, 1975 shootout at the Jumping '
            .'Bull compound on the Pine Ridge Reservation — in which FBI agents Jack Coler and Ronald Williams and AIM '
            .'member Joe Stuntz were killed — he went underground and, around November 1975, crossed into Canada with '
            .'Leonard Peltier and other AIM fugitives. The '
            .'RCMP captured Blackhorse and Peltier near Hinton, Alberta on February 6, 1976 and held them at the '
            .'Oakalla Prison Farm in British Columbia. Unlike Peltier — who was extradited to the United States on '
            .'December 18, 1976 — Blackhorse was held only briefly (reportedly booked on a minor charge and released '
            .'within days) and was never extradited. He remained a free man in Canada under the standing U.S. '
            .'indictment, was reported "free after lengthy court battles" by January 1978, and was last known to be '
            .'living in Edmonton, Alberta — in effect living in exile from U.S. prosecution ever since.';

        DB::transaction(function () use ($prisoner, $description) {
            $prisoner->description = $description;
            $prisoner->aka = 'Frank DeLuca; Frank Black Horse; Francis Douglas Blackhorse; Frank Leonard DeLuca';
            $prisoner->in_custody = false;
            $prisoner->released = true;
            $prisoner->in_exile = true;
            $prisoner->currently_in_exile = true;
            $prisoner->save();

            // Rebuild cases from scratch for idempotency.
            $prisoner->cases()->delete();

            $oakalla = Institution::firstOrCreate(
                ['name' => 'Oakalla Prison Farm'],
                ['city' => 'Burnaby', 'state' => 'British Columbia'],
            );

            // Case 1 — flight into exile, capture, and the brief 1976 detention.
            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $oakalla->id,
                'charges' => 'Assaulting a federal officer — charged with wounding FBI Special Agent Curtis Fitzgerald '
                    .'during the March 11, 1973 occupation of Wounded Knee, South Dakota (indicted August 29, 1974 in '
                    .'Sioux Falls). He was also named a suspect in the RESMURS investigation of the June 26, 1975 Pine '
                    .'Ridge shootout that killed FBI agents Jack Coler and Ronald Williams.',
                'convicted' => 'No. After failing to appear for trial on May 19, 1975 he fled into Canada with Leonard '
                    .'Peltier. The two were captured near Hinton, Alberta on February 6, 1976 and held at the Oakalla '
                    .'Prison Farm; Blackhorse was held only briefly (reportedly booked on a minor charge and released — '
                    .'his exact release date is undocumented, here approximated as February 9, 1976) and, unlike '
                    .'Peltier, was never extradited.',
                'arrest_date' => '1976-02-06',
                'in_exile_since' => '1975-11-01',
                'end_of_exile' => '1976-02-06',
                'incarceration_date' => '1976-02-06',
                'release_date' => '1976-02-09',
            ]);

            // Case 2 — ongoing exile in Canada, from his 1976 release to the present.
            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Following his 1976 release he remained in Canada as a fugitive under the standing U.S. '
                    .'indictment. He was never extradited and, by the best-documented accounts, has lived in exile in '
                    .'Canada ever since (last known in Edmonton, Alberta).',
                'in_exile_since' => '1976-02-09',
            ]);
        });

        $prisoner->refresh();
        $line = $prisoner->cases->map(fn ($c) => 'exile_days='.($c->in_exile_for_days ?? '—').' prison_days='.($c->imprisoned_for_days ?? '—'))->implode(' | ');
        $this->info("Updated {$prisoner->name}: {$line}; years_in_prison=".json_encode($prisoner->years_in_prison));

        return self::SUCCESS;
    }
}
