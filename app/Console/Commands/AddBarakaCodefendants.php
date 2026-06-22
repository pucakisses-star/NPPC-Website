<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Adds Barry Wynn and Charles McCray — the two co-defendants tried with the poet
 * LeRoi Jones (Amiri Baraka) for unlawful possession of weapons after their
 * arrest during the July 1967 Newark rebellion. All three were convicted in the
 * Essex County Court on November 6, 1967; the convictions were reversed on
 * appeal by the Appellate Division of the New Jersey Superior Court in 1969
 * (State v. Jones), which ordered a new trial.
 *
 * The record on them as individuals is thin — they appear almost entirely as
 * Baraka's co-defendants — so only the verifiable facts are stored: the joint
 * weapons charge, the July 1967 rebellion arrest, the November 6, 1967
 * conviction, and the 1969 reversal. No per-defendant sentence or custody dates
 * are recorded (not documented). Idempotent.
 */
final class AddBarakaCodefendants extends Command
{
    protected $signature = 'prisoners:add-baraka-codefendants';

    protected $description = "Add Barry Wynn and Charles McCray (LeRoi Jones's 1967 Newark weapons co-defendants)";

    public function handle(): int
    {
        $essex = Institution::firstOrCreate(
            ['name' => 'Essex County Jail'],
            ['city' => 'Newark', 'state' => 'New Jersey'],
        );

        $people = [
            ['Barry Wynn', 'Barry', 'Wynn'],
            ['Charles McCray', 'Charles', 'McCray'],
        ];

        foreach ($people as [$name, $first, $last]) {
            $fields = [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'state' => 'New Jersey',
                'era' => '1960s',
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was one of two co-defendants tried with the poet LeRoi Jones (Amiri Baraka) for unlawful possession of weapons following their arrest during the July 1967 Newark rebellion. All three were convicted in the Essex County Court on November 6, 1967; the convictions were reversed on appeal by the Appellate Division of the New Jersey Superior Court in 1969 (State v. Jones), which ordered a new trial.",
            ];

            $caseData = [
                'charges' => 'Unlawful possession of weapons (N.J.S. 2A:151-41) — arrested in the July 1967 Newark rebellion and tried with LeRoi Jones (Amiri Baraka)',
                'arrest_date' => '1967-07-14',
                'convicted' => 'Yes — convicted November 6, 1967 with LeRoi Jones; conviction reversed on appeal in 1969 (State v. Jones), new trial ordered',
                'institution_id' => $essex->id,
            ];

            $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();

            if ($prisoner) {
                $prisoner->fill($fields)->save();
                $case = $prisoner->cases()->first();
                if ($case) {
                    $case->fill($caseData)->save();
                } else {
                    $caseData['prisoner_id'] = $prisoner->id;
                    PrisonerCase::create($caseData);
                }
                $this->info("Updated {$name}.");
            } else {
                $prisoner = Prisoner::create($fields);
                $caseData['prisoner_id'] = $prisoner->id;
                PrisonerCase::create($caseData);
                $this->info("Added {$name} (slug: {$prisoner->slug}).");
            }
        }

        return self::SUCCESS;
    }
}
