<?php

namespace App\Console\Commands;

use App\Models\PrisonerCase;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateImprisonedDays extends Command {
    protected $signature = 'cases:update-imprisoned-days';
    protected $description = 'Recalculate imprisoned_for_days for cases where the prisoner is still incarcerated';

    public function handle(): int {
        $cases = PrisonerCase::whereNotNull('incarceration_date')
            ->whereNull('release_date')
            ->get();

        $updated = 0;

        foreach ($cases as $case) {
            $days = (int) Carbon::parse($case->incarceration_date)->diffInDays(Carbon::today());

            if ($case->imprisoned_for_days !== $days) {
                $case->imprisoned_for_days = $days;
                $case->saveQuietly();
                $updated++;
            }
        }

        $this->info("Updated {$updated} of {$cases->count()} active cases.");

        return self::SUCCESS;
    }
}
