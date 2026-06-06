<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds protest-related cases that were initially set aside on the nonviolence
 * bar (allegations of assault, thrown objects, weapons, arson, threats, or
 * doxxing). They are included here at the user's request and stated FACTUALLY
 * and NEUTRALLY: each title gives the actual charge and, where known, the
 * CURRENT disposition (many were dismissed, dropped, or ended in acquittal).
 * Presumption of innocence applies to all pending matters; convictions are
 * stated plainly without valorizing the underlying conduct. In-window
 * (on/after May 7, 2025); sourced from public reporting; matched on URL so the
 * command is idempotent.
 */
class AddFlaggedDashboardCases extends Command {
    protected $signature = 'dashboard:add-flagged-cases';
    protected $description = 'Add violence/threats-flagged protest cases (stated factually, with current dispositions)';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Three Los Angeles activists were charged with stalking a federal agent for following him home and livestreaming while posting his address; two were convicted of stalking and a third was acquitted',
                'url'            => 'https://ktla.com/news/local-news/2-women-found-guilty-of-stalking-ice-officer-home-in-l-a-livestreaming-pursuit/',
                'source'         => 'KTLA',
                'category'       => 'prosecution',
                'published_at'   => '2025-09-26',
                'location_label' => 'Los Angeles, CA',
                'lat'            => 34.0500,
                'lng'            => -118.2500,
            ],
            [
                'title'          => 'San Diego man Gregory Curcio was arrested on a federal doxxing charge for posting an ICE attorney home address online and urging others to harass her; the case is pending',
                'url'            => 'https://www.ice.gov/news/releases/california-man-accused-doxxing-ice-employee-now-custody',
                'source'         => 'ICE',
                'category'       => 'prosecution',
                'published_at'   => '2025-09-22',
                'location_label' => 'San Diego, CA',
                'lat'            => 32.7157,
                'lng'            => -117.1611,
            ],
            [
                'title'          => 'Eight people were arrested on assault and obstruction charges at an anti-ICE protest at the Henry M. Jackson Federal Building in Seattle, where police cited thrown objects, a dumpster fire, and flag-burning',
                'url'            => 'https://www.fox13seattle.com/news/arrests-seattle-anti-ice-protest',
                'source'         => 'FOX 13 Seattle',
                'category'       => 'arrest',
                'published_at'   => '2025-06-11',
                'location_label' => 'Seattle, WA',
                'lat'            => 47.6046,
                'lng'            => -122.3349,
            ],
            [
                'title'          => 'Thirty-four people were arrested (86 taken into custody) at an anti-ICE protest near 26 Federal Plaza in Manhattan, where police reported bottles and objects thrown',
                'url'            => 'https://www.thecity.nyc/2025/06/10/ice-protests-arrests-nypd-trump-immigration/',
                'source'         => 'THE CITY',
                'category'       => 'arrest',
                'published_at'   => '2025-06-10',
                'location_label' => 'New York, NY',
                'lat'            => 40.7160,
                'lng'            => -74.0035,
            ],
            [
                'title'          => 'Four people were arrested after a protest against an ICE raid at Glenn Valley Foods in Omaha; one, a US-citizen plant worker, was charged with assaulting officers and breaking a vehicle window with a rock',
                'url'            => 'https://omaha.com/news/local/crime-courts/article_2a0733ed-8b2d-4b4d-b01d-36156d0841c5.html',
                'source'         => 'Omaha World-Herald',
                'category'       => 'arrest',
                'published_at'   => '2025-06-10',
                'location_label' => 'Omaha, NE',
                'lat'            => 41.2565,
                'lng'            => -95.9345,
            ],
            [
                'title'          => 'About six people were arrested on charges of assaulting officers during multi-night clashes outside the Delaney Hall ICE facility in Newark, where some used umbrellas and trash cans as shields',
                'url'            => 'https://abc7ny.com/post/delaney-hall-protests-6-arrests-protesters-clash-ice-agents-outside-newark-nj/19192526/',
                'source'         => 'ABC7 New York',
                'category'       => 'arrest',
                'published_at'   => '2026-05-27',
                'location_label' => 'Newark, NJ',
                'lat'            => 40.7290,
                'lng'            => -74.2095,
            ],
            [
                'title'          => 'Six people were arrested at an anti-ICE protest on Buford Highway in Brookhaven, Georgia on obstruction and unlawful-assembly charges, with one also charged with aggravated assault on an officer',
                'url'            => 'https://www.atlantanewsfirst.com/2025/06/11/brookhaven-police-identify-suspects-arrested-during-anti-ice-protest-buford-highway/',
                'source'         => 'Atlanta News First',
                'category'       => 'arrest',
                'published_at'   => '2025-06-10',
                'location_label' => 'Brookhaven, GA',
                'lat'            => 33.8651,
                'lng'            => -84.3366,
            ],
            [
                'title'          => 'About 74 people were arrested for failure to disperse at a "No Kings" protest near a downtown Los Angeles federal detention center; DHS said some threw concrete and injured officers',
                'url'            => 'https://abc7.com/post/no-kings-protest-los-angeles-2026-police-say-9-juveniles-arrested-officers-suffered-minor-during-saturdays-rally-downtown/18801910/',
                'source'         => 'ABC7 Los Angeles',
                'category'       => 'arrest',
                'published_at'   => '2026-03-28',
                'location_label' => 'Los Angeles, CA',
                'lat'            => 34.0440,
                'lng'            => -118.2400,
            ],
            [
                'title'          => 'Five Quakertown, Pennsylvania high-school students were charged after an anti-ICE walkout; the most serious felony assault counts were later dismissed or reduced',
                'url'            => 'https://www.nbcphiladelphia.com/news/local/most-serious-charges-axed-for-2nd-teen-involved-in-quakertown-anti-ice-protest/4380878/',
                'source'         => 'NBC Philadelphia',
                'category'       => 'prosecution',
                'published_at'   => '2026-02-20',
                'location_label' => 'Quakertown, PA',
                'lat'            => 40.4418,
                'lng'            => -75.3413,
            ],
            [
                'title'          => 'Detroit man Roman Gomez-Ocadiz was federally charged with impeding officers for using his truck to block an ICE vehicle during an arrest; he was released on bond pending the case',
                'url'            => 'https://www.detroitnews.com/story/news/local/detroit-city/2025/07/01/feds-charge-protester-accused-of-interferring-with-detroit-ice-arrest/84432260007/',
                'source'         => 'The Detroit News',
                'category'       => 'arrest',
                'published_at'   => '2025-06-30',
                'location_label' => 'Detroit, MI',
                'lat'            => 42.3690,
                'lng'            => -83.1530,
            ],
            [
                'title'          => 'Virginia Beach woman Jessica Pinsky was charged with disorderly conduct for kicking a "Trump Train" pickup at a "No Kings" rally; the case is pending',
                'url'            => 'https://www.wtkr.com/news/in-the-community/virginia-beach/woman-arrested-at-vb-no-kings-rally-in-town-center-for-disorderly-conduct',
                'source'         => 'WTKR',
                'category'       => 'arrest',
                'published_at'   => '2026-03-28',
                'location_label' => 'Virginia Beach, VA',
                'lat'            => 36.8420,
                'lng'            => -76.1370,
            ],
            [
                'title'          => 'Chicago teacher Marimar Martinez was shot five times by a Border Patrol agent and charged with ramming his vehicle; prosecutors later dropped the charges after conceding the account was contradicted',
                'url'            => 'https://www.cnn.com/2025/11/20/us/chicago-marimar-martinez-shooting-charges',
                'source'         => 'CNN',
                'category'       => 'prosecution',
                'published_at'   => '2025-10-04',
                'location_label' => 'Chicago, IL',
                'lat'            => 41.8230,
                'lng'            => -87.7050,
            ],
            [
                'title'          => 'Conservative journalist Nick Sortor was arrested on a disorderly-conduct charge at the Portland ICE-facility protests; the district attorney dropped it, finding he appeared to have acted in self-defense',
                'url'            => 'https://www.cnn.com/2025/10/03/us/portland-ice-facility-nick-sortor-arrest',
                'source'         => 'CNN',
                'category'       => 'arrest',
                'published_at'   => '2025-10-02',
                'location_label' => 'Portland, OR',
                'lat'            => 45.4985,
                'lng'            => -122.6715,
            ],
            [
                'title'          => 'Nine people were convicted on terrorism-related charges over a July 4, 2025 ambush at the Prairieland ICE detention center in Alvarado, Texas, in which an officer was shot; others pleaded guilty earlier',
                'url'            => 'https://www.cnn.com/2026/03/13/us/immigration-detention-center-shooting',
                'source'         => 'CNN',
                'category'       => 'prosecution',
                'published_at'   => '2025-07-04',
                'location_label' => 'Alvarado, TX',
                'lat'            => 32.4060,
                'lng'            => -97.2120,
            ],
            [
                'title'          => 'A "No Kings" event safety volunteer, Matthew Alder, was charged with manslaughter after fatally shooting bystander Arthur Ah Loo while firing at a man with a rifle at the Salt Lake City march; the case is pending',
                'url'            => 'https://www.nbcnews.com/news/us-news/safety-volunteer-charged-manslaughter-shooting-salt-lake-city-no-kings-rcna247333',
                'source'         => 'NBC News',
                'category'       => 'prosecution',
                'published_at'   => '2025-06-14',
                'location_label' => 'Salt Lake City, UT',
                'lat'            => 40.7608,
                'lng'            => -111.8910,
            ],
            [
                'title'          => 'Trenten Barker was sentenced to 18 months in federal prison after pleading guilty to arson for igniting a fire at the Portland ICE facility',
                'url'            => 'https://www.justice.gov/usao-or/pr/portland-man-pleads-guilty-arson-immigration-and-customs-enforcement-building',
                'source'         => 'U.S. Attorney (D. Oregon)',
                'category'       => 'prosecution',
                'published_at'   => '2025-06-11',
                'location_label' => 'Portland, OR',
                'lat'            => 45.4980,
                'lng'            => -122.6710,
            ],
            [
                'title'          => 'A pro-Palestinian protester arrested at the Elbit Systems weapons plant in Ladson, South Carolina was acquitted of charges that he damaged an employee vehicle with a flagpole',
                'url'            => 'https://fightbacknews.org/articles/charleston-pro-palestine-organizer-wins-not-guilty-verdict',
                'source'         => 'Fight Back News',
                'category'       => 'prosecution',
                'published_at'   => '2025-08-07',
                'location_label' => 'Ladson, SC',
                'lat'            => 32.9857,
                'lng'            => -80.1098,
            ],
            [
                'title'          => 'Two of the nine Spokane ICE-protest defendants, Bobbi Silva and Mikki Hatfield, were charged with assaulting officers; under plea deals they pleaded guilty to conspiracy and the assault charges are set to be dismissed',
                'url'            => 'https://www.spokesman.com/stories/2025/dec/09/five-of-nine-spokane-ice-protesters-have-pleaded-g/',
                'source'         => 'The Spokesman-Review',
                'category'       => 'prosecution',
                'published_at'   => '2025-06-11',
                'location_label' => 'Spokane, WA',
                'lat'            => 47.6635,
                'lng'            => -117.4280,
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
