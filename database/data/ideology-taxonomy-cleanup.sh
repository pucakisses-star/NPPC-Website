#!/usr/bin/env bash
#
# Ideology taxonomy cleanup: retire six labels, merge two into
# Labor Organizing. 86 records change.
#
# RETIRED -- dropped outright, not merged into anything, the same
# treatment the taxonomy already gives Economic justice, Democracy and
# the other retired labels in the REMOVE list of
# app/Console/Commands/ConsolidateIdeologies.php:
#
#   Self-Defense                14 records
#   New Left                     7 records
#   Black Armed Self-Defense     6 records
#   Black Liberation Theology    4 records
#   Anti-Globalization           4 records
#   Anti-Poverty                 3 records
#                               --
#                               38 labels dropped
#
# BOTH CASINGS OF SELF-DEFENSE ARE RETIRED. The corpus carries the
# capitalised "Self-Defense", while the MAP in the consolidation command
# funnels four variants (Anti-racist self-defense, Self-defense against
# racist violence, Community self-defense, Armed self-defense) into the
# lower-case "Self-defense". Retiring only one spelling would leave the
# other free to reappear on the next consolidation run.
#
# MERGED into Labor Organizing:
#
#   Black Southern Labor Organizing   47 records
#   Labor organizing                   1 record
#                                     --
#                                     48 labels merged; the label goes
#                                     from 816 records to 864
#
# BLACK SOUTHERN LABOR ORGANIZING IS MERGED, NOT RETIRED. It was the
# ONLY ideology on all 47 records that carried it -- the 1910s Arkansas
# sharecroppers organizing for the Progressive Farmers and Household
# Union, including the Elaine defendants -- so dropping it outright
# would have left forty-seven people with no politics recorded at all.
# The generic label is exactly what they were doing, so it takes them.
#
# THE CASING DUPLICATE. The corpus settled on the title-cased spelling,
# 816 records to one, so the single lower-case straggler folds into it
# rather than the other way round. The same mismatch as the self-defense
# one was hiding here: eight variants in the MAP (Labor Activism, Labor
# activism, Labor rights, Working-class organizing, UMWA, Knights of
# Labor, WFM, Labor) pointed at the LOWER-case spelling, so a
# consolidation run would have recreated the duplicate the moment it
# touched any of them. Those targets are retargeted in the same change
# and both merged variants are added to the MAP. No record carries two
# of the merged spellings, so nothing needs de-duplicating.
#
# ORDER IS PRESERVED throughout. Labels are filtered or substituted in
# place, so a record reading ["Black Nationalism", "Black Armed
# Self-Defense"] becomes ["Black Nationalism"], and a merged label keeps
# its original position rather than jumping to the end of the list.
#
# Nine records end with an empty ideology list, because a retired label
# was the only one they carried. That is a normal state in this corpus
# and needs no action; the script lists them at the end purely as a
# record of what changed.
#
# Idempotent: a second run reports zero records to change. Run from the
# repo root:
#   bash database/data/ideology-taxonomy-cleanup.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;

$retire = ["Anti-Poverty", "Self-Defense", "Self-defense", "Black Armed Self-Defense", "Black Liberation Theology", "Anti-Globalization", "New Left"];
$merge  = ["Labor organizing" => "Labor Organizing", "Black Southern Labor Organizing" => "Labor Organizing"];

$people = Prisoner::withoutGlobalScopes()->get(["id", "name", "slug", "ideologies"]);

$changed = 0;
$dropped = 0;
$merged  = 0;
$deduped = 0;
$emptied = [];
$samples = [];

foreach ($people as $p) {
    $ids = $p->ideologies;
    if (! is_array($ids)) {
        $ids = ($ids === null || $ids === "") ? [] : [$ids];
    }

    $new = [];
    $thisDropped = 0;
    $thisMerged  = 0;
    $thisDeduped = 0;

    foreach ($ids as $i) {
        if (in_array($i, $retire, true)) {
            $thisDropped++;

            continue;
        }
        $label = $merge[$i] ?? $i;
        if ($label !== $i) {
            $thisMerged++;
        }
        if (in_array($label, $new, true)) {
            $thisDeduped++;

            continue;
        }
        $new[] = $label;
    }

    if (! $thisDropped && ! $thisMerged && ! $thisDeduped) {
        continue;
    }

    $changed++;
    $dropped += $thisDropped;
    $merged  += $thisMerged;
    $deduped += $thisDeduped;
    if (! $new) {
        $emptied[] = $p->slug;
    }
    if (count($samples) < 14) {
        $samples[] = "  ".str_pad($p->slug, 26)." [".implode(", ", $ids)."]  ->  [".implode(", ", $new)."]";
    }

    DB::table("prisoners")->where("id", $p->id)->update(["ideologies" => json_encode($new)]);
}

foreach ($samples as $s) { echo $s."\n"; }
echo "\n";
echo "Changed {$changed} record(s) (expect 86 on the first run).\n";
echo "  {$dropped} label(s) dropped, {$merged} merged, {$deduped} duplicate(s) collapsed.\n\n";

foreach ($retire as $label) {
    $n = Prisoner::withoutGlobalScopes()->whereJsonContains("ideologies", $label)->count();
    echo "  retired  ".str_pad($label, 26)." now on {$n} record(s)  (expect 0)\n";
}
foreach ($merge as $from => $to) {
    $n = Prisoner::withoutGlobalScopes()->whereJsonContains("ideologies", $from)->count();
    $t = Prisoner::withoutGlobalScopes()->whereJsonContains("ideologies", $to)->count();
    echo "  merged   ".str_pad($from, 26)." now on {$n} record(s)  (expect 0); {$to} now on {$t}\n";
}

if ($emptied) {
    echo "\nRecords now carrying no ideology (a retired label was their only one) -- listed for the record, no action needed:\n";
    foreach ($emptied as $slug) { echo "  {$slug}\n"; }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
