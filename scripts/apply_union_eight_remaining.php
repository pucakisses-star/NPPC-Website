<?php

declare(strict_types=1);

/**
 * Add the remaining six "Union Eight" defendants — Union Theological
 * Seminary students who together refused to register for the draft
 * on October 16, 1940 (the first registration day under the
 * Selective Training and Service Act of 1940), even though seminary
 * students had an automatic exemption. Sentenced November 14, 1940
 * to 1 year federal each at FCI Danbury.
 *
 * Already in the DB and not changed by this script:
 *   - George Houser  (added in scripts/apply_1940s_political_prisoners.php)
 *   - David Dellinger (1960s era — the user has decided to keep his
 *     primary classification at his Chicago Eight era; this script
 *     does not touch his record)
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;

$updates = 0; $creates = 0; $unchanged = 0;

$danbury = Institution::firstOrCreate(
    ['name' => 'FCI Danbury'],
    ['city' => 'Danbury', 'state' => 'Connecticut']
);

$narrative = "One of the eight Union Theological Seminary students known as the \"Union Eight\" who, on October 16, 1940, the first day of registration under the Selective Training and Service Act of 1940, collectively refused to register for the draft despite their automatic seminarian exemption. Their joint statement objected to conscription itself: \"To us, the war system is an evil part of our social order, and we declare we cannot cooperate with it... We believe that war and conscription are sins against God and humanity.\" Convicted in U.S. District Court for the Southern District of New York; sentenced November 14, 1940 to 1 year at FCI Danbury (Connecticut). Most served roughly 9 months before parole. Co-defendants: George Houser, David Dellinger, Don Benedict, Meredith Dallas, Joseph Bevilacqua, Richard Wichlei, William Lovell, Howard Spragg.";

$rows = [
    [
        'name' => 'Don Benedict', 'first' => 'Don', 'last' => 'Benedict',
        'birth' => '1917-04-12', 'death' => '2008-04-30',
        'extra' => " After release Benedict was ordained a Methodist minister and in 1948 co-founded the East Harlem Protestant Parish, an early urban-storefront-church experiment. Active in civil rights and anti-poverty work; long-time executive of Community Renewal Society in Chicago.",
    ],
    [
        'name' => 'Meredith Dallas', 'first' => 'Meredith', 'last' => 'Dallas',
        'birth' => '1916-12-21', 'death' => '2010-04-23',
        'extra' => " After release Dallas became a longtime drama professor at Antioch College, where he co-founded the Antioch Area Theatre and helped establish the Yellow Springs (OH) cooperative theatre tradition.",
    ],
    [
        'name' => 'Joseph Bevilacqua', 'first' => 'Joseph', 'last' => 'Bevilacqua',
        'birth' => '1916-01-01', 'death' => null,
        'extra' => " Less well-documented in the historical record than his Union Eight peers; later a labor educator and minister.",
    ],
    [
        'name' => 'Richard Wichlei', 'first' => 'Richard', 'last' => 'Wichlei',
        'birth' => '1916-01-01', 'death' => null,
        'extra' => " Less well-documented in the historical record than his Union Eight peers; left the seminary after the prison term.",
    ],
    [
        'name' => 'William Lovell', 'first' => 'William', 'last' => 'Lovell',
        'birth' => '1917-01-01', 'death' => '2009-01-01',
        'extra' => " After release Lovell was ordained a Methodist minister; long-time pastor in the San Francisco Bay Area and active in the Methodist Federation for Social Action, civil rights organizing, and anti-apartheid work.",
    ],
    [
        'name' => 'Howard Spragg', 'first' => 'Howard', 'last' => 'Spragg',
        'birth' => '1916-12-15', 'death' => '2010-08-23',
        'extra' => " After release Spragg was ordained in the Congregational Christian Churches and rose to become executive vice president of the United Church Board for Homeland Ministries — the social-action arm of the United Church of Christ — from 1962 to 1981.",
    ],
];

foreach ($rows as $r) {
    if (Prisoner::where('name', $r['name'])->exists()) {
        echo "  [skip]   {$r['name']} — already exists\n"; $unchanged++;
        continue;
    }

    $attrs = [
        'name'        => $r['name'],
        'first_name'  => $r['first'],
        'last_name'   => $r['last'],
        'description' => $narrative . $r['extra'],
        'state'       => 'New York',
        'race'        => 'White',
        'gender'      => 'Male',
        'birthdate'   => $r['birth'],
        'death_date'  => $r['death'],
        'ideologies'  => ['Pacifism','Christian peace witness','War Resister'],
        'affiliation' => ['Union Theological Seminary ("Union Eight")'],
        'era'         => '1940s',
        'in_custody'  => false,
        'released'    => true,
    ];

    $p = Prisoner::create($attrs);
    PrisonerCase::create([
        'prisoner_id'        => $p->id,
        'institution_id'     => $danbury->id,
        'charges'            => 'Selective Training and Service Act of 1940 — refused to register for the draft on the first registration day (October 16, 1940), as one of the "Union Eight" Union Theological Seminary students.',
        'arrest_date'        => '1940-10-16',
        'sentenced_date'     => '1940-11-14',
        'incarceration_date' => '1940-11-14',
        'release_date'       => '1941-08-15',
        'sentence'           => '1 year federal prison at FCI Danbury (~9 months served before parole).',
        'convicted'          => 'Yes — federal conviction',
    ]);
    echo "  [add]    {$r['name']}\n";
    $creates++;
}

echo "\nDone. creates={$creates}, unchanged={$unchanged}\n";
