<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;

/**
 * Dashboard newswire additions for the week of July 12-19, 2026: the
 * two national mobilizations of the week. The Fergie Chambers Spain
 * arrest (July 14) was already on the newswire from earlier coverage.
 */
final class AddWeekJul192026DashboardLinks extends Command {
    protected $signature = 'dashboard:add-week-2026-07-19';
    protected $description = 'Add dashboard links for the week of July 12-19, 2026';

    public function handle(): int {
        $links = [
            [
                'url' => 'https://www.usnews.com/news/top-news/articles/2026-07-18/us-data-center-protests-go-national-as-backlash-grows',
                'title' => 'Data center opponents stage 142 protests across 42 states in first national day of action',
                'source' => 'Reuters / U.S. News',
                'category' => 'protest',
                'published_at' => '2026-07-18',
                'location_label' => 'Nationwide',
            ],
            [
                'url' => 'https://www.independent.com/2026/07/18/good-trouble-lives-on-rally-draws-hundreds-to-santa-barbara-waterfront/',
                'title' => 'Good Trouble Lives On: 400+ demonstrations in all 50 states mark John Lewis anniversary weekend',
                'source' => 'Santa Barbara Independent',
                'category' => 'protest',
                'published_at' => '2026-07-18',
                'location_label' => 'Santa Barbara, CA (nationwide)',
                'lat' => 34.4108,
                'lng' => -119.6866,
            ],
        ];

        foreach ($links as $link) {
            $url = $link['url'];
            unset($link['url']);
            $link['published_at'] = \Illuminate\Support\Carbon::parse($link['published_at']);
            DashboardLink::updateOrCreate(['url' => $url], $link);
            $this->info("Upserted: {$link['title']}");
        }

        return self::SUCCESS;
    }
}
