#!/usr/bin/env bash
#
# Enrich and correct the Juan Segarra-Palmer record (July 2026), from his
# Wikipedia article cross-checked against the Puerto Rico Herald's
# release coverage and the Freedom Archives news list:
#
#  1. His record carried two case rows describing the same Wells Fargo /
#     seditious-conspiracy prosecution, with conflicting release dates.
#     Contemporaneous coverage confirms he was freed in late January 2004,
#     arriving in Puerto Rico on January 24 — the detailed row's
#     "2004-10-10" is corrected and the redundant summary row deleted.
#  2. Fills middle name "Enrique" and appends the sourced biography:
#     Santurce birth, Andover/Harvard education, prisoner-of-war stance
#     at trial, the September 12, 1983 robbery date chosen for Albizu
#     Campos's birthday, and the stricter 1999 clemency terms.
#
# Idempotent: guarded exact-match corrections, marker-guarded appends,
# fill-if-empty scalars.
#
# Run from the repo root:  bash database/data/enrich-segarra-palmer.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "juan-segarra-palmer")->first();
if (! $p) { echo "MISS juan-segarra-palmer\n"; exit(0); }

// 1. Correct the detailed row (the one with the judge recorded) and drop
//    the redundant summary row for the same prosecution.
$detailed = $p->cases()->whereNotNull("judge")->where("judge", "like", "%Nevas%")->first();
if ($detailed) {
    $changed = false;
    if ($detailed->release_date && $detailed->release_date->format("Y-m-d") === "2004-10-10") {
        $detailed->release_date = "2004-01-24";
        $changed = true;
    }
    if ($detailed->sentence === "65 years federal prison") {
        $detailed->sentence = "65 years federal prison, reduced to 55 on appeal; a September 1999 Clinton clemency cut the term further, and he was freed in late January 2004, arriving in Puerto Rico on January 24";
        $changed = true;
    }
    if ($changed) { $detailed->save(); echo "CASE corrected\n"; }
}
if ($p->cases()->count() === 2) {
    $summary = $p->cases()->whereNull("judge")->where("sentence", "like", "%reduced to 55 on appeal%")->first();
    if ($summary) { $summary->delete(); echo "CASE redundant row deleted\n"; }
}

// 2. Fill-if-empty middle name and marker-guarded biography append.
if (empty($p->middle_name)) { $p->middle_name = "Enrique"; $p->save(); echo "MIDDLE\n"; }

$marker = "Phillips Academy";
if (! str_contains((string) $p->description, $marker)) {
    $p->description = trim((string) $p->description) . "\n\nBorn March 6, 1950 in Santurce to a nationalist family with a long history of resistance to both Spanish and American rule, Segarra-Palmer was educated at Phillips Academy Andover and Harvard before returning to organizing work in New York neighborhoods, Boston prisons, and Puerto Rican land-reclamation campaigns. The Wells Fargo robbery was carried out on September 12, 1983 — Pedro Albizu Campos'"'"'s birthday — and at trial Segarra-Palmer declared himself a prisoner of war and refused to participate in the proceedings. The September 1999 Clinton clemency treated him more strictly than the FALN prisoners released outright: deemed to have played a more serious role, he received a sentence reduction requiring roughly five more years inside. Freed in late January 2004 after nearly nineteen years, he was greeted by hundreds on his return to Puerto Rico on January 24.";
    $p->save();
    echo "DESC\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Segarra-Palmer enrichment applied."
