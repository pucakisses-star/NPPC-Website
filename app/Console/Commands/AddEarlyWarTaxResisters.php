<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds four early imprisoned war tax resisters and repairs two existing
 * records, completing the war-tax-resistance lineage alongside the records
 * already in the database (Juanita and Wallace Nelson, Karl Meyer, Randy
 * Kehler and Betsy Corner, Ammon Hennacy).
 *
 * Added: Henry David Thoreau (1846 Concord poll-tax night that produced
 * "Civil Disobedience"), Zerah C. Whipple (1874 Connecticut military
 * commutation tax), James Katsuki Otsuka (1949 Indiana, 90 days), and
 * Eroseanna "Sis" Robinson (1960 Chicago, force-fed on hunger strike).
 *
 * Repaired: Maurice McCrackin (case had no dates — his six-month 1958-59
 * term is well documented) and Ernest Bromley (case wrongly said
 * "Desertion"; his actual jailing was the 1942 North Carolina refusal of
 * the federal auto "victory tax" stamp, often counted as the first modern
 * American war-tax-resistance imprisonment).
 *
 * Bronson Alcott and Charles Lane (Concord poll-tax arrests, 1843) were
 * considered and skipped: both were released within hours when neighbors
 * paid, so they don't meet the actually-imprisoned bar.
 *
 * Dates use honest precision; unknown ends are left blank so durations read
 * unknown. Idempotent: a first+last-name guard skips anyone already present.
 */
final class AddEarlyWarTaxResisters extends Command
{
    protected $signature = 'prisoners:add-early-war-tax-resisters';

    protected $description = 'Add four early imprisoned war tax resisters; repair the Bromley and McCrackin records';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'Henry David Thoreau',
                    'first_name' => 'Henry',
                    'last_name' => 'Thoreau',
                    'description' => 'Henry David Thoreau, the Concord, Massachusetts writer and naturalist, spent the night of July 23-24, 1846 in the Concord jail for refusing to pay six years of poll taxes in protest of the Mexican-American War and slavery. Released the next morning after a relative paid the tax over his objection, he turned the experience into the 1849 essay "Civil Disobedience" ("Resistance to Civil Government") — the founding text of principled law-breaking that later shaped Tolstoy, Gandhi, and Martin Luther King Jr., and made his single night in jail the most consequential imprisonment in American protest history.',
                    'state' => 'Massachusetts',
                    'gender' => 'Male',
                    'ideologies' => ['Abolitionism', 'Anti-war', 'War tax resistance'],
                    'era' => '1800s',
                    'cases' => [[
                        'charges' => 'Jailed for refusing to pay the Massachusetts poll tax in protest of the Mexican-American War and slavery.',
                        'convicted' => 'Held overnight; released when the tax was paid by a relative over his objection',
                        'sentence' => 'One night in the Concord jail, July 23-24, 1846.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1846, 7, 23], 'release_date' => [1846, 7, 24]],
            ],
            [
                'payload' => [
                    'name' => 'Zerah C. Whipple',
                    'first_name' => 'Zerah',
                    'last_name' => 'Whipple',
                    'description' => "Zerah C. Whipple, a young Connecticut Quaker and treasurer of the Connecticut Peace Society, was jailed in 1874 for refusing to pay the state's military commutation tax — the fee levied on men who declined militia service. Refusing on principle either to serve or to buy his way out of serving, he was held in jail until a stranger, saying he could not bear to see a man imprisoned for conscience, paid the tax and freed him. His case became a touchstone in the nineteenth-century peace movement's campaign against militia taxes.",
                    'state' => 'Connecticut',
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism', 'Quakerism', 'War tax resistance'],
                    'affiliation' => ['Connecticut Peace Society'],
                    'era' => '1800s',
                    'cases' => [[
                        'charges' => "Jailed in 1874 for refusing to pay Connecticut's military commutation tax on grounds of conscience.",
                        'convicted' => 'Held for nonpayment; released when a stranger paid the tax',
                        'sentence' => 'Jailed until an anonymous stranger paid the commutation tax on his behalf (exact dates not documented).',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1874, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'James Katsuki Otsuka',
                    'first_name' => 'James',
                    'last_name' => 'Otsuka',
                    'description' => 'James Katsuki Otsuka, a Japanese American pacifist and wartime conscientious objector, became one of the first Americans jailed for modern income-tax war resistance. In December 1949 a federal judge in Indianapolis sentenced him to 90 days in jail and a $100 fine for refusing to pay the small share of his income tax he calculated would go to war — and for refusing to tell the court where his money was. Ammon Hennacy picketed and fasted outside the federal building in his support, and the case became an early landmark of the postwar Peacemakers tax-refusal movement.',
                    'state' => 'Indiana',
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism', 'War tax resistance'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Sentenced in December 1949 by a federal judge in Indianapolis for refusing to pay the war portion of his income tax and refusing to disclose his assets.',
                        'convicted' => 'Convicted, December 1949',
                        'sentence' => '90 days in jail and a $100 fine; served the term into early 1950.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1949, 12, null], 'release_date' => [1950, 3, null]],
            ],
            [
                'payload' => [
                    'name' => 'Eroseanna Robinson',
                    'first_name' => 'Eroseanna',
                    'last_name' => 'Robinson',
                    'description' => 'Eroseanna "Sis" Robinson, a Black track-and-field athlete and Peacemakers activist from Chicago, refused for years to file or pay federal income taxes because, she said, the money would go "for war purposes, guns, and bombs." (In 1958 she had also refused to represent the United States at a state-sponsored track meet, calling herself unwilling to serve as a propaganda tool.) Jailed for contempt in January 1960 and sentenced to a year and a day, she refused to cooperate in any way — she had to be carried into court — and went on hunger strike at the Alderson federal prison, enduring weeks of force-feeding, until authorities gave up and released her in May 1960 after more than three months.',
                    'state' => 'Illinois',
                    'gender' => 'Female',
                    'ideologies' => ['Pacifism', 'War tax resistance', 'Civil rights'],
                    'affiliation' => ['Peacemakers'],
                    'era' => '1960s',
                    'cases' => [[
                        'charges' => 'Jailed for contempt in January 1960 for refusing to file returns, pay federal income tax, or cooperate with the court, in protest of military spending.',
                        'convicted' => 'Held in contempt; sentenced to a year and a day, January 1960',
                        'sentence' => 'A year and a day; went on hunger strike at Alderson and was force-fed for weeks; released in May 1960 after more than three months when authorities relented.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1960, 1, null], 'release_date' => [1960, 5, null]],
            ],
        ];

        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $payload['in_custody'] = false;
            $payload['released'] = true;

            // Skip variant-name duplicates (first AND last name both present).
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
            $prisoner->released = true;
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

        // ---- Repair: Maurice McCrackin — case had no dates ----
        $mccrackin = Prisoner::withoutGlobalScopes()->where('slug', 'maurice-mccrackin')->first();
        if ($mccrackin && ($case = $mccrackin->cases()->first())) {
            $case->setPartialDate('incarceration_date', 1958, 12);
            $case->setPartialDate('release_date', 1959, 6);
            $case->sentence = 'Six months in federal prison (served at Allenwood) for refusing to cooperate with an IRS summons over his war-tax refusal; sentenced December 1958, released mid-1959.';
            $case->save();
            $this->info('Repaired Maurice McCrackin case dates (Dec 1958 – Jun 1959).');
        }

        // ---- Repair: Ernest Bromley — case wrongly said "Desertion" ----
        $bromley = Prisoner::withoutGlobalScopes()->where('slug', 'ernest-bromley')->first();
        if ($bromley && ($case = $bromley->cases()->first())) {
            $case->charges = 'Jailed in North Carolina in 1942 for refusing to buy the federal automobile use ("victory tax") stamp because the revenue funded the war — often counted as the first modern American imprisonment for war tax resistance.';
            $case->convicted = 'Convicted, 1942';
            $case->sentence = 'Served a short jail term in 1942 (roughly two months; exact dates not documented).';
            $case->setPartialDate('incarceration_date', 1942);
            $case->release_date = null;
            $case->save();

            if (! str_contains(strtolower($bromley->description ?? ''), 'tax')) {
                $bromley->description = trim(($bromley->description ?? '')).' In 1942, while a Methodist pastor in North Carolina, he was jailed for refusing to buy the federal automobile "victory tax" stamp — often counted as the first modern American imprisonment for war tax resistance — and he went on to co-found and lead Peacemakers, the center of the postwar tax-refusal movement, with his wife and fellow resister Marion Bromley.';
                $bromley->save();
            }
            $this->info('Repaired Ernest Bromley case (bogus "Desertion" charge → 1942 war-tax-stamp jailing).');
        }

        $this->info("\nDone. Added {$added} early war tax resister(s); repaired McCrackin and Bromley.");

        return self::SUCCESS;
    }
}
