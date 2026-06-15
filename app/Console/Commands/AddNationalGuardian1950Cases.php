<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Political-prisoner case found by reading the National Guardian's 1950 issues
 * (46 issues; OCR extracted from the marxists.org scans). The year's major
 * cases the paper covered — the Rosenbergs, Morton Sobell, Harry Bridges, the
 * Hollywood Ten (imprisoned 1950), Willie McGee, the Trenton Six, the
 * Martinsville Seven, Wesley Robert Wells, and Rosa Lee Ingram — are all
 * already in the database. The one genuine gap surfaced by reading the issues:
 *   - Harold Christoffel (UAW Local 248 president; perjury for denying CP ties)
 * (Notably NOT added: "Frank Clayton," who appears in a 1950 issue but as the
 * white lynch-mob leader convicted of murdering Black farmer Samuel Taylor —
 * a perpetrator, not a political prisoner.)
 * Verified against the Encyclopedia of Milwaukee, UW-Madison, TIME, and
 * Christoffel v. United States (1949). Idempotent.
 */
class AddNationalGuardian1950Cases extends Command {
    protected $signature = 'prisoners:add-ng-1950';
    protected $description = 'Add 1950 National Guardian cases (Harold Christoffel)';

    public function handle(): int {
        if (Prisoner::where('name', 'Harold Christoffel')->exists()) {
            $this->warn('Skipped (already exists): Harold Christoffel');
            return self::SUCCESS;
        }

        DB::transaction(function () {
            $p = Prisoner::create([
                'name'           => 'Harold Christoffel',
                'first_name'     => 'Harold',
                'last_name'      => 'Christoffel',
                'description'    => 'Harold R. Christoffel (1912–1991) was the founding president of United Auto Workers Local 248 at the Allis-Chalmers plant in Milwaukee — the largest union local in Wisconsin and the force behind a bitter 1946–47 strike — who became one of the most prominent labor figures imprisoned in the postwar Red Scare. Called before the House Education and Labor Committee in 1947, he denied being a Communist and was indicted for perjury. In Christoffel v. United States (1949) the Supreme Court reversed his first conviction because the House committee had lacked a quorum when he testified, but he was retried and convicted again in 1950 and sentenced to two-to-six years. He served three years in federal prison, from 1953 to 1956. The National Guardian followed his prosecution as an attack on militant trade unionism.',
                'gender'         => 'Male',
                'state'          => 'Wisconsin',
                'era'            => '1950s',
                'ideologies'     => ['Labor', 'Communism'],
                'affiliation'    => ['United Auto Workers Local 248'],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id' => $p->id,
                'charges'     => 'Perjury — for testifying to the House Education and Labor Committee in 1947 that he was not a Communist; a prosecution his supporters saw as an attack on a militant union leader.',
                'convicted'   => 'Yes — his first conviction was reversed by the Supreme Court in 1949 (Christoffel v. United States, for lack of a committee quorum), but he was retried and convicted again in 1950.',
                'sentence'    => 'Two to six years; he served three years in federal prison, 1953–1956.',
            ]);

            $this->info("Added: {$p->name} (slug: {$p->slug})");
        });

        return self::SUCCESS;
    }
}
