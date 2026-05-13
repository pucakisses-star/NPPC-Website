<?php

namespace App\Console\Commands;

use App\Models\AnnualReport;
use Illuminate\Console\Command;

/**
 * Removes the legacy "Fire to the Prisons" entries from the /annual-report
 * page and replaces them with placeholder NPPC Annual Report rows for
 * 2023, 2024, and 2025. The actual PDF + cover image can be uploaded
 * afterwards via the Filament admin panel at /admin/annual-reports.
 */
final class SwapAnnualReports extends Command {
    protected $signature = 'archive:swap-annual-reports';
    protected $description = 'Drop Fire to the Prisons entries and add NPPC Annual Report 2023/2024/2025 placeholders';

    public function handle(): int {
        $removed = 0;
        foreach (AnnualReport::where('title', 'like', '%Fire to the Prisons%')->get() as $report) {
            $title = $report->title;
            $report->delete();
            $this->info("Removed: {$title}");
            $removed++;
        }

        $titles = [
            'NPPC Annual Report 2023',
            'NPPC Annual Report 2024',
            'NPPC Annual Report 2025',
        ];

        $added = 0;
        foreach ($titles as $title) {
            $existing = AnnualReport::where('title', $title)->first();
            if ($existing) {
                $this->info("Exists: {$title} (id={$existing->id})");

                continue;
            }
            $report = AnnualReport::create([
                'title' => $title,
                'file'  => '',
                'image' => '',
            ]);
            $this->info("Added: {$title} (id={$report->id})");
            $added++;
        }

        $this->info("\nDone. Removed={$removed} Added={$added}");
        $this->line('Upload the PDF + cover image for each report at /admin/annual-reports.');

        return self::SUCCESS;
    }
}
