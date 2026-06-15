<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Lemuel Meadows and Mahan Vanover, United Mine Workers (UMWA) members jailed
 * for six months for picket-limit contempt during the 1976–79 recognition strike
 * against the Blue Diamond Coal Company's Justus mine in Stearns, Kentucky.
 * Surfaced from The Militant / Workers Vanguard strike coverage; sourced to those
 * papers and labor histories of the Stearns/Blue Diamond strike. Idempotent.
 */
class AddStearnsMiners extends Command {
    protected $signature = 'prisoners:add-stearns-miners';
    protected $description = 'Add Lemuel Meadows and Mahan Vanover (Stearns, KY / Blue Diamond Coal strike)';

    private const CONTEXT = <<<'TXT'
was a striking coal miner and member of the United Mine Workers of America (UMWA) jailed during the bitter 1976–79 strike at the Blue Diamond Coal Company's Justus mine in Stearns, Kentucky. After the miners voted 126–57 to be represented by the UMWA, Blue Diamond refused to sign a standard union contract, and the miners walked out in a struggle for union recognition that drew comparisons to Harlan County. State troopers in riot gear escorted strikebreakers through the picket lines, gunfire broke out, and roughly 110 miners and family members were arrested over the course of the strike.
TXT;

    public function handle(): int {
        DB::transaction(function () {
            $defendants = [
                [
                    'name' => 'Lemuel Meadows', 'first' => 'Lemuel', 'last' => 'Meadows',
                    'tail' => 'Lemuel Meadows, one of the striking miners, was convicted of violating a court order limiting the number of pickets and sentenced to six months in jail.',
                ],
                [
                    'name' => 'Mahan Vanover', 'first' => 'Mahan', 'last' => 'Vanover',
                    'tail' => 'Mahan Vanover, a picket captain in the strike, was likewise convicted of contempt of the picket-limit order and sentenced to six months in jail.',
                ],
            ];

            foreach ($defendants as $d) {
                if (Prisoner::where('name', $d['name'])->exists()) {
                    $this->warn("Skipped (already exists): {$d['name']}");
                    continue;
                }

                $prisoner = Prisoner::create([
                    'name'           => $d['name'],
                    'first_name'     => $d['first'],
                    'last_name'      => $d['last'],
                    'description'    => "{$d['name']} {$this->contextSentence()}\n\n{$d['tail']}",
                    'gender'         => 'Male',
                    'state'          => 'Kentucky',
                    'era'            => '1970s',
                    'ideologies'     => ['Labor', 'Trade unionism'],
                    'affiliation'    => ['United Mine Workers of America (UMWA)'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'charges'     => 'Criminal contempt for violating a court order limiting pickets during the United Mine Workers (UMWA) recognition strike against the Blue Diamond Coal Company\'s Justus mine in Stearns, Kentucky (1976–79).',
                    'convicted'   => 'Convicted of contempt during the Stearns, Kentucky coal strike and sentenced to six months.',
                    'sentence'    => 'Six months in jail (1977) for contempt of a picket-limit order.',
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }

    private function contextSentence(): string {
        return trim(self::CONTEXT);
    }
}
