#!/usr/bin/env bash
#
# Fill in / correct incarceration and release dates for Anonymous-affiliated
# cases, from documented primary sources only (DOJ, BOP inmate locator,
# federal dockets, contemporaneous news). Cases where the report-to-prison
# date is genuinely not in the public record are intentionally left alone
# rather than filled with arithmetic estimates.
#
#   - John Anthony Borell III: incarceration 2013-12-06 (court-ordered
#     surrender date; case previously had neither date). Release still
#     undocumented, left null.
#   - Brian Thomas Mettenbrink: release corrected 2011-06-23 -> 2011-06-24
#     (BOP official actual-release date, reg #57431-112).
#   - Dmitriy Guzner: release corrected 2011-05-12 -> 2011-05-13
#     (BOP official actual-release date, reg #29985-050).
#
# Idempotent. Run from the repo root:
#   bash database/data/update-anonymous-case-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$set = function (string $slug, ?string $incFrom, ?string $inc, ?string $relFrom, ?string $rel) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "  {$slug}: NOT FOUND\n"; return; }
    $c = $p->cases()->first();
    if (! $c) { echo "  {$slug}: no case\n"; return; }
    $did = false;

    // Set incarceration only if currently empty (never clobber).
    if ($inc !== null && empty($c->incarceration_date)) {
        $c->incarceration_date = $inc; $did = true;
        echo "  {$slug}: SET incarceration_date {$inc}\n";
    }
    // Correct release only if currently empty or still the old value.
    if ($rel !== null) {
        $cur = (string) $c->release_date;
        if ($cur === "" || $cur === $relFrom || str_starts_with($cur, (string) $relFrom)) {
            if ($cur !== $rel) {
                $c->release_date = $rel; $did = true;
                echo "  {$slug}: SET release_date {$rel}" . ($cur ? " (was {$cur})" : "") . "\n";
            }
        } else {
            echo "  {$slug}: release_date left as {$cur} (not the expected old value)\n";
        }
    }

    if ($c->isDirty()) { $c->save(); }
    if (! $did) { echo "  {$slug}: nothing to do\n"; }
};

$set("john-anthony-borell-iii", null, "2013-12-06", null, null);
$set("brian-thomas-mettenbrink", null, null, "2011-06-23", "2011-06-24");
$set("dmitriy-guzner", null, null, "2011-05-12", "2011-05-13");

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Anonymous case dates updated."
