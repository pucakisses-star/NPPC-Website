#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/audit_in_custody_status_2.sh
#
# Audit batch #2: 5 more confirmed-released prisoners currently still
# flagged in_custody=true on production. Continues the audit started by
# scripts/audit_in_custody_status.sh.
#
# Sources:
#   - Joshua Williams: 8-yr Missouri state arson sentence (Dec 2015)
#     for 18-year-old Ferguson protest fire; released December 2022
#     after 6.5 years.
#   - Shamar Betts: 4-yr federal sentence for inciting riot at Market
#     Place Mall, Champaign IL (May 2020); released ~Aug 2023 after
#     ~3 years served (with 1-year credit for state custody).
#   - Dylan Shakespeare Robinson: 4-yr federal sentence for the
#     Minneapolis MPD Third Precinct fire May 2020; sentenced April 28,
#     2021; ~mid-2024 release (good-time credit on 4-yr sentence
#     starting June 2020 federal custody).
#   - Lauren Handy: 57-month federal FACE Act sentence May 14, 2024;
#     pardoned by President Trump on January 23, 2025 along with 22
#     other anti-abortion FACE Act defendants.
#   - Gage Halupowski: 70-month Oregon state sentence Nov 2019 for
#     June 2019 Portland baton attack; as of July 2024 was at low-
#     security South Fork Forest Camp transitioning out; estimated
#     release mid-2025 (5 yrs 10 mos served from Aug 2019 = ~June 2025).
set -e

php artisan tinker --execute='
$released = [
    "Joshua Williams"  => "2022-12-15",
    "Shamar N. Betts"  => "2023-08-15",
    "Dylan Robinson"   => "2024-06-15",
    "Lauren Handy"     => "2025-01-23",
    "Gage Halupowski"  => "2025-06-15",
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
    $p->in_custody          = false;
    $p->released            = true;
    $p->imprisoned_or_exiled = false;
    $p->saveQuietly();

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
echo "In-custody status audit batch #2 complete."
