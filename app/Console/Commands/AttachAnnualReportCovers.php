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
 * density grows with the documented-case count. Also installs any full
 * report PDFs shipped in database/data/reports/ (currently 2024) and
 * sets the row's file, which makes the cover a clickable link on
 * /annual-report. Idempotent by content hash.
 */
final class AttachAnnualReportCovers extends Command {
    protected $signature = 'annual-reports:attach-covers';
    protected $description = 'Attach generated covers and shipped report PDFs to the annual reports';

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
            if (is_file($src)) {
                $done += $this->install($report, $src, 'annual-reports/cover-'.$year.'.jpg', 'image', 'cover');
            } else {
                $this->warn("Missing repo image cover-{$year}.jpg — skipped.");
            }

            $pdf = database_path('data/reports/nppc-annual-report-'.$year.'.pdf');
            if (is_file($pdf)) {
                $done += $this->install($report, $pdf, 'annual-reports/nppc-annual-report-'.$year.'.pdf', 'file', 'report PDF');
            }
        }

        $this->info("Done. {$done} attachment(s) made.");

        return self::SUCCESS;
    }

    private function install(AnnualReport $report, string $src, string $dest, string $column, string $label): int {
        $disk = Storage::disk('public');
        $bytes = file_get_contents($src);
        if ($report->{$column} === $dest && $disk->exists($dest) && md5($disk->get($dest)) === md5($bytes)) {
            $this->line("Already attached ({$label}): {$report->title}");

            return 0;
        }
        $disk->makeDirectory('annual-reports');
        $disk->put($dest, $bytes);
        $report->{$column} = $dest;
        $report->save();
        $this->info("Attached {$label} to: {$report->title}");

        return 1;
    }
}
