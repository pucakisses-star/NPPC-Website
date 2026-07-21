#!/usr/bin/env bash
#
# Fill in dates of birth for currently-imprisoned prisoners who were missing a
# DOB. The values were researched from public records (DOJ / U.S. Attorney
# press releases, the federal Bureau of Prisons and state DOC inmate locators,
# court filings, and reputable news), matched to each person by state/case to
# avoid namesakes. The roster lives in
# database/data/currently-imprisoned-dob.json.
#
# Precision is preserved: a full date found in a record is stored day-precise;
# a DOB derived from a published age is stored at YEAR precision, so the site
# shows only the year (e.g. "1976") rather than a false January 1. Low-
# confidence / unconfirmed-identity matches were deliberately left out.
#
# Idempotent and non-destructive: a birthdate is written ONLY where the field
# is still empty, so a person who already has a DOB (or gets one later) is
# never overwritten. Run from the repo root:
#   bash database/data/set-currently-imprisoned-dob.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$path = base_path("database/data/currently-imprisoned-dob.json");
$rows = json_decode(file_get_contents($path), true);
if (! is_array($rows)) { echo "Could not read DOB JSON\n"; return; }

$set = 0; $skipHasDob = 0; $missing = 0;

foreach ($rows as $r) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $r["slug"])->first();
    if (! $p) { echo "  not found: {$r["slug"]}\n"; $missing++; continue; }
    if (! empty($p->birthdate)) { $skipHasDob++; continue; }

    $parts = explode("-", $r["dob"]);
    $y = (int) ($parts[0] ?? 0);
    $m = (int) ($parts[1] ?? 1);
    $d = (int) ($parts[2] ?? 1);
    $prec = $r["precision"] ?? "day";

    if ($prec === "year")  { $p->setPartialDate("birthdate", $y); }
    elseif ($prec === "month") { $p->setPartialDate("birthdate", $y, $m); }
    else { $p->setPartialDate("birthdate", $y, $m, $d); }

    $p->save();
    echo "  set {$r["slug"]} = {$r["dob"]} ({$prec})\n";
    $set++;
}

echo "Set {$set} birthdate(s); skipped {$skipHasDob} that already had one; {$missing} slug(s) not found.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Dates of birth filled in for currently-imprisoned prisoners."
