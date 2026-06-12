<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the wrongful arrest of public defender Bernardo Caceres at Rikers
 * Island to the dashboard as a DashboardLink arrest marker. Caceres, of
 * Queens Defenders, was arrested June 11, 2025 after a notoriously unreliable
 * drug field test falsely flagged a client's legal papers as THC-positive;
 * he was paraded past jeering correction officers and charged with promoting
 * contraband. In June 2026 he sued the correction officers' union and its
 * president Benny Boscio for defamation. Matched on URL so the command is
 * idempotent and safe to re-run.
 */
class AddRikersLawyerDashboardCases extends Command {
    protected $signature = 'dashboard:add-rikers-lawyer-cases';
    protected $description = 'Add the wrongful arrest of public defender Bernardo Caceres at Rikers Island to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'NYC lawyer wrongfully arrested for contraband drugs, defamed by Rikers Island union, lawsuit says',
                'url'            => 'https://www.nydailynews.com/2026/06/11/nyc-lawyer-wrongfully-arrested-for-contraband-drugs-defamed-by-rikers-island-union-lawsuit/',
                'source'         => 'New York Daily News',
                'category'       => 'arrest',
                'published_at'   => '2026-06-11',
                'location_label' => 'Rikers Island, NY',
                'lat'            => 40.7918,
                'lng'            => -73.8857,
            ],
        ];

        $created = 0;
        $updated = 0;
        foreach ($cases as $case) {
            $link = DashboardLink::updateOrCreate(
                ['url' => $case['url']],
                array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
            );

            if ($link->wasRecentlyCreated) {
                $created++;
                $this->info("Added: {$case['title']}");
            } else {
                $updated++;
                $this->line("Updated: {$case['title']}");
            }
        }

        $this->info("Done. {$created} added, {$updated} updated.");

        return self::SUCCESS;
    }
}
