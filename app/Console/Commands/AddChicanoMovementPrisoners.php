<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Batch 1 of the WV/Militant comprehensive-sweep additions: Chicano / Raza Unida
 * movement political prisoners (1970s), cross-checked as not already in the
 * database (Francisco "Kiko" Martinez and Los Tres were already in). Sourced to
 * Wikipedia, Texas Monthly, NCpedia-equivalents, court records, and the papers.
 * Idempotent (skips by name).
 */
class AddChicanoMovementPrisoners extends Command {
    protected $signature = 'prisoners:add-chicano-movement';
    protected $description = 'Add Chicano/Raza Unida movement prisoners (Muniz, Cantu, Moody Park Three, Moises Morales)';

    private const MOODY = "%s was one of the \"Moody Park Three,\" members of People United to Fight Police Brutality charged with felony riot over the May 7, 1978 Moody Park rebellion in Houston. The uprising erupted from outrage over the killing of José Campos Torres — a young Mexican-American man beaten by Houston police and thrown into Buffalo Bayou to drown in 1977, whose killers received only one year's probation and a \$1 fine. The three co-defendants — Travis Morales, Mara Youngdahl, and Thomas Hirschi — faced more than 140 years in prison combined. At their 1979 trial they openly defended the rebellion as a justified response to police terror, and were acquitted.";

    private const MOODY_CHARGES = 'Felony riot for allegedly inciting the May 7, 1978 Moody Park rebellion in Houston — the uprising that followed the police killing of José Campos Torres and the officers\' punishment of one year\'s probation and a $1 fine; as a member of People United to Fight Police Brutality, faced part of a combined 140-plus years.';
    private const MOODY_CONVICTED = 'No — acquitted at the 1979 trial, at which the defendants openly defended the rebellion.';

    public function handle(): int {
        $cases = [
            [
                'name' => 'Ramsey Muniz', 'first' => 'Ramsey', 'last' => 'Muniz',
                'gender' => 'Male', 'race' => 'Hispanic', 'birthdate' => '1942-12-13', 'death' => '2022-10-02',
                'state' => 'Texas', 'ideologies' => ['Chicano movement', 'Raza Unida'], 'affiliation' => ['Raza Unida Party'],
                'bio' => 'Ramiro "Ramsey" Muñiz (December 13, 1942 – October 2, 2022) was a Mexican-American attorney and the gubernatorial candidate of the Raza Unida Party — the independent Chicano political party — in Texas in 1972 and 1974, the first Latino to appear on a Texas general-election ballot (he drew some 214,000 votes in 1972). In July and November 1976, federal authorities charged him with conspiracy to smuggle marijuana; he pleaded guilty to one count and served five years in federal prison, a prosecution his supporters saw as political retaliation that effectively destroyed the Raza Unida Party. In 1994 Muñiz was convicted on another drug charge and sentenced to life without parole — a case his many supporters across the Chicano movement also denounced as a frame-up. After a long "Free Ramsey Muñiz" campaign, he was released from federal prison on December 10, 2018, and died in 2022.',
                'charges' => 'Federal drug charges — the Raza Unida Party\'s gubernatorial candidate was charged in 1976 with conspiracy to smuggle marijuana (guilty plea, five years); he was again convicted on a drug charge in 1994 (life), a prosecution supporters likewise considered political targeting.',
                'convicted' => 'Yes — 1976 guilty plea (five years served); 1994 conviction (life without parole), released December 10, 2018.',
                'sentence' => 'Five years (1976); a 1994 life sentence, released in 2018.',
            ],
            [
                'name' => 'Mario Cantu', 'first' => 'Mario', 'last' => 'Cantu',
                'gender' => 'Male', 'race' => 'Hispanic', 'birthdate' => '1937-04-02', 'death' => '2000-11-09',
                'state' => 'Texas', 'ideologies' => ['Chicano movement'], 'affiliation' => ['Tu Casa', 'Mario Cantú Defense Committee'],
                'bio' => 'Mario Cantú (April 2, 1937 – November 9, 2000) was a San Antonio restaurateur and Chicano-movement activist who became, by his account, the first U.S. citizen convicted of harboring undocumented immigrants. Radicalized during a 1962 drug imprisonment, he turned his family\'s West Side restaurant into a hub of Chicano organizing and founded "Tu Casa," a group that helped Mexican immigrants obtain legal status, along with a committee against police brutality. His immigrant-aid work brought a federal prosecution for harboring undocumented workers; despite a Mario Cantú Defense Committee that drew prominent Chicano, political, and religious support, he was convicted and sentenced to five years\' probation. In 1978, summoned to explain trips to Mexico the government deemed probation violations, Cantú chose self-exile instead — traveling to Paris for a year-long campaign denouncing U.S. human-rights abuses against Chicanos and Mexicans.',
                'charges' => 'Conviction for harboring undocumented workers — reportedly the first U.S. citizen convicted of the charge — for his immigrant-aid work through the group "Tu Casa."',
                'convicted' => 'Yes — convicted of harboring undocumented immigrants; sentenced to five years\' probation. He went into self-exile in 1978 rather than face a probation-violation prosecution.',
                'sentence' => 'Five years\' probation (followed by a year of self-exile in Paris).',
            ],
            [
                'name' => 'Moises Morales', 'first' => 'Moises', 'last' => 'Morales',
                'gender' => 'Male', 'race' => 'Hispanic', 'birthdate' => null, 'death' => null,
                'state' => 'New Mexico', 'ideologies' => ['Chicano movement', 'Raza Unida'], 'affiliation' => ['La Raza Unida Party'],
                'bio' => 'Moisés Morales was a La Raza Unida Party activist in Rio Arriba County, New Mexico, who challenged the county\'s longtime Democratic political boss and sheriff, Emilio Naranjo. In 1975, Naranjo\'s men planted marijuana in Morales\'s truck — and in that of fellow activist Antonio "Ike" DeVargas — in what Morales\'s lawyer called one of the most obvious cases of planted evidence and political retaliation he had ever seen. At a December 6, 1976 trial, a jury heard testimony that the sheriff had framed him and acquitted Morales. Naranjo was afterward convicted of perjury in connection with the planted evidence, though the New Mexico Supreme Court overturned that conviction in 1980. Morales later won civil settlements over the frame-up.',
                'charges' => 'Marijuana-possession charges that a jury found had been planted in his truck by the machine of Rio Arriba County boss and sheriff Emilio Naranjo — political retaliation against a La Raza Unida activist who ran against Naranjo for sheriff.',
                'convicted' => 'No — acquitted on December 6, 1976 after the jury heard the marijuana had been planted; Naranjo was later convicted of perjury (overturned 1980), and Morales won civil settlements.',
                'sentence' => null,
            ],
            [
                'name' => 'Travis Morales', 'first' => 'Travis', 'last' => 'Morales',
                'gender' => 'Male', 'race' => 'Hispanic', 'birthdate' => null, 'death' => null,
                'state' => 'Texas', 'ideologies' => ['Chicano movement', 'Anti-police brutality'], 'affiliation' => ['People United to Fight Police Brutality', 'Revolutionary Communist Party'],
                'bio' => sprintf(self::MOODY, 'Travis Morales'),
                'charges' => self::MOODY_CHARGES, 'convicted' => self::MOODY_CONVICTED, 'sentence' => null,
            ],
            [
                'name' => 'Mara Youngdahl', 'first' => 'Mara', 'last' => 'Youngdahl',
                'gender' => 'Female', 'race' => null, 'birthdate' => null, 'death' => null,
                'state' => 'Texas', 'ideologies' => ['Anti-police brutality', 'Revolutionary communism'], 'affiliation' => ['People United to Fight Police Brutality', 'Revolutionary Communist Party'],
                'bio' => sprintf(self::MOODY, 'Mara Youngdahl'),
                'charges' => self::MOODY_CHARGES, 'convicted' => self::MOODY_CONVICTED, 'sentence' => null,
            ],
            [
                'name' => 'Thomas Hirschi', 'first' => 'Thomas', 'last' => 'Hirschi',
                'gender' => 'Male', 'race' => null, 'birthdate' => null, 'death' => null,
                'state' => 'Texas', 'ideologies' => ['Anti-police brutality', 'Revolutionary communism'], 'affiliation' => ['People United to Fight Police Brutality', 'Revolutionary Communist Party'],
                'bio' => sprintf(self::MOODY, 'Thomas Hirschi'),
                'charges' => self::MOODY_CHARGES, 'convicted' => self::MOODY_CONVICTED, 'sentence' => null,
            ],
        ];

        DB::transaction(function () use ($cases) {
            foreach ($cases as $c) {
                if (Prisoner::where('name', $c['name'])->exists()) {
                    $this->warn("Skipped (already exists): {$c['name']}");
                    continue;
                }

                $prisoner = Prisoner::create([
                    'name' => $c['name'], 'first_name' => $c['first'], 'last_name' => $c['last'],
                    'description' => $c['bio'], 'gender' => $c['gender'], 'race' => $c['race'],
                    'birthdate' => $c['birthdate'], 'death_date' => $c['death'],
                    'state' => $c['state'], 'era' => '1970s',
                    'ideologies' => $c['ideologies'], 'affiliation' => $c['affiliation'],
                    'in_custody' => false, 'released' => true, 'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'charges' => $c['charges'], 'convicted' => $c['convicted'], 'sentence' => $c['sentence'],
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
