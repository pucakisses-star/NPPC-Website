<?php

declare(strict_types=1);

/**
 * Round 2 of Occupy-era political prisoners (after Cleveland 5,
 * NATO 3, McMillan, Hammond, Brown, PNW Grand Jury Resisters,
 * Pancho Ramos Stierle):
 *
 *   - Mark Adams (Occupy Wall Street, NYC) - widely identified as
 *     OWS's first sentenced political prisoner. 45-day Rikers
 *     sentence for D17 (Dec 17 2011) "Re-Occupy" action at
 *     Trinity Church / Duarte Square; hunger-struck through his
 *     entire term.
 *
 *   - Cesar Aguirre (Occupy Oakland) - sentenced to 6 months
 *     county jail + 5 years probation for felony vandalism at
 *     OPD Internal Affairs / Recruiting Office during the
 *     Oakland general strike, November 2-3, 2011.
 *
 * Sources: Truthout, Village Voice, KQED, CBS San Francisco,
 * Dissent Magazine, contemporary court coverage.
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
            'name'         => 'Mark Adams',
            'first_name'   => 'Mark',
            'last_name'    => 'Adams',
            'description'  => 'Mark Adams, a long-term Occupy Wall Street organizer, was widely identified by the movement as OWS\'s first sentenced political prisoner. On December 17, 2011 (the "D17" or "Re-Occupy" action) Adams and seven other defendants entered Duarte Square — a privately-owned vacant lot held by Trinity Church — to protest the church\'s refusal to allow Occupy a new encampment after the Zuccotti Park eviction. Convicted in 2012 of trespassing, criminal mischief, and criminal possession of burglary tools (a screwdriver), Adams received a 45-day sentence at Rikers Island, fifteen days longer than even the prosecution had requested; in his ruling the judge cited the "foundational importance of private property." Seven of the eight D17 co-defendants received only community service and fines. Adams hunger-struck through the entirety of his sentence, drinking only juice.',
            'state'        => 'New York',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anti-capitalism', 'Direct action'],
            'affiliation'  => ['Occupy Movement', 'Occupy Wall Street'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst_lookup' => ['name' => 'Manhattan House of Detention (the Tombs)', 'city' => 'New York', 'state' => 'New York'],
            'charges'            => 'Trespassing; criminal mischief; criminal possession of burglary tools (a screwdriver). Stemming from the December 17, 2011 "D17 / Re-Occupy" action at Duarte Square, the privately-held vacant lot owned by Trinity Church which OWS had asked the church to allow as a successor encampment to Zuccotti Park.',
            'arrest_date'        => '2011-12-17',
            'sentenced_date'     => '2012-06-19',
            'incarceration_date' => '2012-06-19',
            'release_date'       => '2012-08-03',
            'judge'              => 'Hon. Matthew A. Sciarrino Jr. (NY Crim. Ct., NY County)',
            'plead'              => 'Not guilty',
            'convicted'          => 'Yes — convicted at bench trial June 19, 2012',
            'sentence'           => '45 days at Rikers Island. Adams hunger-struck the entire sentence (juice only). Seven D17 co-defendants got community service + fines; Adams drew prison time because of the additional burglary-tools count.',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Cesar Aguirre',
            'first_name'   => 'Cesar',
            'last_name'    => 'Aguirre',
            'description'  => 'Cesar Aguirre, then 24 and from Elk Grove, California, was prosecuted for actions during the November 2-3, 2011 Occupy Oakland general strike. Following the strike\'s late-night march on the OPD Internal Affairs and Recruiting Office near Oakland City Hall, prosecutors said Aguirre struck the building\'s windows multiple times with a red metal folding chair, breaking six windows and a door. Convicted in mid-2012 of felony vandalism; in September 2012 sentenced to six months in the Alameda County Jail, five years probation, $6,654 in restitution, and a five-year stay-away order from Frank Ogawa Plaza. Aguirre had already served roughly four months in pretrial custody. He maintained he was innocent throughout.',
            'state'        => 'California',
            'race'         => 'Latino',
            'gender'       => 'Male',
            'ideologies'   => ['Anti-capitalism'],
            'affiliation'  => ['Occupy Movement', 'Occupy Oakland'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst_lookup' => ['name' => 'Alameda County Jail', 'city' => 'Oakland', 'state' => 'California'],
            'charges'            => 'Felony vandalism (Cal. Penal Code § 594) — broke six windows and one door at the OPD Internal Affairs and Recruiting Office during the late-night march phase of the November 2-3, 2011 Occupy Oakland general strike. Restitution $6,654.',
            'arrest_date'        => '2011-11-03',
            'sentenced_date'     => '2012-09-21',
            'incarceration_date' => '2011-11-03',
            'release_date'       => '2012-12-01',
            'judge'              => 'Hon. Jeffrey Horner (Alameda County Superior Court)',
            'plead'              => 'Not guilty',
            'convicted'          => 'Yes — jury convicted of felony vandalism August 2012',
            'sentence'           => '6 months Alameda County Jail + 5 years probation + $6,654 restitution + 5-year stay-away from Frank Ogawa Plaza. Approximately 4 months pretrial custody credited.',
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
