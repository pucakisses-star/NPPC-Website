#!/usr/bin/env bash
#
# Reorder the "Eras" section tabs on the /topics explorer into chronological
# order (oldest to newest). The explorer lists a section's sub-topics by
# their sort_order; this rewrites sort_order on the Eras children to match the
# explicit chronological sequence below.
#
# Each tab is matched by a distinctive keyword (case-insensitive substring),
# so the date ranges in the titles — e.g. "(1850-1861)", with whatever dash
# character — cannot break the match. Any era tab that does not match a known
# keyword is placed after the ordered ones, keeping its existing order, so
# nothing is dropped.
#
# Target order:
#    1. Abolitionism & the Slave Power (1850-1861)
#    2. The Haymarket Affair & the Anti-Anarchist Era (1886-1903)
#    3. The First Red Scare (1917-1920)
#    4. World War II: Japanese Incarceration & the First Smith Act Trials (1941-1945)
#    5. McCarthyism (1947-1957)
#    6. Civil Rights & Black Power
#    7. COINTELPRO (1956-1971)
#    8. The Vietnam War Era (1964-1975)
#    9. The American Indian Movement & Wounded Knee (1973-1977)
#   10. The Reagan Era (1981-1989)
#   11. The Anti-Globalization Movement (1999-2001)
#   12. The War on Terror (2001-)
#   13. The Green Scare (2005-2010)
#   14. Occupy Wall Street (2011-2012)
#   15. Ferguson & the Movement for Black Lives (2014-2016)
#   16. Standing Rock & the #NoDAPL Water Protectors (2016-2017)
#   17. J20: The Inauguration Day Prosecutions (2017)
#   18. The George Floyd Uprising (2020)
#   19. The Stop Cop City Era (2022-)
#   20. The Trump-Era Crackdown on Palestine Solidarity (2024-)
#
# Idempotent: it recomputes and rewrites sort_order each run. Run from the
# repo root:
#   bash database/data/order-era-topics-chronologically.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$eras = \App\Models\Topic::where("slug", "eras")->orWhere("title", "Eras")->whereNull("parent_id")->first();
if (! $eras) { echo "No \"Eras\" section topic found; nothing to do.\n"; return; }

// Ordered distinctive keywords — one per era tab, in chronological order.
// The first keyword contained in a title determines that tab position.
$order = [
    "abolitionism",
    "haymarket",
    "first red scare",
    "world war ii",
    "mccarthy",
    "civil rights",
    "cointelpro",
    "vietnam",
    "american indian movement",
    "reagan",
    "globalization",
    "war on terror",
    "green scare",
    "occupy",
    "ferguson",
    "standing rock",
    "j20",
    "floyd",
    "cop city",
    "palestine",
];

$children = $eras->children()->get();
$ranked = $children->map(function ($t, $i) use ($order) {
    $title = mb_strtolower($t->title);
    $rank = null;
    foreach ($order as $pos => $kw) {
        if (str_contains($title, $kw)) { $rank = $pos; break; }
    }
    return [
        "topic"   => $t,
        "rank"    => $rank ?? 9999,       // unmatched tabs sort to the end
        "orig"    => $t->sort_order ?? $i,
        "matched" => $rank !== null,
    ];
})->sortBy([["rank", "asc"], ["orig", "asc"]])->values();

echo "New order for the Eras tabs:\n";
$i = 0;
foreach ($ranked as $row) {
    $t = $row["topic"];
    if ($t->sort_order !== $i) { $t->sort_order = $i; $t->save(); }
    $tag = $row["matched"] ? "ok" : "UNMATCHED (kept at end)";
    echo "  ".($i + 1).". ".$t->title."  [".$tag."]\n";
    $i++;
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done. ".$ranked->count()." era tab(s) ordered chronologically.\n";
'

echo
echo "Done. Era tabs reordered chronologically."
