#!/usr/bin/env bash
#
# READ-ONLY: list every prisoner currently at sort_order 0, grouped by
# affiliation and sorted by earliest incarceration year, so placement rules can
# be written for them (via prisoners:place-zero-sort / PlaceAllZeroSortPrisoners).
# Changes nothing.
#
#   bash database/data/list-zero-sort-prisoners.sh
#   # or, to a file:  bash database/data/list-zero-sort-prisoners.sh > /tmp/zero-sort.txt
set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Carbon\Carbon;

$rows = [];
Prisoner::withoutGlobalScopes()->where("sort_order", 0)->with("cases")->chunk(500, function ($chunk) use (&$rows) {
    foreach ($chunk as $p) {
        $year = null;
        foreach ($p->cases as $c) {
            foreach (["incarceration_date", "arrest_date", "sentenced_date", "in_exile_since"] as $f) {
                if ($c->{$f}) {
                    $y = (int) Carbon::parse($c->{$f})->year;
                    if ($y > 1000) { $year = $year ? min($year, $y) : $y; }
                }
            }
        }
        if (! $year && $p->era && preg_match("/\\d{4}/", $p->era, $m)) { $year = (int) $m[0]; }
        $aff = (is_array($p->affiliation) && $p->affiliation) ? $p->affiliation[0] : "(no affiliation)";
        $rows[] = [
            "aff" => $aff,
            "year" => $year ?: 9999,
            "name" => $p->name,
            "slug" => $p->slug,
            "era" => $p->era ?: "-",
            "ideol" => (is_array($p->ideologies) && $p->ideologies) ? implode("/", array_slice($p->ideologies, 0, 2)) : "-",
        ];
    }
});

usort($rows, function ($a, $b) {
    return [$a["aff"], $a["year"], $a["name"]] <=> [$b["aff"], $b["year"], $b["name"]];
});

echo "TOTAL at sort_order 0: ".count($rows)."\n\n";
$curAff = null;
foreach ($rows as $r) {
    if ($r["aff"] !== $curAff) {
        $curAff = $r["aff"];
        $n = count(array_filter($rows, fn ($x) => $x["aff"] === $curAff));
        echo "\n=== ".$curAff." ({$n}) ===\n";
    }
    $yr = $r["year"] === 9999 ? "----" : (string) $r["year"];
    echo "  {$yr}  ".str_pad($r["slug"], 34)." | ".str_pad($r["name"], 28)." | ".$r["era"]." | ".$r["ideol"]."\n";
}
echo "\nDone.\n";
'
