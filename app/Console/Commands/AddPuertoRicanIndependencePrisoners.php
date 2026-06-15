<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Batch 6 of the comprehensive-sweep additions: Puerto Rican independence
 * prisoners NOT already in the database (the 1954 Capitol Four — Lebrón, Cancel
 * Miranda, Flores, Figueroa Cordero — plus Albizu Campos and Víctor Gerena are
 * already present). Fills the gaps:
 *   - Oscar Collazo         (1950 Blair House attack; idempotent skip if present)
 *   - Ángel Rodríguez Cristóbal (Vieques; died in federal custody, 1979)
 *   - Filiberto Ojeda Ríos  (Los Macheteros; killed by the FBI, 2005)
 *   - Juan Segarra Palmer   (Los Macheteros; Wells Fargo case; clemency 1999)
 * Sourced to the court records, the Truman Library, UPI/AP, The Nation, Claridad,
 * and the DOJ OIG review of the Ojeda Ríos shooting. Idempotent (skips by name).
 */
class AddPuertoRicanIndependencePrisoners extends Command {
    protected $signature = 'prisoners:add-pr-independence';
    protected $description = 'Add PR independence gaps (Collazo, Rodríguez Cristóbal, Ojeda Ríos, Segarra Palmer)';

    public function handle(): int {
        $cases = [
            [
                'name' => 'Oscar Collazo', 'first' => 'Oscar', 'last' => 'Collazo',
                'gender' => 'Male', 'state' => 'Puerto Rico', 'era' => '1950s',
                'birthdate' => null, 'death' => null, 'released' => true,
                'ideologies' => ['Puerto Rican independence', 'Anti-colonial'],
                'affiliation' => ['Puerto Rican Nationalist Party'],
                'institution' => ['name' => 'United States Penitentiary, Leavenworth', 'city' => 'Leavenworth', 'state' => 'Kansas'],
                'bio' => 'Oscar Collazo was a Puerto Rican Nationalist who, with Griselio Torresola, attempted to assassinate President Harry Truman at Blair House in Washington on November 1, 1950, seeking to draw world attention to Puerto Rico\'s colonial status and to the bloody suppression of the Nationalist uprising on the island days earlier. In the gun battle Torresola and White House policeman Leslie Coffelt were killed and Collazo was wounded. Tried in federal court in Washington, Collazo was convicted on March 7, 1951 and sentenced to death; President Truman commuted the sentence to life in prison in 1952. He served 28 years, much of it at the U.S. Penitentiary at Leavenworth, until President Jimmy Carter commuted his sentence in 1979, freeing him together with the surviving Nationalists imprisoned for the 1954 attack on Congress. Collazo returned to Puerto Rico to a hero\'s welcome and remained an independence militant until his death in 1994.',
                'charges' => 'The November 1, 1950 armed attack on Blair House aimed at killing President Truman — an act of Puerto Rican Nationalist protest against U.S. colonial rule, in which a White House guard and Collazo\'s comrade Griselio Torresola were killed.',
                'convicted' => 'Yes — convicted in federal court on March 7, 1951 and sentenced to death; Truman commuted it to life in 1952.',
                'sentence' => 'Death, commuted to life in 1952; served 28 years before President Carter commuted his sentence and freed him in 1979.',
            ],
            [
                'name' => 'Ángel Rodríguez Cristóbal', 'first' => 'Ángel', 'last' => 'Rodríguez Cristóbal',
                'gender' => 'Male', 'state' => 'Puerto Rico', 'era' => '1970s',
                'birthdate' => null, 'death' => '1979-11-11', 'released' => false,
                'ideologies' => ['Puerto Rican independence', 'Anti-militarism', 'Socialism'],
                'affiliation' => [],
                'institution' => ['name' => 'Federal Correctional Institution, Tallahassee', 'city' => 'Tallahassee', 'state' => 'Florida'],
                'bio' => 'Ángel Rodríguez Cristóbal was a Puerto Rican independence and socialist militant and a leader of the civil-disobedience campaign against the U.S. Navy\'s use of the island of Vieques as a bombing range. Arrested on May 21, 1979 along with some twenty others for occupying the Navy\'s restricted zone in Vieques, he was convicted of trespassing, sentenced to six months in federal prison, and transferred to the Federal Correctional Institution in Tallahassee, Florida. On November 11, 1979 he was found dead in his cell. Prison authorities called it a suicide by hanging, but his body bore a deep wound above the left eyebrow and other injuries, and his family, the independence movement, and much of Puerto Rico believed he had been beaten to death. His death made him a martyr of the Vieques and Puerto Rican independence struggles and set off protests across the island.',
                'charges' => 'Trespassing on the U.S. Navy\'s restricted bombing range in Vieques — part of the mass civil-disobedience campaign against the Navy\'s occupation of the island (arrested May 21, 1979 with about twenty other protesters).',
                'convicted' => 'Yes — convicted of trespass and sentenced to six months in federal prison.',
                'sentence' => 'Six months; he was found dead in the Federal Correctional Institution at Tallahassee on November 11, 1979 — ruled a suicide by the authorities but widely believed by his supporters to have been a killing.',
            ],
            [
                'name' => 'Filiberto Ojeda Ríos', 'first' => 'Filiberto', 'last' => 'Ojeda Ríos',
                'gender' => 'Male', 'state' => 'Puerto Rico', 'era' => '1980s',
                'birthdate' => '1933-04-26', 'death' => '2005-09-23', 'released' => false,
                'ideologies' => ['Puerto Rican independence', 'Armed struggle'],
                'affiliation' => ['Los Macheteros (Ejército Popular Boricua)'],
                'institution' => null,
                'bio' => 'Filiberto Ojeda Ríos (April 26, 1933 – September 23, 2005) was the commander-in-chief of Los Macheteros (the Boricua Popular Army), the clandestine Puerto Rican independence organization. He was the most-wanted figure in the 1983 robbery of $7.2 million from a Wells Fargo depot in West Hartford, Connecticut — staged on September 12, the birthday of Nationalist leader Pedro Albizu Campos — to finance the independence struggle. Indicted in 1985, Ojeda exchanged gunfire with FBI agents during his arrest (a Puerto Rico jury later acquitted him of wounding an agent, accepting his self-defense claim) and was freed on bond; in 1990 he cut off his electronic monitor and went underground. Tried in absentia in 1992, he was convicted on fourteen counts and sentenced to 55 years. He remained a fugitive and a folk hero for fifteen years until September 23, 2005 — the anniversary of the Grito de Lares — when FBI agents surrounded his mountain home in Hormigueros and shot him, then left him to bleed to death, in what independence supporters and many Puerto Ricans condemned as a political assassination.',
                'charges' => 'Seditious conspiracy and the 1983 Wells Fargo robbery in West Hartford, Connecticut ($7.2 million to fund the independence movement), as commander of Los Macheteros; also charged with wounding an FBI agent during his 1985 arrest, of which a Puerto Rico jury acquitted him.',
                'convicted' => 'Convicted in absentia in 1992 on fourteen counts and sentenced to 55 years, after going underground in 1990; acquitted by a Puerto Rico jury of shooting the FBI agent.',
                'sentence' => '55 years (convicted in absentia, 1992). A fugitive for 15 years, he was shot and killed by the FBI at his home on September 23, 2005.',
            ],
            [
                'name' => 'Juan Segarra Palmer', 'first' => 'Juan', 'last' => 'Segarra Palmer',
                'gender' => 'Male', 'state' => 'Puerto Rico', 'era' => '1980s',
                'birthdate' => null, 'death' => null, 'released' => true,
                'ideologies' => ['Puerto Rican independence', 'Armed struggle'],
                'affiliation' => ['Los Macheteros (Ejército Popular Boricua)'],
                'institution' => null,
                'bio' => 'Juan Enrique Segarra-Palmer was a founder of Los Macheteros and a central defendant in the prosecution of the 1983 Wells Fargo robbery in Connecticut — the $7.2 million expropriation that funded the Puerto Rican independence underground. Arrested in the FBI\'s mass roundup of Macheteros on August 30, 1985, he was convicted in 1989 of seditious conspiracy and of the robbery, and sentenced to 65 years in prison, later reduced to 55 on appeal. After serving more than fourteen years, he accepted a 1999 clemency offer from President Bill Clinton — the same clemency that freed a group of imprisoned FALN and independentista activists — and was released on January 25, 2004.',
                'charges' => 'Seditious conspiracy and robbery for the 1983 Wells Fargo expropriation in West Hartford, Connecticut, as a founder of Los Macheteros (arrested in the FBI\'s August 30, 1985 roundup).',
                'convicted' => 'Yes — convicted in 1989 of seditious conspiracy and the Wells Fargo robbery.',
                'sentence' => '65 years, reduced to 55 on appeal; released January 25, 2004 after accepting a 1999 clemency commutation from President Clinton.',
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
                    'birthdate'      => $c['birthdate'],
                    'death_date'     => $c['death'],
                    'state'          => $c['state'],
                    'era'            => $c['era'],
                    'ideologies'     => $c['ideologies'],
                    'affiliation'    => $c['affiliation'],
                    'in_custody'     => false,
                    'released'       => $c['released'],
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
