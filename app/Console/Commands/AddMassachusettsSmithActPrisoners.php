<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Massachusetts "Bay State Reds" — the Boston Smith Act defendants, Communist
 * Party leaders arrested in 1956 and prosecuted for conspiracy to advocate the
 * overthrow of the U.S. government, whose case was dismissed in 1957 after the
 * Supreme Court's Yates v. United States decision gutted Smith Act prosecutions.
 * Adds the five named here (the wider group also included Geoffrey Warner White
 * and Edward Strong). Sourced to UMass Amherst / Smith College / Brown archival
 * collections, People's World, and the CPUSA. Idempotent (skips by name).
 */
class AddMassachusettsSmithActPrisoners extends Command {
    protected $signature = 'prisoners:add-ma-smith-act';
    protected $description = 'Add the Massachusetts Smith Act defendants (Hood, Timpson, Schirmer, Lipshires, Russo)';

    private const CHARGES = 'Conspiracy to advocate the overthrow of the U.S. government, under the Smith Act — a McCarthy-era prosecution of Massachusetts Communist Party leaders (the "Bay State Reds") for their political beliefs and association, brought in 1956.';
    private const CONVICTED = 'No — the charges were dismissed in 1957 after the U.S. Supreme Court\'s Yates v. United States decision; the defendants "won their case."';
    private const SENTENCE = 'No conviction — arrested and prosecuted, but the Smith Act case was dismissed in 1957 without a trial verdict.';

    public function handle(): int {
        $cases = [
            [
                'name' => 'Otis Hood', 'first' => 'Otis', 'last' => 'Hood',
                'gender' => 'Male', 'birthdate' => null, 'death' => null,
                'ideologies' => ['Communism'], 'affiliation' => ['Communist Party USA'],
                'bio' => 'Otis Archer Hood was the chairman of the Massachusetts Communist Party and the lead figure among the "Bay State Reds" prosecuted in the McCarthy era. In 1954 he was among Communists arrested for violating Massachusetts\'s state ban on the Communist Party, winning acquittal, and he was acquitted again after a 1956 state indictment for inciting the overthrow of the government. He was then indicted with his comrades under the federal Smith Act for conspiracy to advocate the government\'s overthrow — charges that were dismissed in 1957 after the Supreme Court\'s Yates decision. Hood spent decades as a Communist and labor organizer in Massachusetts; his papers are held at the University of Massachusetts Amherst.',
            ],
            [
                'name' => 'Anne Timpson', 'first' => 'Anne', 'last' => 'Timpson',
                'gender' => 'Female', 'birthdate' => '1911-05-24', 'death' => '2002-07-09',
                'ideologies' => ['Communism', 'Labor'], 'affiliation' => ['Communist Party USA', 'National Textile Workers Union'],
                'bio' => 'Anne Burlak Timpson (1911–2002), known as "the Red Flame," was a legendary labor organizer and Massachusetts Communist Party leader. The daughter of Ukrainian immigrants, she left school at 14 to work in a silk mill, joined the Communist Party at 16, and in 1933 was elected national secretary of the National Textile Workers Union — reportedly the first American woman to hold so high a union post. The press dubbed her "the Red Flame" for her fiery mill-gate speeches during the great New England textile strikes of the 1930s, a label the mill workers adopted with affection. In 1956 she was among the last Americans arrested under the Smith Act, charged with conspiracy to advocate the overthrow of the government; the case was dropped for lack of evidence after the Yates decision.',
            ],
            [
                'name' => 'Daniel Boone Schirmer', 'first' => 'Daniel', 'last' => 'Schirmer',
                'gender' => 'Male', 'birthdate' => '1915-02-22', 'death' => '2006-04-21',
                'ideologies' => ['Communism', 'Anti-imperialism'], 'affiliation' => ['Communist Party USA'],
                'bio' => 'Daniel Boone Schirmer (1915–2006) was a Harvard-educated Massachusetts Communist Party leader who became one of the "Bay State Reds" prosecuted under the Smith Act. In 1949 and 1951 he had represented the CPUSA in challenges to Massachusetts loyalty-oath laws barring Communists from office. Facing Smith Act sedition charges, he lived underground for about four years before voluntarily surrendering in October 1955; the prosecution of the Massachusetts defendants dragged on until the case collapsed after the 1957 Yates decision. Later disillusioned with the Soviet Union but not with socialism, Schirmer returned to graduate school, earned a Ph.D. from Boston University in 1972, and became a New Left historian and anti-imperialist — best known for "Republic or Empire: American Resistance to the Philippine War" and a lifetime of Philippine solidarity work.',
            ],
            [
                'name' => 'Sidney Lipshires', 'first' => 'Sidney', 'last' => 'Lipshires',
                'gender' => 'Male', 'birthdate' => null, 'death' => null,
                'ideologies' => ['Communism'], 'affiliation' => ['Communist Party USA'],
                'bio' => 'Sidney Lipshires joined the Communist Party in 1939 and in 1947 ran for city alderman in Springfield, Massachusetts on the Communist ticket. On May 29, 1956 — the day his daughter was born — he was arrested under the Smith Act for his Communist Party activities, and he went on to serve as executive secretary of the Massachusetts Smith Act Defendants Committee, publishing a pamphlet exposing the law and the tactics used against the accused. The case never reached trial: the Smith Act prosecution collapsed after the Supreme Court\'s 1957 Yates decision. Lipshires later left the Party, remained active in organized labor, earned a Ph.D., and taught history at Manchester Community College in Connecticut for some thirty years.',
            ],
            [
                'name' => 'Michael Russo', 'first' => 'Michael', 'last' => 'Russo',
                'gender' => 'Male', 'birthdate' => null, 'death' => null,
                'ideologies' => ['Communism'], 'affiliation' => ['Communist Party USA'],
                'bio' => 'Michael A. Russo was one of the Massachusetts "Bay State Reds" — the Communist Party members arrested in 1956 and prosecuted under the Smith Act for conspiracy to advocate the overthrow of the U.S. government. He was photographed with his fellow defendants — Otis Archer Hood, Anne Burlak Timpson, Daniel Boone Schirmer, Sidney Lipshires, Geoffrey Warner White, and Edward Strong — after they won their case in 1957, when the prosecution was dismissed in the wake of the Supreme Court\'s Yates decision. Beyond his role as a Massachusetts Smith Act defendant, the individual record of his life is thinly documented.',
            ],
        ];

        DB::transaction(function () use ($cases) {
            foreach ($cases as $c) {
                if (Prisoner::where('name', $c['name'])->exists()) {
                    $this->warn("Skipped (already exists): {$c['name']}");
                    continue;
                }

                $prisoner = Prisoner::create([
                    'name'           => $c['name'],
                    'first_name'     => $c['first'],
                    'last_name'      => $c['last'],
                    'description'    => $c['bio'],
                    'gender'         => $c['gender'],
                    'birthdate'      => $c['birthdate'],
                    'death_date'     => $c['death'],
                    'state'          => 'Massachusetts',
                    'era'            => '1950s',
                    'ideologies'     => $c['ideologies'],
                    'affiliation'    => $c['affiliation'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'charges'     => self::CHARGES,
                    'convicted'   => self::CONVICTED,
                    'sentence'    => self::SENTENCE,
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
