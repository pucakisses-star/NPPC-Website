<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds WCVB's coverage of the June 8, 2026 Extinction Rebellion climate
 * protest that blocked rush-hour traffic in downtown Boston (Copley Square,
 * outside a natural-gas convention; five arrested) as an additional source
 * marker alongside the existing Boston Globe entry. Categorized "arrest";
 * placed in Boston with a slight offset from the Globe pin. Matched on URL
 * with updateOrCreate, so the command is idempotent.
 */
class AddWcvbBostonClimateDashboardCase extends Command {
    protected $signature = 'dashboard:add-wcvb-boston-climate-case';
    protected $description = 'Add WCVB source for the Boston climate protest to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Climate protestors block rush hour traffic in downtown Boston',
                'url'            => 'https://www.wcvb.com/article/climate-protestors-block-rush-hour-traffic-in-downtown-boston/40787426',
                'source'         => 'WCVB',
                'category'       => 'arrest',
                'published_at'   => '2026-06-08',
                'location_label' => 'Boston, MA',
                'lat'            => 42.3499,
                'lng'            => -71.0782,
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
