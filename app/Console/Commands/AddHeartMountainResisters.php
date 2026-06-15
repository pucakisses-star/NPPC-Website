<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * The Heart Mountain Fair Play Committee — the organized Japanese American draft
 * resistance inside the WWII concentration camps (a gap distinct from the
 * Korematsu/Hirabayashi/Yasui/Endo internment test cases, which are already in
 * the database via the Goldstein roster). Adds the seven convicted Committee
 * leaders plus journalist James Omura (acquitted). Sourced to the Densho
 * Encyclopedia, the Heart Mountain Wyoming Foundation, resisters.com, and
 * Wikipedia. Idempotent (skips by name).
 */
class AddHeartMountainResisters extends Command {
    protected $signature = 'prisoners:add-heart-mountain';
    protected $description = 'Add the Heart Mountain Fair Play Committee leaders + James Omura (1944 Nisei draft resistance)';

    private const FPC = "%s was one of the seven leaders of the Heart Mountain Fair Play Committee, the organized draft-resistance movement among Japanese Americans imprisoned at the Heart Mountain concentration camp in Wyoming during World War II. The Committee held that it was unconstitutional to draft incarcerated Nisei — U.S. citizens stripped of their rights and held behind barbed wire with their families — unless those citizenship rights were first restored. In October 1944 the leaders were tried in federal court in Cheyenne for conspiracy to counsel draft evasion; the jury convicted all seven, and on November 2, 1944 Judge Eugene Rice sentenced them to four years at the Leavenworth federal penitentiary. The U.S. Court of Appeals for the Tenth Circuit overturned the conspiracy convictions in 1945.";

    private const FPC_CHARGES = 'Conspiracy to counsel draft evasion — for leading the Heart Mountain Fair Play Committee, which urged imprisoned Nisei to refuse induction until their constitutional rights as U.S. citizens were restored.';
    private const FPC_CONVICTED = 'Yes — convicted by a federal jury in Cheyenne in October 1944 and sentenced to four years at Leavenworth; the Tenth Circuit overturned the conspiracy convictions in 1945.';
    private const FPC_SENTENCE = 'Four years at the U.S. Penitentiary, Leavenworth; the convictions were reversed on appeal in 1945.';

    public function handle(): int {
        $leavenworth = ['name' => 'United States Penitentiary, Leavenworth', 'city' => 'Leavenworth', 'state' => 'Kansas'];

        $cases = [
            [
                'name' => 'Kiyoshi Okamoto', 'first' => 'Kiyoshi', 'last' => 'Okamoto',
                'lead' => 'Kiyoshi Okamoto, the camp construction inspector and self-styled "Fair Play Committee of One" who founded the movement,',
                'institution' => $leavenworth,
            ],
            [
                'name' => 'Frank Emi', 'first' => 'Frank', 'last' => 'Emi',
                'lead' => 'Frank Emi (1916–2010), a Los Angeles grocer and judo instructor who became the most prominent leader of the resistance,',
                'institution' => $leavenworth,
            ],
            [
                'name' => 'Sam Horino', 'first' => 'Sam', 'last' => 'Horino',
                'lead' => 'Sam Horino, a steering-committee member of the Fair Play Committee,',
                'institution' => $leavenworth,
            ],
            [
                'name' => 'Guntaro Kubota', 'first' => 'Guntaro', 'last' => 'Kubota',
                'lead' => 'Guntaro Kubota, an Issei (first-generation immigrant) member who stood with the Nisei leaders despite not being subject to the draft himself,',
                'institution' => $leavenworth,
            ],
            [
                'name' => 'Paul Nakadate', 'first' => 'Paul', 'last' => 'Nakadate',
                'lead' => 'Paul Nakadate, a steering-committee member of the Fair Play Committee,',
                'institution' => $leavenworth,
            ],
            [
                'name' => 'Min Tamesa', 'first' => 'Min', 'last' => 'Tamesa',
                'lead' => 'Minoru "Min" Tamesa, a steering-committee member of the Fair Play Committee,',
                'institution' => $leavenworth,
            ],
            [
                'name' => 'Ben Wakaye', 'first' => 'Ben', 'last' => 'Wakaye',
                'lead' => 'Ben Wakaye, a steering-committee member of the Fair Play Committee,',
                'institution' => $leavenworth,
            ],
        ];

        DB::transaction(function () use ($cases) {
            foreach ($cases as $c) {
                if (Prisoner::where('name', $c['name'])->exists()) {
                    $this->warn("Skipped (already exists): {$c['name']}");
                    continue;
                }

                $instId = null;
                if ($c['institution']) {
                    $inst = Institution::firstOrCreate(
                        ['name' => $c['institution']['name']],
                        ['city' => $c['institution']['city'], 'state' => $c['institution']['state']]
                    );
                    $instId = $inst->id;
                }

                $prisoner = Prisoner::create([
                    'name'           => $c['name'],
                    'first_name'     => $c['first'],
                    'last_name'      => $c['last'],
                    'description'    => sprintf(self::FPC, $c['lead']),
                    'gender'         => 'Male',
                    'race'           => 'Japanese American',
                    'state'          => 'Wyoming',
                    'era'            => '1940s',
                    'ideologies'     => ['Civil rights', 'Draft resistance'],
                    'affiliation'    => ['Heart Mountain Fair Play Committee'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id'    => $prisoner->id,
                    'institution_id' => $instId,
                    'charges'        => self::FPC_CHARGES,
                    'convicted'      => self::FPC_CONVICTED,
                    'sentence'       => self::FPC_SENTENCE,
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }

            // James Omura — co-defendant journalist, acquitted on First Amendment grounds
            if (Prisoner::where('name', 'James Omura')->exists()) {
                $this->warn('Skipped (already exists): James Omura');
            } else {
                $omura = Prisoner::create([
                    'name'           => 'James Omura',
                    'first_name'     => 'James',
                    'last_name'      => 'Omura',
                    'description'    => 'James Matsumoto Omura (1912–1994) was a Japanese American journalist and the English-language editor of the Denver newspaper Rocky Shimpo, who used his editorials to defend the right of the Heart Mountain draft resisters to challenge their treatment. For this he was indicted in 1944 alongside the seven Fair Play Committee leaders on a charge of conspiracy to counsel draft evasion. At the Cheyenne trial Omura mounted a First Amendment defense and was the only one of the eight defendants acquitted. Ostracized within his own community for years afterward, he was recognized decades later as a defender of press freedom and Japanese American civil rights.',
                    'gender'         => 'Male',
                    'race'           => 'Japanese American',
                    'state'          => 'Colorado',
                    'era'            => '1940s',
                    'ideologies'     => ['Civil rights', 'Press freedom'],
                    'affiliation'    => ['Rocky Shimpo'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id' => $omura->id,
                    'charges'     => 'Conspiracy to counsel draft evasion — for his Rocky Shimpo editorials defending the Heart Mountain draft resisters; an indictment his supporters viewed as an attack on freedom of the press.',
                    'convicted'   => 'No — acquitted at the 1944 Cheyenne trial on First Amendment grounds, the only one of the eight defendants found not guilty.',
                    'sentence'    => null,
                ]);

                $this->info("Added: {$omura->name} (slug: {$omura->slug})");
            }
        });

        return self::SUCCESS;
    }
}
