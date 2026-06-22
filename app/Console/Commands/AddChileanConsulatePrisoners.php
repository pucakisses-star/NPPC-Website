<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds Pablo Marcano García and Nydia Cuevas (Cuevas Rivera), the two Puerto
 * Rican independence activists who seized the Chilean consulate in San Juan on
 * July 3, 1978 and held the honorary consul at gunpoint for some 22 hours —
 * surrendering on July 4 — to demand the release of the imprisoned Puerto Rican
 * Nationalists and the cancellation of the island's Fourth of July celebrations.
 * Both were convicted in federal court (conviction affirmed in United States v.
 * Marcano-García & Cuevas-Rivera, 622 F.2d 12, 1st Cir. 1980). Marcano's
 * 22-year sentence was reduced to seven and he was released in 1985; Cuevas was
 * released in the 1980s.
 *
 * Exact incarceration/release days are not documented, so only the July 4, 1978
 * arrest date is set and the imprisonment span is described in prose.
 * Idempotent — prisoner:add refuses duplicates by name.
 */
final class AddChileanConsulatePrisoners extends Command
{
    protected $signature = 'prisoners:add-chilean-consulate';

    protected $description = 'Add Pablo Marcano García and Nydia Cuevas (1978 Chilean consulate takeover, San Juan)';

    public function handle(): int
    {
        $payloads = [
            [
                'name' => 'Pablo Marcano García',
                'first_name' => 'Pablo',
                'last_name' => 'Marcano García',
                'aka' => 'Pablo Marcano',
                'gender' => 'Male',
                'race' => 'Hispanic',
                'state' => 'Puerto Rico',
                'era' => '1970s',
                'birthdate' => '1952-01-15',
                'ideologies' => ['Puerto Rican independence', 'Anti-imperialism'],
                'in_custody' => false,
                'released' => true,
                'description' => 'Pablo Marcano García (born January 15, 1952 in Gurabo) is a Puerto Rican independence activist and artist. On July 3, 1978 he and Nydia Cuevas seized the Chilean consulate in San Juan and held the honorary consul at gunpoint for some 22 hours — surrendering on July 4 — to demand the release of the imprisoned Puerto Rican Nationalists and the cancellation of the island\'s Fourth of July celebrations. Convicted in federal court and initially facing 22 years, his sentence was reduced to seven; he was imprisoned in New York State and released in 1985 to a hero\'s welcome at the San Juan airport. He became a noted artist, developing his work while behind bars.',
                'cases' => [
                    [
                        'charges' => 'Seized the Chilean consulate in San Juan and held the honorary consul hostage at gunpoint (July 3–4, 1978), to demand freedom for the imprisoned Puerto Rican Nationalists and the cancellation of the island\'s July 4 celebrations.',
                        'convicted' => 'Yes — convicted in the U.S. District Court for Puerto Rico (1978); conviction affirmed on appeal (United States v. Marcano-García & Cuevas-Rivera, 622 F.2d 12, 1st Cir. 1980).',
                        'arrest_date' => '1978-07-04',
                        'sentence' => 'Initially sentenced to 22 years, reduced to seven; held from his 1978 arrest and released in 1985.',
                    ],
                ],
            ],
            [
                'name' => 'Nydia Cuevas',
                'first_name' => 'Nydia',
                'last_name' => 'Cuevas',
                'aka' => 'Nydia Cuevas Rivera',
                'gender' => 'Female',
                'race' => 'Hispanic',
                'state' => 'Puerto Rico',
                'era' => '1970s',
                'ideologies' => ['Puerto Rican independence', 'Anti-imperialism'],
                'in_custody' => false,
                'released' => true,
                'description' => 'Nydia Cuevas (Cuevas Rivera) is a Puerto Rican independence activist who, with Pablo Marcano García, seized the Chilean consulate in San Juan on July 3, 1978 and held the honorary consul at gunpoint for about 22 hours, surrendering on July 4. Their action demanded the release of the imprisoned Puerto Rican Nationalists and the cancellation of the island\'s Fourth of July celebrations. She was convicted in federal court and imprisoned, and was released in the 1980s and welcomed home as a hero.',
                'cases' => [
                    [
                        'charges' => 'Seized the Chilean consulate in San Juan and held the honorary consul hostage at gunpoint (July 3–4, 1978), with Pablo Marcano García, to demand freedom for the imprisoned Puerto Rican Nationalists.',
                        'convicted' => 'Yes — convicted in the U.S. District Court for Puerto Rico (1978); conviction affirmed on appeal (United States v. Marcano-García & Cuevas-Rivera, 622 F.2d 12, 1st Cir. 1980).',
                        'arrest_date' => '1978-07-04',
                        'sentence' => 'Imprisoned for the consulate takeover; released in the 1980s (her individual sentence and release date are not well documented).',
                    ],
                ],
            ],
        ];

        $added = 0;
        $skipped = 0;
        foreach ($payloads as $payload) {
            $name = $payload['name'];
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info("ADD: {$name}");
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("\nDone. added={$added} skipped={$skipped}");

        return self::SUCCESS;
    }
}
