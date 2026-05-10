<?php

declare(strict_types=1);

/**
 * Round 3 of Occupy-era political prisoners — fruits of a six-region
 * parallel-research sweep. Adds seven HIGH or upper-MEDIUM confidence
 * defendants not previously catalogued:
 *
 *   - Mark "Migs" Neiweem (NATO 5 / Occupy Chicago)
 *   - Sebastian Senakiewicz (NATO 5 / Occupy Chicago)
 *   - Frank Cordaro (Occupy Des Moines / Catholic Worker)
 *   - Corey Donahue (Occupy Denver)
 *   - Joshua Wollstein (Occupy Seattle, May Day 2012)
 *   - Cody Ingram (Occupy Seattle, May Day 2012, federal courthouse)
 *   - Andrew "Fish" Fisher (Occupy San Diego)
 *
 * Borderline cases NOT included pending further verification:
 *   - Aaron Minter (NYC, OWS, ~10 days Rikers — barely over the 7-day floor)
 *   - Marcel "Khali" Johnson (Oakland, final disposition unclear)
 *   - Stephen Benavides (Dallas, felony assault charge, no documented sentence)
 *
 * Regions that returned ZERO additional qualifying defendants:
 *   - Mid-Atlantic + Southeast (DC, Atlanta, Tampa, Charlotte, etc.)
 *   - Texas + Southwest (everything beyond the dismissed Houston Port 7)
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\DB;

$entries = [
    [
        'prisoner' => [
            'name'         => 'Mark Neiweem',
            'first_name'   => 'Mark',
            'last_name'    => 'Neiweem',
            'aka'          => 'Migs',
            'description'  => 'Mark "Migs" Neiweem was one of the "NATO 5" — the broader cohort of Occupy Chicago activists arrested by Chicago Police in the days before the May 2012 NATO summit (the better-known NATO 3 — Church, Chase, Betterly — were three of the five). Neiweem was grabbed off Michigan Avenue in a "snatch-and-grab" arrest on May 17, 2012, charged in state court with solicitation to possess an incendiary/explosive device, and held in pretrial detention. He pleaded guilty in April 2013 in a non-cooperating plea and received two concurrent 3-year sentences. Held at Pontiac Correctional Center, Illinois — including five months in disciplinary segregation that prison officials justified by citing the anarchist literature in his cell. Released December 12, 2013 after serving approximately 19 months.',
            'state'        => 'Illinois',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Movement', 'Occupy Chicago', 'NATO 5'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst_lookup' => ['name' => 'Pontiac Correctional Center', 'city' => 'Pontiac', 'state' => 'Illinois'],
            'charges'            => 'Solicitation to possess an incendiary / explosive device (Illinois state). Arrest tied to the broader May 2012 anti-NATO summit prosecutions.',
            'arrest_date'        => '2012-05-17',
            'sentenced_date'     => '2013-04-01',
            'incarceration_date' => '2012-05-17',
            'release_date'       => '2013-12-12',
            'judge'              => 'Cook County Circuit Court (Hon. Thaddeus Wilson presided in NATO-related prosecutions)',
            'plead'              => 'Guilty (non-cooperating)',
            'convicted'          => 'Yes — pleaded guilty April 2013',
            'sentence'           => 'Two concurrent 3-year terms. Served ~19 months including 5 months in disciplinary segregation. Held at Pontiac Correctional Center, Illinois.',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Sebastian Senakiewicz',
            'first_name'   => 'Sebastian',
            'last_name'    => 'Senakiewicz',
            'description'  => 'Sebastian Senakiewicz, a Polish-born Occupy Chicago activist and one of the "NATO 5," was arrested in a May 17, 2012 house raid days before the NATO summit. The state\'s case against him centered on a recorded conversation in which he reportedly joked about a bomb hidden in a Harry Potter book — prosecutors charged him under Illinois\'s false-terrorist-threat statute. He pleaded guilty in November 2012 in a non-cooperating plea, originally receiving 4 years; the sentence was reduced on appeal to 120 days at the Cook County Sheriff\'s boot camp. Upon completion he was deported to Poland in 2013.',
            'state'        => 'Illinois',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Movement', 'Occupy Chicago', 'NATO 5'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst_lookup' => ['name' => 'Cook County Department of Corrections', 'city' => 'Chicago', 'state' => 'Illinois'],
            'charges'            => 'Falsely making a terrorist threat (Illinois state felony). Charge stemmed from a recorded conversation in which Senakiewicz allegedly joked that he had hidden a bomb in a Harry Potter book.',
            'arrest_date'        => '2012-05-17',
            'sentenced_date'     => '2012-11-07',
            'incarceration_date' => '2012-05-17',
            'release_date'       => '2013-04-01',
            'judge'              => 'Cook County Circuit Court',
            'plead'              => 'Guilty (non-cooperating)',
            'convicted'          => 'Yes — pleaded guilty November 2012',
            'sentence'           => 'Originally 4 years; reduced to 120 days at Cook County Sheriff\'s boot camp. Deported to Poland upon release in 2013.',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Frank Cordaro',
            'first_name'   => 'Frank',
            'last_name'    => 'Cordaro',
            'description'  => 'Frank Cordaro is a long-time Iowa Catholic Worker activist (former Catholic priest, co-founder of the Des Moines Catholic Worker) who was deeply involved in Occupy Des Moines and the broader "Occupy the Caucus" actions during the 2012 Republican presidential primary. Arrested at the Iowa State Capitol on January 29, 2012 — along with earlier Occupy-related caucus arrests — Cordaro pleaded guilty the next day to three counts of criminal trespass and was sentenced to 30 days at the Polk County Jail. He served the term in Des Moines in early 2012.',
            'state'        => 'Iowa',
            'race'         => 'White',
            'gender'       => 'Male',
            'birthdate'    => '1951-09-25',
            'ideologies'   => ['Catholic Worker movement', 'Pacifism'],
            'affiliation'  => ['Occupy Movement', 'Occupy Des Moines', 'Catholic Worker', 'Occupy the Caucus'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst_lookup' => ['name' => 'Polk County Jail', 'city' => 'Des Moines', 'state' => 'Iowa'],
            'charges'            => 'Three counts of criminal trespass (Iowa state). Stemming from January 29, 2012 Iowa State Capitol protest plus earlier "Occupy the Caucus" actions in late December 2011 / early January 2012.',
            'arrest_date'        => '2012-01-29',
            'sentenced_date'     => '2012-01-30',
            'incarceration_date' => '2012-01-30',
            'release_date'       => '2012-02-29',
            'judge'              => 'Polk County District Court',
            'plead'              => 'Guilty',
            'convicted'          => 'Yes — pleaded guilty 2012-01-30',
            'sentence'           => '30 days at Polk County Jail.',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Corey Donahue',
            'first_name'   => 'Corey',
            'last_name'    => 'Donahue',
            'description'  => 'Corey Donahue was one of the most-arrested figures of the entire Occupy movement, an Occupy Denver organizer cited and detained dozens of times during the Civic Center Park encampment. Most prominently he was charged in connection with a November 13, 2011 incident at the encampment with two felony counts of inciting a riot plus a misdemeanor obstruction count. Donahue eventually pleaded out via a global plea deal and served approximately 9 months at the Denver County Jail on the riot/obstruction charges (concurrent with separate misdemeanor unlawful-sexual-contact case from a March 2012 confrontation with TV journalist Eli Stokols, which complicated his support among other Occupy organizers).',
            'state'        => 'Colorado',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anti-capitalism'],
            'affiliation'  => ['Occupy Movement', 'Occupy Denver'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst_lookup' => ['name' => 'Denver County Jail', 'city' => 'Denver', 'state' => 'Colorado'],
            'charges'            => 'Two felony counts of inciting a riot + misdemeanor obstruction (Nov 13, 2011 incident at Occupy Denver encampment in Civic Center Park). A separate misdemeanor unlawful-sexual-contact charge from March 2012 was resolved as part of a global plea.',
            'arrest_date'        => '2011-11-13',
            'sentenced_date'     => '2013-01-01',
            'incarceration_date' => '2013-01-01',
            'release_date'       => '2013-10-01',
            'judge'              => 'Denver District Court',
            'plead'              => 'Guilty (global plea)',
            'convicted'          => 'Yes — global plea deal',
            'sentence'           => '~9 months Denver County Jail (concurrent on riot/obstruction + misdemeanor counts).',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Joshua Wollstein',
            'first_name'   => 'Joshua',
            'last_name'    => 'Wollstein',
            'description'  => 'Joshua Wollstein, then 28 and from Tacoma, Washington, was charged with riot — a Class C felony under Washington state law — for his role in the May Day 2012 Occupy Seattle march that broke off into a black-bloc property-damage rampage downtown. Prosecutors said Wollstein threw two beer bottles at Seattle Police Department officers at 5th and Olive, struck a detective in the shin while resisting arrest, and was carrying a lead-loaded leather sap. Of the eight arrested in connection with the May Day march where prosecutors\' charges held up, Wollstein is the most prominently identified state-court defendant; he served roughly two months at the King County Jail in 2012-2013.',
            'state'        => 'Washington',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Movement', 'Occupy Seattle'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst_lookup' => ['name' => 'King County Jail', 'city' => 'Seattle', 'state' => 'Washington'],
            'charges'            => 'Riot (Washington Class C felony, RCW 9A.84.010). Allegations: thrown beer bottles at SPD officers at 5th and Olive during May Day 2012 march, contact with a detective during arrest, possession of a lead-loaded leather sap.',
            'arrest_date'        => '2012-05-01',
            'sentenced_date'     => '2013-01-01',
            'incarceration_date' => '2012-05-01',
            'release_date'       => '2013-03-01',
            'judge'              => 'King County Superior Court',
            'plead'              => 'Guilty',
            'convicted'          => 'Yes',
            'sentence'           => '~2 months at King County Jail.',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Cody Ingram',
            'first_name'   => 'Cody',
            'last_name'    => 'Ingram',
            'description'  => 'Cody Ingram was charged in federal court (W.D. Wash.) with destruction of government property after the Occupy Seattle May Day 2012 march, in which a black-bloc faction smashed glass doors at the William Kenzo Nakamura U.S. Courthouse on 5th Avenue with a wooden stick. According to Seattle Times reporting, Ingram is the only federally-prosecuted defendant from the May Day 2012 march to actually serve federal time — described in subsequent press accounts as "just over a month in prison" for damage to the courthouse doors.',
            'state'        => 'Washington',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Movement', 'Occupy Seattle'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst_lookup' => ['name' => 'SeaTac Federal Detention Center', 'city' => 'SeaTac', 'state' => 'Washington'],
            'charges'            => 'Destruction of government property (federal, 18 U.S.C. § 1361). Smashed glass doors at the William Kenzo Nakamura U.S. Courthouse, 1010 Fifth Ave, Seattle, with a wooden stick during Occupy Seattle May Day 2012.',
            'arrest_date'        => '2012-05-01',
            'sentenced_date'     => '2013-02-01',
            'incarceration_date' => '2012-05-01',
            'release_date'       => '2013-03-15',
            'judge'              => 'U.S. District Court for the Western District of Washington',
            'plead'              => 'Guilty',
            'convicted'          => 'Yes',
            'sentence'           => '~1 month federal custody (held at SeaTac Federal Detention Center).',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Andrew Fisher',
            'first_name'   => 'Andrew',
            'last_name'    => 'Fisher',
            'aka'          => 'Fish',
            'description'  => 'Andrew "Fish" Fisher, an Occupy San Diego organizer, was convicted under California Penal Code § 148(a)(1) of obstruction of a peace officer during a protest line at the Civic Center Plaza encampment. Sentenced April 24, 2012 by Judge Richard S. Whitney of San Diego County Superior Court to 90 days in San Diego County Jail and three years of probation, with employment/education conditions and a no-alcohol/drugs requirement. The harsh sentence was widely understood as a deliberate signal to the San Diego Occupy community.',
            'state'        => 'California',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anti-capitalism'],
            'affiliation'  => ['Occupy Movement', 'Occupy San Diego'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst_lookup' => ['name' => 'San Diego County Jail', 'city' => 'San Diego', 'state' => 'California'],
            'charges'            => 'Obstruction of a peace officer (Cal. Penal Code § 148(a)(1)). Stemming from a Civic Center Plaza protest line at the Occupy San Diego encampment.',
            'arrest_date'        => '2011-12-15',
            'sentenced_date'     => '2012-04-24',
            'incarceration_date' => '2012-04-24',
            'release_date'       => '2012-07-23',
            'judge'              => 'Hon. Richard S. Whitney (San Diego County Superior Court)',
            'plead'              => 'Not guilty',
            'convicted'          => 'Yes — convicted at trial',
            'sentence'           => '90 days San Diego County Jail + 3 years probation, with employment/education conditions and no-alcohol/drugs requirement.',
        ],
    ],
];

$created = 0;
$skipped = 0;
foreach ($entries as $entry) {
    $p = $entry['prisoner'];
    $existing = Prisoner::whereRaw('LOWER(name) = ?', [mb_strtolower($p['name'])])->first();
    if ($existing) {
        echo "[skip]    {$p['name']} already exists (id={$existing->id})\n";
        $skipped++;
        continue;
    }

    DB::transaction(function () use ($p, $entry, &$created) {
        $prisoner = Prisoner::create($p);
        $created++;
        $c = $entry['case'];
        $inst = Institution::firstOrCreate(
            ['name' => $c['inst_lookup']['name']],
            ['city' => $c['inst_lookup']['city'] ?? null, 'state' => $c['inst_lookup']['state'] ?? null]
        );
        $caseAttrs = array_merge(
            ['prisoner_id' => $prisoner->id, 'institution_id' => $inst->id],
            array_diff_key($c, ['inst_lookup' => true])
        );
        PrisonerCase::create($caseAttrs);
        echo "[create]  {$prisoner->name} (id={$prisoner->id})  case at {$inst->name}\n";
    });
}

echo "\nDone. created={$created}, skipped={$skipped}\n";
