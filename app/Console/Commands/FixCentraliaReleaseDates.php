<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Corrects release dates for two Centralia (1919 IWW) defendants whose recorded
 * release did not match the historical parole record (and the IWW memorial
 * plaque):
 *
 *   - John Lamb: release stored as 1922-06-06 (~2 years), but he was paroled
 *     with the others in the early 1930s — "Imprisoned 13 years". Set to
 *     1933-05-01 (≈13 years, matching Britt Smith).
 *   - O.C. Bland: release stored as 1933-01-01 (≈12.7 years), plaque says
 *     "Imprisoned 12 years"; O.C. Bland was paroled a year before his brother
 *     Bert. Set to 1932-04-05 (exactly 12 years).
 *
 * Each change is applied ONLY when the case still holds the old (incorrect)
 * value, so this is safe to re-run and will not clobber a later manual edit.
 * imprisoned_for_days is recomputed automatically by the PrisonerCase saving
 * hook.
 */
final class FixCentraliaReleaseDates extends Command
{
    protected $signature = 'prisoners:fix-centralia-release-dates';

    protected $description = 'Correct John Lamb and O.C. Bland release dates to match the Centralia parole record';

    /**
     * slug => [expected-current-release (Y-m-d) or null, [year, month, day] to set, label]
     *
     * @var array<int,array{slug:string,from:string,to:array{int,int,int}}>
     */
    private const FIXES = [
        ['slug' => 'john-lamb', 'from' => '1922-06-06', 'to' => [1933, 5, 1]],
        ['slug' => 'oc-bland', 'from' => '1933-01-01', 'to' => [1932, 4, 5]],
    ];

    public function handle(): int
    {
        $changed = 0;

        foreach (self::FIXES as $f) {
            $prisoner = Prisoner::withUnderReview()->where('slug', $f['slug'])->first();
            if (! $prisoner) {
                $this->warn("Prisoner not found: {$f['slug']}");

                continue;
            }

            $case = $prisoner->cases()
                ->whereNotNull('incarceration_date')
                ->orderBy('incarceration_date')
                ->get()
                ->first(fn ($c) => optional($c->release_date)->format('Y-m-d') === $f['from']);

            if (! $case) {
                $this->line("{$prisoner->name}: no case with release {$f['from']} — already corrected or changed. Skipping.");

                continue;
            }

            [$y, $m, $d] = $f['to'];
            $case->setPartialDate('release_date', $y, $m, $d);
            $case->save();

            $case->refresh();
            $years = round(Carbon::parse($case->incarceration_date)->diffInDays(Carbon::parse($case->release_date)) / 365.25, 1);
            $this->info("{$prisoner->name}: release {$f['from']} → ".sprintf('%04d-%02d-%02d', $y, $m, $d)." (~{$years} years, imprisoned_for_days={$case->imprisoned_for_days}).");
            $changed++;
        }

        if ($changed > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        $this->info("\nDone. Corrected {$changed} case(s).");

        return self::SUCCESS;
    }
}
