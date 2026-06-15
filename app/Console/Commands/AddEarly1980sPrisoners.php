<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Four political-prisoner cases surfaced from the 1979–85 Workers Vanguard /
 * Militant widening, cross-checked as not already in the database:
 *   - Marshall "Eddie" Conway (Baltimore Black Panther; ~44 years)
 *   - Eddie James Carthan (Tchula, MS mayor frame-up)
 *   - Frank "Moon" Muscare (1980 Chicago firefighters' strike)
 *   - Wayne Cryts (Missouri farm-bankruptcy protest)
 * Sourced to Wikipedia, the Baltimore Sun, the Washington Post, UPI, and the
 * papers themselves. Idempotent (skips by name).
 */
class AddEarly1980sPrisoners extends Command {
    protected $signature = 'prisoners:add-early-1980s';
    protected $description = 'Add Conway, Carthan, Muscare, and Cryts (1979–85 WV/Militant widening)';

    public function handle(): int {
        $cases = [
            [
                'name' => 'Marshall Conway', 'first' => 'Marshall', 'last' => 'Conway',
                'gender' => 'Male', 'race' => 'Black', 'state' => 'Maryland', 'era' => '1970s',
                'ideologies' => ['Black Power', 'Black liberation'],
                'affiliation' => ['Black Panther Party (Baltimore chapter)'],
                'institution' => ['name' => 'Maryland Penitentiary', 'city' => 'Baltimore', 'state' => 'Maryland'],
                'bio' => 'Marshall "Eddie" Conway was a leader of the Baltimore chapter of the Black Panther Party who spent roughly 44 years in prison — among the longest-held political prisoners in the United States — for a 1970 killing he always insisted was a frame-up. Conway was convicted in 1971 of the ambush murder of Baltimore Police Officer Donald Sager. He maintained that he had been targeted for his Panther organizing under the FBI\'s COINTELPRO program; there was no physical evidence linking him to the shooting, and the case leaned heavily on a jailhouse informant. He was released on parole in March 2014 after a Maryland appellate court ruled that his jury had been given improper instructions. In prison Conway earned three college degrees and founded a prisoners\' literacy program; after his release he became a producer at The Real News Network and hosted the show "Rattling the Bars." He died in February 2023.',
                'charges' => 'First-degree murder in the 1970 ambush killing of Baltimore Police Officer Donald Sager — a conviction Conway always maintained was an FBI/police frame-up of a Black Panther leader (no physical evidence tied him to the shooting; the case relied on a jailhouse informant).',
                'convicted' => 'Convicted in 1971 and sentenced to life. Released on parole in March 2014 after an appellate court found the jury had been improperly instructed.',
                'sentence' => 'Life imprisonment; served about 44 years before his 2014 release — among the longest-held U.S. political prisoners.',
            ],
            [
                'name' => 'Eddie Carthan', 'first' => 'Eddie', 'last' => 'Carthan',
                'gender' => 'Male', 'race' => 'Black', 'state' => 'Mississippi', 'era' => '1980s',
                'ideologies' => ['Civil rights', 'Black political empowerment'],
                'affiliation' => [],
                'institution' => null,
                'bio' => 'Eddie James Carthan was elected in 1977 as the first Black mayor of Tchula, Mississippi since Reconstruction — and his term provoked a campaign of prosecutions that civil-rights groups nationwide denounced as a racist frame-up. After Carthan tried to install his own appointee as police chief in 1980 (over a rival, white chief the aldermen had appointed), he was beaten by police at City Hall, charged with assault, convicted, sentenced to three years, and forced from office. In 1981 he was charged with capital murder for allegedly hiring gunmen to kill a Tchula alderman and political rival, and faced the death penalty. A national "Free Eddie Carthan" campaign drew support from civil-rights, labor, and church organizations, and in 1982 a jury acquitted him of the murder. Carthan maintained throughout that he had been targeted by the local white power structure; after his acquittal he traveled the country supporting other frame-up victims.',
                'charges' => 'A series of prosecutions after he became the first Black mayor of Tchula, Mississippi since Reconstruction: an assault conviction (1981) carrying three years, after he was beaten by police in a dispute over control of the police department, which forced him from office; and a 1981 capital-murder charge for allegedly ordering the killing of a political rival — of which a jury acquitted him in 1982.',
                'convicted' => 'Convicted of assault (1981) and sentenced to three years; acquitted of capital murder in 1982 after a national defense campaign.',
                'sentence' => 'Three years on the assault conviction; acquitted of murder.',
            ],
            [
                'name' => 'Frank Muscare', 'first' => 'Frank', 'last' => 'Muscare',
                'gender' => 'Male', 'race' => null, 'state' => 'Illinois', 'era' => '1980s',
                'ideologies' => ['Labor', 'Trade unionism'],
                'affiliation' => ['Chicago Fire Fighters Union, Local 2 (IAFF)'],
                'institution' => null,
                'bio' => 'Frank "Moon" Muscare was the president of Chicago Fire Fighters Union Local 2 during the bitter 1980 Chicago firefighters\' strike against Mayor Jane Byrne. In February 1980, after contract talks collapsed, roughly 4,000 firefighters walked out in defiance of a court restraining order — the first firefighters\' strike in the city\'s history. Muscare refused to order his members back to work, and a judge jailed him for contempt of court, sentencing him to five months and reportedly citing his "big mouth," while levying fines that reached $40,000 a day against the union and its officials. The walkout lasted 23 days before the union ended it on March 8, 1980. Muscare died in 2005.',
                'charges' => 'Criminal contempt of court for leading and refusing to end the February 1980 Chicago firefighters\' strike — in which some 4,000 members of Fire Fighters Union Local 2 walked out against Mayor Jane Byrne in defiance of a court restraining order.',
                'convicted' => 'Jailed for contempt and sentenced to five months; the court also levied fines reaching $40,000 a day against the union and its officers.',
                'sentence' => 'Five months in jail (1980) for contempt.',
            ],
            [
                'name' => 'Wayne Cryts', 'first' => 'Wayne', 'last' => 'Cryts',
                'gender' => 'Male', 'race' => null, 'state' => 'Missouri', 'era' => '1980s',
                'ideologies' => ['Farmers movement'],
                'affiliation' => [],
                'institution' => null,
                'bio' => 'Wayne Cryts was a southeast-Missouri soybean farmer who became a national symbol of the early-1980s farm crisis after he was jailed for defying a federal court in a protest over grain-elevator bankruptcy law — a law that stripped farmers of ownership of crops they had stored if the elevator company went bankrupt. In February 1981, more than 3,000 farmers rallied in New Madrid, Missouri to support him, and that July Cryts and others removed his soybeans from the bankrupt Bernie, Missouri elevator. When the bankruptcy court demanded he name who had helped him, Cryts refused, and on April 28, 1982 a U.S. Bankruptcy Court judge jailed him for civil contempt in Russellville, Arkansas under an indefinite sentence. His jailing drew national attention to the farm movement, and Congress later changed the bankruptcy law in response to his case.',
                'charges' => 'Civil contempt of a federal bankruptcy court for refusing to say who had helped him remove his soybeans from a bankrupt grain elevator in Bernie, Missouri — an act of protest against bankruptcy law that stripped farmers of ownership of crops stored in failed elevators.',
                'convicted' => 'Jailed for civil contempt (not a criminal conviction) by a U.S. Bankruptcy Court judge, beginning April 28, 1982.',
                'sentence' => 'Indefinite civil-contempt jailing (1982) in Russellville, Arkansas; released later that year. Congress subsequently amended the bankruptcy law in response to his case.',
            ],
        ];

        DB::transaction(function () use ($cases) {
            foreach ($cases as $c) {
                if (Prisoner::where('name', $c['name'])->exists()) {
                    $this->warn("Skipped (already exists): {$c['name']}");
                    continue;
                }

                $institutionId = null;
                if ($c['institution']) {
                    $inst = Institution::firstOrCreate(
                        ['name' => $c['institution']['name']],
                        ['city' => $c['institution']['city'], 'state' => $c['institution']['state']]
                    );
                    $institutionId = $inst->id;
                }

                $prisoner = Prisoner::create([
                    'name'           => $c['name'],
                    'first_name'     => $c['first'],
                    'last_name'      => $c['last'],
                    'description'    => $c['bio'],
                    'gender'         => $c['gender'],
                    'race'           => $c['race'],
                    'state'          => $c['state'],
                    'era'            => $c['era'],
                    'ideologies'     => $c['ideologies'],
                    'affiliation'    => $c['affiliation'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id'    => $prisoner->id,
                    'institution_id' => $institutionId,
                    'charges'        => $c['charges'],
                    'convicted'      => $c['convicted'],
                    'sentence'       => $c['sentence'],
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
