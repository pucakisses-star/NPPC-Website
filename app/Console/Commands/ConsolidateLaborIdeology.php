<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Renames the broad labor-movement ideology label to "Labor Activism" across
 * every prisoner: "Labor organizing" (the previous broad category) and the
 * stray "Labor activism" both become "Labor Activism". More specific labor
 * ideologies (Anarcho-Syndicalism, Industrial unionism, Trade unionism, Farm
 * organizing, Black Southern labor organizing, Syndicalism, Greenback-Labor)
 * are deliberately left untouched. Each prisoner's ideology list is de-duplicated
 * after the remap. Idempotent — the canonical label is never a key. Use
 * --dry-run to preview.
 */
class ConsolidateLaborIdeology extends Command
{
    protected $signature = 'prisoners:consolidate-labor-ideology {--dry-run : Show what would change without writing}';

    protected $description = 'Merge "Labor organizing"/"Labor activism" into "Labor Activism"';

    /** variant => canonical. Canonical ("Labor Activism") never appears as a key. */
    private const MAP = [
        'Labor organizing' => 'Labor Activism',
        'Labor activism' => 'Labor Activism',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $prisoners = Prisoner::withoutGlobalScopes()->get(['id', 'ideologies']);

        $changed = 0;
        $remaps = 0;
        foreach ($prisoners as $p) {
            $ids = $p->ideologies;
            if (! is_array($ids)) {
                $ids = ($ids === null || $ids === '') ? [] : [$ids];
            }
            if (! $ids) {
                continue;
            }

            $new = [];
            $didRemap = false;
            foreach ($ids as $i) {
                $canon = self::MAP[$i] ?? $i;
                if ($canon !== $i) {
                    $didRemap = true;
                    $remaps++;
                }
                if (! in_array($canon, $new, true)) {
                    $new[] = $canon;
                }
            }

            if ($new !== array_values($ids)) {
                $changed++;
                if (! $dryRun) {
                    DB::table('prisoners')->where('id', $p->id)->update(['ideologies' => json_encode($new)]);
                }
            } elseif ($didRemap) {
                $changed++;
            }
        }

        if ($dryRun) {
            $this->info("Dry run — {$changed} prisoner rows would change; {$remaps} label remaps.");
        } else {
            Cache::forget(PrisonerApiController::cacheKey());
            $this->info("Consolidated to 'Labor Activism' on {$changed} prisoner rows ({$remaps} remaps). API cache cleared.");
        }

        return self::SUCCESS;
    }
}
