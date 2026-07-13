<?php

namespace App\Console\Commands;

use App\Models\AnnualReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches the generated cover images (database/data/photos/
 * annual-report-covers/) to the three AnnualReport rows, matched by the
 * year in their titles. The covers are an on-brand series — NPPC logo,
 * giant Verlag year, indigo rule, a theme line, and a starfield whose
 * density grows with the documented-case count. Idempotent by content
 * hash; only sets image, never touches file.
 */
final class AttachAnnualReportCovers extends Command {
    protected $signature = 'annual-reports:attach-covers';
    protected $description = 'Attach generated cover images to the annual reports';

    public function handle(): int {
        $disk = Storage::disk('public');
        $done = 0;

        foreach (['2023', '2024', '2025'] as $year) {
            $report = AnnualReport::where('title', 'like', '%'.$year.'%')->first();
            if (! $report) {
                $this->warn("No report with {$year} in its title — skipped.");
                continue;
            }
            $src = database_path('data/photos/annual-report-covers/cover-'.$year.'.jpg');
            if (! is_file($src)) {
                $this->warn("Missing repo image cover-{$year}.jpg — skipped.");
                continue;
            }
            $dest = 'annual-reports/cover-'.$year.'.jpg';
            $bytes = file_get_contents($src);
            if ($report->image === $dest && $disk->exists($dest) && md5($disk->get($dest)) === md5($bytes)) {
                $this->line("Already attached: {$report->title}");
                continue;
            }
            $disk->makeDirectory('annual-reports');
            $disk->put($dest, $bytes);
            $report->image = $dest;
            $report->save();
            $this->info("Attached cover to: {$report->title}");
            $done++;
        }

        $this->info("Done. {$done} cover(s) attached.");

        return self::SUCCESS;
    }
}
