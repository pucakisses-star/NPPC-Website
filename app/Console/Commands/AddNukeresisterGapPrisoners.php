<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Adds 11 anti-nuclear / anti-war prisoners surfaced by a deep
 * sweep of nukeresister.org that weren't already in the NPPC DB.
 *
 * - Kings Bay Plowshares 7 stragglers: Patrick O'Neill, Elizabeth
 *   "Liz" McAlister
 * - Drone whistleblower: Daniel Hale (USP Marion CMU, 45 mo
 *   Espionage Act)
 * - Büchel Air Base anti-nuke actions in Germany (US B61
 *   weapons): Dennis DuVall (US Vietnam vet, multiple terms incl.
 *   Bautzen Prison), Frits ter Kuile, Holger Isabelle Jänicke,
 *   Susan van der Hijden
 * - Anti-recruiting / SOA Watch / Y-12 line-crossing line:
 *   Norm Lowry (PA, 1-7 yr state), Beth Rosdatter (Y-12 2010,
 *   1 mo Knox County), Nancy Smith + Michael David Omondi (SOA
 *   2010, 6 mo federal each)
 *
 * No new PDFs to add — the back-issues archive is exclusively the
 * newsletter run we already imported (69 issues through Dec 2025).
 */
final class AddNukeresisterGapPrisoners extends Command {
    protected $signature = 'archive:add-nukeresister-gap-prisoners';
    protected $description = 'Add 11 anti-nuclear / anti-war prisoners surfaced from nukeresister.org sweep';

    public function handle(): int {
        $data = json_decode(file_get_contents(database_path('data/nukeresister-gap-prisoners.json')), true);

        $added = 0;
        $failed = 0;
        foreach ($data['prisoners'] as $p) {
            $entry = [
                'name' => $p['name'],
                'description' => $p['description'],
                'era' => $p['era'],
                'released' => ($p['status'] ?? '') === 'released',
                'in_custody' => ($p['status'] ?? '') === 'in_custody',
                'awaiting_trial' => ($p['status'] ?? '') === 'awaiting_trial',
            ];
            if (! empty($p['aka'])) {
                $entry['aka'] = $p['aka'];
            }
            if (! empty($p['state'])) {
                $entry['state'] = $p['state'];
            }
            $parts = preg_split('/\s+/', trim($p['name']));
            $entry['first_name'] = $parts[0];
            $entry['last_name'] = end($parts);
            $entry['ideologies'] = ['Anti-nuclear', 'Anti-war', 'Catholic Worker / Plowshares'];
            $entry['affiliation'] = ['Nuclear Resister'];

            $case = [
                'charges' => $p['charges'] ?? null,
                'sentence' => $p['sentence'] ?? null,
            ];
            if (! empty($p['incarceration_date'])) {
                $case['incarceration_date'] = $p['incarceration_date'];
            }
            if (! empty($p['release_date'])) {
                $case['release_date'] = $p['release_date'];
            }
            $entry['cases'] = [$case];

            $this->line("\n— {$p['name']} —");
            $code = Artisan::call('prisoner:add', ['json' => json_encode($entry)]);
            $this->line(trim(Artisan::output()));
            if ($code === self::SUCCESS) {
                $added++;
            } else {
                $failed++;
            }
        }

        $this->info("\nAdded: {$added}    Failed: {$failed}");

        return self::SUCCESS;
    }
}
