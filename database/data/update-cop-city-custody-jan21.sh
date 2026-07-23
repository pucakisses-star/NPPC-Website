#!/usr/bin/env bash
#
# Fill in jail time for three Cop City / Defend the Atlanta Forest defendants
# arrested January 21, 2023, from documented booking/release info:
#
#   - Graham Evatt   incarcerated 1/21/2023, released ~1/26/2023   -> ~5 days
#   - Ivan Ferguson  incarcerated 1/21/2023, released 2/15/2023    -> 25 days
#   - Emily Murphy   incarcerated 1/21/2023, released 5/4/2023     -> 103 days
#
# Sets incarceration/arrest/release dates and imprisoned_for_days on each
# person's existing case. Idempotent. Run from the repo root:
#   bash database/data/update-cop-city-custody-jan21.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$data = [
    ["graham-evatt",  2023, 1, 21, 2023, 1, 26, 5],
    ["ivan-ferguson", 2023, 1, 21, 2023, 2, 15, 25],
    ["emily-murphy",  2023, 1, 21, 2023, 5, 4, 103],
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
echo "Done. Jan 21 Cop City custody durations updated."
