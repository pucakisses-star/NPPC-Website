<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Console\Command;

/**
 * Consolidates the singular "Report" news category into "Reports". If both
 * exist, every article under "Report" is reassigned to "Reports" and the
 * "Report" category is deleted; if only "Report" exists, it is renamed to
 * "Reports". Idempotent and safe to re-run (a no-op once "Report" is gone).
 */
final class MergeReportCategory extends Command
{
    protected $signature = 'articles:merge-report-category';

    protected $description = "Merge the singular 'Report' news category into 'Reports'";

    public function handle(): int
    {
        $report = Category::where('title', 'Report')->first();
        if (! $report) {
            $this->info("No 'Report' category found — nothing to do.");

            return self::SUCCESS;
        }

        $reports = Category::where('title', 'Reports')->where('id', '!=', $report->id)->first();

        if (! $reports) {
            // No "Reports" category yet — just rename "Report" → "Reports".
            $report->title = 'Reports';
            $report->slug = $this->uniqueSlug('reports', $report->id);
            $report->save();
            $this->info("Renamed 'Report' → 'Reports'.");

            return self::SUCCESS;
        }

        // Both exist — move "Report" articles to "Reports", then delete "Report".
        $moved = Article::where('category_id', $report->id)->update(['category_id' => $reports->id]);
        $report->delete();
        $this->info("Merged 'Report' into 'Reports' — {$moved} article(s) moved, 'Report' deleted.");

        return self::SUCCESS;
    }

    private function uniqueSlug(string $base, string $ignoreId): string
    {
        $slug = $base;
        $i = 2;
        while (Category::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
