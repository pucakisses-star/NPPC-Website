#!/usr/bin/env bash
#
# Fill in jail time for the two Cartersville flyer-case defendants from the
# Bartow County (GA) jail roster (jailroster.bc-cville.org). Both were arrested
# April 28, 2023 for posting flyers identifying the trooper who killed
# Tortuguita, and their booking/release dates come straight from the roster:
#
#   - Julia Dupuis      booked 4/28/2023, released 5/16/2023  -> 18 days
#   - Charley Tennenbaum booked 4/28/2023, released 7/10/2023 -> 73 days
#     (roster booking name: TENNENBAUM, CAROLINE HART)
#
# The other Cop City / Defend the Atlanta Forest defendants are NOT in the
# Bartow roster — they were booked in DeKalb County — so this only touches
# these two Bartow cases.
#
# Sets arrest/incarceration/release dates and imprisoned_for_days on each
# person's case (creating a case if none exists). Idempotent. Run from the repo
# root:
#   bash database/data/update-cartersville-custody.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$data = [
    ["julia-dupuis",       2023, 4, 28, 2023, 5, 16, 18],
    ["charley-tennenbaum", 2023, 4, 28, 2023, 7, 10, 73],
];
$updated = 0;
foreach ($data as $d) {
    [$slug, $ay, $am, $ad, $ry, $rm, $rd, $days] = $d;
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "  not found: {$slug}\n"; continue; }
    $c = $p->cases()->first();
    if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; }
    $c->setPartialDate("arrest_date", $ay, $am, $ad);
    $c->setPartialDate("incarceration_date", $ay, $am, $ad);
    $c->setPartialDate("release_date", $ry, $rm, $rd);
    $c->imprisoned_for_days = $days;
    $c->save();
    echo "  {$slug}: {$days} days ({$ay}-{$am}-{$ad} to {$ry}-{$rm}-{$rd})\n";
    $updated++;
}
echo "Updated {$updated} case(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Cartersville custody durations updated."
