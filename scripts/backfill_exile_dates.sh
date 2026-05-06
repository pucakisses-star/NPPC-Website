#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/backfill_exile_dates.sh
#
# After updating the PrisonerCase booted() saving hook to auto-derive
# in_exile_since from release_date when the prisoner is flagged
# in_exile, this script touches every case row of every in_exile or
# currently_in_exile prisoner so the new logic runs and populates
# in_exile_since (and therefore in_exile_for_days, and therefore the
# Time in Exile counter on the prisoner page).
#
# The hook only sets in_exile_since when it is currently null and the
# case has a release_date - so explicitly-set values (e.g. Bill
# Haywood's 1921-03-31 bail-jump date) are preserved.
set -e

php artisan tinker --execute='
$prisoners = App\Models\Prisoner::where(function ($q) {
    $q->where("in_exile", true)->orWhere("currently_in_exile", true);
})->get();

echo "Found {$prisoners->count()} in-exile prisoners.\n\n";

$casesTouched = 0;
$exileBackfilled = 0;

foreach ($prisoners as $p) {
    foreach ($p->cases as $case) {
        $oldExile = $case->in_exile_since?->toDateString();
        // Trigger the booted hook to recompute everything
        $case->save();
        $newExile = $case->fresh()->in_exile_since?->toDateString();
        $casesTouched++;
        if (! $oldExile && $newExile) {
            $exileBackfilled++;
            echo "backfilled: {$p->name} -> in_exile_since = {$newExile}, in_exile_for_days = {$case->in_exile_for_days}\n";
        }
    }
}

echo "\nDone. {$casesTouched} cases touched; {$exileBackfilled} had in_exile_since auto-derived from release_date.\n";

// Report any in-exile prisoners whose cases still have no exile date
// (these need manual setting because they have no release_date either)
$stillMissing = [];
foreach ($prisoners as $p) {
    foreach ($p->cases as $case) {
        if (! $case->fresh()->in_exile_since) {
            $stillMissing[] = $p->name;
            break;
        }
    }
}
if (count($stillMissing)) {
    echo "\nIn-exile prisoners still without in_exile_since (no release_date to derive from):\n";
    foreach ($stillMissing as $n) { echo "  - {$n}\n"; }
}
'

echo
echo "Exile-date backfill complete."
