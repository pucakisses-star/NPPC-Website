<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the Beto Coral case to the dashboard: Colombian leftist activist and
 * Petro ally Franklin "Beto" Coral Garrido, detained by ICE in Arizona on
 * June 16, 2026 and facing deportation after overstaying a visa — a detention
 * critics (and President Petro) tie to a State Department memo by Secretary
 * Marco Rubio, framed as retaliation for his criticism of a Trump-backed
 * Colombian candidate. Idempotent (matched on URL).
 */
final class AddBetoCoralDashboardCase extends Command
{
    protected $signature = 'dashboard:add-beto-coral';

    protected $description = 'Add the Beto Coral ICE-detention case to the dashboard';

    public function handle(): int
    {
        $case = [
            'title' => 'Colombian leftist activist and Petro ally Beto Coral (Franklin Coral Garrido) detained by ICE in Arizona and facing deportation, in a case critics tie to a State Department memo by Marco Rubio over his criticism of a Trump-backed candidate',
            'url' => 'https://www.newsweek.com/trump-admin-responds-ice-detains-beto-coral-colombian-president-ally-12087542',
            'source' => 'Newsweek',
            'category' => 'arrest',
            'published_at' => '2026-06-17',
            'location_label' => 'Arizona',
            'lat' => 33.4484,
            'lng' => -112.0740,
        ];

        $link = DashboardLink::firstOrCreate(
            ['url' => $case['url']],
            array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
        );

        if ($link->wasRecentlyCreated) {
            $this->info("Added: {$case['title']}");
        } else {
            $this->line("Skipped (already present): {$case['title']}");
        }

        return self::SUCCESS;
    }
}
