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
            [
                'title'          => 'Veteran U.S. Attorney Erik Siebert was forced out, and at least six Eastern District of Virginia prosecutors were fired or demoted, after refusing to bring the cases against James Comey and Letitia James',
                'url'            => 'https://www.npr.org/2025/09/20/nx-s1-5547837/us-attorney-virginia-resigns-letitia-james-probe',
                'source'         => 'NPR',
                'category'       => 'other',
                'published_at'   => '2025-09-19',
                'location_label' => 'Alexandria, VA',
                'lat'            => 38.8048,
                'lng'            => -77.0469,
            ],
            [
                'title'          => 'Federal prosecutor Maurene Comey, who handled the Epstein and Sean Combs cases, was fired without explanation; she sued, alleging retaliation for being the daughter of former FBI Director James Comey',
                'url'            => 'https://www.cbsnews.com/news/trump-doj-fires-maurene-comey-n-y-prosecutor-and-former-fbi-directors-daughter/',
                'source'         => 'CBS News',
                'category'       => 'other',
                'published_at'   => '2025-07-16',
                'location_label' => 'New York, NY',
                'lat'            => 40.7140,
                'lng'            => -74.0060,
            ],
            [
                'title'          => 'Senior FBI officials including former acting director Brian Driscoll were fired in a purge of agents tied to January 6 and Trump investigations; they sued FBI Director Kash Patel alleging political retaliation',
                'url'            => 'https://www.npr.org/2025/09/10/g-s1-87947/fbi-lawsuit-firing-retribution',
                'source'         => 'NPR',
                'category'       => 'other',
                'published_at'   => '2025-08-07',
                'location_label' => 'Newark, NJ',
                'lat'            => 40.7357,
                'lng'            => -74.1724,
            ],
            [
                'title'          => 'About 15 FBI agents were fired for having knelt during a 2020 George Floyd protest, years after the bureau cleared them; they sued, citing First Amendment retaliation',
                'url'            => 'https://www.npr.org/2025/12/08/g-s1-100970/fbi-agents-kneel-protest-lawsuit',
                'source'         => 'NPR',
                'category'       => 'other',
                'published_at'   => '2025-09-26',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8951,
                'lng'            => -77.0247,
            ],
            [
                'title'          => 'President Trump fired Bureau of Labor Statistics Commissioner Erika McEntarfer after a weak jobs report, accusing her without evidence of rigging the numbers',
                'url'            => 'https://www.nbcnews.com/business/economy/trump-orders-firing-bls-commissioner-weak-jobs-report-rcna222531',
                'source'         => 'NBC News',
                'category'       => 'other',
                'published_at'   => '2025-08-01',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8980,
                'lng'            => -77.0090,
            ],
            [
                'title'          => 'The administration revoked the security clearances of 37 current and former national-security officials, including former CIA and intelligence chiefs, in a memo critics called political retaliation',
                'url'            => 'https://federalnewsnetwork.com/intelligence-community/2025/08/trump-administration-revokes-security-clearances-of-37-current-and-former-government-officials/',
                'source'         => 'Federal News Network',
                'category'       => 'other',
                'published_at'   => '2025-08-19',
                'location_label' => 'Washington, DC',
                'lat'            => 38.9030,
                'lng'            => -77.0160,
            ],
            [
                'title'          => 'ABC suspended Jimmy Kimmel Live after the FCC chair threatened the network license over a Kimmel monologue about the Charlie Kirk killing; the show returned days later amid backlash',
                'url'            => 'https://www.nbcnews.com/pop-culture/tv/disneys-abc-pulls-jimmy-kimmel-live-fcc-chair-blasts-hosts-charlie-kir-rcna232033',
                'source'         => 'NBC News',
                'category'       => 'other',
                'published_at'   => '2025-09-17',
                'location_label' => 'Los Angeles, CA',
                'lat'            => 34.1016,
                'lng'            => -118.3267,
            ],
            [
                'title'          => 'Paramount paid President Trump $16 million to settle his lawsuit over a "60 Minutes" edit while seeking federal merger approval; CBS then canceled the Stephen Colbert show days after he called the deal a bribe',
                'url'            => 'https://www.npr.org/2025/07/02/nx-s1-5290171/trump-lawsuit-paramount-cbs-60-minutes-kamala-harris',
                'source'         => 'NPR',
                'category'       => 'other',
                'published_at'   => '2025-07-02',
                'location_label' => 'New York, NY',
                'lat'            => 40.7637,
                'lng'            => -73.9820,
            ],
            [
                'title'          => 'A federal appeals court let the White House continue barring the Associated Press from press events over its refusal to adopt the term "Gulf of America," a viewpoint-based exclusion',
                'url'            => 'https://www.cnn.com/2025/06/06/politics/white-house-ban-associated-press-continue',
                'source'         => 'CNN',
                'category'       => 'other',
                'published_at'   => '2025-06-06',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8977,
                'lng'            => -77.0365,
            ],
            [
                'title'          => 'The Justice Department subpoenaed Wall Street Journal reporters to unmask sources for a story about internal warnings over the Iran military campaign',
                'url'            => 'https://www.cbsnews.com/news/justice-department-wall-street-journal-subpoenas/',
                'source'         => 'CBS News',
                'category'       => 'other',
                'published_at'   => '2026-03-04',
                'location_label' => 'New York, NY',
                'lat'            => 40.7150,
                'lng'            => -74.0130,
            ],
            [
                'title'          => 'A court voided the mass layoffs gutting Voice of America, ruling that Trump appointee Kari Lake was unlawfully dismantling the congressionally chartered news service',
                'url'            => 'https://thehill.com/regulation/court-battles/5773475-voice-of-america-layoffs-blocked/',
                'source'         => 'The Hill',
                'category'       => 'other',
                'published_at'   => '2025-08-29',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8869,
                'lng'            => -77.0184,
            ],
            [
                'title'          => 'Federal judges struck down Trump executive orders targeting the law firms Jenner & Block, WilmerHale, and Susman Godfrey as unconstitutional retaliation for their clients and protected speech',
                'url'            => 'https://www.nbcnews.com/politics/justice-department/trump-executive-order-targeting-jenner-law-firm-unconstitutional-judge-rcna205230',
                'source'         => 'NBC News',
                'category'       => 'other',
                'published_at'   => '2025-05-23',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8935,
                'lng'            => -77.0145,
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
