<?php

declare(strict_types=1);

/**
 * Add 11 Occupy-era political prisoners:
 *   - Cleveland 5 (FBI bridge-bombing sting against Occupy
 *     Cleveland, 2012): Wright, Baxter, Stevens, Stafford, Hayne
 *   - NATO 3 (Occupy Chicago / 2012 NATO summit informant case):
 *     Church, Chase, Betterly
 *   - Cecily McMillan (Zuccotti Park 6-month anniversary 2012)
 *   - Jeremy Hammond (Anonymous/AntiSec, Stratfor hack 2011)
 *   - Barrett Brown (Anonymous-linked journalist, Stratfor case)
 *
 * Idempotent: skips anyone already in the DB by name match.
 * Each prisoner gets one PrisonerCase. Institutions are looked up
 * by name with firstOrCreate (city/state filled when newly seen).
 *
 * Heads up: Anthony Hayne cooperated against the rest of the
 * Cleveland 5. Some solidarity orgs exclude cooperators from
 * political-prisoner framing; user explicitly asked to include all.
 *
 * Sources: BOP locator records, federal sentencing announcements,
 * It's Going Down / Sparrows Nest archives, contemporary news
 * coverage (NYT, Cleveland Plain Dealer, Chicago Tribune, Reuters).
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\DB;

function instById(array $row): Institution
{
    return Institution::firstOrCreate(
        ['name' => $row['name']],
        [
            'city'  => $row['city']  ?? null,
            'state' => $row['state'] ?? null,
        ]
    );
}

$entries = [
    // ---- Cleveland 5 -------------------------------------------
    [
        'prisoner' => [
            'name'         => 'Brandon Baxter',
            'first_name'   => 'Brandon',
            'last_name'    => 'Baxter',
            'description'  => 'Brandon Baxter was an Occupy Cleveland organizer entrapped in a 2012 FBI sting in which an undercover informant supplied fake C-4 explosives and pressed five Cleveland anarchists to attempt to detonate them on the Route 82 bridge over the Cuyahoga Valley near Brecksville, Ohio. The explosives were inert and the bridge was never in danger; the entire plot was constructed and equipment-supplied by the informant. Baxter, then 20, pleaded guilty in October 2012 and was sentenced on November 30, 2012 to 9 years and 9 months in federal prison.',
            'state'        => 'Ohio',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Wall Street', 'Occupy Cleveland'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst' => ['name' => 'FCI Beckley', 'city' => 'Beaver', 'state' => 'West Virginia'],
            'charges' => "Conspiracy to use a weapon of mass destruction; attempted use of a weapon of mass destruction; conspiracy to maliciously damage / destroy property used in interstate commerce by means of an explosive (18 U.S.C. §§ 2332a, 844(i), 844(n)).",
            'arrest_date' => '2012-04-30',
            'sentenced_date' => '2012-11-30',
            'incarceration_date' => '2012-11-30',
            'release_date' => '2020-04-01',
            'judge' => 'Hon. David D. Dowd Jr. (N.D. Ohio)',
            'plead' => 'Guilty',
            'convicted' => 'Yes — pleaded guilty 2012-10-01 (entrapment defense waived under plea agreement)',
            'sentence' => '117 months federal prison + 5 years supervised release. FBI sting; informant Shaquille Azir supplied the (inert) explosives.',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Douglas Wright',
            'first_name'   => 'Douglas',
            'last_name'    => 'Wright',
            'description'  => 'Douglas Wright, then 26 and Indianapolis-born, was identified by prosecutors as the lead figure of the Cleveland 5 — a small Occupy Cleveland affinity group entrapped by FBI informant Shaquille Azir into a bridge-bombing plot whose explosives were entirely inert and supplied by the informant. Wright pleaded guilty in October 2012 and on November 20, 2012 was sentenced to the longest term of any of the five defendants: 11 years and 6 months in federal prison.',
            'state'        => 'Ohio',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Wall Street', 'Occupy Cleveland'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst' => ['name' => 'FCI Loretto', 'city' => 'Loretto', 'state' => 'Pennsylvania'],
            'charges' => "Conspiracy to use a weapon of mass destruction; attempted use of a weapon of mass destruction; conspiracy to maliciously damage / destroy property used in interstate commerce by means of an explosive.",
            'arrest_date' => '2012-04-30',
            'sentenced_date' => '2012-11-20',
            'incarceration_date' => '2012-11-20',
            'release_date' => '2022-09-01',
            'judge' => 'Hon. David D. Dowd Jr. (N.D. Ohio)',
            'plead' => 'Guilty',
            'convicted' => 'Yes — pleaded guilty 2012-10-01',
            'sentence' => '138 months federal prison + 5 years supervised release.',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Connor Stevens',
            'first_name'   => 'Connor',
            'last_name'    => 'Stevens',
            'description'  => 'Connor Stevens, then 20, was the youngest of the Cleveland 5. Identified by friends and family as a quiet poetry-writer drawn into Occupy Cleveland\'s informant-saturated affinity group, he pleaded guilty in October 2012 and was sentenced on November 20, 2012 to 8 years and 1 month in federal prison.',
            'state'        => 'Ohio',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Wall Street', 'Occupy Cleveland'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst' => ['name' => 'FCI Loretto', 'city' => 'Loretto', 'state' => 'Pennsylvania'],
            'charges' => "Conspiracy to use a weapon of mass destruction; attempted use of a weapon of mass destruction; conspiracy to maliciously damage / destroy property used in interstate commerce by means of an explosive.",
            'arrest_date' => '2012-04-30',
            'sentenced_date' => '2012-11-20',
            'incarceration_date' => '2012-11-20',
            'release_date' => '2019-12-01',
            'judge' => 'Hon. David D. Dowd Jr. (N.D. Ohio)',
            'plead' => 'Guilty',
            'convicted' => 'Yes — pleaded guilty 2012-10-01',
            'sentence' => '97 months federal prison + 5 years supervised release.',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Joshua Stafford',
            'first_name'   => 'Joshua',
            'last_name'    => 'Stafford',
            'aka'          => 'Skelly',
            'description'  => 'Joshua "Skelly" Stafford, then 23, was initially found incompetent to stand trial in 2012, then re-evaluated as competent and prosecuted alongside the other Cleveland 5 defendants. Stafford did not plead guilty; he was convicted at a bench trial and sentenced on February 25, 2013 to 10 years in federal prison. The plot for which he was prosecuted was constructed by FBI informant Shaquille Azir and the explosives were inert.',
            'state'        => 'Ohio',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Wall Street', 'Occupy Cleveland'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst' => ['name' => 'FCI Manchester', 'city' => 'Manchester', 'state' => 'Kentucky'],
            'charges' => "Conspiracy to use a weapon of mass destruction; attempted use of a weapon of mass destruction; conspiracy to maliciously damage / destroy property used in interstate commerce by means of an explosive.",
            'arrest_date' => '2012-04-30',
            'sentenced_date' => '2013-02-25',
            'incarceration_date' => '2013-02-25',
            'release_date' => '2021-08-01',
            'judge' => 'Hon. David D. Dowd Jr. (N.D. Ohio)',
            'plead' => 'Not guilty',
            'convicted' => 'Yes — bench trial conviction',
            'sentence' => '120 months federal prison + 5 years supervised release. Originally found incompetent to stand trial; re-evaluated competent and convicted.',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Anthony Hayne',
            'first_name'   => 'Anthony',
            'last_name'    => 'Hayne',
            'description'  => 'Anthony Hayne, the oldest Cleveland 5 defendant at 35, pleaded guilty in July 2012 and cooperated with the prosecution against his four co-defendants. Sentenced on December 1, 2012 to 6 years in federal prison. Most Occupy / anarchist solidarity organizations excluded Hayne from political-prisoner solidarity work because of his cooperation; this entry is included for completeness and historical accuracy.',
            'state'        => 'Ohio',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Wall Street', 'Occupy Cleveland'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst' => ['name' => 'Federal Bureau of Prisons (federal custody)'],
            'charges' => "Conspiracy to use a weapon of mass destruction; attempted use of a weapon of mass destruction; conspiracy to maliciously damage / destroy property used in interstate commerce by means of an explosive.",
            'arrest_date' => '2012-04-30',
            'sentenced_date' => '2012-12-01',
            'incarceration_date' => '2012-12-01',
            'release_date' => '2017-08-01',
            'judge' => 'Hon. David D. Dowd Jr. (N.D. Ohio)',
            'plead' => 'Guilty (cooperator)',
            'convicted' => 'Yes — pleaded guilty 2012-07-25; cooperated against co-defendants',
            'sentence' => '72 months federal prison + 5 years supervised release.',
        ],
    ],

    // ---- NATO 3 ------------------------------------------------
    [
        'prisoner' => [
            'name'         => 'Brian Jacob Church',
            'first_name'   => 'Brian',
            'middle_name'  => 'Jacob',
            'last_name'    => 'Church',
            'description'  => 'Brian Jacob Church, then 22 and from Florida, traveled to Chicago in May 2012 to participate in protests against the NATO summit. Church and two co-defendants (Brent Betterly and Jared Chase) were entrapped by two undercover Chicago Police Department officers known as "Mo" and "Gloves," who supplied beer-bottle Molotov-cocktail components and pushed the conversation toward violent attack plans. At trial in 2014 the NATO 3 were ACQUITTED of the headline terrorism charges (the first to be brought under Illinois\'s post-9/11 state terrorism statute) but convicted of mob action and possession of an incendiary device. Sentenced April 25, 2014 to 5 years; released later that year with credit for two years\' pretrial detention.',
            'state'        => 'Illinois',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Wall Street', 'Occupy Chicago', 'NATO 3'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst' => ['name' => 'Illinois Department of Corrections', 'state' => 'Illinois'],
            'charges' => "Mob action; possession of an incendiary device with intent to commit arson. ACQUITTED of all terrorism counts (Illinois state terrorism statute) — the first jury verdict on those charges.",
            'arrest_date' => '2012-05-16',
            'sentenced_date' => '2014-04-25',
            'incarceration_date' => '2012-05-16',
            'release_date' => '2015-02-01',
            'judge' => 'Hon. Thaddeus Wilson (Cook County Circuit Court)',
            'plead' => 'Not guilty',
            'convicted' => 'Yes — convicted at trial of mob action and possession of incendiary device; acquitted of terrorism counts',
            'sentence' => '5 years Illinois state prison; released early-2015 with pretrial credit.',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Jared Chase',
            'first_name'   => 'Jared',
            'last_name'    => 'Chase',
            'description'  => 'Jared Chase, then 26 and from Keene, New Hampshire, was one of the NATO 3 entrapped by Chicago Police undercover officers ahead of the May 2012 NATO summit. Like his co-defendants Church and Betterly he was acquitted at trial of all terrorism counts but convicted of mob action and possession of an incendiary device. Chase has Huntington\'s disease and his condition deteriorated significantly during his incarceration; in addition to his original NATO 3 sentence he received additional time for in-prison incidents prosecutors connected to mental-health symptoms.',
            'state'        => 'New Hampshire',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Wall Street', 'Occupy Chicago', 'NATO 3'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst' => ['name' => 'Illinois Department of Corrections', 'state' => 'Illinois'],
            'charges' => "Mob action; possession of an incendiary device with intent to commit arson. ACQUITTED of all terrorism counts.",
            'arrest_date' => '2012-05-16',
            'sentenced_date' => '2014-04-25',
            'incarceration_date' => '2012-05-16',
            'release_date' => '2019-08-01',
            'judge' => 'Hon. Thaddeus Wilson (Cook County Circuit Court)',
            'plead' => 'Not guilty',
            'convicted' => 'Yes — convicted at trial; acquitted of terrorism',
            'sentence' => '8 years (initial 5-year NATO 3 sentence + additional time for in-custody incidents related to Huntington\'s symptoms).',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Brent Betterly',
            'first_name'   => 'Brent',
            'last_name'    => 'Betterly',
            'description'  => 'Brent Betterly, then 24 and from Florida, was the third NATO 3 defendant entrapped by Chicago Police undercover officers ahead of the May 2012 NATO summit. Acquitted at trial of all terrorism counts but convicted of mob action and possession of an incendiary device. Sentenced April 25, 2014 to 6 years; released in 2016.',
            'state'        => 'Illinois',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Wall Street', 'Occupy Chicago', 'NATO 3'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst' => ['name' => 'Illinois Department of Corrections', 'state' => 'Illinois'],
            'charges' => "Mob action; possession of an incendiary device with intent to commit arson. ACQUITTED of all terrorism counts.",
            'arrest_date' => '2012-05-16',
            'sentenced_date' => '2014-04-25',
            'incarceration_date' => '2012-05-16',
            'release_date' => '2016-08-01',
            'judge' => 'Hon. Thaddeus Wilson (Cook County Circuit Court)',
            'plead' => 'Not guilty',
            'convicted' => 'Yes — convicted at trial; acquitted of terrorism',
            'sentence' => '6 years Illinois state prison.',
        ],
    ],

    // ---- Cecily McMillan ---------------------------------------
    [
        'prisoner' => [
            'name'         => 'Cecily McMillan',
            'first_name'   => 'Cecily',
            'last_name'    => 'McMillan',
            'description'  => 'Cecily McMillan, an Occupy Wall Street organizer and graduate student at The New School, was arrested at Zuccotti Park on March 17, 2012, the six-month anniversary of the OWS encampment. She testified that NYPD Officer Grantley Bovell grabbed her right breast from behind and that her elbow strike was a reflexive response. Convicted of felony assault on a police officer in May 2014; nine of the twelve jurors subsequently wrote to the court asking for leniency. Sentenced May 19, 2014 to 90 days; served approximately 58 days at the Rose M. Singer Center on Rikers Island and was released in early July 2014. Her case became the most-covered Occupy Wall Street prosecution.',
            'state'        => 'New York',
            'race'         => 'White',
            'gender'       => 'Female',
            'birthdate'    => '1988-05-09',
            'ideologies'   => ['Democratic socialism'],
            'affiliation'  => ['Occupy Wall Street', 'Democratic Socialists of America'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst' => ['name' => 'Rose M. Singer Center, Rikers Island', 'city' => 'East Elmhurst', 'state' => 'New York'],
            'charges' => "Felony assault on a police officer (NY Penal Law § 120.05). McMillan testified that Officer Grantley Bovell grabbed her breast from behind and her elbow strike was reflexive.",
            'arrest_date' => '2012-03-17',
            'sentenced_date' => '2014-05-19',
            'incarceration_date' => '2014-05-19',
            'release_date' => '2014-07-02',
            'judge' => 'Hon. Ronald A. Zweibel (NY Supreme Court, NY County)',
            'plead' => 'Not guilty',
            'convicted' => 'Yes — convicted at trial 2014-05-05',
            'sentence' => '90 days at Rikers (Rose M. Singer Center) + 5 years probation. Served approximately 58 days; released July 2014. Nine of twelve jurors wrote the judge asking for leniency.',
        ],
    ],

    // ---- Hacktivists -------------------------------------------
    [
        'prisoner' => [
            'name'         => 'Jeremy Hammond',
            'first_name'   => 'Jeremy',
            'last_name'    => 'Hammond',
            'aka'          => 'sup_g, Anarchaos, yohoho, POW',
            'description'  => 'Jeremy Hammond, a Chicago anarchist hacker affiliated with Anonymous and AntiSec, breached the servers of the private intelligence contractor Strategic Forecasting Inc. (Stratfor) in December 2011, exfiltrating roughly 5 million internal emails that WikiLeaks subsequently published as the Global Intelligence Files. Hammond identified the action as motivated by Occupy and AntiSec politics and as direct retaliation for Stratfor\'s monitoring of Occupy and other social movements. Arrested March 5, 2012, he pleaded guilty in May 2013 to one count of violating the Computer Fraud and Abuse Act and on November 15, 2013 was sentenced to 10 years (the statutory maximum) in federal prison plus 3 years supervised release. Released April 2020. Hammond was repeatedly named on Occupy / Anonymous solidarity political-prisoner lists throughout his sentence.',
            'state'        => 'Illinois',
            'race'         => 'White',
            'gender'       => 'Male',
            'birthdate'    => '1985-01-08',
            'ideologies'   => ['Anarchism', 'Hacktivism'],
            'affiliation'  => ['Occupy Wall Street', 'Anonymous', 'AntiSec'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
            'inmate_number'=> '18729-424',
        ],
        'case' => [
            'inst' => ['name' => 'FCI Manchester', 'city' => 'Manchester', 'state' => 'Kentucky'],
            'charges' => "Conspiracy to violate the Computer Fraud and Abuse Act, 18 U.S.C. § 1030 (Stratfor server breach, December 2011). Hammond also acknowledged participation in attacks on government and law-enforcement websites and the publication of internal communications via WikiLeaks (Global Intelligence Files).",
            'arrest_date' => '2012-03-05',
            'sentenced_date' => '2013-11-15',
            'incarceration_date' => '2012-03-05',
            'release_date' => '2020-04-30',
            'judge' => 'Hon. Loretta A. Preska (S.D.N.Y.)',
            'plead' => 'Guilty',
            'convicted' => 'Yes — pleaded guilty 2013-05-28',
            'sentence' => '120 months federal prison (statutory maximum on the single count) + 3 years supervised release. BOP register #18729-424. Held at MCC New York pretrial; designated to FCI Manchester KY.',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Barrett Brown',
            'first_name'   => 'Barrett',
            'last_name'    => 'Brown',
            'description'  => 'Barrett Brown, a journalist and self-described unofficial spokesperson for Anonymous, was arrested September 12, 2012 during a raid at his Dallas home and indicted on a series of charges connected to the Anonymous/AntiSec hack of private intelligence contractor Stratfor. The most-criticized count was the original "linking" charge — accessing stolen data because he had merely posted a hyperlink in chat to a public dump of the Stratfor files; the government dropped that count in 2014 after a sustained press-freedom backlash. Brown pleaded guilty April 2014 to transmitting threats, accessory after the fact, and obstruction. Sentenced January 22, 2015 to 63 months federal prison + $890,250 restitution. Released November 29, 2016.',
            'state'        => 'Texas',
            'race'         => 'White',
            'gender'       => 'Male',
            'birthdate'    => '1981-08-14',
            'ideologies'   => ['Hacktivism', 'Press freedom'],
            'affiliation'  => ['Occupy Wall Street', 'Anonymous'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
            'inmate_number'=> '45047-177',
        ],
        'case' => [
            'inst' => ['name' => 'FCI Seagoville', 'city' => 'Seagoville', 'state' => 'Texas'],
            'charges' => "Internet threats; accessory after the fact (to the Stratfor hack); obstruction of the execution of a search warrant. The government\'s original headline charge — that Brown\'s pasting of a hyperlink to a publicly-available leak constituted unauthorized access — was dropped in March 2014 after press-freedom backlash.",
            'arrest_date' => '2012-09-12',
            'sentenced_date' => '2015-01-22',
            'incarceration_date' => '2012-09-12',
            'release_date' => '2016-11-29',
            'judge' => 'Hon. Sam A. Lindsay (N.D. Tex.)',
            'plead' => 'Guilty',
            'convicted' => 'Yes — pleaded guilty 2014-04-29',
            'sentence' => '63 months federal prison + 2 years supervised release + $890,250 restitution. BOP register #45047-177.',
        ],
    ],
];

// ---- Apply -----------------------------------------------------
$created = 0;
$skipped = 0;
$caseAdds = 0;
foreach ($entries as $entry) {
    $p = $entry['prisoner'];
    $existing = Prisoner::whereRaw('LOWER(name) = ?', [mb_strtolower($p['name'])])->first();
    if ($existing) {
        echo "[skip]    {$p['name']} already exists (id={$existing->id})\n";
        $skipped++;
        continue;
    }

    DB::transaction(function () use ($p, $entry, &$created, &$caseAdds) {
        $prisoner = Prisoner::create($p);
        $created++;
        $c = $entry['case'];
        $inst = instById($c['inst']);
        $caseAttrs = array_merge(
            ['prisoner_id' => $prisoner->id, 'institution_id' => $inst->id],
            array_diff_key($c, ['inst' => true])
        );
        PrisonerCase::create($caseAttrs);
        $caseAdds++;
        echo "[create]  {$prisoner->name} (id={$prisoner->id})  case at {$inst->name}\n";
    });
}

echo "\nDone. created={$created}, skipped={$skipped}, cases={$caseAdds}\n";
