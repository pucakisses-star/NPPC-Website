<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds the Washington Post pressmen who were incarcerated over the violent start
 * of the 1975–76 pressmen's strike. A federal grand jury indicted 15 pressmen for
 * the Oct. 1, 1975 pressroom sabotage (while none of the line-crossers who clashed
 * with strikers were charged — a selectivity the union denounced as a union-busting
 * frame-up). Fourteen pleaded guilty to misdemeanors in April 1977 and were
 * sentenced May 19, 1977 by D.C. Superior Court Judge Sylvia Bacon.
 *
 * This command creates records for the SIX who received custodial terms. The other
 * eight received fines only (no incarceration) and are therefore documented here
 * but not added as prisoner records: Lawrence H. Boyd, Dennis Hughes, Joseph E.
 * Mozingo, John B. Zarbough, Joseph J. Schumacher, Lucius J. Smith ($250 each),
 * Walter J. Stahli ($500), Fred F. Tweedlie ($750); a 15th pressman was fined $250
 * with a suspended term the prior month.
 *
 * Sourced to The Washington Post, "Post Pressmen Penalized for Strike Events"
 * (May 20, 1977). Idempotent: skips a prisoner who already exists by name.
 */
class AddWaPoPressmenStrikePrisoners extends Command {
    protected $signature = 'prisoners:add-wapo-pressmen';
    protected $description = 'Add the incarcerated Washington Post pressmen from the 1975–76 strike prosecutions';

    private const CONTEXT = <<<'TXT'
The pressmen's strike against The Washington Post began before dawn on October 1, 1975, when members of Local 6 of the International Printing and Graphic Communications Union disabled all 72 of the paper's press units and beat the night foreman, James Hover. The Post hired permanent replacements, broke the union, and the District's pressmen voted to decertify Local 6 in March 1977 — a landmark defeat for organized labor. A federal grand jury indicted fifteen pressmen over the pressroom violence, while none of the workers who crossed the picket line and clashed with strikers were charged; supporters denounced the prosecutions as a selective, union-busting "frame-up" that drained the local's resources through legal-defense costs. Fourteen pressmen pleaded guilty in April 1977 to misdemeanors — ranging from disorderly conduct to simple assault — after prosecutors dropped the felony charges, and were sentenced on May 19, 1977 by D.C. Superior Court Judge Sylvia Bacon, who declared that "the existence of a labor dispute or a strike does not justify violence." Their sentences were met with protest rallies outside the courthouse.
TXT;

    public function handle(): int {
        DB::transaction(function () {
            $doc = Institution::firstOrCreate(
                ['name' => 'District of Columbia Department of Corrections'],
                ['city' => 'Washington', 'state' => 'District of Columbia']
            );

            $defendants = [
                [
                    'name' => 'Jack D. McIntosh', 'first' => 'Jack', 'last' => 'McIntosh',
                    'fragment' => 'Jack D. McIntosh received the stiffest sentence of the group — two concurrent one-year jail terms. He had pleaded guilty to destruction of property in the pressroom and was separately convicted by a jury of assaulting former Washington Post reporter Jules Witcover (later a syndicated columnist). Prosecutors said McIntosh played a significant role in disabling the presses, and he was required to serve at least four months before becoming eligible for parole.',
                    'charges' => 'Destruction of property in the Washington Post pressroom (guilty plea) and assault on former Post reporter Jules Witcover (jury conviction), arising from the violent start of the pressmen\'s strike on October 1, 1975.',
                    'convicted' => 'Yes — guilty plea (destruction of property) plus a jury conviction (assault on reporter Jules Witcover); sentenced May 19, 1977 in D.C. Superior Court by Judge Sylvia Bacon.',
                    'sentence' => 'Two concurrent one-year jail terms; required to serve at least four months before parole eligibility.',
                ],
                [
                    'name' => 'Eugene E. O\'Sullivan', 'first' => 'Eugene', 'last' => 'O\'Sullivan',
                    'fragment' => 'Eugene E. O\'Sullivan was sentenced to 120 days in the District\'s work-release program, plus a suspended jail term and one year\'s probation. In a sentencing memorandum, prosecutors described O\'Sullivan and Cecil Rust as "the leaders and catalysts for the violence, damage and destruction."',
                    'charges' => 'Misdemeanor arising from the October 1, 1975 pressroom violence (pleaded guilty in April 1977 after felony charges were dropped in a plea agreement).',
                    'convicted' => 'Yes — guilty plea (April 1977); sentenced May 19, 1977 in D.C. Superior Court by Judge Sylvia Bacon.',
                    'sentence' => '120 days in the D.C. work-release program (halfway house), plus a suspended jail term and one year\'s probation.',
                ],
                [
                    'name' => 'Cecil E. Rust', 'first' => 'Cecil', 'last' => 'Rust',
                    'fragment' => 'Cecil E. Rust was sentenced to 120 days in the District\'s work-release program, plus a suspended jail term and one year\'s probation. In a sentencing memorandum, prosecutors described Rust and Eugene O\'Sullivan as "the leaders and catalysts for the violence, damage and destruction."',
                    'charges' => 'Misdemeanor arising from the October 1, 1975 pressroom violence (pleaded guilty in April 1977 after felony charges were dropped in a plea agreement).',
                    'convicted' => 'Yes — guilty plea (April 1977); sentenced May 19, 1977 in D.C. Superior Court by Judge Sylvia Bacon.',
                    'sentence' => '120 days in the D.C. work-release program (halfway house), plus a suspended jail term and one year\'s probation.',
                ],
                [
                    'name' => 'John H. Raffo', 'first' => 'John', 'last' => 'Raffo',
                    'fragment' => 'John H. Raffo was sentenced to 120 days in the District\'s work-release program, plus a suspended jail term and one year\'s probation.',
                    'charges' => 'Misdemeanor arising from the October 1, 1975 pressroom violence (pleaded guilty in April 1977 after felony charges were dropped in a plea agreement).',
                    'convicted' => 'Yes — guilty plea (April 1977); sentenced May 19, 1977 in D.C. Superior Court by Judge Sylvia Bacon.',
                    'sentence' => '120 days in the D.C. work-release program (halfway house), plus a suspended jail term and one year\'s probation.',
                ],
                [
                    'name' => 'Gil W. Fowler', 'first' => 'Gil', 'last' => 'Fowler',
                    'fragment' => 'Gil W. Fowler was sentenced to 60 days in the District\'s work-release program, plus a suspended jail term and one year\'s probation.',
                    'charges' => 'Misdemeanor arising from the October 1, 1975 pressroom violence (pleaded guilty in April 1977 after felony charges were dropped in a plea agreement).',
                    'convicted' => 'Yes — guilty plea (April 1977); sentenced May 19, 1977 in D.C. Superior Court by Judge Sylvia Bacon.',
                    'sentence' => '60 days in the D.C. work-release program (halfway house), plus a suspended jail term and one year\'s probation.',
                ],
                [
                    'name' => 'Michael Tenorio', 'first' => 'Michael', 'last' => 'Tenorio',
                    'fragment' => 'Michael Tenorio was sentenced to 60 days in the District\'s work-release program, plus a suspended jail term and one year\'s probation. Prosecutors said Tenorio and Jack McIntosh "played very significant roles in disabling the presses."',
                    'charges' => 'Misdemeanor arising from the October 1, 1975 pressroom violence (pleaded guilty in April 1977 after felony charges were dropped in a plea agreement).',
                    'convicted' => 'Yes — guilty plea (April 1977); sentenced May 19, 1977 in D.C. Superior Court by Judge Sylvia Bacon.',
                    'sentence' => '60 days in the D.C. work-release program (halfway house), plus a suspended jail term and one year\'s probation.',
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
                    'description'    => self::CONTEXT."\n\n".$d['fragment'],
                    'gender'         => 'Male',
                    'state'          => 'District of Columbia',
                    'era'            => '1970s',
                    'ideologies'     => ['Labor', 'Trade unionism'],
                    'affiliation'    => ['International Printing and Graphic Communications Union, Local 6'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id'    => $prisoner->id,
                    'institution_id' => $doc->id,
                    'charges'        => $d['charges'],
                    'convicted'      => $d['convicted'],
                    'sentenced_date' => '1977-05-19',
                    'sentence'       => $d['sentence'],
                    'judge'          => 'Sylvia Bacon',
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
