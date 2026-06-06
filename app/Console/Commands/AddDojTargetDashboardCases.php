<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds people the Trump administration TARGETED for retaliation -- criminal
 * referrals, DOJ/FBI investigations, attempted firings, grand-jury attempts,
 * and military reviews -- but who were NOT criminally indicted. These are
 * deliberately framed as "targeted / investigated / referred -- not charged"
 * and use the "other" category to keep them distinct from actual arrests and
 * prosecutions. In-window (on/after May 7, 2025); sourced from public reporting;
 * matched on URL so the command is idempotent.
 *
 * NOTE: Miles Taylor and Chris Krebs are intentionally omitted -- their only
 * documented retaliatory action (the April 9, 2025 executive orders) predates
 * the dashboard timeline start, with no confirmed in-window milestone.
 */
class AddDojTargetDashboardCases extends Command {
    protected $signature = 'dashboard:add-doj-target-cases';
    protected $description = 'Add Trump-administration retaliation targets (investigated/referred, not indicted) to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'U.S. Senator Adam Schiff was criminally referred to the Justice Department by a Trump housing official over alleged mortgage fraud; no charges were filed, and the DOJ later reviewed the referral itself for misconduct',
                'url'            => 'https://www.pbs.org/newshour/politics/document-shows-doj-examining-the-handling-of-mortgage-fraud-investigation-into-sen-schiff',
                'source'         => 'PBS NewsHour',
                'category'       => 'other',
                'published_at'   => '2025-07-15',
                'location_label' => 'Burbank, CA',
                'lat'            => 34.1808,
                'lng'            => -118.3090,
            ],
            [
                'title'          => 'President Trump moved to fire Federal Reserve Governor Lisa Cook over mortgage-fraud accusations and the DOJ opened an investigation; a judge blocked the firing and no charges were filed',
                'url'            => 'https://www.npr.org/2025/08/28/nx-s1-5520674/lisa-cook-lawsuit-trump-fed',
                'source'         => 'NPR',
                'category'       => 'other',
                'published_at'   => '2025-08-25',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8929,
                'lng'            => -77.0445,
            ],
            [
                'title'          => 'Federal prosecutors opened a criminal investigation into Federal Reserve Chair Jerome Powell over the central-bank headquarters renovation; a judge quashed the subpoenas and the DOJ later dropped the probe',
                'url'            => 'https://www.cnn.com/2026/01/11/business/federal-prosecutors-criminal-investigation-federal-reserve-chair-jerome-powell',
                'source'         => 'CNN',
                'category'       => 'other',
                'published_at'   => '2026-01-10',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8924,
                'lng'            => -77.0448,
            ],
            [
                'title'          => 'The FBI opened a criminal investigation into former CIA Director John Brennan over the 2016 Russia assessment after a referral from Trump appointees; no charges were filed',
                'url'            => 'https://www.pbs.org/newshour/politics/ap-sources-witnesses-subpoenaed-to-testify-before-d-c-grand-jury-in-john-brennan-investigation',
                'source'         => 'PBS NewsHour',
                'category'       => 'other',
                'published_at'   => '2025-07-09',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8940,
                'lng'            => -77.0250,
            ],
            [
                'title'          => 'U.S. Representative Eric Swalwell was criminally referred to the DOJ by a Trump housing official over his mortgage; no charges were filed, and he sued, alleging retaliation for criticizing Trump',
                'url'            => 'https://www.cnn.com/2025/11/25/politics/eric-swalwell-bill-pulte-lawsuit',
                'source'         => 'CNN',
                'category'       => 'other',
                'published_at'   => '2025-11-25',
                'location_label' => 'Dublin, CA',
                'lat'            => 37.7022,
                'lng'            => -121.9358,
            ],
            [
                'title'          => 'The Office of Special Counsel opened a Hatch Act investigation into former Special Counsel Jack Smith, who twice prosecuted Trump; House Republicans also subpoenaed his testimony',
                'url'            => 'https://www.cnn.com/2025/10/06/politics/jack-smith-january-6-gop-lawmakers-phone-records',
                'source'         => 'CNN',
                'category'       => 'other',
                'published_at'   => '2025-08-02',
                'location_label' => 'Washington, DC',
                'lat'            => 38.9050,
                'lng'            => -77.0160,
            ],
            [
                'title'          => 'A federal grand jury declined to indict six Democratic lawmakers (Crow, Goodlander, DeLuzio, Houlahan, Kelly, and Slotkin) after the DOJ sought charges over their video telling troops they may refuse illegal orders',
                'url'            => 'https://www.cnn.com/2026/02/10/politics/jury-declines-to-indict-lawmakers-illegal-orders-video',
                'source'         => 'CNN',
                'category'       => 'other',
                'published_at'   => '2026-02-10',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8899,
                'lng'            => -77.0091,
            ],
            [
                'title'          => 'The Pentagon opened a review that could recall Senator and retired Navy Captain Mark Kelly to active duty for court-martial over a video telling troops they may refuse illegal orders',
                'url'            => 'https://www.cnn.com/2025/11/24/politics/kelly-recall-service-pentagon',
                'source'         => 'CNN',
                'category'       => 'other',
                'published_at'   => '2025-11-24',
                'location_label' => 'Tucson, AZ',
                'lat'            => 32.2226,
                'lng'            => -110.9747,
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
