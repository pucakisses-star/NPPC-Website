<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the coordinated late-May 2026 anti-vivisection "beagle testing" protest
 * wave to the dashboard as DashboardLink map markers (category: protest). These
 * were peaceful demonstrations (no arrests) on/around May 30, 2026 targeting the
 * beagle breeding/testing pipeline — Charles River Laboratories (Worcester),
 * Marshall BioResources (North Rose), and Ridglan Farms (Blue Mounds).
 *
 * The Ridglan criminal *prosecution* is tracked separately (a marker in
 * AddSpeechArrestDashboardCases and prisoner records in
 * prisoners:add-ridglan-beagle-defendants); this command is just the protests.
 *
 * Coordinated cities Cleveland, Seattle, and Nashville were reported in
 * aggregate roundups but lacked any dedicated local sourcing (venue, date,
 * attendance), so they are intentionally omitted rather than logged with
 * invented detail. Matched on URL via updateOrCreate, so re-running is safe.
 */
class AddBeagleProtestWaveDashboardCases extends Command {
    protected $signature = 'dashboard:add-beagle-protest-wave';
    protected $description = 'Add the May 2026 beagle-testing protest wave (Worcester, North Rose, Blue Mounds) to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Protesters demand Charles River Laboratories end beagle testing at its downtown Worcester lab',
                'url'            => 'https://www.telegram.com/story/news/2026/06/02/beagle-testing-protest-worcester-charles-river-laboratories/90353095007/',
                'source'         => 'Worcester Telegram & Gazette',
                'category'       => 'protest',
                'published_at'   => '2026-05-30',
                'location_label' => 'Charles River Laboratories, 55 Union St, Worcester, MA',
                'lat'            => 42.2692540,
                'lng'            => -71.7978551,
            ],
            [
                'title'          => "'Stand for the Beagles': three-day vigil outside the Marshall BioResources beagle-breeding facility",
                'url'            => 'https://13wham.com/news/local/animal-advocates-protest-animal-experimentation-outside-breeding-facility-farm-in-wayne-county-marshall-bioresources-farms-lake-bluff-road-huron-north-rose-beagle-lovers-dogs-beagles-against-all-oddz-research',
                'source'         => '13 WHAM',
                'category'       => 'protest',
                'published_at'   => '2026-05-30',
                'location_label' => 'Marshall BioResources, North Rose, NY',
                'lat'            => 43.2138581,
                'lng'            => -76.9049358,
            ],
            [
                'title'          => 'About 100 rally outside Ridglan Farms, calling for release of the last ~650 beagles and the facility\'s closure',
                'url'            => 'https://www.cbs58.com/news/protesters-rally-outside-ridglan-farms-call-for-release-of-remaining-beagles-and-closure-of-facility',
                'source'         => 'CBS 58',
                'category'       => 'protest',
                'published_at'   => '2026-05-30',
                'location_label' => 'Ridglan Farms, Blue Mounds, WI',
                'lat'            => 42.9794371,
                'lng'            => -89.7939825,
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
