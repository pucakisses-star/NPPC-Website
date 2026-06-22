<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds the eight co-defendants prosecuted with Pedro Albizu Campos in the 1936
 * federal SEDITIOUS CONSPIRACY case (United States v. Albizu Campos et al.) —
 * the post-Riggs-assassination prosecution of the Nationalist Party leadership.
 * All were named in the federal indictment of April 3, 1936; the first trial
 * hung 7–5 to acquit, a second jury convicted, and the leaders were transferred
 * to the U.S. Penitentiary in Atlanta on June 7, 1937. The appellate opinion
 * (Albizu v. United States, 88 F.2d 138, 1st Cir. 1937) confirms the verdicts.
 *
 * Consistent with Albizu's own 1936 case, the Atlanta custody is described in
 * prose rather than asserted with unverified day-level dates (only the years
 * are reliably documented), so the 1936 cases carry no incarceration/release
 * dates. Honest gaps: the cadets' exact sentences (secondary Nationalist
 * histories say a flat "10 years"; the opinion states no figure) and their
 * birth/death/biographical data are not documented in accessible sources.
 * Rafael Ortiz Pacheco was indicted but is ABSENT from the opinion's verdict
 * list, so his conviction is recorded as unconfirmed (indictment qualifies).
 *
 * Note: Consuelo Lee de Corretjer (Juan Antonio Corretjer's wife) is already in
 * the database; he was not. Idempotent: skips any name already present.
 */
final class Add1936Codefendants extends Command
{
    protected $signature = 'prisoners:add-1936-codefendants';

    protected $description = 'Add the eight 1936 seditious-conspiracy co-defendants of Albizu Campos';

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
        // Shared template for the rank-and-file Cadets of the Republic.
        $cadet = function (string $name, string $first, string $last, string $convicted, string $sentence, string $bioExtra = '', ?string $aka = null): array {
            $r = [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Hispanic',
                'state' => 'Puerto Rico',
                'era' => '1930s',
                'ideologies' => ['Puerto Rican Independence', 'Anti-colonial'],
                'affiliation' => ['Puerto Rican Nationalist Party', 'Cadets of the Republic'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was a member of the Cadets of the Republic, the youth corps of the Puerto Rican Nationalist Party.{$bioExtra} He was among the party members indicted with Pedro Albizu Campos in the 1936 federal seditious-conspiracy case that followed the assassination of Insular Police Chief Francis Riggs.",
                'cases' => [[
                    'charges' => 'Seditious conspiracy — conspiring to overthrow United States authority in Puerto Rico; named in the federal indictment of April 3, 1936 (the post-Riggs-assassination prosecution of the Nationalist Party)',
                    'arrest_date' => '1936-04-03',
                    'convicted' => $convicted,
                    'sentence' => $sentence,
                ]],
            ];
            if ($aka) {
                $r['aka'] = $aka;
            }

            return $r;
        };

        $tenYears = 'Reported as ten years by Nationalist histories (the appellate opinion states no figure); transported to the U.S. Penitentiary in Atlanta on June 7, 1937';

        return [
            [
                'name' => 'Juan Antonio Corretjer',
                'first_name' => 'Juan',
                'middle_name' => 'Antonio',
                'last_name' => 'Corretjer',
                'aka' => 'Juan Antonio Corretjer Montes',
                'gender' => 'Male',
                'race' => 'Hispanic',
                'state' => 'Puerto Rico',
                'era' => '1930s',
                'birthdate' => '1908-03-03',
                'death_date' => '1985-01-19',
                'ideologies' => ['Puerto Rican Independence', 'Anti-colonial'],
                'affiliation' => ['Puerto Rican Nationalist Party', 'Liga Socialista Puertorriqueña'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "Juan Antonio Corretjer (1908–1985), born in Ciales, was Puerto Rico's \"National Poet\" and a lifelong independence militant who became Secretary-General of the Nationalist Party in 1936. Convicted that year in the federal seditious-conspiracy case tied to the Riggs assassination, he was held at La Princesa for refusing to surrender the party's Book of Acts and then at the Atlanta federal penitentiary, gaining release in 1942. He was again arrested during the 1950 Nationalist revolt and, after co-founding the Liga Socialista Puertorriqueña in 1962, faced a federal conspiracy prosecution in 1969 that was dismissed in 1971. He remained a central figure of the independence movement until his death in San Juan in 1985.",
                'cases' => [
                    [
                        'charges' => 'Seditious conspiracy — conspiring to overthrow United States authority in Puerto Rico; named in the federal indictment of April 3, 1936 (after the assassination of Insular Police Chief Francis Riggs)',
                        'arrest_date' => '1936-04-03',
                        'convicted' => 'Convicted on all three counts at the second trial (the first hung 7–5 to acquit); conviction affirmed, Albizu v. United States, 88 F.2d 138 (1st Cir. 1937)',
                        'sentence' => "Held about a year at La Princesa prison (San Juan) for refusing to surrender the Nationalist Party's Book of Acts, then at the U.S. Penitentiary in Atlanta from June 1937; released in 1942. Reported term 6–7 years (sources vary)",
                    ],
                    [
                        'charges' => 'Federal conspiracy charges targeting the Liga Socialista Puertorriqueña, which he co-founded in 1962 with his wife Consuelo Lee Tapia',
                        'convicted' => 'Charges dismissed in 1971 (no conviction)',
                        'sentence' => 'Charges dismissed in 1971',
                    ],
                ],
            ],
            [
                'name' => 'Clemente Soto Vélez',
                'first_name' => 'Clemente',
                'last_name' => 'Soto Vélez',
                'gender' => 'Male',
                'race' => 'Hispanic',
                'state' => 'Puerto Rico',
                'era' => '1930s',
                'birthdate' => '1905-01-04',
                'death_date' => '1993-04-15',
                'ideologies' => ['Puerto Rican Independence', 'Anti-colonial'],
                'affiliation' => ['Puerto Rican Nationalist Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Clemente Soto Vélez (1905–1993), born in Lares, was a major Puerto Rican poet and co-founder of the avant-garde "Atalaya de los Dioses" movement who became a militant organizer in the Nationalist Party. Convicted in the 1936 federal seditious-conspiracy case after the Riggs assassination, he was sentenced to seven years and imprisoned at the Atlanta federal penitentiary. Released in 1940, he was quickly re-jailed for violating parole with pro-independence speeches and finished his term at Lewisburg, Pennsylvania, gaining release in 1942. He spent much of his later life in New York City, where he mentored generations of Puerto Rican and Nuyorican writers until his death in 1993.',
                'cases' => [[
                    'charges' => 'Seditious conspiracy — conspiring to overthrow United States authority in Puerto Rico; named in the federal indictment of April 3, 1936 (after the assassination of Insular Police Chief Francis Riggs)',
                    'arrest_date' => '1936-04-03',
                    'convicted' => 'Convicted on the first and second counts; conviction affirmed, Albizu v. United States, 88 F.2d 138 (1st Cir. 1937)',
                    'sentence' => 'Seven years; imprisoned at the U.S. Penitentiary in Atlanta from 1937, paroled in 1940, re-imprisoned at the Lewisburg penitentiary (Pennsylvania) for a parole violation, and released in 1942',
                ]],
            ],
            [
                'name' => 'Luis F. Velázquez',
                'first_name' => 'Luis',
                'middle_name' => 'F.',
                'last_name' => 'Velázquez',
                'gender' => 'Male',
                'race' => 'Hispanic',
                'state' => 'Puerto Rico',
                'era' => '1930s',
                'ideologies' => ['Puerto Rican Independence', 'Anti-colonial'],
                'affiliation' => ['Puerto Rican Nationalist Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Luis F. Velázquez was a leader of the Puerto Rican Nationalist Party from Ponce and a member of its national directorate. On June 15, 1932 he struck Emilio del Toro y Cuebas, Chief Justice of the Puerto Rico Supreme Court, over an insult to the Puerto Rican flag — a case (Velázquez v. People of Puerto Rico) in which Pedro Albizu Campos served as his defense attorney. He was convicted on all three counts with Albizu in the 1936 federal seditious-conspiracy trial and imprisoned at the United States Penitentiary in Atlanta.',
                'cases' => [[
                    'charges' => 'Seditious conspiracy — conspiring to overthrow United States authority in Puerto Rico; named in the federal indictment of April 3, 1936 (after the assassination of Insular Police Chief Francis Riggs)',
                    'arrest_date' => '1936-04-03',
                    'convicted' => 'Convicted on all three counts; conviction affirmed, Albizu v. United States, 88 F.2d 138 (1st Cir. 1937)',
                    'sentence' => 'Reported as ten years; imprisoned at the U.S. Penitentiary in Atlanta from June 1937',
                ]],
            ],
            $cadet(
                'Erasmo Velázquez',
                'Erasmo',
                'Velázquez',
                'Convicted on the first and second counts; conviction affirmed, Albizu v. United States, 88 F.2d 138 (1st Cir. 1937)',
                $tenYears,
            ),
            $cadet(
                'Julio H. Velázquez',
                'Julio',
                'Velázquez',
                'Convicted on all three counts; conviction affirmed, Albizu v. United States, 88 F.2d 138 (1st Cir. 1937)',
                $tenYears,
                ' He served as an officer in the Nationalist cadet corps.',
                'Julio Héctor Velázquez',
            ),
            [
                'name' => 'Rafael Ortiz Pacheco',
                'first_name' => 'Rafael',
                'last_name' => 'Ortiz Pacheco',
                'gender' => 'Male',
                'race' => 'Hispanic',
                'state' => 'Puerto Rico',
                'era' => '1930s',
                'ideologies' => ['Puerto Rican Independence', 'Anti-colonial'],
                'affiliation' => ['Puerto Rican Nationalist Party', 'Cadets of the Republic'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Rafael Ortiz Pacheco was a member of the Cadets of the Republic, the youth corps of the Puerto Rican Nationalist Party, and was indicted with Pedro Albizu Campos in the 1936 federal seditious-conspiracy case following the assassination of Insular Police Chief Francis Riggs. Unlike his co-defendants he does not appear in the verdict list of the appellate opinion (Albizu v. United States, 88 F.2d 138), so his conviction is not confirmed by the court record, though secondary accounts place him among the defendants.',
                'cases' => [[
                    'charges' => 'Seditious conspiracy — conspiring to overthrow United States authority in Puerto Rico; named in the federal indictment of April 3, 1936 (after the assassination of Insular Police Chief Francis Riggs)',
                    'arrest_date' => '1936-04-03',
                    'convicted' => 'Indicted and prosecuted; his conviction is not confirmed in the appellate record (he is absent from the verdict list of Albizu v. United States, 88 F.2d 138)',
                    'sentence' => 'Not documented',
                ]],
            ],
            $cadet(
                'Juan Gallardo Santiago',
                'Juan',
                'Gallardo Santiago',
                'Convicted on all three counts; conviction affirmed, Albizu v. United States, 88 F.2d 138 (1st Cir. 1937)',
                $tenYears,
                ' He recruited cadets in the Buenavista section.',
            ),
            $cadet(
                'Pablo Rosado Ortiz',
                'Pablo',
                'Rosado Ortiz',
                'Convicted on the first and third counts; conviction affirmed, Albizu v. United States, 88 F.2d 138 (1st Cir. 1937)',
                $tenYears,
            ),
        ];
    }
}
