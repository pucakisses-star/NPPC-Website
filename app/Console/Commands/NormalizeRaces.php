<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Normalizes the prisoners.race column to a small set of major, distinct
 * racial categories — one value per race. Ethnic/national sub-labels
 * (e.g. Japanese American, Vietnamese, Filipino) collapse into the broad
 * category (Asian), and mixed/variant spellings are unified. Idempotent.
 *
 * Canonical categories: White, Black, Asian, Hispanic, Native American,
 * Middle Eastern. Records with no race (null) are left untouched.
 */
class NormalizeRaces extends Command
{
    protected $signature = 'prisoners:normalize-races {--dry-run : Show what would change without writing}';

    protected $description = 'Collapse the prisoner race column into major distinct racial categories';

    /**
     * Non-canonical value => canonical major category.
     */
    private const MAP = [
        // Hispanic (base level for the Latino / Hispanic cluster)
        'Hispanic/Latino' => 'Hispanic',
        'Hispanic/Latina' => 'Hispanic',
        'Latino' => 'Hispanic',
        'Latina' => 'Hispanic',
        'Latinx' => 'Hispanic',
        'Chicano' => 'Hispanic',
        'Chicana' => 'Hispanic',
        'Cuban' => 'Hispanic',
        'Cuban American' => 'Hispanic',
        'Mexican American' => 'Hispanic',
        'Puerto Rican' => 'Hispanic',

        // Asian (collapse national/ethnic sub-labels)
        'Japanese American' => 'Asian',
        'Asian American' => 'Asian',
        'Filipino' => 'Asian',
        'Vietnamese' => 'Asian',
        'South Asian' => 'Asian',

        // Middle Eastern
        'Arab' => 'Middle Eastern',
        'Yemeni-American' => 'Middle Eastern',

        // Native American
        'Indigenous' => 'Native American',
        'Indigenous (Cherokee/Choctaw)' => 'Native American',
        'Pascua Yaqui' => 'Native American',
        'Apache/Chicano' => 'Native American', // Luis V. Rodriguez — indigenous-rights activist

        // Mixed heritage — mapped to the foregrounded political identity
        'Black/Cherokee' => 'Black', // Sekou Kambui — New Afrikan political prisoner
        'Afro-Puerto Rican' => 'Black', // Martin Sostre — Black-liberation jailhouse lawyer
    ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $total = 0;

        foreach (self::MAP as $from => $to) {
            $query = Prisoner::withUnderReview()->where('race', $from);
            $count = $query->count();
            if ($count === 0) {
                continue;
            }

            if (! $dry) {
                $query->update(['race' => $to]);
            }

            $this->line(sprintf('%s %-30s -> %-16s (%d)', $dry ? '[dry]' : '  ok ', $from, $to, $count));
            $total += $count;
        }

        $this->info("\n".($dry ? 'Would update' : 'Updated')." {$total} record(s).");

        // Report any race values still outside the canonical set.
        $canonical = ['White', 'Black', 'Asian', 'Hispanic', 'Native American', 'Middle Eastern'];
        $stray = Prisoner::withUnderReview()
            ->whereNotNull('race')
            ->whereNotIn('race', $canonical)
            ->distinct()
            ->pluck('race')
            ->all();

        if ($stray) {
            $this->warn('Non-canonical race values still present: '.implode(', ', $stray));
        }

        return self::SUCCESS;
    }
}
