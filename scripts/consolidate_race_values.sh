#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/consolidate_race_values.sh
#
# Consolidates prisoner race values to the canonical six:
#   Asian, White, Black, Arab, Latino/Hispanic, Native American
#
# Mappings:
#   Asian American, Asian / South Asian, Asian/South Asian,
#     South Asian, Asian (already)        -> Asian
#   Middle Eastern / North African,
#     Middle Eastern/North African        -> Arab
#   American Indian / Alaska Native,
#     American Indian/Alaska Native       -> Native American
#   Hispanic                              -> Latino/Hispanic
#   Biracial / Multiracial                -> Black (per user)
#   Unknown                               -> printed for review,
#                                            defaulted to White
set -e

php artisan tinker --execute='
$mappings = [
    "Asian American"                  => "Asian",
    "Asian / South Asian"             => "Asian",
    "Asian/South Asian"               => "Asian",
    "South Asian"                     => "Asian",
    "Middle Eastern / North African"  => "Arab",
    "Middle Eastern/North African"    => "Arab",
    "American Indian / Alaska Native" => "Native American",
    "American Indian/Alaska Native"   => "Native American",
    "Hispanic"                        => "Latino/Hispanic",
    "Biracial / Multiracial"          => "Black",
    "Biracial/Multiracial"            => "Black",
];

$total = 0;
foreach ($mappings as $from => $to) {
    $count = App\Models\Prisoner::where("race", $from)->count();
    if ($count === 0) {
        echo "  (none) {$from}\n";
        continue;
    }
    App\Models\Prisoner::where("race", $from)->update(["race" => $to]);
    echo "  {$count} updated: {$from} -> {$to}\n";
    $total += $count;
}

echo "\nUnknown entries (to be defaulted to White; review names below):\n";
$unknownPrisoners = App\Models\Prisoner::where("race", "Unknown")->get(["id","name"]);
foreach ($unknownPrisoners as $p) {
    echo "  - {$p->name} (id={$p->id})\n";
}
$unknownCount = App\Models\Prisoner::where("race", "Unknown")->update(["race" => "White"]);
echo "  {$unknownCount} updated: Unknown -> White\n";
$total += $unknownCount;

echo "\nFinal race counts:\n";
$rows = App\Models\Prisoner::select("race", \DB::raw("count(*) as c"))
    ->groupBy("race")
    ->orderByDesc("c")
    ->get();
foreach ($rows as $r) {
    echo "  " . str_pad($r->race ?? "(null)", 30) . $r->c . "\n";
}
echo "\nTotal records updated: {$total}\n";
'

echo
echo "Race-value consolidation complete."
