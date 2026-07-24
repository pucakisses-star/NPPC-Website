#!/usr/bin/env bash
#
# Apply verified incarceration / release dates (site owner's researched table)
# to existing records. Date precision is preserved: where only a month or year
# is established, that precision is recorded (so the page shows e.g. "May 1937").
#
#   Alger Hiss            1951-03-22 -> 1954-11-27   (USP Lewisburg; perjury)
#   Harry Bridges         1950-08-05 -> 1950-08-25   (SF County Jail; 21 days)
#   Frank Wilkinson       1961-05-01 -> 1962-02-01   (USP Lewisburg; HUAC contempt)
#   Caroline Decker       arr 1934-07-20; 1935-05-02 -> May 1937 (Tehachapi; parole)
#   Louise Todd           Nov 1935 -> 1936-12-19     (Tehachapi)
#   Earl King             1936-08-27 -> 1941-11-28   (San Quentin; paroled)
#   Ernest Ramsay         1936-08-27 -> 1941-11-28   (San Quentin; paroled)
#   Frank Conner          Sep 1936 -> 1941-11-28     (San Quentin; exact arrest day unknown)
#   Festus Coleman        1941-04-12 -> Nov 1951     (San Quentin; released on/before Nov 16 1951)
#   Julius Rosenberg      1950-07-17 -> executed 1953-06-19 (Sing Sing; died in custody)
#   Ethel Rosenberg       1950-08-11 -> executed 1953-06-19 (Sing Sing; died in custody)
#
# Richard Gladstein already carries the verified 1952-04-24 -> 1952-09-23 dates
# and is left unchanged. Qian Xuesen / Tsien Hsue-Shen is not in the database.
#
# Idempotent. Run from the repo root:
#   bash database/data/verified-custody-dates-1930s-50s.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$sd = function ($c, $field, $a) {
    if ($a === null) { return; }
    $c->setPartialDate($field, $a[0], $a[1] ?? null, $a[2] ?? null);
};

// slug => [arrest, incarceration, release, death_in_custody, [instName,instCity,instState]|null]
$entries = [
    ["alger-hiss",       null,            [1951,3,22],  [1954,11,27], null,        null],
    ["harry-bridges",    null,            [1950,8,5],   [1950,8,25],  null,        ["San Francisco County Jail","San Francisco","California"]],
    ["frank-wilkinson",  null,            [1961,5,1],   [1962,2,1],   null,        ["USP Lewisburg","Lewisburg","Pennsylvania"]],
    ["caroline-decker",  [1934,7,20],     [1935,5,2],   [1937,5,null],null,        ["Tehachapi State Prison","Tehachapi","California"]],
    ["louise-todd",      null,            [1935,11,null],[1936,12,19],null,        null],
    ["earl-king",        [1936,8,27],     [1936,8,27],  [1941,11,28], null,        ["San Quentin State Prison","San Quentin","California"]],
    ["ernest-ramsay",    [1936,8,27],     [1936,8,27],  [1941,11,28], null,        ["San Quentin State Prison","San Quentin","California"]],
    ["frank-conner",     null,            [1936,9,null],[1941,11,28], null,        ["San Quentin State Prison","San Quentin","California"]],
    ["festus-coleman",   [1941,4,12],     [1941,4,12],  [1951,11,null],null,       ["San Quentin State Prison","San Quentin","California"]],
    ["julius-rosenberg", [1950,7,17],     [1950,7,17],  null,         [1953,6,19], null],
    ["ethel-rosenberg",  [1950,8,11],     [1950,8,11],  null,         [1953,6,19], null],
];

$done = 0;
foreach ($entries as $e) {
    [$slug, $arr, $inc, $rel, $dic, $inst] = $e;
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "  not found: {$slug}\n"; continue; }

    $c = $p->cases()->first();
    if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; }

    if ($inst !== null) {
        $institution = \App\Models\Institution::firstOrCreate(["name" => $inst[0]], ["city" => $inst[1] ?? null, "state" => $inst[2] ?? null]);
        $c->institution_id = $institution->id;
    }

    $sd($c, "arrest_date", $arr);
    $sd($c, "incarceration_date", $inc);
    if ($dic !== null) {
        $sd($c, "death_in_custody_date", $dic);   // hook mirrors this to release_date
    } else {
        $sd($c, "release_date", $rel);
    }
    $c->save();

    $p->refresh();
    echo "  {$slug}: ".($c->partialDateIso("incarceration_date") ?? "-")." -> ".($c->partialDateIso("release_date") ?? "-")." ({$c->imprisoned_for_days} days)\n";
    $done++;
}

echo "\nUpdated {$done} record(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Verified custody dates applied."
