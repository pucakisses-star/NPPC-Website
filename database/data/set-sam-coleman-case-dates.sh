#!/usr/bin/env bash
#
# Add specific custody dates to Sam Coleman's (Samuel Irving Coleman's) harboring
# case, replacing the vague "April 1954" month with day-level dates:
#
#   arrest        Aug 27, 1953  captured with Robert Thompson, Sidney Steinberg,
#                               Shirley Kremen and Carl Ross at the Twain Harte,
#                               California cabin (corroborated by the Sidney
#                               Steinberg record already in this repo)
#   convicted     Apr 26, 1954  jury verdict (guilty of harboring)
#   sentenced     May  3, 1954  three years
#   incarcerated  May  3, 1954  remanded at sentencing
#   released      Nov 20, 1954  freed on $40,000 bail pending appeal
#
# The convictions were later reversed by the Supreme Court in Kremen v. United
# States, 353 U.S. 346 (1957), so only this ~6.5-month pre-appeal stretch was
# served. imprisoned_for_days is left to derive from the incarceration/release
# dates (~201 days). The prisoner bio's vague month is likewise updated and the
# fugitive's name corrected (Sidney Stein -> Sidney Steinberg).
#
# Idempotent. Run from the repo root:
#   bash database/data/set-sam-coleman-case-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()
    ->where("slug", "sam-coleman")
    ->orWhereRaw("LOWER(name) = ?", ["sam coleman"])
    ->first();

if (! $p) { echo "Sam Coleman not found.\n"; return; }

// Tidy the bio: specific verdict date and the fugitive'"'"'s full surname.
if (! empty($p->description)) {
    $bio = $p->description;
    $bio = str_replace("Sidney Stein ", "Sidney Steinberg ", $bio);
    $bio = str_replace("harboring in April 1954", "harboring on April 26, 1954", $bio);
    if ($bio !== $p->description) { $p->description = $bio; $p->save(); echo "Bio updated.\n"; }
}

$c = $p->cases()->first();
if (! $c) {
    $c = new \App\Models\PrisonerCase();
    $c->prisoner_id = $p->id;
    $c->charges = "Harboring Communist Smith Act fugitives";
    echo "No existing case — created one.\n";
}

$c->convicted = "Yes — jury verdict April 26, 1954";
$c->sentence  = "Three years. Released November 20, 1954 on \$40,000 bail pending appeal; the convictions were reversed by the U.S. Supreme Court in Kremen v. United States, 353 U.S. 346 (1957), over the FBI\u{2019}s warrantless seizure of the cabin\u{2019}s contents, so only this pre-appeal stretch was served.";
$c->setPartialDate("arrest_date", 1953, 8, 27);
$c->setPartialDate("sentenced_date", 1954, 5, 3);
$c->setPartialDate("incarceration_date", 1954, 5, 3);
$c->setPartialDate("release_date", 1954, 11, 20);
$c->save();

echo "Case dates set: arrest ".($c->partialDateIso("arrest_date") ?? "-")
    .", incarcerated ".($c->partialDateIso("incarceration_date") ?? "-")
    ." -> released ".($c->partialDateIso("release_date") ?? "-")
    ." ({$c->imprisoned_for_days} days).\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Sam Coleman case dates set (May 3 - Nov 20, 1954)."
