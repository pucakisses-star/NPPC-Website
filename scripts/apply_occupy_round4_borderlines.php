<?php

declare(strict_types=1);

/**
 * Round 4 — two borderline Occupy cases that subsequent research
 * promoted from "pending verification" to "add":
 *
 *   - Aaron Minter (NYC, OWS) — 10 days at Rikers, sentenced
 *     September 21, 2012 in the "Priority Seating For the 1%"
 *     subway-sticker case (alias Aaron Black).
 *   - Marcel "Khali" Johnson (Occupy Oakland) — held in solitary
 *     confinement at California Men\'s Colony for approximately
 *     one year and subsequently transferred to California State
 *     Prison Sacramento (still incarcerated as of late 2014).
 *     Exact final sentence figure not in publicly indexed sources;
 *     actual incarceration was multi-year.
 *
 * Stephen Benavides (Dallas) was investigated and EXCLUDED — no
 * documented sentence beyond 4 days pretrial detention.
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
            'name'         => 'Aaron Minter',
            'first_name'   => 'Aaron',
            'last_name'    => 'Minter',
            'aka'          => 'Aaron Black',
            'description'  => 'Aaron Minter, an Occupy Wall Street organizer who frequently went by the alias Aaron Black, was sentenced to 10 days at Rikers Island on September 21, 2012 in connection with an action protesting MTA fare hikes. On April 5, 2012, Minter and a co-defendant, Jeffery Brewer, placed adhesive decals reading "Priority Seating For The 1%" on subway-car seats — a piece of political street-theater that was filmed by a TV news crew. Police identified them from the news segment and arrested them on vandalism charges. Minter pleaded out and served 10 days at Rikers in late September 2012.',
            'state'        => 'New York',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anti-capitalism'],
            'affiliation'  => ['Occupy Movement', 'Occupy Wall Street'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst_lookup' => ['name' => 'Rose M. Singer Center, Rikers Island', 'city' => 'East Elmhurst', 'state' => 'New York'],
            'charges'            => 'Misdemeanor vandalism (NYC) — stickering subway-car seats with decals reading "Priority Seating For The 1%" on April 5, 2012, an action protesting MTA fare hikes that was filmed by a local TV news crew.',
            'arrest_date'        => '2012-04-05',
            'sentenced_date'     => '2012-09-21',
            'incarceration_date' => '2012-09-21',
            'release_date'       => '2012-10-01',
            'judge'              => 'New York County Criminal Court',
            'plead'              => 'Guilty',
            'convicted'          => 'Yes — pleaded guilty 2012-09-21',
            'sentence'           => '10 days at Rikers Island (New York City Department of Correction).',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Marcel Johnson',
            'first_name'   => 'Marcel',
            'last_name'    => 'Johnson',
            'aka'          => 'Khali, Kali',
            'description'  => 'Marcel "Khali" Johnson, a Black Occupy Oakland organizer involved in food and blanket distribution at the Frank Ogawa Plaza encampment, was arrested December 16, 2011 outside Oakland City Hall in a dispute over a blanket and anti-encroachment ordinance violations. Because of an unrelated Sacramento probation hold, he was sent to Santa Rita Jail rather than released. There he was placed in solitary confinement and denied his prescribed psychiatric medication; an altercation with a corrections officer in solitary led to a felony assault charge that, layered with two prior strikes, exposed him to a potential life sentence under California\'s three-strikes law. The case was prosecuted aggressively by the Alameda County DA over the objections of a robust Occupy legal-defense campaign led by attorney Dan Siegel. Johnson served approximately one year in solitary at California Men\'s Colony (CMC) before being transferred to California State Prison Sacramento (CSP-Sac), where he remained at least through late 2014.',
            'state'        => 'California',
            'race'         => 'Black',
            'gender'       => 'Male',
            'ideologies'   => ['Anti-capitalism'],
            'affiliation'  => ['Occupy Movement', 'Occupy Oakland'],
            'era'          => 'Post-9/11',
            'in_custody'   => false, // status unclear post-2014; conservative
            'released'     => true,
        ],
        'case' => [
            'inst_lookup' => ['name' => 'California Medical Facility, Vacaville', 'city' => 'Vacaville', 'state' => 'California'],
            'charges'            => 'Initial misdemeanor obstruction (Dec 16, 2011 Oakland City Hall blanket/anti-encroachment dispute) layered onto a Sacramento probation hold; subsequent felony assault on a corrections officer arising from an altercation in Santa Rita Jail solitary confinement after Johnson was denied prescribed psychiatric medication. Three-strikes enhancement on prior convictions exposed him to potential life sentence.',
            'arrest_date'        => '2011-12-16',
            'incarceration_date' => '2011-12-16',
            'sentenced_date'     => null,
            'release_date'       => null,
            'judge'              => 'Alameda County Superior Court',
            'plead'              => 'Unknown — case prosecuted aggressively by Alameda County DA',
            'convicted'          => 'Yes — convicted of felony assault on corrections officer; three-strikes enhancement averted',
            'sentence'           => 'Final sentence figure not in publicly indexed sources. Actual incarceration multi-year: ~1 year solitary at California Men\'s Colony (CMC), then transferred to California State Prison Sacramento by late 2014. Held also at Santa Rita Jail (Alameda County) during pretrial.',
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
        // strip nulls so they don't override default-null DB columns
        $caseAttrs = array_filter($caseAttrs, fn ($v) => $v !== null);
        PrisonerCase::create($caseAttrs);
        echo "[create]  {$prisoner->name} (id={$prisoner->id})  case at {$inst->name}\n";
    });
}

echo "\nDone. created={$created}, skipped={$skipped}\n";
