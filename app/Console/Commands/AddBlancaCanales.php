<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Blanca Canales — the Puerto Rican Nationalist leader who led the
 * October 30, 1950 Jayuya Uprising (El Grito de Jayuya), raising the outlawed
 * Puerto Rican flag and proclaiming the Republic of Puerto Rico. One of the
 * few women in history to lead an armed revolt against the United States, she
 * was sentenced to life plus sixty years and imprisoned for about seventeen
 * years before a full pardon in 1967. Matches the 1950s Nationalist Party
 * convention used for Lolita Lebrón and Andrés Figueroa Cordero. The exact
 * 1967 pardon date is not documented, so the release is modeled as ~17 years
 * after her November 1950 capture (the figure cited by sources); the sentence
 * text states this explicitly. Idempotent.
 */
final class AddBlancaCanales extends Command
{
    protected $signature = 'prisoners:add-blanca-canales';

    protected $description = 'Add Blanca Canales (leader of the 1950 Jayuya Uprising; ~17 years imprisoned)';

    public function handle(): int
    {
        if (Prisoner::withUnderReview()->where('name', 'Blanca Canales')->exists()) {
            $this->warn('Blanca Canales already exists — skipping.');

            return self::SUCCESS;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name' => 'Blanca Canales',
                'first_name' => 'Blanca',
                'last_name' => 'Canales',
                'aka' => 'Blanca Canales Torresola',
                'gender' => 'Female',
                'race' => 'Latino',
                'state' => 'Puerto Rico',
                'era' => '1950s',
                'birthdate' => '1906-02-17',
                'death_date' => '1996-07-25',
                'ideologies' => ['Puerto Rican Independence', 'Anti-colonial'],
                'affiliation' => ['Puerto Rican Nationalist Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "Blanca Canales Torresola (1906–1996) was a Puerto Rican Nationalist leader and one of the few women in history to lead an armed revolt against the United States. A leader of the Puerto Rican Nationalist Party who helped organize its women's branch, the Daughters of Freedom (Hijas de la Libertad), she led the Jayuya Uprising on October 30, 1950 — raising the outlawed Puerto Rican flag in the town plaza and proclaiming Puerto Rico a free republic. Nationalists held Jayuya for three days until U.S. military forces bombed and retook the town and captured her. Convicted in connection with the uprising, she was sentenced to life imprisonment plus sixty years and held at the Alderson Federal Prison Camp in West Virginia and later the women's prison at Vega Alta, Puerto Rico, before receiving a full pardon from Governor Roberto Sánchez Vilella in 1967 after about seventeen years.",
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Leading the October 30, 1950 Jayuya Uprising (El Grito de Jayuya) — raising the outlawed Puerto Rican flag and proclaiming the Republic of Puerto Rico; charged with killing a police officer, wounding three others, and burning the town post office',
                'arrest_date' => '1950-11-01',
                'incarceration_date' => '1950-11-01',
                'release_date' => '1967-11-01',
                'convicted' => 'Yes — convicted in connection with the Jayuya Uprising',
                'sentence' => "Life imprisonment plus sixty years; held at the Alderson Federal Prison Camp (West Virginia) and the women's prison at Vega Alta, Puerto Rico, until a full pardon by Governor Roberto Sánchez Vilella in 1967 (about seventeen years served; exact release date not documented)",
            ]);
        });

        $this->info('Added Blanca Canales.');

        return self::SUCCESS;
    }
}
