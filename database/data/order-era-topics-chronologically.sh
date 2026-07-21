#!/usr/bin/env bash
#
# Reorder the "Eras" section tabs on the /topics explorer into chronological
# order. The explorer lists a section's sub-topics by their sort_order; this
# assigns sort_order to the Eras children according to the historical start
# year of each era, so the tabs read oldest-to-newest.
#
# Each era title is matched (case-insensitive substring) against a table of
# known eras and their start years. Any era tab that does not match a known
# keyword is placed after the matched ones, keeping its existing relative
# order, so nothing is ever dropped or hidden.
#
# Idempotent: it simply recomputes and rewrites sort_order each run. Run from
# the repo root:
#   bash database/data/order-era-topics-chronologically.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$eras = \App\Models\Topic::where("slug", "eras")->orWhere("title", "Eras")->whereNull("parent_id")->first();
if (! $eras) { echo "No \"Eras\" section topic found; nothing to do.\n"; return; }

// Ordered keyword => historical start year. First keyword contained in a
// title wins, so more specific phrases are listed before broader ones.
$chronology = [
    "abolition" => 1850, "harpers ferry" => 1859, "john brown" => 1859, "civil war" => 1861,
    "reconstruction" => 1865,
    "haymarket" => 1886, "gilded age" => 1877, "anarchist" => 1886,
    "first red scare" => 1917, "palmer" => 1919, "world war i" => 1917,
    "great depression" => 1930, "labor" => 1934,
    "second red scare" => 1947, "mccarthy" => 1950, "mccarran" => 1950, "red scare" => 1919,
    "civil rights" => 1955, "black power" => 1955, "cointelpro" => 1956,
    "vietnam" => 1964, "anti-war" => 1965, "anti war" => 1965, "draft" => 1965,
    "puerto rican" => 1974,
    "green scare" => 1998, "environmental" => 1998, "animal" => 1998,
    "war on terror" => 2001, "post-9/11" => 2001, "material support" => 2001, "terror" => 2001,
    "occupy" => 2011,
    "ferguson" => 2014, "black lives matter" => 2014, "uprising" => 2020, "george floyd" => 2020, "floyd" => 2020,
    "j20" => 2017, "inauguration" => 2017,
    "cop city" => 2022, "stop cop city" => 2022,
    "palestine" => 2023, "gaza" => 2023,
];

$children = $eras->children()->get();
$ranked = $children->map(function ($t, $i) use ($chronology) {
    $title = mb_strtolower($t->title);
    $year = null;
    foreach ($chronology as $kw => $y) {
        if (str_contains($title, $kw)) { $year = $y; break; }
    }
    return [
        "topic"    => $t,
        "year"     => $year ?? 99999,   // unmatched eras sort to the end
        "orig"     => $t->sort_order ?? $i,
        "matched"  => $year !== null,
    ];
})->sortBy([["year", "asc"], ["orig", "asc"]])->values();

echo "New order for the Eras tabs:\n";
$i = 0;
foreach ($ranked as $row) {
    $t = $row["topic"];
    if ($t->sort_order !== $i) { $t->sort_order = $i; $t->save(); }
    $tag = $row["matched"] ? "~".$row["year"] : "unmatched (kept at end)";
    echo "  ".($i + 1).". ".$t->title."  [".$tag."]\n";
    $i++;
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done. ".$ranked->count()." era tab(s) ordered chronologically.\n";
'

echo
echo "Done. Era tabs reordered chronologically."
