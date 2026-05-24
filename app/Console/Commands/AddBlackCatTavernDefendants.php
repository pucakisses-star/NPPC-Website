<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds Benny Baker and Charles Talley — two of the six patrons
 * convicted under California Penal Code §647 (lewd conduct) after
 * the January 1, 1967 LAPD raid on the Black Cat Tavern in Silver
 * Lake, Los Angeles. The raid happened minutes after the New Year\'s
 * Eve midnight kiss; plainclothes officers severely beat patrons
 * and bartenders. The arrests sparked the February 11, 1967 Black
 * Cat protest — one of the earliest organized LGBT public
 * demonstrations in U.S. history, two years before Stonewall.
 *
 * Baker and Talley were the lead-named defendants in the resulting
 * California Supreme Court appeal; their convictions were upheld
 * and the U.S. Supreme Court declined to hear the case. They were
 * forced to register as sex offenders for the rest of their lives.
 */
final class AddBlackCatTavernDefendants extends Command {
    protected $signature = 'archive:add-black-cat-tavern';
    protected $description = 'Add Benny Baker and Charles Talley (Black Cat Tavern raid, LA 1967)';

    public function handle(): int {
        $sharedDescription = 'One of six gay men arrested by undercover LAPD officers during the January 1, 1967 raid on the Black Cat Tavern in Silver Lake, Los Angeles. Officers entered the bar minutes after the New Year\'s Eve midnight kiss and brutally beat patrons and bartenders. Convicted under California Penal Code §647(a) for lewd conduct (kissing another man in public); forced to register as a sex offender for life. The arrests prompted the February 11, 1967 Black Cat protest — one of the earliest organized LGBT public demonstrations in U.S. history, two and a half years before Stonewall. Baker and Talley\'s appeal reached the California Supreme Court, which upheld their convictions; the U.S. Supreme Court declined to hear the case.';

        $sharedCase = [
            'institution_state' => 'California',
            'charges' => 'California Penal Code §647(a) — lewd conduct (kissing another man in public during a New Year\'s Eve celebration at a gay bar).',
            'arrest_date' => '1967-01-01',
            'sentenced_date' => '1967-09-01',
            'convicted' => 'Yes — upheld by California Supreme Court; U.S. Supreme Court declined to hear appeal.',
            'sentence' => 'Misdemeanor conviction; lifetime sex offender registration.',
        ];

        $base = [
            'state' => 'California',
            'gender' => 'Male',
            'ideologies' => ['Queer liberation', 'Civil rights'],
            'affiliation' => ['Black Cat Tavern defendants'],
            'era' => '1960s',
            'in_custody' => false,
            'released' => true,
        ];

        $payloads = [
            ['name' => 'Benny Baker', 'first_name' => 'Benny', 'last_name' => 'Baker'] + $base + ['description' => $sharedDescription, 'cases' => [$sharedCase]],
            ['name' => 'Charles Talley', 'first_name' => 'Charles', 'last_name' => 'Talley'] + $base + ['description' => $sharedDescription, 'cases' => [$sharedCase]],
        ];

        $added = 0; $skipped = 0;
        foreach ($payloads as $p) {
            $exit = $this->call('prisoner:add', ['json' => json_encode($p)]);
            if ($exit === self::SUCCESS) { $this->info('ADD: '.$p['name']); $added++; }
            else { $skipped++; }
        }
        $this->info("Done — added {$added}, skipped {$skipped}.");
        return self::SUCCESS;
    }
}
