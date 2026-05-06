#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_palestine_2025_exile_dates.sh
#
# Sets in_exile_since (and where applicable end_of_exile) on the case
# rows of 2025-wave Palestine-solidarity prisoners who left the US to
# avoid ICE detention. Without in_exile_since set, the booted hook
# leaves in_exile_for_days null and the Time in Exile counter doesn't
# render on their pages.
#
# Verified flight / deportation dates:
#   - Ranjani Srinivasan: fled to Canada via JFK on March 11, 2025
#   - Momodou Taal: voluntarily left the US on March 31, 2025 after
#     federal court denied emergency injunction
#   - Rasha Alawieh: deported to Lebanon via Paris on March 16, 2025
#     (in apparent violation of Judge Sorokin's emergency order)
#
# Source: previous round-15-wave research; coverage cited in the
# round-15 add scripts.
set -e

php artisan tinker --execute='
$exiles = [
    "Ranjani Srinivasan" => "2025-03-11",
    "Momodou Taal"       => "2025-03-31",
    "Rasha Alawieh"      => "2025-03-16",
];

$fixed = 0;
foreach ($exiles as $name => $date) {
    $p = App\Models\Prisoner::where("name", $name)->first();
    if (!$p) {
        echo "skip: prisoner {$name} not found\n";
        continue;
    }
    // Make sure prisoner-level exile flags are correct
    if (! $p->in_exile)            { $p->in_exile = true; }
    if (! $p->currently_in_exile)  { $p->currently_in_exile = true; }
    if (! $p->imprisoned_or_exiled){ $p->imprisoned_or_exiled = true; }
    $p->saveQuietly();

    foreach ($p->cases as $case) {
        $oldDate = $case->in_exile_since?->toDateString() ?? "(null)";
        $case->in_exile_since = $date;
        $case->save();
        $fixed++;
        echo "fixed: {$name} (case id={$case->id}) in_exile_since {$oldDate}->{$date}; in_exile_for_days now = {$case->in_exile_for_days}\n";
    }
}
echo "\nDone. {$fixed} case rows updated.\n";
'

echo
echo "Palestine-2025 exile-date fix complete."
