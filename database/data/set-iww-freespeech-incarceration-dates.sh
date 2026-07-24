#!/usr/bin/env bash
#
# Fill the incarceration_date for three IWW free-speech-fight prisoners whose
# records already carried arrest_date/release_date but not incarceration_date
# (the field the site's "Incarcerated" line reads from). Jailing was immediate,
# so incarceration is dated to the arrest, keeping the arrest's precision.
#
#   Frank Little   Sep 29, 1909 -> by Oct 8, 1909   (Missoula free-speech fight)
#   E. Cousins     Nov 1909 (month) -> Mar 4, 1910   (Spokane free-speech fight)
#   C. L. Filigno  Nov 1909 (month) -> Mar 4, 1910   (Spokane free-speech fight)
#
# (Sam T. Crane, from the same Spokane fight, was handled in a separate change.)
#
# Idempotent. Run from the repo root:
#   bash database/data/set-iww-freespeech-incarceration-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
// slug => [year, month|null, day|null] for incarceration_date (matches arrest precision)
$people = [
    "frank-little" => [1909, 9, 29],
    "e-cousins"    => [1909, 11, null],
    "c-l-filigno"  => [1909, 11, null],
];

foreach ($people as $slug => $d) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "MISS {$slug}\n"; continue; }
    $p->in_custody = false; $p->released = true; $p->save();

    $c = $p->cases()->first();
    if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; }
    $c->setPartialDate("incarceration_date", $d[0], $d[1] ?? null, $d[2] ?? null);
    $c->save();

    echo str_pad($slug,14)." inc ".str_pad((string)($c->partialDateIso("incarceration_date") ?? "-"),10)
        ." -> rel ".str_pad((string)($c->partialDateIso("release_date") ?? "-"),10)
        ." (".($c->imprisoned_for_days === null ? "n/a" : $c->imprisoned_for_days)." days)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. IWW free-speech incarceration dates filled."
