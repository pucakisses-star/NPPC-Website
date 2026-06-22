<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Eleventh batch from reading The Black Panther — drawn from the late-1971 and
 * 1972 issues:
 *
 *  - Rev. Charles Koen and Leon Dent — leaders of the Black United Front of
 *    Cairo, Illinois, jailed on an assault frame-up from a 1968 police beating.
 *  - Fred Bell — a Dallas SNCC organizer re-sentenced to ten years on a bank-
 *    robbery frame-up before Judge William Wayne Justice.
 *  - The Richmond, California "hitch-hiker" frame-up: Artie Adanandus (shot in
 *    the back by police), Clarence Peters and Forrest Blade.
 *  - LaSaunders Hudson — a Memphis dry-cleaner who disarmed IRS agents in a tax
 *    protest and was charged with assault with intent to murder.
 *  - Tommy Atwood — charged with Ben Chavis and Marvin Patrick in the Wilmington,
 *    N.C. "Cumber" murder-conspiracy case.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers11Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-11';

    protected $description = 'Add Black Panther newspaper prisoners from the 1971-72 issues, batch 11';

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
                    if (! empty($c['institution_name'])) {
                        $inst = Institution::firstOrCreate(
                            ['name' => $c['institution_name']],
                            ['city' => $c['institution_city'] ?? null, 'state' => $c['institution_state'] ?? null],
                        );
                        $c['institution_id'] = $inst->id;
                    }
                    unset($c['institution_name'], $c['institution_city'], $c['institution_state']);
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
        // Richmond, CA "hitch-hiker" frame-up (shared case, Sept. 13, 1972).
        $richmond = function (string $name, string $first, string $last, string $descExtra, array $caseExtra = []): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was one of three young Black men who, on September 13, 1972, gave a ride to two white hitch-hikers heading from Berkeley to San Francisco; when the women later told Richmond, California police a story of kidnapping, the three were hunted down. Artie Adanandus was shot in the back by Richmond police, and Adanandus, Clarence Peters and Forrest Blade were charged with the attempted rape, kidnapping and robbery of the two women — charges The Black Panther reported as a racist frame-up.{$descExtra} Documented in The Black Panther (September 23, 1972).",
                'cases' => [array_merge([
                    'institution_state' => 'California',
                    'charges' => 'Attempted rape, kidnapping and robbery of two white hitch-hikers (Richmond, California, September 13, 1972 — reported as a frame-up)',
                    'arrest_date' => '1972-09-13',
                ], $caseExtra)],
            ];
        };

        return [
            [
                'name' => 'Charles Koen',
                'first_name' => 'Charles',
                'last_name' => 'Koen',
                'aka' => 'Rev. Charles Koen',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Illinois',
                'era' => '1970s',
                'ideologies' => ['Black liberation', 'Civil rights'],
                'affiliation' => ['Black United Front of Cairo'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'The Reverend Charles Koen was national chairman of the Black United Front of Cairo, Illinois, a hub of the long Black freedom struggle in that southern-Illinois town. His imprisonment stemmed from a September 1968 incident in which he and Brother Leon Dent were stopped over a defective brake light, arrested, and beaten by eight officers at the police station (Koen suffered head lacerations and broken bones in his hands); Koen was then charged with assault on a police officer and convicted. After his appeal was denied in 1971 he was sent to the St. Louis Workhouse to serve a six-month sentence, where he conducted a water fast in protest that left his weight down from 170 to 110 pounds before Judge David McMullan granted him parole. Documented in The Black Panther (September 25, 1971).',
                'cases' => [[
                    'institution_name' => 'St. Louis Workhouse',
                    'institution_city' => 'St. Louis',
                    'institution_state' => 'Missouri',
                    'charges' => 'Assault on a police officer (stemming from a September 1968 police beating of Koen and Leon Dent in Cairo, Illinois)',
                    'convicted' => 'Yes — convicted of assault; six-month sentence; paroled in 1971 after a protest water fast',
                    'sentence' => 'Six months',
                ]],
            ],
            [
                'name' => 'Leon Dent',
                'first_name' => 'Leon',
                'last_name' => 'Dent',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Illinois',
                'era' => '1970s',
                'ideologies' => ['Black liberation', 'Civil rights'],
                'affiliation' => ['Black United Front of Cairo'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Leon Dent was a companion of the Reverend Charles Koen in the Black United Front of Cairo, Illinois. In September 1968 he and Koen were stopped by police over a defective brake light, arrested, and beaten by eight officers at the station; police alleged that Dent had attacked them, the claim on which the assault charges against the two men rested. Documented in The Black Panther (September 25, 1971).',
                'cases' => [[
                    'institution_state' => 'Illinois',
                    'charges' => 'Assault on police officers (the September 1968 arrest and police beating in Cairo, Illinois)',
                ]],
            ],
            [
                'name' => 'Fred Bell',
                'first_name' => 'Fred',
                'last_name' => 'Bell',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Texas',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Student Nonviolent Coordinating Committee'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Fred Bell was a Texas community organizer (associated with the Dallas chapter of SNCC) whom The Black Panther described as railroaded to prison on a frame-up charge of aiding and abetting a bank robbery — a charge his supporters, the New Bois d\'Arc Patriots of Dallas, said was never formally made and which the convicting jury was not qualified to rule on. On July 21, 1972, after an appeals court had directed that his sentence be reduced to no more than six years, Judge William Steger (a recent Nixon appointee) instead re-sentenced him to ten years, reportedly calling Bell a "bad citizen." His case ran in the federal court of Judge William Wayne Justice. Documented in The Black Panther (August 5, 1972).',
                'cases' => [[
                    'institution_state' => 'Texas',
                    'charges' => 'Aiding and abetting a bank robbery (a frame-up, per The Black Panther; the charge was reportedly never formally made)',
                    'convicted' => 'Yes — re-sentenced July 21, 1972',
                    'sentence' => 'Ten years',
                ]],
            ],

            // ---- Richmond, CA hitch-hiker frame-up ----
            $richmond('Artie Adanandus', 'Artie', 'Adanandus',
                ' Adanandus, seventeen, was shot in the back by Richmond police as he fled and was hospitalized.',
                ['charges' => 'Attempted rape, kidnapping and robbery (Richmond, California, Sept. 13, 1972); shot in the back by police during the arrest']),
            $richmond('Clarence Peters', 'Clarence', 'Peters',
                ' Clarence Peters, eighteen, was held in the Richmond city jail on $5,000 bail.'),
            $richmond('Forrest Blade', 'Forrest', 'Blade',
                ' Forrest Blade, sixteen, was held without bail at the juvenile hall in Martinez.'),

            [
                'name' => 'LaSaunders Hudson',
                'first_name' => 'LaSaunders',
                'last_name' => 'Hudson',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Tennessee',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'LaSaunders Hudson was a Black dry-cleaning business owner (B.H.K. Cleaners) in Memphis, Tennessee. On June 7, 1972, when four armed IRS agents came to his shop demanding $197 in back taxes, Hudson — in a protest against the taxation of an oppressed people who had never been compensated for slavery — disarmed the agents, ordered three of them to strip and leave, and held one until the governor of Tennessee would come, as a crowd of some 2,000 Black supporters gathered outside. He was charged with assault with intent to commit murder and released on $2,000 bail; he was defended by attorney Walter Bailey. Documented in The Black Panther (July 1, 1972).',
                'cases' => [[
                    'institution_state' => 'Tennessee',
                    'charges' => 'Assault with intent to commit murder (the June 7, 1972 IRS-agent standoff at his Memphis dry-cleaners)',
                ]],
            ],
            [
                'name' => 'Tommy Atwood',
                'first_name' => 'Tommy',
                'last_name' => 'Atwood',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'North Carolina',
                'era' => '1970s',
                'ideologies' => ['Black liberation', 'Civil rights'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Tommy Atwood was charged, along with the Rev. Ben Chavis and Marvin Patrick, in the Wilmington, North Carolina prosecution over the killing of Harvey Cumber during the racial unrest of February 1971 — a conspiracy-to-murder case parallel to the firebombing charges of the better-known Wilmington Ten. The state\'s key witness was the informant Allen "Crazy Al" Hall. Documented in The Black Panther (May 27, 1972).',
                'cases' => [[
                    'institution_state' => 'North Carolina',
                    'charges' => 'Conspiracy to murder (the death of Harvey Cumber during the February 1971 Wilmington, N.C. unrest)',
                ]],
            ],
        ];
    }
}
