#!/usr/bin/env bash
#
# BATCH 54 -- remove Cliver Alcala's projected release date.
#
# cliver-antonio-alcala-cordero, the retired Venezuelan major general
# sentenced in March 2024 to 262 months, carried a stored RELEASE DATE
# of September 14, 2038 — while his flags say in custody and his own
# sentence text estimates release around 2042. A date in 2038 for a man
# still inside is a Bureau of Prisons PROJECTION, not a release, and
# the standing rule since batch 30 (Sarah Lockrey) is that a projected
# date is never stored as a release: it made his profile read
# "Imprisoned For 14 years 6 months 10 days" as though the span were
# already served.
#
# The date is cleared. He remains in custody; his counter now runs from
# his March 4, 2024 federal commitment like every other current
# prisoner, and the sentence text is amended to note that no projected
# date is stored. The 2038-vs-2042 disagreement between the deleted
# date and the prose is preserved here: 2038-09-14 has the shape of a
# BOP good-time projection from the 2020 arrest, while ~2042 is the
# arithmetic of 262 months from sentencing. Neither belongs in the
# release field.
#
# Nothing else on the record changes. Idempotent: compared before
# writing.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-54.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

echo "==================================================================="
echo "  Batch 54 — Alcala: projected release date removed"
echo "==================================================================="

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "cliver-antonio-alcala-cordero")->with("cases")->first();

if (! $p) {
    echo "Record not found — nothing changed.\n";
    return;
}

$case = $p->cases->sortBy("created_at")->first();

if (! $case) {
    echo "No case row — nothing changed.\n";
    return;
}

$case->setRelation("prisoner", $p);

$notes = [];

if ($case->release_date) {
    $notes[] = "release date cleared (was ".$case->release_date->format("Y-m-d")." — a projection, not a release)";
    $case->setPartialDate("release_date", null);
}

$sentence = "262 months federal prison, sentenced March 5, 2024, after an August 2023 guilty plea to a narco-terrorism conspiracy count. In custody since surrendering to DEA agents on March 27, 2020. Bureau of Prisons projections have put his release in the late 2030s and the plain arithmetic of the sentence runs to about 2042; a projected date is not a release, so none is stored.";

if ($case->sentence !== $sentence) {
    $case->sentence = $sentence;
    $notes[] = "sentence text";
}

if ($notes) {
    $case->save();
}

echo "  ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
echo "  days now: ", ($case->imprisoned_for_days === null ? "null (in custody — computed live)" : $case->imprisoned_for_days), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
