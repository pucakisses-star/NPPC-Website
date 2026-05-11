<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Backfills the `website` field for the 13 SLABC-sourced prisoners with
 * the support sites documented in their case histories. Many of these
 * sites are dead today but remain useful as historical references and
 * Wayback Machine pivot points.
 */
final class BackfillSlAbcPrisonerWebsites extends Command {
    protected $signature = 'archive:backfill-slabc-prisoner-websites';
    protected $description = 'Set the website field for SLABC-sourced prisoners';

    public function handle(): int {
        $map = [
            'Jordan Halliday' => 'https://supportjordan.org',
            'Tim DeChristopher' => 'https://www.peacefuluprising.org',
            // ALKQN NC-12 — all share a single support site
            'Jorge Peter Cornell' => 'https://alkqnsupport.com',
            'Russell Kilfoil' => 'https://alkqnsupport.com',
            'Randolph Kilfoil' => 'https://alkqnsupport.com',
            'Ernesto Wilson' => 'https://alkqnsupport.com',
            'Samuel Velasquez' => 'https://alkqnsupport.com',
            'Jason Yates' => 'https://alkqnsupport.com',
            'Irvin Vasquez' => 'https://alkqnsupport.com',
            'Wesley Williams' => 'https://alkqnsupport.com',
            'Carlos Coleman' => 'https://alkqnsupport.com',
            'Russell Lloyd Cornell' => 'https://alkqnsupport.com',
            // KteeO had no dedicated personal site; the umbrella support
            // org was Committee Against Political Repression.
            'Katherine Olejnik' => 'https://nopoliticalrepression.wordpress.com',
        ];

        $updated = 0;
        $missing = 0;

        foreach ($map as $name => $website) {
            $prisoner = Prisoner::where('name', $name)->first();
            if (! $prisoner) {
                $this->warn("Not found: {$name}");
                $missing++;

                continue;
            }
            if ($prisoner->website === $website) {
                continue;
            }
            $prisoner->website = $website;
            $prisoner->save();
            $this->info("Set website for {$name}: {$website}");
            $updated++;
        }

        $this->info("\nDone. Updated={$updated} Missing={$missing}");

        return self::SUCCESS;
    }
}
