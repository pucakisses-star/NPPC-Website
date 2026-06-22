<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Seventh batch from reading The Black Panther — drawn from the Feb-Oct 1971
 * issues, plus two cases from a July 24, 1976 issue (the marxists.org scan is
 * mis-filed under a 1971 volume number, but its masthead and the events it
 * describes are 1975-76; content date is authoritative).
 *
 *  - New Haven: Steve Long, a chapter Breakfast Program coordinator (1970).
 *  - San Quentin: Marvin Smith, acquitted by a jury yet held in solitary.
 *  - Winston-Salem NCCF: coordinator Larry Little and Julius Cornell, arrested
 *    in the January 1971 "meat-truck" frame-up (the Grady Fuller case).
 *  - Leesville, Louisiana: Vincent Robinson and Herbert McGee, jailed for
 *    leafleting near Fort Polk (Sept. 1971).
 *  - Detroit/exile: artist Glanton Dowdell, seized in Sweden on a U.S. demand.
 *  - Greenville, S.C.: Charles Wakefield, sentenced to death on a frame-up
 *    (1976). Washington State: Wilford Davis, a paralyzed prisoner denied care.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers7Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-7';

    protected $description = 'Add Black Panther newspaper prisoners from the 1971 (and one 1976) issues, batch 7';

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
        $mk = function (array $p): array {
            return array_merge([
                'gender' => 'Male',
                'race' => 'Black',
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
            ], $p);
        };

        return [
            $mk([
                'name' => 'Steve Long',
                'first_name' => 'Steve',
                'last_name' => 'Long',
                'state' => 'Connecticut',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'description' => 'Steve Long was the Breakfast Program coordinator for the Connecticut State Chapter of the Black Panther Party. On July 2, 1970, at a People\'s Block Party on Ward Street in New Haven, a policeman tried to break up a children\'s march; when Long and a co-defendant, Chuck Scott, moved to shield the children, the officer attacked Scott, and in the ensuing struggle the officer was knocked unconscious and his revolver taken by the crowd. Warrants were issued for both men, and Long was charged with robbery with violence, assault on a police officer, and resisting arrest. The Black Panther (March 27, 1971) called him a political prisoner.',
                'cases' => [[
                    'institution_state' => 'Connecticut',
                    'charges' => 'Robbery with violence, assault on a police officer, and resisting arrest (New Haven, July 2, 1970)',
                ]],
            ]),
            $mk([
                'name' => 'Marvin Smith',
                'first_name' => 'Marvin',
                'last_name' => 'Smith',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Prison movement', 'Black liberation'],
                'affiliation' => [],
                'description' => 'Marvin Smith was a California prisoner, transferred from Soledad to San Quentin, who became a figure in the prison movement. He was charged with assault with a deadly weapon and with holding guards hostage but was acquitted by a jury; San Quentin officials nonetheless kept him locked in a maximum-security adjustment center. In 1971 he filed suit in U.S. District Court to bar the prison from punishing him for acts of which a jury had acquitted him. The underlying incident, per The Black Panther, was his stepping in to break up a fight between a guard and another inmate, Johnny Miller — who had witnessed a guard\'s misconduct and refused a payoff.',
                'cases' => [[
                    'institution_name' => 'San Quentin State Prison',
                    'institution_city' => 'San Quentin',
                    'institution_state' => 'California',
                    'charges' => 'Assault with a deadly weapon and holding guards hostage (held in solitary despite acquittal)',
                    'convicted' => 'No — acquitted by a jury',
                ]],
            ]),
            $mk([
                'name' => 'Larry Little',
                'first_name' => 'Larry',
                'last_name' => 'Little',
                'state' => 'North Carolina',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party', 'National Committee to Combat Fascism'],
                'description' => 'Larry Little was the coordinator of the National Committee to Combat Fascism — the Black Panther Party chapter — in Winston-Salem, North Carolina. In January 1971 he was arrested with Julius Cornell on charges of accessory after the fact, part of the "meat-truck" frame-up that police used as a pretext to attack the chapter\'s office (the same episode in which Grady Fuller was arrested). He went on to a long career in Winston-Salem public life, serving many years on the city\'s Board of Aldermen and teaching at Winston-Salem State University.',
                'cases' => [[
                    'institution_state' => 'North Carolina',
                    'charges' => 'Accessory after the fact (the January 1971 Winston-Salem "meat-truck" frame-up of the NCCF)',
                ]],
            ]),
            $mk([
                'name' => 'Julius Cornell',
                'first_name' => 'Julius',
                'last_name' => 'Cornell',
                'state' => 'North Carolina',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['National Committee to Combat Fascism'],
                'description' => 'Julius Cornell was a member of the National Committee to Combat Fascism (the Black Panther Party formation) in Winston-Salem, North Carolina. In January 1971 he was arrested with chapter coordinator Larry Little on charges of accessory after the fact, part of the "meat-truck" frame-up police used to justify raiding the chapter\'s office — the same case in which Grady Fuller was arrested. Documented in The Black Panther (January-February 1971).',
                'cases' => [[
                    'institution_state' => 'North Carolina',
                    'charges' => 'Accessory after the fact (the January 1971 Winston-Salem "meat-truck" frame-up of the NCCF)',
                ]],
            ]),
            $mk([
                'name' => 'Vincent Robinson',
                'first_name' => 'Vincent',
                'last_name' => 'Robinson',
                'state' => 'Louisiana',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'description' => 'Vincent Robinson was a member of the Louisiana State Chapter of the Black Panther Party. In September 1971, while distributing literature with Herbert McGee in Leesville, Louisiana — about ten miles from the Fort Polk army base — he was arrested and charged with vagrancy, soliciting, disturbing the peace, and resisting arrest, with bond set at $699. The two were held in a jail that had been condemned since 1942 and were threatened with beatings by guards. Documented in The Black Panther (October 30, 1971).',
                'cases' => [[
                    'institution_state' => 'Louisiana',
                    'charges' => 'Vagrancy, soliciting, disturbing the peace, and resisting arrest (Leesville, Louisiana, September 1971)',
                ]],
            ]),
            $mk([
                'name' => 'Herbert McGee',
                'first_name' => 'Herbert',
                'last_name' => 'McGee',
                'state' => 'Louisiana',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'description' => 'Herbert McGee was a member of the Louisiana State Chapter of the Black Panther Party. In September 1971 he and Vincent Robinson were arrested while passing out literature in Leesville, Louisiana, near the Fort Polk army base. McGee was charged with vagrancy and disturbing the peace, with bond set at $585, and was held with Robinson in a jail condemned since 1942. Documented in The Black Panther (October 30, 1971).',
                'cases' => [[
                    'institution_state' => 'Louisiana',
                    'charges' => 'Vagrancy and disturbing the peace (Leesville, Louisiana, September 1971)',
                ]],
            ]),
            $mk([
                'name' => 'Glanton Dowdell',
                'first_name' => 'Glanton',
                'last_name' => 'Dowdell',
                'state' => 'Michigan',
                'era' => '1970s',
                'ideologies' => ['Black liberation', 'Black nationalism'],
                'affiliation' => [],
                'description' => 'Glanton Dowdell was a Detroit artist — best known for painting the "Black Madonna" at the Shrine of the Black Madonna — and a figure in the city\'s Black Power and Republic of New Afrika circles who went into political exile in Europe. In 1971 he was arrested by Swedish authorities at the demand of the U.S. Embassy, which was pressing for the deportation of Black exiles and GIs; The Black Panther (March 27, 1971) named him among the political refugees facing such repression abroad.',
                'cases' => [[
                    'charges' => 'Arrested in Sweden (1971) on a U.S. government deportation demand',
                ]],
            ]),
            $mk([
                'name' => 'Charles Wakefield',
                'first_name' => 'Charles',
                'last_name' => 'Wakefield',
                'state' => 'South Carolina',
                'era' => '1970s',
                'ideologies' => [],
                'affiliation' => [],
                'description' => 'Charles Wakefield was a 21-year-old Black man in Greenville, South Carolina, convicted in 1976 of murdering Greenville County Sheriff\'s Lt. Frank Looper and Looper\'s father, Rufus, during an alleged armed robbery on January 31, 1975 — and given a mandatory death sentence on a record of conflicting testimony. Indicted nine months after the killings, he was convicted on the word of two county-jail inmates who were offered reduced sentences and of an alleged accomplice, Wyatt Earp Harper, himself indicted as an accessory; three prosecution witnesses gave conflicting descriptions and a published composite drawing did not resemble him. A survey of Greenville County found that 72 percent of Black residents believed he had been framed. Documented in The Black Panther (July 24, 1976).',
                'cases' => [[
                    'institution_state' => 'South Carolina',
                    'charges' => 'Murder of Greenville County Sheriff\'s Lt. Frank Looper and his father Rufus Looper (alleged Jan. 31, 1975 armed robbery)',
                    'convicted' => 'Yes — convicted 1976 on conflicting testimony; sentenced to death',
                    'sentence' => 'Death (mandatory)',
                ]],
            ]),
            $mk([
                'name' => 'Wilford Davis',
                'first_name' => 'Wilford',
                'last_name' => 'Davis',
                'state' => 'Washington',
                'era' => '1970s',
                'ideologies' => [],
                'affiliation' => [],
                'description' => 'Wilford Davis was a Black prisoner in Washington State, convicted of murder and serving a life sentence. He had been shot in the spine and paralyzed from the waist down at Oroville on November 28, 1974; after his April 1975 conviction he was sent to the Washington Corrections Center at Shelton, where the specialized medical care his paralysis required was withdrawn. He filed suit seeking $125,000 in damages over the inhumane denial of medical care. Documented in The Black Panther (July 24, 1976).',
                'cases' => [[
                    'institution_name' => 'Washington Corrections Center',
                    'institution_city' => 'Shelton',
                    'institution_state' => 'Washington',
                    'charges' => 'Murder',
                    'convicted' => 'Yes — convicted April 1975',
                    'sentence' => 'Life imprisonment',
                ]],
            ]),
        ];
    }
}
