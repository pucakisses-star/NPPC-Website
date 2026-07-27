#!/usr/bin/env bash
#
# READ-ONLY: find prisoners whose cases look like duplicates of each other, so
# inflated "Imprisoned for..." counters can be tracked down. The public page
# sums imprisoned_for_days across a prisoner cases, so a duplicated case
# doubles the displayed time (Morrie R. Preston showed 14 years for a 7-year
# sentence this way).
#
# Two cases are flagged as suspected duplicates when they overlap in time:
#   - identical incarceration_date, or
#   - identical release_date, or
#   - their custody ranges overlap AND their charges text is similar
#     (same first 30 characters, case-insensitive).
# Changes nothing.
#
#   bash database/data/audit-duplicate-cases.sh
#   bash database/data/audit-duplicate-cases.sh > /tmp/dupe-cases.txt
set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Carbon\Carbon;

$flagged = 0;
$checked = 0;

Prisoner::withoutGlobalScopes()->with("cases")->chunk(300, function ($chunk) use (&$flagged, &$checked) {
    foreach ($chunk as $p) {
        $checked++;
        $cases = $p->cases->values();
        if ($cases->count() < 2) { continue; }

        $pairs = [];
        for ($i = 0; $i < $cases->count(); $i++) {
            for ($j = $i + 1; $j < $cases->count(); $j++) {
                $a = $cases[$i]; $b = $cases[$j];
                $reason = null;

                if ($a->incarceration_date && $b->incarceration_date
                    && Carbon::parse($a->incarceration_date)->isSameDay(Carbon::parse($b->incarceration_date))) {
                    $reason = "same incarceration date";
                } elseif ($a->release_date && $b->release_date
                    && Carbon::parse($a->release_date)->isSameDay(Carbon::parse($b->release_date))) {
                    $reason = "same release date";
                } else {
                    $aStart = $a->incarceration_date ?: $a->arrest_date;
                    $bStart = $b->incarceration_date ?: $b->arrest_date;
                    if ($aStart && $bStart) {
                        $aEnd = $a->release_date ?: $aStart;
                        $bEnd = $b->release_date ?: $bStart;
                        $overlap = Carbon::parse($aStart)->lte(Carbon::parse($bEnd))
                            && Carbon::parse($bStart)->lte(Carbon::parse($aEnd));
                        $ca = strtolower(substr(trim((string) $a->charges), 0, 30));
                        $cb = strtolower(substr(trim((string) $b->charges), 0, 30));
                        if ($overlap && $ca !== "" && $ca === $cb) { $reason = "overlapping dates + same charges"; }
                    }
                }

                if ($reason) { $pairs[] = [$a, $b, $reason]; }
            }
        }

        if (! $pairs) { continue; }
        $flagged++;
        $total = $cases->sum("imprisoned_for_days");
        echo "\n".$p->name."  [".$p->slug."]  ".$cases->count()." cases, total days={$total}\n";
        foreach ($pairs as [$a, $b, $reason]) {
            echo "  ".$reason."\n";
            foreach ([$a, $b] as $c) {
                echo "    ".substr($c->id, 0, 8)."  inc=".str_pad(($c->incarceration_date ? Carbon::parse($c->incarceration_date)->toDateString() : "-"), 10)
                    ." rel=".str_pad(($c->release_date ? Carbon::parse($c->release_date)->toDateString() : "-"), 10)
                    ." days=".str_pad((string) ($c->imprisoned_for_days ?? "null"), 6)
                    ." inst=".($c->institution_id ? "yes" : "no ")
                    ."  ".substr((string) $c->charges, 0, 50)."\n";
            }
        }
    }
});

echo "\n".str_repeat("=", 70)."\n";
echo "Checked {$checked} prisoners; {$flagged} with suspected duplicate cases.\n";
echo "Done.\n";
'
