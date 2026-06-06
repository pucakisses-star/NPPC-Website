<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * A thematic "deep dive" sweep that adds high-profile but previously-overlooked
 * cases to the dashboard: marquee political detentions and release/ruling
 * milestones for detained activists (core to the political-prisoner mission),
 * press-freedom cases, plus labor, arms-to-Israel, trans-rights, and the LA/SF
 * June 2025 anti-ICE flashpoint. All in-window (on/after May 7, 2025) and
 * nonviolent; sourced from public reporting; matched on URL so the command is
 * idempotent.
 */
class AddDeepDiveDashboardCases extends Command {
    protected $signature = 'dashboard:add-deepdive-cases';
    protected $description = 'Add overlooked high-profile protest/detention/prosecution cases to the dashboard';

    public function handle(): int {
        $cases = [
            // ---- Marquee political detentions & case milestones ----
            [
                'title'          => 'U.S. Senator Alex Padilla was forced to the ground and handcuffed by federal agents after trying to ask Homeland Security Secretary Kristi Noem a question at a Los Angeles press conference',
                'url'            => 'https://www.nbcnews.com/politics/congress/sen-alex-padilla-forcibly-removed-dhs-sec-kristi-noems-press-conferenc-rcna212688',
                'source'         => 'NBC News',
                'category'       => 'arrest',
                'published_at'   => '2025-06-12',
                'location_label' => 'Los Angeles, CA',
                'lat'            => 34.0498,
                'lng'            => -118.4515,
            ],
            [
                'title'          => 'Columbia activist Mahmoud Khalil, a legal permanent resident detained over pro-Palestinian protest, was ordered released on bail after a judge called his detention highly unusual',
                'url'            => 'https://www.npr.org/2025/06/20/nx-s1-5440351/judge-orders-release-of-columbia-activist-mahmoud-khalil',
                'source'         => 'NPR',
                'category'       => 'prosecution',
                'published_at'   => '2025-06-20',
                'location_label' => 'Newark, NJ',
                'lat'            => 40.7357,
                'lng'            => -74.1724,
            ],
            [
                'title'          => 'Tufts PhD student Rumeysa Ozturk was ordered released from ICE detention after a judge found she was held in apparent retaliation for a pro-Palestinian op-ed',
                'url'            => 'https://www.npr.org/2025/05/09/nx-s1-5393055/tufts-student-rumeysa-ozturk-ordered-freed-from-immigration-detention',
                'source'         => 'NPR',
                'category'       => 'prosecution',
                'published_at'   => '2025-05-09',
                'location_label' => 'Somerville, MA',
                'lat'            => 42.4072,
                'lng'            => -71.1190,
            ],
            [
                'title'          => 'Georgetown scholar Badar Khan Suri was ordered released from ICE detention after a judge found no evidence to justify holding him over his pro-Palestinian ties',
                'url'            => 'https://www.pbs.org/newshour/politics/georgetown-scholar-released-from-immigration-detention-after-federal-judges-ruling',
                'source'         => 'PBS NewsHour',
                'category'       => 'prosecution',
                'published_at'   => '2025-05-14',
                'location_label' => 'Alexandria, VA',
                'lat'            => 38.8048,
                'lng'            => -77.0469,
            ],
            [
                'title'          => 'A federal judge barred ICE from arresting Columbia student Yunseo Chung, a longtime legal permanent resident targeted for deportation over her campus protest activity',
                'url'            => 'https://www.washingtonpost.com/education/2025/06/05/yunseo-chung-columbia-deportation-court/',
                'source'         => 'The Washington Post',
                'category'       => 'prosecution',
                'published_at'   => '2025-06-05',
                'location_label' => 'New York, NY',
                'lat'            => 40.7140,
                'lng'            => -74.0060,
            ],
            [
                'title'          => 'A federal appeals court let Columbia student Mohsen Mahdawi remain free, rejecting the government bid to re-detain the pro-Palestinian organizer',
                'url'            => 'https://www.cbsnews.com/news/mohsen-mahdawi-can-remain-free-from-custody-appeals-court-rules-trump-administration-detained/',
                'source'         => 'CBS News',
                'category'       => 'prosecution',
                'published_at'   => '2025-05-09',
                'location_label' => 'Burlington, VT',
                'lat'            => 44.4759,
                'lng'            => -73.2121,
            ],
            [
                'title'          => 'Palestinian activist Leqaa Kordia was released after a year in ICE detention tied to a Columbia-area protest, with the immigration judge citing very little government evidence',
                'url'            => 'https://abc7ny.com/post/leqaa-kordia-columbia-protester-released-ice-detention-year-custody-following-trump-campus-crackdown/18722300/',
                'source'         => 'ABC7 New York',
                'category'       => 'prosecution',
                'published_at'   => '2026-03-17',
                'location_label' => 'Alvarado, TX',
                'lat'            => 32.4060,
                'lng'            => -97.2120,
            ],
            [
                'title'          => 'Milwaukee County Judge Hannah Dugan was convicted of felony obstruction for helping an undocumented man avoid ICE agents at her courthouse',
                'url'            => 'https://wisconsinexaminer.com/2025/12/18/federal-obstruction-case-against-judge-hannah-dugan-goes-to-the-jury/',
                'source'         => 'Wisconsin Examiner',
                'category'       => 'prosecution',
                'published_at'   => '2025-12-18',
                'location_label' => 'Milwaukee, WI',
                'lat'            => 43.0389,
                'lng'            => -87.9065,
            ],
            // ---- Press freedom ----
            [
                'title'          => 'Australian TV reporter Lauren Tomasi was shot with a less-lethal round live on air while covering the Los Angeles anti-ICE protests',
                'url'            => 'https://www.cbsnews.com/news/reporter-los-angeles-protests-rubber-bullet-lauren-tomasi-9news-australia/',
                'source'         => 'CBS News',
                'category'       => 'other',
                'published_at'   => '2025-06-08',
                'location_label' => 'Los Angeles, CA',
                'lat'            => 34.0540,
                'lng'            => -118.2390,
            ],
            [
                'title'          => 'CNN correspondent Jason Carroll was detained live on air and two of his crew were arrested while covering the Los Angeles anti-ICE protests',
                'url'            => 'https://thehill.com/homenews/media/5341889-cnn-reporter-detained-during-protests/',
                'source'         => 'The Hill',
                'category'       => 'arrest',
                'published_at'   => '2025-06-09',
                'location_label' => 'Los Angeles, CA',
                'lat'            => 34.0510,
                'lng'            => -118.2450,
            ],
            [
                'title'          => 'Journalist Steve Held was tackled by federal agents and detained for six hours while filming, in press gear, at the Broadview ICE facility outside Chicago',
                'url'            => 'https://pressfreedomtracker.us/all-incidents/journalist-tackled-arrested-by-federal-agents-at-illinois-ice-protest/',
                'source'         => 'U.S. Press Freedom Tracker',
                'category'       => 'arrest',
                'published_at'   => '2025-09-27',
                'location_label' => 'Broadview, IL',
                'lat'            => 41.8545,
                'lng'            => -87.8555,
            ],
            [
                'title'          => 'Freelance photojournalist Alexa Wilkinson was charged with a felony hate crime over her coverage of a New York protest; the Manhattan DA later dropped the case',
                'url'            => 'https://hyperallergic.com/manhattan-da-drops-charges-against-photographer-alexa-wilkinson/',
                'source'         => 'Hyperallergic',
                'category'       => 'prosecution',
                'published_at'   => '2025-09-28',
                'location_label' => 'New York, NY',
                'lat'            => 40.7560,
                'lng'            => -73.9903,
            ],
            // ---- Labor ----
            [
                'title'          => 'Ten teachers and supporters arrested for disorderly conduct in an AFT sit-in blocking the Connecticut governor office over school funding; they chanted "Fund our schools!"',
                'url'            => 'https://ctmirror.org/2025/05/21/teachers-arrested-capitol-education-funding/',
                'source'         => 'The Connecticut Mirror',
                'category'       => 'arrest',
                'published_at'   => '2025-05-21',
                'location_label' => 'Hartford, CT',
                'lat'            => 41.7637,
                'lng'            => -72.6851,
            ],
            // ---- Arms to Israel ----
            [
                'title'          => 'About 138 arrested for trespassing as Jewish Voice for Peace occupied the San Francisco office of Senator Alex Padilla, demanding he support a block on U.S. arms sales to Israel',
                'url'            => 'https://jweekly.com/2025/08/28/138-arrested-at-sf-protest-of-sen-padillas-votes-on-israel/',
                'source'         => 'J. The Jewish News of Northern California',
                'category'       => 'arrest',
                'published_at'   => '2025-08-27',
                'location_label' => 'San Francisco, CA',
                'lat'            => 37.7905,
                'lng'            => -122.4010,
            ],
            // ---- Trans rights ----
            [
                'title'          => 'Nine trans-rights activists with the Gender Liberation Movement arrested at the U.S. Supreme Court after the Skrmetti ruling upholding a ban on gender-affirming care for minors',
                'url'            => 'https://www.washingtonblade.com/2025/06/20/nine-trans-activists-arrested-outside-supreme-court/',
                'source'         => 'Washington Blade',
                'category'       => 'arrest',
                'published_at'   => '2025-06-20',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8906,
                'lng'            => -77.0044,
            ],
            // ---- LA/SF June 2025 anti-ICE flashpoint ----
            [
                'title'          => 'About 155 arrested for failure to disperse outside the San Francisco ICE building during a second night of anti-ICE protests; chants of "Whose streets? Our streets!"',
                'url'            => 'https://www.kqed.org/news/12043255/sf-protesters-denounce-ice-raids-and-trumps-national-guard-deployment-to-la',
                'source'         => 'KQED',
                'category'       => 'arrest',
                'published_at'   => '2025-06-08',
                'location_label' => 'San Francisco, CA',
                'lat'            => 37.7950,
                'lng'            => -122.4020,
            ],
            [
                'title'          => 'University of Alabama PhD student Alireza Doroudi, detained in the campus pro-Palestinian crackdown, agreed to self-deport to Iran after 68 days in ICE detention',
                'url'            => 'https://alabamareflector.com/2025/05/09/attorney-university-of-alabama-student-detained-by-ice-chooses-to-return-to-iran/',
                'source'         => 'Alabama Reflector',
                'category'       => 'arrest',
                'published_at'   => '2025-05-08',
                'location_label' => 'Tuscaloosa, AL',
                'lat'            => 33.2098,
                'lng'            => -87.5692,
            ],
            [
                'title'          => 'A federal judge ordered the release of Indonesian student Aditya Harsono, finding ICE detained him in retaliation for his "Free Palestine" and Black Lives Matter social-media posts',
                'url'            => 'https://www.mprnews.org/story/2025/05/15/aditya-harsono-released-after-nearly-two-months-in-ice-custody',
                'source'         => 'MPR News',
                'category'       => 'arrest',
                'published_at'   => '2025-05-14',
                'location_label' => 'Marshall, MN',
                'lat'            => 44.4469,
                'lng'            => -95.7883,
            ],
            [
                'title'          => 'A federal judge ruled the administration ideological-deportation policy, which detained and deported noncitizen students and faculty over pro-Palestinian speech, violated the First Amendment',
                'url'            => 'https://www.aaup.org/news/court-rules-aaup-v-rubio-trump-admin-violated-first-amendment',
                'source'         => 'AAUP',
                'category'       => 'other',
                'published_at'   => '2025-09-30',
                'location_label' => 'Boston, MA',
                'lat'            => 42.3601,
                'lng'            => -71.0589,
            ],
            [
                'title'          => 'Minneapolis man Kyle Wagner was charged federally with cyberstalking and threatening communications after self-identifying with antifa; the DOJ and White House publicly labeled him a domestic terrorist',
                'url'            => 'https://www.pbs.org/newshour/nation/minneapolis-man-charged-with-threatening-cyberstalking-ice-officers',
                'source'         => 'PBS NewsHour',
                'category'       => 'prosecution',
                'published_at'   => '2026-02-05',
                'location_label' => 'Minneapolis, MN',
                'lat'            => 44.9778,
                'lng'            => -93.2650,
            ],
            // ---- Wrongful-deportation / due-process & speech-detention cases ----
            [
                'title'          => 'Kilmar Abrego Garcia, wrongly deported to a Salvadoran prison in an admitted error, was returned to the U.S. and indicted on smuggling charges; a judge dismissed the case in May 2026 as a vindictive prosecution',
                'url'            => 'https://www.npr.org/2026/05/22/nx-s1-5831958/federal-judge-dismisses-criminal-charges-against-kilmar-abrego-garcia',
                'source'         => 'NPR',
                'category'       => 'prosecution',
                'published_at'   => '2026-05-22',
                'location_label' => 'Greenbelt, MD',
                'lat'            => 38.9963,
                'lng'            => -76.8900,
            ],
            [
                'title'          => 'Andry Hernandez Romero, a gay Venezuelan makeup artist deported to a Salvadoran mega-prison on the basis of his tattoos without being charged, was freed after 125 days in a July 2025 prisoner exchange',
                'url'            => 'https://roughdraftatlanta.com/2025/07/22/venezuelan-makeup-artist-released/',
                'source'         => 'Rough Draft Atlanta',
                'category'       => 'other',
                'published_at'   => '2025-07-23',
                'location_label' => 'San Diego, CA',
                'lat'            => 32.5421,
                'lng'            => -117.0300,
            ],
            [
                'title'          => 'Harvard researcher Kseniia Petrova, a Russian scientist who fled after protesting the Ukraine invasion, was criminally charged over an undeclared frog-embryo sample; a judge later ruled the visa revocation unlawful',
                'url'            => 'https://www.washingtonpost.com/nation/2025/06/12/harvard-researcher-kseniia-petrova-released/',
                'source'         => 'The Washington Post',
                'category'       => 'prosecution',
                'published_at'   => '2025-06-25',
                'location_label' => 'Boston, MA',
                'lat'            => 42.3601,
                'lng'            => -71.0589,
            ],
            [
                'title'          => 'Australian writer Alistair Kitchen was detained, searched, and denied U.S. entry at Los Angeles after being questioned about his reporting on the Columbia University protests and his views on Gaza',
                'url'            => 'https://cpj.org/2025/06/australian-writer-questioned-deported-from-us-after-report-on-pro-palestinian-protests/',
                'source'         => 'CPJ',
                'category'       => 'other',
                'published_at'   => '2025-06-12',
                'location_label' => 'Los Angeles, CA',
                'lat'            => 33.9416,
                'lng'            => -118.4085,
            ],
            [
                'title'          => 'Cincinnati imam and hospital chaplain Ayman Soliman was detained by ICE for 73 days after his asylum was revoked on a terrorism allegation that DHS later dropped as a mistake',
                'url'            => 'https://ohiocapitaljournal.com/2025/09/26/ice-releases-ohio-hospital-chaplain-case-for-detention-unclear/',
                'source'         => 'Ohio Capital Journal',
                'category'       => 'other',
                'published_at'   => '2025-07-09',
                'location_label' => 'Cincinnati, OH',
                'lat'            => 39.1031,
                'lng'            => -84.5120,
            ],
            // ---- May Day 2026 anti-ICE mass arrests ----
            [
                'title'          => '25 people, including a California state senator and San Francisco supervisors, were arrested for a roadway blockade of San Francisco International Airport during the May Day 2026 anti-ICE protests',
                'url'            => 'https://missionlocal.org/2026/05/s-f-supervisors-past-and-present-arrested-at-sfo-anti-ice-protest/',
                'source'         => 'Mission Local',
                'category'       => 'arrest',
                'published_at'   => '2026-05-01',
                'location_label' => 'San Francisco, CA',
                'lat'            => 37.6213,
                'lng'            => -122.3790,
            ],
            [
                'title'          => 'Congressional candidate Chuck Park was among those arrested by the NYPD at a Sunrise Movement protest near the New York Stock Exchange on May Day 2026',
                'url'            => 'https://time.com/article/2026/05/01/may-day-international-workers-day-protests-rallies-marches-us/',
                'source'         => 'TIME',
                'category'       => 'arrest',
                'published_at'   => '2026-05-01',
                'location_label' => 'New York, NY',
                'lat'            => 40.7069,
                'lng'            => -74.0113,
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
