<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;

/**
 * Adds a batch of 27 news articles to the /dashboard Live Tracker as curated
 * DashboardLinks (newswire + ticker, and map markers where coordinates are
 * known). Each is upserted by URL, so the command is idempotent and safe to
 * re-run. Titles, sources, dates, categories and locations were researched from
 * each article; two New York Times items (Beto Coral; the Minnesota isolation
 * piece) block automated access, so their titles are accurate descriptive
 * summaries rather than the verbatim headline (flagged inline).
 */
final class AddNews2026DashboardBatch extends Command
{
    protected $signature = 'dashboard:add-2026-news-batch';

    protected $description = 'Add the batch of 2026 protest/arrest/prosecution news articles to the dashboard';

    /** @var list<array{url:string,title:string,source:string,published_at:string,category:string,location_label:?string,lat:?float,lng:?float}> */
    private const LINKS = [
        ['url' => 'https://www.sgvtribune.com/2026/02/02/el-monte-activist-alleges-ice-officers-broke-her-car-window-pointed-weapons-at-her/', 'title' => 'El Monte activist alleges ICE officers broke her car window, pointed weapons at her', 'source' => 'San Gabriel Valley Tribune', 'published_at' => '2026-02-02 12:00:00', 'category' => 'other', 'location_label' => 'El Monte, CA', 'lat' => 34.0686, 'lng' => -118.0276],
        ['url' => 'https://www.missourinet.com/2026/06/17/protest-summit-highlight-divide-over-data-centers-in-missouri/', 'title' => 'Protest, summit highlight divide over data centers in Missouri', 'source' => 'Missourinet', 'published_at' => '2026-06-17 12:00:00', 'category' => 'protest', 'location_label' => 'Jefferson City, MO', 'lat' => 38.5767, 'lng' => -92.1735],
        ['url' => 'https://www.washingtonblade.com/2026/03/05/13-hiv-aids-activists-arrested-on-capitol-hill/', 'title' => '13 HIV/AIDS activists arrested on Capitol Hill', 'source' => 'Washington Blade', 'published_at' => '2026-03-05 12:00:00', 'category' => 'arrest', 'location_label' => 'Capitol Hill, Washington, DC', 'lat' => 38.8866, 'lng' => -77.0097],
        ['url' => 'https://www.reuters.com/legal/government/civil-rights-activists-arrested-over-minnesota-church-protest-2026-01-22/', 'title' => 'Civil rights activists arrested over Minnesota church protest', 'source' => 'Reuters', 'published_at' => '2026-01-22 12:00:00', 'category' => 'arrest', 'location_label' => 'St. Paul, MN', 'lat' => 44.9537, 'lng' => -93.0900],
        ['url' => 'https://www.npr.org/2026/01/16/g-s1-106374/immigration-peaceful-protesters', 'title' => "Judge rules immigration officers in Minneapolis can't detain peaceful protesters", 'source' => 'NPR', 'published_at' => '2026-01-16 23:57:59', 'category' => 'prosecution', 'location_label' => 'Minneapolis, MN', 'lat' => 44.9778, 'lng' => -93.2650],
        ['url' => 'https://abcnews.com/US/4-arrested-after-suspicious-device-thrown-protest-nyc/story?id=130863389', 'title' => "Improvised explosive device was thrown during dueling protests outside NYC mayor's home: Police", 'source' => 'ABC News', 'published_at' => '2026-03-08 16:53:00', 'category' => 'arrest', 'location_label' => 'Gracie Mansion, New York, NY', 'lat' => 40.7766, 'lng' => -73.9426],
        ['url' => 'https://www.theguardian.com/us-news/2026/jan/23/st-paul-ice-protest-women-released', 'title' => 'Women arrested over anti-ICE church protest in St Paul freed from detention', 'source' => 'The Guardian', 'published_at' => '2026-01-24 01:20:09', 'category' => 'arrest', 'location_label' => 'St Paul, MN', 'lat' => 44.9537, 'lng' => -93.0900],
        ['url' => 'https://vnews.com/2026/02/11/williston-ice-protest-arrests/', 'title' => 'Upper Valley activists arrested in protest against ICE data center', 'source' => 'Valley News', 'published_at' => '2026-02-11 12:00:00', 'category' => 'arrest', 'location_label' => 'Williston, VT', 'lat' => 44.4469, 'lng' => -73.1126],
        ['url' => 'https://www.theguardian.com/us-news/2026/jan/08/venezuela-protester-arrested', 'title' => 'US protester arrested after TV interview says she was targeted due to Venezuela trip', 'source' => 'The Guardian', 'published_at' => '2026-01-08 12:00:00', 'category' => 'arrest', 'location_label' => null, 'lat' => null, 'lng' => null],
        ['url' => 'https://www.democracynow.org/2026/5/26/headlines/new_jersey_police_arrest_10_activists_attempting_to_stop_ammunition_shipment_to_israel', 'title' => 'New Jersey Police Arrest 10 Activists Attempting to Stop Ammunition Shipment to Israel', 'source' => 'Democracy Now!', 'published_at' => '2026-05-26 12:00:00', 'category' => 'arrest', 'location_label' => 'Elizabeth, NJ', 'lat' => 40.6639, 'lng' => -74.2107],
        ['url' => 'https://www.daylightsandiego.org/san-diego-activist-sentenced-to-45-days-home-arrest-after-pleading-guilty-to-misdemeanor-assault-of-immigration-official/', 'title' => 'San Diego activist sentenced to 45 days home arrest after pleading guilty to misdemeanor assault of immigration official', 'source' => 'Daylight San Diego', 'published_at' => '2026-03-05 12:00:00', 'category' => 'prosecution', 'location_label' => 'San Diego, CA', 'lat' => 32.7157, 'lng' => -117.1611],
        // NYT blocks automated access; title is an accurate descriptive summary, not the verbatim headline.
        ['url' => 'https://www.nytimes.com/2026/06/19/us/rubio-beto-coral-colombia.html', 'title' => 'Rubio approved a memo to detain Colombian activist Beto Coral, New York Times reports', 'source' => 'The New York Times', 'published_at' => '2026-06-19 12:00:00', 'category' => 'arrest', 'location_label' => null, 'lat' => null, 'lng' => null],
        ['url' => 'https://nypost.com/2026/06/25/us-news/youtube-activist-long-island-auditor-arrested-after-video-stunt-at-pba-headquarters-union/', 'title' => "YouTube activist Long Island Auditor arrested after 'threatening' video stunt at PBA headquarters: union", 'source' => 'New York Post', 'published_at' => '2026-06-25 12:00:00', 'category' => 'arrest', 'location_label' => 'Brentwood, NY', 'lat' => 40.7812, 'lng' => -73.2462],
        ['url' => 'https://www.reuters.com/world/court-wont-revisit-ruling-opening-door-pro-palestinian-activist-mahmoud-khalils-2026-05-22/', 'title' => "Court won't revisit ruling opening door to pro-Palestinian activist Mahmoud Khalil's rearrest", 'source' => 'Reuters', 'published_at' => '2026-05-22 12:00:00', 'category' => 'prosecution', 'location_label' => 'U.S. Court of Appeals (3rd Cir.), Philadelphia, PA', 'lat' => 39.9526, 'lng' => -75.1652],
        ['url' => 'https://www.michigandaily.com/news/news-briefs/four-pro-palestine-activists-released-on-bond-after-fbi-arrests/', 'title' => 'Four pro-Palestine activists released on bond after FBI arrests', 'source' => 'The Michigan Daily', 'published_at' => '2026-06-14 12:00:00', 'category' => 'arrest', 'location_label' => 'Detroit, MI', 'lat' => 42.3314, 'lng' => -83.0458],
        ['url' => 'https://www.independent.com/2026/06/04/case-dismissed-against-santa-barbara-activist-arrested-for-slashing-ice-tire/', 'title' => 'Case Dismissed Against Santa Barbara Activist Arrested for Slashing ICE Tire', 'source' => 'The Santa Barbara Independent', 'published_at' => '2026-06-04 10:14:00', 'category' => 'prosecution', 'location_label' => 'Santa Barbara, CA', 'lat' => 34.4208, 'lng' => -119.6982],
        // NYT blocks automated access; title is an accurate descriptive summary, not the verbatim headline.
        ['url' => 'https://www.nytimes.com/2026/06/18/us/minnesota-protester-isolation-trump-immigration-crackdown.html', 'title' => "A Minnesota protester held in isolation amid Trump's immigration crackdown", 'source' => 'The New York Times', 'published_at' => '2026-06-18 12:00:00', 'category' => 'arrest', 'location_label' => 'Minnesota', 'lat' => null, 'lng' => null],
        ['url' => 'https://spectrumnews1.com/oh/columbus/sports/2026/06/03/ohio-child-marriage', 'title' => 'Ohio protesters push for bill to make child marriages illegal', 'source' => 'Spectrum News 1', 'published_at' => '2026-06-03 17:10:00', 'category' => 'protest', 'location_label' => 'Ohio Statehouse, Columbus, OH', 'lat' => 39.9612, 'lng' => -82.9988],
        ['url' => 'https://www.wistv.com/2026/06/06/protests-continue-sc-state-house-14-year-old-cyrus-carmack-belton/', 'title' => 'Protests continue at SC State House for 14-year-old Cyrus Carmack-Belton', 'source' => 'WIS News 10', 'published_at' => '2026-06-06 18:19:00', 'category' => 'protest', 'location_label' => 'South Carolina State House, Columbia, SC', 'lat' => 34.0007, 'lng' => -81.0348],
        ['url' => 'https://wach.com/news/local/dozens-rally-at-south-carolina-state-house-after-chow-acquittal-in-belton-killing', 'title' => 'State House rally reignites calls for justice after Rick Chow found not guilty', 'source' => 'WACH', 'published_at' => '2026-06-06 18:06:00', 'category' => 'protest', 'location_label' => 'South Carolina State House, Columbia, SC', 'lat' => 34.0007, 'lng' => -81.0348],
        ['url' => 'https://www.wjcl.com/article/bluffton-south-carolina-ice-operation-protest/71511073', 'title' => 'Demonstrators in Bluffton protest ICE operation, call for support of immigrant families', 'source' => 'WJCL', 'published_at' => '2026-06-06 01:40:00', 'category' => 'protest', 'location_label' => 'Bluffton, SC', 'lat' => 32.2371, 'lng' => -80.8604],
        ['url' => 'https://www.dispatch.com/picture-gallery/news/2026/06/24/house-bill-472-protest-ohio-statehouse/90683217007/', 'title' => 'Protesters rally against mail-in voting bill outside Ohio Statehouse', 'source' => 'The Columbus Dispatch', 'published_at' => '2026-06-24 17:14:57', 'category' => 'protest', 'location_label' => 'Ohio Statehouse, Columbus, OH', 'lat' => 39.9612, 'lng' => -82.9988],
        ['url' => 'https://www.wyff4.com/article/south-carolina-redistricting-protest-statehouse/71310885', 'title' => 'Protesters rally on Statehouse steps to oppose redistricting in South Carolina', 'source' => 'WYFF', 'published_at' => '2026-05-14 17:47:00', 'category' => 'protest', 'location_label' => 'South Carolina State House, Columbia, SC', 'lat' => 34.0007, 'lng' => -81.0348],
        ['url' => 'https://www.ksla.com/video/2026/05/08/protesters-shout-shut-it-down-lawmakers-tackle-louisiana-map-redraw/', 'title' => 'Protesters shout "Shut it down" as lawmakers tackle Louisiana map redraw', 'source' => 'KSLA News 12', 'published_at' => '2026-05-08 11:56:12', 'category' => 'protest', 'location_label' => 'Louisiana State Capitol, Baton Rouge, LA', 'lat' => 30.4571, 'lng' => -91.1874],
        ['url' => 'https://www.democracynow.org/2026/5/5/headlines/federal_appeals_court_blocks_ex_prisoner_who_won_election_from_taking_office_in_new_orleans', 'title' => 'Federal Appeals Court Blocks Ex-Prisoner Who Won Election from Taking Office in New Orleans', 'source' => 'Democracy Now!', 'published_at' => '2026-05-05 12:00:00', 'category' => 'prosecution', 'location_label' => 'New Orleans, LA', 'lat' => 29.9511, 'lng' => -90.0715],
        ['url' => 'https://veritenews.org/2026/04/17/ice-immigration-arrests-court-hearing/', 'title' => 'Immigration hearing arrests spark protest', 'source' => 'Verite News', 'published_at' => '2026-04-17 12:00:00', 'category' => 'protest', 'location_label' => 'New Orleans Immigration Court, New Orleans, LA', 'lat' => 29.9499, 'lng' => -90.0668],
        ['url' => 'https://www.thecentersquare.com/louisiana/article_7a07137c-57cf-4e44-8d75-17d145e2839f.html', 'title' => 'Louisiana redistricting begins with protests, tension', 'source' => 'The Center Square', 'published_at' => '2026-05-08 12:00:00', 'category' => 'protest', 'location_label' => 'Louisiana State Capitol, Baton Rouge, LA', 'lat' => 30.4571, 'lng' => -91.1874],
    ];

    public function handle(): int
    {
        $created = 0;
        $updated = 0;

        foreach (self::LINKS as $l) {
            $attributes = [
                'title' => $l['title'],
                'source' => $l['source'],
                'category' => $l['category'],
                'published_at' => $l['published_at'],
                'location_label' => $l['location_label'],
                'lat' => $l['lat'],
                'lng' => $l['lng'],
            ];

            $link = DashboardLink::where('url', $l['url'])->first();
            if ($link) {
                $link->fill($attributes)->save();
                $updated++;
                $this->info("Updated: {$link->title}");
            } else {
                $link = DashboardLink::create($attributes + ['url' => $l['url']]);
                $created++;
                $this->info("Created: {$link->title}");
            }
        }

        $this->newLine();
        $this->info('Done. Created '.$created.', updated '.$updated.', total '.count(self::LINKS).'.');

        return self::SUCCESS;
    }
}
