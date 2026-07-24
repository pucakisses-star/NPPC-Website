#!/usr/bin/env bash
#
# READ-ONLY probe: for every prisoner case that has an incarceration date but NO
# release date on a prisoner who is NOT currently in custody (the records that now
# show 0 years because imprisoned_for_days has no end to count to), report what
# dating information the record already carries -- sentence text, sentenced_date,
# arrest_date, institution, incarceration year, death_date.
#
# The point is to see whether each case can be capped at its real release time
# from data already recorded (parse the sentence term, or use sentenced_date /
# death_date), instead of either counting to today or showing 0. Modifies nothing.
#
# Run from the repo root:
#   bash database/data/probe-open-case-dating.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$cases = 0; $prisoners = 0;
$withSentence = 0; $withSentencedDate = 0; $withArrest = 0; $withDeath = 0;
$sentenceCounts = [];
$instCounts = [];
$yearCounts = [];

foreach (Prisoner::withoutGlobalScopes()->with(["cases.institution"])->get() as $p) {
    if ($p->in_custody || $p->awaiting_trial) { continue; }
    $touched = false;
    foreach ($p->cases as $c) {
        if (! $c->incarceration_date || $c->release_date) { continue; }
        $cases++; $touched = true;

        $sent = trim((string) $c->sentence);
        if ($sent !== "") {
            $withSentence++;
            $key = strlen($sent) > 60 ? substr($sent, 0, 60)."..." : $sent;
            $sentenceCounts[$key] = ($sentenceCounts[$key] ?? 0) + 1;
        }
        if (! empty($c->sentenced_date)) { $withSentencedDate++; }
        if (! empty($c->arrest_date)) { $withArrest++; }
        if (! empty($p->death_date)) { $withDeath++; }

        $inst = $c->institution?->name ?? "(none)";
        $instCounts[$inst] = ($instCounts[$inst] ?? 0) + 1;

        $yr = substr((string) $c->partialDateIso("incarceration_date"), 0, 4);
        if ($yr !== "") { $yearCounts[$yr] = ($yearCounts[$yr] ?? 0) + 1; }
    }
    if ($touched) { $prisoners++; }
}

echo "Open, not-in-custody cases (now showing 0): {$cases} across {$prisoners} prisoners\n";
echo "  with sentence text:   {$withSentence}\n";
echo "  with sentenced_date:  {$withSentencedDate}\n";
echo "  with arrest_date:     {$withArrest}\n";
echo "  deceased prisoner:    {$withDeath}\n";

arsort($sentenceCounts);
echo "\n--- Distinct sentence values (top 60 by frequency) ---\n";
$i = 0;
foreach ($sentenceCounts as $s => $n) {
    echo str_pad((string) $n, 5, " ", STR_PAD_LEFT)."  ".$s."\n";
    if (++$i >= 60) { break; }
}

arsort($instCounts);
echo "\n--- Institutions (top 40 by case count) ---\n";
$i = 0;
foreach ($instCounts as $inst => $n) {
    echo str_pad((string) $n, 5, " ", STR_PAD_LEFT)."  ".$inst."\n";
    if (++$i >= 40) { break; }
}

ksort($yearCounts);
echo "\n--- Incarceration year distribution ---\n";
foreach ($yearCounts as $yr => $n) {
    echo "  ".$yr.": ".$n."\n";
}

echo "\nRead-only probe -- nothing was modified.\n";
'

echo
echo "Done. Open-case dating probe complete (read-only)."
