<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Tenth batch from reading The Black Panther — drawn from the February 19, 1972
 * issue:
 *
 *  - Chauncy Gilmore and Jeffery Gaulden — two young Black Californians given
 *    indeterminate life terms; Gaulden was a witness to the killing of Fred
 *    Billingslea and was moved between prisons to keep him from testifying.
 *  - Fred Billingslea — a San Quentin prisoner killed by guards' tear gas in his
 *    locked cell (Feb. 25, 1970).
 *  - The High Point Four — Black Panther members in High Point, N.C. tried over
 *    a Feb. 10, 1971 police attack on their community center: Randolph Jennings,
 *    Larry Medley and Bradford Lilley convicted (7-10 years); George Dewitt
 *    acquitted.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers10Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-10';

    protected $description = 'Add Black Panther newspaper prisoners from the Feb 19 1972 issue, batch 10';

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
        // The High Point Four (shared case: a Feb. 10, 1971 police attack on the
        // High Point, N.C. BPP community center, tried Feb. 1972 before Judge
        // J. William Copeland on a charge of assaulting Lt. Shaw Cooke).
        $highPoint = function (string $name, string $first, string $last, string $outcome, string $descExtra = ''): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'North Carolina',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was one of the \"High Point Four,\" young Black Panther Party members in High Point, North Carolina arrested on February 10, 1971 when the High Point police (under Chief Laurie Pritchett) attacked the party's community center at 6 a.m. They were charged with assaulting a policeman, Lt. Shaw Cooke, who was wounded by the single shot police claimed came from the center — though no evidence placed a gun in any defendant's hand.{$descExtra} They were tried in February 1972 before Judge J. William Copeland in Guilford County. Documented in The Black Panther (February 19, 1972).",
                'cases' => [[
                    'institution_state' => 'North Carolina',
                    'charges' => 'Assault on a police officer (the Feb. 10, 1971 police attack on the High Point BPP community center)',
                    'arrest_date' => '1971-02-10',
                    'convicted' => $outcome,
                ]],
            ];
        };

        return [
            [
                'name' => 'Chauncy Gilmore',
                'first_name' => 'Chauncy',
                'last_name' => 'Gilmore',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => [],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Chauncy Gilmore was a 21-year-old Black man from the Oakland Bay Area. On the night of January 14, 1971, walking past the Villa Roma cafe in Berkeley, he was set upon, beaten, robbed and knocked unconscious by two white men — Alfred Tenbrink and the cafe\'s owner, Bruno Maieroni — who hurled racial slurs at him. Witnesses said three other Black men then appeared and shots were fired, killing Maieroni and wounding his mother; Tenbrink died two months later. Although Gilmore had been beaten unconscious and his fingerprints were not on the gun, a grand jury indicted him for murder on the strength of disputed "confession" tapes, and on February 6, 1972 Judge Cooper sentenced him to six months to life in a California state prison. Documented in The Black Panther (February 19, 1972).',
                'cases' => [[
                    'institution_name' => 'California Medical Facility',
                    'institution_city' => 'Vacaville',
                    'institution_state' => 'California',
                    'charges' => 'Two counts of murder and one of attempted murder (the Jan. 14, 1971 Villa Roma cafe shooting in Berkeley; the prosecution sought the gas chamber)',
                    'convicted' => 'Yes — convicted; sentenced February 6, 1972',
                    'sentence' => 'Six months to life (indeterminate)',
                ]],
            ],
            [
                'name' => 'Jeffery Gaulden',
                'first_name' => 'Jeffery',
                'last_name' => 'Gaulden',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Prison movement'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Jeffery Gaulden, of San Diego, California, was 21 when he was sentenced to five years to life following a 1971 altercation at a party — an indeterminate term that left him, as a "lifer," facing the gas chamber for any further charge. Imprisoned at San Quentin, he came forward as a witness against the guards in the February 25, 1970 killing of fellow prisoner Fred Billingslea, and was transferred to the Adjustment Center at Folsom to isolate him and prevent his testimony in the wrongful-death suit. Documented in The Black Panther (February 19, 1972).',
                'cases' => [[
                    'institution_name' => 'Folsom State Prison',
                    'institution_city' => 'Represa',
                    'institution_state' => 'California',
                    'charges' => 'Convicted following a 1971 party altercation',
                    'convicted' => 'Yes',
                    'sentence' => 'Five years to life (indeterminate)',
                ]],
            ],
            [
                'name' => 'Fred Billingslea',
                'first_name' => 'Fred',
                'last_name' => 'Billingslea',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'death_date' => '1970-02-25',
                'ideologies' => [],
                'affiliation' => [],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'Fred Billingslea was a Black prisoner at San Quentin State Prison who was killed on February 25, 1970, when guards fired tear gas into his locked cell and then beat and dragged him; he died of the assault. His killing became a rallying point in the California prison movement, and prisoners who witnessed it — among them Jeffery Gaulden — faced retaliation and transfers for coming forward in the wrongful-death suit against San Quentin officials. Documented in The Black Panther (February 19, 1972).',
                'cases' => [[
                    'institution_name' => 'San Quentin State Prison',
                    'institution_city' => 'San Quentin',
                    'institution_state' => 'California',
                    'charges' => 'Died in custody — killed by guards\' tear gas in his locked cell, February 25, 1970',
                    'death_in_custody_date' => '1970-02-25',
                ]],
            ],

            // ---- The High Point Four ----
            $highPoint('Randolph Jennings', 'Randolph', 'Jennings',
                'Yes — convicted February 1972; sentenced to 7-10 years (appeal bond $7,000)',
                ' Randolph Jennings was eighteen years old.'),
            $highPoint('Larry Medley', 'Larry', 'Medley',
                'Yes — convicted February 1972; sentenced to 7-10 years (appeal bond $7,000)',
                ' Larry Medley, seventeen, still carried a 12-gauge shotgun slug in his shoulder from the police attack.'),
            $highPoint('Bradford Lilley', 'Bradford', 'Lilley',
                'Yes — convicted February 1972; sentenced to 7-10 years (appeal bond $7,000)',
                ' Bradford Lilley was twenty years old.'),
            $highPoint('George Dewitt', 'George', 'Dewitt',
                'No — acquitted in a compromise verdict',
                ' George Dewitt was acquitted of the charges.'),
        ];
    }
}
