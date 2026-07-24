#!/usr/bin/env bash
#
# Correct John Berry (director): he was blacklisted and self-exiled to France,
# but per Wikipedia he FIRST returned to the U.S. in the early 1960s (directing
# television such as East Side/West Side and Seaway) — not 1973. He later worked
# in France again and returned once more in the 1970s (Claudine, 1974).
#
# This is self-contained: it converts the record from imprisonment to exile
# (clearing arrest/incarceration/release) and sets in_exile_since = 1951 and
# end_of_exile = 1963 (early 1960s, year precision), and corrects the "returned
# in 1973" sentence in the description. Supersedes fix-john-berry-exile.sh.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-john-berry-return-date.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "john-berry-director")->first();
if (! $p) { echo "john-berry-director not found.\n"; return; }

// Clean the name: "(director)" is a Wikipedia disambiguation suffix, not part
// of his name (and there is no other John Berry in the database). The slug is
// left unchanged so existing links keep working.
$p->name = "John Berry";
if (empty($p->first_name)) { $p->first_name = "John"; }
if (empty($p->last_name)) { $p->last_name = "Berry"; }
$p->aka = null;

$p->in_exile = true;
$p->currently_in_exile = false;
$p->in_custody = false;
$p->released = false;

// Correct the description return date (idempotent str_replace).
if ($p->description) {
    $p->description = str_replace(
        "He returned to the U.S. in 1973 and continued to direct in theater and film until his death in Paris in 1999.",
        "With the blacklist gone he first returned to the U.S. in the early 1960s (directing television such as East Side/West Side and Seaway), later worked again in France, and returned to direct films including Claudine (1974). He continued to direct in theater and film until his death in Paris in 1999.",
        $p->description
    );
}
$p->save();

$c = $p->cases()->first();
if (! $c) { echo "No case found.\n"; return; }
$c->arrest_date = null;
$c->incarceration_date = null;
$c->release_date = null;
$c->imprisoned_for_days = null;
$c->setPartialDate("in_exile_since", 1951);
$c->setPartialDate("end_of_exile", 1963);
$c->convicted = "No — never charged; named in HUAC testimony and industry blacklist (1951)";
$c->charges = ["No criminal charges — named in HUAC testimony and blacklisted (1951)"];
$c->sentence = "Blacklist-era exile in France, roughly 1951 to the early 1960s (about a decade)";
$c->save();

$p->refresh();
echo "Fixed john-berry-director: in exile 1951 - early 1960s (in_exile_for_days={$c->in_exile_for_days}); return date corrected.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. John Berry return date and exile corrected."
