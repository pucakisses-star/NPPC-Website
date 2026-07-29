#!/usr/bin/env bash
#
# Combine the two Industrial Workers of the World affiliations.
#
#   Industrial Workers of the World (IWW)   606 records   <- canonical
#   Industrial Workers of the World          10 records   <- folds in
#
# Two records carry BOTH spellings; they end up with one, in the
# position the first of the two already held, rather than the label
# twice.
#
# ORDER IS PRESERVED. The label is substituted in place, so a record
# reading ["Industrial Workers of the World", "Socialist Party of
# America"] becomes ["Industrial Workers of the World (IWW)",
# "Socialist Party of America"] and nothing jumps to the end of the list.
#
# THE MAP IN ConsolidateAffiliations ALREADY POINTED THE RIGHT WAY for
# this pair, so the labels had simply drifted apart again since it was
# last run; this script applies the merge directly rather than running
# the whole 117-entry consolidation, which would also touch records
# nobody asked about.
#
# TWO BACKWARDS ENTRIES IN THAT MAP ARE FIXED in the same change. It
# carried:
#
#   'Black Liberation Movement'  => 'Black liberation movement'
#   'Animal Liberation Movement' => 'Animal liberation movement'
#
# which is upside down: the title-cased spellings are the ones the
# corpus uses (18 and 4 records), and the lower-cased targets are used
# by NOBODY. Running the consolidation would have emptied two good
# labels and invented two new ones. Both are reversed at the source so
# that can no longer happen. This script does not rewrite those records
# -- they are already correct -- it only stops the command from
# breaking them later.
#
# 'Workers (Communist) Party' => 'Communist Party USA' (1 record) is
# left alone: it is a correct mapping, but it is not what was asked for
# here and it can go in whenever the full consolidation is run.
#
# Idempotent: a second run reports zero records to change. Run from the
# repo root:
#   bash database/data/merge-iww-affiliation.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;

$from = "Industrial Workers of the World";
$to   = "Industrial Workers of the World (IWW)";

$people = Prisoner::withoutGlobalScopes()->get(["id", "name", "slug", "affiliation"]);

$converted = 0;
$deduped   = 0;
$samples   = [];

foreach ($people as $p) {
    $aff = $p->affiliation;
    if (! is_array($aff)) {
        $aff = ($aff === null || $aff === "") ? [] : [$aff];
    }
    if (! in_array($from, $aff, true)) {
        continue;
    }

    $hadTarget = in_array($to, $aff, true);

    $new = [];
    foreach ($aff as $a) {
        $label = $a === $from ? $to : $a;
        if (! in_array($label, $new, true)) {
            $new[] = $label;
        }
    }

    if ($hadTarget) { $deduped++; } else { $converted++; }
    if (count($samples) < 10) {
        $samples[] = "  ".str_pad($p->slug, 30)." [".implode(", ", $aff)."]  ->  [".implode(", ", $new)."]";
    }

    DB::table("prisoners")->where("id", $p->id)->update(["affiliation" => json_encode($new)]);
}

foreach ($samples as $s) { echo $s."\n"; }
echo "\n";
echo "Converted {$converted} record(s) to the (IWW) form.\n";
echo "Collapsed a duplicate on {$deduped} record(s) that already carried both.\n";

$remaining = Prisoner::withoutGlobalScopes()->whereJsonContains("affiliation", $from)->count();
$total     = Prisoner::withoutGlobalScopes()->whereJsonContains("affiliation", $to)->count();
echo "\nBare \"Industrial Workers of the World\" now on {$remaining} record(s) (expect 0).\n";
echo "\"Industrial Workers of the World (IWW)\" now on {$total} record(s) (expect 614: 606 + the 8 that did not already carry it).\n";

foreach (["Black Liberation Movement", "Animal Liberation Movement"] as $label) {
    $n = Prisoner::withoutGlobalScopes()->whereJsonContains("affiliation", $label)->count();
    echo "  {$label}: {$n} record(s) — untouched, and the map that would have lower-cased it is fixed.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
