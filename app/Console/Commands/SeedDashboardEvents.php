<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Seeds the dashboard newswire + map with the full curated set of protest /
 * arrest events. This single command replaces the earlier one-off
 * add-*-events commands. Every source URL becomes a newswire item; one
 * representative source per event carries coordinates (the map marker). Rows
 * are keyed by URL via updateOrCreate, so it is idempotent and safe to re-run.
 *
 * Run on the server: php artisan dashboard:seed-events
 */
final class SeedDashboardEvents extends Command {
    protected $signature = 'dashboard:seed-events';
    protected $description = 'Seed the dashboard newswire + map with all curated protest-arrest events';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Claremore, OK · Rogers State University · Feb 17, 2026 (Project Mustang) ──
        ['Arrest made at heated Claremore data center meeting', 'https://www.newson6.com/tulsa-oklahoma-news/arrest-made-during-heated-claremore-meeting-over-proposed-data-center', 'News On 6', '2026-02-17', 36.3186099, -95.6361137, 'Rogers State University, Claremore, OK'],
        ['Community activist arrested at Claremore data center meeting', 'https://ktul.com/news/local/community-activist-arrested-at-data-center-meeting-in-claremore', 'KTUL', '2026-02-17', 36.3186099, -95.6361137, 'Rogers State University, Claremore, OK'],
        ['Oklahoma man arrested at AI data center meeting', 'https://www.businessinsider.com/data-center-meeting-claremore-oklahoma-man-arrested-beale-infrastructure-ai-2026-2', 'Business Insider', '2026-02-17', 36.3186099, -95.6361137, 'Rogers State University, Claremore, OK'],
        ['Oklahoma farmer jailed for trespassing at AI data center town hall', 'https://www.tomshardware.com/tech-industry/big-tech/oklahoma-farmer-arrested-and-jailed-for-trespassing-during-ai-data-center-town-hall-removed-by-officers-after-going-a-few-seconds-over-allotted-speaking-time-trying-to-hand-paperwork-to-counselors', "Tom's Hardware", '2026-02-17', 36.3186099, -95.6361137, 'Rogers State University, Claremore, OK'],

        // ── Port Washington, WI · Common Council, City Hall · Dec 2, 2025 ──
        ['Three arrested at Port Washington data center hearing', 'https://www.datacenterdynamics.com/en/news/three-arrested-at-data-center-hearing-in-port-washington-wisconsin/', 'DataCenter Dynamics', '2025-12-02', 43.3876540, -87.8710410, 'Port Washington City Hall, WI'],
        ['Arrests at Port Washington data center meeting', 'https://www.fox6now.com/news/port-washington-data-center-meeting-arrests', 'FOX6 Milwaukee', '2025-12-02', 43.3876540, -87.8710410, 'Port Washington City Hall, WI'],
        ['Three arrested at Port Washington city data center meeting', 'https://www.fox6now.com/news/port-washington-data-center-concerns-arrests-city-meeting', 'FOX6 Milwaukee', '2025-12-03', 43.3876540, -87.8710410, 'Port Washington City Hall, WI'],
        ['Arrest at Port Washington data center protest', 'https://spectrumnews1.com/wi/milwaukee/news/2025/12/07/port-washington-data-center-peaceful-protest-arrest', 'Spectrum News 1', '2025-12-02', 43.3876540, -87.8710410, 'Port Washington City Hall, WI'],
        ['Woman dragged from council meeting over data center protest', 'https://www.alternet.org/woman-violently-arrested-after-speaking-out-against-ai-data-centers/', 'AlterNet', '2025-12-02', 43.3876540, -87.8710410, 'Port Washington City Hall, WI'],

        // ── Columbus, OH · Ohio Statehouse · Jun 1, 2026 ──
        ["Blogger 'The Rooster' arrested outside Ohio Statehouse", 'https://signalohio.org/progressive-blogger-the-rooster-arrested-outside-statehouse-charged-with-harassment/', 'Signal Ohio', '2026-06-01', 39.9611755, -82.9987942, 'Ohio Statehouse, Columbus, OH'],

        // ── Dixon, IL · threats over a data center near Rock Falls · May 27, 2026 ──
        ['Illinois man arrested after threats over data center', 'https://www.datacenterdynamics.com/en/news/illinois-man-arrested-after-threatening-local-authorities-to-stop-data-center-development/', 'DataCenter Dynamics', '2026-05-27', 41.8426378, -89.4832453, 'Dixon, IL (Rock Falls data center dispute)'],
        ['Dixon data center critic arrested', 'https://www.businessinsider.com/dixon-illinois-data-center-development-critic-arrested-2026-5', 'Business Insider', '2026-05-27', 41.8426378, -89.4832453, 'Dixon, IL (Rock Falls data center dispute)'],
        ['Dixon man arrested over Rock Falls data center threats', 'https://hoodline.com/2026/05/dixon-man-busted-after-threats-over-rock-falls-data-center-site/', 'Hoodline', '2026-05-27', 41.8426378, -89.4832453, 'Dixon, IL (Rock Falls data center dispute)'],

        // ── Williston, VT · White Cap Business Park (ICE targeting center) · Feb 9, 2026 ──
        ['11 arrested during ICE protest at Williston business park', 'https://vtdigger.org/2026/02/10/11-arrested-during-ice-protest-at-williston-business-park/', 'VTDigger', '2026-02-09', 44.4607481, -73.1229347, 'White Cap Business Park, Williston, VT'],
        ['Arrests at Williston ICE surveillance center protest', 'https://vnews.com/2026/02/11/williston-ice-protest-arrests/', 'Valley News', '2026-02-09', 44.4607481, -73.1229347, 'White Cap Business Park, Williston, VT'],

        // ── El Centro, CA · Imperial County Board of Supervisors · Apr 11, 2026 ──
        ['Man arrested at Imperial County data center board meeting', 'https://www.latimes.com/california/story/2026-04-11/man-speaking-against-data-center-arrested-at-imperial-county-board-meeting-as-tensions-flare-nationwide', 'Los Angeles Times', '2026-04-11', 32.7929238, -115.5631508, 'Imperial County Admin Center, El Centro, CA'],

        // ── El Centro, CA · near El Centro Library · Apr 20, 2026 ──
        ['El Centro resident arrested for online threats over data center', 'https://www.kpbs.org/news/public-safety/2026/04/20/el-centro-resident-arrested-for-allegedly-making-online-threats-against-data-center-developer', 'KPBS', '2026-04-20', 32.7917070, -115.5556662, 'El Centro, CA (near El Centro Library)'],

        // ── Andover Township, NJ · "The Barn", 146 Lake Iliff Rd · May 7, 2026 ──
        ['NJ man arrested at town meeting over secret data center deal', 'https://thenerdstash.com/new-jersey-man-arrested-at-town-meeting-after-confronting-officials-over-secret-ai-data-center-deal-how-much-are-they-paying-you/', 'The Nerd Stash', '2026-05-07', 41.0312573, -74.7207799, 'The Barn, Andover Township, NJ'],

        // ── Hobart, IN · Police Court Complex, 705 E 4th St · May 7, 2026 ──
        ['Hobart data center meeting draws large crowd, one arrest', 'https://www.chicagotribune.com/2026/05/09/hobart-meeting-on-data-centers-brings-large-crowd-tight-security-and-one-arrest/', 'Chicago Tribune', '2026-05-07', 41.5316011, -87.2525158, 'Hobart Police Court Complex, IN'],
        ['Man removed and arrested at Indiana data center meeting', 'https://www.fox32chicago.com/news/video-shows-man-removed-arrested-indiana-data-center-meeting', 'FOX 32 Chicago', '2026-05-07', 41.5316011, -87.2525158, 'Hobart Police Court Complex, IN'],
        ['Video: man removed from Indiana data center meeting', 'https://www.fox32chicago.com/video/fmc-qtdelt547oamiybt', 'FOX 32 Chicago', '2026-05-07', 41.5316011, -87.2525158, 'Hobart Police Court Complex, IN'],

        // ── Philadelphia, PA · Delaware Valley Intelligence Center · Jun 1, 2026 ──
        ['Police tracked anti-data-center speech as extremism', 'https://theintercept.com/2026/06/01/ai-data-center-protest-police-surveillance/', 'The Intercept', '2026-06-01', 39.9105823, -75.2219422, 'Delaware Valley Intelligence Center, Philadelphia, PA'],
        // ── Climate · New York State Capitol, Albany NY · Apr 21, 2026 (+ Mar 25) ──
        ['18 arrested at NY Capitol over climate-law rollback', 'https://capitalregion.iheart.com/content/2026-04-22-protesters-arrested-at-new-york-state-capitol-over-climate-law/', 'iHeart Capital Region', '2026-04-21', 42.6525913, -73.7573712, 'New York State Capitol, Albany, NY'],
        ['Climate advocates arrested at New York State Capitol', 'https://www.wamc.org/news/2026-03-25/climate-advocates-arrested-at-new-york-state-capitol', 'WAMC', '2026-03-25', 42.6525913, -73.7573712, 'New York State Capitol, Albany, NY'],
        ['21 arrested as hundreds swarm Capitol over climate law', 'https://www.news10.com/capitol/climate-law-protests-arrests/', 'News10', '2026-03-25', 42.6525913, -73.7573712, 'New York State Capitol, Albany, NY'],

        // ── Housing · 212 Jefferson Ave, Bedford-Stuyvesant, Brooklyn · Apr 22, 2026 ──
        ['Council member Chi Ossé arrested at Brooklyn eviction protest', 'https://ny1.com/nyc/brooklyn/news/2026/04/22/councilmember-chi-oss--arrested-during-eviction-dispute-in-brooklyn', 'NY1', '2026-04-22', 40.6831871, -73.9491010, '212 Jefferson Ave, Bedford-Stuyvesant, Brooklyn'],
        ['NYC councilmember released after violent arrest at anti-eviction protest', 'https://www.democracynow.org/2026/4/23/headlines/nyc_councilmember_chi_osse_released_after_violent_arrest_at_anti_eviction_protest', 'Democracy Now!', '2026-04-22', 40.6831871, -73.9491010, '212 Jefferson Ave, Bedford-Stuyvesant, Brooklyn'],

        // ── Anti-war · Hart Senate Office Building, Washington DC · Mar 4, 2026 ──
        ['Marine veteran arrested at Senate hearing over Israel policy', 'https://www.military.com/feature/2026/03/05/brian-mcginnis-removed-senate-hearing-after-protest-over-us-policy-toward-israel.html', 'Military.com', '2026-03-04', 38.8928461, -77.0041747, 'Hart Senate Office Building, Washington, DC'],
        ['Ex-Marine arrested, arm broken during Iran-war protest in Senate', 'https://www.democracynow.org/2026/3/11/brian_mcginnis_iran_war_protest_congress', 'Democracy Now!', '2026-03-04', 38.8928461, -77.0041747, 'Hart Senate Office Building, Washington, DC'],
        ['NC firefighter, Marine veteran charged after Senate hearing protest', 'https://abc11.com/post/marine-veteran-north-carolina-charged-protesting-war-iran-senate-hearing/18679829/', 'ABC11', '2026-03-04', 38.8928461, -77.0041747, 'Hart Senate Office Building, Washington, DC'],

        // ── Trans rights · Idaho State Capitol, Boise ID · Apr 1, 2026 (+ Apr 3) ──
        ['Nine arrested at Idaho Statehouse over anti-trans bill', 'https://idahocapitalsun.com/2026/04/01/protestors-urging-idaho-governor-to-veto-bill-outing-trans-kids-to-parents-arrested-at-statehouse/', 'Idaho Capital Sun', '2026-04-01', 43.6177696, -116.1996904, 'Idaho State Capitol, Boise, ID'],
        ['Six arrested at Idaho Capitol over trans bathroom-ban sit-in', 'https://www.thepinknews.com/2026/04/06/idaho-trans-bathroom-ban/', 'PinkNews', '2026-04-03', 43.6177696, -116.1996904, 'Idaho State Capitol, Boise, ID'],

        // ── Immigration · ICE facility, South Waterfront, Portland OR · Jan 9, 2026 ──
        ['Six arrested at Portland ICE facility protest', 'https://www.newsweek.com/portland-protest-outside-ice-facility-sees-multiple-people-arrested-10868665', 'Newsweek', '2026-01-09', 45.4925394, -122.6725455, 'ICE facility, South Waterfront, Portland, OR'],
        ['PPB monitors protest near ICE facility; six arrests made', 'https://www.portland.gov/police/news/2026/1/9/ppb-monitors-protest-activity-near-ice-facility-six-arrests-made', 'City of Portland', '2026-01-09', 45.4925394, -122.6725455, 'ICE facility, South Waterfront, Portland, OR'],
        // ── Disability rights / Medicaid · Russell Senate Office Building, DC · Jun 25, 2025 ──
        ['Dozens arrested protesting Medicaid cuts at Senate building', 'https://www.nbcwashington.com/news/politics/more-than-30-arrested-at-senate-building-while-protesting-medicaid-cuts/3944587/', 'NBC4 Washington', '2025-06-25', 38.8928229, -77.0062654, 'Russell Senate Office Building, Washington, DC'],
        ['Wheelchair users zip-tied at Senate Medicaid-cuts protest', 'https://www.wusa9.com/article/news/local/dc/protestors-arrested-russell-senate-office-building/65-973a0d86-65ad-4658-bdae-e72343070601', 'WUSA9', '2025-06-25', 38.8928229, -77.0062654, 'Russell Senate Office Building, Washington, DC'],

        // ── Disability rights / Medicaid · Rayburn House Office Building, DC · May 13, 2025 ──
        ['25 arrested protesting Medicaid cuts at House hearing', 'https://www.axios.com/2025/05/13/capitol-police-arrest-protesters-medicaid-budget', 'Axios', '2025-05-13', 38.8867704, -77.0100669, 'Rayburn House Office Building, Washington, DC'],
        ['Activists arrested outside Medicaid hearing at the Capitol', 'https://www.deseret.com/politics/2025/05/13/protesters-arrested-outside-medicaid-meeting/', 'Deseret News', '2025-05-13', 38.8867704, -77.0100669, 'Rayburn House Office Building, Washington, DC'],

        // ── Labor · Empire State Building, Manhattan NY · Dec 4, 2025 (Starbucks strike) ──
        ['12 Starbucks workers arrested in Empire State Building sit-in', 'https://www.democracynow.org/2025/12/5/headlines/12_arrested_as_striking_starbucks_workers_hold_sit_in_protest_at_empire_state_building', 'Democracy Now!', '2025-12-04', 40.7484421, -73.9856589, 'Empire State Building, Manhattan, NY'],
        ['12 striking Starbucks workers arrested at Empire State Building', 'https://abc7ny.com/post/12-starbucks-workers-arrested-protesting-outside-empire-state-building-manhattan/18253341/', 'ABC7 New York', '2025-12-04', 40.7484421, -73.9856589, 'Empire State Building, Manhattan, NY'],

        // ── Labor · Starbucks Roasting Plant, York County PA · Dec 17, 2025 ──
        ['Starbucks workers arrested blocking York roasting plant', 'https://paydayreport.com/border-patrol-raids-picket-line-starbucks-workers-arrested-at-roasting-plant-ghiradelli-workers-move-to-strike/', 'Payday Report', '2025-12-17', 40.0516630, -76.7393755, 'Starbucks Roasting Plant, York County, PA'],

        // ── Voting rights · Florida State Capitol, Tallahassee · May 15, 2026 ──
        ['Rep. Angie Nixon arrested after 5-hour Florida Capitol sit-in', 'https://www.wlrn.org/government-politics/2026-05-15/democratic-state-rep-angie-nixon-arrested-after-5-hour-protest-at-florida-capitol', 'WLRN', '2026-05-15', 30.4381743, -84.2821703, 'Florida State Capitol, Tallahassee, FL'],

        // ── Voting rights · Tennessee State Capitol, Nashville · May 7, 2026 ──
        ['Three arrested at Tennessee Capitol over Memphis redistricting', 'https://nashvillebanner.com/2026/05/07/tennessee-congressional-redistricting-confederate-flag/', 'Nashville Banner', '2026-05-07', 36.1658290, -86.7842374, 'Tennessee State Capitol, Nashville, TN'],

        // ── Press freedom · Cities Church, St. Paul MN · Jan 30, 2026 ──
        ['Journalists Don Lemon and Georgia Fort arrested over protest coverage', 'https://www.aljazeera.com/news/2026/1/30/journalist-don-lemon-arrested-in-connection-to-minnesota-ice-protest', 'Al Jazeera', '2026-01-30', 44.9409935, -93.1648789, 'Cities Church, St. Paul, MN'],
        ['Don Lemon arrested by federal authorities, attorney says', 'https://www.nbcnews.com/news/us-news/don-lemon-arrested-federal-authorities-attorney-says-rcna256680', 'NBC News', '2026-01-30', 44.9409935, -93.1648789, 'Cities Church, St. Paul, MN'],
        // ── Immigration / labor · Edward R. Roybal Federal Building, Los Angeles · Jun 6, 2025 ──
        ['SEIU leader David Huerta charged with felony after ICE-raid arrest', 'https://www.cbsnews.com/news/david-huerta-seiu-charged-los-angeles-ice-protest-trump/', 'CBS News', '2025-06-06', 34.0528364, -118.2389802, 'Edward R. Roybal Federal Building, Los Angeles, CA'],
        ['SEIU leader David Huerta released after charge for impeding ICE', 'https://laist.com/news/la-immigration-raids-protests-huerta-charged', 'LAist', '2025-06-06', 34.0528364, -118.2389802, 'Edward R. Roybal Federal Building, Los Angeles, CA'],

        // ── Immigration · Federal Building, 300 N Los Angeles St, Los Angeles · Jun 10, 2025 ──
        ['50 arrested at anti-ICE protest outside downtown LA federal building', 'https://abc7.com/live-updates/live-updates-protesters-clash-officers-during-ice-protest-downtown-la/18511419/', 'ABC7 Los Angeles', '2025-06-10', 34.0537473, -118.2396971, 'Federal Building, 300 N Los Angeles St, Los Angeles, CA'],
        ['At least 71 charged after Los Angeles anti-ICE protests', 'https://lapublicpress.org/2025/08/ice-raids-la-arrests-charges/', 'LA Public Press', '2025-06-10', 34.0537473, -118.2396971, 'Federal Building, 300 N Los Angeles St, Los Angeles, CA'],

        // ── "No Kings" day · Spokane City Hall, WA · Jun 14, 2025 ──
        ['11 arrested at Spokane No Kings protest outside City Hall', 'https://www.spokesman.com/stories/2025/jun/15/11-arrested-at-spokanes-no-kings-protest/', 'The Spokesman-Review', '2025-06-14', 47.6605613, -117.4241938, 'Spokane City Hall, WA'],

        // ── "No Kings" day · Los Angeles City Hall · Jun 14, 2025 ──
        ['38 arrested after No Kings protest in downtown Los Angeles', 'https://www.cbsnews.com/losangeles/news/38-people-arrested-following-no-kings-protest-in-downtown-la/', 'CBS Los Angeles', '2025-06-14', 34.0536961, -118.2429212, 'Los Angeles City Hall'],

        // ── "No Kings" day · Civic Center, Denver CO · Jun 14, 2025 ──
        ['36 arrested after Denver No Kings demonstration', 'https://coloradosun.com/2025/06/15/no-kings-arrests-denver/', 'The Colorado Sun', '2025-06-14', 39.7392357, -104.9891142, 'Civic Center, Denver, CO'],
        ['Denver police arrest dozens after No Kings protest', 'https://www.cbsnews.com/colorado/news/denver-police-arrest-17-people-in-connection-with-no-kings-protest-department-says/', 'CBS Colorado', '2025-06-14', 39.7392357, -104.9891142, 'Civic Center, Denver, CO'],
        // ── Newark NJ · Delaney Hall ICE detention center · May 9, 2025 (Mayor Ras Baraka) ──
        ['Newark Mayor Ras Baraka arrested at Delaney Hall ICE facility', 'https://www.cbsnews.com/newyork/news/newark-mayor-ras-baraka-ice-arrest/', 'CBS New York', '2025-05-09', 40.7180549, -74.1287016, 'Delaney Hall ICE detention center, Newark, NJ'],
        ['Newark mayor charged with trespassing after ICE-facility arrest', 'https://www.washingtonpost.com/nation/2025/05/09/newark-mayor-ice-arrest-ras-baraka-nj/', 'The Washington Post', '2025-05-09', 40.7180549, -74.1287016, 'Delaney Hall ICE detention center, Newark, NJ'],

        // ── Manhattan NY · 26 Federal Plaza immigration court · Jun 17, 2025 (Comptroller Brad Lander) ──
        ['NYC Comptroller Brad Lander arrested by ICE at immigration court', 'https://www.cnn.com/2025/06/17/us/brad-lander-ice-arrest-nyc', 'CNN', '2025-06-17', 40.7154682, -74.0042025, '26 Federal Plaza immigration court, Manhattan, NY'],
        ['Brad Lander detained by masked federal agents, released without charges', 'https://www.thecity.nyc/2025/06/17/brad-lander-arrest-ice-immigration-court/', 'THE CITY', '2025-06-17', 40.7154682, -74.0042025, '26 Federal Plaza immigration court, Manhattan, NY'],

        // ── Broadview IL · Broadview ICE Processing Center · Nov 14, 2025 (clergy) ──
        ['At least seven faith leaders arrested at Broadview ICE facility', 'https://religionnews.com/2025/11/15/at-least-seven-faith-leaders-arrested-at-ice-facility-protest/', 'Religion News Service', '2025-11-14', 41.8681021, -87.8659406, 'Broadview ICE Processing Center, Broadview, IL'],
        ['Evanston residents among 21 arrested in Broadview ICE protest', 'https://evanstonroundtable.com/2025/11/14/evanston-residents-among-21-arrested-in-ice-protest/', 'Evanston RoundTable', '2025-11-14', 41.8681021, -87.8659406, 'Broadview ICE Processing Center, Broadview, IL'],
        ['Pastors describe brutality of arrests at Broadview ICE facility', 'https://chicago.suntimes.com/immigration/2025/11/16/clergy-arrests-broadview-ice', 'Chicago Sun-Times', '2025-11-14', 41.8681021, -87.8659406, 'Broadview ICE Processing Center, Broadview, IL'],
        // ── Trans rights · HHS headquarters (Humphrey Building), Washington DC · Feb 17, 2026 ──
        ['24 arrested blockading HHS over trans youth care ban', 'https://www.washingtonblade.com/2026/02/20/trans-activists-arrested-outside-hhs-headquarters-in-d-c/', 'Washington Blade', '2026-02-17', 38.8866298, -77.0143854, 'HHS headquarters (Humphrey Building), Washington, DC'],
        ['25 arrested protesting HHS gender-affirming care rules', 'https://www.advocate.com/health/transgender-health/protest-anti-transgender-hhs-rules', 'The Advocate', '2026-02-17', 38.8866298, -77.0143854, 'HHS headquarters (Humphrey Building), Washington, DC'],
        ['Moms risk arrest to protect gender-affirming care', 'https://19thnews.org/2026/03/moms-arrested-gender-affirming-care/', 'The 19th', '2026-02-17', 38.8866298, -77.0143854, 'HHS headquarters (Humphrey Building), Washington, DC'],

        // ── Labor · Greater NY Hospital Association, 555 W 57th St, Manhattan · Feb 5, 2026 ──
        ['13 nurses arrested during NYC strike day of action', 'https://www.amny.com/news/nurses-strike-nyc-arrested-civil-disobedience/', 'amNewYork', '2026-02-05', 40.7703849, -73.9905079, 'Greater NY Hospital Association, 555 W 57th St, Manhattan, NY'],
        // ── Austin TX · J.J. Pickle Federal Building · Jun 9, 2025 ──
        ['At least 13 arrested after anti-ICE march in Austin', 'https://www.kxan.com/news/local/austin/austin-police-holds-press-conference-following-anti-ice-protest/', 'KXAN', '2025-06-09', 30.2694066, -97.7390923, 'J.J. Pickle Federal Building, Austin, TX'],
        ['Austin anti-ICE protest met with tear gas, multiple arrested', 'https://www.fox7austin.com/news/austin-ice-protest-arrests-police-livestream', 'FOX 7 Austin', '2025-06-09', 30.2694066, -97.7390923, 'J.J. Pickle Federal Building, Austin, TX'],

        // ── Seattle WA · Henry M. Jackson Federal Building · Jun 11, 2025 ──
        ['Eight arrested at anti-ICE protest outside Seattle federal building', 'https://www.king5.com/article/news/local/protest-blocks-intersections-downtown-seattle-ice/281-5eb88df3-e1cf-4d92-811d-82a901b3cdab', 'KING 5', '2025-06-11', 47.6045821, -122.3354856, 'Henry M. Jackson Federal Building, Seattle, WA'],
        ['Several arrested during Seattle anti-ICE protest', 'https://www.fox13seattle.com/news/arrests-seattle-anti-ice-protest', 'FOX 13 Seattle', '2025-06-11', 47.6045821, -122.3354856, 'Henry M. Jackson Federal Building, Seattle, WA'],

        // ── DeKalb County GA · Chamblee Tucker Rd (Embry Village) · Jun 14, 2025 (journalist Mario Guevara) ──
        ['Salvadoran journalist Mario Guevara arrested covering DeKalb protest', 'https://www.11alive.com/article/news/local/protests/bodycam-video-salvadoran-journalist-arrested-dekalb-county-mario-guevara/85-8de24d09-dfb6-4546-be0e-7d1bb9e393f3', '11Alive', '2025-06-14', 33.8854949, -84.2846477, 'Chamblee Tucker Rd (Embry Village), DeKalb County, GA'],
        ['DeKalb police hand journalist Mario Guevara over to ICE', 'https://atlantaciviccircle.org/2025/06/18/dekalb-police-journalist-mario-guevara-ice-custody/', 'Atlanta Civic Circle', '2025-06-14', 33.8854949, -84.2846477, 'Chamblee Tucker Rd (Embry Village), DeKalb County, GA'],
        // ── Las Vegas NV · downtown (Lloyd D. George Federal Courthouse) · Jun 11, 2025 ──
        ['Nearly 100 arrested at downtown Las Vegas anti-ICE protest', 'https://www.reviewjournal.com/crime/nearly-100-arrested-in-downtown-las-vegas-ice-protest-police-say-3384453/', 'Las Vegas Review-Journal', '2025-06-11', 36.1661179, -115.1426318, 'Downtown Las Vegas (federal courthouse), NV'],
        ['Anti-ICE protest in downtown Las Vegas turns into standoff', 'https://lasvegassun.com/news/2025/jun/12/anti-ice-protest-in-downtown-las-vegas-turns-into/', 'Las Vegas Sun', '2025-06-11', 36.1661179, -115.1426318, 'Downtown Las Vegas (federal courthouse), NV'],

        // ── St. Louis MO · St. Louis City Hall · Apr 17, 2026 (State of the City) ──
        ["Five arrested disrupting St. Louis mayor's State of the City", 'https://www.stlpr.org/government-politics-issues/2026-04-17/st-louis-mayor-cara-spencer-speech-protestors-arrested', 'St. Louis Public Radio', '2026-04-17', 38.6268322, -90.1994026, 'St. Louis City Hall, MO'],
        ['St. Louis police defend arrests of north-city protesters', 'https://www.ksdk.com/article/news/local/state-of-city-chaos-st-louis-police-defend-arrests-of-north-city-protestors/63-c21bfa44-83e8-4ad7-9d67-da50478f04b2', 'KSDK', '2026-04-17', 38.6389941, -90.2314748, 'North St. Louis, MO'],
        // ── San Francisco CA · ICE field office, 630 Sansome St · Jun 8, 2025 ──
        ['Over 150 arrested at San Francisco anti-ICE protest', 'https://www.kqed.org/news/12043255/sf-protesters-denounce-ice-raids-and-trumps-national-guard-deployment-to-la', 'KQED', '2025-06-08', 37.7960291, -122.4016621, 'ICE field office, 630 Sansome St, San Francisco, CA'],
        ['ICE protest in San Francisco ends with 154 arrested', 'https://sfstandard.com/2025/06/08/anti-ice-protest-s/', 'The San Francisco Standard', '2025-06-08', 37.7960291, -122.4016621, 'ICE field office, 630 Sansome St, San Francisco, CA'],

        // ── Cincinnati / Covington · John A. Roebling Suspension Bridge · Jul 17, 2025 ──
        ['Police arrest more than a dozen at anti-ICE Roebling Bridge march', 'https://www.wvxu.org/local-news/2025-07-18/covington-police-arrest-at-anti-ice-march-across-roebling-bridge', 'WVXU', '2025-07-17', 39.0928989, -84.5098665, 'John A. Roebling Suspension Bridge, Covington, KY'],
        ['UC students detained during Roebling Bridge ICE protest', 'https://www.newsrecord.org/news/uc-students-detained-during-roebling-bridge-ice-protest/article_4f71371c-9a6a-4705-9bda-c34012acdef5.html', 'The News Record', '2025-07-17', 39.0928989, -84.5098665, 'John A. Roebling Suspension Bridge, Covington, KY'],
        // ── Minneapolis MN · Minneapolis-St. Paul International Airport · Jan 23, 2026 ──
        ['About 100 clergy arrested at anti-ICE protest at MSP Airport', 'https://www.cbsnews.com/minnesota/news/clergy-members-arrested-minneapolis-st-paul-international-airport/', 'CBS Minnesota', '2026-01-23', 44.8780191, -93.2209281, 'Minneapolis-St. Paul International Airport, MN'],
        ['100 clergy arrested at airport protest as Minnesotans strike against ICE', 'https://www.spokesman.com/stories/2026/jan/23/100-clergy-arrested-at-airport-protest-as-minnesot/', 'The Spokesman-Review', '2026-01-23', 44.8780191, -93.2209281, 'Minneapolis-St. Paul International Airport, MN'],

        // ── Spokane WA · Thomas S. Foley U.S. Courthouse · Jun 11, 2025 (blockade, later federal charges) ──
        ['Nine Spokane ICE-protesters, including ex-council president, federally charged', 'https://www.krem.com/article/news/local/former-spokane-city-council-ben-stuckart-federally-indicted-ice-protests/293-b7211c4d-12e8-407d-b3d9-17f42c3cf6f1', 'KREM', '2025-06-11', 47.6585808, -117.4260620, 'Thomas S. Foley U.S. Courthouse, Spokane, WA'],
        ['Federal agents arrest 9 over Spokane ICE protest, including ex-council president', 'https://www.democracynow.org/2025/7/16/headlines/federal_agents_arrest_9_over_spokane_ice_protests_including_former_city_council_president', 'Democracy Now!', '2025-06-11', 47.6585808, -117.4260620, 'Thomas S. Foley U.S. Courthouse, Spokane, WA'],
        // ── Worcester MA · Eureka Street · May 8, 2025 ──
        ['Two arrested as Worcester neighbors confront ICE detention', 'https://www.bostonglobe.com/2025/05/08/metro/ice-arrests-worcester-woman-spurs-protest/', 'The Boston Globe', '2025-05-08', 42.2389790, -71.8491721, 'Eureka Street, Worcester, MA'],
        ['Two arrested after neighbors try to stop ICE detaining Worcester mother', 'https://www.boston.com/news/local-news/2025/05/08/two-arrested-after-neighbors-try-to-stop-ice-agents-from-detaining-worcester-mother/', 'Boston.com', '2025-05-08', 42.2389790, -71.8491721, 'Eureka Street, Worcester, MA'],
        ['Worcester ICE raid has city on edge', 'https://www.wbur.org/news/2025/05/16/worcester-police-ice-arrest-protesters-activists', 'WBUR', '2025-05-08', 42.2389790, -71.8491721, 'Eureka Street, Worcester, MA'],
        // ── Miami FL · Krome Detention Center · Nov 22, 2025 ──
        ["31 arrested blocking entrance to Miami's Krome Detention Center", 'https://www.nbcmiami.com/news/local/31-arrested-during-protest-at-krome-detention-center/3724939/', 'NBC 6 South Florida', '2025-11-22', 25.7534297, -80.4897094, 'Krome Detention Center, Miami, FL'],
        ['Tampa photojournalist arrested covering Miami ICE protest', 'https://www.tampabay.com/news/tampa/2025/11/26/dave-decker-arrest-miami-ice-protest-immigration-creative-loafing-zuma-press/', 'Tampa Bay Times', '2025-11-22', 25.7744981, -80.1919950, 'Miami, FL'],

        // ── Omaha NE · Glenn Valley Foods (68th & J St) · Jun 10, 2025 ──
        ['Four protesters charged after Omaha ICE raid at meatpacking plant', 'https://nebraskapublicmedia.org/en/news/news-articles/defendants-accused-of-interfering-with-law-enforcement-after-omaha-ice-raid-appear-in-federal-court/', 'Nebraska Public Media', '2025-06-10', 41.2149098, -96.0174295, 'Glenn Valley Foods, Omaha, NE'],
        ['Immigration raid rocks Nebraska plant; protesters and police clash', 'https://flatwaterfreepress.org/ice-raids-hit-omaha-meatpacking-plants/', 'Flatwater Free Press', '2025-06-10', 41.2149098, -96.0174295, 'Glenn Valley Foods, Omaha, NE'],
        // ── Chicago IL · Federal Plaza (the Loop) · Jun 10, 2025 ──
        ['17 arrested as thousands rally against ICE in downtown Chicago', 'https://www.wbez.org/crime/2025/06/11/17-arrested-4-charged-with-felonies-as-thousands-gathered-for-anti-ice-protests-in-downtown-chicago', 'WBEZ', '2025-06-10', 41.8791718, -87.6292686, 'Federal Plaza, downtown Chicago, IL'],
        ['17 arrested, 4 charged with felonies at anti-ICE protest in the Loop', 'https://chicago.suntimes.com/crime/2025/06/11/17-arrested-4-charged-with-felonies-as-thousands-gathered-for-anti-ice-protests-in-downtown-chicago', 'Chicago Sun-Times', '2025-06-10', 41.8791718, -87.6292686, 'Federal Plaza, downtown Chicago, IL'],
        ['17 arrested at anti-ICE protest in downtown Chicago', 'https://www.cbsnews.com/chicago/news/17-arrested-chicago-ice-protest-downtown-police/', 'CBS Chicago', '2025-06-10', 41.8791718, -87.6292686, 'Federal Plaza, downtown Chicago, IL'],
        // ── New York NY · Foley Square (Lower Manhattan) · Jun 10, 2025 ──
        ['Over 80 arrested as thousands flood Foley Square in anti-ICE protest', 'https://www.thecity.nyc/2025/06/10/ice-protests-arrests-nypd-trump-immigration/', 'THE CITY', '2025-06-10', 40.7144380, -74.0030793, 'Foley Square, Lower Manhattan, NY'],
        ['86 arrested at NYC anti-ICE protest in Foley Square', 'https://abc7ny.com/post/80-protesters-arrested-demonstrators-marched-lower-manhattan-amid-trump-immigration-crackdown/16720891/', 'ABC7 New York', '2025-06-10', 40.7144380, -74.0030793, 'Foley Square, Lower Manhattan, NY'],

        // ── Philadelphia PA · Federal Detention Center (Center City) · Jun 10, 2025 ──
        ["15 arrested at anti-ICE protest in Philadelphia's Center City", 'https://whyy.org/articles/philadelphia-ice-protest-arrests-raids/', 'WHYY', '2025-06-10', 39.9528980, -75.1516234, 'Federal Detention Center, Center City, Philadelphia, PA'],
        ['Anti-ICE protest in Philadelphia leads to 15 arrests', 'https://www.cbsnews.com/philadelphia/news/ice-protest-philadelphia-donald-trump/', 'CBS Philadelphia', '2025-06-10', 39.9655661, -75.1815202, 'Center City, Philadelphia, PA'],
        // ── Philadelphia PA · Target, Mifflin St (South Philly) · Feb 5, 2026 ──
        ['About 40 anti-ICE activists arrested at sit-in inside South Philly Target', 'https://www.inquirer.com/news/philadelphia/south-philadelphia-target-protest-ice-arrests-20260205.html', 'The Philadelphia Inquirer', '2026-02-05', 39.9243865, -75.1461590, 'Target, Mifflin St, South Philadelphia, PA'],

        // ── Burlington MA · ICE field office, 1000 District Ave · Apr 29, 2026 ──
        ['11 arrested in civil disobedience at Burlington ICE facility', 'https://www.boston.com/news/local-news/2026/04/29/11-arrested-outside-burlington-ice-facility-in-act-of-civil-disobedience-police-say/', 'Boston.com', '2026-04-29', 42.4826346, -71.2088722, 'ICE field office, 1000 District Ave, Burlington, MA'],
        ['11 arrested after protesting outside Burlington ICE facility', 'https://www.wbur.org/news/2026/04/29/burlington-ice-detention-facility-arrests', 'WBUR', '2026-04-29', 42.4826346, -71.2088722, 'ICE field office, 1000 District Ave, Burlington, MA'],

        // ── Albuquerque NM · ICE office (Watson Dr SE) · Jan 9, 2026 ──
        ['Two detained at Albuquerque ICE office protest', 'https://sourcenm.com/2026/01/09/protestors-ice-clash-at-albuquerque-dhs-facility/', 'Source New Mexico', '2026-01-09', 35.0012708, -106.6170198, 'ICE office (Watson Dr SE), Albuquerque, NM'],
        ['Two arrested at Albuquerque ICE protest after confrontation', 'https://www.abqjournal.com/news/two-arrested-at-ice-protest-in-albuquerque/2957357', 'Albuquerque Journal', '2026-01-09', 35.0012708, -106.6170198, 'ICE office (Watson Dr SE), Albuquerque, NM'],
        // ── Portland ME · Sen. Susan Collins' office (Canal Plaza) · Jan 28, 2026 ──
        ["Nine anti-ICE protesters arrested at Sen. Collins' office in Portland, Maine", 'https://www.cbsnews.com/boston/news/susan-collins-ice-protest-portland-maine/', 'CBS Boston', '2026-01-28', 43.6570957, -70.2556842, "Sen. Susan Collins' office (Canal Plaza), Portland, ME"],

        // ── Denver CO · Colorado State Capitol · Jun 10, 2025 ──
        ['17 arrested at anti-ICE protest at the Colorado Capitol', 'https://www.coloradopolitics.com/colorado-in-dc/denver-protest-arrests-state-capitol/article_727b84fd-a17f-5c68-b716-ea952541141e.html', 'Colorado Politics', '2025-06-10', 39.7399969, -104.9844034, 'Colorado State Capitol, Denver, CO'],
        ['Anti-ICE demonstrations expand to Colorado; police arrest 17', 'https://www.axios.com/local/denver/2025/06/11/ice-protests-colorado-los-angeles', 'Axios Denver', '2025-06-10', 39.7399969, -104.9844034, 'Colorado State Capitol, Denver, CO'],
        // ── Tucson AZ · ICE office (S Country Club Rd) · Jun 11, 2025 ──
        ['Three arrested at anti-ICE protest outside Tucson ICE office', 'https://ktar.com/immigration/ice-protest-in-tucson/5716880/', 'KTAR News', '2025-06-11', 32.1353443, -110.9260530, 'ICE office (S Country Club Rd), Tucson, AZ'],
        ['Three arrested after anti-ICE protest in Tucson turns tense', 'https://www.azfamily.com/2025/06/12/watch-anti-ice-protest-tucson-turns-violent/', 'AZFamily', '2025-06-11', 32.1353443, -110.9260530, 'ICE office (S Country Club Rd), Tucson, AZ'],

        // ── Brookhaven / metro Atlanta GA · Buford Highway · Jun 10, 2025 ──
        ['6 arrested at anti-ICE protest along Buford Highway in Brookhaven', 'https://www.ajc.com/news/2025/06/immigration-protest-along-buford-highway-marred-by-tear-gas-and-fireworks/', 'The Atlanta Journal-Constitution', '2025-06-10', 33.8521948, -84.3185412, 'Buford Highway, Brookhaven (Atlanta), GA'],
        ['Brookhaven police identify 6 arrested at Buford Highway anti-ICE protest', 'https://www.atlantanewsfirst.com/2025/06/11/brookhaven-police-identify-suspects-arrested-during-anti-ice-protest-buford-highway/', 'Atlanta News First', '2025-06-10', 33.8521948, -84.3185412, 'Buford Highway, Brookhaven (Atlanta), GA'],

        // ── Dallas TX · Margaret Hunt Hill Bridge · Jun 9, 2025 ──
        ["One arrested at anti-ICE march on Dallas' Margaret Hunt Hill Bridge", 'https://www.cbsnews.com/texas/news/arrest-during-ice-protest-margaret-hunt-hill-bridge-dallas/', 'CBS Texas', '2025-06-09', 32.7800134, -96.8220017, 'Margaret Hunt Hill Bridge, Dallas, TX'],

        // ── Portland OR · ICE facility, South Waterfront · Oct 4, 2025 ──
        ['Federal officers fire tear gas, arrest several at Portland ICE facility', 'https://www.opb.org/article/2025/10/04/portland-ice-facility-protest/', 'OPB', '2025-10-04', 45.4925394, -122.6725455, 'ICE facility, South Waterfront, Portland, OR'],
        // ── Newark NJ · Delaney Hall ICE jail · May 2026 (multi-night clashes) ──
        ['Protesters and police clash for days at the Delaney Hall ICE jail', 'https://www.cnn.com/2026/05/30/us/delaney-hall-new-jersey-ice-protests', 'CNN', '2026-05-29', 40.7180549, -74.1287016, 'Delaney Hall ICE detention center, Newark, NJ'],
        ['Six arrested as protesters clash with agents outside Delaney Hall', 'https://abc7ny.com/post/delaney-hall-protests-6-arrests-protesters-clash-ice-agents-outside-newark-nj/19192526/', 'ABC7 New York', '2026-05-29', 40.7180549, -74.1287016, 'Delaney Hall ICE detention center, Newark, NJ'],

        // ── Minneapolis MN · Bishop Henry Whipple Federal Building · Jan 8, 2026 ──
        ['11 arrested at Whipple Federal Building protest after ICE shooting', 'https://www.fox9.com/news/minneapolis-ice-shooting-jan-8-2026', 'FOX 9', '2026-01-08', 44.8942120, -93.1948904, 'Bishop Henry Whipple Federal Building, Minneapolis (Fort Snelling), MN'],
        ['Anti-ICE protests outside Whipple Federal Building bring arrests', 'https://www.mprnews.org/story/2026/01/23/antiice-protests-outside-whipple-federal-building-brings-arrests', 'MPR News', '2026-01-08', 44.8942120, -93.1948904, 'Bishop Henry Whipple Federal Building, Minneapolis (Fort Snelling), MN'],

        // ── Broadview IL · Broadview ICE Processing Center · Nov 7, 2025 ("suburban moms" sit-in) ──
        ['14 "suburban moms" arrested in sit-in at Broadview ICE facility', 'https://chicago.suntimes.com/immigration/2025/11/07/fourteen-suburban-moms-arrested-in-sit-in-protest-outside-broadview-ice-facility', 'Chicago Sun-Times', '2025-11-07', 41.8681021, -87.8659406, 'Broadview ICE Processing Center, Broadview, IL'],
        ['Suburban moms arrested during sit-in at Broadview ICE facility', 'https://www.nbcchicago.com/news/local/suburban-moms-arrested-during-sit-in-at-ice-processing-facility-witnesses-say/3848961/', 'NBC Chicago', '2025-11-07', 41.8681021, -87.8659406, 'Broadview ICE Processing Center, Broadview, IL'],
        // ── San Francisco CA · Market & Van Ness · Jun 9, 2025 (second night) ──
        ['92 arrested on second night of San Francisco anti-ICE protests', 'https://www.kqed.org/news/12043544/dozens-more-arrested-in-calmer-night-of-san-francisco-ice-protests', 'KQED', '2025-06-09', 37.7753971, -122.4193700, 'Market & Van Ness, San Francisco, CA'],
        ['SFPD arrests dozens on second night of mass ICE protests', 'https://missionlocal.org/2025/06/sf-mission-march-mobilized-thousands-against-ice/', 'Mission Local', '2025-06-09', 37.7753971, -122.4193700, 'Market & Van Ness, San Francisco, CA'],

        // ── Los Angeles CA · City Hall (student walkout) · Feb 4, 2026 ──
        ['Several arrested as LA students walk out against ICE operations', 'https://www.cbsnews.com/losangeles/news/lapd-arrests-protesters-students-gathered-downtown-los-angeles-ice-operations-immigration/', 'CBS Los Angeles', '2026-02-04', 34.0536961, -118.2429212, 'Los Angeles City Hall (student walkout), CA'],

        // ===== additional curated data-center / ICE conflict items =====
        // ── Claremore OK · Rogers State University · Feb 17, 2026 (additional source) ──
        ['Tension over a proposed AI data center leads to an arrest in Oklahoma', 'https://africa.businessinsider.com/news/tension-over-a-proposed-ai-data-center-leads-to-an-arrest-in-oklahoma/jk912pd', 'Business Insider Africa', '2026-02-17', 36.3186099, -95.6361137, 'Rogers State University, Claremore, OK'],
        // ── Woodland Park CO · cell tower sabotage · Aug 28, 2025 ──
        ['Man arrested for sabotaging cell tower, causing Woodland Park outage', 'https://www.datacenterdynamics.com/en/news/man-arrested-for-causing-cell-tower-outage-in-colorado/', 'DataCenter Dynamics', '2025-08-28', 38.9938016, -105.0570450, 'Woodland Park, CO'],
        // ── Williston VT · ICE surveillance center · Oct 6, 2025 (context, no arrest) ──
        ['ICE expands social-media surveillance hub in Vermont', 'https://vtdigger.org/2025/10/06/ice-plans-to-boost-its-surveillance-on-social-media-using-contractors-in-vermont/', 'VTDigger', '2025-10-06', 44.4607481, -73.1229347, 'White Cap Business Park, Williston, VT'],
        // ── Port Washington WI · Common Council · Dec 3, 2025 (additional source) ──
        ['Chaos erupts at Port Washington data center hearing; three removed', 'https://ozaukeepress.com/content/meeting-erupts-chaos-over-data-center', 'Ozaukee Press', '2025-12-03', 43.3878941, -87.8713384, 'Port Washington City Hall, WI'],
        // ── Williston VT · White Cap Business Park · Jan 11, 2026 (banner drop, no arrest) ──
        ['Activists target ICE surveillance office with banner drop in Williston', 'https://vtdigger.org/2026/01/14/activists-target-ice-digital-surveillance-site-in-williston/', 'VTDigger', '2026-01-11', 44.4607481, -73.1229347, 'White Cap Business Park, Williston, VT'],
        // ── Trinidad TX · May 8, 2026 (Facebook-post arrest) ──
        ["Woman arrested over Facebook post about her town's water", 'https://www.fox4news.com/news/woman-arrested-facebook-post-concerning-trinidad-water-poisoning', 'FOX 4', '2026-05-08', 32.1440417, -96.0910814, 'Trinidad, TX'],
        // ── Indianapolis IN · Martindale-Brightwood · Apr 6, 2026 (data-center backlash shooting) ──
        ["13 shots fired at Indianapolis councilor's home with 'No Data Centers' note", 'https://www.cbsnews.com/news/indianapolis-councilor-ron-gibson-home-shooting-data-centers-note/', 'CBS News', '2026-04-06', 39.8082861, -86.1197981, 'Martindale-Brightwood, Indianapolis, IN'],

        // ===== broader protest movements (May 2025 – May 2026) =====
        // ── "No Kings" / 50501 anti-Trump ──
        ["One arrested as 'No Kings' protesters fill the Arizona Capitol", 'https://www.azfamily.com/2025/10/18/no-kings-protests-planned-across-arizona-nationwide-amid-government-shutdown/', "Arizona's Family", '2025-10-18', 33.4481220, -112.0972114, 'Arizona State Capitol, Phoenix, AZ'],
        ["14 arrested at a downtown LA 'No Kings' protest", 'https://laist.com/news/politics/lapd-made-at-least-13-arrests-at-saturdays-no-kings-protest', 'LAist', '2025-10-18', 34.0535694, -118.2385494, 'Metropolitan Detention Center, Los Angeles, CA'],
        ["12 arrested at a Denver 'No Kings' rally as police deploy smoke", 'https://www.cbsnews.com/colorado/news/no-kings-rallies-colorado-october-2025/', 'CBS Colorado', '2025-10-18', 39.7399969, -104.9844034, 'Colorado State Capitol, Denver, CO'],
        ["75 arrested after a 'No Kings' march in downtown Los Angeles", 'https://www.cbsnews.com/losangeles/news/no-kings-downtown-los-angeles-march-28/', 'CBS Los Angeles', '2026-03-28', 34.0555008, -118.2456965, 'Gloria Molina Grand Park, Los Angeles, CA'],

        // ── May Day · Make Billionaires Pay (anti-billionaire / climate justice) ──
        ['Climate activists block the New York Stock Exchange on May Day', 'https://www.cbsnews.com/newyork/news/may-day-protest-nyc-washington-square-park/', 'CBS New York', '2026-05-01', 40.7068530, -74.0112564, 'New York Stock Exchange, Manhattan, NY'],
        ["About 25,000 march down Park Avenue to 'Make Billionaires Pay'", 'https://www.thenation.com/article/environment/make-billionaires-pay-march-climate-change/', 'The Nation', '2025-09-20', 40.7563245, -73.9721063, 'Park Avenue, Manhattan, NY'],

        // ── Faith / labor anti-ICE actions ──
        ["54 faith leaders arrested in a Hart Senate 'Abolish ICE' sit-in", 'https://www.indcatholicnews.com/news/54249', 'Independent Catholic News', '2026-01-30', 38.8928461, -77.0041747, 'Hart Senate Office Building, Washington, DC'],
        ['At least 10 clergy arrested at the Philadelphia ICE field office', 'https://www.nbcphiladelphia.com/news/local/clergy-members-arrested-during-anti-ice-protest-in-philly/4377018/', 'NBC10 Philadelphia', '2026-03-30', 39.9512871, -75.1535469, 'ICE field office, N 8th St, Philadelphia, PA'],

        // ── Pro-Palestine campus organizing ──
        ["80 arrested as pro-Palestinian protesters occupy Columbia's Butler Library", 'https://www.cbsnews.com/newyork/news/columbia-university-library-pro-palestinian-demonstration/', 'CBS New York', '2025-05-07', 40.8077507, -73.9624901, 'Columbia University (Butler Library), New York, NY'],
        ['13 arrested at a pro-Palestine Gaza protest on Boston Common', 'https://www.tuftsdaily.com/article/2025/10/two-students-arrested-at-pro-palestine-protest-in-boston', 'The Tufts Daily', '2025-10-07', 42.3550826, -71.0656909, 'Boston Common, Boston, MA'],
        ["11 Stanford protesters indicted over the president's-office takeover", 'https://www.paloaltoonline.com/crime/2025/10/03/grand-jury-indicts-11-pro-palestine-stanford-protestors/', 'Palo Alto Online', '2025-10-03', 37.4289814, -122.1700548, 'Stanford University, Stanford, CA'],
        ['33 charged over the UW engineering-building occupation', 'https://www.kuow.org/stories/33-charged-in-occupation-and-vandalism-of-university-of-washington-engineering-building', 'KUOW', '2026-03-04', 47.6554303, -122.3001692, 'University of Washington, Seattle, WA'],

        // ── Anti–National Guard deployment ──
        ["'Free the 901' coalition marches against the National Guard in Memphis", 'https://mlk50.com/2025/09/30/free-the-901-coalition-forms-to-oppose-recent-federal-intervention-in-memphis/', 'MLK50', '2025-09-28', 35.1489460, -90.0521594, 'Memphis City Hall, Memphis, TN'],

        // ── Trans & LGBTQ rights ──
        ['Nine trans activists arrested at the Supreme Court over the Skrmetti ruling', 'https://www.washingtonblade.com/2025/06/20/nine-trans-activists-arrested-outside-supreme-court/', 'Washington Blade', '2025-06-18', 38.8906043, -77.0044112, 'U.S. Supreme Court, Washington, DC'],
        ['Trans activists stage a bathroom sit-in at the Texas Capitol over SB8', 'https://www.thepinknews.com/2025/08/25/texas-trans-bathroom-bill-protest/', 'PinkNews', '2025-08-22', 30.2746652, -97.7404598, 'Texas State Capitol, Austin, TX'],

        // ── March for Life (anti-abortion) ──
        ['Tens of thousands rally at the 53rd National March for Life', 'https://www.catholicbusinessjournal.com/news/life-and-liberty/csr-catholic-social-responsibility/life-issues/highlights-53rd-national-march-for-life-in-the-nations-capital-on-january-23-2026', 'Catholic Business Journal', '2026-01-23', 38.8897468, -77.0230745, 'National Mall, Washington, DC'],

        // ── Synagogue / Israel-related demonstrations ──
        ['Two arrested as a pro-Palestinian protest disrupts an LA synagogue event', 'https://www.jta.org/2025/12/04/united-states/2-arrested-after-pro-palestinian-protest-disrupts-event-on-la-synagogue-campus', 'Jewish Telegraphic Agency', '2025-12-04', 34.0621612, -118.3049974, 'Wilshire Boulevard Temple, Los Angeles, CA'],
        ['Dueling crowds face off at Park East Synagogue over an Israeli real-estate expo', 'https://ny1.com/nyc/manhattan/news/2026/05/06/tense-protests-and-counter-protest-outside-park-east-synagogue', 'NY1', '2026-05-06', 40.7670121, -73.9633639, 'Park East Synagogue, Manhattan, NY'],
        ['Four arrested at dueling protests over an Israeli real-estate expo in Brooklyn', 'https://www.brooklynpaper.com/midwood-pro-palestine-pro-israel-protest/', 'Brooklyn Paper', '2026-05-11', 40.6205176, -73.9558275, 'Young Israel of Midwood, Brooklyn, NY'],

        // ── Met Gala protest (Bezos-sponsored) · Chris Smalls arrested · May 4, 2026 ──
        ['Labor leader Chris Smalls arrested at the protest outside the Bezos-sponsored Met Gala', 'https://hyperallergic.com/rollicking-protest-against-bezoss-met-gala-erupts-in-manhattan/', 'Hyperallergic', '2026-05-04', 40.7794396, -73.9633825, 'Metropolitan Museum of Art (Met Gala), Manhattan, NY'],

        // ===== more curated protest / arrest events (round 2) =====
        // ── Pro-Palestine campus · University of Michigan (Rackham) · Oct 23, 2025 ──
        ['Three arrested protesting an IDF-soldier talk at the University of Michigan', 'https://www.michigandaily.com/news/news-briefs/three-pro-palestine-activists-arrested-for-protesting-speech-given-by-former-israeli-soldiers/', 'The Michigan Daily', '2025-10-23', 42.2806127, -83.7373430, 'Rackham Graduate School, University of Michigan, Ann Arbor, MI'],
        // ── Pro-Palestine campus · Ohio State University · Apr 15, 2026 ──
        ['Two arrested at an Ohio State protest of a Students Supporting Israel event', 'https://www.wosu.org/politics-government/2026-04-15/protest-organizers-criticize-ohio-state-after-university-police-arrest-student-and-staff-member', 'WOSU', '2026-04-15', 39.9979095, -83.0081480, 'Ohio Union, Ohio State University, Columbus, OH'],
        // ── Anti-ICE · Charlotte ICE office · Nov 17, 2025 ──
        ['Woman charged with assaulting an officer at a Charlotte ICE-office protest', 'https://www.wfae.org/crime-justice/2025-11-21/charlotte-woman-faces-federal-charge-missing-property-after-arrest-at-ice-protest', 'WFAE', '2025-11-17', 35.1644837, -80.9110578, 'U.S. ICE office, southwest Charlotte, NC'],
        // ── Anti-ICE · LA Metropolitan Detention Center (round-the-clock protest) · Aug 6, 2025 ──
        ['18 arrested at a round-the-clock anti-ICE protest at the LA detention center', 'https://capitalandmain.com/federal-officers-continue-arresting-anti-ice-protesters-during-24-7-demonstrations', 'Capital & Main', '2025-08-06', 34.0535694, -118.2385494, 'Metropolitan Detention Center, Los Angeles, CA'],
        // ── Anti-ICE · Columbia University gates · Feb 5, 2026 ──
        ["NYPD arrests 12 at an anti-ICE demonstration outside Columbia's gates", 'https://abc7ny.com/post/anti-ice-protest-outside-columbia-university-leads-12-arrests/18549912/', 'ABC7 New York', '2026-02-05', 40.8077507, -73.9624901, 'Columbia University gates (Broadway & W 116th St), Manhattan, NY'],
        // ── Anti-ICE · San Diego City Hall (mayor's office occupation) · Jan 23, 2026 ──
        ["Six arrested occupying the San Diego mayor's office over ICE cooperation", 'https://www.nbcsandiego.com/news/local/anti-ice-protesters-occupy-san-diego-mayors-office/3965984/', 'NBC 7 San Diego', '2026-01-23', 32.7169986, -117.1628361, 'San Diego City Administration Building, CA'],
        // ── "No Kings" · Salt Lake City · Jun 14, 2025 ──
        ['No Kings march in Salt Lake City marred by a fatal shooting; one arrested', 'https://www.sltrib.com/news/2025/06/20/man-held-no-kings-shooting/', 'The Salt Lake Tribune', '2025-06-14', 40.7668142, -111.8871222, 'Wallace F. Bennett Federal Building, Salt Lake City, UT'],
        // ── "No Kings" · Charlotte (First Ward Park) · Jun 14, 2025 ──
        ['Two arrested after the No Kings protest in uptown Charlotte', 'https://www.wccbcharlotte.com/2025/06/14/cmpd-two-arrested-one-injured-after-conclusion-of-no-kings-protest-in-uptown-charlotte/', 'WCCB Charlotte', '2025-06-14', 35.2281758, -80.8359315, 'First Ward Park, Charlotte, NC'],
        // ── "No Kings" · Nashville (Bicentennial Capitol Mall) · Jun 14, 2025 ──
        ['Armed counter-protester arrested at the Nashville No Kings rally', 'https://www.wgnsradio.com/article/93378/murfreesboro-teen-arrested-at-nashville-protest-for-brandishing-handgun', 'WGNS Radio', '2025-06-14', 36.1711123, -86.7876691, 'Bicentennial Capitol Mall State Park, Nashville, TN'],
        // ── "No Kings" · Tucson · Jun 14, 2025 ──
        ['Two arrested after No Kings protesters clashed with Tucson police', 'https://www.kold.com/2025/06/16/tucson-police-arrest-two-after-officer-assaulted-during-weekend-protests/', 'KOLD News 13', '2025-06-14', 32.2097000, -110.9268000, 'E 22nd St & S Country Club Rd, Tucson, AZ'],
        // ── "No Kings 2.0" · Portland OR ICE facility · Oct 18, 2025 (facility already pinned) ──
        ['Three arrested at the Portland ICE building after a No Kings 2.0 rally', 'https://www.kptv.com/2025/10/18/latest-updates-people-protest-portland-ice-building-after-no-kings-20-rally/', 'KPTV FOX 12', '2025-10-18', 45.4925352, -122.6725241, 'Portland ICE facility, OR'],
        // ── "No Kings" · Spokane ICE facility · Mar 28, 2026 ──
        ['Two arrested outside the ICE facility at a downtown Spokane No Kings rally', 'https://www.spokesman.com/stories/2026/mar/28/two-arrested-outside-ice-facility-at-downtown-spok/', 'The Spokesman-Review', '2026-03-28', 47.6658024, -117.4181787, 'ICE facility, 411 W Cataldo Ave, Spokane, WA'],
        // ── Pro-Palestine · JVP sit-in at Sen. Schumer's office, Manhattan · Aug 1, 2025 ──
        ["JVP sit-in at Schumer's office; 50 arrested over Gaza arms sales", 'https://www.commondreams.org/news/chuck-schumer-gaza', 'Common Dreams', '2025-08-01', 40.7550043, -73.9718262, 'Sens. Schumer & Gillibrand offices, 780 Third Ave, Manhattan, NY'],
        // ── Anti-ICE · 26 Federal Plaza, Manhattan · Aug 8, 2025 (plaza already pinned) ──
        ['15 arrested at an anti-ICE protest outside 26 Federal Plaza', 'https://www.cbsnews.com/newyork/news/federal-plaza-ice-protests-nyc/', 'CBS News New York', '2025-08-08', 40.7156451, -74.0033016, '26 Federal Plaza, Manhattan, NY'],
        // ── Anti-abortion · Red Rose Rescue, Delaware County · Jul 31, 2025 ──
        ['Six Red Rose Rescue activists arrested at a Delaware County abortion clinic', 'https://www.liveaction.org/news/six-red-rose-rescuers-arrested-outreach-pennsylvania', 'Live Action', '2025-07-31', 39.8512000, -75.3835000, "Delaware County Women's Center, Upland, PA"],
        // ── Anti-abortion · Operation Rescue blockade, Memphis · Dec 5, 2025 ──
        ['14 anti-abortion activists arrested blockading a Memphis Planned Parenthood', 'https://msmagazine.com/2025/11/26/anti-abortion-traning-operation-rescue-trump-clinic-violence/', 'Ms. Magazine', '2025-12-05', 35.1396914, -89.9797158, 'Planned Parenthood, 2430 Poplar Ave, Memphis, TN'],
        // ── Labor · teachers' sit-in at the Connecticut Capitol · May 21, 2025 ──
        ["10 educators arrested in a sit-in at Gov. Lamont's office over school funding", 'https://ctmirror.org/2025/05/21/teachers-arrested-capitol-education-funding/', 'CT Mirror', '2025-05-21', 41.7641400, -72.6822665, 'Connecticut State Capitol, Hartford, CT'],
        // ── Labor · SEIU 721 county workers, Los Angeles · Jun 3, 2025 ──
        ['Six SEIU county workers arrested in civil disobedience at the LA Board of Supervisors', 'https://mynewsla.com/government/2025/06/03/la-county-workers-gather-in-downtown-demanding-contract-negotiations-4/', 'MyNewsLA', '2025-06-03', 34.0568474, -118.2462025, 'Kenneth Hahn Hall of Administration, Los Angeles, CA'],
        // ── Labor · Culinary Union airport workers, Las Vegas · Dec 3, 2025 ──
        ['About two dozen Culinary Union airport workers arrested blocking a Las Vegas road', 'https://lasvegassun.com/news/2025/dec/03/las-vegas-airport-workers-arrested-in-civil-disobe/', 'Las Vegas Sun', '2025-12-03', 36.0861034, -115.1611002, 'Harry Reid International Airport, Las Vegas, NV'],

        // ===== more curated events (round 3: prosecutions, ICE surges, statehouses) =====
        // ── Prosecution · Portland ICE cases (Hatfield courthouse) ──
        ['Portland man pleads guilty to arson at the ICE facility', 'https://local12.com/news/nation-world/portland-man-pleads-guilty-to-arson-at-ice-facility-faces-up-to-20-years-in-prison-trenten-barker-oregon-multnomah-county-immigration-customs-enforcement', 'Local 12', '2025-11-19', 45.5157824, -122.6763053, 'Mark O. Hatfield U.S. Courthouse, Portland, OR'],
        ['Portlander pleads guilty to hitting an ICE officer with a rock at a protest', 'https://www.opb.org/article/2026/02/20/portlander-pleads-guilty-hitting-ice-officer-protest/', 'OPB', '2026-02-20', 45.5157824, -122.6763053, 'Mark O. Hatfield U.S. Courthouse, Portland, OR'],
        // ── Prosecution · Los Angeles anti-ICE cases (Roybal courthouse, already pinned) ──
        ['Man sentenced to 4 years for a Molotov cocktail at the LA anti-ICE protests', 'https://www.ksat.com/news/national/2026/02/01/man-sentenced-to-4-years-in-prison-for-throwing-molotov-cocktail-during-la-immigration-protest/', 'KSAT', '2026-01-30', 34.0479960, -118.2521002, 'Los Angeles, CA'],
        ['Elpidio Reyna pleads guilty to assaulting a federal officer with rocks', 'https://www.nbclosangeles.com/news/local/paramount-federal-officer-assault-elpidio-reyna/3849329/', 'NBC Los Angeles', '2026-02-17', 33.8989170, -118.1710050, 'Paramount, CA'],
        ['Six men plead guilty to attacking CHP officers at the LA anti-ICE protests', 'https://www.cbsnews.com/losangeles/news/california-men-plead-guilty-violence-chp-officers-los-angeles-immigration-protests/', 'CBS Los Angeles', '2026-04-23', 34.0479960, -118.2521002, 'Los Angeles, CA'],
        ['Two women convicted of stalking an ICE officer after a livestreamed pursuit', 'https://www.foxla.com/news/ice-officer-stalking-livestream-conviction-los-angeles', 'FOX 11 Los Angeles', '2026-03-03', 34.0479960, -118.2521002, 'Los Angeles, CA'],
        // ── Prosecution · San Diego "unmasking" sentencing · Mar 5, 2026 ──
        ['Activist sentenced for unmasking an ICE agent at a San Diego raid', 'https://timesofsandiego.com/crime/2026/03/05/activist-home-detention-no-prison-federal-agent/', 'Times of San Diego', '2026-03-05', 32.7143363, -117.1647304, 'Edward J. Schwartz U.S. Courthouse, San Diego, CA'],
        // ── Prosecution · Don Lemon indicted over a St. Paul protest · Jan 29, 2026 ──
        ['Don Lemon and others indicted over a Minnesota anti-ICE church protest', 'https://www.pbs.org/newshour/nation/read-the-full-indictment-against-don-lemon-georgia-fort-and-others-charged-in-minnesota', 'PBS NewsHour', '2026-01-29', 44.9465967, -93.0891484, 'Warren E. Burger Federal Building, St. Paul, MN'],
        // ── Prosecution · Broadview protesters' charges dropped · May 21, 2026 ──
        ['Charges dropped against four Broadview ICE protesters over misconduct', 'https://blockclubchicago.org/2026/05/21/trial-date-for-broadview-protesters-vacated-just-days-ahead-of-expected-start/', 'Block Club Chicago', '2026-05-21', 41.8639650, -87.8533732, 'Broadview ICE facility, IL'],
        // ── Prosecution · "Spokane 3" convicted · May 30, 2026 ──
        ["The 'Spokane 3' are convicted of federal conspiracy over an anti-ICE blockade", 'https://www.foxnews.com/us/spokane-3-protesters-convicted-federal-conspiracy-charges-blocking-ice-transfer-washington', 'Fox News', '2026-05-30', null, null, null],
        // ── Prosecution · UC Irvine pro-Palestine trial · Apr 7, 2026 ──
        ['Jury acquits two in the UC Irvine pro-Palestine protest trial', 'https://mynewsla.com/crime/2026/04/07/2-of-3-defendants-acquitted-in-uci-protest-trial/', 'MyNewsLA', '2026-04-07', 33.6445025, -117.8441480, 'Physical Sciences Quad, UC Irvine, CA'],
        // ── Prosecution · Princeton Clio Hall trespass trial · Jun 17, 2025 ──
        ['Trespass trial set for 13 Princeton Clio Hall sit-in protesters', 'https://paw.princeton.edu/article/clio-hall-protest-trial-municipal-court-delayed-until-june', 'Princeton Alumni Weekly', '2025-06-17', 40.3476620, -74.6591469, 'Clio Hall, Princeton University, NJ'],
        // ── Campus · Cal Poly Humboldt occupation arrest · Mar 6, 2026 ──
        ["Activist arrested after a Palestine occupation of Cal Poly Humboldt's Nelson Hall", 'https://lostcoastoutpost.com/2026/mar/6/three-students-handed-interim-suspensions-nelson-h/', 'Lost Coast Outpost', '2026-03-06', 40.8764763, -124.0800564, 'Nelson Hall, Cal Poly Humboldt, Arcata, CA'],
        // ── Campus · Brooklyn College encampment cleared · May 8, 2025 ──
        ['NYPD clears a pro-Palestine encampment at Brooklyn College; seven arrested', 'https://www.cbsnews.com/newyork/news/brooklyn-college-protest-may-2025/', 'CBS News New York', '2025-05-08', 40.6310352, -73.9520082, 'Brooklyn College East Quad, Brooklyn, NY'],
        // ── ICE surge · south Minneapolis (Lake St) · Dec 15, 2025 ──
        ['ICE agents clash with residents; two arrested in south Minneapolis', 'https://www.cbsnews.com/minnesota/news/ice-agents-south-minneapolis-clash-protests/', 'CBS Minnesota', '2025-12-15', 44.9491775, -93.2814357, 'Lake St & Pillsbury Ave (Karmel Mall), Minneapolis, MN'],
        // ── ICE surge · Minneapolis hotel housing ICE agents · Jan 29, 2026 ──
        ['67 arrested protesting outside a Minneapolis hotel housing ICE agents', 'https://www.advocate.com/news/minneapolis-hotel-ice-arrests-67', 'The Advocate', '2026-01-29', 44.9737275, -93.2300222, 'Graduate by Hilton, 615 Washington Ave SE, Minneapolis, MN'],
        // ── ICE surge · Broadview ICE facility (already pinned) · Sep 19, 2025 ──
        ['At least four arrested in a 12-hour protest at the Broadview ICE facility', 'https://www.cbsnews.com/chicago/news/broadview-ice-facility-protest-illinois/', 'CBS Chicago', '2025-09-19', 41.8639650, -87.8533732, 'Broadview ICE facility, IL'],
        // ── ICE surge · Whipple Federal Building (already pinned) · Feb 7, 2026 ──
        ['54 arrested at an anti-ICE protest outside the Whipple Federal Building', 'https://kstp.com/kstp-news/top-news/unlawful-assembly-declared-outside-whipple-building-after-deputy-was-struck-in-the-head/', 'KSTP', '2026-02-07', 44.8942120, -93.1948904, 'Whipple Federal Building, Minneapolis, MN'],
        // ── ICE · 26 Federal Plaza (already pinned) · Sep 18, 2025 ──
        ['Public Advocate Jumaane Williams and 15 officials among 75+ arrested at 26 Federal Plaza', 'https://www.cityandstateny.com/politics/2025/09/state-and-city-lawmakers-arrested-26-federal-plaza/408218/', 'City & State NY', '2025-09-18', 40.7156451, -74.0033016, '26 Federal Plaza, Manhattan, NY'],
        // ── ICE · SF immigration court (already pinned) · Dec 16, 2025 ──
        ['42 faith leaders arrested chaining themselves to the SF immigration court', 'https://missionlocal.org/2025/12/faith-leaders-chain-immigration-court-san-francisco/', 'Mission Local', '2025-12-16', 37.7901682, -122.4021443, 'SF immigration court, CA'],
        // ── DC · about 60 veterans arrested at the US Capitol · Jun 13, 2025 ──
        ['About 60 veterans arrested at the US Capitol over the military parade', 'https://www.cbsnews.com/news/police-arrest-protesters-u-s-capitol/', 'CBS News', '2025-06-13', 38.8898130, -77.0090208, 'U.S. Capitol grounds, Washington, DC'],
        // ── DC · Moral Monday (US Capitol, already pinned) · Jun 30, 2025 ──
        ['38 arrested with caskets at a Moral Monday protest of Medicaid cuts', 'https://wjla.com/news/local/protestors-arrested-capitol-opposing-medicaid-casket-donald-trump-big-beautiful-bill-bishop-william-j-barber-jamie-raskin-low-wage-americans-supreme-court-rotunda-moral-mondays-repairers-of-the-breach', 'WJLA', '2025-06-30', 38.8898130, -77.0090208, 'U.S. Capitol grounds, Washington, DC'],
        // ── Statehouse · Sen. Capito's office sit-in, Charleston WV · Jun 25, 2025 ──
        ["Six arrested in a sit-in at Sen. Capito's office over SNAP and Medicaid cuts", 'https://www.wvgazettemail.com/news/legal_affairs/6-arrested-during-sit-in-at-capitos-office-to-protest-one-big-beautiful-bill/article_44438316-d7e9-4bf2-ac69-657e6a385196.html', 'WV Gazette-Mail', '2025-06-25', 38.3511946, -81.6382586, "Sen. Capito's office (500 Virginia St E), Charleston, WV"],
        // ── Statehouse · Texas Capitol redistricting (already pinned) · Aug 18, 2025 ──
        ['Four arrested refusing to leave the Texas Capitol over redistricting', 'https://www.fox7austin.com/news/four-protesters-arrested-after-refusing-leave-texas-capitol-building', 'FOX 7 Austin', '2025-08-18', 30.2746912, -97.7405171, 'Texas Capitol, Austin, TX'],
        // ── Statehouse · CT Capitol climate sit-in (already pinned) · Nov 17, 2025 ──
        ["Nine climate activists arrested in a sit-in at Gov. Lamont's office", 'https://ctnewsjunkie.com/2025/11/18/nine-arrested-after-ct-climate-activists-stage-sit-in-at-governors-office/', 'CT News Junkie', '2025-11-17', null, null, null],
        // ── Anti-ICE · ICE garage blockade, Center City Philadelphia · Mar 30, 2026 ──
        ['10 arrested blocking an ICE garage in Center City Philadelphia', 'https://www.inquirer.com/news/immigration-protest-ice-garage-center-city-philadelphia-20260330.html', 'The Philadelphia Inquirer', '2026-03-30', 39.9540235, -75.1530279, 'ICE garage, 8th & Cherry St, Center City, Philadelphia, PA'],

        // ── Policy · White House "pre-crime" counterterrorism strategy · May 6, 2026 ──
        ["White House 'pre-crime' counterterrorism strategy targets left-wing activists", 'https://www.kenklippenstein.com/p/insane-pre-crime-strategy-unveiled', 'Ken Klippenstein', '2026-05-06', 38.8976387, -77.0365528, 'White House, Washington, DC'],

        // ===== round 4: state-by-state fill (mostly No Kings rallies in unmapped states) =====
        // ── South Carolina · Charleston No Kings · Jun 14, 2025 ──
        ['Two arrested after the Charleston No Kings rally splinters into a march', 'https://www.counton2.com/news/local-news/video-shows-protesters-arrest-after-splintering-from-charleston-no-kings-rally/', 'WCBD News 2', '2025-06-14', 32.7861288, -79.9363609, 'Marion Square, Charleston, SC'],
        // ── Alabama · Fairhope No Kings · Oct 18, 2025 (charges later dropped) ──
        ['Woman in an inflatable costume arrested at the Fairhope No Kings protest', 'https://alabamareflector.com/2025/10/20/fairhope-police-arrest-woman-in-penis-costume-at-no-kings-protest/', 'Alabama Reflector', '2025-10-18', 30.5215729, -87.9012085, 'Fairhope, AL'],
        // ── Montana · Kalispell No Kings · Jun 14, 2025 ──
        ['Five arrested and one injured by a car at the Kalispell No Kings protest', 'https://dailyinterlake.com/news/2025/jun/17/five-arrested-and-one-injured-in-largely-peaceful-no-kings-protest-in-kalispell/', 'Daily Inter Lake', '2025-06-14', 48.1996415, -114.3133057, 'Depot Park, Kalispell, MT'],
        // ── Virginia · Richmond No Kings · Oct 18, 2025 ──
        ['Thousands flood Richmond for a No Kings rally at the Virginia Capitol', 'https://virginiamercury.com/2025/10/18/thousands-flood-richmond-streets-for-no-king-rally-in-protest-of-trump-administration/', 'Virginia Mercury', '2025-10-18', 37.5388175, -77.4335577, 'Virginia State Capitol, Richmond, VA'],
        // ── Louisiana · New Orleans No Kings · Oct 18, 2025 ──
        ['About 6,500 march through the Marigny for No Kings in New Orleans', 'https://www.nola.com/news/no-kings-louisiana/article_f5bd0b46-eab9-483a-bc9b-1d4045f3f4cc.html', 'NOLA.com', '2025-10-18', 29.9650386, -90.0573012, 'Washington Square Park (Marigny), New Orleans, LA'],
        // ── Maryland · Baltimore No Kings · Oct 18, 2025 ──
        ['Thousands rally in Baltimore for the No Kings day of protest', 'https://www.thebanner.com/politics-power/national-politics/no-kings-protest-baltimore-maryland-24ARQ7QOCBCHZD2Q5NZSKQQJWY/', 'The Baltimore Banner', '2025-10-18', 39.2855316, -76.6132279, 'McKeldin Square, Baltimore, MD'],
        // ── Mississippi · Jackson No Kings · Jun 14, 2025 ──
        ['About 1,500 surround the Mississippi Capitol for a No Kings protest', 'https://mississippitoday.org/2025/06/14/no-kings-mississippi-protests-donald-trump-jackson-gulfport/', 'Mississippi Today', '2025-06-14', 32.3038186, -90.1820767, 'Mississippi State Capitol, Jackson, MS'],
        // ── Kansas · Topeka No Kings · Jun 14, 2025 ──
        ['More than 5,000 rally at the Kansas Statehouse for No Kings', 'https://lawrencekstimes.com/2025/06/14/topeka-no-kings-protest/', 'Lawrence Times', '2025-06-14', 39.0481240, -95.6780165, 'Kansas State Capitol, Topeka, KS'],
        // ── Iowa · Des Moines student walkouts over an ICE detention · Sep 26, 2025 ──
        ['Des Moines students walk out over the ICE detention of their superintendent', 'https://www.nbcnews.com/news/us-news/des-moines-iowa-superintendent-ian-roberts-ice-detained-rcna234470', 'NBC News', '2025-09-26', 41.5910263, -93.6032079, 'Iowa State Capitol, Des Moines, IA'],
        // ── Arkansas · Little Rock DHS deportation hub · Nov 19, 2025 ──
        ["Daily anti-ICE protests outside Little Rock's DHS deportation hub", 'https://arktimes.com/arkansas-blog/2025/11/19/ices-little-rock-office-becomes-focus-of-protests-against-detentions-deportations-of-immigrants', 'Arkansas Times', '2025-11-19', 34.7214370, -92.2248431, 'DHS office, 4501 E Roosevelt Rd, Little Rock, AR'],
        // ── Alaska · Anchorage No Kings · Oct 18, 2025 ──
        ['Thousands pack Anchorage Town Square Park for No Kings', 'https://www.adn.com/alaska-news/anchorage/2025/10/18/no-kings-demonstrators-protest-in-downtown-anchorage/', 'Anchorage Daily News', '2025-10-18', 61.2171274, -149.8924689, 'Town Square Park, Anchorage, AK'],
        // ── North Dakota · Bismarck No Kings · Jun 14, 2025 ──
        ['Hundreds rally at the North Dakota Capitol for No Kings', 'https://bismarcktribune.com/news/state-regional/article_9d51f3b3-466d-45de-ae3f-3606c70a4c25.html', 'Bismarck Tribune', '2025-06-14', 46.8208186, -100.7830689, 'North Dakota State Capitol, Bismarck, ND'],
        // ── South Dakota · Sioux Falls No Kings · Oct 18, 2025 ──
        ['No Kings protesters take to the streets across South Dakota', 'https://southdakotasearchlight.com/briefs/enough-of-the-tyranny-no-kings-protesters-take-to-the-streets-in-south-dakota/', 'South Dakota Searchlight', '2025-10-18', 43.5460000, -96.7313000, 'downtown Sioux Falls, SD'],
        // ── Wyoming · Cheyenne No Kings · Jun 14, 2025 ──
        ['Hundreds crowd the Wyoming Capitol lawn for a No Kings protest', 'https://www.wyomingnews.com/news/local_news/hundreds-crowd-capitol-lawn-for-no-kings-protest/article_cce1b882-d070-4e1b-a893-0dbf8f48cc4b.html', 'Wyoming Tribune Eagle', '2025-06-14', 41.1402683, -104.8202289, 'Wyoming State Capitol, Cheyenne, WY'],
        // ── New Hampshire · Concord No Kings · Oct 18, 2025 ──
        ['No Kings day draws thousands to the New Hampshire State House', 'https://www.concordmonitor.com/2025/10/18/no-kings-day-protest-concord/', 'Concord Monitor', '2025-10-18', 43.2068627, -71.5381542, 'New Hampshire State House, Concord, NH'],
        // ── Rhode Island · Providence No Kings · Oct 18, 2025 ──
        ['Thousands rally at the Rhode Island State House for No Kings', 'https://www.golocalprov.com/news/PHOTOS-Thousands-Attend-No-Kings-Protest-at-Rhode-Island-State-House', 'GoLocalProv', '2025-10-18', 41.8308956, -71.4149587, 'Rhode Island State House, Providence, RI'],
        // ── Hawaii · Honolulu No Kings · Oct 18, 2025 ──
        ['Thousands gather in Honolulu for the No Kings day of protest', 'https://spectrumlocalnews.com/hi/hawaii/news/2025/10/19/honolulu-no-kings-demonstration-draws-thousands', 'Spectrum News Hawaii', '2025-10-18', 21.3073335, -157.8569903, 'Hawaii State Capitol, Honolulu, HI'],

        // ===== round 5: major political-prosecution & political-prisoner cases =====
        // ── Stop Cop City · RICO charges dismissed against all 61 defendants · Dec 30, 2025 ──
        ['Judge dismisses RICO charges against 61 Stop Cop City defendants', 'https://www.atlantanewsfirst.com/2025/12/31/racketeering-charges-brought-against-public-safety-training-center-protesters-dismissed/', 'Atlanta News First', '2025-12-30', 33.7499951, -84.3907337, 'Fulton County Courthouse, Atlanta, GA'],
        // ── Political prisoner · Mahmoud Khalil (Columbia) freed · Jun 20, 2025 ──
        ['Columbia activist Mahmoud Khalil freed from ICE detention over his Gaza activism', 'https://www.npr.org/2025/06/20/nx-s1-5440351/judge-orders-release-of-columbia-activist-mahmoud-khalil', 'NPR', '2025-06-20', 31.7080563, -92.1507019, 'Central Louisiana ICE Processing Center, Jena, LA'],
        // ── Political prisoner · Rümeysa Öztürk (Tufts) freed · May 9, 2025 ──
        ['Tufts student Rümeysa Öztürk freed from ICE detention over a Gaza op-ed', 'https://www.npr.org/2025/05/09/nx-s1-5393055/tufts-student-rumeysa-ozturk-ordered-freed-from-immigration-detention', 'NPR', '2025-05-09', 30.4827000, -92.5938000, 'South Louisiana ICE Processing Center, Basile, LA'],
        // ── Delaware · No Kings · Oct 18, 2025 (the last unmapped state) ──
        ['Thousands across Delaware rally for the No Kings day of protest', 'https://www.wdel.com/no-kings-2-0-rallies-set-for-saturday-in-delaware/article_cb54b54a-0518-4807-86d1-8aa78a347f85.html', 'WDEL', '2025-10-18', 39.1573179, -75.5197365, 'Legislative Hall, Dover, DE'],

        // ── Political prisoner · activist Jeanette Vizguerra freed from the Aurora ICE jail · Dec 22, 2025 ──
        ['Activist Jeanette Vizguerra freed from the Aurora ICE jail after nine months', 'https://coloradosun.com/2025/12/22/jeanette-vizguerra-released-ice/', 'The Colorado Sun', '2025-12-22', 39.7612260, -104.8505622, 'Aurora ICE Processing Center, Aurora, CO'],

        // ===== round 6: ICE detention-center resistance (overlooked local stories) =====
        // ── "Alligator Alcatraz" · Everglades, FL · Jun 28, 2025 ──
        ['Hundreds line the Tamiami Trail to oppose the Everglades "Alligator Alcatraz"', 'https://www.wlrn.org/environment/2025-06-28/demonstrators-line-tamiami-trail-everglades-to-oppose-alligator-alcatraz', 'WLRN', '2025-06-28', 25.8631663, -80.8984570, 'Everglades Detention Facility ("Alligator Alcatraz"), Ochopee, FL'],
        // ── Northwest ICE Processing Center · Tacoma, WA · Jun 6, 2025 ──
        ['200 union members rally at the Tacoma ICE jail for a detained machinist', 'https://labornotes.org/2025/06/iam-max-machinists-rally-for-member-detained-ice', 'Labor Notes', '2025-06-06', 47.2490911, -122.4224454, 'Northwest ICE Processing Center, Tacoma, WA'],
        // ── Camp East Montana · Fort Bliss, El Paso TX · Aug 17, 2025 ──
        ['Protesters rally as a new tent detention camp opens at Fort Bliss', 'https://abcnews.go.com/US/opening-new-migrant-detention-center-texas-met-protests/story?id=124760368', 'ABC News', '2025-08-17', 31.8329886, -106.3253286, 'Camp East Montana, Fort Bliss, El Paso, TX'],
        // ── South Texas Family Residential Center · Dilley, TX · Jan 28, 2026 ──
        ['Two arrested and tear-gassed at a protest outside the Dilley family detention center', 'https://www.ksat.com/news/local/2026/01/28/protesters-detained-hit-by-tear-gas-outside-dilley-family-detention-facility/', 'KSAT', '2026-01-28', 28.6564798, -99.2021906, 'South Texas Family Residential Center, Dilley, TX'],
        // ── Aurora ICE Processing Center · Aurora, CO · Jul 7, 2025 (facility already pinned) ──
        ['Lawmakers join a five-hour protest at the Aurora ICE detention center', 'https://sentinelcolorado.com/metro/local-leaders-join-hours-long-protest-against-ice-detention-center-in-aurora/', 'Sentinel Colorado', '2025-07-07', 39.7599698, -104.8490663, 'Aurora ICE center, CO'],

        // ===== round 7: protest trials, acquittals & charges dropped =====
        // ── Prosecution · Rep. LaMonica McIver ordered to trial · Nov 13, 2025 ──
        ['Rep. LaMonica McIver ordered to trial over the Delaney Hall ICE visit', 'https://www.cnn.com/2025/11/13/politics/lamonica-mciver-charges-new-jersey-immigration-detention-center', 'CNN', '2025-11-13', 40.7297710, -74.1723023, 'MLK Jr. Federal Building & Courthouse, Newark, NJ'],
        // ── Prosecution · charges dropped, Clovis CA ICE-protest escort · Apr 2026 ──
        ['Charges dropped against a man who escorted students to a Clovis ICE protest', 'https://calmatters.org/justice/2026/04/ice-protests-clovis-charges/', 'CalMatters', '2026-04-15', 36.8516317, -119.6934914, 'Clovis, CA'],
        // ── Prosecution · Newark Mayor Baraka trespassing charge dismissed · May 21, 2025 ──
        ['Trespassing charge dismissed against Newark Mayor Ras Baraka', 'https://www.washingtonpost.com/national-security/2025/05/21/mciver-baraka-newark-ice-court-charges/', 'The Washington Post', '2025-05-21', 40.7297710, -74.1723023, 'MLK Courthouse, Newark, NJ'],
        // ── Prosecution · Stop Cop City · Ayla King mistrial · Jul 7, 2025 ──
        ['Mistrial declared for the first Stop Cop City RICO defendant', 'https://www.11alive.com/article/news/crime/trials/public-safety-training-center-rico-case-speedy-trial-defendant-ayla-king-mistrial/85-6f96368d-c446-4a0a-b724-e6c2966817fd', '11Alive', '2025-07-07', 33.7499951, -84.3907337, 'Fulton County Courthouse, Atlanta, GA'],
        // ── Prosecution · Portland ICE protester acquitted by a jury · Mar 28, 2026 ──
        ['Portland ICE protester Angella Davis acquitted by a jury on all charges', 'https://www.kptv.com/2026/03/28/portland-ice-protester-acquitted-after-altercation-with-conservative-influencer-nick-sortor/', 'KPTV', '2026-03-28', 45.5157824, -122.6763053, 'Hatfield Courthouse, Portland, OR'],

        // ── Prosecution · Chicago jury acquits man of a Bovino murder-for-hire charge · Jan 22, 2026 ──
        ['Chicago man acquitted of a murder-for-hire plot against Border Patrol cmdr. Bovino', 'https://news.wttw.com/2026/01/22/chicago-man-acquitted-murder-hire-charge-plot-allegedly-targeting-border-patrol-chief', 'WTTW', '2026-01-22', 41.8788378, -87.6289654, 'Dirksen Courthouse, Chicago, IL'],
        // ── Prosecution · Fairhope "No Kings" costume case · acquitted · Apr 15, 2026 ──
        ['Fairhope "No Kings" costume protester Renea Gamble acquitted on all charges', 'https://www.fox10tv.com/2026/04/15/fairhope-woman-found-not-guilty-penis-costume-arrest-case/', 'FOX10 News', '2026-04-15', 30.5231647, -87.9028458, 'Fairhope, AL'],
        // ── Prosecution · Memphis No Kings charges dropped · Apr 16, 2026 ──
        ['Charges dropped against three arrested at the Memphis No Kings protest', 'https://wreg.com/news/charges-dropped-against-three-arrested-during-no-kings-protest/', 'WREG', '2026-04-16', 35.1489500, -90.0479460, 'Shelby County courthouse, Memphis, TN'],

        // ===== round 9: notable protests (no arrests) — Northeast =====
        ['Thousands rally at Niagara Square for the Buffalo No Kings protest', 'https://www.btpm.org/2025-06-16/no-kings-protests-draw-thousands-to-downtown-buffalo-and-across-wny', 'Buffalo Toronto Public Media', '2025-06-14', 42.8864213, -78.8781479, 'Niagara Square, Buffalo, NY'],
        ['Rochester residents rally against ICE crackdowns', 'https://spectrumlocalnews.com/nys/rochester/news/2025/06/22/rochester-residents-unite-against-ice-rally', 'Spectrum News', '2025-06-22', 43.1538368, -77.6020248, 'MLK Jr. Memorial Park, Rochester, NY'],
        ['About 8,000 line Erie Boulevard for the Syracuse-area No Kings rally', 'https://www.waer.org/news/2025-10-20/second-no-kings-rally-in-syracuse-region-draws-an-estimated-8-000-people-in-protest-of-president-donald-trump-and-republican-policies', 'WAER', '2025-10-18', 43.0556661, -76.0824890, 'Erie Blvd, DeWitt (Syracuse), NY'],
        ['Trans-rights die-in protests UPMC gender-affirming-care cuts', 'https://www.wesa.fm/health-science-tech/2025-09-08/protesters-die-in-upmc-elimination-gender-affirming-care-trans-youth', 'WESA', '2025-09-08', 40.4413772, -79.9947565, 'US Steel Tower (UPMC), Grant St, Pittsburgh, PA'],
        ['Hundreds gather in Perry Square for the Erie No Kings rally', 'https://www.erienewsnow.com/news/local/no-king-s-day-rally-takes-place-in-perry-square/article_165a9627-0260-4126-9ce1-abbd5963019e.html', 'Erie News Now', '2025-10-18', 42.1297009, -80.0845881, 'Perry Square, Erie, PA'],
        ['Thousands line Wolf Road for the Capital Region No Kings day', 'https://cbs6albany.com/news/local/protesters-rally-against-trump-on-no-kings-day-across-the-capital-region', 'CBS6 Albany', '2025-06-14', 42.7090300, -73.8219697, 'Wolf Road, Colonie (Albany), NY'],
        ['Hundreds gather for an ICE Out of Ithaca rally on the Commons', 'https://www.cornellsun.com/article/2026/01/hundreds-gather-for-ice-out-of-ithaca-rally-in-the-commons-joining-national-shutdown', 'Cornell Daily Sun', '2026-01-31', 42.4402739, -76.4969049, 'Bernie Milton Pavilion, Ithaca Commons, NY'],
        ['No Kings rally draws a crowd to Springwood Park in Asbury Park', 'https://tworivertimes.com/despite-downpour-crowd-turns-out-to-no-kings-rally-in-asbury-park/', 'Two River Times', '2025-06-14', 40.2154926, -74.0201295, 'Springwood Park, Asbury Park, NJ'],
        ['Hundreds bring the No Kings day to the New Jersey State House', 'https://www.tapinto.net/towns/east-windsor-hightstown/sections/central-southern-nj-news/articles/no-kings-day-protest-brings-hundreds-to-state-house-in-trenton-demostrators-say-nj-does-not-stand-for-cruelty', 'TAPinto', '2025-06-14', 40.2202256, -74.7701276, 'New Jersey State House, Trenton, NJ'],
        ['No Kings rally and march at Brookdale Park in Montclair', 'https://patch.com/new-jersey/montclair/another-no-kings-protest-will-take-place-montclair-here-s-when', 'Montclair Patch', '2026-03-28', 40.8345308, -74.1930483, 'Brookdale Park, Montclair, NJ'],
        ['More than 3,000 fill the Soldiers and Sailors Monument for No Kings', 'https://manchester.inklink.news/thousands-in-new-hampshire-join-in-national-no-kings-protests/', 'Manchester Ink Link', '2025-06-14', 42.7657544, -71.4677556, 'Soldiers and Sailors Monument, Nashua, NH'],
        ['No Kings rally gathers at the Stamford courthouse', 'https://patch.com/connecticut/stamford/no-kings-protest-planned-weekend-stamford', 'Stamford Patch', '2025-10-18', 41.0606525, -73.5348452, 'Stamford Superior Court, Stamford, CT'],
        ['Hundreds rally against ICE at the Providence federal building', 'https://www.bostonglobe.com/2025/06/09/metro/providence-ri-protest-ice-immigration-raids/', 'The Boston Globe', '2025-06-09', 41.8268000, -71.4118000, 'Providence Federal Center, Providence, RI'],
        ['No Kings III draws about 1,000 to Court Square in Springfield', 'https://thereminder.com/local-news/hampden-county/springfield/no-kings-iii-draws-smaller-still-determined-crowd-in-springfield/', 'The Reminder', '2026-03-28', 42.1000576, -72.5891174, 'Court Square, Springfield, MA'],
        ['Vermonters rally at City Hall after South Burlington ICE arrests', 'https://www.vermontpublic.org/local-news/2026-03-13/vermonters-rally-after-south-burlington-ice-arrests-protests', 'Vermont Public', '2026-03-13', 44.4761767, -73.2128553, 'Burlington City Hall, Burlington, VT'],

        // ===== round 9: notable protests (no arrests) — Southeast =====
        ['No Kings march fills Hampton Boulevard in Norfolk', 'https://www.13newsnow.com/article/news/politics/no-kings-protest-demonstration-virginia-updates/291-d9d775e9-d0fb-43a3-ae31-9927712eb863', '13News Now', '2025-06-14', 36.8867000, -76.3045000, 'Hampton Blvd, Norfolk, VA'],
        ['Hundreds rally at Falls Park demanding immigration accountability', 'https://www.yahoo.com/news/articles/greenville-protest-calls-accountability-immigration-202549241.html', 'Greenville News', '2026-02-16', 34.8453023, -82.4009997, 'Falls Park, Greenville, SC'],
        ['Hundreds protest a near-total abortion ban at the SC State House', 'https://www.dailygamecock.com/gallery/68dd827f1dd69', 'The Daily Gamecock', '2025-10-01', 34.0003435, -81.0330519, 'South Carolina State House, Columbia, SC'],
        ['Workers Over Billionaires Labor Day rally in Savannah', 'https://www.wtoc.com/2025/09/01/workers-over-billionaires-rally-savannah/', 'WTOC', '2025-09-01', 32.0798065, -81.0858640, 'Emmet Park, Savannah, GA'],
        ['Old Capitol rally against a mass ICE raid on a construction site', 'https://www.wctv.tv/2025/05/31/its-unfair-more-than-150-rally-capital-city-protest-unjust-immigration-raids/', 'WCTV', '2025-05-30', 30.4381654, -84.2813500, 'Florida Historic Capitol, Tallahassee, FL'],
        ['Atlanta VA nurses and veterans protest mental-health staffing cuts', 'https://www.atlantanewsfirst.com/2025/08/12/atlanta-va-nurses-protest-mental-health-cuts/', 'Atlanta News First', '2025-08-12', 33.8018044, -84.3117000, 'Atlanta VA Medical Center, Decatur, GA'],
        ['Student-led march against ICE and Border Patrol raids in Durham', 'https://www.wral.com/news/local/protests-ice-border-patrol-durham-november-2025/', 'WRAL', '2025-11-21', 35.9966810, -78.9018737, 'CCB Plaza, Durham, NC'],
        ['Hundreds protest after the ICE shooting of Renee Good in Lexington', 'https://www.wkyt.com/2026/01/12/hundreds-protest-ice-actions-downtown-lexington/', 'WKYT', '2026-01-11', 38.0497000, -84.4960000, 'Fayette County courthouse, Lexington, KY'],
        ['Hundreds rally at Orlando City Hall over the ICE killing of Renee Good', 'https://www.orlandoweekly.com/news/hundreds-rally-at-orlando-city-hall-to-protest-ice-killing-renee-good/', 'Orlando Weekly', '2026-01-11', 28.5375894, -81.3796974, 'Orlando City Hall, FL'],
        ['PSA Airlines flight attendants picket over low pay at Charlotte airport', 'https://www.wsoctv.com/news/local/psa-workers-protest-higher-wages-charlotte-douglas/FCWEV6XFFNHNJA7SSJKCYHJVAU/', 'WSOC-TV', '2025-08-18', 35.2107415, -80.9457435, 'Charlotte Douglas International Airport, NC'],
        ['Hundreds stage a die-in against ICE in Wilmington', 'https://www.whqr.org/local/2026-02-03/hundreds-of-wilmington-residents-die-in-as-part-of-a-nationwide-protest-against-federal-immigration-enforcement', 'WHQR', '2026-01-30', 34.2050891, -77.9210562, 'New Hanover Regional Medical Center, Wilmington, NC'],
        ['No Kings rally fills Washington Park in Roanoke', 'https://www.wsls.com/news/local/2025/10/18/roanoke-protestors-raise-their-voices-against-the-trump-administration-during-no-kings-rally/', 'WSLS 10', '2025-10-18', 37.2862990, -79.9455755, 'Washington Park, Roanoke, VA'],

        // ===== round 9: notable protests (no arrests) — Midwest =====
        ['No Kings rally fills Cleveland Public Square', 'https://www.news5cleveland.com/news/local-news/live-updates-no-kings-protests-in-northeast-ohio', 'News 5 Cleveland', '2025-06-14', 41.4996470, -81.6936673, 'Public Square, Cleveland, OH'],
        ['Hundreds rally against ICE at Fountain Square after an ICE shooting', 'https://www.citybeat.com/news/photos-hundreds-gather-at-fountain-square-for-ice-protest/', 'Cincinnati CityBeat', '2026-01-08', 39.1014843, -84.5125001, 'Fountain Square, Cincinnati, OH'],
        ['Thousands pack Courthouse Square for the Dayton No Kings protest', 'https://dayton247now.com/news/local/hundreds-gather-in-dayton-for-no-kings-protest', 'Dayton 24/7 Now', '2025-10-18', 39.7594000, -84.1917000, 'Courthouse Square, Dayton, OH'],
        ['Hundreds pack Promenade Park for the Toledo No Kings rally', 'https://www.toledoblade.com/local/politics/2025/06/14/no-kings-rally-promenade-park-summit-street-ohio-trump/stories/20250614109', 'The Blade', '2025-06-14', 41.6503453, -83.5332719, 'Promenade Park, Toledo, OH'],
        ['Several thousand rally at Clark Park for the Detroit No Kings march', 'https://www.cbsnews.com/detroit/news/no-kings-protests-detroit-michigan-june-14/', 'CBS News Detroit', '2025-06-14', 42.3142000, -83.1085000, 'Clark Park, Detroit, MI'],
        ['Cosecha rallies against ICE at Calder Plaza in Grand Rapids', 'https://wwmt.com/news/local/immigration-enforcement-rally-against-ice-grand-rapids-movimiento-cosecha-president-trump-grand-rapids-rapid-response-western-michigan-protest', 'WWMT Newschannel 3', '2025-06-17', 42.9688629, -85.6707020, 'Calder Plaza, Grand Rapids, MI'],
        ['Trans-rights protest over the end of gender-affirming care at U-M', 'https://www.michigandaily.com/news/administration/it-is-the-ultimate-betrayal-umich-and-ann-arbor-community-reflects-on-impacts-of-end-to-gender-affirming-care/', 'The Michigan Daily', '2025-09-11', 42.2829000, -83.7264000, 'University of Michigan Hospital, Ann Arbor, MI'],
        ['More than 3,000 rally at the Indiana Statehouse for No Kings', 'https://indianacitizen.org/tsf-story-protestors-rally-at-statehouse-for-no-kings-2-0/', 'The Indiana Citizen', '2025-10-18', 39.7677059, -86.1616073, 'Indiana Statehouse, Indianapolis, IN'],
        ['Thousands gather at the Allen County Courthouse for No Kings', 'https://www.21alivenews.com/2025/06/14/fort-wayne-joins-nationwide-day-action-during-no-kings-protest-saturday/', '21Alive News', '2025-06-14', 41.0798509, -85.1393709, 'Allen County Courthouse, Fort Wayne, IN'],
        ['Hundreds march from Cathedral Square against Milwaukee ICE arrests', 'https://www.wpr.org/news/hundreds-protest-milwaukee-ice-arrests-trump-military-immigration-raids', 'Wisconsin Public Radio', '2025-06-10', 43.0419498, -87.9050083, 'Cathedral Square Park, Milwaukee, WI'],
        ['UW students rally at Library Mall over Gaza ceasefire violations', 'https://www.dailycardinal.com/article/2025/10/protesters-rally-against-israel-ceasefire-violations', 'The Daily Cardinal', '2025-10-23', 43.0758000, -89.3996000, 'Library Mall, UW-Madison, WI'],
        ['About 1,500 fill Leicht Park for the Green Bay No Kings protest', 'https://fox11online.com/news/local/no-kings-protest-to-be-held-in-green-bay-northeast-wisconsin-saturday', 'FOX 11 News', '2025-06-14', 44.5195634, -88.0161265, 'Leicht Memorial Park, Green Bay, WI'],
        ['Thousands line 8th Avenue for the Cedar Rapids No Kings protest', 'https://www.kcrg.com/2025/06/14/thousands-gather-no-kings-protest-downtown-cedar-rapids/', 'KCRG-TV9', '2025-06-14', 41.9759000, -91.6668000, '8th Avenue, downtown Cedar Rapids, IA'],
        ['About 200 march from Union Station against ICE in St. Louis', 'https://www.stlpr.org/government-politics-issues/2025-06-11/photos-st-louis-ice-national-guard-protest-immigration', 'St. Louis Public Radio', '2025-06-11', 38.6281219, -90.2080262, 'Union Station, St. Louis, MO'],
        ['Thousands rally at Country Club Plaza for the Kansas City No Kings', 'https://www.kcur.org/news/2025-10-18/no-kings-protests-kansas-city-2', 'KCUR', '2025-10-18', 39.0420441, -94.5927959, 'Country Club Plaza, Kansas City, MO'],
        ['Thousands fill Turner Park for the Omaha No Kings rally', 'https://www.wowt.com/2025/10/18/protestors-sound-off-turner-park-saturday-omahas-no-kings-rally/', 'WOWT 6 News', '2025-10-18', 41.2587494, -95.9601692, 'Turner Park, Omaha, NE'],
        ['Thousands rally at Fargo City Hall for the No Kings day of defiance', 'https://www.inforum.com/news/fargo/fargo-no-kings-event-draws-thousands', 'InForum', '2025-06-14', 46.8785490, -96.7828916, 'Fargo City Hall, Fargo, ND'],
        ['About 3,500 crowd Lake Ave and Superior St for the Duluth No Kings rally', 'https://www.northernnewsnow.com/2025/06/15/hundreds-protesters-crowd-lake-avenue-superior-street-no-kings-rally/', 'Northern News Now', '2025-06-14', 46.7798329, -92.0925170, 'Lake Ave & Superior St, Duluth, MN'],

        // ===== round 9: notable protests (no arrests) — South Central + Mountain =====
        ['About 15,000 march on No Kings Day in downtown Houston', 'https://www.click2houston.com/news/local/2025/06/09/downtown-houston-no-kings-immigration-protest-to-counter-military-parade-on-trumps-79th-birthday/', 'KPRC Click2Houston', '2025-06-14', 29.7601790, -95.3693747, 'Houston City Hall, TX'],
        ['Anti-ICE protesters fill downtown San Antonio near the National Guard', 'https://www.tpr.org/border-immigration/2025-06-11/anti-ice-protestors-fill-downtown-san-antonio-streets-as-national-guard-troops-gather-nearby', 'Texas Public Radio', '2025-06-11', 29.4245815, -98.4951255, 'San Antonio City Hall, TX'],
        ['March against ICE detentions and anti-LGBTQ policies in San Antonio', 'https://www.ksat.com/news/local/2025/08/02/protest-against-ice-detainments-anti-lgbtq-policies-to-be-held-downtown/', 'KSAT', '2025-08-02', 29.4281053, -98.4891496, 'Travis Park, San Antonio, TX'],
        ['About 6,500 rally at Burk Burnett Park for the Fort Worth No Kings', 'https://fortworthreport.org/2025/10/18/thousands-protest-in-downtown-fort-worth-for-no-kings-rally/', 'Fort Worth Report', '2025-10-18', 32.7531000, -97.3356000, 'Burk Burnett Park, Fort Worth, TX'],
        ['LGBTQ Texans rally at the Capitol against anti-trans bills', 'https://www.kut.org/politics/2025-05-09/texas-lgbtq-rally-capitol-equality', 'KUT Austin', '2025-05-09', 30.2746912, -97.7405171, 'Texas Capitol, Austin, TX'],
        ['Over 1,500 rally at the Tulsa County Courthouse for No Kings', 'https://www.kjrh.com/news/local-news/protestors-gather-at-no-kings-demonstrations-across-tulsa', 'KJRH 2 News', '2025-06-14', 36.1495026, -95.9939171, 'Tulsa County Courthouse, Tulsa, OK'],
        ['Thousands march from Scissortail Park to OKC City Hall for No Kings', 'https://freepressokc.com/no-kings-protest-draws-1000s-to-downtown-oklahoma-city/', 'Free Press OKC', '2025-06-14', 35.4690000, -97.5195000, 'Oklahoma City Hall, OK'],
        ['Thousands line the Broadway Bridge for the Little Rock No Kings protest', 'https://www.ualrpublicradio.org/local-regional-news/2025-06-14/thousands-take-to-the-streets-in-little-rock-no-kings-protest', 'Little Rock Public Radio', '2025-06-14', 34.7499803, -92.2748844, 'Broadway Bridge, Little Rock, AR'],
        ['Thousands march to the Roundhouse for No Kings Day in Santa Fe', 'https://sourcenm.com/2025/10/18/thousands-protest-in-santa-fe-albuquerque-for-no-kings-day/', 'Source New Mexico', '2025-10-18', 35.6823019, -105.9396340, 'New Mexico State Capitol (Roundhouse), Santa Fe, NM'],
        ['About 3,000 gather at Albert Johnson Park for the Las Cruces No Kings', 'https://www.krwg.org/krwg-news/2025-10-18/thousands-showed-up-for-no-kings-protest-in-las-cruces', 'KRWG Public Media', '2025-10-18', 32.3156888, -106.7797806, 'Albert Johnson Park, Las Cruces, NM'],
        ['About 20,000 endure the heat at the Phoenix No Kings rally', 'https://azcapitoltimes.com/news/2025/06/14/phoenix-no-kings-rally-draws-thousands-of-peaceful-protesters-despite-extreme-heat/', 'Arizona Capitol Times', '2025-06-14', 33.4481378, -112.0941698, 'Wesley Bolin Memorial Plaza, Phoenix, AZ'],
        ['Thousands march for the Reno No Kings rally', 'https://www.kolotv.com/2025/10/20/thousands-show-up-no-kings-rally-reno/', 'KOLO 8 News', '2025-10-18', 39.5215393, -119.8102973, 'Bruce R. Thompson Federal Courthouse, Reno, NV'],
        ['No Kings rally draws a crowd to the Ogden Municipal Building', 'https://www.fox13now.com/news/local-news/where-to-participate-in-no-kings-protests-across-utah-on-saturday', 'Fox 13 Salt Lake', '2025-10-18', 41.2195603, -111.9711966, 'Ogden Municipal Building, Ogden, UT'],
        ['About 2,500 fill the courthouse lawn for the Billings No Kings rally', 'https://www.ktvq.com/news/local-news/no-kings-protest-draws-large-crowd-in-downtown-billings', 'KTVQ Q2', '2025-06-14', 45.7844147, -108.5056684, 'Yellowstone County Courthouse, Billings, MT'],
        ['About 10,000 march downtown for the Missoula No Kings rally', 'https://missoulacurrent.com/no-kings-missoula/', 'Missoula Current', '2025-10-18', 46.8732919, -113.9959132, 'Missoula County Courthouse, Missoula, MT'],
        ['Hundreds rally at Healing Park for the Casper No Kings protest', 'https://oilcity.news/politics/2025/06/14/photos-hundreds-gather-for-no-kings-protest-in-caspers-healing-park-on-saturday/', 'Oil City News', '2025-06-14', 42.8497000, -106.3247000, 'Healing Park, Casper, WY'],

        // ===== round 9: notable protests (no arrests) — West + Pacific =====
        ['No Kings rally draws thousands to the California State Capitol', 'https://www.cbsnews.com/sacramento/news/no-kings-protest-sacramento-northern-california/', 'CBS Sacramento', '2025-06-14', 38.5766749, -121.4937139, 'California State Capitol, Sacramento, CA'],
        ['Hundreds rally against ICE raids at San Jose City Hall', 'https://abc7news.com/post/san-jose-protest-hundreds-gather-peaceful-resistance-ice-raids/16710736/', 'ABC7 News', '2025-06-10', 37.3380339, -121.8853233, 'San Jose City Hall, CA'],
        ['No Kings march fills Frank Ogawa Plaza in Oakland', 'https://www.indybay.org/newsitems/2025/06/03/18876977.php', 'Indybay', '2025-06-14', 37.8051719, -122.2718787, 'Frank Ogawa Plaza, Oakland, CA'],
        ['No Kings protest draws thousands to Fresno', 'https://fresnoland.org/2025/06/14/no-kings-protests-in-fresno-draw-thousands/', 'Fresnoland', '2025-06-14', 36.8077000, -119.8002000, 'Cary Park, Fresno, CA'],
        ['Thousands flood downtown Riverside for the No Kings protest', 'https://riversiderecord.org/thousands-flood-downtown-riverside-streets-as-part-of-nationwide-no-kings-protests/', 'The Riverside Record', '2025-06-14', 33.9806000, -117.3728000, 'Market St & University Ave, Riverside, CA'],
        ['Nearly 200 march against ICE detention conditions in Bakersfield', 'https://www.turnto23.com/news/in-your-neighborhood/bakersfield/nearly-200-protesters-march-against-ice-detention-conditions-in-largest-demonstration-in-four-years', 'KERO 23ABC', '2025-09-27', 35.3711000, -119.0173000, 'Bakersfield Museum of Art, CA'],
        ['Hundreds rally at Oxnard City Hall against farm immigration raids', 'https://www.nbclosangeles.com/news/local/protesters-rally-in-oxnard-in-opposition-of-immigration-raids-at-agricultural-fields/3720868/', 'NBC Los Angeles', '2025-06-10', 34.2007238, -119.1798266, 'Oxnard City Hall, CA'],
        ['Workers Over Billionaires Labor Day rally at Long Beach City Hall', 'https://www.cbsnews.com/losangeles/news/labor-day-parade-rallies-take-place-across-southern-california/', 'CBS Los Angeles', '2025-09-01', 33.7682353, -118.1974649, 'Long Beach City Hall, CA'],
        ['Thousands rally for democracy at the San Diego No Kings', 'https://timesofsandiego.com/politics/2025/10/18/thousands-bang-drum-democracy-no-kings-rallies-san-diego/', 'Times of San Diego', '2025-10-18', 32.7204690, -117.1716401, 'Waterfront Park, San Diego, CA'],
        ['No Kings protest draws 10,000 to downtown Eugene', 'https://lookouteugene-springfield.com/story/latest-news/2025/06/14/no-kings-protest-draws-10000-demonstrators-to-downtown-eugene/', 'Lookout Eugene-Springfield', '2025-06-14', 44.0517615, -123.0857715, 'Wayne Morse U.S. Courthouse, Eugene, OR'],
        ['Protesters swarm the Oregon Capitol for the No Kings demonstration', 'https://www.salemreporter.com/2025/06/14/protesters-swarm-to-downtown-salem-to-join-no-kings-demonstration-streets-closed/', 'Salem Reporter', '2025-06-14', 44.9382743, -123.0307767, 'Oregon State Capitol, Salem, OR'],
        ['Thousands gather at Drake Park for the Bend No Kings rally', 'https://bendbulletin.com/2025/06/14/thousands-gather-in-bend-as-part-of-nationwide-no-kings-movement/', 'The Bulletin', '2025-06-14', 44.0576868, -121.3199292, 'Drake Park, Bend, OR'],
        ['No Kings rally draws about 5,000 to the Washington State Capitol', 'https://www.king5.com/article/news/local/thousands-rally-state-capitol-olympia-no-kings-protest/281-34253521-dd14-438e-94b6-1a8e9ffb2b6d', 'KING 5', '2025-06-14', 47.0357617, -122.9048521, 'Washington State Capitol, Olympia, WA'],
        ['Thousands join the No Kings protest at Maritime Heritage Park', 'https://www.cascadiadaily.com/2025/oct/18/thousands-of-bellingham-residents-join-nationwide-no-kings-protest/', 'Cascadia Daily News', '2025-10-18', 48.7541366, -122.4826147, 'Maritime Heritage Park, Bellingham, WA'],
        ['No Kings march draws several thousand to the Vancouver Waterfront', 'https://www.camaspostrecord.com/news/2025/jun/19/vancouver-crowds-call-for-no-kings/', 'Camas-Washougal Post-Record', '2025-06-14', 45.6228825, -122.6776300, 'Vancouver Waterfront Park, Vancouver, WA'],
        ['Pro-Palestine, anti-war rally at the Red Wagon in Spokane', 'https://www.spokesman.com/stories/2025/jun/22/protesters-rally-at-red-wagon-against-iran-war/', 'The Spokesman-Review', '2025-06-22', 47.6610000, -117.4222000, 'Riverfront Park (Red Wagon), Spokane, WA'],
        ['Pro-Ukraine demonstrators protest the Trump-Putin summit at a JBER gate', 'https://alaskapublic.org/news/anchorage/2025-08-15/from-simmering-rage-to-psilocybin-alaskans-share-thoughts-on-trump-putin-visit', 'Alaska Public Media', '2025-08-15', 61.2517077, -149.6941751, 'JBER, Anchorage, AK'],
        ['No Kings rally draws 2,500 along the Hilo Bayfront', 'https://bigislandnow.com/2025/10/18/no-kings-rally-in-hilo-draws-2500-as-part-of-nationwide-protest-of-trump-and-his-administration/', 'Big Island Now', '2025-10-18', 19.7250000, -155.0860000, 'Kamehameha Ave (Bayfront), Hilo, HI'],

        // ===== round 10: notable no-arrest protests — new topics & small towns =====
        // Public lands · veterans · housing · gun violence · anti-war
        ['We Love Public Lands rally draws about 2,000 against a land sell-off', 'https://sourcenm.com/2025/06/23/public-lands-protesters-picket-western-governors-conference/', 'Source New Mexico', '2025-06-23', 35.6866327, -105.9438868, 'De Vargas Park, Santa Fe, NM'],
        ['Navajo activists protest a BLM oil and gas lease sale to protect Dinetah', 'https://sourcenm.com/2025/11/05/native-activists-protest-more-oil-and-gas-drilling-in-the-greater-chaco-landscape/', 'Source New Mexico', '2025-11-05', 35.5985669, -106.0380320, 'BLM state office, Santa Fe, NM'],
        ['Veterans and military families occupy a House office over the Iran war; 66 arrested', 'https://gvwire.com/2026/04/21/dozens-arrested-after-veterans-military-families-occupy-capitol-building-in-iran-war-protest/', 'GV Wire', '2026-04-21', 38.8866421, -77.0073587, 'Cannon House Office Building, Washington, DC'],
        ['Public-housing tenants rally and march to the mayor\'s office over conditions', 'https://missionlocal.org/2025/07/s-f-public-housing-tenants-rally-at-city-hall-urging-mayor-lurie-to-step-up/', 'Mission Local', '2025-06-15', 37.7792929, -122.4192601, 'San Francisco City Hall, CA'],
        ['Southwest Philadelphia residents rally to save 925 affordable-housing units', 'https://gridphilly.com/blog-home/2026/06/01/southwest-philly-residents-rally-to-save-affordable-housing/', 'Grid Magazine', '2026-06-01', 39.9432931, -75.2149880, 'Kingsessing, Philadelphia, PA'],
        ['Wear Orange rally honors gun-violence victims on Gun Violence Awareness Day', 'https://www.alxnow.com/2025/06/02/moms-demand-action-hosting-wear-orange-rally-against-gun-violence-in-old-town/', 'ALXnow', '2025-06-06', 38.8047219, -77.0433232, 'Market Square, Alexandria, VA'],
        ['Moms Demand Action Wear Orange rally against gun violence at the courthouse', 'https://www.hendersonvillelightning.com/news/15160-demonstrators-wear-orange-to-protest-gun-violence.html', 'Hendersonville Lightning', '2025-06-06', 35.3145264, -82.4604151, 'Historic Courthouse, Hendersonville, NC'],
        ['Dozens rally at City Hall against US military action in Iran', 'https://www.newhavenindependent.org/2026/03/01/anti-war-demonstrators-rally-downtown/', 'New Haven Independent', '2026-03-01', 41.3073780, -72.9243304, 'New Haven City Hall, CT'],

        // Science · education · healthcare · Medicaid · SNAP · town halls
        ['Stand Up for Science Take Back Science rally fills Robinson Park', 'https://www.kunm.org/local-news/2026-03-05/stand-up-for-science-albuquerque-rally', 'KUNM', '2026-03-07', 35.0857518, -106.6567306, 'Robinson Park, Albuquerque, NM'],
        ['Hundreds join a faculty-staff walkout over budget cuts at Middlebury', 'https://www.middleburycampus.com/article/2025/05/hundreds-attend-faculty-staff-led-walkout-to-protest-budget-cuts', 'The Middlebury Campus', '2025-05-08', 44.0036696, -73.1729528, 'Old Chapel, Middlebury College, VT'],
        ['Hundreds of NC State students march against ICE from the Memorial Belltower', 'https://ncnewsline.com/2025/11/21/on-nc-states-campus-hundreds-of-students-protest-as-fears-of-ice-persist/', 'NC Newsline', '2025-11-21', 35.7718497, -78.6740870, 'Memorial Belltower, NC State, Raleigh, NC'],
        ['FIU students and faculty protest a 287(g) ICE agreement at Graham Center', 'https://panthernow.com/2025/09/19/fiu-students-and-faculty-protest-ice-agreement-during-bot-meeting/', 'PantherNOW', '2025-09-18', 25.7553898, -80.3762833, 'Graham Center, FIU, Miami, FL'],
        ['High Noon for Healthcare rally against Medicaid cuts at Jamaica Hospital', 'https://qns.com/2025/06/healthcare-workers-join-rally-against-medicaid-cut/', 'QNS', '2025-06-23', 40.6937510, -73.7813148, 'Jamaica Hospital, Queens, NY'],
        ['Healthcare workers and immigrants rally against Medicaid cuts at Jackson Memorial', 'https://www.wlrn.org/south-florida/2025-07-25/trump-bill-medicaid-protests-immigrants-healthcare-workers', 'WLRN', '2025-07-26', 25.7916705, -80.2126015, 'Jackson Memorial Hospital, Miami, FL'],
        ['Enloe Health nurses rally against a home-health and hospice unit closure', 'https://www.nationalnursesunited.org/press/chico-nurses-at-enloe-health-to-hold-rally-to-protest-closure', 'National Nurses United', '2025-10-02', 39.7424302, -121.8502008, 'Enloe Health, Chico, CA'],
        ['Nurses rally over a maternity-unit closure at Research Medical Center', 'https://www.nationalnursesunited.org/press/nurses-at-research-medical-center-to-hold-rally-demanding-immediate-action-on-unsafe-patient-care-conditions', 'National Nurses United', '2026-06-04', 39.0072154, -94.5589343, 'Research Medical Center, Kansas City, MO'],
        ['Planned Parenthood Rage Against the Regime rally and human chain', 'https://www.wral.com/news/local/rage-against-the-regime-human-chain-raleigh-protest-aug-2025/', 'WRAL', '2025-08-02', 35.8404202, -78.6800216, 'Halifax Mall, Raleigh, NC'],
        ['Constituents protest SNAP and Medicaid cuts outside Rep. LaHood\'s Normal office', 'https://www.25newsnow.com/2025/05/15/dozens-gather-normal-outside-lahoods-office-protest-potential-federal-cuts-snap-medicare-medicaid/', '25 News Now', '2025-05-15', 40.5094554, -88.9839365, 'Rep. LaHood office, Normal, IL'],
        ['Rep. Mike Flood booed over the budget bill and Medicaid at a Lincoln town hall', 'https://nebraskapublicmedia.org/en/news/news-articles/congressman-flood-met-with-boisterous-opposition-over-big-beautiful-bill-at-lincoln-town-hall/', 'Nebraska Public Media', '2025-08-04', 40.8166706, -96.7050894, 'Kimball Recital Hall, UNL, Lincoln, NE'],
        ['Protesters heckle Rep. Crenshaw over Medicaid cuts at a Kingwood town hall', 'https://reduceflooding.com/2025/08/30/protesters-disrupt-crenshaw-town-hall-in-kingwood-on-harveys-8th-anniversary/', 'Reduce Flooding', '2025-08-28', 30.0544429, -95.1851032, 'Kingwood Community Center, Houston, TX'],
        ['Rep. Bryan Steil booed over tariffs and Medicaid at a Wisconsin town hall', 'https://abcnews.go.com/Politics/republican-rep-bryan-steil-booed-defending-trump-tariffs/story?id=124274659', 'ABC News', '2025-07-31', 42.6716982, -88.5313407, 'Elkhorn Area High School, Elkhorn, WI'],
        ['NIH staff hold a weekly funeral vigil over research cuts and layoffs', 'https://cancerletter.com/the-cancer-letter/20250703_3/', 'The Cancer Letter', '2025-06-07', 38.9988498, -77.0968366, 'Medical Center Metro, Bethesda, MD'],
        ['CDC workers walk out and demand RFK Jr. resign over the ACIP firings', 'https://www.npr.org/2025/06/10/nx-s1-5425477/former-cdc-employees-protest-against-dismantling-of-public-health', 'NPR', '2025-06-10', 33.7811778, -84.3230951, 'CDC Roybal Campus, Atlanta, GA'],
        ['Rally for SNAP demands food benefits be funded amid the shutdown', 'https://projectbread.org/news/recap-of-the-rally-for-snap-hunger-is-not-a-choice-but-inaction-is', 'Project Bread', '2025-10-28', 42.3586020, -71.0638751, 'Massachusetts State House, Boston, MA'],
        ['Hands Off Our Food Stamps rally and food drive over the SNAP lapse', 'https://www.news10.com/news/food-is-a-human-right-protestors-demand-full-snap-benefits-at-albany-rally/', 'WTEN News10', '2025-11-09', 42.6576772, -73.7646942, 'Townsend Park, Albany, NY'],

        // Small-town No Kings · Good Trouble · Pride · vigils
        ['No Kings rally lines Main Street across from Library Square in Hutchinson', 'https://dailyyonder.com/rural-minnesotans-join-nationwide-no-kings-protests/2025/10/22/', 'Daily Yonder', '2025-10-18', 44.8917051, -94.3685834, 'Library Square, Hutchinson, MN'],
        ['About 2,000 pack Lincoln Square for a No Kings protest in Gettysburg', 'https://gettysburgconnection.org/thousands-in-gettysburg-join-rallies-across-the-country-to-declare-no-kings/', 'Gettysburg Connection', '2025-06-14', 39.8304401, -77.2307883, 'Lincoln Square, Gettysburg, PA'],
        ['No Kings march fills Bayfront Park and downtown Petoskey', 'https://www.interlochenpublicradio.org/2025-06-14/thousands-gather-across-northern-michigan-for-no-kings-protests', 'Interlochen Public Radio', '2025-06-14', 45.3748013, -84.9638106, 'Bayfront Park, Petoskey, MI'],
        ['No Kings rally fills the Jackson County Courthouse lawn in rural Maquoketa', 'https://www.iowapublicradio.org/ipr-news/2025-10-20/rural-iowans-protest-trump-no-kings-rally', 'Iowa Public Radio', '2025-10-18', 42.0688912, -90.6673147, 'Jackson County Courthouse, Maquoketa, IA'],
        ['No Kings rally at the DeSoto County Courthouse in Hernando', 'https://magnoliatribune.com/2025/06/11/seven-no-kings-protests-against-trump-planned-in-mississippi-saturday-part-of-nationwide-effort/', 'Magnolia Tribune', '2025-06-14', 34.8230336, -89.9948960, 'DeSoto County Courthouse, Hernando, MS'],
        ['No Kings demonstrators line the Snake River bridge in tiny Alpine', 'https://wyofile.com/no-kings-rallies-come-to-rural-conservative-wyoming/', 'WyoFile', '2025-06-14', 43.1636455, -111.0178761, 'Snake River bridge, Alpine, WY'],
        ['Good Trouble Lives On rally fills Veterans Memorial Park in Sierra Vista', 'https://www.kgun9.com/news/community-inspired-journalism/cochise-county/showing-solidarity-hundreds-gather-in-sierra-vista-for-good-trouble-lives-on-protest', 'KGUN9', '2025-07-17', 31.5565447, -110.2679161, 'Veterans Memorial Park, Sierra Vista, AZ'],
        ['Good Trouble rally draws 150+ to the Middlesex Historic Courthouse', 'https://www.ssentinel.com/good-trouble-lives-on-rally-draws-scores-to-old-courthouse/', 'Southside Sentinel', '2025-07-17', 37.6066088, -76.5946726, 'Middlesex Historic Courthouse, Saluda, VA'],
        ['Good Trouble Lives On protest at the Morris Township municipal building', 'https://morristowngreen.com/2025/07/07/good-trouble-lives-on-protest-of-trump-civil-and-human-rights-policies-planned-for-morris-township-july-17/', 'Morristown Green', '2025-07-17', 40.7811825, -74.4632736, 'Morris Township municipal building, NJ'],
        ['First Amherst Pride march heads down North Pleasant Street to the town common', 'https://theamherstcurrent.org/2025/06/03/first-amherst-pride-march-and-rally-coming-on-june-22/', 'The Amherst Current', '2025-06-22', 42.3727442, -72.5193091, 'Amherst town common, MA'],
        ['Candlelight vigil for immigrants at the Jefferson County Courthouse', 'https://www.spiritofjefferson.com/news/article_68167bb6-a38c-4287-a38a-0958e5f9178a.html', 'Spirit of Jefferson', '2025-12-10', 39.2894203, -77.8598607, 'Jefferson County Courthouse, Charles Town, WV'],
        ['100 protest a Wilder ICE raid at the Canyon County Administration building', 'https://www.boisestatepublicradio.org/news/2025-10-20/fbi-ice-raid-wilder-idaho-protest-aclu-poder', 'Boise State Public Radio', '2025-10-20', 43.6653323, -116.6815731, 'Canyon County Administration, Caldwell, ID'],
        ['Vigil after an ICE raid in the Super Gigante grocery parking lot', 'https://whyy.org/articles/norristown-immigrants-ice-raid-supermarket/', 'WHYY', '2025-07-16', 40.1146022, -75.3446458, 'Super Gigante, Norristown, PA'],
        ['300+ hold a Labor Day ICE-detainee vigil in Hinds Plaza', 'https://www.dailyprincetonian.com/article/2025/09/princeton-news-broadfocus-town-protest-ice-immigrant-vigil-labor-day', 'The Daily Princetonian', '2025-09-01', 40.3513545, -74.6601680, 'Hinds Plaza, Princeton, NJ'],

        // Feed-only (no marker — already-pinned venue or dense city)
        ['Bend rally: 100+ march against a public-lands sell-off', 'https://www.centraloregondaily.com/news/local/bend-protest-public-lands-sale-proposal-june-2025/article_19f8898d-80dc-492a-a729-3006b8058219.html', 'Central Oregon Daily', '2025-06-21', 44.0584771, -121.3222223, 'Drake Park, Bend, OR'],
        ['Unite for Veterans: thousands protest VA cuts on the D-Day anniversary', 'https://www.stripes.com/veterans/2025-06-06/veterans-rally-national-mall-18036672.html', 'Stars and Stripes', '2025-06-06', 38.8854281, -77.0414735, 'National Mall, Washington, DC'],
        ['Homeless residents march against a looming encampment sweep in San Jose', 'https://sanjosespotlight.com/san-jose-homeless-residents-rally-against-looming-sweep/', 'San José Spotlight', '2025-08-12', 37.3380339, -121.8853233, 'San Jose City Hall, CA'],
        ['Shelter Is a Human Right rally against encampment sweeps in Ithaca', 'https://www.cornellsun.com/article/2025/11/shelter-is-a-human-right-community-members-rally-at-city-hall-to-protest-homeless-encampment-sweeps-lack-of-shelters', 'Cornell Daily Sun', '2025-11-12', 42.4389044, -76.4985110, 'Ithaca City Hall, NY'],
        ['Hundreds rally at the Federal Building against US-Israel strikes on Iran', 'https://missionlocal.org/2026/02/sf-us-israel-iran-war-protest/', 'Mission Local', '2026-02-28', 37.7796112, -122.4115426, 'SF Federal Building, CA'],
        ['100+ rally at the Seattle Federal Building against the war on Iran', 'https://www.fox13seattle.com/news/protest-war-iran-seattle-federal-building', 'FOX 13 Seattle', '2026-04-07', 47.6045821, -122.3354856, 'Jackson Federal Building, Seattle, WA'],
        ['Hundreds rally against sweeping faculty and program cuts at The New School', 'https://hyperallergic.com/hundreds-rally-against-sweeping-cuts-at-new-school/', 'Hyperallergic', '2025-12-10', 40.7354792, -73.9970709, 'The New School, Manhattan, NY'],
        ['Students rally over Ohio State\'s compliance with anti-DEI Senate Bill 1', 'https://www.thelantern.com/2025/11/students-faculty-gather-on-campus-in-protest-of-ohio-states-compliance-with-senate-bill-1/', 'The Lantern', '2025-11-07', 39.9994828, -83.0127590, 'OSU Oval, Columbus, OH'],
        ['Cancer survivors and disability advocates rally on Medicaid cuts at McCormick\'s office', 'https://whyy.org/articles/philadelphia-medicaid-cuts-rally-senate/', 'WHYY', '2025-05-28', 39.9655661, -75.1815202, 'Center City, Philadelphia, PA'],
        ['Nurses rally over Medicaid cuts outside Rep. Ciscomani\'s Tucson office', 'https://www.kgun9.com/news/community-inspired-journalism/midtown-news/tucson-nurses-protest-medicaid-cuts-in-big-beautiful-bill-outside-rep-ciscomanis-office', 'KGUN9', '2025-07-01', 32.2228765, -110.9748470, 'Tucson, AZ'],
        ['Rep. LaMalfa jeered over Medicaid cuts at his first Chico town hall in 8 years', 'https://www.mynspr.org/news/2025-08-11/lamalfa-faces-loud-crowd-at-first-chico-town-hall-in-8-years', 'North State Public Radio', '2025-08-11', 39.7284945, -121.8374777, 'Chico, CA'],
        ['Hundreds of CDC staff walk out after top officials resign over RFK Jr.', 'https://www.commondreams.org/news/cdc-staff-walkout', 'Common Dreams', '2025-08-28', 33.7811778, -84.3230951, 'CDC Roybal Campus, Atlanta, GA'],
        ['West Philadelphia rally demands SNAP benefits be restored: Fund SNAP, not ICE', 'https://whyy.org/articles/philadelphia-snap-benefits-freeze-protest/', 'WHYY', '2025-11-01', 39.9614679, -75.2368077, 'West Philadelphia, PA'],

        // ===== round 11: thin states, US territories, new movements & 2026 outcomes =====
        // Under-covered states & US territories (No Kings, Good Trouble, solidarity)
        ['Thousands rally at a Portland No Kings protest; two arrested', 'https://www.bangordailynews.com/2025/06/14/portland/portland-police-courts/police-arrest-2-protesters-no-kings-rally-trump-portland/', 'Bangor Daily News', '2025-06-14', 43.6568471, -70.2598232, 'Monument Square, Portland, ME'],
        ['Hundreds gather at the Maine State House for a Good Trouble protest', 'https://spectrumlocalnews.com/me/maine/politics/2025/07/17/hundreds-flock-to-maine-state-house-for--good-trouble--protest', 'Spectrum News Maine', '2025-07-17', 44.3065349, -69.7810851, 'Maine State House, Augusta, ME'],
        ['Thousands fill Broadway Park for a second Bangor No Kings rally', 'https://www.wabi.tv/2025/10/18/bangor-joins-nationwide-no-kings-protest/', 'WABI-TV', '2025-10-18', 44.8109592, -68.7704614, 'Broadway Park, Bangor, ME'],
        ['Hundreds rally in Lewiston in solidarity with the Somali community', 'https://www.mainepublic.org/immigration/2025-12-14/hundreds-rally-in-lewiston-in-solidarity-with-somali-community', 'Maine Public', '2025-12-13', 44.0992442, -70.2036716, 'Kennedy Park, Lewiston, ME'],
        ['About 750 rally at the West Virginia Capitol for No Kings', 'https://wvpublic.org/no-kings-events-crisscross-w-va/', 'West Virginia Public Broadcasting', '2025-06-14', 38.3358429, -81.6142029, 'West Virginia State Capitol, Charleston, WV'],
        ['Thousands fill downtown Wichita for a No Kings protest', 'https://www.kwch.com/2025/10/19/no-kings-protest-draws-large-crowd-downtown-wichita-cities-nationwide/', 'KWCH-TV', '2025-10-18', 37.6860264, -97.3355680, 'Douglas Ave & Broadway, Wichita, KS'],
        ['No Kings protest fills Lawyers Mall by the Maryland State House', 'https://www.thebanner.com/politics-power/no-kings-protests-maryland-FF3BRTLNJRHKLNWSLBKMIVZ3DU/', 'The Baltimore Banner', '2025-06-14', 38.9790574, -76.4915458, 'Lawyers Mall, Annapolis, MD'],
        ['Thousands rally on Dickson Street for a Fayetteville No Kings protest', 'https://www.kuaf.com/show/ozarks-at-large/2025-06-16/thousands-attend-no-kings-protest-in-downtown-fayetteville', 'KUAF', '2025-06-14', 36.0663679, -94.1624909, 'Dickson Street, Fayetteville, AR'],
        ['About 5,000 rally at Railroad Park for a Birmingham No Kings protest', 'https://birminghamwatch.org/2025/10/18/thousands-rally-in-birmingham-as-part-of-no-kings-protests/', 'BirminghamWatch', '2025-10-18', 33.5095126, -86.8088454, 'Railroad Park, Birmingham, AL'],
        ['Several thousand rally at Metro Hall for a Louisville No Kings protest', 'https://www.lpm.org/news/2025-06-14/no-kings-protestors-rally-across-kentucky-in-national-anti-authoritarian-display', 'Louisville Public Media', '2025-06-14', 38.2541239, -85.7591339, 'Metro Hall, Louisville, KY'],
        ['Activists protest ICE detentions in a San Juan neighborhood', 'https://thelatinonewsletter.org/p/activists-protest-immigration-detentions-in-san-juan-neighborhood', 'The Latino Newsletter', '2025-11-16', 18.4046351, -66.0478486, 'Rio Piedras, San Juan, PR'],
        ['Guam holds the first No Kings rally in a US territory', 'https://www.islapublic.org/news/2025-06-15/no-kings-rally-in-guam-demands-protection-of-democracy-and-equal-rights', 'Isla Public', '2025-06-14', 13.5196540, 144.8171200, 'Micronesia Mall, Dededo, GU'],
        ['A No Kings motorcade and rally draws a crowd on the St. Croix bypass', 'https://www.virginislandsdailynews.com/news/virgin-islands-residents-join-no-kings-protest-movement/article_90e135d5-3804-493c-83ff-a10fbf45efdf.html', 'Virgin Islands Daily News', '2026-03-28', 17.7468293, -64.7078331, 'Christiansted bypass, St. Croix, VI'],
        ['About 1,000 rally on Minnesota Avenue for a Sioux Falls No Kings day of action', 'https://www.dakotanewsnow.com/2025/06/14/sioux-falls-takes-part-nationwide-no-kings-rally/', 'Dakota News Now', '2025-06-14', 43.5480280, -96.7245684, 'Downtown Sioux Falls, SD'],

        // Tesla Takedown · climate / public lands · federal-worker (anti-DOGE)
        ['Indivisible Charlotte holds its 10th Tesla Takedown protest against DOGE', 'https://www.wsoctv.com/news/local/indivisible-charlotte-holds-10th-tesla-protest-matthews/5TSV4TMBWJATLM6I44FLJAT35U/', 'WSOC-TV', '2025-05-10', 35.1409085, -80.7189124, 'Tesla dealership, Matthews, NC'],
        ['Roughly 1,000 rally against Trump at the Montana Capitol', 'https://montanafreepress.org/2025/06/14/roughly-1000-rally-against-trump-at-montana-capitol/', 'Montana Free Press', '2025-06-14', 46.5857390, -112.0184275, 'Montana State Capitol, Helena, MT'],
        ['NIOSH workers and AFGE rally against HHS layoffs in Washington', 'https://westvirginiawatch.com/2025/05/16/labor-unions-representing-laid-off-niosh-cdc-workers-to-protest-in-d-c-next-week/', 'West Virginia Watch', '2025-05-22', 38.8866298, -77.0143854, 'HHS HQ, Washington, DC'],
        ['Tesla Takedown protesters rally against Musk and DOGE in Matthews', 'https://www.wfae.org/charlotte-area/2025-07-15/protesters-rally-against-elon-musk-outside-tesla-dealership', 'WFAE', '2025-07-15', 35.1409085, -80.7189124, 'Tesla dealership, Matthews, NC'],
        ['Hundreds rally Not for Sale against a public-lands sell-off at the Wyoming Capitol', 'https://wyofile.com/public-lands-rally-draws-large-varied-crowd-to-wyoming-statehouse/', 'WyoFile', '2025-06-26', 41.1402683, -104.8202289, 'Wyoming Capitol, Cheyenne, WY'],
        ['Federal workers rally against the shutdown and DOGE cuts in Tucson', 'https://www.tucsonspotlight.org/federal-workers-leaders-rally-amid-government-shutdown/', 'Tucson Spotlight', '2025-10-06', 32.2229936, -110.9739013, 'El Presidio Plaza, Tucson, AZ'],
        ['Federal workers rally against the shutdown and job cuts in Cincinnati', 'https://www.wvxu.org/local-news/2025-10-14/federal-workers-rally-cincinnati-shutdown', 'WVXU', '2025-10-14', 39.1016812, -84.5080828, 'Downtown Cincinnati, OH'],

        // Labor · healthcare · education strikes & pickets
        ['Houlton Regional Hospital nurses begin a four-day strike over ER staffing', 'https://www.wagmtv.com/2026/05/22/nurses-houlton-regional-hospital-plan-four-day-strike/', 'WAGM-TV', '2026-05-26', 46.1323216, -67.8429374, 'Houlton Regional Hospital, Houlton, ME'],
        ['HACC faculty picket over a stalled contract and pay raises', 'https://local21news.com/news/local/hacc-faculty-union-plans-strike-picket-monday-amid-ongoing-contract-negotiations-haccea-pay-raise-tuition-harrisburg-area-community-college-education-association-pennsylvania-pa', 'CBS 21', '2025-11-03', 40.2943815, -76.8844305, 'HACC Harrisburg Campus, PA'],
        ['District 11 teachers stage the first Colorado Springs strike in 50 years', 'https://coloradosun.com/2025/10/08/hundreds-of-colorado-springs-teachers-strike-over-loss-of-collective-bargaining/', 'The Colorado Sun', '2025-10-08', 38.8380344, -104.8229911, 'Acacia Park, Colorado Springs, CO'],
        ['Iowa City Starbucks baristas join a nationwide unfair-labor strike', 'https://www.kcrg.com/2025/12/04/iowa-starbucks-workers-join-national-strike/', 'KCRG-TV9', '2025-12-04', 41.6508937, -91.5346322, 'Starbucks, S Clinton St, Iowa City, IA'],
        ['UAW Local 2093 strikes American Axle over stagnant wages', 'https://www.clickondetroit.com/news/local/2026/06/01/uaw-workers-strike-at-american-axle-plant-in-michigan/', 'WDIV Local 4', '2026-06-01', 41.9345643, -85.6400717, 'American Axle (Dauch) plant, Three Rivers, MI'],
        ['Backus Hospital nurses hold an informational picket over patient care', 'https://www.wfsb.com/2025/05/15/nurses-backus-hospital-hold-demonstration-over-patient-care-concerns/', 'WFSB Channel 3', '2025-05-15', 41.5427325, -72.0886873, 'Backus Hospital, Norwich, CT'],
        ['Butler Hospital workers strike over wages, staffing and pensions', 'https://thepublicsradio.org/labor/butler-hospital-workers-ratify-new-contract-ending-strike/', 'The Public\'s Radio', '2025-05-15', 41.8424483, -71.3807299, 'Butler Hospital, Providence, RI'],
        ['Meriter nurses launch the first strike in the hospital\'s history over staffing', 'https://www.wpr.org/news/nurses-madison-meriter-hospital-on-strike-contract-demands', 'Wisconsin Public Radio', '2025-05-27', 43.0660496, -89.4017123, 'Meriter Hospital, Madison, WI'],
        ['OSU Wexner nurses picket over workplace violence and staffing', 'https://www.wosu.org/2025-06-25/osu-nurses-picket-for-safer-workplace-higher-wages-amid-contract-negotiations', 'WOSU', '2025-06-25', 39.9948571, -83.0198739, 'OSU Wexner, Columbus, OH'],
        ['Duluth nurses picket St. Luke\'s and Essentia over unsafe staffing', 'https://mnnurses.org/nurses-picket-twin-cities-duluth-hospitals-as-15000-seek-new-contracts-that-prioritize-patient-safety/', 'Minnesota Nurses Association', '2025-06-04', 46.7979957, -92.0864394, 'Duluth, MN'],
        ['Ascension Saint Agnes nurses hold a one-day strike over staffing', 'https://www.cbsnews.com/baltimore/news/maryland-nurses-strike-ascension-saint-agnes-hospital/', 'CBS News Baltimore', '2025-07-24', 39.2837940, -76.7624370, 'St. Agnes Hospital, Baltimore, MD'],
        ['UNM Hospital workers picket over understaffing and low wages', 'https://www.ems1.com/hospital/nurses-staff-protest-at-n-m-hospital-citing-heavy-workloads-and-low-wages', 'EMS1', '2025-10-31', 35.0884989, -106.6179208, 'UNM Hospital, Albuquerque, NM'],
        ['Memphis Starbucks baristas strike in the Red Cup Rebellion', 'https://www.actionnews5.com/2025/12/01/starbucks-workers-strike-outside-memphis-store-poplar-avenue/', 'Action News 5', '2025-11-30', 35.1289637, -89.9495139, 'Poplar Ave, Memphis, TN'],
        ['Stand Up for Science rally hits the Oklahoma Capitol against research cuts', 'https://freepressokc.com/despite-cold-pro-science-rally-draws-modest-crowd-to-capitol/', 'Free Press OKC', '2026-03-07', 35.4922055, -97.5033333, 'Oklahoma Capitol, Oklahoma City, OK'],
        ['UMC nurses strike for a fifth time over staffing and retention', 'https://www.wwno.org/public-health/2025-11-11/protestors-brave-cold-snap-to-support-striking-nurses-at-new-orleans-umc', 'WWNO', '2025-11-11', 29.9597986, -90.0821311, 'UMC, New Orleans, LA'],
        ['Missoula scientists rally to Take Back Science against research cuts', 'https://www.mtpr.org/montana-news/2026-03-11/demonstrators-gather-in-missoula-to-stand-up-for-science', 'Montana Public Radio', '2026-03-07', 46.8732919, -113.9959132, 'Missoula, MT'],

        // Prosecution outcomes & notable arrests (2025-26)
        ['Milwaukee judge Hannah Dugan is found guilty of obstructing ICE agents', 'https://en.wikipedia.org/wiki/Hannah_Dugan', 'Wikipedia', '2025-12-18', 43.0387634, -87.9050046, 'U.S. Courthouse, Milwaukee, WI'],
        ['Eight anti-ICE protesters are convicted on terrorism charges in the Prairieland trial', 'https://theintercept.com/2026/03/13/ice-protesters-terrorism-prairieland-antifa/', 'The Intercept', '2026-03-13', 32.7499470, -97.3329500, 'Eldon B. Mahon U.S. Courthouse, Fort Worth, TX'],
        ['A CBP officer is charged with assault over the Durango ICE protest', 'https://coloradonewsline.com/briefs/federal-officer-charged-durango-ice-protest/', 'Colorado Newsline', '2026-04-21', 37.2748174, -107.8787224, 'La Plata County Court, Durango, CO'],
        ['Newark Mayor Ras Baraka sues the US Attorney for false arrest and malicious prosecution', 'https://www.pbs.org/newshour/politics/newark-mayor-sues-federal-prosecutor-saying-arrest-at-immigration-detention-site-was-political', 'PBS NewsHour', '2025-06-03', 40.7297710, -74.1723023, 'MLK Courthouse, Newark, NJ'],
        ['A jury acquits ICE protester Brayan Ramos-Brito of assaulting a federal officer', 'https://www.cbsnews.com/losangeles/news/la-county-ice-protest-not-guilty-acquittal/', 'CBS News Los Angeles', '2025-09-17', 34.0528364, -118.2389802, 'Roybal Courthouse, Los Angeles, CA'],
        ['The Stanford protesters case ends in a hung jury and a mistrial on felony charges', 'https://www.kqed.org/news/12073534/stanford-pro-palestinian-protesters-case-ends-in-mistrial', 'KQED', '2026-02-14', 37.3937219, -121.9307405, 'Santa Clara County Court, San Jose, CA'],
        ['A man is indicted for trying to set fire to the Surprise ICE building', 'https://www.azfamily.com/2026/05/20/19-year-old-arrested-allegedly-setting-fire-surprise-ice-building/', 'Arizona\'s Family', '2026-05-07', 33.4477432, -112.0804769, 'Phoenix federal courthouse, AZ'],

        // ===== elected officials arrested / charged at protests =====
        ['SF Supervisors Mandelman and Chan and Sen. Becker arrested at the SFO May Day protest', 'https://missionlocal.org/2026/05/s-f-supervisors-past-and-present-arrested-at-sfo-anti-ice-protest/', 'Mission Local', '2026-05-01', 37.6157466, -122.3903590, 'SFO International Terminal, San Francisco, CA'],
        ['Oak Park Township Trustee Juan Muñoz detained by ICE at the Broadview facility protest', 'https://www.cbsnews.com/chicago/news/oak-park-officials-stand-with-protesters-broadview-ice-facility/', 'CBS News Chicago', '2025-10-03', null, null, null],
        ['Oak Park Trustee Brian Straw indicted over the Broadview ICE facility protest', 'https://capitolnewsillinois.com/news/democratic-candidates-officeholders-indicted-for-impeding-agent-outside-ice-facility/', 'Capitol News Illinois', '2025-09-26', null, null, null],
        ['Ex-Worcester councilor Etel Haxhiaj convicted of assaulting an officer at an ICE arrest', 'https://www.cbsnews.com/boston/news/worcester-city-councilor-etel-haxhiaj-guilty-assault-police-ice/', 'CBS News Boston', '2026-02-11', 42.2674401, -71.8006902, 'Worcester courthouse, MA'],

        // ===== round 12: month-by-month sweep (events missed by topic/location passes) =====
        // May–Jul 2025
        ['Seattle police arrest 23 at an anti-LGBTQ Mayday USA rally and counterprotest', 'https://www.kuow.org/stories/anti-lgbtq-demonstrators-counterprotesters-descend-on-seattle-city-hall', 'KUOW', '2025-05-24', 47.6156732, -122.3182531, 'Cal Anderson Park, Seattle, WA'],
        ['A driver accelerates an SUV into a crowd leaving a No Kings rally in Culpeper', 'https://www.nbcwashington.com/news/local/northern-virginia/man-accused-of-driving-through-crowd-of-protesters-in-culpeper/3936544/', 'NBC4 Washington', '2025-06-14', 38.4733823, -77.9961275, 'Culpeper, VA'],
        ['A hit-and-run driver strikes a No Kings protester in downtown Riverside; two charged', 'https://abc7.com/post/police-arrest-driver-who-plowed-into-crowd-no-kings-protest-riverside/16766376/', 'ABC7 Los Angeles', '2025-06-14', null, null, null],
        ['Three arrested, including a Proud Boy with brass knuckles, at an Ocala No Kings rally', 'https://352today.com/news/257752-three-arrested-during-no-kings-rally-in-ocalas-downtown-square/', '352Today', '2025-06-14', 29.1860001, -82.1360994, 'Ocala Downtown Square, FL'],
        ['One counterprotester with a handgun arrested at a 10,000-strong Nashville No Kings protest', 'https://tennesseelookout.com/briefs/one-arrested-at-peaceful-no-kings-protest-in-nashville/', 'Tennessee Lookout', '2025-06-14', null, null, null],
        ['DeKalb County drops the charges against arrested journalist Mario Guevara', 'https://www.atlantanewsfirst.com/2025/06/25/dekalb-county-drops-charges-against-journalist-arrested-during-anti-ice-protest/', 'Atlanta News First', '2025-06-25', null, null, null],
        ['Gunfire ambush at the Prairieland ICE detention center wounds an officer; 11 charged', 'https://www.keranews.org/news/2025-07-11/prairieland-detention-center-alvarado-u-s-immigration-and-customs-enforcement-shooting-alvarado-police-officer-questions', 'KERA News', '2025-07-04', 32.4206417, -97.1967418, 'Prairieland Detention Center, Alvarado, TX'],
        ['CSU Channel Islands professor arrested during an ICE raid at Glass House Farms', 'https://abc7.com/post/cal-state-channel-islands-professor-jonathan-caravello-arrested-during-camarillo-immigration-raid-due-court-monday/17117396/', 'ABC7 Los Angeles', '2025-07-10', 34.1799214, -119.0830036, 'Glass House Farms, Camarillo, CA'],
        ['An ICE courthouse arrest sparks an ICE out of Philly rally at the criminal justice center', 'https://billypenn.com/2025/07/18/philadelphia-ice-arrest-courthouse-protest/', 'Billy Penn', '2025-07-18', null, null, null],

        // Aug–Oct 2025
        ['DOJ paralegal Sean Dunn arrested for throwing a sandwich at a Border Patrol agent', 'https://en.wikipedia.org/wiki/Trial_of_Sean_Dunn', 'Wikipedia', '2025-08-10', null, null, null],
        ['About 3,200 IAM machinists strike Boeing fighter-jet plants', 'https://www.stlpr.org/economy-business/2025-08-04/boeing-union-workers-go-on-strike-in-st-louis-area', 'St. Louis Public Radio', '2025-08-04', 38.7488983, -90.3453298, 'Boeing Defense plant, Berkeley, MO'],
        ['More than 750 nurses strike Henry Ford Genesys Hospital', 'https://www.wsws.org/en/articles/2025/09/05/smyb-s05.html', 'World Socialist Web Site', '2025-09-01', 42.8921370, -83.6424515, 'Henry Ford Genesys Hospital, Grand Blanc, MI'],
        ['600+ nurse anesthetists and midwives strike Kaiser Permanente Oakland', 'https://unacuhcp.org/news/hundreds-of-northern-california-kaiser-healthcare-workers-will-strike-to-improve-patient-care/', 'UNAC/UHCP', '2025-09-08', 37.8233565, -122.2591941, 'Kaiser Permanente Oakland Medical Center, CA'],
        ['DC college students walk out over the federal takeover of the capital', 'https://georgetownvoice.com/2025/09/10/walkout/', 'The Georgetown Voice', '2025-09-09', null, null, null],
        ['18 arrested at the Broadview ICE facility during a visit by DHS Secretary Noem', 'https://www.cbsnews.com/chicago/news/protest-broadview-ice-facility-police-noem/', 'CBS News Chicago', '2025-10-03', null, null, null],
        ['A No Kings rally packs B.A. Clark Park in north Spokane', 'https://www.spokesman.com/stories/2025/oct/18/no-kings-day-rally-packs-sidewalks-in-north-spokan/', 'The Spokesman-Review', '2025-10-18', null, null, null],
        ['More than 10,000 rally at the Ohio Statehouse for No Kings day', 'https://www.statenews.org/government-politics/2025-10-19/thousands-rallied-in-no-kings-protests-in-ohio-including-huge-turnout-at-statehouse', 'Statehouse News Bureau', '2025-10-18', null, null, null],
        ['About 8,000 join a No Kings march through downtown Knoxville', 'https://www.wvlt.tv/2025/10/19/no-arrests-made-after-thousands-participate-no-kings-protest-knoxville-police-say/', 'WVLT', '2025-10-18', 35.9646188, -83.9182821, 'Henley Street, Knoxville, TN'],
        ['Woman charged with brandishing a gun from a car at a Myrtle Beach No Kings protest', 'https://wpde.com/news/local/arrested-suspect-no-kings-protest-myrtle-beach-chapin-memorial-park-drive-by-police-department-400-14th-avenue-north-woman-location', 'WPDE ABC15', '2025-10-18', 33.6176017, -78.9657127, 'Chapin Memorial Park, Myrtle Beach, SC'],

        // Nov 2025–Jan 2026
        ['Fund Food Stamps Now rally at the Georgia Capitol over the SNAP cutoff', 'https://www.fox5atlanta.com/news/georgia-food-stamps-snap-party-socialism-liberation-rally-capitol', 'FOX 5 Atlanta', '2025-11-01', 33.7490553, -84.3881711, 'Georgia State Capitol, Atlanta, GA'],
        ['Charlotte woman charged with assaulting a federal officer at an ICE protest', 'https://www.wbtv.com/2025/11/19/charlotte-woman-accused-assaulting-federal-officer-over-weekend-officials-say/', 'WBTV', '2025-11-16', 35.1639886, -80.9106908, 'ICE office (Tyvola), Charlotte, NC'],
        ['St. Paul man indicted for ramming an ICE vehicle in an arrest that sparked a protest', 'https://www.fox9.com/news/man-rammed-ice-vehicle-arrest-st-paul-protest-indictment-dec-2025', 'FOX 9', '2025-12-03', null, null, null],
        ['Portland high school students walk out and march to City Hall over ICE detentions', 'https://www.mainepublic.org/immigration/2025-12-03/hundreds-of-portland-high-school-students-rally-against-trump-admins-immigration-policies', 'Maine Public', '2025-12-03', null, null, null],
        ['Dozens march in New Orleans against a planned Border Patrol Swamp Sweep', 'https://www.wwno.org/immigration/2025-12-01/no-ice-no-fear-dozens-protest-in-new-orleans-ahead-of-louisiana-immigration-sweep', 'WWNO', '2025-12-01', null, null, null],
        ['Hundreds of Washington County students walk out over ICE activity', 'https://www.opb.org/article/2025/12/08/washington-county-students-walkout-ice-arrests/', 'OPB', '2025-12-08', 45.4497535, -122.8080243, 'Southridge High School, Beaverton, OR'],
        ['A crowd of 80+ confronts ICE during a Chanhassen rooftop standoff', 'https://kstp.com/kstp-news/top-news/crowd-gathers-in-chanhassen-in-response-to-a-supposed-ice-arrest/', 'KSTP', '2025-12-13', 44.8618403, -93.5307207, 'Avienda Pkwy, Chanhassen, MN'],
        ['Residents rally at the Michigan Capitol against data center projects', 'https://www.wilx.com/2025/12/16/data-center-protest-outside-michigan-capitol/', 'WILX', '2025-12-16', 42.7336057, -84.5554583, 'Michigan State Capitol, Lansing, MI'],
        ['Hundreds rally at Fountain Square over the ICE killing of Renée Good', 'https://www.wvxu.org/local-news/2026-01-08/cincinnati-protest-ice-shooting-renee-good', 'WVXU', '2026-01-08', null, null, null],
        ['Up to 300 rally outside the Buffalo ICE office after the Minneapolis shooting', 'https://www.wxxinews.org/2026-01-08/buffalo-protesters-denounce-ice-in-response-to-fatal-shooting-in-minnesota', 'WXXI', '2026-01-08', 42.8911058, -78.8767093, 'ICE office, 250 Delaware Ave, Buffalo, NY'],
        ['Hundreds hold a candlelight vigil and march for Renée Good in Cleveland', 'https://www.cleveland19.com/2026/01/10/hundreds-gather-vigil-march-cleveland-following-deadly-ice-shooting/', 'Cleveland 19 News', '2026-01-09', null, null, null],
        ['A US citizen is detained for hours by ICE at a Phoenix protest', 'https://coppercourier.com/2026/01/27/citizen-detained-ice-phoenix/', 'Copper Courier', '2026-01-22', null, null, null],
        ['16 anti-abortion activists arrested at an HHS protest over mifepristone', 'https://www.ms.now/news/anti-abortion-clinic-protests', 'Ms. Magazine', '2026-01-22', null, null, null],
        ['66 arrested after anti-ICE protesters take over a Tribeca hotel lobby', 'https://ny1.com/nyc/manhattan/news/2026/01/28/anti-ice-protest-inside-tribeca-hilton-leads-to-dozens-of-arrests', 'NY1', '2026-01-27', null, null, null],

        // Feb–Jun 2026
        ['Houston students walk out of class over a classmate detained by ICE', 'https://www.houstonpublicmedia.org/articles/education/2026/02/04/542549/ice-protest-houston-school-tea/', 'Houston Public Media', '2026-02-03', 29.8488954, -95.3612575, 'Sam Houston MSTC, Houston, TX'],
        ['East Central High students disciplined after an anti-ICE walkout', 'https://www.ksat.com/news/local/2026/02/20/30-students-disciplined-at-east-central-high-school-after-anti-ice-walkout/', 'KSAT', '2026-02-13', 29.3504990, -98.2949240, 'East Central High School, San Antonio, TX'],
        ['A student-led anti-ICE march on Presidents Day from Main Plaza to the Alamo', 'https://www.tpr.org/news/2026-02-16/san-antonio-students-hold-anti-ice-press-conference-and-march-on-presidents-day', 'Texas Public Radio', '2026-02-16', null, null, null],
        ['38 cited and one jailed at an anti-ICE protest outside the Whipple Federal Building', 'https://www.mprnews.org/story/2026/03/02/whipple-building-ice-protestors-cited-1-arrested-sunday', 'MPR News', '2026-03-01', null, null, null],
        ['McGavock High students walk out to protest ICE activity in Nashville', 'https://www.nashvillescene.com/news/pithinthewind/mcgavock-high-students-protest-ice-nashville/article_df7a99e7-4495-4a45-9ab5-0fcf5cf253c1.html', 'Nashville Scene', '2026-03-06', null, null, null],
        ['Strikers walk off the job at the JBS Swift beef plant in Greeley', 'https://coloradosun.com/2026/04/12/jbs-meatpackers-greeley-ratify-contract/', 'The Colorado Sun', '2026-03-16', 40.4248132, -104.6907136, 'JBS Swift Beef plant, Greeley, CO'],
        ['Three arrested as about 500 protest an ICE warehouse-detention purchase', 'https://www.ksl.com/article/51468395/3-arrested-as-hundreds-protest-new-ice-purchase-of-salt-lake-warehouse', 'KSL.com', '2026-03-18', 40.6397675, -111.8984930, 'ICE warehouse, 6020 W 300 S, Salt Lake City, UT'],
        ['Congressional candidate Don Leonard arrested at a Grove City No Kings protest', 'https://www.wosu.org/politics-government/2026-03-30/congressional-candidate-arrested-at-grove-city-no-kings-protest-after-using-megaphone', 'WOSU Public Media', '2026-03-28', 39.8819334, -83.0939180, 'Grove City Hall, OH'],
        ['Union nurses rally against Columbia Memorial inpatient bed cuts', 'https://cbs6albany.com/news/local/union-workers-nurses-rally-over-proposed-patient-bed-cuts-at-hospital-columbia-memorial-hospital-albany-medical-center-wrgb', 'CBS6 Albany', '2026-04-15', null, null, null],
        ['An emergency rally outside the immigration court after ICE detains parents at a hearing', 'https://bigeasymagazine.com/2026/04/17/ice-arrests-at-new-orleans-immigration-court-spark-outrage-and-protest/', 'Big Easy Magazine', '2026-04-17', null, null, null],
        ['Harvard graduate workers launch a strike with picket lines', 'https://www.wbur.org/news/2026/04/21/harvard-graduate-student-union-strike', 'WBUR', '2026-04-21', 42.3763521, -71.1158262, 'Harvard Science Center Plaza, Cambridge, MA'],
        ['Communities Not Cages rally against the Project Blue data center', 'https://www.tucsonspotlight.org/tucson-protesters-rally-against-project-blue-data-center/', 'Tucson Spotlight', '2026-04-25', 32.1335811, -110.9322259, 'Project Blue site (Houghton Rd), Tucson, AZ'],
        ['Congressional candidate Chuck Park among 25 arrested in a May Day protest at the NYSE', 'https://qns.com/2026/05/chuck-park-arrested-may-day/', 'QNS', '2026-05-01', null, null, null],
        ['Four arrested for interfering with a traffic stop near the ICE detention warehouse', 'https://www.ksl.com/article/51495984/protesters-at-ice-detention-facility-arrested-for-allegedly-interfering-with-traffic-stop', 'KSL.com', '2026-05-10', null, null, null],
        ['More than 600 rally at the Utah Capitol against a Box Elder data center', 'https://www.ksl.com/article/51501551/hundreds-of-utahns-rally-against-proposed-box-elder-data-center', 'KSL.com', '2026-05-23', 40.7755406, -111.8881341, 'Utah State Capitol, Salt Lake City, UT'],
        ['A Vermont prosecutor declines to charge six arrested at a South Burlington ICE operation', 'https://www.wcax.com/2026/04/23/state-officials-escalate-feud-with-prosecutor-over-dropped-protest-charges/', 'WCAX', '2026-03-11', null, null, null],
        ['Bell County residents pack commissioners court to oppose a Temple data center', 'https://www.kwtx.com/2026/06/02/we-dont-want-this-bell-county-residents-show-up-by-masses-protest-data-center-asks-commissioners-court-intervention/', 'KWTX', '2026-06-01', 31.0415614, -97.4894434, 'Bell County Justice Complex, Belton, TX'],
        ['Protesters in six counties rally demanding Rep. Dan Meuser hold a town hall', 'https://lebtown.com/2026/06/02/organizers-protestors-gathered-in-6-counties-across-meusers-district-demanding-town-hall/', 'LebTown', '2026-06-01', 40.3334311, -76.4237266, 'Lebanon County Courthouse, PA'],

        // ===== round 13: deeper month sweep (second-tier events) =====
        ['Cincinnati Socialists protest the ICE detention of teen Emerson Colindres; one jailed', 'https://spectrumnews1.com/oh/columbus/news/2025/06/10/multiple-protests--protestor-jailed-after-most-recent-illegal-immigration-arrest-', 'Spectrum News 1', '2025-06-09', 39.3898269, -84.5555424, 'Butler County Jail, Hamilton, OH'],
        ['Community confronts an ICE raid at Buona Forchetta; agents fire flash-bangs, 4 arrested', 'https://www.nbcsandiego.com/news/local/community-speaks-after-ice-raids-at-south-park-italian-restaurant/3837706/', 'NBC 7 San Diego', '2025-05-30', 32.7213042, -117.1302361, 'Buona Forchetta (South Park), San Diego, CA'],
        ['A rally on the federal courthouse steps in Portland in solidarity with LA', 'https://www.mainepublic.org/politics/2025-06-08/portland-rally-voices-solidarity-with-l-a-protestors-amid-federal-immigration-raids', 'Maine Public', '2025-06-08', 43.6591537, -70.2548940, 'Gignoux U.S. Courthouse, Portland, ME'],
        ['Union members rally at the federal courthouse against the ICE crackdown in Pittsburgh', 'https://www.wesa.fm/politics-government/2025-06-09/pittsburgh-immigration-rally', '90.5 WESA', '2025-06-09', 40.4421828, -79.9948563, 'Weis U.S. Courthouse, Pittsburgh, PA'],
        ['A crowd protests the opening-day visit to the Alligator Alcatraz detention camp', 'https://www.wlrn.org/immigration/2025-07-01/alligator-alcatraz-protest-trump-visit', 'WLRN', '2025-07-01', 25.9361216, -81.0951271, 'Big Cypress (US-41), Ochopee, FL'],
        ['A coalition rallies at the Broward Transitional Center against Alligator Alcatraz', 'https://www.local10.com/news/local/2025/07/05/broward-rally-calls-for-immigration-reform-protests-detention-center-known-as-alligator-alcatraz/', 'Local 10', '2025-07-05', 26.2759673, -80.1519984, 'Broward Transitional Center, Pompano Beach, FL'],
        ['800 nurses hold informational pickets at Cape Cod and Falmouth hospitals', 'https://www.massnurses.org/2025/07/11/rns-at-cape-cod-and-falmouth-hospitals-to-hold-informational-pickets-on-tuesday-july-15/', 'Massachusetts Nurses Association', '2025-07-15', 41.6546200, -70.2735507, 'Cape Cod Hospital, Hyannis, MA'],
        ['Hilton Americas-Houston hotel workers launch a nine-day Labor Day strike', 'https://www.hoteldive.com/news/houston-hotel-workers-strike/758963/', 'Hotel Dive', '2025-09-01', 29.7526867, -95.3606855, 'Hilton Americas, Houston, TX'],
        ['Two charged after locking themselves to Mountain Valley Pipeline equipment', 'https://www.wfxrtv.com/news/regional-news/virginia-news/protestors-charged-after-locking-themselves-to-mountain-valley-pipeline-equipment-in-montgomery-co/', 'WFXR', '2025-09-05', 37.2165215, -80.2325427, 'Mountain Valley Pipeline, Elliston, VA'],
        ['Dozens protest a Loudoun schools transgender policy at a school board meeting', 'https://www.fox5dc.com/news/protests-continue-outside-loudoun-county-school-board-meeting-over-transgender-rights-policy', 'FOX 5 DC', '2025-09-10', 39.0313880, -77.5203106, 'LCPS Administration Building, Ashburn, VA'],
        ['Lights for Liberty ICE vigil outside the Blair County Courthouse', 'https://www.altoonamirror.com/znewsletter-sunday/2025/11/vigil-puts-ice-issues-in-focus/', 'Altoona Mirror', '2025-11-23', 40.4297781, -78.3929301, 'Blair County Courthouse, Hollidaysburg, PA'],
        ['Residents protest federal agents during an ICE raid in Santa Maria', 'https://www.edhat.com/news/residents-protest-against-federal-agents-during-ice-raid-in-santa-maria/', 'Edhat', '2025-11-13', 34.9531295, -120.4358570, 'Santa Maria, CA'],
        ['5,700 Sharp HealthCare nurses begin a three-day strike over pay and sick leave', 'https://timesofsandiego.com/business/2025/11/26/sharp-healthcare-nurses-begin-3-day-strike-at-multiple-facilities/', 'Times of San Diego', '2025-11-26', 32.7791522, -117.0084724, 'Sharp Grossmont Hospital, La Mesa, CA'],
        ['Hundreds pack Knox High to oppose data centers; the commission backs a moratorium', 'https://wkvi.com/2025/12/starke-county-plan-commission-unanimously-recommend-a-moratorium-on-data-centers/', 'WKVI', '2025-12-04', 41.2858476, -86.6241212, 'Knox Community High School, Knox, IN'],
        ['WCCUSD teachers begin the first strike in the district\'s 60-year history', 'https://richmondside.org/2025/12/01/wccusd-teachers-union-strike-to-begin-december-4/', 'Richmondside', '2025-12-04', 37.9057282, -122.2945129, 'El Cerrito High School, CA'],
        ['A Sullivan school board keeps a transgender sports and bathroom ban amid protest', 'https://www.bangordailynews.com/2025/12/10/politics/state-politics/sullivan-school-transgender-student-policy/', 'Bangor Daily News', '2025-12-10', 44.5111581, -68.1664973, 'Sumner Memorial High School, Sullivan, ME'],
        ['Activists post 200 ICE-kidnapped-a-community-member-here signs citywide', 'https://www.wlrn.org/immigration/2025-12-11/ice-immigration-deportation-arrests-lake-worth-florida', 'WLRN', '2025-12-11', 26.6159698, -80.0569927, 'Guatemalan-Maya Center, Lake Worth Beach, FL'],
        ['450 steelworkers strike the ArcelorMittal tubular plant in Shelby', 'https://www.richlandsource.com/2026/01/29/arcelormittal-employees-mark-16-days-on-picket-line-thursday/', 'Richland Source', '2026-01-13', 40.8813008, -82.6670228, 'ArcelorMittal Tubular Products, Shelby, OH'],
        ['A rally in Middletown after ICE arrests a nursing student near the courthouse', 'https://www.wfsb.com/2026/04/04/protesters-rally-middletown-after-ice-arrests-nursing-student-near-courthouse/', 'WFSB', '2026-04-04', 41.5623178, -72.6509061, 'Washington & Main St, Middletown, CT'],
        ['Two dozen protest ICE courthouse arrests after traffic court in Springfield', 'https://www.wsmv.com/2026/03/17/robertson-county-ice-courthouse-arrests-spark-protest/', 'WSMV', '2026-03-17', 36.5091102, -86.8853435, 'Robertson County Court, Springfield, TN'],
        ['A Charlottesville housing coalition rallies at City Hall over student-housing', 'https://www.29news.com/2026/03/31/charlottesville-low-income-housing-coalition-holds-rally-calls-city-council-change/', 'CBS19', '2026-03-30', 38.0296678, -78.4776624, 'Charlottesville City Hall, VA'],
        ['Hundreds rally in Morris against a proposed hyperscale data center', 'https://www.wcsjnews.com/news/local/data-center-protest-held-in-morris-on-saturday/article_f3eac834-b42a-4dc9-b9a8-ce7196f30b34.html', 'WCSJ News', '2026-06-02', 41.3574135, -88.4215234, 'Route 47, Morris, IL'],
        ['Sheridan teachers strike and picket over a stalled contract', 'https://coloradosun.com/2026/04/01/colorado-education-sheridan-school-district-teachers-strike/', 'The Colorado Sun', '2026-04-01', 39.6482059, -104.9879641, 'Sheridan School District, Englewood, CO'],
        ['At least 5 teens arrested as a student anti-ICE walkout clashes with police', 'https://www.nbcphiladelphia.com/news/local/quakertown-community-high-school-ice-protest-clash-discussion/4360196/', 'NBC10 Philadelphia', '2026-02-20', 40.4365133, -75.3421338, 'Quakertown Community High School, PA'],
        ['A crowd packs Perry Village hall to protest a data-center project', 'https://www.news5cleveland.com/news/local-news/crowd-packs-perry-village-hall-to-protest-data-center-project-as-tensions-rise-across-ohio', 'News 5 Cleveland', '2026-04-09', 41.7600000, -81.1370000, 'Perry Village, OH'],
        ['Hundreds rally against a proposed Bitdeer data center near the Ohio Turnpike', 'https://www.cleveland19.com/2026/05/30/shalersville-residents-rally-against-proposed-data-center/', 'Cleveland 19 News', '2026-05-29', 41.2389448, -81.2312132, 'Shalersville Township, OH'],
        ['A Riverhead Vigil for Justice and ICE Out protest at Town Hall', 'https://riverheadlocal.com/2026/02/25/vigil-for-justice-and-ice-out-protest-planned-in-riverhead-feb-27-and-28/', 'RiverheadLOCAL', '2026-02-27', 40.9184009, -72.6646282, 'Riverhead Town Hall, NY'],
        ['Little Lake City teachers strike for the first time in 150 years', 'https://edsource.org/updates/little-lake-city-school-district-teachers-strike-for-1st-time-in-150-years', 'EdSource', '2026-04-16', 33.9480787, -118.0691499, 'Little Lake City School District, Santa Fe Springs, CA'],
        ['Hundreds pack a high school gym to oppose a $6B data center before a council vote', 'https://www.stlpr.org/show/st-louis-on-the-air/2026-04-24/festus-residents-opposed-data-center-national-news-lawsuit-election-city-council', 'St. Louis Public Radio', '2026-03-30', 38.2014433, -90.3928989, 'Festus High School, Festus, MO'],
        ['Teamsters beer-delivery workers strike over contract concessions near Charleston', 'https://wvpublic.org/story/economy/beer-delivery-workers-strike-over-unfair-working-conditions/', 'West Virginia Public Broadcasting', '2026-05-13', 38.5026220, -81.6385982, 'The Beverage Market, Sissonville, WV'],
        ['Hundreds hold a Catholic Mass in the street outside the Indianapolis ICE office', 'https://www.wfyi.org/2026-05-02/hundreds-hold-mass-in-the-street-outside-ice-office-in-indianapolis', 'WFYI', '2026-05-02', 39.8862013, -86.2668692, 'ICE office (Woodland Dr), Indianapolis, IN'],
        ['SEIU 1199 nurses hold an informational picket over unsafe staffing in Lorain', 'https://chroniclet.com/news/462098/nurses-picket-outside-lorains-mercy-regional-medical-center/', 'Chronicle-Telegram', '2026-04-01', 41.4374395, -82.2353485, 'Mercy Regional Medical Center, Lorain, OH'],
        // feed-only (already-pinned cities / sensitive)
        ['Residents rally against ICE-THP traffic-stop sweeps in Nashville', 'https://www.wsmv.com/2025/05/18/protesters-rally-against-ice-operations-nashville/', 'WSMV', '2025-05-17', null, null, null],
        ['UFCW Local 7 begins an unfair-labor-practice strike at Colorado Safeway/Albertsons', 'https://www.ufcw7.org/l7press/unfair-labor-practice-strikes-to-begin-at-some-colorado-safewayalbertsons-locations-on-sunday', 'UFCW Local 7', '2025-06-18', null, null, null],
        ['300 nurses begin an open-ended strike against Essentia Health in Duluth', 'https://www.mprnews.org/story/2025/07/08/duluth-area-nurses-strike-citing-stalled-contract-negotiations', 'MPR News', '2025-07-08', null, null, null],
        ['DC33 sanitation workers picket on day three of a citywide municipal strike', 'https://billypenn.com/2025/07/03/philly-strike-afscme-district-council-33-updates-trash-emergency-services-libraries/', 'Billy Penn', '2025-07-03', null, null, null],
        ['An ICE Out rally of 500+ at Columbus City Hall against the immigration crackdown', 'https://www.wosu.org/news/2025-06-10/anti-ice-protesters-rally-in-downtown-columbus-against-trumps-immigration-crackdown', 'WOSU', '2025-06-10', null, null, null],
        ['1,400 nurses strike at USC Keck Hospital and the Norris Cancer Center', 'https://abc7.com/post/more-1400-nurses-go-strike-uscs-keck-hospital-norris-comprehensive-cancer-center/18091497/', 'ABC7 Los Angeles', '2025-10-31', null, null, null],
        ['Two arrested as counter-protesters disrupt a Charlie Kirk vigil on Boston Common', 'https://www.foxnews.com/us/antifa-agitators-disrupt-boston-charlie-kirk-vigil-2-arrested', 'Fox News', '2025-09-18', null, null, null],
        ['More than 70 protest the D11 school board over book-banning policies', 'https://www.rmpbs.org/blogs/education/d11-colorado-springs-protest', 'Rocky Mountain PBS', '2025-11-05', null, null, null],
        ['Climate activists arrested in Gov. Lamont\'s office over a gas pipeline', 'https://ctmirror.org/2025/11/17/ct-climate-natural-gas-expansion-protest/', 'CT Mirror', '2025-11-17', null, null, null],
        ['Bronstein tenants rally over a gas outage and repairs at a 72-unit building', 'https://qns.com/2025/11/sunnyside-rally-bronstein-tenants/', 'QNS', '2025-11-15', null, null, null],
        ['140 Legacy Health nurse practitioners and PAs strike in Portland', 'https://nwlaborpress.org/2025/12/nurse-practitioners-and-associate-physicians-strike-at-legacy/', 'NW Labor Press', '2025-12-02', null, null, null],
        ['Federal agents pepper-spray protesters and Rep. Grijalva at a Tucson ICE raid', 'https://azluminaria.org/2025/12/05/tucson-immigration-raid-pepper-spray-adelita-grijalva/', 'AZ Luminaria', '2025-12-05', null, null, null],
        ['About 1,000 march at Washington Square Park in an ICE Out for Good rally', 'https://www.sltrib.com/news/2026/01/10/salt-lake-city-ice-out-good/', 'The Salt Lake Tribune', '2026-01-10', null, null, null],
        ['Social Circle leaders rally at the state Capitol against a planned ICE detention center', 'https://www.gpb.org/news/2026/03/26/social-circle-community-leaders-gather-at-state-capitol-share-concerns-over-planned', 'Georgia Public Broadcasting', '2026-03-26', null, null, null],
        ['An interfaith coalition holds a vigil where ICE detained a neighbor in Tucson', 'https://azluminaria.org/2026/05/13/where-ice-detained-a-neighbor-tucson-communities-hold-a-vigil/', 'AZ Luminaria', '2026-05-09', null, null, null],
        ['A May Day march from Voces de la Frontera to the Federal Building in Milwaukee', 'https://postindustrial.com/stories/may-day-march-in-milwaukee-unites-immigrants-workers-against-trump-policies/', 'Postindustrial', '2026-05-01', null, null, null],
        // ===== round 14: geographic gap-fill (thin states & territories) =====
        ['Protesters use the College World Series as a backdrop to decry ICE raids', 'https://www.klkntv.com/protesters-rally-against-immigration-raids-outside-of-college-world-series-in-omaha/', 'KLKN-TV', '2025-06-13', 41.2669678, -95.9325486, 'Charles Schwab Field, Omaha, NE'],
        ['A Grand Island High ICE walkout ends in an altercation; one man arrested', 'https://www.1011now.com/2026/02/17/grand-island-police-arrest-one-cite-juvenile-after-gish-walk-out-incident/', '1011 NOW', '2026-02-16', 40.9421547, -98.3663464, 'Grand Island Senior High, Grand Island, NE'],
        ['About 150 gather in Grand Forks to protest ICE after the Renée Good killing', 'https://www.grandforksherald.com/news/local/around-150-gather-in-grand-forks-to-protest-ice-following-fatal-shooting-of-minneapolis-woman-renee-good', 'Grand Forks Herald', '2026-01-10', 47.9252529, -97.0305162, 'DeMers Ave & S Third St, Grand Forks, ND'],
        ['A reported JD Vance visit draws a protest at the Applied Digital data center site', 'https://www.grandforksherald.com/news/north-dakota/reported-jd-vance-visit-draws-impromptu-protest-at-north-dakota-data-center-site', 'Grand Forks Herald', '2026-01-12', 46.9900000, -96.8800000, 'Applied Digital site, Harwood, ND'],
        ['Fargo-Moorhead protesters gather amid a blizzard to oppose ICE actions', 'https://www.inforum.com/news/fargo/fargo-moorhead-protesters-gather-amid-blizzard-to-oppose-ice-actions', 'InForum', '2026-01-18', 46.8782488, -96.7872339, 'Broadway Square, Fargo, ND'],
        ['Hundreds in Minot join the nationwide No Kings protest', 'https://www.kfyrtv.com/2025/10/18/no-kings-protest-minot/', 'KFYR-TV', '2025-10-18', 48.2271952, -101.2940792, 'Town & Country Shopping Center, Minot, ND'],
        ['More than 1,000 line Beartracks Bridge for an ICE Out rally in Missoula', 'https://www.kpax.com/news/missoula-county/more-than-a-thousand-protesters-line-beartracks-bridge-for-ice-out-rally', 'KPAX', '2026-01-25', 46.8678501, -113.9966684, 'Beartracks Bridge, Missoula, MT'],
        ['MSU Students for a Democratic Society protest ICE actions in Bozeman', 'https://www.montanarightnow.com/bozeman/protest-against-ice-actions-held-by-msu-students-in-bozeman/article_ee8efd9d-7ca8-4a0d-8bbf-a5a51a6de73e.html', 'Montana Right Now', '2026-01-25', 45.6796098, -111.0418018, 'Gallatin County Courthouse, Bozeman, MT'],
        ['A crowd blocks CBP agents at the Clarion Hotel in Rock Springs', 'https://wyofile.com/ice-protest-at-rock-springs-hotel-prompts-police-response/', 'WyoFile', '2026-01-29', 41.5814373, -109.2596633, 'Clarion Hotel, 2518 Foothill Blvd, Rock Springs, WY'],
        ['Rep. Hageman exits a Casper town hall early amid jeers over ICE killings', 'https://oilcity.news/community/2026/01/28/rep-hageman-touts-earmarks-faces-fiery-ice-questions-in-casper/', 'Oil City News', '2026-01-27', 42.8237000, -106.3270000, 'Wheeler Concert Hall, Casper, WY'],
        ['About 35 rally at the Teton County Jail over 48-hour ICE holds', 'https://891khol.org/demonstrators-protest-sheriffs-ice-hold-policy/', 'KHOL 89.1 FM', '2026-02-28', 43.4775957, -110.7605646, 'Teton County Sheriff Office, 180 S King St, Jackson, WY'],
        ['Indivisible 605 holds an ICE Out For Good protest in Sioux Falls', 'https://www.dakotanewsnow.com/2026/01/12/indivisible-605-holds-ice-out-good-protest-sioux-falls/', 'Dakota News Now', '2026-01-11', 43.5148035, -96.7710965, 'Louise Ave & W 41st St, Sioux Falls, SD'],
        ['A community march for trans rights against the SB 244 bathroom bill in Lawrence', 'https://www2.ljworld.com/news/general-news/2026/mar/23/protesters-gather-in-south-park-to-oppose-anti-transgender-legislation/', 'Lawrence Journal-World', '2026-03-23', 38.9613752, -95.2359038, 'South Park, Lawrence, KS'],
        ['A vigil for Renée Good draws about 600 to Beartracks Bridge in Missoula', 'https://montanafreepress.org/2026/01/12/hundreds-gather-in-missoula-for-vigil-honoring-renee-good/', 'Montana Free Press', '2026-01-11', null, null, null],
        ['A Billings rally honors Alex Pretti and urges no ICE cooperation', 'https://www.ktvq.com/news/local-news/billings-rally-honors-alex-pretti-protests-immigration-enforcement', 'KTVQ', '2026-01-29', null, null, null],
        ['Hundreds rally on Mass Street after ICE detentions of five Lawrence residents', 'https://www.kctv5.com/2026/02/20/hundreds-rally-lawrence-after-ice-detentions-students-plan-second-demonstration/', 'KCTV5', '2026-02-20', null, null, null],
        ['Hundreds condemn ICE in a rally at the Kansas Statehouse', 'https://www.iolaregister.com/news/state-news/rally-fills-kansas-statehouse', 'Iola Register', '2026-01-14', null, null, null],
        ['Hundreds line Highway 95 in Coeur d\'Alene against ICE after the Pretti killing', 'https://www.kxly.com/news/hundreds-protest-immigration-enforcement-in-coeur-dalene/article_810e6b99-950e-45b8-b011-2c505044b713.html', 'KXLY', '2026-01-25', 47.6742994, -116.7811531, 'US-95 corridor, Coeur d\'Alene, ID'],
        ['An ICE Out rally on the Broadway Bridge in Idaho Falls; protesters hit by tomatoes', 'https://www.eastidahonews.com/2026/01/protestors-rally-again-against-ice-in-idaho-falls-but-this-time-get-hit-by-tomatoes/', 'East Idaho News', '2026-01-31', 43.4928506, -112.0445146, 'Broadway Bridge, Idaho Falls, ID'],
        ['750-1,000 march from Caldwell Park to the courthouse in Pocatello', 'https://www.eastidahonews.com/2026/01/photo-gallery-hundreds-gather-in-pocatello-to-protest-us-immigration-enforcement/', 'East Idaho News', '2026-01-31', 42.8677561, -112.4405650, 'Caldwell Park, Pocatello, ID'],
        ['Over 1,000 northern Nevadans rally on Carson Street against ICE', 'https://mynews4.com/news/local/hundreds-of-northern-nevadans-gather-in-carson-city-for-anti-ice-protest', 'News4 KRNV', '2026-01-10', 39.1674877, -119.7672960, 'Carson Street, Carson City, NV'],
        ['Reno becomes the first Nevada city to pause new data centers after a packed meeting', 'https://www.reviewjournal.com/news/environment/this-nevada-city-is-the-first-to-pause-new-data-centers-3824360/', 'Las Vegas Review-Journal', '2026-05-14', 39.5259235, -119.8128858, 'Reno City Hall, 1 E 1st St, NV'],
        ['About 120 rally at the Chaves County Courthouse for No Kings in Roswell', 'https://nmpoliticalreport.com/2025/06/14/thousands-of-new-mexicans-turn-out-in-cities-big-and-small-for-no-kings-protests/', 'NM Political Report', '2025-06-14', 33.3969800, -104.5217604, 'Chaves County Courthouse, Roswell, NM'],
        ['Demonstrators protest Sen. Dan Sullivan at the Alaska Capitol before a speech', 'https://www.juneauempire.com/2026/02/20/we-do-not-consent-demonstrators-protest-us-sen-dan-sullivan-ahead-of-legislative-speech/', 'Juneau Empire', '2026-02-18', 58.3021659, -134.4101030, 'Alaska State Capitol, Juneau, AK'],
        ['Carson High students walk out along Saliman Road over ICE killings', 'https://www.nevadaappeal.com/news/2026/jan/29/carson-high-students-protest-ice-with-walkout/', 'Nevada Appeal', '2026-01-29', null, null, null],
        ['A Washoe County district-wide student walkout converges on downtown Reno', 'https://www.kunr.org/local-stories/2026-02-02/washoe-county-students-walkout-as-part-of-national-protest-against-ice', 'KUNR', '2026-01-30', null, null, null],
        ['Dozens at a PSL Anchorage Stand with Minnesota anti-ICE rally', 'https://www.adn.com/alaska-news/anchorage/2026/01/23/dozens-of-anchorage-residents-stand-with-minnesota-against-ice-at-protest/', 'Anchorage Daily News', '2026-01-24', null, null, null],
        ['Bowling Green residents protest ICE raids and the detention of teen Ernesto Manuel-Andres', 'https://www.wbko.com/2025/06/12/bowling-green-residents-protest-immigration-raids/', 'WBKO', '2025-06-11', 36.9815150, -86.4658930, 'Russellville Rd & Morgantown Rd, Bowling Green, KY'],
        ['Six arrested at a Homewood protest over the police killing of Jabari Peoples', 'https://abc3340.com/news/local/alabama-protest-in-homewood-leads-to-six-arrests-for-disorderly-conduct-august-2025-jabari-peoples', 'ABC 33/40', '2025-08-22', 33.4846626, -86.7917993, '18th Street South, Homewood, AL'],
        ['An Aiken Workers Over Billionaires Labor Day rally', 'https://www.wrdw.com/2025/09/01/csra-protesters-make-voices-heard-workers-over-billionaires-rally/', 'WRDW', '2025-09-01', 33.5277656, -81.7206405, 'Odell Weeks Center, Aiken, SC'],
        ['Mountaineers Indivisible holds a Workers Over Billionaires protest at WVU', 'https://www.wdtv.com/2025/09/01/mica-protests-front-wvu-coliseum-joins-national-workers-over-billionaires-effort/', 'WDTV', '2025-09-01', 39.6494459, -79.9814036, 'WVU Coliseum, Morgantown, WV'],
        ['A No Kings rally draws a crowd at the Houston County Courthouse in Dothan', 'https://www.wtvy.com/2025/10/18/no-kings-rally-draws-crowd-dothan/', 'WTVY', '2025-10-18', 31.2240133, -85.3930347, 'Houston County Courthouse, Dothan, AL'],
        ['Lafayette\'s second No Kings rally draws about 400', 'https://www.theadvocate.com/acadiana/multimedia/photos/photos-lafayette-no-kings-protest-draws-hundreds/collection_fe35b430-032d-497b-a56b-47bcadd64ef4.html', 'The Advocate', '2025-10-18', 30.2225384, -92.0199959, 'Old City Hall, 217 W Main St, Lafayette, LA'],
        ['Tucker County residents rally against a data center at a WVDEP air-quality hearing', 'https://wvmetronews.com/2025/12/03/protesters-oppose-tucker-county-data-center-as-air-quality-board-considers-permit-appeal/', 'WV MetroNews', '2025-12-03', 38.3104715, -81.5659790, 'WV DEP HQ, 601 57th St SE, Charleston, WV'],
        ['Hundreds march in Columbia after an ICE agent kills a Minneapolis woman', 'https://www.wistv.com/2026/01/08/live-protestors-gather-sc-state-house-after-ice-officer-kills-woman-minneapolis/', 'WIS-TV', '2026-01-08', 34.0007540, -81.0352313, 'SC Statehouse, Columbia, SC'],
        ['An anti-ICE protest fills Noble Park in Paducah after an ICE shooting', 'https://www.wkyufm.org/news/2026-01-10/anti-ice-protests-draw-crowds-across-parts-of-western-kentucky-saturday', 'WKU Public Radio', '2026-01-10', 37.0869830, -88.6384887, 'Noble Park, Paducah, KY'],
        ['Marshall County residents rally against a proposed ICE detention facility', 'https://www.fox13memphis.com/news/protestors-marshall-county-leaders-react-to-rumors-of-ice-detention-facility-coming-to-north-mississippi/article_f328d967-be04-4ae3-a518-6459effe2b15.html', 'FOX13 Memphis', '2026-01-16', 34.8718000, -89.6873000, 'Interchange Commerce Center, Byhalia, MS'],
        ['Little Rock Central High students walk out and march to the Arkansas Capitol over ICE', 'https://arktimes.com/arkansas-blog/2026/02/06/little-rock-high-school-students-walk-out-in-protest-of-ice', 'Arkansas Times', '2026-02-06', 34.7374667, -92.3001982, 'Little Rock Central High, Little Rock, AR'],
        ['A rally for an ICE-detained 18-year-old outside Rep. Brett Guthrie\'s office', 'https://www.wkyufm.org/news/2025-06-20/bowling-green-community-rallies-in-support-of-18-year-old-arrested-by-ice-ahead-of-bond-hearing', 'WKU Public Radio', '2025-06-20', null, null, null],
        ['Over a dozen protest ICE outside the Little Rock Homeland Security office', 'https://katv.com/news/local/protestors-gather-outside-little-rock-homeland-security-office-to-oppose-ice-deportations-fbi-agency-community-family-inhumane-treatment-immigrants-deportation-roots-arkansas-safety', 'KATV', '2025-12-19', null, null, null],
        ['A Columbia anti-ICE rally on the national strike day marches to City Hall', 'https://www.southcarolinapublicradio.org/sc-news/2026-02-01/sc-students-activists-protest-ice-activity-on-day-of-national-strikes', 'SC Public Radio', '2026-01-30', null, null, null],
        ['Hundreds protest a planned ICE office at 1441 Main Street in Columbia', 'https://www.wistv.com/2026/02/13/columbia-citizens-hold-protest-against-ice-office-set-open-main-street/', 'WIS-TV', '2026-02-13', null, null, null],
        ['Newark joins the nationwide No Kings protest at UD North Green', 'https://www.wdel.com/news/thousands-attend-local-no-kings-rallies/article_c26c15be-ee36-4723-95ad-0a8944d4a0b1.html', 'WDEL', '2025-10-18', 39.6873058, -75.7804390, 'UD North Green, Newark, DE'],
        ['Indivisible Southern Delaware holds an anti-ICE protest honoring Renée Good', 'https://www.coasttv.com/news/indivisible-southern-delaware-gathers-community-members-for-ice-protest-in-rehoboth-beach/article_f77d2c25-286b-4b3b-ac9c-a12d9ab8c2f7.html', 'CoastTV', '2026-01-10', 38.7164771, -75.0835107, 'Route 1, Rehoboth Beach, DE'],
        ['An ICE Out For Good protest along Concord Pike at the First Unitarian Church', 'https://www.wdel.com/news/anti-ice-protests-planned-in-delaware-and-across-u-s/article_254b7f28-e081-49a6-81f3-79fb054d3e14.html', 'WDEL', '2026-01-11', 39.8008416, -75.5499525, 'First Unitarian Church, 730 Halstead Rd, Wilmington, DE'],
        ['Hundreds join a No Kings rally at Ladies Wildwood Park in Keene', 'https://mykeenenow.com/news/219912-hundreds-join-no-kings-protests-in-keene-and-walpole-as-movement-sweeps-new-hampshire/', 'My Keene Now', '2025-10-18', 42.9404931, -72.3095987, 'Ladies Wildwood Park, Keene, NH'],
        ['A No Dictators rally draws 500 along Kaumualii Highway in Lihue', 'https://kauainownews.com/2025/10/18/kauais-no-dictators-rally-draws-500-as-part-of-nationwide-protest-against-president-trump/', 'Kauai Now', '2025-10-18', 21.9745792, -159.3753256, 'Kaumualii Highway, Lihue, HI'],
        ['No Kings demonstrators line Kaahumanu Avenue in Kahului', 'https://mauinow.com/2025/06/15/no-kings-events-across-the-state-draw-crowds-in-protest-of-trump-administration/', 'Maui Now', '2025-06-14', 20.8893376, -156.4726987, 'Kaahumanu Avenue, Kahului, HI'],
        ['Thousands rally against Trump and an ICE detention center in Hagerstown', 'https://localnews1.org/no-kings-rally-draws-thousands-to-hagarstown-against-trump-policies-and-ice-detention-center/', 'LocalNews1', '2026-03-30', 39.6420041, -77.7203299, 'Washington & Potomac St, Hagerstown, MD'],
        ['A Danbury rally against ICE detentions outside the Superior Courthouse', 'https://ctmirror.org/2025/06/17/danbury-ice-presence/', 'CT Mirror', '2025-06-17', 41.3986207, -73.4481365, 'Danbury Superior Courthouse, White St, CT'],
        ['A New Britain City Hall rally after an East Street car wash ICE raid', 'https://www.wfsb.com/2025/08/06/community-members-rally-outside-new-britain-city-hall-protest-ice-after-raid-car-wash/', 'WFSB', '2025-08-05', 41.6677673, -72.7825506, 'New Britain City Hall, 27 W Main St, CT'],
        ['A Westerly No Kings rally at Old Benny\'s in Dunns Corner', 'https://www.yahoo.com/news/no-kings-protests-locations-ri-090935006.html', 'Rhode Island Current', '2025-06-14', 41.3517023, -71.7674471, '248 Post Rd, Dunns Corner, Westerly, RI'],
        ['Tito Kayak arrested at an anti-government protest outside La Fortaleza', 'https://www.metro.pr/noticias/2025/10/06/manifestantes-repudian-politicas-del-gobierno-frente-a-fortaleza/', 'Metro Puerto Rico', '2025-10-06', 18.4641779, -66.1191388, 'La Fortaleza, Old San Juan, PR'],
        ['Madres Contra la Guerra protest US militarization at Muniz Air Base', 'https://www.elnuevodia.com/noticias/locales/notas/no-queremos-guerra-en-nuestro-continente-ciudadanos-se-manifiestan-en-contra-de-la-presencia-militar-en-la-isla/', 'El Nuevo Dia', '2025-09-07', 18.4425919, -65.9933397, 'Muniz Air National Guard Base, Carolina, PR'],
        ['A No Kings march along Veterans Drive to the Ron de Lugo courthouse in St. Thomas', 'https://www.virginislandsdailynews.com/news/no-kings-protesters-recall-decades-long-fight-for-civil-liberties-voting-rights/article_b85d6059-3c69-4031-9299-f62d9c3e1bb5.html', 'Virgin Islands Daily News', '2025-10-18', 18.3405163, -64.9268025, 'Ron de Lugo Federal Building, Veterans Dr, St. Thomas, Charlotte Amalie, VI'],
        // ===== round 15: cause-based deep dives (prosecutions/verdicts, climate & Indigenous, pro-Palestine & campus, abortion & trans/LGBTQ) =====
        ['Tyler McGrath acquitted of "assault by airhorn" at a Portland No Kings protest', 'https://www.pressherald.com/2026/04/03/no-kings-protester-acquitted-after-portland-police-accused-him-of-assault-by-airhorn/', 'Portland Press Herald', '2026-03-18', 43.659322, -70.253968, 'Cumberland County Courthouse, Portland, ME'],
        ['Heather Morrow\'s felony assault charge is dropped and refiled as misdemeanors after a Charlotte ICE protest', 'https://www.wbtv.com/2025/11/26/charlotte-woman-disputes-federal-charges-after-protesting-border-patrol-activity-outside-dhs-office/', 'WBTV', '2025-11-25', 35.229910, -80.846322, 'Charles R. Jonas Federal Building, Charlotte, NC'],
        ['Three Ostrouchko family members indicted over an assault on a TPUSA reporter at a Minneapolis ICE protest', 'https://www.fox9.com/news/tpusa-reporter-savanah-hernandez-attack-2-indicted-federal-charges', 'FOX 9', '2026-04-29', 44.894558, -93.197082, 'Bishop Henry Whipple Federal Building, Minneapolis, MN'],
        ['Brendan Geier charged with biting ICE officers at the Newark Delaney Hall protest', 'https://www.yahoo.com/news/us/articles/man-charged-assaulting-federal-officers-095644023.html', 'Yahoo News', '2026-05-28', 40.731163, -74.173614, 'Frank R. Lautenberg U.S. Courthouse, Newark, NJ'],
        ['Sidney Reid acquitted of assaulting an FBI agent at a DC immigration protest', 'https://www.ms.now/deadline-white-house/deadline-legal-blog/sidney-reid-not-guilty-verdict-ice-trump-doj-rcna238137', 'MSNBC', '2025-10-16', null, null, null],
        ['Luis Hipolito acquitted of punching an ICE agent during a downtown LA immigration sweep', 'https://www.cbsnews.com/losangeles/video/man-accused-of-punching-ice-agent-during-downtown-la-immigration-sweep-acquitted-by-jury/', 'CBS Los Angeles', '2026-03-31', null, null, null],
        ['CSU professor Jonathan Caravello acquitted of assaulting a Border Patrol agent at the Camarillo ICE raid', 'https://www.nbclosangeles.com/news/local/jury-acquits-csu-channel-islands-professor-federal-ice-assault-case/3874052/', 'NBC Los Angeles', '2026-04-09', null, null, null],
        ['SEIU leader David Huerta\'s felony is reduced to a misdemeanor; he pleads not guilty in the LA ICE-protest case', 'https://www.cbsnews.com/news/david-huerta-california-labor-felony-charge-immigration-protest-reduced/', 'CBS News', '2025-11-25', null, null, null],
        ['David Cox arrested over a threat to firebomb ICE agents at a NYC No Kings protest', 'https://abc7ny.com/post/man-charged-making-terroristic-threats-firebomb-ice-agents-no-kings-protest-nyc/18038138/', 'ABC7 New York', '2025-10-18', null, null, null],
        ['Daryn Herzberg sues DHS for excessive force after being tackled at the Portland ICE building', 'https://www.opb.org/article/2026/03/03/protester-sues-trump-administration-excessive-force-ice-building/', 'OPB', '2026-03-02', null, null, null],
        ['Indigenous-led Line 5 tunnel procession at the Mackinac Policy Conference', 'https://radio.wcmu.org/local-regional-news/2025-05-29/line-5-tunnel-protestors-rally-on-mackinac-island-call-on-state-to-reject-permits', 'WCMU Public Radio', '2025-05-29', 45.852163, -84.617365, 'Fort Mackinac, Mackinac Island, MI'],
        ['A coalition rallies against Elon Musk\'s xAI gas turbines near Memphis', 'https://dailymemphian.com/article/52376/coalition-protests-elon-musk-xai-gas-turbine-plans-memphis-mississippi', 'Daily Memphian', '2025-06-09', 34.988149, -90.019536, 'Springfield Baptist Church, Southaven, MS'],
        ['Residents pack a listening session against a 675-acre hyperscale data center', 'https://www.wbrc.com/2025/09/14/bessemer-residents-frustrated-over-proposed-data-center/', 'WBRC', '2025-09-13', 33.401777, -86.954437, 'Bessemer, AL'],
        ['Over 100 rally to stop the Williams/Transco NESE fracked-gas pipeline', 'https://www.foodandwaterwatch.org/2025/09/10/100-rally-in-monmouth-county-to-stop-nese-gas-pipeline/', 'Food and Water Watch', '2025-09-09', 40.440064, -74.096573, 'Bayshore Waterfront Park, Middletown, NJ'],
        ['Climate Defiance and Sunrise disrupt an Occidental CEO at a Harvard symposium', 'https://insideclimatenews.org/news/19092025/activists-disrupt-fossil-fuel-executive-at-harvard-symposium/', 'Inside Climate News', '2025-09-17', 42.371311, -71.121305, 'Harvard University, Cambridge, MA'],
        ['Tigers Against Pollution march against the xAI data center', 'https://www.aol.com/articles/memphis-activists-stage-downtown-march-005536611.html', 'Commercial Appeal', '2025-10-04', 35.136193, -90.051248, 'I AM A MAN Plaza, Memphis, TN'],
        ['An Apache Stronghold faith vigil to protect Oak Flat from the Resolution Copper mine', 'https://pcusa.org/news-storytelling/news/2025/10/16/faith-leaders-amplify-need-protect-religious-freedom-oak-flat-vigil', 'Presbyterian Church (USA)', '2025-10-13', 33.308600, -111.049700, 'Oak Flat, Tonto National Forest, Superior, AZ'],
        ['Residents protest a Dominion data-center transmission line at an open house', 'https://www.fox5dc.com/news/prince-william-county-residents-once-again-protest-against-proposed-data-centers', 'FOX 5 DC', '2025-11-05', 38.756582, -77.521175, 'George Mason University SciTech campus, Manassas, VA'],
        ['Demonstrators rally at TVA HQ against the Ridgeline methane gas pipeline', 'https://appvoices.org/2025/11/06/rally-to-protest-construction-of-ridgeline-pipeline/', 'Appalachian Voices', '2025-11-06', 35.966733, -83.920657, 'Tennessee Valley Authority headquarters, Knoxville, TN'],
        ['Activists blockade the Wells Fargo HQ over abandoned climate goals; 7 arrested', 'https://insideclimatenews.org/news/07082025/wells-fargo-drops-climate-commitments-protest/', 'Inside Climate News', '2025-07-23', null, null, null],
        ['A Gulf South "Toxic Billionaires Tour" marches on LNG financiers AIG and BlackRock', 'https://www.ran.org/press-releases/over-70-gulf-south-fossil-fuel-fighters-partner-with-nyc-social-justice-groups-for-a-week-of-action-confronting-the-financiers-of-climate-chaos/', 'Rainforest Action Network', '2025-07-30', null, null, null],
        ['Coal miners and advocates rally over a stalled silica-dust mine safety rule', 'https://appvoices.org/2025/10/14/coal-miners-advocates-rally-in-washington/', 'Appalachian Voices', '2025-10-14', null, null, null],
        ['Climate Defiance blocks an elevator over Hochul\'s climate budget deal; arrests made', 'https://www.mediasanctuary.org/stories/2026/climate-activists-arrested-protesting-climate-budget-deal/', 'The Sanctuary for Independent Media', '2026-05-05', null, null, null],
        ['UT Dallas graduates walk out of commencement over weapons investments; 2 arrested', 'https://retrogradenews.com/2025/07/21/student-protesters-at-spring-2025-graduation-face-chokeholds-arrests/', 'Retrograde News', '2025-05-16', 32.978329, -96.745489, 'University of Texas at Dallas, Richardson, TX'],
        ['10 arrested blockading a ZIM weapons shipment at Port Newark-Elizabeth', 'https://wagingnonviolence.org/2026/05/pro-palestine-activists-arrested-blockading-new-jersey-port/', 'Waging Nonviolence', '2026-05-22', 40.685697, -74.162584, 'Maher Terminals, Port Newark-Elizabeth, Elizabeth, NJ'],
        ['UCLA Nakba Day demonstration; 2 arrested as SJP marches across campus', 'https://dailybruin.com/2025/05/15/2-protesters-arrested-during-on-campus-demonstration-remembering-nakba-day', 'Daily Bruin', '2025-05-15', null, null, null],
        ['Portland police arrest 20 at an Old Port Gaza die-in blocking traffic', 'https://www.mainepublic.org/maine/2025-05-21/portland-protesters-block-traffic-plan-hunger-strike-to-call-for-end-of-israel-gaza-war', 'Maine Public', '2025-05-21', null, null, null],
        ['CUNY Law graduates walk out of commencement for Palestine', 'https://www.savageminds.co/p/cuny-law-students-walk-out-of-graduation', 'Savage Minds', '2025-05-22', null, null, null],
        ['138 arrested in a JVP sit-in at Sen. Padilla\'s San Francisco office over Gaza arms', 'https://www.democracynow.org/2025/8/28/headlines/jewish_peace_activists_demand_california_senators_end_support_for_israels_assault_on_gaza', 'Democracy Now!', '2025-08-28', null, null, null],
        ['56 arrested as Rabbis for Ceasefire block the Brooklyn Bridge on Yom Kippur', 'https://www.commondreams.org/news/rabbi-protest-new-york', 'Common Dreams', '2025-10-02', null, null, null],
        ['About 100 arrested in a JVP sit-in demanding Schumer and Gillibrand vote no on Israel arms', 'https://www.commondreams.org/news/anti-israel-protesters-arrested', 'Common Dreams', '2026-04-13', null, null, null],
        ['Activists arrested protesting the removal of the Pulse memorial rainbow crosswalk', 'https://www.wftv.com/news/local/state-attorney-worrell-address-pulse-rainbow-crosswalk-protest-cases/LRGHP65XPJG67ANHQUO2P6UG4U/', 'WFTV', '2025-08-31', 28.520083, -81.376434, 'Pulse memorial crosswalk, Orange Avenue, Orlando, FL'],
        ['An activist strips at a Davis school board meeting to protest a trans bathroom policy', 'https://edsource.org/updates/woman-strips-at-school-board-meeting-to-protest-bathroom-policy', 'EdSource', '2025-09-18', 38.546773, -121.745436, 'Davis Joint Unified School District, Davis, CA'],
        ['An anti-abortion group leader is charged in a shooting outside a Columbia Planned Parenthood', 'https://www.wistv.com/2025/11/25/man-charged-shooting-outside-columbia-planned-parenthood/', 'WIS-TV', '2025-11-14', 34.015485, -81.004565, 'Planned Parenthood, Columbia, SC'],
        ['A Gorham drag storytime draws protesters and counter-protesters; police called', 'https://www.themainewire.com/2026/04/police-called-to-gorham-library-as-drag-queen-event-sparks-public-access-clash/', 'The Maine Wire', '2026-04-04', 43.676944, -70.441449, 'Baxter Memorial Library, Gorham, ME'],
        ['A man is arrested dousing an Appleton courthouse Pride flag with gasoline', 'https://fox11online.com/news/local/appleton-man-arrested-after-allegedly-dousing-courthouse-pride-flag-with-gasoline-outagamie-county-walnut-street-lgbtq-hate-crime-inclusive-division-june-wisconsin-steven-william-paltzer-police-jail', 'FOX 11 News', '2026-06-01', 44.259499, -88.411991, 'Outagamie County Courthouse, Appleton, WI'],
        ['A Protect Trans Futures march for trans healthcare to the Massachusetts Statehouse', 'https://www.assignedmedia.org/breaking-news/trans-activists-march-boston-healthcare', 'Assigned Media', '2026-02-08', 42.358602, -71.063875, 'Massachusetts State House, Boston, MA'],
        ['A TransYOUniting rally against UPMC ending gender-affirming care for trans youth', 'https://www.wesa.fm/politics-government/2025-06-29/advocates-rally-in-support-of-trans-health-care-in-front-of-upmc-building', 'WESA', '2025-06-29', null, null, null],
        ['A Unite for Trans Youth Lives rally over hospital gender-care cuts', 'https://windycitytimes.com/2025/08/12/chicago-protest-targets-hospital-cuts-to-gender-affirming-care-for-youth/', 'Windy City Times', '2025-08-10', null, null, null],
    ];

    /**
     * Per-event category override; every link defaults to "protest" (a
     * demonstration / march / sit-in). The URLs below reclassify an event as
     * "arrest" (a targeted detention — data-center-meeting, threat or sabotage
     * arrests, ICE detentions, named journalist / official arrests),
     * "prosecution" (charges, indictments or sentencing) or "other" (a shooting
     * or surveillance reporting). Additional sources inherit their event's marker.
     */
    private array $categories = [
        'arrest' => [
            // Claremore, OK — data-center town hall arrest
            'https://www.newson6.com/tulsa-oklahoma-news/arrest-made-during-heated-claremore-meeting-over-proposed-data-center',
            'https://ktul.com/news/local/community-activist-arrested-at-data-center-meeting-in-claremore',
            'https://www.businessinsider.com/data-center-meeting-claremore-oklahoma-man-arrested-beale-infrastructure-ai-2026-2',
            'https://www.tomshardware.com/tech-industry/big-tech/oklahoma-farmer-arrested-and-jailed-for-trespassing-during-ai-data-center-town-hall-removed-by-officers-after-going-a-few-seconds-over-allotted-speaking-time-trying-to-hand-paperwork-to-counselors',
            'https://africa.businessinsider.com/news/tension-over-a-proposed-ai-data-center-leads-to-an-arrest-in-oklahoma/jk912pd',
            // Port Washington, WI — data-center hearing arrests
            'https://www.datacenterdynamics.com/en/news/three-arrested-at-data-center-hearing-in-port-washington-wisconsin/',
            'https://www.fox6now.com/news/port-washington-data-center-meeting-arrests',
            'https://www.fox6now.com/news/port-washington-data-center-concerns-arrests-city-meeting',
            'https://spectrumnews1.com/wi/milwaukee/news/2025/12/07/port-washington-data-center-peaceful-protest-arrest',
            'https://www.alternet.org/woman-violently-arrested-after-speaking-out-against-ai-data-centers/',
            'https://ozaukeepress.com/content/meeting-erupts-chaos-over-data-center',
            // Columbus, OH — blogger arrested outside Statehouse
            'https://signalohio.org/progressive-blogger-the-rooster-arrested-outside-statehouse-charged-with-harassment/',
            // Dixon, IL — data-center threats arrest
            'https://www.datacenterdynamics.com/en/news/illinois-man-arrested-after-threatening-local-authorities-to-stop-data-center-development/',
            'https://www.businessinsider.com/dixon-illinois-data-center-development-critic-arrested-2026-5',
            'https://hoodline.com/2026/05/dixon-man-busted-after-threats-over-rock-falls-data-center-site/',
            // El Centro, CA — board-meeting arrest + online-threats arrest
            'https://www.latimes.com/california/story/2026-04-11/man-speaking-against-data-center-arrested-at-imperial-county-board-meeting-as-tensions-flare-nationwide',
            'https://www.kpbs.org/news/public-safety/2026/04/20/el-centro-resident-arrested-for-allegedly-making-online-threats-against-data-center-developer',
            // Andover Township, NJ — town-meeting arrest
            'https://thenerdstash.com/new-jersey-man-arrested-at-town-meeting-after-confronting-officials-over-secret-ai-data-center-deal-how-much-are-they-paying-you/',
            // Hobart, IN — data-center meeting arrest
            'https://www.chicagotribune.com/2026/05/09/hobart-meeting-on-data-centers-brings-large-crowd-tight-security-and-one-arrest/',
            'https://www.fox32chicago.com/news/video-shows-man-removed-arrested-indiana-data-center-meeting',
            'https://www.fox32chicago.com/video/fmc-qtdelt547oamiybt',
            // Brooklyn, NY — Council member Chi Ossé arrested
            'https://ny1.com/nyc/brooklyn/news/2026/04/22/councilmember-chi-oss--arrested-during-eviction-dispute-in-brooklyn',
            'https://www.democracynow.org/2026/4/23/headlines/nyc_councilmember_chi_osse_released_after_violent_arrest_at_anti_eviction_protest',
            // Hart Senate Office Building, DC — Marine veteran Brian McGinnis arrested
            'https://www.military.com/feature/2026/03/05/brian-mcginnis-removed-senate-hearing-after-protest-over-us-policy-toward-israel.html',
            'https://www.democracynow.org/2026/3/11/brian_mcginnis_iran_war_protest_congress',
            'https://abc11.com/post/marine-veteran-north-carolina-charged-protesting-war-iran-senate-hearing/18679829/',
            // St. Paul, MN — journalists Don Lemon & Georgia Fort arrested
            'https://www.aljazeera.com/news/2026/1/30/journalist-don-lemon-arrested-in-connection-to-minnesota-ice-protest',
            'https://www.nbcnews.com/news/us-news/don-lemon-arrested-federal-authorities-attorney-says-rcna256680',
            // Newark, NJ — Mayor Ras Baraka arrested at Delaney Hall
            'https://www.cbsnews.com/newyork/news/newark-mayor-ras-baraka-ice-arrest/',
            'https://www.washingtonpost.com/nation/2025/05/09/newark-mayor-ice-arrest-ras-baraka-nj/',
            // Manhattan, NY — Comptroller Brad Lander detained by ICE
            'https://www.cnn.com/2025/06/17/us/brad-lander-ice-arrest-nyc',
            'https://www.thecity.nyc/2025/06/17/brad-lander-arrest-ice-immigration-court/',
            // DeKalb County, GA — journalist Mario Guevara arrested
            'https://www.11alive.com/article/news/local/protests/bodycam-video-salvadoran-journalist-arrested-dekalb-county-mario-guevara/85-8de24d09-dfb6-4546-be0e-7d1bb9e393f3',
            'https://atlantaciviccircle.org/2025/06/18/dekalb-police-journalist-mario-guevara-ice-custody/',
            // Worcester, MA — ICE detention of a mother
            'https://www.bostonglobe.com/2025/05/08/metro/ice-arrests-worcester-woman-spurs-protest/',
            'https://www.boston.com/news/local-news/2025/05/08/two-arrested-after-neighbors-try-to-stop-ice-agents-from-detaining-worcester-mother/',
            'https://www.wbur.org/news/2025/05/16/worcester-police-ice-arrest-protesters-activists',
            // Woodland Park, CO — cell-tower sabotage arrest
            'https://www.datacenterdynamics.com/en/news/man-arrested-for-causing-cell-tower-outage-in-colorado/',
            // Trinidad, TX — Facebook-post arrest
            'https://www.fox4news.com/news/woman-arrested-facebook-post-concerning-trinidad-water-poisoning',
            // Met Gala protest (Bezos-sponsored) — Chris Smalls arrested
            'https://hyperallergic.com/rollicking-protest-against-bezoss-met-gala-erupts-in-manhattan/',
            // Charlotte, NC — woman charged with assaulting an officer at an ICE protest
            'https://www.wfae.org/crime-justice/2025-11-21/charlotte-woman-faces-federal-charge-missing-property-after-arrest-at-ice-protest',
            // round 3 — Cal Poly Humboldt occupation arrest
            'https://lostcoastoutpost.com/2026/mar/6/three-students-handed-interim-suspensions-nelson-h/',
            // round 4 — Fairhope AL inflatable-costume arrest at a No Kings protest
            'https://alabamareflector.com/2025/10/20/fairhope-police-arrest-woman-in-penis-costume-at-no-kings-protest/',
            // round 5 — pro-Palestine activist detentions (Khalil, Öztürk)
            'https://www.npr.org/2025/06/20/nx-s1-5440351/judge-orders-release-of-columbia-activist-mahmoud-khalil',
            'https://www.npr.org/2025/05/09/nx-s1-5393055/tufts-student-rumeysa-ozturk-ordered-freed-from-immigration-detention',
            // local — Colorado activist Jeanette Vizguerra freed from Aurora ICE jail
            'https://coloradosun.com/2025/12/22/jeanette-vizguerra-released-ice/',
            // elected officials detained at protests
            'https://missionlocal.org/2026/05/s-f-supervisors-past-and-present-arrested-at-sfo-anti-ice-protest/',
            'https://www.cbsnews.com/chicago/news/oak-park-officials-stand-with-protesters-broadview-ice-facility/',
            // round 12 — targeted arrests / attacks at protests
            'https://www.nbcwashington.com/news/local/northern-virginia/man-accused-of-driving-through-crowd-of-protesters-in-culpeper/3936544/',
            'https://abc7.com/post/police-arrest-driver-who-plowed-into-crowd-no-kings-protest-riverside/16766376/',
            'https://352today.com/news/257752-three-arrested-during-no-kings-rally-in-ocalas-downtown-square/',
            'https://tennesseelookout.com/briefs/one-arrested-at-peaceful-no-kings-protest-in-nashville/',
            'https://abc7.com/post/cal-state-channel-islands-professor-jonathan-caravello-arrested-during-camarillo-immigration-raid-due-court-monday/17117396/',
            'https://en.wikipedia.org/wiki/Trial_of_Sean_Dunn',
            'https://www.cbsnews.com/chicago/news/protest-broadview-ice-facility-police-noem/',
            'https://wpde.com/news/local/arrested-suspect-no-kings-protest-myrtle-beach-chapin-memorial-park-drive-by-police-department-400-14th-avenue-north-woman-location',
            'https://www.wbtv.com/2025/11/19/charlotte-woman-accused-assaulting-federal-officer-over-weekend-officials-say/',
            'https://www.ms.now/news/anti-abortion-clinic-protests',
            'https://ny1.com/nyc/manhattan/news/2026/01/28/anti-ice-protest-inside-tribeca-hilton-leads-to-dozens-of-arrests',
            'https://www.mprnews.org/story/2026/03/02/whipple-building-ice-protestors-cited-1-arrested-sunday',
            'https://www.ksl.com/article/51468395/3-arrested-as-hundreds-protest-new-ice-purchase-of-salt-lake-warehouse',
            'https://www.wosu.org/politics-government/2026-03-30/congressional-candidate-arrested-at-grove-city-no-kings-protest-after-using-megaphone',
            'https://qns.com/2026/05/chuck-park-arrested-may-day/',
            'https://www.ksl.com/article/51495984/protesters-at-ice-detention-facility-arrested-for-allegedly-interfering-with-traffic-stop',
            // round 13
            'https://spectrumnews1.com/oh/columbus/news/2025/06/10/multiple-protests--protestor-jailed-after-most-recent-illegal-immigration-arrest-',
            'https://www.wfxrtv.com/news/regional-news/virginia-news/protestors-charged-after-locking-themselves-to-mountain-valley-pipeline-equipment-in-montgomery-co/',
            'https://www.nbcphiladelphia.com/news/local/quakertown-community-high-school-ice-protest-clash-discussion/4360196/',
            'https://ctmirror.org/2025/11/17/ct-climate-natural-gas-expansion-protest/',
            // round 14
            'https://www.1011now.com/2026/02/17/grand-island-police-arrest-one-cite-juvenile-after-gish-walk-out-incident/',
            'https://abc3340.com/news/local/alabama-protest-in-homewood-leads-to-six-arrests-for-disorderly-conduct-august-2025-jabari-peoples',
            'https://www.metro.pr/noticias/2025/10/06/manifestantes-repudian-politicas-del-gobierno-frente-a-fortaleza/',
            // round 15
            'https://abc7ny.com/post/man-charged-making-terroristic-threats-firebomb-ice-agents-no-kings-protest-nyc/18038138/',
            'https://insideclimatenews.org/news/07082025/wells-fargo-drops-climate-commitments-protest/',
            'https://www.mediasanctuary.org/stories/2026/climate-activists-arrested-protesting-climate-budget-deal/',
            'https://retrogradenews.com/2025/07/21/student-protesters-at-spring-2025-graduation-face-chokeholds-arrests/',
            'https://wagingnonviolence.org/2026/05/pro-palestine-activists-arrested-blockading-new-jersey-port/',
            'https://dailybruin.com/2025/05/15/2-protesters-arrested-during-on-campus-demonstration-remembering-nakba-day',
            'https://www.mainepublic.org/maine/2025-05-21/portland-protesters-block-traffic-plan-hunger-strike-to-call-for-end-of-israel-gaza-war',
            'https://www.democracynow.org/2025/8/28/headlines/jewish_peace_activists_demand_california_senators_end_support_for_israels_assault_on_gaza',
            'https://www.commondreams.org/news/rabbi-protest-new-york',
            'https://www.commondreams.org/news/anti-israel-protesters-arrested',
            'https://www.wftv.com/news/local/state-attorney-worrell-address-pulse-rainbow-crosswalk-protest-cases/LRGHP65XPJG67ANHQUO2P6UG4U/',
            'https://www.wistv.com/2025/11/25/man-charged-shooting-outside-columbia-planned-parenthood/',
            'https://fox11online.com/news/local/appleton-man-arrested-after-allegedly-dousing-courthouse-pride-flag-with-gasoline-outagamie-county-walnut-street-lgbtq-hate-crime-inclusive-division-june-wisconsin-steven-william-paltzer-police-jail',
        ],
        'prosecution' => [
            // SEIU leader David Huerta charged with felony
            'https://www.cbsnews.com/news/david-huerta-seiu-charged-los-angeles-ice-protest-trump/',
            'https://laist.com/news/la-immigration-raids-protests-huerta-charged',
            // At least 71 charged after Los Angeles anti-ICE protests
            'https://lapublicpress.org/2025/08/ice-raids-la-arrests-charges/',
            // Nine Spokane ICE-protesters federally charged / indicted
            'https://www.krem.com/article/news/local/former-spokane-city-council-ben-stuckart-federally-indicted-ice-protests/293-b7211c4d-12e8-407d-b3d9-17f42c3cf6f1',
            'https://www.democracynow.org/2025/7/16/headlines/federal_agents_arrest_9_over_spokane_ice_protests_including_former_city_council_president',
            // Four protesters charged after the Omaha ICE raid
            'https://nebraskapublicmedia.org/en/news/news-articles/defendants-accused-of-interfering-with-law-enforcement-after-omaha-ice-raid-appear-in-federal-court/',
            // 11 pro-Palestine Stanford protesters indicted (president's-office takeover)
            'https://www.paloaltoonline.com/crime/2025/10/03/grand-jury-indicts-11-pro-palestine-stanford-protestors/',
            // 33 charged over the UW engineering-building occupation
            'https://www.kuow.org/stories/33-charged-in-occupation-and-vandalism-of-university-of-washington-engineering-building',
            // round 3 — anti-ICE / campus prosecutions
            'https://local12.com/news/nation-world/portland-man-pleads-guilty-to-arson-at-ice-facility-faces-up-to-20-years-in-prison-trenten-barker-oregon-multnomah-county-immigration-customs-enforcement',
            'https://www.opb.org/article/2026/02/20/portlander-pleads-guilty-hitting-ice-officer-protest/',
            'https://www.ksat.com/news/national/2026/02/01/man-sentenced-to-4-years-in-prison-for-throwing-molotov-cocktail-during-la-immigration-protest/',
            'https://www.nbclosangeles.com/news/local/paramount-federal-officer-assault-elpidio-reyna/3849329/',
            'https://www.cbsnews.com/losangeles/news/california-men-plead-guilty-violence-chp-officers-los-angeles-immigration-protests/',
            'https://www.foxla.com/news/ice-officer-stalking-livestream-conviction-los-angeles',
            'https://timesofsandiego.com/crime/2026/03/05/activist-home-detention-no-prison-federal-agent/',
            'https://www.pbs.org/newshour/nation/read-the-full-indictment-against-don-lemon-georgia-fort-and-others-charged-in-minnesota',
            'https://blockclubchicago.org/2026/05/21/trial-date-for-broadview-protesters-vacated-just-days-ahead-of-expected-start/',
            'https://www.foxnews.com/us/spokane-3-protesters-convicted-federal-conspiracy-charges-blocking-ice-transfer-washington',
            'https://mynewsla.com/crime/2026/04/07/2-of-3-defendants-acquitted-in-uci-protest-trial/',
            'https://paw.princeton.edu/article/clio-hall-protest-trial-municipal-court-delayed-until-june',
            // round 5 — Stop Cop City RICO dismissal
            'https://www.atlantanewsfirst.com/2025/12/31/racketeering-charges-brought-against-public-safety-training-center-protesters-dismissed/',
            // round 7 — trials / acquittals / charges dropped
            'https://www.cnn.com/2025/11/13/politics/lamonica-mciver-charges-new-jersey-immigration-detention-center',
            'https://calmatters.org/justice/2026/04/ice-protests-clovis-charges/',
            'https://www.washingtonpost.com/national-security/2025/05/21/mciver-baraka-newark-ice-court-charges/',
            'https://www.11alive.com/article/news/crime/trials/public-safety-training-center-rico-case-speedy-trial-defendant-ayla-king-mistrial/85-6f96368d-c446-4a0a-b724-e6c2966817fd',
            'https://www.kptv.com/2026/03/28/portland-ice-protester-acquitted-after-altercation-with-conservative-influencer-nick-sortor/',
            // round 8 — more acquittals / charges dropped
            'https://news.wttw.com/2026/01/22/chicago-man-acquitted-murder-hire-charge-plot-allegedly-targeting-border-patrol-chief',
            'https://www.fox10tv.com/2026/04/15/fairhope-woman-found-not-guilty-penis-costume-arrest-case/',
            'https://wreg.com/news/charges-dropped-against-three-arrested-during-no-kings-protest/',
            // round 11 — 2026 outcomes (verdicts, acquittals, charges, lawsuits)
            'https://en.wikipedia.org/wiki/Hannah_Dugan',
            'https://theintercept.com/2026/03/13/ice-protesters-terrorism-prairieland-antifa/',
            'https://coloradonewsline.com/briefs/federal-officer-charged-durango-ice-protest/',
            'https://www.pbs.org/newshour/politics/newark-mayor-sues-federal-prosecutor-saying-arrest-at-immigration-detention-site-was-political',
            'https://www.cbsnews.com/losangeles/news/la-county-ice-protest-not-guilty-acquittal/',
            'https://www.kqed.org/news/12073534/stanford-pro-palestinian-protesters-case-ends-in-mistrial',
            'https://www.azfamily.com/2026/05/20/19-year-old-arrested-allegedly-setting-fire-surprise-ice-building/',
            // Oak Park trustee indicted over the Broadview ICE protest
            'https://capitolnewsillinois.com/news/democratic-candidates-officeholders-indicted-for-impeding-agent-outside-ice-facility/',
            // ex-Worcester councilor convicted over the ICE-arrest clash
            'https://www.cbsnews.com/boston/news/worcester-city-councilor-etel-haxhiaj-guilty-assault-police-ice/',
            // round 12 — protest prosecutions / declinations
            'https://www.atlantanewsfirst.com/2025/06/25/dekalb-county-drops-charges-against-journalist-arrested-during-anti-ice-protest/',
            'https://www.fox9.com/news/man-rammed-ice-vehicle-arrest-st-paul-protest-indictment-dec-2025',
            'https://www.wcax.com/2026/04/23/state-officials-escalate-feud-with-prosecutor-over-dropped-protest-charges/',
            // round 15
            'https://www.pressherald.com/2026/04/03/no-kings-protester-acquitted-after-portland-police-accused-him-of-assault-by-airhorn/',
            'https://www.wbtv.com/2025/11/26/charlotte-woman-disputes-federal-charges-after-protesting-border-patrol-activity-outside-dhs-office/',
            'https://www.fox9.com/news/tpusa-reporter-savanah-hernandez-attack-2-indicted-federal-charges',
            'https://www.yahoo.com/news/us/articles/man-charged-assaulting-federal-officers-095644023.html',
            'https://www.ms.now/deadline-white-house/deadline-legal-blog/sidney-reid-not-guilty-verdict-ice-trump-doj-rcna238137',
            'https://www.cbsnews.com/losangeles/video/man-accused-of-punching-ice-agent-during-downtown-la-immigration-sweep-acquitted-by-jury/',
            'https://www.nbclosangeles.com/news/local/jury-acquits-csu-channel-islands-professor-federal-ice-assault-case/3874052/',
            'https://www.cbsnews.com/news/david-huerta-california-labor-felony-charge-immigration-protest-reduced/',
            'https://www.opb.org/article/2026/03/03/protester-sues-trump-administration-excessive-force-ice-building/',
        ],
        'other' => [
            // round 13 — Charlie Kirk vigil counter-protest clash
            'https://www.foxnews.com/us/antifa-agitators-disrupt-boston-charlie-kirk-vigil-2-arrested',
            // round 12 — violence / detention reporting at protests
            'https://www.keranews.org/news/2025-07-11/prairieland-detention-center-alvarado-u-s-immigration-and-customs-enforcement-shooting-alvarado-police-officer-questions',
            'https://coppercourier.com/2026/01/27/citizen-detained-ice-phoenix/',
            // Philadelphia — police tracked anti-data-center speech as extremism
            'https://theintercept.com/2026/06/01/ai-data-center-protest-police-surveillance/',
            // Williston, VT — ICE social-media surveillance hub expansion
            'https://vtdigger.org/2025/10/06/ice-plans-to-boost-its-surveillance-on-social-media-using-contractors-in-vermont/',
            // Indianapolis — councilor's home shot with a "No Data Centers" note
            'https://www.cbsnews.com/news/indianapolis-councilor-ron-gibson-home-shooting-data-centers-note/',
            // White House "pre-crime" counterterrorism strategy
            'https://www.kenklippenstein.com/p/insane-pre-crime-strategy-unveiled',
            // round 14
            'https://www.reviewjournal.com/news/environment/this-nevada-city-is-the-first-to-pause-new-data-centers-3824360/',
        ],
    ];

    public function handle(): int {
        // flatten the override map into url => category (everything else: protest)
        $catByUrl = [];
        foreach ($this->categories as $category => $urls) {
            foreach ($urls as $u) {
                $catByUrl[$u] = $category;
            }
        }

        $created = 0;
        $updated = 0;
        $markers = 0;
        $byCategory = [];

        foreach ($this->links as [$title, $url, $source, $date, $lat, $lng, $label]) {
            $category = $catByUrl[$url] ?? 'protest';
            $link = DashboardLink::updateOrCreate(
                ['url' => $url],
                [
                    'title' => $title,
                    'source' => $source,
                    'published_at' => Carbon::parse($date . ' 09:00'),
                    'lat' => $lat,
                    'lng' => $lng,
                    'location_label' => $label,
                    'category' => $category,
                ],
            );

            $link->wasRecentlyCreated ? $created++ : $updated++;
            if ($lat !== null) {
                $markers++;
                $byCategory[$category] = ($byCategory[$category] ?? 0) + 1;
            }
        }

        ksort($byCategory);
        $breakdown = collect($byCategory)->map(fn ($n, $c) => "{$c} {$n}")->implode(', ');
        $this->info("Done. {$created} created, {$updated} updated — " . count($this->links) . " newswire items, {$markers} map markers ({$breakdown}).");

        return self::SUCCESS;
    }
}
