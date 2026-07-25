#!/usr/bin/env bash
#
# Diagnostic: for each placement-rule slug that PlaceAllZeroSortPrisoners
# reported as "not found", search the prisoners table by a distinctive name
# fragment and print the ACTUAL id / name / slug / sort_order so the stale
# rule can be corrected. Read-only — changes nothing.
#
#   bash database/data/resolve-missing-placement-slugs.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

// [reported-slug => distinctive surname/fragment to search on]
$missing = [
    "bradford-morris"        => "Morris",
    "anna-mae-aquash"        => "Aquash",
    "james-lawson-jr"        => "Lawson",
    "william-c-rodgers"      => "Rodgers",
    "william-bichsel"        => "Bichsel",
    "ali-mohamed-bagegni"    => "Bagegni",
    "ahmad-mustafa"          => "Mustafa",
    "zuhair-hamed-el-shwehdi"=> "Shwehdi",
    "osameh-al-wahaidy"      => "Wahaidy",
    "ahmadullah-sais-niazi"  => "Niazi",
];

foreach ($missing as $slug => $needle) {
    echo "── rule slug: {$slug}  (searching name LIKE %{$needle}%)\n";
    $hits = Prisoner::withoutGlobalScopes()
        ->where("name", "like", "%{$needle}%")
        ->orWhere("slug", "like", "%{$needle}%")
        ->get(["id", "name", "slug", "sort_order", "under_review"]);
    if ($hits->isEmpty()) {
        echo "     (no match — likely never added, or deleted/merged)\n";
        continue;
    }
    foreach ($hits as $h) {
        $ur = $h->under_review ? " [under_review]" : "";
        echo "     -> {$h->slug}  |  {$h->name}  |  sort={$h->sort_order}{$ur}\n";
    }
}
echo "Done.\n";
'
