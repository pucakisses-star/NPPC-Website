<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 8 from the ILD's Labor Defender (1929): the two landmark cases of the
 * year — the Gastonia textile-strike murder trial and the Yucaipa red-flag
 * case that became Stromberg v. California.
 *
 * GASTONIA: On 7 June 1929 police attacked the National Textile Workers Union
 * tent colony at the Loray Mill in Gastonia, N.C., and in the exchange of fire
 * Police Chief O. F. Aderholt was killed. The state charged sixteen strikers
 * and organizers with his murder. After a mistrial, seven were retried in
 * October 1929 and convicted of second-degree murder with sentences totalling
 * some 117 years; released on ILD bail pending appeal, all seven fled to the
 * Soviet Union in 1930. Fred Beal, the lead defendant, is already recorded;
 * this adds his six convicted co-defendants and the other strikers held with
 * them in the Gaston County jail on the capital charge.
 *
 * YUCAIPA / STROMBERG: In 1929 California prosecuted the young staff of a
 * communist children's summer camp at Yucaipa for a daily red-flag-raising
 * ceremony under the state's red-flag law. Yetta Stromberg's appeal produced
 * Stromberg v. California (1931), in which the Supreme Court struck down the
 * ban on displaying a flag as "opposition to organized government" — a
 * foundational free-speech decision. One co-defendant, Isadore Berkowitz, died
 * in custody.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddGastoniaAndStromberg extends Command
{
    protected $signature = 'prisoners:add-gastonia-stromberg';

    protected $description = 'Add the Gastonia 1929 murder defendants and the Yucaipa/Stromberg v. California red-flag defendants';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        $gastoniaBase = "On 7 June 1929, police attacked the National Textile Workers Union tent colony at the Loray Mill in Gastonia, North Carolina, and Police Chief O. F. Aderholt was killed in the exchange of fire. Sixteen strikers and organizers were charged with his murder — a frame-up, the defense held, aimed at breaking the communist-led Southern textile drive. The International Labor Defense mounted a national campaign for the Gastonia defendants.";

        // The six convicted co-defendants of Fred Beal.
        $convicted = [
            ['Clarence Miller', 'Clarence', 'Miller', '17 to 20 years', "a Young Communist and union organizer"],
            ['Joseph Harrison', 'Joseph', 'Harrison', '17 to 20 years', "a striker who had himself been wounded by the police on 7 June"],
            ['George Carter', 'George', 'Carter', '17 to 20 years', "a union organizer"],
            ['W. M. McGinnis', 'W. M.', 'McGinnis', '12 to 15 years', "a Loray mill striker"],
            ['Louis McLaughlin', 'Louis', 'McLaughlin', '12 to 15 years', "a Loray mill striker"],
            ['K. Y. Hendricks', 'K. Y.', 'Hendricks', '5 to 7 years', "a National Textile Workers Union member"],
        ];
        foreach ($convicted as [$name, $first, $last, $term, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, {$who}, was one of the seven Gastonia defendants convicted of second-degree murder in the retrial of October 1929 for the death of Police Chief Aderholt, and was sentenced to {$term}. Released on ILD bail pending appeal, he was among the seven — with lead defendant Fred Beal — who fled to the Soviet Union in 1930 rather than serve the sentences. ".$gastoniaBase,
                'state' => 'North Carolina', 'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['National Textile Workers Union'],
                'era' => '1920s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with the first-degree murder of Police Chief O. F. Aderholt in the 7 June 1929 attack on the Gastonia NTWU tent colony.',
                    'convicted' => 'Convicted of second-degree murder, October 1929; jumped bail to the USSR, 1930',
                    'sentence' => "{$term}; released on ILD bail pending appeal, never served.",
                    'institution_name' => 'Gaston County Jail',
                    'institution_city' => 'Gastonia', 'institution_state' => 'North Carolina',
                ]],
            ], ['arrest_date' => [1929, 6, 7]]);
        }

        // Other defendants held on the capital charge (charges later dropped).
        $held = [
            ['Vera Buch', 'Vera', 'Buch', 'Female', "a National Textile Workers Union organizer and one of the three women charged with murder"],
            ['Amy Schechter', 'Amy', 'Schechter', 'Female', "a Workers International Relief organizer and one of the three women charged with murder"],
            ['Sophie Melvin', 'Sophie', 'Melvin', 'Female', "a nineteen-year-old youth organizer and one of the three women charged with murder"],
            ['K. O. Byers', 'K. O.', 'Byers', 'Male', "a Loray mill striker arrested with Fred Beal at Spartanburg"],
            ['Robert Allen', 'Robert', 'Allen', 'Male', "a National Textile Workers Union striker"],
            ['J. C. Heffner', 'J. C.', 'Heffner', 'Male', "a Loray mill striker"],
            ['N. F. Gibson', 'N. F.', 'Gibson', 'Male', "a National Textile Workers Union striker"],
            ['Russell Knight', 'Russell', 'Knight', 'Male', "a striker held in an isolation cell after contracting smallpox in jail"],
            ['Delmar Hampton', 'Delmar', 'Hampton', 'Male', "a Loray mill striker"],
        ];
        foreach ($held as [$name, $first, $last, $gender, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, {$who}, was one of the sixteen Gastonia defendants held in the Gaston County jail on the first-degree murder charge — facing the electric chair — after the 7 June 1929 killing of Police Chief Aderholt. The murder charge against the defendants outside the convicted seven was dropped before the October retrial. ".$gastoniaBase,
                'state' => 'North Carolina', 'gender' => $gender,
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['National Textile Workers Union'],
                'era' => '1920s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with the first-degree murder of Police Chief O. F. Aderholt after the 7 June 1929 attack on the Gastonia NTWU tent colony.',
                    'convicted' => 'Held for months on the capital charge; charges dropped before the October 1929 retrial',
                    'sentence' => 'Held in the Gaston County jail facing the electric chair; not among the seven ultimately retried.',
                    'institution_name' => 'Gaston County Jail',
                    'institution_city' => 'Gastonia', 'institution_state' => 'North Carolina',
                ]],
            ], ['arrest_date' => [1929, 6, 7]]);
        }

        // ---- Yucaipa / Stromberg v. California ----
        $strombergBase = "In 1929 California prosecuted the young staff of a communist children's summer camp at Yucaipa under the state's red-flag law for a daily ceremony raising a red flag; the raid also turned up radical literature. The defendants were sentenced in October 1929.";
        $mk([
            'name' => 'Yetta Stromberg', 'first_name' => 'Yetta', 'last_name' => 'Stromberg',
            'description' => "Yetta Stromberg, a nineteen-year-old member of the Young Communist League, was a supervisor at a communist children's summer camp at Yucaipa, California, where she led a daily ceremony raising a red flag. Convicted in 1929 under California's red-flag law and sentenced to one to ten years at San Quentin, she appealed, and in Stromberg v. California (1931) the U.S. Supreme Court struck down the law's ban on displaying a flag as a symbol of \"opposition to organized government\" — a landmark First Amendment decision and one of the first to apply free-speech protection against the states.",
            'state' => 'California', 'gender' => 'Female',
            'ideologies' => ['Communism'],
            'affiliation' => ['Young Communist League'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Convicted under California's red-flag law for leading a daily red-flag ceremony at a communist children's camp in Yucaipa.",
                'convicted' => 'Convicted, 1929; conviction reversed in Stromberg v. California (1931)',
                'sentence' => 'One to ten years at San Quentin; conviction struck down by the Supreme Court.',
                'institution_name' => 'San Quentin State Prison',
                'institution_city' => 'San Quentin', 'institution_state' => 'California',
            ]],
        ], ['incarceration_date' => [1929, null, null]]);
        $mk([
            'name' => 'Isadore Berkowitz', 'first_name' => 'Isadore', 'last_name' => 'Berkowitz',
            'description' => "Isadore Berkowitz was one of the staff of the communist children's summer camp at Yucaipa, California prosecuted in 1929 under the state's red-flag law alongside Yetta Stromberg. He died in custody during the case. ".$strombergBase,
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1920s', 'in_custody' => false, 'released' => false,
            'cases' => [[
                'charges' => "Prosecuted under California's red-flag law over the communist children's camp at Yucaipa.",
                'convicted' => 'Died in custody during the case, 1929',
                'sentence' => 'Died in custody before the case was resolved.',
            ]],
        ], []);
        foreach ([
            ['Bella Mintz', 'Bella', 'Mintz'],
            ['Esther Karpiloff', 'Esther', 'Karpiloff'],
            ['Emma Schneiderman', 'Emma', 'Schneiderman'],
            ['Jennie Wolfson', 'Jennie', 'Wolfson'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of the staff of the communist children's summer camp at Yucaipa, California convicted in 1929 under the state's red-flag law alongside Yetta Stromberg — whose appeal became the landmark Stromberg v. California — and sentenced to a term of six months to five years. ".$strombergBase,
                'state' => 'California', 'gender' => 'Female',
                'ideologies' => ['Communism'],
                'era' => '1920s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Convicted under California's red-flag law over the communist children's camp at Yucaipa (co-defendant of Yetta Stromberg).",
                    'convicted' => 'Convicted, 1929',
                    'sentence' => 'Six months to five years.',
                ]],
            ], ['incarceration_date' => [1929, null, null]]);
        }

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

        $this->info("\nDone. Processed {$added} Gastonia/Stromberg prisoner(s).");

        return self::SUCCESS;
    }
}
