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
        // Each entry: [month, day, historical year, title, description].
        // The (month, day) pair is uniquely constrained — only one entry per
        // calendar slot. New rows are created via firstOrCreate so reruns are
        // safe and existing entries are never overwritten.
        $entries = [
            [3, 28, 1919, 'Arkansas bans anarchism and communism',
             "The state of Arkansas joined most US states in banning anarchism and communism. The law to 'define and punish anarchy and Bolshevism' barred revolutionary activism and red and black flags, with up to six months jail as a punishment."],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($entries as [$month, $day, $year, $title, $description]) {
            DB::transaction(function () use ($month, $day, $year, $title, $description, &$created, &$skipped) {
                $existing = CalendarEntry::where('month', $month)->where('day', $day)->first();
                if ($existing) {
                    $this->line("Skipping {$month}/{$day} — already has: {$existing->title}");
                    $skipped++;
                    return;
                }

                CalendarEntry::create([
                    'month'       => $month,
                    'day'         => $day,
                    'year'        => $year,
                    'title'       => $title,
                    'description' => $description,
                    'published'   => true,
                ]);

                $this->info("Added {$month}/{$day}/{$year}: {$title}");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
