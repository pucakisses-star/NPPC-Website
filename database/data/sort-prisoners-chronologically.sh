#!/usr/bin/env bash
#
# Rank the prisoner database sort_order so the /database list reads
# reverse-chronologically by SPECIFIC YEAR (NEWEST first) while keeping every
# case cohort together.
#
# The public list (and the /api/prisoners payload) is ordered purely by
# sort_order. This rewrites it:
#
#   1. Every record gets a real year, not just a decade. The year is the
#      earliest dated event on any of the person's cases (incarceration,
#      arrest, sentencing or release). Only when a person has no dated case at
#      all do we fall back to the decade parsed from their era tag. So a 2026
#      case sorts above a 2023 case sorts above a 2020 case — they no longer
#      collapse into one "2020s" block.
#   2. Case cohorts stay together. A "cohort" is an affiliation shared by 2+
#      people whose years span at most ~20 (a single case/event — Camden 28,
#      Milwaukee 14, the Cop City defendants, the Prairieland defendants, ...),
#      as opposed to a broad multi-era movement (IWW, Catholic Worker). Each
#      person is grouped by the RAREST such cohort they belong to (their most
#      specific case), and the whole cohort is placed in its members majority
#      year — so a member whose own dates differ still sits with the group.
#   3. Years run newest-to-oldest; within a year, cohorts and unaffiliated
#      people keep their existing relative order (a stable sort). Records with
#      no year at all are parked at the very end.
#
# Run tag-cohort-affiliations.sh FIRST so the Cop City and Prairieland cohorts
# carry a group affiliation. Idempotent: re-running reproduces the ranking.
# Run from the repo root:
#   bash database/data/sort-prisoners-chronologically.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$rows = \App\Models\Prisoner::withoutGlobalScopes()
    ->with("cases:id,prisoner_id,arrest_date,sentenced_date,incarceration_date,release_date")
    ->select("id", "era", "affiliation", "sort_order")
    ->orderBy("sort_order")->orderBy("id")->get();

// Pass 1: gather per-record year + affiliation, and per-affiliation stats.
$counts = []; $affYears = []; $recs = [];
foreach ($rows as $i => $p) {
    // Earliest dated event across all of this persons cases.
    $yr = null;
    foreach ($p->cases as $c) {
        foreach (["incarceration_date", "arrest_date", "sentenced_date", "release_date"] as $f) {
            if (! empty($c->{$f})) {
                $yy = (int) \Carbon\Carbon::parse($c->{$f})->year;
                if ($yy && ($yr === null || $yy < $yr)) { $yr = $yy; }
            }
        }
    }
    // Fall back to the decade from the era tag only when there is no dated case.
    if ($yr === null && $p->era && preg_match("/(1[6-9]\d\d|20\d\d)/", (string) $p->era, $m)) {
        $yr = ((int) floor(((int) $m[1]) / 10)) * 10;
    }

    $aff = array_values(array_filter(array_map(fn ($v) => trim((string) $v), (array) $p->affiliation), fn ($v) => $v !== ""));
    $recs[] = ["id" => $p->id, "yr" => $yr, "aff" => $aff, "so" => (int) $p->sort_order, "idx" => $i, "ck" => null, "sy" => null];
    foreach ($aff as $a) {
        $counts[$a] = ($counts[$a] ?? 0) + 1;
        if ($yr !== null) { $affYears[$a][] = $yr; }
    }
}

// Single-event cohorts: 2+ members, year span <= 20. Value = majority year
// (tie -> earliest).
$cohortAff = [];
foreach ($counts as $a => $n) {
    if ($n < 2 || empty($affYears[$a])) { continue; }
    $years = $affYears[$a];
    if (max($years) - min($years) > 20) { continue; }
    $freq = [];
    foreach ($years as $yv) { $freq[$yv] = ($freq[$yv] ?? 0) + 1; }
    $best = null; $bestN = -1;
    foreach ($freq as $yv => $cnt) {
        $yv = (int) $yv;
        if ($cnt > $bestN || ($cnt === $bestN && $yv < $best)) { $best = $yv; $bestN = $cnt; }
    }
    $cohortAff[$a] = $best;
}

// Each record: cohort key = rarest cohort affiliation it carries; sort year =
// that cohorts majority year, else the records own year.
foreach ($recs as &$r) {
    $cands = array_values(array_filter($r["aff"], fn ($a) => isset($cohortAff[$a])));
    if ($cands) {
        usort($cands, fn ($x, $y) => [$counts[$x], $x] <=> [$counts[$y], $y]);
        $r["ck"] = $cands[0];
        $r["sy"] = $cohortAff[$cands[0]];
    } else {
        $r["sy"] = $r["yr"];
    }
}
unset($r);

// Bucket by sort year; newest first, no-year last.
$buckets = [];
foreach ($recs as $r) {
    $bk = $r["sy"] === null ? "~noyear" : sprintf("%04d", $r["sy"]);
    $buckets[$bk][] = $r;
}
$bkeys = array_keys($buckets);
usort($bkeys, function ($a, $b) {
    $na = $a === "~noyear"; $nb = $b === "~noyear";
    if ($na !== $nb) { return $na <=> $nb; }
    if ($na) { return 0; }
    return ((int) $b) <=> ((int) $a);
});

// Within each year bucket, cluster cohorts (anchored at first appearance),
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
    $label = $r["sy"] === null ? "(no year)" : (((int) floor($r["sy"] / 10)) * 10) . "s";
    $decadeCounts[$label] = ($decadeCounts[$label] ?? 0) + 1;
    $i++;
}

echo "Ranked " . count($final) . " prisoners newest-first by year, cohorts grouped; " . count($cohortAff) . " cohorts detected; {$changed} sort_order value(s) changed.\n\n";
echo "Order now runs (grouped by decade for readability, but sorted by exact year within):\n";
foreach ($decadeCounts as $label => $n) { echo "  {$label}: {$n}\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done. Prisoner database re-sorted newest-first by exact year with case cohorts grouped."
