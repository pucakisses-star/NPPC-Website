#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/init_unknown_exile_dates_to_today.sh
#
# For prisoners flagged in_exile or currently_in_exile but whose case
# rows have no in_exile_since AND no release_date (so the auto-derive
# hook can't fill it from release_date), this script sets
# in_exile_since on the most recent case to today's date - so the
# Time in Exile counter starts rendering at "0 days" today and ticks
# up by 1 each day via the daily cases:update-imprisoned-days
# scheduled command.
#
# This is a deliberate fallback for prisoners whose actual exile-start
# date is not known. It is historically inaccurate but at least
# surfaces the in-exile status visibly on the public page. If the
# actual exile-start date is later identified, replace it via the
# Filament admin and the daily command will recompute accordingly.
set -e

php artisan tinker --execute='
$prisoners = App\Models\Prisoner::where(function ($q) {
    $q->where("in_exile", true)->orWhere("currently_in_exile", true);
})->get();

$today = \Carbon\Carbon::today()->toDateString();
$set = 0;
foreach ($prisoners as $p) {
    // Find a case row that is missing in_exile_since
    $caseToFix = $p->cases()->whereNull("in_exile_since")->orderByDesc("created_at")->first();
    if (! $caseToFix) {
        continue; // already has in_exile_since on at least one case
    }
    // Skip if there is a release_date (the booted hook would derive from that)
    if ($caseToFix->release_date) {
        continue;
    }
    $caseToFix->in_exile_since = $today;
    $caseToFix->save();
    $set++;
    echo "set in_exile_since={$today} on {$p->name} (case id={$caseToFix->id}); in_exile_for_days now = {$caseToFix->in_exile_for_days}\n";
}
echo "\nDone. {$set} cases initialized to today.\n";

// Also run the daily scheduler command to refresh everything
echo "\nRunning cases:update-imprisoned-days to refresh all counters...\n";
\Artisan::call("cases:update-imprisoned-days");
echo \Artisan::output();
'

echo
echo "Unknown-exile-date initializer complete."
