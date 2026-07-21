#!/usr/bin/env bash
#
# Fill in the structured details of George Marshall's contempt-of-Congress
# case, per site-owner research:
#   - Incarcerated June 2, 1950 (first held at the Washington, D.C. jail,
#     then transferred to FCI Ashland, Kentucky)
#   - Sentence: three months AND a $500 fine (fine was previously missing)
#   - Released September 1950 (exact day not yet verified, so release_date
#     is left null rather than guessed)
#
# Idempotent: only writes fields that are still empty / not yet corrected.
# Run from the repo root:
#   bash database/data/update-george-marshall-case.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "george-marshall")->first();
if (! $p) { echo "george-marshall not found\n"; return; }

$changed = false;

// The single contempt-of-Congress case (match on the charge text).
$case = $p->cases()
    ->where("charges", "like", "%Contempt of Congress%")
    ->first();

if ($case) {
    if (empty($case->incarceration_date)) {
        $case->incarceration_date = "1950-06-02";
        $changed = true;
        echo "SET incarceration_date 1950-06-02\n";
    }
    if (trim((string) $case->sentence) === "Three months") {
        $case->sentence = "Three months and a \$500 fine";
        $changed = true;
        echo "SET sentence to include \$500 fine\n";
    }
    if ($case->isDirty()) { $case->save(); }
} else {
    echo "contempt case not found — skipping case fields\n";
}

// Note the initial custody at the D.C. jail in the bio (structured schema
// has no place for it). Guard on a marker so re-runs do not duplicate it.
if (strpos((string) $p->description, "Washington, D.C.") === false) {
    $p->description = rtrim((string) $p->description)
        . " He was first jailed in Washington, D.C., before being transferred to the Federal Correctional Institution at Ashland.";
    $p->save();
    $changed = true;
    echo "APPENDED D.C. jail detail to description\n";
}

echo $changed ? "Updated george-marshall.\n" : "Nothing to do.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. George Marshall case details updated."
