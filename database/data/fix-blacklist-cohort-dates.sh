#!/usr/bin/env bash
#
# Correct verified birth/death date transcription errors across the McCarthy-era
# / Communist / Hollywood-blacklist / labor cohort. Each date below was checked
# against authoritative public sources (Wikipedia, obituaries, ALBA, Find A
# Grave, Baltimore Sun, People's World, etc.); only clear, sourced discrepancies
# are changed here. Records whose dates merely lack an exact day (year/month
# placeholders that are otherwise correct) are left untouched, as are records
# with unresolved identity questions.
#
# Precision is preserved: month-only corrections set month precision, year-only
# corrections set year precision (so the site shows "April 1982", not a fake day).
#
# Bill Dunne: the stored death date (1953-09-23) was spurious — he is a living
# anarchist political prisoner (birth 1954-08-03 is correct). The bogus death
# date is cleared. His custody status is NOT changed here (out of scope for a
# date-correction pass); review separately if needed.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-blacklist-cohort-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
// [slug, birth [y,m,d] or null=leave, death [y,m,d] or null=leave]
// Use null for a missing month/day part to record coarser precision.
$fixes = [
    ["grace-carlson",          [1906, 11, 13], [1992, 7, 7]],
    ["louis-weinstock",        null,           [1994, 11, 26]],
    ["olen-montgomery",        null,           [1959, 3, 9]],
    ["steve-nelson",           null,           [1993, 12, 11]],
    ["leon-josephson",         [1898, 6, 17],  [1966, 2, null]],
    ["louis-vitale",           null,           [2023, 9, 6]],
    ["max-geldman",            null,           [1989, 12, 2]],
    ["robert-g-thompson",      [1915, 6, 21],  [1965, 10, 16]],
    ["simon-w-gerson",         [1909, 1, 23],  [2004, 12, 26]],
    ["william-weinstone",      [1897, 12, 15], [1985, 10, 22]],
    ["edward-k-barsky",        [1895, 6, 6],   null],
    ["v-j-jerome",             [1896, 10, 12], null],
    ["carl-braden",            [1914, 6, 24],  null],
    ["carl-winter",            null,           [1991, 11, 16]],
    ["saul-wellman",           [1913, 8, 18],  [2003, 12, 11]],
    ["maurice-braverman",      [1916, 2, 1],   [2002, 3, 25]],
    ["barrows-dunham",         null,           [1995, 11, 19]],
    ["george-pettibone",       null,           [1908, 8, 3]],
    ["alexander-trachtenberg", [1884, 11, 23], [1966, 12, 26]],
    ["betty-gannett",          [1906, null, null], [1970, 3, 4]],
    ["ben-gold",               null,           [1985, 7, 24]],
    ["alexander-bittelman",    null,           [1982, 4, null]],
];

$done = 0;
foreach ($fixes as $f) {
    [$slug, $b, $d] = $f;
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "  not found: {$slug}\n"; continue; }
    if ($b !== null) { $p->setPartialDate("birthdate", $b[0], $b[1] ?? null, $b[2] ?? null); }
    if ($d !== null) { $p->setPartialDate("death_date", $d[0], $d[1] ?? null, $d[2] ?? null); }
    $p->save();
    echo "  {$slug}: born ".($p->partialDateIso("birthdate") ?? "-").", died ".($p->partialDateIso("death_date") ?? "-")."\n";
    $done++;
}

// Bill Dunne: clear the spurious death date only (he is living; birth is correct).
$bd = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "bill-dunne")->first();
if ($bd) {
    $bd->setPartialDate("death_date", null);
    $bd->save();
    echo "  bill-dunne: cleared spurious death date (born ".($bd->partialDateIso("birthdate") ?? "-").", living)\n";
    $done++;
} else {
    echo "  not found: bill-dunne\n";
}

echo "\nCorrected {$done} record(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Blacklist/HUAC cohort date corrections applied."
