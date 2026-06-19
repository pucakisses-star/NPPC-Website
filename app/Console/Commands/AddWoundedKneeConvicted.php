<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds the rank-and-file defendants who were CONVICTED (affirmed on appeal)
 * for the 1973 Wounded Knee occupation / Custer courthouse protest but were
 * missing from the database. Per the user's decision these are included even
 * though most drew probation/suspended sentences or their sentences are not
 * recorded — they were prosecuted and convicted for the protest activity.
 * Each record states the sentence reality honestly. Idempotent.
 *
 *  - Stanley Holder            — Cedar Rapids postal-inspector case (probation)
 *  - Robert High Eagle         — Custer courthouse riot/arson (with Sarah Bad
 *                                Heart Bull); sentence undocumented
 *  - Geneva Red Feather, Joseph Bill, Sioux Casper, Christopher Oliver Land,
 *    Martina White Bear        — Civil Disorder Act convictions, affirmed
 *                                (US v. Red Feather, 541 F.2d 1275); sentences
 *                                not in the appellate record
 */
final class AddWoundedKneeConvicted extends Command
{
    protected $signature = 'prisoners:add-wounded-knee-convicted';

    protected $description = 'Add convicted (mostly probation) Wounded Knee/Custer defendants missing from the DB';

    public function handle(): int
    {
        $added = 0;
        $skipped = 0;

        foreach ($this->records() as $r) {
            if (Prisoner::withUnderReview()->where('name', $r['name'])->exists()) {
                $this->warn("Exists, skipping: {$r['name']}");
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($r) {
                $cases = $r['cases'] ?? [];
                unset($r['cases']);
                $prisoner = Prisoner::create($r);
                foreach ($cases as $c) {
                    $c['prisoner_id'] = $prisoner->id;
                    PrisonerCase::create($c);
                }
            });

            $this->info("Added: {$r['name']}");
            $added++;
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }

    private function records(): array
    {
        // Red Feather "civil disorder" co-defendants share a charge/bio template.
        $redFeather = function (string $name, string $first, string $last, ?string $gender) {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => $gender,
                'race' => 'Native American',
                'state' => 'South Dakota',
                'era' => '1970s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['American Indian Movement'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was one of five defendants convicted of attempting to interfere with U.S. Marshals and FBI agents during the 1973 occupation of Wounded Knee — bringing weapons and ammunition through the federal perimeter — in violation of the Civil Disorder Act. The conviction was affirmed by the Eighth Circuit (United States v. Red Feather, 541 F.2d 1275, 1976); the sentence is not stated in the court record.",
                'cases' => [[
                    'charges' => 'Interfering with U.S. Marshals and FBI agents during a civil disorder — bringing weapons/ammunition through the federal perimeter at the 1973 Wounded Knee occupation (18 U.S.C. § 231(a)(3))',
                    'convicted' => 'Convicted; conviction affirmed (United States v. Red Feather, 541 F.2d 1275, 8th Cir. 1976)',
                    'sentence' => 'Not recorded in the appellate record',
                ]],
            ];
        };

        return [
            [
                'name' => 'Stanley Holder',
                'first_name' => 'Stanley',
                'middle_name' => 'Richard',
                'last_name' => 'Holder',
                'aka' => 'Stan Holder',
                'gender' => 'Male',
                'race' => 'Native American',
                'state' => 'Oklahoma',
                'era' => '1970s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['American Indian Movement'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "Stanley Richard Holder was a Wichita Vietnam combat veteran who served as the American Indian Movement's chief of security during the 1973 occupation of Wounded Knee. Tried at Cedar Rapids, Iowa alongside Carter Camp and Leonard Crow Dog, he was convicted of aiding and abetting the robbery of a U.S. postal inspector's revolver taken during the occupation. The conviction was affirmed on appeal (United States v. Holder, 566 F.2d 617, 1977), though his five-year sentence was suspended in favor of three years' probation. (Note: not to be confused with the unrelated British nurse of the same name.)",
                'cases' => [[
                    'charges' => "Aiding and abetting the robbery of a U.S. postal inspector's revolver during the 1973 Wounded Knee occupation (tried at Cedar Rapids, Iowa, with Carter Camp and Leonard Crow Dog)",
                    'convicted' => 'Convicted 1975; conviction affirmed (United States v. Holder, 566 F.2d 617, 8th Cir. 1977)',
                    'sentence' => "Five-year sentence suspended; three years' probation (no prison time)",
                ]],
            ],
            [
                'name' => 'Robert High Eagle',
                'first_name' => 'Robert',
                'last_name' => 'High Eagle',
                'gender' => 'Male',
                'race' => 'Native American',
                'state' => 'South Dakota',
                'era' => '1970s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['American Indian Movement'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "Robert High Eagle was a co-defendant of Sarah Bad Heart Bull, convicted of \"riot where arson was committed\" for the American Indian Movement's February 6, 1973 protest at the Custer County Courthouse in South Dakota — a protest sparked by the lenient manslaughter charge brought against the white man who killed Wesley Bad Heart Bull. The conviction was affirmed by the South Dakota Supreme Court (State v. Bad Heart Bull, 257 N.W.2d 715, 1977); his individual sentence is not documented in available sources.",
                'cases' => [[
                    'charges' => 'Riot where arson was committed — the February 6, 1973 AIM protest at the Custer County Courthouse, SD (co-defendant of Sarah Bad Heart Bull)',
                    'convicted' => 'Convicted; conviction affirmed (State v. Bad Heart Bull, 257 N.W.2d 715, S.D. 1977)',
                    'sentence' => 'Not documented',
                ]],
            ],
            $redFeather('Geneva Red Feather', 'Geneva', 'Red Feather', 'Female'),
            $redFeather('Joseph Bill', 'Joseph', 'Bill', 'Male'),
            $redFeather('Sioux Casper', 'Sioux', 'Casper', null),
            $redFeather('Christopher Oliver Land', 'Christopher', 'Land', 'Male'),
            $redFeather('Martina White Bear', 'Martina', 'White Bear', 'Female'),
        ];
    }
}
