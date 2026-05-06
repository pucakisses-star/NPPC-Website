#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/audit_in_custody_apply_all.sh
#
# Consolidated audit-application script for all 17 confirmed releases
# from batches 1-4. The earlier per-batch scripts only set release_date
# on case rows where it was currently null - they skipped cases with an
# existing (often stale or wrong) release_date. This script forces the
# correct release_date onto every case row of each named prisoner so
# the case-detail page displays the released state correctly.
#
# Logic per prisoner:
#   1. Set prisoner-level flags (in_custody=false, released=true,
#      imprisoned_or_exiled=false; for Imam Jamil Al-Amin, also set
#      death_date and leave released=false).
#   2. For every case row attached to that prisoner, set
#      release_date (or death_in_custody_date for Al-Amin) to the
#      confirmed date. The booted hook then recomputes
#      imprisoned_for_days from the corrected end date.
set -e

php artisan tinker --execute='
$confirmedReleases = [
    // Batch 1
    "Brittany Martin"      => "2024-11-27",
    "Leonard Peltier"      => "2025-02-18",
    "Veronza Bowers Jr."   => "2024-05-24",
    "Howard Eugene Nall"   => "2024-10-31",
    "Ellie Brett"          => "2024-06-15",
    "Jose Felan"           => "2025-12-03",
    "Matthew White"        => "2025-06-15",
    "Branden Wolfe"        => "2024-10-15",
    // Batch 2
    "Joshua Williams"      => "2022-12-15",
    "Shamar N. Betts"      => "2023-08-15",
    "Dylan Robinson"       => "2024-06-15",
    "Lauren Handy"         => "2025-01-23",
    "Gage Halupowski"      => "2025-06-15",
    // Batch 3
    "Alex Saab"            => "2023-12-20",
    "Carolyn Feldman"      => "2010-03-19",
    // Batch 4
    "Luke O Donovan"       => "2016-07-25",
    "Sonia Anayibe Rojas"  => "2018-08-17",
];

foreach ($confirmedReleases as $name => $releaseDate) {
    $p = App\Models\Prisoner::where("name", $name)
        ->orWhere("name", "like", "%{$name}%")
        ->first();
    if (!$p) { echo "skip: prisoner {$name} not found\n"; continue; }

    // Prisoner-level flags
    $p->in_custody          = false;
    $p->released            = true;
    $p->imprisoned_or_exiled = false;
    $p->saveQuietly();

    // Force release_date on every case row, overwriting any stale value.
    foreach ($p->cases as $case) {
        $oldRel = $case->release_date?->toDateString() ?? "(null)";
        $case->release_date = $releaseDate;
        $case->save();
        echo "fixed: {$name} (case id={$case->id}) release_date {$oldRel} -> {$releaseDate}; imprisoned_for_days={$case->imprisoned_for_days}\n";
    }
}

echo "\n=== Imam Jamil Al-Amin special handling (died in custody) ===\n";
$alAmin = App\Models\Prisoner::where("name", "like", "%Al-Amin%")
    ->orWhere("name", "like", "%Jamil Abdullah%")
    ->orWhere("name", "like", "%H. Rap Brown%")
    ->get();

foreach ($alAmin as $p) {
    $p->in_custody          = false;
    $p->released            = false;
    $p->death_date          = "2025-11-23";
    $p->age                 = 82;
    $p->imprisoned_or_exiled = false;
    $p->saveQuietly();
    echo "updated prisoner: {$p->name} (id={$p->id}) death_date=2025-11-23\n";
    foreach ($p->cases as $case) {
        $case->death_in_custody_date = "2025-11-23";
        $case->save();
        echo "  case id={$case->id} death_in_custody_date=2025-11-23\n";
    }
}

echo "\nAll done.\n";
'

echo
echo "Consolidated audit application complete."
