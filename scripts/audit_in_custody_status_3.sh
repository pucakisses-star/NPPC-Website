#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/audit_in_custody_status_3.sh
#
# Audit batch #3:
#   - Alex Saab: released Dec 20, 2023 in US-Venezuela prisoner swap
#     (Biden clemency; 10 American detainees + Fat Leonard returned to
#     US in exchange).
#   - Imam Jamil Al-Amin / H. Rap Brown / Jamil Abdullah al-Amin: died
#     Nov 23, 2025 at FMC Butner of Stage 4 cancer at age 82. Production
#     has TWO entries (duplicate) - both need death info set, and one
#     should be merged or noted.
#   - Carolyn Feldman / Carrie Feldman: civil-contempt grand-jury
#     resister, released March 19, 2010 after 4 months. Production
#     entry has contradictory in_custody=true AND released=true flags.
set -e

php artisan tinker --execute='
$cleanReleases = [
    "Alex Saab"          => "2023-12-20",
    "Carolyn Feldman"    => "2010-03-19",
];

foreach ($cleanReleases as $name => $releaseDate) {
    $p = App\Models\Prisoner::where("name", $name)->first();
    if (!$p) {
        echo "skip: prisoner {$name} not found\n";
        continue;
    }
    $p->in_custody          = false;
    $p->released            = true;
    $p->imprisoned_or_exiled = false;
    $p->saveQuietly();

    foreach ($p->cases as $case) {
        if (! $case->release_date) {
            $case->release_date = $releaseDate;
            $case->save();
            echo "fixed: {$name} (case id={$case->id}) release_date={$releaseDate}; imprisoned_for_days now = {$case->imprisoned_for_days}\n";
        }
    }
}

// Imam Jamil Al-Amin: died in custody Nov 23, 2025. Two duplicate entries
// in production - update both, and set death_in_custody_date on cases.
$alAminEntries = App\Models\Prisoner::where("name", "like", "%Al-Amin%")
    ->orWhere("name", "like", "%Jamil Abdullah%")
    ->orWhere("name", "like", "%H. Rap Brown%")
    ->get();

foreach ($alAminEntries as $p) {
    $p->in_custody          = false;
    $p->released            = false;
    $p->death_date          = "2025-11-23";
    $p->age                 = 82;
    $p->imprisoned_or_exiled = false;
    $p->saveQuietly();
    echo "updated prisoner: {$p->name} (id={$p->id}) death_date=2025-11-23\n";

    foreach ($p->cases as $case) {
        if (! $case->death_in_custody_date) {
            $case->death_in_custody_date = "2025-11-23";
            $case->save();
            echo "  case id={$case->id} death_in_custody_date=2025-11-23; imprisoned_for_days={$case->imprisoned_for_days}\n";
        }
    }
}
echo "\nBatch 3 done.\n";
'

echo
echo "In-custody status audit batch #3 complete."
