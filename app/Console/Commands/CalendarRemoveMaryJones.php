<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use Illuminate\Console\Command;

/**
 * Removes the "Black trans sex worker Mary Jones tried in NYC"
 * calendar entry. Idempotent.
 */
final class CalendarRemoveMaryJones extends Command {
    protected $signature = 'calendar:remove-mary-jones';
    protected $description = 'Remove the Mary Jones (1836) calendar entry';

    public function handle(): int {
        $deleted = CalendarEntry::query()
            ->where('title', 'Black trans sex worker Mary Jones tried in NYC')
            ->delete();

        $this->info("Deleted {$deleted} calendar entries.");
        return self::SUCCESS;
    }
}
