<?php

namespace App\Console\Commands;

use App\Models\PrisonerCase;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateImprisonedDays extends Command {
    protected $signature = 'cases:update-imprisoned-days';
    protected $description = 'Recalculate imprisoned_for_days and in_exile_for_days for cases where the prisoner is still incarcerated or in exile';

    public function handle(): int {
        $imprisonedUpdated = 0;
        $imprisonedCases = PrisonerCase::whereNotNull('incarceration_date')
            ->whereNull('release_date')
            ->get();
        foreach ($imprisonedCases as $case) {
            $days = (int) Carbon::parse($case->incarceration_date)->diffInDays(Carbon::today());
            if ($case->imprisoned_for_days !== $days) {
                $case->imprisoned_for_days = $days;
                $case->saveQuietly();
                $imprisonedUpdated++;
            }
        }
        $this->info("imprisoned_for_days: updated {$imprisonedUpdated} of {$imprisonedCases->count()} active cases.");

        $exileUpdated = 0;
        $exileCases = PrisonerCase::whereNotNull('in_exile_since')
            ->whereNull('end_of_exile')
            ->get();
        foreach ($exileCases as $case) {
            $days = (int) Carbon::parse($case->in_exile_since)->diffInDays(Carbon::today());
            if ($case->in_exile_for_days !== $days) {
                $case->in_exile_for_days = $days;
                $case->saveQuietly();
                $exileUpdated++;
            }
        }
        $this->info("in_exile_for_days: updated {$exileUpdated} of {$exileCases->count()} active exile cases.");

        return self::SUCCESS;
    }
}
