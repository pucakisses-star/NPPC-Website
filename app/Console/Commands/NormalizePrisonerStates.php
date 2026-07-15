<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Normalizes the prisoners.state field from two-letter postal abbreviations
 * (e.g. "CA", "NY", "PR") to full state names ("California", "New York",
 * "Puerto Rico"), so every entry uses the same spelled-out form the rest of the
 * database already uses. Also folds the "Washington, D.C." variant into the
 * canonical "District of Columbia".
 *
 * Matches the stored value exactly against the USPS abbreviation table, so it
 * only ever touches rows that hold a recognised code and is safe to re-run
 * (already-expanded rows no longer match). Uses a bulk query-builder update
 * (state is not part of the slug, so no model events are needed) and busts the
 * /api/prisoners cache when anything changes.
 */
class NormalizePrisonerStates extends Command
{
    protected $signature = 'prisoners:normalize-states {--dry-run : Show the counts without writing}';

    protected $description = 'Expand two-letter state abbreviations on prisoner entries to full state names';

    /** USPS two-letter code => full name (states, DC, and inhabited territories). */
    private const STATES = [
        'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
        'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
        'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
        'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
        'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
        'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
        'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
        'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
        'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
        'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
        'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
        'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
        'WI' => 'Wisconsin', 'WY' => 'Wyoming',
        'DC' => 'District of Columbia',
        'PR' => 'Puerto Rico', 'VI' => 'U.S. Virgin Islands', 'GU' => 'Guam',
        'AS' => 'American Samoa', 'MP' => 'Northern Mariana Islands',
        // Non-postal variants that are the same place under a different spelling.
        'Washington, D.C.' => 'District of Columbia',
        'Washington DC' => 'District of Columbia',
        'D.C.' => 'District of Columbia',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $totalRows = 0;
        $codesTouched = 0;

        foreach (self::STATES as $code => $full) {
            $count = Prisoner::withoutGlobalScopes()->where('state', $code)->count();
            if ($count === 0) {
                continue;
            }

            $this->line(sprintf('  %-16s -> %-22s %d', $code, $full, $count));
            $totalRows += $count;
            $codesTouched++;

            if (! $dryRun) {
                Prisoner::withoutGlobalScopes()->where('state', $code)->update(['state' => $full]);
            }
        }

        if ($dryRun) {
            $this->info("\nDry run — {$totalRows} prisoner rows across {$codesTouched} codes would be expanded.");
        } else {
            if ($totalRows > 0) {
                Cache::forget(PrisonerApiController::cacheKey());
            }
            $this->info("\nExpanded {$totalRows} prisoner rows across {$codesTouched} codes to full state names. API cache cleared.");
        }

        return self::SUCCESS;
    }
}
