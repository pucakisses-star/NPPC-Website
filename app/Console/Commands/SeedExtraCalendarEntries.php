<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Seed a second batch of "On This Day" calendar entries beyond the
 * Working Class History import — 55 movement / political-prisoner
 * dates curated to fill the empty days in the existing calendar.
 *
 * Idempotent: skips any day that already has an entry. One entry
 * per (month, day) like the original seeder.
 */
final class SeedExtraCalendarEntries extends Command {
    protected $signature = 'calendar:seed-extra';
    protected $description = 'Seed additional "On This Day" entries on days the existing calendar has not yet filled.';

    public function handle(): int {
        $path = resource_path('data/extra-calendar-entries.json');
        if (! file_exists($path)) {
            $this->error("Missing data file: {$path}");
            return self::FAILURE;
        }

        $entries = json_decode(file_get_contents($path), true);
        if (! is_array($entries)) {
            $this->error("Failed to parse {$path}");
            return self::FAILURE;
        }

        $this->info('Loaded '.count($entries).' candidate entries.');

        $created = 0;
        $skipped = 0;

        foreach ($entries as $row) {
            DB::transaction(function () use ($row, &$created, &$skipped) {
                $existing = CalendarEntry::where('month', $row['month'])
                    ->where('day', $row['day'])
                    ->first();

                if ($existing) {
                    $this->line("skip  {$row['month']}/{$row['day']}  has: ".$existing->title);
                    $skipped++;
                    return;
                }

                CalendarEntry::create([
                    'month'       => $row['month'],
                    'day'         => $row['day'],
                    'year'        => $row['year'] ?? null,
                    'title'       => $row['title'],
                    'description' => $row['description'],
                    'published'   => true,
                ]);
                $this->info("add   {$row['month']}/{$row['day']}  {$row['title']}");
                $created++;
            });
        }

        $this->line('');
        $this->info("Done. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
