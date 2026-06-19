<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Harry M. Wicks, a founding 1919 Communist Party of America leader
 * named in the October 1919 Bureau of Investigation surveillance report.
 * He was arrested in Spokane in April 1919 for "disloyalty" and reportedly
 * jailed again in the November 1919 federal crackdown.
 *
 * NOTE: Wicks was repeatedly (but never conclusively) accused of being a
 * police informant / agent provocateur. Per the user's decision he is
 * included, with those allegations stated plainly in the biography so the
 * record is honest about the ambiguity. Idempotent (skips if he exists).
 */
final class AddHarryWicks extends Command
{
    protected $signature = 'prisoners:add-harry-wicks';

    protected $description = 'Add Harry M. Wicks (1919 CPA leader; Spokane "disloyalty" arrest) as a prisoner';

    public function handle(): int
    {
        if (Prisoner::withUnderReview()->where('name', 'Harry Wicks')->exists()) {
            $this->warn('Harry Wicks already exists — skipping.');

            return self::SUCCESS;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name' => 'Harry Wicks',
                'first_name' => 'Harry',
                'middle_name' => 'Moore',
                'last_name' => 'Wicks',
                'aka' => 'H. M. Wicks; Herbert Moore Wicks',
                'gender' => 'Male',
                'race' => 'White',
                'birthdate' => '1889-12-10',
                'death_date' => '1956-01-01',
                'state' => 'Oregon',
                'era' => '1910s',
                'ideologies' => ['Communism'],
                'affiliation' => ['Communist Party of America'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Harry M. Wicks (Herbert Moore Wicks, 1889–1956) was a founding leader of the Communist Party of America in 1919 — a delegate to its founding convention, a member of the committee that drafted the party program, a member of its Central Executive Committee representing Portland, Oregon, and associate editor of the party organ The Communist. In April 1919 he was arrested in Spokane, Washington, for "disloyalty" after a militant strike speech, and secondary sources report he was jailed again during the November 1919 federal anti-Communist crackdown, though no trial, conviction, or sentence is on record for either arrest. From 1919 onward he was repeatedly accused of being a police informant or agent provocateur — including a 1923 charge (of which a Workers Party commission cleared him) and a 1937 expulsion from the CPUSA as an alleged spy; historian Theodore Draper found no convincing evidence for the spy charges. He later served as a Communist International representative to the Communist Party of Australia (1930–31) and died in Chicago in 1956.',
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Arrested for "disloyalty" in Spokane, Washington (April 1919) after a strike speech; reportedly jailed again in the November 1919 federal Communist crackdown',
                'convicted' => 'Unknown — no trial, conviction, or sentence is on record for either arrest',
            ]);
        });

        $this->info('Added Harry Wicks.');

        return self::SUCCESS;
    }
}
