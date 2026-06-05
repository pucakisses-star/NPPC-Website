<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * State-by-state sweep of the dashboard timeline window (May 7, 2025 onward) to
 * fill in geographic gaps -- protest/arrest cases in states that had no (or very
 * few) markers. Batch 1 covers New England + Mid-Atlantic findings (Maine,
 * Vermont, West Virginia), all nonviolent civil-disobedience arrests sourced
 * from public reporting. Matched on URL so the command is idempotent.
 */
class AddStateSweepDashboardCases extends Command {
    protected $signature = 'dashboard:add-state-sweep-cases';
    protected $description = 'Add state-sweep protest/arrest cases (geographic gap-fill) to the dashboard';

    public function handle(): int {
        $cases = [
            // ---- Maine ----
            [
                'title'          => 'Nine faith leaders arrested for trespassing at a "pray-in" in the Portland office of Senator Susan Collins, urging an end to ICE funding in Maine; they sang "We Shall Overcome"',
                'url'            => 'https://www.mainepublic.org/immigration/2026-01-27/nine-faith-leaders-arrested-at-pray-in-at-susan-collins-portland-office',
                'source'         => 'Maine Public',
                'category'       => 'arrest',
                'published_at'   => '2026-01-27',
                'location_label' => 'Portland, ME',
                'lat'            => 43.6580,
                'lng'            => -70.2560,
            ],
            // ---- Vermont ----
            [
                'title'          => 'Thirteen protesters cited for trespassing after occupying the atrium of an ICE digital-surveillance center in Williston, Vermont; the prosecutor later declined to charge them',
                'url'            => 'https://vtdigger.org/2026/02/10/11-arrested-during-ice-protest-at-williston-business-park/',
                'source'         => 'VTDigger',
                'category'       => 'arrest',
                'published_at'   => '2026-02-09',
                'location_label' => 'Williston, VT',
                'lat'            => 44.4760,
                'lng'            => -73.0840,
            ],
            [
                'title'          => 'Four arrested for unlawful trespass blockading the entrances to the ICE digital-surveillance center in Williston, Vermont',
                'url'            => 'https://vtdigger.org/2026/05/14/four-arrested-at-protest-against-ice-at-williston-facility/',
                'source'         => 'VTDigger',
                'category'       => 'arrest',
                'published_at'   => '2026-05-14',
                'location_label' => 'Williston, VT',
                'lat'            => 44.4755,
                'lng'            => -73.0848,
            ],
            // ---- West Virginia ----
            [
                'title'          => 'Six arrested for trespassing in a sit-in at the Charleston office of Senator Shelley Moore Capito against Medicaid and SNAP cuts, including two city council members',
                'url'            => 'https://westvirginiawatch.com/2025/06/25/six-arrested-while-protesting-cuts-to-medicaid-snap-outside-capitos-charleston-office/',
                'source'         => 'West Virginia Watch',
                'category'       => 'arrest',
                'published_at'   => '2025-06-25',
                'location_label' => 'Charleston, WV',
                'lat'            => 38.3506,
                'lng'            => -81.6320,
            ],
            [
                'title'          => 'Six arrested for trespassing, including a congressional candidate and clergy, in a Moral Mondays sit-in at the Morgantown office of Senator Shelley Moore Capito over Medicaid and SNAP cuts',
                'url'            => 'https://wvpublic.org/story/government/west-virginians-protest-across-state-six-arrested-in-morgantown/',
                'source'         => 'West Virginia Public Broadcasting',
                'category'       => 'arrest',
                'published_at'   => '2026-01-20',
                'location_label' => 'Morgantown, WV',
                'lat'            => 39.6360,
                'lng'            => -79.9540,
            ],
        ];

        $created = 0;
        foreach ($cases as $case) {
            $link = DashboardLink::firstOrCreate(
                ['url' => $case['url']],
                array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
            );

            if ($link->wasRecentlyCreated) {
                $created++;
                $this->info("Added: {$case['title']}");
            } else {
                $this->line("Skipped (already present): {$case['title']}");
            }
        }

        $this->info("Done. {$created} new case(s) added; ".(count($cases) - $created).' already present.');

        return self::SUCCESS;
    }
}
