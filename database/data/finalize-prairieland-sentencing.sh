#!/usr/bin/env bash
#
# Finalize the Prairieland (July 4, 2025 ICE-detention-center case) FEDERAL
# sentences on each defendant's case, and fix the descriptions that still read
# as pre-sentencing.
#
# Final imposed sentences (sources: DOJ USAO-NDTX; Al Jazeera Jul 1, 2026;
# Houston Public Media Jun 24, 2026; per-defendant plea terms supplied by the
# site owner):
#   Trial (verdict Mar 13, 2026; sentenced Jun 23, 2026)
#     Benjamin Song 100y; Maricela Rueda 70y; Cameron Arnold, Zachary Evetts,
#     Meagan/Bradford Morris, Savanna Batten, Elizabeth Soto, Ines Soto 50y each
#     Daniel Rolando Sanchez-Estrada 30y (appealing)
#   Guilty plea to material support (sentenced Jul 1, 2026 unless noted)
#     Joy Gibson 180mo (15y); Rebecca Morgan 180mo (15y);
#     Lynette Sharp 110mo (9y2m); John Thomas 110mo (9y2m);
#     Seth Sikes 72mo (6y); Susan Kent 72mo (6y, sentenced Jul 6);
#     Nathan Baumann 22mo
#
# Idempotent. Run from the repo root:
#   bash database/data/finalize-prairieland-sentencing.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$base = "Riot; providing material support to terrorists; conspiracy to use and carry an explosive; using and carrying an explosive";
$verdict = "Yes — convicted at trial (federal jury, Northern District of Texas; verdict March 13, 2026)";
$plea = "Yes — pleaded guilty to providing material support to terrorists";

// [slug, charges, convicted, sentence, sy, sm]
$data = [
    ["benjamin-song", $base . "; attempted murder of officers; discharging a firearm (3 counts)", $verdict, "100 years", 2026, 6],
    ["maricela-rueda", $base . "; conspiracy to conceal documents", $verdict, "70 years", 2026, 6],
    ["cameron-arnold", $base, $verdict, "50 years", 2026, 6],
    ["zachary-evetts", $base, $verdict, "50 years", 2026, 6],
    ["meagan-morris", $base, $verdict, "50 years", 2026, 6],
    ["savanna-batten", $base, $verdict, "50 years", 2026, 6],
    ["elizabeth-soto", $base, $verdict, "50 years", 2026, 6],
    ["ines-soto", $base, $verdict, "50 years", 2026, 6],
    ["daniel-rolando-sanchez-estrada", "Corruptly concealing a document or record; conspiracy to conceal documents", $verdict, "30 years (appealing)", 2026, 6],
    ["joy-gibson", "Providing material support to terrorists (guilty plea)", $plea, "180 months (15 years)", 2026, 7],
    ["rebecca-morgan", "Providing material support to terrorists (guilty plea)", $plea, "180 months (15 years)", 2026, 7],
    ["lynette-sharp", "Providing material support to terrorists (guilty plea)", $plea, "110 months (9 years, 2 months)", 2026, 7],
    ["john-thomas", "Providing material support to terrorists (guilty plea)", $plea, "110 months (9 years, 2 months)", 2026, 7],
    ["seth-sikes", "Providing material support to terrorists (guilty plea)", $plea, "72 months (6 years)", 2026, 7],
    ["susan-kent", "Providing material support to terrorists (guilty plea)", $plea, "72 months (6 years)", 2026, 7],
    ["nathan-baumann", "Providing material support to terrorists (guilty plea)", $plea, "22 months", 2026, 7],
];
$done = 0;
foreach ($data as $d) {
    [$slug, $charges, $convicted, $sentence, $sy, $sm] = $d;
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "  not found: {$slug}\n"; continue; }
    $c = $p->cases()->first();
    if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; }
    $c->charges = $charges;
    $c->convicted = $convicted;
    $c->sentence = $sentence;
    $c->setPartialDate("incarceration_date", 2025, 7);
    $c->setPartialDate("sentenced_date", $sy, $sm);
    $c->save();
    $p->in_custody = true; $p->released = false; $p->save();
    echo "  {$slug}: {$sentence}\n"; $done++;
}

// Fix the descriptions still reading as pre-sentencing (targeted, idempotent).
$fixes = [
    "ines-soto" => [
        ["She faces a minimum of 10 years and a maximum of 60 years in federal prison.", "On June 23, 2026 she was sentenced to 50 years in federal prison."],
        [" Sentencing is scheduled for June 18, 2026.", ""],
    ],
    "nathan-baumann" => [
        ["Sentencing is scheduled for July 1, 2026 in the U.S. District Court for the Northern District of Texas. ", ""],
        ["They face up to 15 years in federal prison.", "On July 1, 2026 they were sentenced to 22 months in federal prison."],
    ],
    "seth-sikes" => [
        ["Sentencing is scheduled for July 1, 2026 in the U.S. District Court for the Northern District of Texas. ", ""],
        ["They face up to 15 years in federal prison.", "On July 1, 2026 they were sentenced to 72 months (6 years) in federal prison."],
    ],
    "susan-kent" => [
        ["Sentencing is scheduled for July 1, 2026 in the U.S. District Court for the Northern District of Texas. ", ""],
        ["They face up to 15 years in federal prison.", "On July 6, 2026 they were sentenced to 72 months (6 years) in federal prison."],
    ],
    "lynette-sharp" => [
        ["Sentencing is scheduled for July 1, 2026 in the U.S. District Court for the Northern District of Texas. ", ""],
        ["They face up to 15 years in federal prison.", "On July 1, 2026 they were sentenced to 110 months (9 years, 2 months) in federal prison."],
    ],
    "john-thomas" => [
        ["Sentencing is scheduled for July 1, 2026 in the U.S. District Court for the Northern District of Texas. ", ""],
        ["They face up to 15 years in federal prison.", "On July 1, 2026 they were sentenced to 110 months (9 years, 2 months) in federal prison."],
    ],
];
foreach ($fixes as $slug => $reps) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p || ! $p->description) { continue; }
    $desc = $p->description;
    foreach ($reps as $r) { $desc = str_replace($r[0], $r[1], $desc); }
    if ($desc !== $p->description) { $p->description = $desc; $p->save(); echo "  desc updated: {$slug}\n"; }
}

echo "\nFinalized {$done} Prairieland federal sentence(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Prairieland federal sentencing finalized."
