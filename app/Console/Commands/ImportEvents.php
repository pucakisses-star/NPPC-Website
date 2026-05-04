<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ImportEvents extends Command
{
    protected $signature = 'events:import {--file=resources/data/events.json : Path to JSON file (relative to project root)} {--update : Update existing events that match by (title, event_date) instead of skipping}';
    protected $description = 'Import real events into the Events table from a hand-maintained JSON file. Each entry: title, event_date (YYYY-MM-DD), time, location, description, body, event_url, series.';

    public function handle(): int
    {
        $file = base_path($this->option('file'));

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($file), true);
        if (! is_array($rows)) {
            $this->error("File is not valid JSON: {$file}");
            return self::FAILURE;
        }

        $this->info("Loaded " . count($rows) . " entries from {$file}.");

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors  = 0;

        foreach ($rows as $i => $row) {
            $required = ['title', 'event_date'];
            foreach ($required as $field) {
                if (empty($row[$field])) {
                    $this->error("Row {$i}: missing required field '{$field}', skipping.");
                    $errors++;
                    continue 2;
                }
            }

            try {
                $date = Carbon::parse($row['event_date']);
            } catch (\Throwable $e) {
                $this->error("Row {$i} ({$row['title']}): bad event_date '{$row['event_date']}', skipping.");
                $errors++;
                continue;
            }

            // Skip the example placeholder so it doesn't end up live
            if (str_starts_with(strtoupper($row['title']), 'EXAMPLE ')) {
                $this->line("Skipping example placeholder row.");
                $skipped++;
                continue;
            }

            $existing = Event::where('title', $row['title'])
                ->whereDate('event_date', $date->toDateString())
                ->first();

            $payload = [
                'title'       => $row['title'],
                'description' => $row['description'] ?? null,
                'body'        => $row['body'] ?? null,
                'event_date'  => $date,
                'time'        => $row['time'] ?? null,
                'location'    => $row['location'] ?? null,
                'event_url'   => $row['event_url'] ?? null,
                'series'      => $row['series'] ?? null,
                'published'   => true,
            ];

            if ($existing) {
                if (! $this->option('update')) {
                    $this->line("Skipping (exists): {$row['title']} on {$date->toDateString()}");
                    $skipped++;
                    continue;
                }
                $existing->update($payload);
                $this->info("Updated: {$row['title']} ({$date->toDateString()})");
                $updated++;
                continue;
            }

            Event::create($payload);
            $this->info("Added: {$row['title']} ({$date->toDateString()})");
            $created++;
        }

        $this->info("\nDone. Created {$created}, updated {$updated}, skipped {$skipped}, errors {$errors}.");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
