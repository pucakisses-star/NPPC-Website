<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Two-part cleanup for the pro-life FACE Act activists pardoned by President
 * Trump on January 23, 2025:
 *
 *  1) Adds the three not yet in the database — Fr. Fidelis Moscinski, Joel
 *     Curry, and Justin Phillips.
 *  2) Fixes the release dates for pardoned activists whose case had no release
 *     date (so their "time in jail" was still counting up) or a clearly wrong
 *     one, setting the release to the pardon date 2025-01-23 and marking them
 *     released / not in custody.
 *
 * Idempotent: prisoner:add refuses duplicates; the release fix is safe to re-run.
 */
final class AddFaceActMissingAndFixReleases extends Command
{
    protected $signature = 'prisoners:add-face-act-missing-fix-releases';

    protected $description = 'Add 3 missing FACE Act pardonees and fix pardoned-activist release dates';

    private const PARDON_DATE = '2025-01-23';

    /** Pardoned activists whose release date is missing or wrong — set to the pardon date. */
    private const FIX_RELEASE = [
        'eva-edl', 'chester-gallagher', 'william-goodman', 'dennis-green',
        'paula-harlow', 'heather-idoni', 'paul-place', 'paul-vaughn',
        'eva-zastrow', 'james-zastrow', 'jay-smith', 'coleman-boyd',
    ];

    public function handle(): int
    {
        // ---- 1) Add the three missing pardonees ----
        $payloads = [
            [
                'name' => 'Fidelis Moscinski',
                'first_name' => 'Fidelis',
                'last_name' => 'Moscinski',
                'aka' => 'Father Fidelis Moscinski',
                'description' => 'Father Fidelis Moscinski is a Franciscan Friar of the Renewal (CFR) from the Bronx, New York, a Catholic priest repeatedly jailed over the years for pro-life "rescue" actions blocking abortion-clinic entrances. In 2023 he received a six-month federal sentence for a FACE Act violation at a clinic in Hempstead, New York. He was among the 23 pro-life activists pardoned by President Trump on January 23, 2025.',
                'state' => 'New York',
                'gender' => 'Male',
                'ideologies' => ['Pro-life activism', 'Catholicism'],
                'affiliation' => ['Franciscan Friars of the Renewal', 'Red Rose Rescue'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'FACE Act violation for blocking access to an abortion clinic in Hempstead, New York, as part of a "Red Rose Rescue." Sentenced in 2023 to six months in federal prison; pardoned January 23, 2025.',
                    'convicted' => 'Convicted of a FACE Act violation (2023)',
                    'sentence' => 'Six months in federal prison (2023).',
                    'imprisoned_for_days' => 180,
                    'release_date' => self::PARDON_DATE,
                ]],
            ],
            [
                'name' => 'Joel Curry',
                'first_name' => 'Joel',
                'last_name' => 'Curry',
                'description' => 'Joel Curry, of Norton Shores, Michigan, was one of seven pro-life activists convicted in August 2024 of a federal civil-rights conspiracy and a FACE Act violation for a blockade of an abortion clinic in Sterling Heights, Michigan, on August 27, 2020, which he livestreamed. Sentencing was still pending when President Trump pardoned him, with the other FACE Act defendants, on January 23, 2025, so he served no prison term.',
                'state' => 'Michigan',
                'gender' => 'Male',
                'ideologies' => ['Pro-life activism'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Conspiracy against rights (18 U.S.C. § 241) and a FACE Act violation for blockading an abortion clinic in Sterling Heights, Michigan, on August 27, 2020. Convicted August 2024; pardoned January 23, 2025 before sentencing.',
                    'convicted' => 'Convicted by a federal jury, August 2024',
                    'sentence' => 'Pardoned before sentencing (January 23, 2025); served no prison term.',
                ]],
            ],
            [
                'name' => 'Justin Phillips',
                'first_name' => 'Justin',
                'last_name' => 'Phillips',
                'description' => 'Justin Phillips, of Flint, Michigan, was one of seven pro-life activists convicted in August 2024 of a federal civil-rights conspiracy and a FACE Act violation for a blockade of an abortion clinic in Sterling Heights, Michigan, on August 27, 2020. Sentencing was still pending when President Trump pardoned him, with the other FACE Act defendants, on January 23, 2025, so he served no prison term.',
                'state' => 'Michigan',
                'gender' => 'Male',
                'ideologies' => ['Pro-life activism'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Conspiracy against rights (18 U.S.C. § 241) and a FACE Act violation for blockading an abortion clinic in Sterling Heights, Michigan, on August 27, 2020. Convicted August 2024; pardoned January 23, 2025 before sentencing.',
                    'convicted' => 'Convicted by a federal jury, August 2024',
                    'sentence' => 'Pardoned before sentencing (January 23, 2025); served no prison term.',
                ]],
            ],
        ];

        foreach ($payloads as $payload) {
            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
            if (! $prisoner) {
                continue;
            }
            $prisoner->in_custody = false;
            $prisoner->released = true;
            $prisoner->save();

            $caseData = $payload['cases'][0];
            $case = $prisoner->cases()->first();
            if ($case) {
                foreach (['charges', 'convicted', 'sentence', 'imprisoned_for_days', 'release_date'] as $f) {
                    if (! empty($caseData[$f])) {
                        $case->{$f} = $caseData[$f];
                    }
                }
                $case->save();
            }
        }

        // ---- 2) Fix release dates on already-pardoned records ----
        $fixed = 0;
        foreach (self::FIX_RELEASE as $slug) {
            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("  (release fix) no prisoner '{$slug}'");

                continue;
            }
            $prisoner->released = true;
            $prisoner->in_custody = false;
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case) {
                $case->release_date = self::PARDON_DATE;
                $case->save();
            }
            $this->info("Fixed release date (pardon 2025-01-23): {$prisoner->name}");
            $fixed++;
        }

        $this->info("\nDone. Added the 3 missing pardonees; fixed release on {$fixed} record(s).");

        return self::SUCCESS;
    }
}
