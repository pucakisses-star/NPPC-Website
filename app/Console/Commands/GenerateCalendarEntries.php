<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use App\Models\PrisonerCase;
use App\Models\Prisoner;
use Illuminate\Console\Command;

class GenerateCalendarEntries extends Command {
    protected $signature = 'calendar:generate {--dry-run : Preview without saving}';
    protected $description = 'Auto-generate calendar entries from prisoner case dates';

    public function handle(): int {
        $dryRun = $this->option('dry-run');
        $created = 0;
        $skipped = 0;

        $cases = PrisonerCase::with(['prisoner', 'institution'])->get();

        foreach ($cases as $case) {
            if (! $case->prisoner) continue;

            $events = [];

            if ($case->arrest_date) {
                $events[] = [
                    'date'  => $case->arrest_date,
                    'title' => $case->prisoner->name.' arrested',
                    'type'  => 'arrest',
                ];
            }

            if ($case->incarceration_date) {
                $events[] = [
                    'date'  => $case->incarceration_date,
                    'title' => $case->prisoner->name.' incarcerated'.($case->institution ? ' at '.$case->institution->name : ''),
                    'type'  => 'incarceration',
                ];
            }

            if ($case->sentenced_date) {
                $events[] = [
                    'date'  => $case->sentenced_date,
                    'title' => $case->prisoner->name.' sentenced'.($case->sentence ? ': '.$case->sentence : ''),
                    'type'  => 'sentencing',
                ];
            }

            if ($case->release_date) {
                $events[] = [
                    'date'  => $case->release_date,
                    'title' => $case->prisoner->name.' released',
                    'type'  => 'release',
                ];
            }

            if ($case->death_in_custody_date) {
                $events[] = [
                    'date'  => $case->death_in_custody_date,
                    'title' => $case->prisoner->name.' died in custody',
                    'type'  => 'death',
                ];
            }

            foreach ($events as $event) {
                $month = (int) $event['date']->format('n');
                $day = (int) $event['date']->format('j');
                $year = (int) $event['date']->format('Y');

                // Check if entry already exists for this month/day/year
                $exists = CalendarEntry::where('month', $month)
                    ->where('day', $day)
                    ->where('year', $year)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line(sprintf('  %s %02d → %s (%d)', $event['date']->format('M'), $day, $event['title'], $year));
                } else {
                    CalendarEntry::create([
                        'month'       => $month,
                        'day'         => $day,
                        'year'        => $year,
                        'title'       => $event['title'],
                        'description' => null,
                        'image'       => $case->prisoner->photo,
                        'prisoner_id' => $case->prisoner->id,
                        'published'   => true,
                    ]);
                }

                $created++;
            }
        }

        if ($dryRun) {
            $this->warn("{$created} entries would be created, {$skipped} skipped (slot taken).");
        } else {
            $this->info("{$created} entries created, {$skipped} skipped.");
        }

        return self::SUCCESS;
    }
}
