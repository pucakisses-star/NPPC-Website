<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds miscellaneous free-speech / protest arrest cases to the dashboard as
 * DashboardLink markers (map pins + newswire). Each is an arrest or charge over
 * expressive conduct — a joke, a post, sidewalk chalk, a protest. Sourced from
 * public reporting; matched on URL so the command is idempotent and re-runnable.
 */
class AddSpeechArrestDashboardCases extends Command {
    protected $signature = 'dashboard:add-speech-cases';
    protected $description = 'Add miscellaneous free-speech / protest arrest cases to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'FIU student Gabriela Saldana charged with a felony "written threat" over a WhatsApp joke about Netanyahu',
                'url'            => 'https://wsvn.com/news/local/miami-dade/fiu-student-arrested-for-wanting-israels-netanyahu-to-drop-bombs-on-school-event-arena-police-say/',
                'source'         => 'WSVN',
                'category'       => 'arrest',
                'published_at'   => '2026-04-16',
                'location_label' => 'Miami, FL',
                'lat'            => 25.7563,
                'lng'            => -80.3736,
            ],
            [
                'title'          => 'Protesters arrested (charges later dropped) for chalking the rainbow back onto Orlando\'s Pulse memorial crosswalk',
                'url'            => 'https://www.orlandoweekly.com/news/pulse/state-attorney-drops-charges-for-pulse-crosswalk-arrests/',
                'source'         => 'Orlando Weekly',
                'category'       => 'protest',
                'published_at'   => '2025-08-31',
                'location_label' => 'Orlando, FL',
                'lat'            => 28.5306,
                'lng'            => -81.3766,
            ],
            [
                'title'          => 'Julian Pecora Cardenas charged with conspiracy for livestreaming a Border Patrol convoy in San Pedro, CA',
                'url'            => 'https://www.pbs.org/wgbh/frontline/article/caught-in-crackdown-ice-cbp-immigration-protests-arrests-convictions/',
                'source'         => 'PBS FRONTLINE',
                'category'       => 'arrest',
                'published_at'   => '2025-07-05',
                'location_label' => 'San Pedro, CA',
                'lat'            => 33.7361,
                'lng'            => -118.2922,
            ],
            [
                'title'          => 'Army veteran Jay Carey arrested for burning a U.S. flag in protest near the White House; charges later dropped',
                'url'            => 'https://www.nbcnews.com/politics/justice-department/drops-case-veteran-carey-arrested-burning-american-flag-white-house-rcna263438',
                'source'         => 'NBC News',
                'category'       => 'protest',
                'published_at'   => '2025-08-25',
                'location_label' => 'Washington, DC',
                'lat'            => 38.9000,
                'lng'            => -77.0365,
            ],
            [
                'title'          => 'SEIU California president David Huerta arrested and charged over an LA anti-ICE protest (felony later reduced to a misdemeanor)',
                'url'            => 'https://www.cbsnews.com/news/david-huerta-california-labor-felony-charge-immigration-protest-reduced/',
                'source'         => 'CBS News',
                'category'       => 'arrest',
                'published_at'   => '2025-06-06',
                'location_label' => 'Los Angeles, CA',
                'lat'            => 34.0522,
                'lng'            => -118.2437,
            ],
            [
                'title'          => 'Journalist Mario Guevara arrested while livestreaming an Atlanta "No Kings" protest; charges dropped, then deported by ICE',
                'url'            => 'https://www.cnn.com/2025/10/03/media/mario-guevara-journalist-deported-ice-el-salvador',
                'source'         => 'CNN',
                'category'       => 'arrest',
                'published_at'   => '2025-06-14',
                'location_label' => 'Atlanta, GA',
                'lat'            => 33.7490,
                'lng'            => -84.3880,
            ],
            [
                'title'          => 'Student journalist Lucas Griffith arrested covering an anti-ICE march in Covington, KY (felony rioting count dropped; convicted of failure to disperse)',
                'url'            => 'https://cpj.org/2025/10/ohio-journalist-convicted-after-arrest-while-covering-kentucky-protest/',
                'source'         => 'CPJ',
                'category'       => 'arrest',
                'published_at'   => '2025-07-17',
                'location_label' => 'Covington, KY',
                'lat'            => 39.0837,
                'lng'            => -84.5086,
            ],
            [
                'title'          => 'Katherine Hinds arrested over anti-Trump banners on Connecticut highway overpasses; all charges later dropped',
                'url'            => 'https://www.ctpublic.org/news/investigative/2025-10-28/charges-dropped-against-ct-woman-arrested-for-protesting-on-highway-bridges',
                'source'         => 'CT Public',
                'category'       => 'protest',
                'published_at'   => '2025-07-19',
                'location_label' => 'Hamden, CT',
                'lat'            => 41.3959,
                'lng'            => -72.8968,
            ],
            [
                'title'          => 'Arden Wells charged with felony "terrorizing" over a Facebook post critical of the Tangipahoa Parish sheriff (DA rejected all counts)',
                'url'            => 'https://www.fox8live.com/2026/02/04/tangipahoa-prosecutors-refuse-charge-man-arrested-over-facebook-post/',
                'source'         => 'FOX 8',
                'category'       => 'arrest',
                'published_at'   => '2025-07-25',
                'location_label' => 'Amite City, LA',
                'lat'            => 30.7299,
                'lng'            => -90.5126,
            ],
            [
                'title'          => 'Jeana Gamble arrested in a costume at an Alabama "No Kings" protest; acquitted of all charges',
                'url'            => 'https://reason.com/2026/04/16/62-year-old-protester-acquitted-on-all-charges-for-wearing-penis-costume/',
                'source'         => 'Reason',
                'category'       => 'protest',
                'published_at'   => '2025-10-18',
                'location_label' => 'Fairhope, AL',
                'lat'            => 30.5230,
                'lng'            => -87.9033,
            ],
            [
                'title'          => 'Navy veteran Kolton Krottinger charged with felony online impersonation over a satirical Facebook post (charge later declined)',
                'url'            => 'https://www.cbsnews.com/texas/news/hood-county-veteran-facebook-impersonation-arrest-free-speech-case/',
                'source'         => 'CBS Texas',
                'category'       => 'arrest',
                'published_at'   => '2025-11-05',
                'location_label' => 'Granbury, TX',
                'lat'            => 32.4421,
                'lng'            => -97.7942,
            ],
            [
                'title'          => 'Journalists Don Lemon and Georgia Fort arrested on federal charges over filming an anti-ICE protest at a St. Paul church',
                'url'            => 'https://www.pbs.org/newshour/politics/don-lemon-pleads-not-guilty-to-civil-rights-charges-in-anti-ice-minnesota-church-protest',
                'source'         => 'PBS NewsHour',
                'category'       => 'arrest',
                'published_at'   => '2026-01-30',
                'location_label' => 'St. Paul, MN',
                'lat'            => 44.9537,
                'lng'            => -93.0900,
            ],
            [
                'title'          => 'Three "No Kings" protesters arrested in Memphis (charges later dropped)',
                'url'            => 'https://wreg.com/news/charges-dropped-against-three-arrested-during-no-kings-protest/',
                'source'         => 'WREG',
                'category'       => 'protest',
                'published_at'   => '2026-03-28',
                'location_label' => 'Memphis, TN',
                'lat'            => 35.1495,
                'lng'            => -90.0490,
            ],
            [
                'title'          => 'NYC Council Member Chi Osse arrested while protesting a Brooklyn eviction',
                'url'            => 'https://www.thecity.nyc/2026/04/22/brooklyn-eviction-chi-osse-arrest-police-deed-theft/',
                'source'         => 'THE CITY',
                'category'       => 'protest',
                'published_at'   => '2026-04-22',
                'location_label' => 'Brooklyn, NY',
                'lat'            => 40.6782,
                'lng'            => -73.9442,
            ],
            [
                'title'          => 'Charges against the "Broadview Six" anti-ICE protesters (incl. Kat Abughazaleh) dismissed after admitted grand-jury misconduct',
                'url'            => 'https://chicago.suntimes.com/immigration/2026/05/21/broadview-ice-protest-grand-jury-transcript-kat-abughazaleh-trump',
                'source'         => 'Chicago Sun-Times',
                'category'       => 'prosecution',
                'published_at'   => '2026-05-21',
                'location_label' => 'Broadview, IL',
                'lat'            => 41.8639,
                'lng'            => -87.8534,
            ],
            [
                'title'          => 'Spokane 3 (Jac Archer, Justice Forral, Bajun Mavalwalla II) convicted of federal conspiracy for blocking an ICE detainee transfer',
                'url'            => 'https://www.spokesman.com/stories/2026/may/28/3-spokane-ice-protesters-found-guilty-in-conspirac/',
                'source'         => 'Spokesman-Review',
                'category'       => 'prosecution',
                'published_at'   => '2026-05-28',
                'location_label' => 'Spokane, WA',
                'lat'            => 47.6588,
                'lng'            => -117.4260,
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
