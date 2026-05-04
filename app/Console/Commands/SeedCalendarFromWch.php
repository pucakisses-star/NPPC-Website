<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedCalendarFromWch extends Command
{
    protected $signature = 'calendar:seed-from-wch';
    protected $description = 'Seed the calendar with political-prisoner-relevant "On This Day" entries from Working Class History.';

    public function handle(): int
    {
        $path = resource_path('data/wch-calendar-entries.json');
        if (! file_exists($path)) {
            $this->error("Missing data file: {$path}");
            return self::FAILURE;
        }

        $entries = json_decode(file_get_contents($path), true);
        if (! is_array($entries)) {
            $this->error("Failed to parse {$path}");
            return self::FAILURE;
        }

        $this->info("Loaded " . count($entries) . " entries.");

        $created = 0;
        $skipped = 0;

        foreach ($entries as $row) {
            DB::transaction(function () use ($row, &$created, &$skipped) {
                $existing = CalendarEntry::where('month', $row['month'])
                    ->where('day', $row['day'])
                    ->first();

                if ($existing) {
                    $this->line("Skipping {$row['month']}/{$row['day']} — already has: {$existing->title}");
                    $skipped++;
                    return;
                }

                CalendarEntry::create([
                    'month'       => $row['month'],
                    'day'         => $row['day'],
                    'year'        => $row['year'],
                    'title'       => $row['title'],
                    'description' => $row['description'],
                    'published'   => true,
                ]);

                $this->info("Added {$row['month']}/{$row['day']}/{$row['year']}: {$row['title']}");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
