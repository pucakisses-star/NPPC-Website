<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;

/**
 * Dashboard newswire additions for the week of July 12-19, 2026: the
 * two national mobilizations, the ICE killings of Lorenzo Salgado
 * Araujo (Houston, July 7) and Johan Sebastian Duran Guerrero
 * (Biddeford, Maine, July 13) with the protest wave around them, and
 * Trump's reversal of the DHS traffic-stop pause. Every row carries a
 * map pin. The Fergie Chambers Spain arrest (July 14) was already on
 * the newswire.
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
                'location_label' => 'Nationwide (pinned at Washington, DC)',
                'lat' => 38.9072,
                'lng' => -77.0369,
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
            [
                'url' => 'https://www.texastribune.org/2026/07/11/texas-houston-protest-ice-shooting-lorenzo-salgado-araujo/',
                'title' => 'Hundreds in Houston protest the ICE killing of Lorenzo Salgado Araujo; march swells past 1,000',
                'source' => 'The Texas Tribune',
                'category' => 'protest',
                'published_at' => '2026-07-11',
                'location_label' => 'Houston, TX',
                'lat' => 29.7604,
                'lng' => -95.3698,
            ],
            [
                'url' => 'https://www.kut.org/crime-justice/2026-07-12/austin-tx-vigil-lorenzo-salgado-araujo-ice-shooting-houston',
                'title' => 'About 200 pack a South Austin church for a vigil honoring Lorenzo Salgado Araujo',
                'source' => 'KUT Austin',
                'category' => 'protest',
                'published_at' => '2026-07-12',
                'location_label' => 'Austin, TX',
                'lat' => 30.2672,
                'lng' => -97.7431,
            ],
            [
                'url' => 'https://www.npr.org/2026/07/13/nx-s1-5890966/houston-community-holds-vigil-for-man-killed-by-ice-agents',
                'title' => 'Houston community holds vigil for Lorenzo Salgado Araujo, killed by an ICE officer on July 7',
                'source' => 'NPR',
                'category' => 'protest',
                'published_at' => '2026-07-13',
                'location_label' => 'East Houston, TX',
                'lat' => 29.7426,
                'lng' => -95.2677,
            ],
            [
                'url' => 'https://www.pressherald.com/2026/07/14/who-is-joan-sebastian-guerrero-the-man-killed-by-ice-in-biddeford/',
                'title' => 'ICE officer kills Johan Sebastián Durán Guerrero, 26, in Biddeford — second fatal ICE shooting in a week',
                'source' => 'Portland Press Herald',
                'category' => 'other',
                'published_at' => '2026-07-14',
                'location_label' => 'Biddeford, ME',
                'lat' => 43.4926,
                'lng' => -70.4534,
            ],
            [
                'url' => 'https://www.cnn.com/2026/07/15/us/trump-ice-traffic-stops',
                'title' => 'Trump overturns DHS suspension of ICE traffic stops a day after the pause over two fatal shootings',
                'source' => 'CNN',
                'category' => 'other',
                'published_at' => '2026-07-15',
                'location_label' => 'Washington, DC',
                'lat' => 38.8977,
                'lng' => -77.0365,
            ],
            [
                'url' => 'https://www.click2houston.com/news/local/2026/07/17/over-30-houston-organizations-labor-groups-call-for-emergency-protest-to-demand-justice-for-lorenzo-salgado-araujo/',
                'title' => 'Over 30 Houston organizations and labor groups call an emergency protest for justice for Salgado Araujo',
                'source' => 'KPRC Click2Houston',
                'category' => 'protest',
                'published_at' => '2026-07-17',
                'location_label' => 'Houston, TX',
                'lat' => 29.7500,
                'lng' => -95.3400,
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
