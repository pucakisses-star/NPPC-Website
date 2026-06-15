<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds the two verifiable members of "the Trenton 7" — Jim Hart and Al Larcinese
 * — UAW Local 372 auto workers sentenced (Dec 12, 1977, by Judge John Feikens) to
 * a week in federal prison for criminal contempt over a 1977 "heat walkout"
 * wildcat at Chrysler's Trenton, MI engine plant. Seven were convicted, but only
 * these two are actually named as defendants across the Workers Vanguard / Militant
 * coverage; the other five names in circulation could not be verified, so they are
 * not invented here. Idempotent.
 */
class AddTrentonSeven extends Command {
    protected $signature = 'prisoners:add-trenton-seven';
    protected $description = 'Add the named Trenton 7 defendants (Hart, Larcinese) — 1977 Chrysler heat-walkout contempt';

    private const CONTEXT = <<<'TXT'
On December 12, 1977, U.S. District Judge John Feikens sentenced seven auto workers — "the Trenton 7" — to a week in federal prison for criminal contempt of a restraining order barring picketing at Chrysler's Trenton Engine Plant (UAW Local 372) in Trenton, Michigan. During the brutal heat wave of the summer of 1977, thousands of Detroit-area auto workers had walked out of plants where temperatures reached 120–130°F; at the Trenton plant, the firing of several union stewards over one such heat walkout triggered a week-long wildcat strike. Of the hundreds of workers who picketed, seven were singled out for prosecution. Although nearly 60 workers fired for heat walkouts at other Detroit-area plants were rehired in a Chrysler–UAW deal, the Trenton 7 were not reinstated and were instead tried without a jury and convicted. Supporters denounced the case as a frame-up and an assault on the right to strike.
TXT;

    public function handle(): int {
        DB::transaction(function () {
            $defendants = [
                ['name' => 'Jim Hart', 'first' => 'Jim', 'last' => 'Hart',
                 'tail' => 'Jim Hart was one of the seven defendants. "Between it all," he said, "management and the union are sleeping together."'],
                ['name' => 'Al Larcinese', 'first' => 'Al', 'last' => 'Larcinese',
                 'tail' => 'Al Larcinese was one of the seven defendants, who likened the prosecution to the labor repression of the 1930s.'],
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
                    'description'    => self::CONTEXT."\n\n".$d['tail'],
                    'gender'         => 'Male',
                    'state'          => 'Michigan',
                    'era'            => '1970s',
                    'ideologies'     => ['Labor', 'Trade unionism'],
                    'affiliation'    => ['United Auto Workers (UAW) Local 372'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'charges'     => 'Criminal contempt of a federal restraining order barring picketing during a week-long wildcat strike (the summer 1977 "heat walkout") at Chrysler\'s Trenton Engine Plant, as a member of UAW Local 372 — one of seven workers ("the Trenton 7") singled out from hundreds of pickets.',
                    'convicted'   => 'Convicted of criminal contempt without a jury and sentenced on December 12, 1977 to one week in federal prison.',
                    'sentenced_date' => '1977-12-12',
                    'sentence'    => 'One week in federal prison (December 1977).',
                    'judge'       => 'John Feikens',
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
