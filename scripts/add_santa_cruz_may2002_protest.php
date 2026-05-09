<?php

declare(strict_types=1);

/**
 * Apply the Nuclear Resister #118 "Santa Cruz" article to the
 * database (https://www.nukeresister.org/static/nr118/nr118santacruz.html).
 *
 * The article reports on a May 22, 2002 anti-Iraq-war demonstration
 * in Santa Cruz, California. Protesters confronted U.S. Representative
 * Sam Farr (D-CA) over his pro-war stance; police sparked a melee
 * and a handful of activists were arrested. Five defendants are
 * named in the article. NR #118 was published in early 2003 — "last
 * May 22" is May 22, 2002.
 *
 * Updates:
 *   - Steve Argue (already in DB) — description says "late 1990s",
 *     which is off by ~5 years. Append the corrected article facts
 *     and a corrected date in the case.
 *
 * New entries:
 *   - Vincent Lombardo (resisting arrest; 18 months probation)
 *   - Nassim Zerriffi (resisting arrest + misdemeanor assault on officer)
 *   - Jim Cosner (resisting arrest; left town before trial; under warrant)
 *
 * Skipped: Kuo-ling Luo — charges (disturbing the peace) were
 * dismissed in October 2002 and the article describes them as an
 * "innocent bystander" not actually prosecuted to conviction. They
 * don't fit the prisoner-database scope.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;

$jail = Institution::firstOrCreate(
    ['name' => 'Santa Cruz County Jail'],
    ['city' => 'Santa Cruz', 'state' => 'California']
);

// ---- 1. Steve Argue: correct date / add article context ----

$argue = Prisoner::where('name', 'Steve Argue')->first();
if ($argue) {
    $articleNote = "Per Nuclear Resister #118 (early 2003): On May 22, 2002 Argue was arrested during an anti-Iraq-war demonstration in Santa Cruz that targeted U.S. Representative Sam Farr (D-CA) over Farr's pro-war stance. Police sparked a melee; Argue was charged with felony battery and resisting arrest. He was sentenced to nine months in the Santa Cruz County Jail and added 30 days to his sentence by refusing transfer to minimum security. He began serving on November 23, 2002 in Unit K. His statement at sentencing: \"I'm not your chump, I'm not your slave.\"";

    if (! str_contains((string) $argue->description, 'May 22, 2002')) {
        $argue->description = trim((string) $argue->description) . ' ' . $articleNote;
        $argue->save();
        echo "  [update] Steve Argue — appended Nuclear Resister #118 facts (May 22, 2002 demo, 9-month + 30 day sentence at SCC Jail Unit K)\n";

        // Also normalize the era to "2000s" if it's currently "1990s".
        if ($argue->era === '1990s') {
            $argue->era = '2000s';
            $argue->save();
            echo "           era 1990s -> 2000s\n";
        }
    } else {
        echo "  [skip]   Steve Argue — already has the May 22, 2002 article facts\n";
    }
} else {
    echo "  [warn]   Steve Argue — not found, skipping update\n";
}

// ---- 2. Three new prisoners ----

$rows = [
    [
        'name'        => 'Vincent Lombardo',
        'first'       => 'Vincent',
        'last'        => 'Lombardo',
        'description' => "Vincent Lombardo was one of the activists arrested when Santa Cruz police sparked a melee during a May 22, 2002 anti-Iraq-war demonstration that confronted U.S. Representative Sam Farr (D-CA) over his pro-war stance. Lombardo was charged with resisting arrest. He was sentenced to 18 months probation. (Source: Nuclear Resister Issue #118.)",
        'charges'     => "California Penal Code § 148(a)(1) — resisting arrest. Arrested at a Santa Cruz, CA anti-Iraq-war demonstration on May 22, 2002.",
        'sentence'    => "18 months probation; no jail time imposed.",
        'in_custody'  => false,
        'released'    => true,
        'arrest'      => '2002-05-22',
        'incarc'      => null,
        'release'     => null,
    ],
    [
        'name'        => 'Nassim Zerriffi',
        'first'       => 'Nassim',
        'last'        => 'Zerriffi',
        'description' => "Nassim Zerriffi was one of the activists arrested when Santa Cruz police sparked a melee during a May 22, 2002 anti-Iraq-war demonstration that confronted U.S. Representative Sam Farr (D-CA) over his pro-war stance. Zerriffi was charged with resisting arrest and misdemeanor assault on a peace officer. Trial was scheduled for February 28, 2003. (Source: Nuclear Resister Issue #118.)",
        'charges'     => "California Penal Code § 148(a)(1) — resisting arrest; § 241(c) — misdemeanor assault on a peace officer. Arrested at a Santa Cruz, CA anti-Iraq-war demonstration on May 22, 2002.",
        'sentence'    => "Disposition pending as of Nuclear Resister #118 (early 2003); trial scheduled February 28, 2003.",
        'in_custody'  => false,
        'released'    => true,
        'arrest'      => '2002-05-22',
        'incarc'      => null,
        'release'     => null,
    ],
    [
        'name'        => 'Jim Cosner',
        'first'       => 'Jim',
        'last'        => 'Cosner',
        'description' => "Jim Cosner was one of the activists arrested when Santa Cruz police sparked a melee during a May 22, 2002 anti-Iraq-war demonstration that confronted U.S. Representative Sam Farr (D-CA) over his pro-war stance. Cosner was charged with resisting arrest. He left town before his trial and was reported under warrant as of Nuclear Resister Issue #118 (early 2003).",
        'charges'     => "California Penal Code § 148(a)(1) — resisting arrest. Arrested at a Santa Cruz, CA anti-Iraq-war demonstration on May 22, 2002.",
        'sentence'    => "No conviction recorded; left town before trial and reported under warrant as of early 2003.",
        'in_custody'  => false,
        'released'    => true,
        'arrest'      => '2002-05-22',
        'incarc'      => null,
        'release'     => null,
    ],
];

foreach ($rows as $r) {
    if (Prisoner::where('name', $r['name'])->exists()) {
        echo "  [skip]   {$r['name']} — already in DB\n";
        continue;
    }

    $p = Prisoner::create([
        'name'        => $r['name'],
        'first_name'  => $r['first'],
        'last_name'   => $r['last'],
        'description' => $r['description'],
        'state'       => 'California',
        'gender'      => 'Male',
        'ideologies'  => ['Anti-War', 'Anti-Iraq War'],
        'affiliation' => null,
        'era'         => '2000s',
        'in_custody'  => $r['in_custody'],
        'released'    => $r['released'],
    ]);

    PrisonerCase::create([
        'prisoner_id'        => $p->id,
        'institution_id'     => $jail->id,
        'charges'            => $r['charges'],
        'arrest_date'        => $r['arrest'],
        'incarceration_date' => $r['incarc'],
        'release_date'       => $r['release'],
        'sentence'           => $r['sentence'],
        'convicted'          => $r['name'] === 'Jim Cosner'
            ? 'No — left town before trial; under warrant'
            : ($r['name'] === 'Nassim Zerriffi'
                ? 'Pending as of Nuclear Resister #118 (early 2003)'
                : 'Yes — convicted of resisting arrest'),
    ]);

    echo "  [add]    {$r['name']}  (Santa Cruz May 22, 2002)\n";
}

echo "\nDone.\n";
echo "Note: Kuo-ling Luo (also arrested May 22, 2002) was deliberately not added.\n";
echo "Per the article, charges (disturbing the peace) were dismissed in October 2002\n";
echo "and Luo is described as an innocent bystander — outside political-prisoner scope.\n";
