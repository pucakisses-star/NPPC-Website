#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_prairieland_dates.sh
#
# Sets arrest_date and incarceration_date on the six Prairieland defendants
# currently in the database whose case rows were missing both fields.
#
# The Prairieland case: 9 federal defendants and 7 cooperators tried for
# the July 4, 2025 attack on the Prairieland ICE Detention Center in
# Alvarado, Texas, in which fireworks were used to lure correctional
# officers and Alvarado police outside, then a gunman fired on them
# (Officer Lt. Thomas Gross was wounded, no fatalities). Per Wikipedia
# and KERA News reporting, "ten people were arrested that night [July 4],
# and another the following morning" - so the initial-10 arrest date is
# July 4, 2025. Benjamin Song was the 11th, captured separately in Dallas
# on July 15, 2025 after an 11-day manhunt.
#
# All defendants have been held in federal pretrial detention since
# arrest; federal jury verdict came on March 13, 2026 (Northern District
# of Texas, Fort Worth) with sentencing set for June 18, 2026.
#
# Source: 2025 Prairieland ICE detention center incident (Wikipedia);
# KERA News reporting; FBI/DHS arrest releases.
set -e

php artisan tinker --execute='
$arrests = [
    "Benjamin Song"   => "2025-07-15",  // 11-day manhunt; FBI captured in Dallas
    "Elizabeth Soto"  => "2025-07-04",  // initial 10 arrested night of attack
    "Ines Soto"       => "2025-07-04",
    "Maricela Rueda"  => "2025-07-04",
    "Savanna Batten"  => "2025-07-04",
    "Zachary Evetts"  => "2025-07-04",
];

$fixed = 0;
foreach ($arrests as $name => $date) {
    $p = App\Models\Prisoner::where("name", $name)->first();
    if (!$p) {
        echo "skip: prisoner {$name} not found\n";
        continue;
    }
    foreach ($p->cases as $case) {
        if (! str_contains((string) $case->charges, "Prairieland") &&
            ! str_contains((string) $case->charges, "Alvarado") &&
            ! str_contains((string) $case->charges, "ICE Detention")) {
            // Skip non-Prairieland cases just in case
            continue;
        }
        $oldArr = $case->arrest_date?->toDateString() ?? "(null)";
        $oldInc = $case->incarceration_date?->toDateString() ?? "(null)";
        $case->arrest_date        = $date;
        $case->incarceration_date = $date;
        $case->save();
        $fixed++;
        echo "fixed: {$name} (case id={$case->id}) arrest_date {$oldArr}->{$date}, incarceration_date {$oldInc}->{$date}; imprisoned_for_days now = {$case->imprisoned_for_days}\n";
    }
}
echo "\nDone. {$fixed} case rows updated.\n";
'

echo
echo "Prairieland incarceration-date fix complete."
