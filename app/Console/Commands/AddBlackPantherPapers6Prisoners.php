<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Sixth batch from reading The Black Panther — drawn from the early-1971 issues
 * (Jan-Jul 1971, vols. 5-6). Regional political-prisoner cases the paper
 * covered that were missing from the database:
 *
 *  - Tucson, Arizona: the Oct. 3, 1969 Crown Liquors killing frame-up —
 *    Donnell Thomas (sentenced to death), David Williams and Robert "Bobby"
 *    Skinner (life). Built on a statement from a one-eyed, drug-using witness
 *    (Lucius Sorrell) ruled incompetent to testify; prosecutor Horton Weiss.
 *  - Cleveland, Ohio: Darryl Harris and Essex Smith, NCCF members arrested
 *    Sept. 29, 1970 amid a sweep of 25+ Cleveland Panthers.
 *  - Berkeley/Oakland: Charles Brunson, a Panther seized off the street by the
 *    FBI on April 15, 1971 on interstate-weapons charges.
 *  - Buffalo, N.Y.: Geraldine Robinson, co-defendant of Martin Sostre in the
 *    1967 bookstore heroin frame-up (the State's key witness later recanted).
 *  - East Palo Alto: the Black Liberation Front / Stanford Hospital workers'
 *    struggle — Chris Laury, Leo Hazzile and Samuel Bridges.
 *  - New Orleans: the Desire Project / NCCF case — Alfred McCoy, George Russell
 *    and Betty Powell (of the "New Orleans 24").
 *  - New York City: Victor Martinez of the Inmates Liberation Front (1970 Tombs
 *    rebellion). Winston-Salem: NCCF member Grady Fuller. Illinois: Monk Teba.
 *
 * Several of these are documented primarily in The Black Panther; such records
 * note that in the bio. Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers6Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-6';

    protected $description = 'Add Black Panther newspaper prisoners from the early-1971 issues (Tucson, Cleveland, New Orleans, East Palo Alto, etc.)';

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
        // Prisoner-level defaults (these are 50-year-old snapshots; nobody is in
        // custody now). Override per record as needed.
        $mk = function (array $p): array {
            return array_merge([
                'gender' => 'Male',
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
            ], $p);
        };

        $tucsonNote = ' The case rested on a statement attributed to a one-eyed, heroin-using witness, Lucius Sorrell, whom the State had had declared incompetent to testify; prosecutor Horton Weiss. Police claimed the defendants were Black Panthers, which The Black Panther (April 17, 1971) said was false. Documented in The Black Panther.';

        return [
            // ---- Tucson, Arizona: the Crown Liquors frame-up ----
            $mk([
                'name' => 'Donnell Thomas',
                'first_name' => 'Donnell',
                'last_name' => 'Thomas',
                'race' => 'Black',
                'state' => 'Arizona',
                'era' => '1960s',
                'ideologies' => ['Black liberation'],
                'affiliation' => [],
                'released' => false,
                'description' => 'Donnell Thomas was one of four Black men charged in the October 3, 1969 hold-up killing of liquor-store clerk Mason Branch at Crown Liquors on Grant Road in Tucson, Arizona. He was convicted of first-degree murder and sentenced to death in the gas chamber, and was imprisoned on death row at the Arizona State Prison alongside his cousin and co-defendant David Williams.'.$tucsonNote,
                'cases' => [[
                    'institution_name' => 'Arizona State Prison',
                    'institution_state' => 'Arizona',
                    'charges' => 'First-degree murder, armed robbery, and conspiracy (the Oct. 3, 1969 hold-up killing of clerk Mason Branch at Crown Liquors, Tucson)',
                    'convicted' => 'Yes — convicted of first-degree murder',
                    'sentence' => 'Death (gas chamber)',
                    'prosecutor' => 'Horton Weiss',
                ]],
            ]),
            $mk([
                'name' => 'David Williams',
                'first_name' => 'David',
                'last_name' => 'Williams',
                'race' => 'Black',
                'state' => 'Arizona',
                'era' => '1960s',
                'ideologies' => ['Black liberation'],
                'affiliation' => [],
                'description' => 'David Williams, cousin and co-defendant of Donnell Thomas, was one of four Black men charged in the October 3, 1969 hold-up killing of clerk Mason Branch at Crown Liquors in Tucson, Arizona. Facing the gas chamber, he pleaded guilty under a deal with the court and was imprisoned at the Arizona State Prison.'.$tucsonNote,
                'cases' => [[
                    'institution_name' => 'Arizona State Prison',
                    'institution_state' => 'Arizona',
                    'charges' => 'First-degree murder, armed robbery, and conspiracy (the Oct. 3, 1969 Crown Liquors hold-up, Tucson)',
                    'convicted' => 'Yes — pleaded guilty under a deal to avoid the death penalty',
                    'prosecutor' => 'Horton Weiss',
                ]],
            ]),
            $mk([
                'name' => 'Robert Skinner',
                'first_name' => 'Robert',
                'last_name' => 'Skinner',
                'aka' => 'Bobby',
                'race' => 'Black',
                'state' => 'Arizona',
                'era' => '1960s',
                'ideologies' => ['Black liberation'],
                'affiliation' => [],
                'description' => 'Robert "Bobby" Skinner was a Black community organizer in Tucson, Arizona — active in educating his people but, by The Black Panther\'s account, not a member of any organization — whom local police had reportedly threatened ("We\'re going to get you"). He was one of four men charged in the October 3, 1969 Crown Liquors hold-up killing; while jailed he was also smeared with a "sodomy" charge that was laughed out of court. He went to trial, was convicted of first-degree murder, and was sentenced to life imprisonment.'.$tucsonNote,
                'cases' => [[
                    'institution_name' => 'Arizona State Prison',
                    'institution_state' => 'Arizona',
                    'charges' => 'First-degree murder, armed robbery, and conspiracy (the Oct. 3, 1969 Crown Liquors hold-up, Tucson)',
                    'convicted' => 'Yes — convicted of first-degree murder',
                    'sentence' => 'Life imprisonment',
                    'prosecutor' => 'Horton Weiss',
                ]],
            ]),

            // ---- Cleveland, Ohio: Darryl Harris & Essex Smith ----
            $mk([
                'name' => 'Darryl Harris',
                'first_name' => 'Darryl',
                'last_name' => 'Harris',
                'race' => 'Black',
                'state' => 'Ohio',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['National Committee to Combat Fascism'],
                'description' => 'Darryl Harris was a Cleveland organizer associated with the local National Committee to Combat Fascism (the Black Panther Party formation that ran survival programs there). On September 29, 1970 he and Essex Smith were arrested and charged with rape, abduction, armed robbery, and shooting with intent to kill, and held in lieu of $50,000 bail — although, The Black Panther reported, they had alibi witnesses. Their arrests came amid a sweep in which more than 25 Cleveland Panthers were charged with some 40 felonies between June and September 1970. Documented in The Black Panther (May 1, 1971).',
                'cases' => [[
                    'institution_state' => 'Ohio',
                    'charges' => 'Rape, abduction, armed robbery, and shooting with intent to kill (Cleveland; the defense reported alibi witnesses)',
                    'arrest_date' => '1970-09-29',
                ]],
            ]),
            $mk([
                'name' => 'Essex Smith',
                'first_name' => 'Essex',
                'last_name' => 'Smith',
                'race' => 'Black',
                'state' => 'Ohio',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['National Committee to Combat Fascism'],
                'description' => 'Essex Smith was a Cleveland organizer associated with the local National Committee to Combat Fascism. On September 29, 1970 he and Darryl Harris were arrested and charged with rape, abduction, armed robbery, and shooting with intent to kill, and held in lieu of $50,000 bail despite having alibi witnesses, according to The Black Panther. The arrests were part of a wider 1970 crackdown in which more than 25 Cleveland Panthers were charged with some 40 felonies. Documented in The Black Panther (May 1, 1971).',
                'cases' => [[
                    'institution_state' => 'Ohio',
                    'charges' => 'Rape, abduction, armed robbery, and shooting with intent to kill (Cleveland; the defense reported alibi witnesses)',
                    'arrest_date' => '1970-09-29',
                ]],
            ]),

            // ---- Berkeley / Oakland: Charles Brunson ----
            $mk([
                'name' => 'Charles Brunson',
                'first_name' => 'Charles',
                'last_name' => 'Brunson',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Black Power', 'Revolutionary socialism'],
                'affiliation' => ['Black Panther Party'],
                'description' => 'Charles Brunson had been a member of the Black Panther Party for about three years — working in Sacramento, Washington, D.C., and the party\'s Central Headquarters in Oakland — when, on April 15, 1971, FBI agents seized him off the street in front of the Huey P. Newton Intercommunal Youth Institute in Berkeley and took him to the city prison in San Francisco. He was charged with conspiring to transport stolen weapons across state lines (a violation of the Virginia firearms law) and held on $10,000 bail. Documented in The Black Panther (May 1, 1971).',
                'cases' => [[
                    'institution_name' => 'San Francisco City Prison',
                    'institution_city' => 'San Francisco',
                    'institution_state' => 'California',
                    'charges' => 'Conspiring to transport stolen weapons across state lines (violation of the Virginia firearms act)',
                    'arrest_date' => '1971-04-15',
                ]],
            ]),

            // ---- Buffalo, N.Y.: Geraldine Robinson (Sostre co-defendant) ----
            $mk([
                'name' => 'Geraldine Robinson',
                'first_name' => 'Geraldine',
                'last_name' => 'Robinson',
                'gender' => 'Female',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1960s',
                'ideologies' => ['Black liberation'],
                'affiliation' => [],
                'description' => 'Geraldine Robinson was the co-defendant of Black militant and radical bookseller Martin Sostre, arrested with him in 1967 at his Afro-Asian Bookstore in Buffalo, New York, and convicted in the same narcotics frame-up. She drew an indeterminate sentence, and her five young children were divided among foster homes during her imprisonment. The State\'s key witness, Arto Williams, later admitted he had lied — that narcotics detectives had driven him to the bookstore to set Sostre up in exchange for dropping his own charges — undermining the case against both defendants. Documented in The Black Panther (May 8, 1971).',
                'cases' => [[
                    'institution_state' => 'New York',
                    'charges' => 'Narcotics (alleged sale of heroin) — arrested with Martin Sostre at his Buffalo bookstore; the State\'s key witness later recanted',
                    'convicted' => 'Yes — convicted on the same frame-up as Martin Sostre',
                    'sentence' => 'Indeterminate sentence',
                ]],
            ]),

            // ---- East Palo Alto: Black Liberation Front / Stanford Hospital ----
            $mk([
                'name' => 'Chris Laury',
                'first_name' => 'Chris',
                'last_name' => 'Laury',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Liberation Front'],
                'description' => 'Chris Laury was a member of the Black Liberation Front in East Palo Alto, California, arrested on May 7, 1971 and charged with two felonies over the alleged April 1971 beating of Mary Jane Schmidt, a senior clerk at Stanford Hospital, during a hospital-workers\' struggle. He was taken at the Black Liberation Front headquarters where he lived; The Black Panther argued he had been misidentified, noting the victim described a 6-foot-2 heavily built man while Laury was about 5-foot-8 and 140 pounds. Documented in The Black Panther (May 29, 1971).',
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Two felonies — the alleged beating of Stanford Hospital clerk Mary Jane Schmidt (defense argued mistaken identity)',
                    'arrest_date' => '1971-05-07',
                ]],
            ]),
            $mk([
                'name' => 'Leo Hazzile',
                'first_name' => 'Leo',
                'last_name' => 'Hazzile',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Liberation Front'],
                'description' => 'Leo Hazzile, a former Black student leader at Stanford, was arrested in the May 1971 East Palo Alto Black Liberation Front sweep connected to the Stanford Hospital workers\' struggle. His bail was set at $12,500 and later reduced to $6,250. The specifics of his charges are documented only in The Black Panther (May 29, 1971).',
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Arrested in the May 1971 East Palo Alto Black Liberation Front sweep (bail set at $12,500, reduced to $6,250)',
                ]],
            ]),
            $mk([
                'name' => 'Samuel Bridges',
                'first_name' => 'Samuel',
                'last_name' => 'Bridges',
                'aka' => 'Sam',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Liberation Front'],
                'description' => 'Samuel "Sam" Bridges was a Black former Stanford Hospital worker whose firing helped spark the April 1971 hospital sit-in in the Mid-Peninsula. On May 14, 1971, while driving with Chris Laury to visit a friend in Ukiah, he was stopped by police — who said they found marijuana and an open wine bottle — and arrested. The East Palo Alto Black Liberation Front Defense Committee called the stop political harassment aimed at the movement. Documented in The Black Panther (May 29, 1971).',
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Marijuana possession (arrested with Chris Laury, May 14, 1971; the defense committee called the stop political harassment)',
                    'arrest_date' => '1971-05-14',
                ]],
            ]),

            // ---- New Orleans: the Desire Project / NCCF case ----
            $mk([
                'name' => 'Alfred McCoy',
                'first_name' => 'Alfred',
                'last_name' => 'McCoy',
                'race' => 'Black',
                'state' => 'Louisiana',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party', 'National Committee to Combat Fascism'],
                'description' => 'Alfred McCoy was associated with the New Orleans chapter of the Black Panther Party during the 1970 confrontations around the Desire housing project. He was convicted of aggravated battery against two police infiltrators of the party (Melvin Howard and Israel Fields) and sentenced to five years at Louisiana\'s Angola penitentiary — part of the wider New Orleans prosecution that became known for the "New Orleans 24." Documented in The Black Panther (June 12, 1971).',
                'cases' => [[
                    'institution_name' => 'Louisiana State Penitentiary',
                    'institution_city' => 'Angola',
                    'institution_state' => 'Louisiana',
                    'charges' => 'Aggravated battery (upon two police infiltrators of the New Orleans Black Panther Party)',
                    'convicted' => 'Yes',
                    'sentence' => 'Five years at Angola (Louisiana State Penitentiary)',
                ]],
            ]),
            $mk([
                'name' => 'George Russell',
                'first_name' => 'George',
                'last_name' => 'Russell',
                'race' => 'Black',
                'state' => 'Louisiana',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'description' => 'George Russell was a member of the New Orleans Black Panther Party. In November 1970, as he, Harold Holmes, and some 25 others set out for the People\'s Revolutionary Constitutional Convention in Washington, D.C., they were arrested by heavily armed New Orleans police; Russell was charged with criminal trespassing, criminal anarchy, and criminal property damage. His case was part of the larger New Orleans Desire-project prosecution. Documented in The Black Panther (June 12, 1971).',
                'cases' => [[
                    'institution_state' => 'Louisiana',
                    'charges' => 'Criminal trespassing, criminal anarchy, and criminal property damage (New Orleans, November 1970)',
                ]],
            ]),
            $mk([
                'name' => 'Betty Powell',
                'first_name' => 'Betty',
                'last_name' => 'Powell',
                'gender' => 'Female',
                'race' => 'Black',
                'state' => 'Louisiana',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'description' => 'Betty Powell was a member of the New Orleans Black Panther Party. During the pre-dawn raid of November 26, 1970, in which about fifty New Orleans police disguised as priests and postal workers stormed the party\'s office in the Desire housing project, she was shot in the chest. She was among the six people then arrested and charged with attempted murder and violating the federal firearms act, and was held at the Orleans Parish Prison awaiting trial. Documented in The Black Panther (June 12, 1971).',
                'cases' => [[
                    'institution_name' => 'Orleans Parish Prison',
                    'institution_city' => 'New Orleans',
                    'institution_state' => 'Louisiana',
                    'charges' => 'Attempted murder and violation of the federal firearms act (the Nov. 26, 1970 police raid on the Desire-project Black Panther Party office)',
                    'arrest_date' => '1970-11-26',
                ]],
            ]),

            // ---- New York City: Victor Martinez (Tombs rebellion / ILF) ----
            $mk([
                'name' => 'Victor Martinez',
                'first_name' => 'Victor',
                'last_name' => 'Martinez',
                'state' => 'New York',
                'era' => '1970s',
                'ideologies' => ['Prison movement'],
                'affiliation' => ['Inmates Liberation Front'],
                'description' => 'Victor Martinez was a member of the Inmates Liberation Front, the organization that grew out of the 1970 rebellion at the Tombs (the Manhattan House of Detention) in New York City. After the uprising, indictments for kidnapping and conspiracy to murder came down on about two dozen prisoners; Martinez, out on bail, was one of them. Shortly after giving an interview to The Black Panther (January 16, 1971), fearing for his life if jailed again, he went underground.',
                'cases' => [[
                    'institution_state' => 'New York',
                    'charges' => 'Kidnapping and conspiracy to murder (arising from the 1970 Tombs jail rebellion, New York City)',
                ]],
            ]),

            // ---- Winston-Salem, N.C.: Grady Fuller (NCCF) ----
            $mk([
                'name' => 'Grady Fuller',
                'first_name' => 'Grady',
                'last_name' => 'Fuller',
                'race' => 'Black',
                'state' => 'North Carolina',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['National Committee to Combat Fascism'],
                'description' => 'Grady Fuller was a member of the National Committee to Combat Fascism (the Black Panther Party formation) in Winston-Salem, North Carolina. On January 16, 1971, police raided the Black community and the NCCF office on the pretext that members had robbed a meat truck, arresting Fuller along with several others; his bail was set at $7,000. Documented in The Black Panther (January 30, 1971).',
                'cases' => [[
                    'institution_state' => 'North Carolina',
                    'charges' => 'Alleged larceny of a meat truck — the pretext for a police raid on the Winston-Salem NCCF office (bail set at $7,000)',
                    'arrest_date' => '1971-01-16',
                ]],
            ]),

            // ---- Illinois: Monk Teba (newspaper-only) ----
            $mk([
                'name' => 'Monk Teba',
                'first_name' => 'Monk',
                'last_name' => 'Teba',
                'race' => 'Black',
                'state' => 'Illinois',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'description' => 'Monk Teba was a member of the Illinois chapter of the Black Panther Party and a contributor of poetry and columns to The Black Panther (bylined from Chicago and Carbondale). The paper reported (January 9, 1971) that he had been framed: after a white University of Indiana professor, Arthur G. Carne, was caught with a stolen typewriter, Carne claimed "a Panther named Monk Teba" had given it to him, whereupon Teba was charged with grand theft and possession of stolen property and held on $7,000 bail. NOTE: "Monk Teba" is a movement name and this case is documented only in The Black Panther; it is not independently corroborated.',
                'cases' => [[
                    'institution_state' => 'Illinois',
                    'charges' => 'Grand theft and possession of stolen property (per The Black Panther, a frame-up centered on a stolen typewriter; bail $7,000)',
                ]],
            ]),
        ];
    }
}
