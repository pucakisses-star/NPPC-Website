#!/usr/bin/env bash
#
# Rank the prisoner database sort_order so the /database list reads
# reverse-chronologically by era (NEWEST first) while keeping every case
# cohort together.
#
# The public list (and the /api/prisoners payload) is ordered purely by
# sort_order. This rewrites it in two nested passes:
#
#   1. Case cohorts stay together. A "cohort" is an affiliation shared by 2+
#      people whose eras span at most ~20 years (a single case/event — Camden
#      28, Milwaukee 14, the Cop City defendants, the Prairieland defendants,
#      ...), as opposed to a broad multi-era movement (IWW, Catholic Worker).
#      Each person is grouped by the RAREST such cohort they belong to (their
#      most specific case), and the whole cohort is placed in its members'
#      majority decade — so a stray member whose era tag differs still sits
#      with the group.
#   2. Decades run newest-to-oldest; within a decade, cohorts and unaffiliated
#      people keep their existing relative order (a stable sort). No-era records
#      are parked at the very end.
#
# Run tag-cohort-affiliations.sh FIRST so the Cop City and Prairieland cohorts
# carry a group affiliation. Idempotent: re-running reproduces the ranking.
# Run from the repo root:
#   bash database/data/sort-prisoners-chronologically.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$rows = \App\Models\Prisoner::withoutGlobalScopes()
    ->select("id", "era", "affiliation", "sort_order")
    ->orderBy("sort_order")->orderBy("id")->get();

// Pass 1: gather per-record decade + affiliation, and per-affiliation stats.
$counts = []; $affDecs = []; $recs = [];
foreach ($rows as $i => $p) {
    $dec = null;
    if ($p->era && preg_match("/(1[6-9]\d\d|20\d\d)/", (string) $p->era, $m)) {
        $dec = ((int) floor(((int) $m[1]) / 10)) * 10;
    }
    $aff = array_values(array_filter(array_map(fn ($v) => trim((string) $v), (array) $p->affiliation), fn ($v) => $v !== ""));
    $recs[] = ["id" => $p->id, "dec" => $dec, "aff" => $aff, "so" => (int) $p->sort_order, "idx" => $i, "ck" => null, "sd" => null];
    foreach ($aff as $a) {
        $counts[$a] = ($counts[$a] ?? 0) + 1;
        if ($dec !== null) { $affDecs[$a][] = $dec; }
    }
}

// Single-era cohorts: 2+ members, era span <= 20y. Value = majority decade.
$cohortAff = [];
foreach ($counts as $a => $n) {
    if ($n < 2 || empty($affDecs[$a])) { continue; }
    $decs = $affDecs[$a];
    if (max($decs) - min($decs) > 20) { continue; }
    $freq = [];
    foreach ($decs as $dv) { $freq[$dv] = ($freq[$dv] ?? 0) + 1; }
    $best = null; $bestN = -1;
    foreach ($freq as $dv => $cnt) {
        $dv = (int) $dv;
        if ($cnt > $bestN || ($cnt === $bestN && $dv < $best)) { $best = $dv; $bestN = $cnt; }
    }
    $cohortAff[$a] = $best;
}

// Each record: cohort key = rarest cohort affiliation it carries; sort decade
// = that cohorts majority decade, else the records own decade.
foreach ($recs as &$r) {
    $cands = array_values(array_filter($r["aff"], fn ($a) => isset($cohortAff[$a])));
    if ($cands) {
        usort($cands, fn ($x, $y) => [$counts[$x], $x] <=> [$counts[$y], $y]);
        $r["ck"] = $cands[0];
        $r["sd"] = $cohortAff[$cands[0]];
    } else {
        $r["sd"] = $r["dec"];
    }
}
unset($r);

// Bucket by sort decade; newest first, no-era last.
$buckets = [];
foreach ($recs as $r) {
    $bk = $r["sd"] === null ? "~noera" : sprintf("%04d", $r["sd"]);
    $buckets[$bk][] = $r;
}
$bkeys = array_keys($buckets);
usort($bkeys, function ($a, $b) {
    $na = $a === "~noera"; $nb = $b === "~noera";
    if ($na !== $nb) { return $na <=> $nb; }
    if ($na) { return 0; }
    return ((int) $b) <=> ((int) $a);
});

// Within each decade bucket, cluster cohorts (anchored at first appearance),
// keeping everything elses current relative order.
$final = [];
foreach ($bkeys as $bk) {
    $bucket = $buckets[$bk];
    $anchor = [];
    foreach ($bucket as $j => $r) {
        if ($r["ck"] !== null && ! isset($anchor[$r["ck"]])) { $anchor[$r["ck"]] = $j; }
    }
    $tuples = [];
    foreach ($bucket as $j => $r) {
        $primary = $r["ck"] !== null ? $anchor[$r["ck"]] : $j;
        $tuples[] = ["p" => $primary, "j" => $j, "r" => $r];
    }
    usort($tuples, fn ($x, $y) => [$x["p"], $x["j"]] <=> [$y["p"], $y["j"]]);
    foreach ($tuples as $t) { $final[] = $t["r"]; }
}

$i = 0; $changed = 0; $decadeCounts = [];
foreach ($final as $r) {
    if ($r["so"] !== $i) {
        \App\Models\Prisoner::withoutGlobalScopes()->where("id", $r["id"])->update(["sort_order" => $i]);
        $changed++;
    }
    $label = $r["sd"] === null ? "(no era)" : ($r["sd"] . "s");
    $decadeCounts[$label] = ($decadeCounts[$label] ?? 0) + 1;
    $i++;
}

echo "Ranked " . count($final) . " prisoners newest-first, cohorts grouped; " . count($cohortAff) . " cohorts detected; {$changed} sort_order value(s) changed.\n\n";
echo "Order now runs:\n";
foreach ($decadeCounts as $label => $n) { echo "  {$label}: {$n}\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done. Prisoner database re-sorted newest-first with case cohorts grouped."
