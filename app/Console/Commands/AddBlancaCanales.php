<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds (or corrects) Blanca Canales — the Puerto Rican Nationalist leader who
 * led the October 30, 1950 Jayuya Uprising (El Grito de Jayuya), raising the
 * outlawed Puerto Rican flag and proclaiming the Republic of Puerto Rico. One
 * of the few women in history to lead an armed revolt against the United
 * States, she was sentenced to life plus sixty years and imprisoned for about
 * seventeen years before a full pardon.
 *
 * Dates: secondary biographies date her imprisonment to the October 30, 1950
 * uprising and her pardon/release to AUGUST 1967 (not the "17-years-to-the-day"
 * October 31 figure); no source gives the exact release day, so it is stored as
 * 1967-08-01 with the sentence text noting the imprecision. Matches the 1950s
 * Nationalist Party convention used for Lolita Lebrón and Andrés Figueroa
 * Cordero.
 *
 * Idempotent: if the record already exists (e.g. an earlier run used the
 * placeholder November dates) it is updated in place rather than skipped, so
 * re-running always converges on the corrected arrest/release dates.
 */
final class AddBlancaCanales extends Command
{
    protected $signature = 'prisoners:add-blanca-canales';

    protected $description = 'Add or correct Blanca Canales (leader of the 1950 Jayuya Uprising)';

    public function handle(): int
    {
        $fields = [
            'name' => 'Blanca Canales',
            'first_name' => 'Blanca',
            'last_name' => 'Canales',
            'aka' => 'Blanca Canales Torresola',
            'gender' => 'Female',
            'race' => 'Hispanic',
            'state' => 'Puerto Rico',
            'era' => '1950s',
            'birthdate' => '1906-02-17',
            'death_date' => '1996-07-25',
            'ideologies' => ['Puerto Rican Independence', 'Anti-colonial'],
            'affiliation' => ['Puerto Rican Nationalist Party'],
            'in_custody' => false,
            'released' => true,
            'awaiting_trial' => false,
            'description' => "Blanca Canales Torresola (1906–1996) was a Puerto Rican Nationalist leader and one of the few women in history to lead an armed revolt against the United States. A leader of the Puerto Rican Nationalist Party who helped organize its women's branch, the Daughters of Freedom (Hijas de la Libertad), she led the Jayuya Uprising on October 30, 1950 — raising the outlawed Puerto Rican flag in the town plaza and proclaiming Puerto Rico a free republic. Nationalists held Jayuya for three days until U.S. military forces bombed and retook the town, and she was imprisoned for leading the uprising. Convicted in connection with the revolt, she was sentenced to life imprisonment plus sixty years and held at the Alderson Federal Prison Camp in West Virginia and later the women's prison at Vega Alta, Puerto Rico, before receiving a full pardon from Governor Roberto Sánchez Vilella in August 1967 after about seventeen years.",
        ];

        $case = [
            'charges' => 'Leading the October 30, 1950 Jayuya Uprising (El Grito de Jayuya) — raising the outlawed Puerto Rican flag and proclaiming the Republic of Puerto Rico; charged with killing a police officer, wounding three others, and burning the town post office',
            'arrest_date' => '1950-10-30',
            'incarceration_date' => '1950-10-30',
            'release_date' => '1967-08-01',
            'convicted' => 'Yes — convicted in connection with the Jayuya Uprising',
            'sentence' => "Life imprisonment plus sixty years; held at the Alderson Federal Prison Camp (West Virginia) and the women's prison at Vega Alta, Puerto Rico, until a full pardon by Governor Roberto Sánchez Vilella in August 1967 (about seventeen years served; the exact day of release is not documented)",
        ];

        $existing = Prisoner::withUnderReview()->where('name', 'Blanca Canales')->first();

        if (! $existing) {
            DB::transaction(function () use ($fields, $case) {
                $prisoner = Prisoner::create($fields);
                $case['prisoner_id'] = $prisoner->id;
                PrisonerCase::create($case);
            });
            $this->info('Added Blanca Canales.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($existing, $fields, $case) {
            $existing->fill($fields)->save();

            $row = $existing->cases()->first();
            if ($row) {
                $row->fill($case)->save();
            } else {
                $case['prisoner_id'] = $existing->id;
                PrisonerCase::create($case);
            }
        });
        $this->info('Updated Blanca Canales (corrected arrest → 1950-10-30, release → August 1967).');

        return self::SUCCESS;
    }
}
