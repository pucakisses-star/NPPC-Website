#!/usr/bin/env bash
#
# Add Ralph Abernathy's additional custody history. His record already holds
# the 1967 Birmingham contempt (5 days) and the 1968 Poor People's Campaign
# (20 days); this adds the other documented arrests/jailings. Per the site
# owner, the December 1961 Albany detention is NOT counted as two days — he
# posted bond that night — so it is recorded as a same-day release.
#
# Idempotent: each case is keyed by arrest_date; existing arrest_dates are
# skipped. Run from the repo root:
#   bash database/data/add-abernathy-cases.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "ralph-abernathy")->first();
if (! $p) { echo "ralph-abernathy not found\n"; return; }

$cases = [
    ["arrest_date"=>"1961-05-25", "charges"=>"Freedom Rides campaign, Montgomery, Alabama", "sentence"=>"Arrested; custody duration unresolved."],
    ["arrest_date"=>"1961-12-16", "incarceration_date"=>"1961-12-16", "release_date"=>"1961-12-16", "imprisoned_for_days"=>0, "charges"=>"Albany Movement protest, Albany, Georgia", "sentence"=>"Released on bond the same night."],
    ["arrest_date"=>"1962-07-10", "incarceration_date"=>"1962-07-10", "release_date"=>"1962-07-12", "imprisoned_for_days"=>2, "charges"=>"Albany Movement protest, Albany, Georgia (July 1962)", "sentence"=>"About two days in jail."],
    ["arrest_date"=>"1963-04-12", "incarceration_date"=>"1963-04-12", "release_date"=>"1963-04-20", "imprisoned_for_days"=>8, "charges"=>"Birmingham campaign — Good Friday demonstration in defiance of the anti-demonstration injunction; jailed with Dr. King, Birmingham, Alabama", "sentence"=>"About eight days in jail."],
    ["arrest_date"=>"1964-06-11", "charges"=>"St. Augustine movement, St. Augustine, Florida", "sentence"=>"Arrested; custody duration unresolved."],
    ["arrest_date"=>"1965-02-01", "incarceration_date"=>"1965-02-01", "release_date"=>"1965-02-05", "imprisoned_for_days"=>4, "charges"=>"Selma voting-rights campaign, Selma, Alabama", "sentence"=>"About four days in jail."],
    ["arrest_date"=>"1969-04-30", "incarceration_date"=>"1969-04-30", "release_date"=>"1969-05-07", "imprisoned_for_days"=>7, "charges"=>"Charleston hospital workers strike, Charleston, South Carolina", "sentence"=>"About seven days in jail."],
];

$added = 0;
foreach ($cases as $data) {
    $ad = $data["arrest_date"];
    if ($p->cases()->whereDate("arrest_date", $ad)->exists()) { echo "  skip $ad (already present)\n"; continue; }
    $data["prisoner_id"] = $p->id;
    \App\Models\PrisonerCase::create($data);
    echo "  added case $ad\n";
    $added++;
}

echo "Added {$added} case(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Ralph Abernathy custody history added."
