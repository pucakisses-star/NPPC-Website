#!/usr/bin/env bash
#
# Enrich Marty Gottesfeld's thin case record. The stored case had only
# Charges = "Hacking" and a release date; this fills in the well-documented
# facts already stated in his bio and confirmed by the cases export:
#   - Incarceration date: 2016-02-17 (held since Feb 2016 after arrest at sea)
#   - Charges: the actual federal counts (DDoS on Boston Children's Hospital)
#   - Convicted: federal jury, August 2018
#   - Sentence: 121 months (~10 years) + restitution, sentenced January 2019
# The existing release date (2023-11-14) is left as-is.
#
# Idempotent: only writes case fields that are still empty / still the bare
# "Hacking" placeholder. Run from the repo root:
#   bash database/data/update-marty-gottesfeld-case.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "marty-gottesfeld")->first();
if (! $p) { echo "marty-gottesfeld not found\n"; return; }

$case = $p->cases()->first();
if (! $case) { echo "no case row to enrich\n"; return; }

$changed = false;

if (empty($case->incarceration_date)) {
    $case->incarceration_date = "2016-02-17";
    $changed = true; echo "SET incarceration_date 2016-02-17\n";
}

// Replace the bare "Hacking" placeholder with the real charges.
$charges = $case->charges;
if (is_array($charges)) { $charges = implode(" ", $charges); }
if (trim((string) $charges) === "" || strtolower(trim((string) $charges)) === "hacking") {
    $case->charges = "Conspiracy to damage protected computers and damaging protected computers (18 U.S.C. § 1030) — DDoS attacks on Boston Children'"'"'s Hospital and Wayside Youth & Family Support Network";
    $changed = true; echo "SET charges to the federal counts\n";
}

if (empty($case->convicted)) {
    $case->convicted = "Yes — convicted by a federal jury, August 2018";
    $changed = true; echo "SET convicted\n";
}

if (empty($case->sentence)) {
    $case->sentence = "121 months (just over 10 years) in federal prison plus restitution; sentenced January 2019";
    $changed = true; echo "SET sentence\n";
}

if ($case->isDirty()) { $case->save(); }

echo $changed ? "Updated marty-gottesfeld case.\n" : "Nothing to do.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Marty Gottesfeld case enriched."
