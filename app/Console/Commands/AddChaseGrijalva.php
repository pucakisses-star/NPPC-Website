<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Add Chase Payton Grijalva — 30-year-old former U.S. Army mechanic
 * from Portland, OR, sentenced March 4, 2026 in Multnomah County to
 * 24 months in Oregon state prison plus 5 years probation and
 * $55,548 in restitution for shooting out 17 Portland red-light /
 * speed-enforcement cameras across 12 incidents in May–June 2024.
 *
 * Surfaced by a 2026-03-09 @JasonBassler1 tweet framing the case as
 * disproportionate punishment for an anti-surveillance protest and
 * noting that officers used force during his arrest.
 *
 * Idempotent — re-runs report "already exists."
 */
final class AddChaseGrijalva extends Command {
    protected $signature = 'prisoners:add-chase-grijalva';
    protected $description = 'Add Chase Grijalva (Portland traffic-camera shootings, 2026 sentencing)';

    public function handle(): int {
        if (Prisoner::where('slug', 'chase-grijalva')->orWhere('name', 'Chase Grijalva')->exists()) {
            $this->warn('Chase Grijalva already in DB — skipping.');
            return self::SUCCESS;
        }

        DB::transaction(function () {
            $institution = Institution::firstOrCreate(
                ['name' => 'Oregon Department of Corrections'],
                ['city' => 'Salem', 'state' => 'Oregon']
            );

            $prisoner = Prisoner::create([
                'name'         => 'Chase Grijalva',
                'first_name'   => 'Chase',
                'middle_name'  => 'Payton',
                'last_name'    => 'Grijalva',
                'gender'       => 'Male',
                'state'        => 'Oregon',
                'era'          => '2020s',
                'ideologies'   => ['Anti-surveillance'],
                'affiliation'  => ['U.S. Army (veteran)'],
                'in_custody'   => true,
                'released'     => false,
                'description'  => "Chase Payton Grijalva is a 30-year-old former U.S. Army mechanic from Portland, Oregon. Over a roughly two-week stretch in late May and early June 2024 he shot out 17 city red-light and speed-enforcement cameras across 12 separate incidents, beginning May 27, 2024 at NE Martin Luther King Jr. Boulevard and Lloyd Boulevard. He was arrested on June 10, 2024 after a witness identified him near SE 122nd and SE Stark; after a Miranda warning he admitted to police that he had been shooting cameras for more than a week.\n\nOn Wednesday, March 4, 2026, Multnomah County Circuit Court Judge Andrew Lavin sentenced him to 24 months in Oregon state prison followed by five years of probation, with an additional five-year suspended prison term contingent on probation compliance, and ordered him to pay \$55,548 in restitution. Total camera damage was estimated at roughly \$500,000. He pleaded guilty to one count of unlawful use of a firearm, two counts of unlawful use of a weapon, and one count of first-degree criminal mischief.\n\nSupporters frame the case as a disproportionate punishment for a sustained act of civil disobedience against municipal surveillance infrastructure and have flagged that arresting officers used force during the June 2024 arrest. Critics view it as a straightforward case of repeated firearm-related criminal mischief. The case is logged here because of the anti-surveillance protest framing in the public discussion; the entry is neutral and does not endorse either characterization.",
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $institution->id,
                'charges'            => 'Unlawful use of a firearm (1 count); unlawful use of a weapon (2 counts); first-degree criminal mischief — shooting out 17 Portland red-light/speed-enforcement cameras across 12 incidents, May–June 2024.',
                'arrest_date'        => '2024-06-10',
                'sentenced_date'     => '2026-03-04',
                'incarceration_date' => '2026-03-04',
                'release_date'       => '2028-03-04',
                'plead'              => 'Guilty',
                'convicted'          => 'Yes — 2026, Multnomah County Circuit Court',
                'judge'              => 'Hon. Andrew Lavin (Multnomah County Circuit Court)',
                'sentence'           => '24 months Oregon state prison + 5 years probation + 5 years suspended; $55,548 restitution',
            ]);
        });

        $this->info('Added Chase Grijalva.');

        return self::SUCCESS;
    }
}
