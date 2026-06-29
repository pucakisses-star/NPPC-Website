<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds two members of Jefferson Davis's party captured at the end of the Civil
 * War and held as political prisoners at Fort Delaware:
 *
 *   - Col. Francis R. Lubbock — former Governor of Texas and aide-de-camp to
 *     Davis; released November 23, 1865.
 *   - Burton N. Harrison — Davis's private secretary; released January 16, 1866.
 *
 * Both were captured with the presidential party in Georgia in May 1865.
 * Idempotent and update-capable: prisoner:add creates a missing record, then
 * this command backfills the status flags + case dates so a re-run enriches a
 * record created by an earlier run.
 */
final class AddLubbockHarrison extends Command
{
    protected $signature = 'prisoner:add-lubbock-harrison';

    protected $description = 'Add Col. Francis R. Lubbock and Burton N. Harrison (Fort Delaware political prisoners)';

    public function handle(): int
    {
        $payloads = [
            [
                'name' => 'Francis R. Lubbock',
                'first_name' => 'Francis',
                'last_name' => 'Lubbock',
                'aka' => 'Francis Richard Lubbock',
                'description' => "Francis Richard Lubbock (1815–1905) was the ninth Governor of Texas (1861–1863). After leaving office he joined the Confederate Army and in August 1864 was appointed a colonel and aide-de-camp to Confederate President Jefferson Davis. He fled Richmond with Davis at the end of the war and was captured with the presidential party in Georgia in May 1865. Lubbock was imprisoned at Fort Delaware as a political prisoner — held for a time in solitary confinement — for roughly eight months, and was released on November 23, 1865. He later returned to Texas and served as State Treasurer from 1878 to 1891.",
                'birthdate' => '1815-10-16',
                'death_date' => '1905-06-22',
                'state' => 'Delaware',
                'gender' => 'Male',
                'ideologies' => ['Confederate States of America', "States' rights"],
                'affiliation' => ['Confederate States Army', 'Government of Texas'],
                'era' => '1860s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Fort Delaware',
                    'institution_city' => 'Delaware City',
                    'institution_state' => 'Delaware',
                    'charges' => "Captured with Jefferson Davis's presidential party in Georgia in May 1865 and held as a political prisoner. A former Governor of Texas and colonel/aide-de-camp to Davis.",
                    'arrest_date' => '1865-05-10',
                    'incarceration_date' => '1865-05-10',
                    'release_date' => '1865-11-23',
                    'convicted' => 'Held without trial as a political prisoner; later paroled.',
                    'sentence' => "About eight months' military imprisonment (partly in solitary confinement); released November 23, 1865.",
                    'imprisoned_for_days' => 197,
                ]],
            ],
            [
                'name' => 'Burton N. Harrison',
                'first_name' => 'Burton',
                'middle_name' => 'Norvell',
                'last_name' => 'Harrison',
                'aka' => 'Burton Norvell Harrison',
                'description' => "Burton Norvell Harrison (1838–1904) was a lawyer who served as private secretary to Confederate President Jefferson Davis from 1862. He was captured with Davis and Varina Davis in Georgia in May 1865, held briefly at the Old Capitol Prison in Washington, and then imprisoned at Fort Delaware as a political prisoner, where he resumed his legal studies. He was released on January 16, 1866. Harrison afterward settled in New York City, where he practiced law and married the writer Constance Cary.",
                'birthdate' => '1838-07-14',
                'death_date' => '1904-03-29',
                'state' => 'Delaware',
                'gender' => 'Male',
                'ideologies' => ['Confederate States of America'],
                'affiliation' => ['Confederate States government'],
                'era' => '1860s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Fort Delaware',
                    'institution_city' => 'Delaware City',
                    'institution_state' => 'Delaware',
                    'charges' => "Private secretary to Confederate President Jefferson Davis; captured with Davis's party in Georgia in May 1865 and held as a political prisoner (briefly at the Old Capitol Prison, then Fort Delaware).",
                    'arrest_date' => '1865-05-10',
                    'incarceration_date' => '1865-05-10',
                    'release_date' => '1866-01-16',
                    'convicted' => 'Held without trial as a political prisoner.',
                    'sentence' => "About eight months' imprisonment; released January 16, 1866.",
                    'imprisoned_for_days' => 251,
                ]],
            ],
        ];

        $boolFlags = ['in_custody', 'released'];
        $caseFields = ['charges', 'arrest_date', 'incarceration_date', 'release_date', 'convicted', 'sentence', 'imprisoned_for_days'];

        foreach ($payloads as $payload) {
            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
            if (! $prisoner) {
                continue;
            }
            foreach ($boolFlags as $flag) {
                if (array_key_exists($flag, $payload)) {
                    $prisoner->{$flag} = $payload[$flag];
                }
            }
            foreach (['birthdate', 'death_date'] as $f) {
                if (! empty($payload[$f])) {
                    $prisoner->{$f} = $payload[$f];
                }
            }
            $prisoner->save();

            $caseData = $payload['cases'][0] ?? null;
            $case = $prisoner->cases()->first();
            if ($caseData && $case) {
                foreach ($caseFields as $f) {
                    if (! empty($caseData[$f])) {
                        $case->{$f} = $caseData[$f];
                    }
                }
                $case->save();
            }
        }

        $this->info("\nDone. Lubbock (released 1865-11-23) and Harrison (released 1866-01-16) ensured.");

        return self::SUCCESS;
    }
}
