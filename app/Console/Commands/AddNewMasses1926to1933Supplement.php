<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * New Masses 1926–1933 — supplement to batch 1.
 *
 * A completeness audit of the mined 1926–1933 notes (re-reading every named
 * US prisoner flagged, including the borderline ones) surfaced six genuine,
 * documented US class-war prisoners who were actually detained but who had no
 * record in the database and were not in the first New Masses batch:
 *
 *  - James B. McNamara — the LA Times bombing case (batch 1 added his brother
 *    John J. but not James B. himself, who planted the dynamite and served
 *    life);
 *  - Agnes Smedley — jailed under the Espionage Act for Indian-independence work;
 *  - Roberto Elia — the Palmer-Raids deportation co-detainee of Andrea Salsedo
 *    (Salsedo is already in the database);
 *  - Philip Masonovich — the convicted defendant of the 1916 Mesaba Range case
 *    (Tresca and Scarlett from the same case are already in the database);
 *  - William Bross Lloyd — the "millionaire Communist," convicted under the
 *    Illinois sedition law in the 1920 Red Scare;
 *  - Morris Pass — a WWI anti-war cartoonist imprisoned at McNeil's Island.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddNewMasses1926to1933Supplement extends Command
{
    protected $signature = 'prisoners:add-new-masses-1926-1933-supplement';

    protected $description = 'Add six genuine US class-war prisoners missed by the first New Masses 1926-1933 batch (James B. McNamara, Agnes Smedley, Roberto Elia, Philip Masonovich, William Bross Lloyd, Morris Pass)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── LOS ANGELES TIMES BOMBING (the brother who set the charge) ───
        $mk([
            'name' => 'James B. McNamara', 'first_name' => 'James', 'last_name' => 'McNamara',
            'description' => "James B. McNamara (1882–1941) was an Iron Workers' union militant and the brother of John J. McNamara. He placed the dynamite that destroyed the Los Angeles Times building on October 1, 1910, an explosion that killed some twenty workers in the bitter open-shop war against organized labor. Defended by Clarence Darrow, the brothers pleaded guilty in 1911; James was sentenced to life imprisonment and died at San Quentin in 1941, having become a symbol of the era's class-war labor conflict.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['International Association of Bridge and Structural Iron Workers'],
            'era' => '1910s', 'in_custody' => false, 'released' => false,
            'cases' => [[
                'charges' => 'Placed the dynamite that destroyed the Los Angeles Times building (1910).',
                'convicted' => 'Pleaded guilty, 1911',
                'sentence' => 'Life imprisonment; died at San Quentin in 1941.',
                'institution_name' => 'San Quentin State Prison',
                'institution_city' => 'San Quentin', 'institution_state' => 'California',
            ]],
        ], ['incarceration_date' => [1911, null, null]]);

        // ── ESPIONAGE ACT / INDIAN INDEPENDENCE ─────────────────────────
        $mk([
            'name' => 'Agnes Smedley', 'first_name' => 'Agnes', 'last_name' => 'Smedley',
            'description' => "Agnes Smedley (1892–1950) was an American radical journalist and writer who was arrested in New York in March 1918 under the Espionage Act for her work in the Indian independence movement (the Ghadar/\"Freedom for India\" cause) and held in the Tombs for about six months. She went on to become a noted correspondent of the Chinese revolution and a contributor to New Masses.",
            'state' => 'New York', 'gender' => 'Female',
            'ideologies' => ['Anti-imperialism', 'Communism'],
            'era' => '1910s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested under the Espionage Act for Indian-independence (Ghadar) activity.',
                'convicted' => 'Held, 1918',
                'sentence' => 'Held about six months in the Tombs; released.',
                'institution_name' => 'The Tombs',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1918, 3, null]]);

        // ── PALMER RAIDS DEPORTATION (Salsedo's co-detainee) ────────────
        $mk([
            'name' => 'Roberto Elia', 'first_name' => 'Roberto', 'last_name' => 'Elia',
            'description' => "Roberto Elia was an Italian anarchist printer arrested in New York in early 1920 during the Palmer Raids and held incommunicado by the Department of Justice for deportation, together with Andrea Salsedo. Salsedo fell to his death from the fourteenth-floor DOJ offices in May 1920 — an event that helped spur Sacco and Vanzetti's fatal trip to New York — after which Elia was deported to Italy.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Anarchism'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Held for deportation by the Department of Justice in the Palmer Raids.',
                'convicted' => 'Held, 1920',
                'sentence' => 'Held incommunicado for deportation; deported to Italy.',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1920, null, null]]);

        // ── MESABA RANGE 1916 (the convicted defendant) ─────────────────
        $mk([
            'name' => 'Philip Masonovich', 'first_name' => 'Philip', 'last_name' => 'Masonovich',
            'description' => "Philip Masonovich was a Montenegrin immigrant iron miner caught up in the 1916 Mesaba Range strike in Minnesota. When a deputy sheriff was killed during a clash at his Biwabik home, he, his wife, and their boarders were arrested and charged with first-degree murder. Masonovich pleaded guilty to manslaughter and served a prison term — the plea bargain that secured the release of the IWW strike organizers Carlo Tresca, Sam Scarlett and Matthew Schmidt, who are already recorded in the database.",
            'state' => 'Minnesota', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Industrial Workers of the World'],
            'era' => '1910s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with first-degree murder after a deputy was killed at his home in the Mesaba Range strike.',
                'convicted' => 'Pleaded guilty to manslaughter, 1916',
                'sentence' => 'Served a prison term.',
                'institution_state' => 'Minnesota',
            ]],
        ], ['arrest_date' => [1916, null, null]]);

        // ── 1920 RED SCARE (Illinois sedition) ──────────────────────────
        $mk([
            'name' => 'William Bross Lloyd', 'first_name' => 'William', 'last_name' => 'Bross Lloyd',
            'description' => "William Bross Lloyd (1875–1946), known as the \"millionaire Communist,\" was a founder and leader of the Communist Labor Party. Arrested in the 1920 Palmer-era raids in Chicago, he was convicted with a group of co-defendants under Illinois' sedition/espionage law and sentenced to one to five years at Joliet; the defense was led by Clarence Darrow. The convictions were later commuted.",
            'state' => 'Illinois', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['Communist Labor Party'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Convicted under Illinois' sedition law as a Communist Labor Party leader.",
                'convicted' => 'Convicted, 1920',
                'sentence' => 'One to five years at Joliet; later commuted.',
                'institution_name' => 'Joliet Penitentiary',
                'institution_city' => 'Joliet', 'institution_state' => 'Illinois',
            ]],
        ], ['arrest_date' => [1920, null, null]]);

        // ── WWI ANTI-WAR (McNeil's Island) ──────────────────────────────
        $mk([
            'name' => 'Morris Pass', 'first_name' => 'Morris', 'last_name' => 'Pass',
            'description' => "Morris Pass was a radical cartoonist and anti-war activist who, with his brother Joseph Pass, was prosecuted for opposing World War I and imprisoned at the federal penitentiary on McNeil's Island, Washington. Associated with the Pacific Northwest labor movement and the Seattle general strike, he later drew for New Masses.",
            'state' => 'Washington', 'gender' => 'Male',
            'ideologies' => ['Anti-war', 'Labor organizing'],
            'era' => '1910s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Prosecuted for anti-war activity during World War I.',
                'convicted' => 'Convicted, WWI era',
                'sentence' => "Imprisoned at the federal penitentiary on McNeil's Island.",
                'institution_name' => "McNeil Island Penitentiary",
                'institution_state' => 'Washington',
            ]],
        ], []);

        // ── INSERT ───────────────────────────────────────────────────────
        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $payload['in_custody'] = false;
            if (! array_key_exists('released', $payload)) {
                $payload['released'] = true;
            }

            $existing = Prisoner::withoutGlobalScopes()
                ->where('name', 'like', '%'.$payload['first_name'].'%')
                ->where('name', 'like', '%'.$payload['last_name'].'%')
                ->first();
            if ($existing) {
                $this->line("  already in database as \"{$existing->name}\" — skipping {$payload['name']}.");

                continue;
            }

            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
            if (! $prisoner) {
                continue;
            }
            $prisoner->in_custody = false;
            $prisoner->released = $payload['released'];
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case && ! empty($person['dates'])) {
                foreach ($person['dates'] as $field => [$y, $m, $d]) {
                    $case->setPartialDate($field, $y, $m, $d);
                }
                $case->save();
            }
            $added++;
        }

        $this->info("\nDone. Processed {$added} of ".count($people)." New Masses 1926-1933 supplement prisoner(s).");

        return self::SUCCESS;
    }
}
