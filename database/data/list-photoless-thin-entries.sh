#!/usr/bin/env bash
#
# READ-ONLY: pick 50 random prisoners that have NO photo and whose entries
# still need work, and print their names, gaps and full descriptions so the
# records can be researched and enriched. Changes nothing.
#
# "Needs work" is scored from the gaps in each record:
#   +3 no description at all      +2 description under 200 characters
#   +2 no cases                   +1 case with no dates at all
#   +1 no birthdate               +1 no era        +1 no state
#   +1 no ideologies              +1 no affiliation
# Only records scoring 2 or more are eligible; the 50 are sampled at random
# from that pool (pass a count to change how many).
#
#   bash database/data/list-photoless-thin-entries.sh
#   bash database/data/list-photoless-thin-entries.sh 100 > /tmp/needs-work.txt
set -euo pipefail
cd "$(dirname "$0")/../.."

COUNT="${1:-50}"

COUNT="$COUNT" php artisan tinker --execute='
use App\Models\Prisoner;

$want = (int) (getenv("COUNT") ?: 50);

$pool = [];
Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->whereNull("photo")->orWhere("photo", ""))
    ->with("cases")
    ->chunk(500, function ($chunk) use (&$pool) {
        foreach ($chunk as $p) {
            $desc = trim((string) $p->description);
            $gaps = [];
            $score = 0;

            if ($desc === "") { $score += 3; $gaps[] = "no bio"; }
            elseif (strlen($desc) < 200) { $score += 2; $gaps[] = "bio only ".strlen($desc)." chars"; }

            if ($p->cases->isEmpty()) { $score += 2; $gaps[] = "no cases"; }
            else {
                $undated = $p->cases->filter(fn ($c) => ! $c->arrest_date && ! $c->incarceration_date && ! $c->sentenced_date && ! $c->in_exile_since)->count();
                if ($undated) { $score += 1; $gaps[] = $undated." undated case(s)"; }
            }

            if (! $p->birthdate) { $score += 1; $gaps[] = "no birthdate"; }
            if (! $p->era) { $score += 1; $gaps[] = "no era"; }
            if (! $p->state) { $score += 1; $gaps[] = "no state"; }
            if (! is_array($p->ideologies) || ! $p->ideologies) { $score += 1; $gaps[] = "no ideology"; }
            if (! is_array($p->affiliation) || ! $p->affiliation) { $score += 1; $gaps[] = "no affiliation"; }

            if ($score >= 2) {
                $pool[] = [
                    "name" => $p->name,
                    "slug" => $p->slug,
                    "era" => $p->era ?: "-",
                    "state" => $p->state ?: "-",
                    "score" => $score,
                    "gaps" => implode(", ", $gaps),
                    "desc" => $desc,
                    "cases" => $p->cases->count(),
                ];
            }
        }
    });

$total = count($pool);
shuffle($pool);
$pick = array_slice($pool, 0, $want);
usort($pick, fn ($a, $b) => [$b["score"], $a["name"]] <=> [$a["score"], $b["name"]]);

echo "Photo-less prisoners needing work: {$total} in pool; showing ".count($pick)." at random.\n";
echo str_repeat("=", 78)."\n\n";

$i = 0;
foreach ($pick as $r) {
    $i++;
    echo "{$i}. {$r["name"]}   [{$r["slug"]}]\n";
    echo "   era: {$r["era"]} | state: {$r["state"]} | cases: {$r["cases"]} | needs-work score: {$r["score"]}\n";
    echo "   gaps: {$r["gaps"]}\n";
    echo "   description: ".($r["desc"] !== "" ? $r["desc"] : "(none)")."\n\n";
}

echo "Done.\n";
'
