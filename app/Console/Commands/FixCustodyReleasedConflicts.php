<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Resolves the four prisoners that had BOTH "in custody" and "released" checked,
 * setting each to its verified real status:
 *   - John Mazurek      — released (March 2026 Alford plea: 10 yrs probation, time served)
 *   - Luke O'Donovan     — released (left Washington State Prison July 2016)
 *   - Sekou Kambui       — released (paroled June 2014)
 *   - Jamil Abdullah al-Amin (H. Rap Brown) — died in federal prison Nov 23, 2025;
 *       neither in custody nor released (death date already recorded)
 *
 * Keyed by slug; sets the flags authoritatively (idempotent). --dry-run previews.
 */
final class FixCustodyReleasedConflicts extends Command
{
    protected $signature = 'prisoners:fix-custody-released-conflicts {--dry-run : Preview without saving}';

    protected $description = 'Set correct in_custody/released for the prisoners that had both checked';

    /** @var array<string,array{in_custody:bool,released:bool,note:string}> slug => target */
    private const FIXES = [
        'john-mazurek' => ['in_custody' => false, 'released' => true, 'note' => 'released (probation/time served, 2026)'],
        'luke-odonovan' => ['in_custody' => false, 'released' => true, 'note' => 'released (left prison July 2016)'],
        'sekou-kambui' => ['in_custody' => false, 'released' => true, 'note' => 'released (paroled June 2014)'],
        'jamil-abdullah-al-amin' => ['in_custody' => false, 'released' => false, 'note' => 'deceased — died in prison Nov 23, 2025'],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $fixed = 0;
        $notFound = 0;

        foreach (self::FIXES as $slug => $target) {
            $prisoner = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Not found: {$slug}");
                $notFound++;

                continue;
            }

            $before = ($prisoner->in_custody ? 'custody' : '').($prisoner->released ? '+released' : '');
            if ($dryRun) {
                $this->line("  would set in_custody=".($target['in_custody'] ? 'true' : 'false')
                    .', released='.($target['released'] ? 'true' : 'false')
                    ."  [{$before}] — {$prisoner->name}: {$target['note']}");
            } else {
                $prisoner->in_custody = $target['in_custody'];
                $prisoner->released = $target['released'];
                $prisoner->save();
                $this->info("  {$prisoner->name}: {$target['note']}");
            }
            $fixed++;
        }

        $this->info("\nDone".($dryRun ? ' (dry run)' : '').". fixed={$fixed} notFound={$notFound}");

        return self::SUCCESS;
    }
}
