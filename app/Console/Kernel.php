<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Refresh the stored duration columns. Open-ended cases count up to
        // today, so their correct value grows daily and has to be rewritten.
        //
        // This used to be cases:update-imprisoned-days, which reimplemented
        // both calculations instead of calling the model, and so had none of
        // the guards that live there: it counted *every* case with an
        // incarceration date and no release date up to the present, whether
        // or not anyone was still being held. That is how Julia Emory, jailed
        // as a suffragist in 1917, came to publish "Imprisoned For 323 years",
        // and Thornton Blackburn, who escaped slavery in 1833, "193 years" in
        // exile. prisoners:recompute-imprisonment calls
        // PrisonerCase::computeImprisonedForDays() and computeInExileForDays()
        // — the same methods the model's saving hook uses — so the nightly
        // job and an ordinary admin save can no longer disagree.
        $schedule->command('prisoners:recompute-imprisonment --apply')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
