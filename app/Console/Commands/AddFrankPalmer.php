<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Frank L. Palmer (b. ~1893) — Colorado labor journalist jailed more than once
 * for his strike and organizing activity. Editor of the Denver labor weekly the
 * Colorado Labor Advocate and a correspondent for the Federated Press (the
 * left-wing labor news service); a leading voice of the strikers in the
 * 1927–1928 Colorado coal strike (the Columbine Mine Massacre) and author of
 * "Spies in Steel: An Exposé of Industrial War" (1928).
 *
 * The fact that he was "jailed more than once for his strike and organizing
 * activity" and lived in semi-retirement in Holland, Michigan, comes from a
 * 1967 newspaper account (surfaced by the maintainer). The specific charges,
 * dates and sentences of those jailings are NOT documented in the readily
 * available sources and are therefore not asserted here. Idempotent (skips by
 * name). Sources: Federated Press and Columbine Mine Massacre records; F. L.
 * Palmer, "Spies in Steel" (Denver, 1928).
 */
class AddFrankPalmer extends Command
{
    protected $signature = 'prisoners:add-frank-palmer';

    protected $description = 'Add Frank L. Palmer, the Colorado labor editor jailed for strike and organizing activity';

    public function handle(): int
    {
        DB::transaction(function () {
            $name = 'Frank L. Palmer';

            if (Prisoner::where('name', $name)->exists()) {
                $this->warn('Skipped (already exists): '.$name);

                return;
            }

            $prisoner = Prisoner::create([
                'name' => $name,
                'first_name' => 'Frank',
                'middle_name' => 'L.',
                'last_name' => 'Palmer',
                'description' => 'Frank L. Palmer was a labor journalist and editor prominent in 1920s Colorado. He edited the Colorado Labor Advocate, a Denver labor weekly, and was a correspondent for the Federated Press, the left-wing labor news service. During the 1927–1928 Colorado coal strike — the great walkout that culminated in the Columbine Mine Massacre, the killing of striking miners at Serene, Colorado in November 1927 — Palmer was a leading voice of the strikers, urging them to keep striking peacefully. He exposed corporate anti-union espionage in his 1928 book "Spies in Steel: An Exposé of Industrial War." A "fiery labor editor," he was, by a 1967 account, jailed more than once for his strike and organizing activity; that account also placed him in semi-retirement in Holland, Michigan, late in life. The specific charges, dates and sentences of his jailings are not documented in the readily available sources.',
                'state' => 'Colorado',
                'era' => '1920s',
                'ideologies' => ['Labor rights'],
                'affiliation' => ['Federated Press', 'Colorado Labor Advocate'],
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);

            // Year-only birth date -> year precision so the site shows "1893".
            $prisoner->setPartialDate('birthdate', 1893);
            $prisoner->save();

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Jailed on multiple occasions in connection with his strike and labor-organizing activity as a Colorado labor editor.',
                'convicted' => 'Jailed more than once for his strike and organizing activity (per a 1967 account); the specific charges and dispositions are not documented in the available sources.',
            ]);

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        return self::SUCCESS;
    }
}
