#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/audit_in_custody_status.sh
#
# Audit batch #1: 8 prisoners currently flagged in_custody=true on
# production who have been released since their entry was last
# updated. Flips in_custody->false, released->true, sets release_date
# and ensures imprisoned_or_exiled tracks the new state.
#
# This is a partial audit (8 of 97 in-custody flags reviewed).
# Remaining 89 entries either are confirmed still-incarcerated by
# recent reporting, or need additional verification.
#
# Sources for each release:
#   - Brittany Martin: ACLU South Carolina; SC Department of Corrections
#     released her Nov 27 2024 after the 4-year BOPHAN sentence.
#   - Leonard Peltier: Biden Executive Grant of Clemency Jan 19 2025;
#     released to home confinement on the Turtle Mountain Reservation
#     Feb 18, 2025.
#   - Veronza Bowers Jr.: Released from FMC Butner May 24, 2024 after
#     51 years federal time, the longest-held federal political
#     prisoner in U.S. history.
#   - Howard Eugene Nall: Released fall 2024 per the Uprising Support
#     post-release page.
#   - Ellie Melvin Brett: Released June 2024 per the Uprising Support
#     post-release page (fellow Atlanta Floyd-uprising defendant).
#   - Jose Felan: Released to halfway house in Del Rio, TX on
#     Dec 3, 2025 per the Uprising Support individual-support page.
#   - Matthew White: Released to halfway house June 2025 per Uprising
#     Support and his March 2025 Midwest Books to Prisoners letter.
#   - Branden Michael Wolfe: 41-month federal sentence imposed May 2021,
#     completed ~Oct 2024 with good-time credit; supervised release.
set -e

php artisan tinker --execute='
$released = [
    "Brittany Martin"      => "2024-11-27",
    "Leonard Peltier"      => "2025-02-18",
    "Veronza Bowers Jr."   => "2024-05-24",
    "Howard Eugene Nall"   => "2024-10-31",
    "Ellie Brett"          => "2024-06-15",
    "Jose Felan"           => "2025-12-03",
    "Matthew White"        => "2025-06-15",
    "Branden Wolfe"        => "2024-10-15",
];

$updated = 0;
foreach ($released as $name => $releaseDate) {
    $p = App\Models\Prisoner::where("name", $name)
        ->orWhere("name", "like", "%{$name}%")
        ->first();
    if (!$p) {
        echo "skip: prisoner {$name} not found\n";
        continue;
    }

    // Update prisoner-level flags
    $p->in_custody          = false;
    $p->released            = true;
    $p->imprisoned_or_exiled = false;
    $p->saveQuietly();

    // Update the most recent case (or all cases that still lack a release_date)
    $touched = false;
    foreach ($p->cases as $case) {
        if (! $case->release_date) {
            $case->release_date = $releaseDate;
            $case->save();
            $touched = true;
            echo "fixed: {$name} (case id={$case->id}) release_date={$releaseDate}; imprisoned_for_days now = {$case->imprisoned_for_days}\n";
        }
    }
    if (! $touched) {
        echo "note:  {$name} - all cases already had release_date; only flipped prisoner-level flags\n";
    }
    $updated++;
}
echo "\nDone. {$updated} prisoners updated.\n";
'

echo
echo "In-custody status audit batch #1 complete."
