#!/usr/bin/env bash
#
# Second custody-data pass for Cop City / Defend the Atlanta Forest defendants,
# from documented (mostly first-person / official) release info.
#
#  1. Feola: the record was duplicated — "Henri Feola" (current name) and
#     "Madeleine Feola" (the name they were booked under, which carries the
#     booking mugshot) are the same person. This merges Madeleine into Henri:
#     moves the photo, records "Madeleine Feola" as an aka, sets custody
#     (Jan 21 - Apr 26, 2023; Feola states 96 days), and deletes the duplicate.
#
#  2. Custody corrections (day counts use the person's own stated figure where
#     they gave one):
#       Priscilla Grim  Mar 5 - Apr 5, 2023   (she states exactly 31 days)
#       Luke Harper     Mar 5 - Jun 2, 2023   (his own "day 90")
#       Victor Puertas  Mar 5, 2023 - Feb 26, 2024 (DeKalb then ICE, continuous)
#       John Mazurek    Feb 8 - Apr 1, 2024   (53 days official jail credit)
#
#  3. Day-count fill-ins for defendants who already had release dates:
#       Thomas Jurgens (2), Kayley Meissner (13), Adele MacLean (2),
#       Marlon Kautz (2), Savannah Patterson (2).
#
# Idempotent. Run from the repo root:
#   bash database/data/update-cop-city-custody-batch2.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
// --- 1. Merge the Feola duplicate (Madeleine -> Henri) ---
$h = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "henri-feola")->first();
$m = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "madeleine-feola")->first();
if ($h && $m) {
    if (empty($h->photo) && ! empty($m->photo)) { $h->photo = $m->photo; }
    if (empty($h->aka)) { $h->aka = "Madeleine Feola"; }
    foreach (["gender", "race", "state", "birthdate"] as $f) {
        if (empty($h->{$f}) && ! empty($m->{$f})) { $h->{$f} = $m->{$f}; }
    }
    $h->ideologies = array_values(array_unique(array_merge((array) $h->ideologies, (array) $m->ideologies))) ?: null;
    $h->affiliation = array_values(array_unique(array_merge((array) $h->affiliation, (array) $m->affiliation))) ?: null;
    $h->save();
    \App\Models\PrisonerCase::where("prisoner_id", $m->id)->delete();
    $m->delete();
    echo "Merged madeleine-feola into henri-feola.\n";
}

// --- 2 & 3. Set custody dates + durations on the first case ---
$data = [
    ["henri-feola",        2023, 1, 21, 2023, 4, 26, 96],
    ["priscilla-grim",     2023, 3, 5,  2023, 4, 5,  31],
    ["luke-harper",        2023, 3, 5,  2023, 6, 2,  90],
    ["victor-puertas",     2023, 3, 5,  2024, 2, 26, 358],
    ["john-mazurek",       2024, 2, 8,  2024, 4, 1,  53],
    ["thomas-jurgens",     2023, 3, 5,  2023, 3, 7,  2],
    ["kayley-meissner",    2023, 3, 5,  2023, 3, 18, 13],
    ["adele-maclean",      2023, 5, 31, 2023, 6, 2,  2],
    ["marlon-kautz",       2023, 5, 31, 2023, 6, 2,  2],
    ["savannah-patterson", 2023, 5, 31, 2023, 6, 2,  2],
];
$updated = 0;
foreach ($data as $d) {
    [$slug, $ay, $am, $ad, $ry, $rm, $rd, $days] = $d;
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "  not found: {$slug}\n"; continue; }
    $c = $p->cases()->first();
    if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; }
    $c->setPartialDate("arrest_date", $ay, $am, $ad);
    $c->setPartialDate("incarceration_date", $ay, $am, $ad);
    $c->setPartialDate("release_date", $ry, $rm, $rd);
    $c->imprisoned_for_days = $days;
    $c->save();
    echo "  {$slug}: {$days} days ({$ay}-{$am}-{$ad} to {$ry}-{$rm}-{$rd})\n";
    $updated++;
}
echo "Updated {$updated} case(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Cop City custody batch 2 applied."
