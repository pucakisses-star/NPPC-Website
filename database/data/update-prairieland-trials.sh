#!/usr/bin/env bash
#
# Fill in case outcomes for the Prairieland Trials — the federal prosecution of
# people arrested after the July 4, 2025 protest outside the Prairieland ICE
# Detention Center in Alvarado, Texas, which the U.S. Department of Justice
# labeled a "North Texas Antifa Cell." (Surfaced via Fire Ant Movement Defense,
# fireantmovement.org / @fireantmovementdefense.)
#
# Sourcing: DOJ USAO-NDTX conviction release + Al Jazeera (July 1, 2026) and
# Houston Public Media (June 24, 2026) sentencing coverage.
#
#  - Nine defendants were convicted at trial (jury verdict March 13, 2026) and
#    sentenced in June 2026. Actual imposed sentences: Benjamin Song 100 years;
#    Maricela Rueda 70 years; Cameron Arnold, Zachary Evetts, Bradford/Meagan
#    Morris, Savanna Batten, Elizabeth Soto, Ines Soto 50 years each; Daniel
#    Rolando Sanchez-Estrada 30 years (appealing).
#  - Seven defendants pleaded guilty to providing material support to
#    terrorists and were sentenced in July 2026; reported terms in that group
#    ranged from about 2 to 15 years. (Exact per-person plea sentences appear
#    only in a movement statement and are NOT asserted here — just the plea and
#    the range.)
#
# Most of these already have records; this fills their (empty) case. Only
# Daniel Rolando Sanchez-Estrada is created. Idempotent. Run from the repo root:
#   bash database/data/update-prairieland-trials.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$base = "Riot; providing material support to terrorists; conspiracy to use and carry an explosive; using and carrying an explosive";
$verdict = "Yes — convicted at trial (federal jury, Northern District of Texas; verdict March 13, 2026)";
$pleaConv = "Yes — pleaded guilty to providing material support to terrorists";
$pleaSentence = "Sentenced July 2026; sentences in the plea group were reported to range from about 2 to 15 years";
$pleaCharges = "Providing material support to terrorists (guilty plea)";

// [slug, charges, convicted, sentence]  (trial group; sentenced June 2026)
$trial = [
    ["benjamin-song", $base . "; attempted murder of officers; discharging a firearm (3 counts)", $verdict, "100 years"],
    ["maricela-rueda", $base . "; conspiracy to conceal documents", $verdict, "70 years"],
    ["cameron-arnold", $base, $verdict, "50 years"],
    ["zachary-evetts", $base, $verdict, "50 years"],
    ["meagan-morris",  $base, $verdict, "50 years"],
    ["savanna-batten", $base, $verdict, "50 years"],
    ["elizabeth-soto", $base, $verdict, "50 years"],
    ["ines-soto",      $base, $verdict, "50 years"],
];
$pleas = ["seth-sikes", "nathan-baumann", "joy-gibson", "susan-kent", "rebecca-morgan", "lynette-sharp", "john-thomas"];

$applyCase = function ($p, $charges, $convicted, $sentence, $sy, $sm) {
    $c = $p->cases()->first();
    if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; }
    $c->charges = $charges;
    $c->convicted = $convicted;
    $c->sentence = $sentence;
    $c->setPartialDate("incarceration_date", 2025, 7);
    $c->setPartialDate("sentenced_date", $sy, $sm);
    $c->save();
    $p->in_custody = true;
    $p->released = false;
    $p->save();
};

$done = 0; $missing = 0;
foreach ($trial as $t) {
    [$slug, $charges, $convicted, $sentence] = $t;
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "  trial defendant not found: {$slug}\n"; $missing++; continue; }
    if ($slug === "meagan-morris" && empty($p->aka)) { $p->aka = "Bradford Morris"; $p->save(); }
    $applyCase($p, $charges, $convicted, $sentence, 2026, 6);
    echo "  {$slug}: {$sentence}\n"; $done++;
}
foreach ($pleas as $slug) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "  plea defendant not found: {$slug}\n"; $missing++; continue; }
    $applyCase($p, $pleaCharges, $pleaConv, $pleaSentence, 2026, 7);
    echo "  {$slug}: guilty plea\n"; $done++;
}

// Daniel Rolando Sanchez-Estrada — not yet in the database.
$slug = "daniel-rolando-sanchez-estrada";
$p = \App\Models\Prisoner::withoutGlobalScopes()
    ->where("slug", $slug)->orWhere("name", "like", "%Sanchez-Estrada%")->first();
if (! $p) {
    $p = \App\Models\Prisoner::create([
        "name" => "Daniel Rolando Sanchez-Estrada",
        "first_name" => "Daniel", "middle_name" => "Rolando", "last_name" => "Sanchez-Estrada",
        "aka" => "Daniel Sanchez Estrada",
        "description" => "Daniel Rolando Sanchez-Estrada is one of the defendants in the Prairieland case, the federal prosecution of people arrested after the July 4, 2025 protest outside the Prairieland ICE Detention Center in Alvarado, Texas, which the U.S. Department of Justice labeled a \"North Texas Antifa Cell.\" He was convicted at trial (jury verdict March 13, 2026) of corruptly concealing a document or record and conspiracy to conceal documents — accused of moving a box of movement materials, and not accused of the shooting — and was sentenced in 2026 to 30 years in federal prison. He has filed a notice of appeal.",
        "state" => "Texas", "era" => "2020s",
        "ideologies" => ["Anti-fascism"], "affiliation" => ["Prairieland Defendants"],
        "in_custody" => true, "released" => false,
    ]);
    echo "  created {$p->slug}\n"; $done++;
} else {
    echo "  Sanchez-Estrada already present: {$p->slug}\n";
}
$applyCase($p, "Corruptly concealing a document or record; conspiracy to conceal documents", $verdict, "30 years (appealing)", 2026, 6);

echo "\nUpdated/created {$done}; not found {$missing}.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Prairieland Trials case outcomes updated."
