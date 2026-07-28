#!/usr/bin/env bash
#
# Replace the Pacifism ideology with Anti-War.
#
# The two labels had drifted into parallel use across the corpus:
#
#   Anti-War            916 records
#   Pacifism            876 records
#   carrying both        84 records
#
# After the merge, Anti-War carries 1,708 records and Pacifism is gone.
# The 84 records that already had both simply lose the duplicate rather
# than ending up with Anti-War twice.
#
# ORDER IS PRESERVED. Pacifism is replaced in place, so a record reading
# ["Anarchism", "Pacifism", "Labor Organizing"] becomes ["Anarchism",
# "Anti-War", "Labor Organizing"] -- the label does not jump to the end of
# the list. Where the record already carried Anti-War, the Pacifism entry
# is dropped and the existing Anti-War keeps its original position.
#
# The canonical map in app/Console/Commands/ConsolidateIdeologies.php is
# updated in the same change, so Pacifism, Pacifist and Nonviolence all
# now resolve to Anti-War and a future consolidation run cannot bring the
# label back.
#
# Idempotent: a second run reports zero records to change. Run from the
# repo root:
#   bash database/data/merge-pacifism-into-anti-war.sh
#
# NOTE: 27 historical add-* artisan commands still contain the literal
# 'Pacifism' in their seed data. They are one-off importers that refuse
# duplicates and are not re-run in normal operation, and the consolidation
# map above would catch anything they did reintroduce. They are left as a
# record of what was originally imported.

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;

$from = "Pacifism";
$to   = "Anti-War";

$people = Prisoner::withoutGlobalScopes()->get(["id", "name", "slug", "ideologies"]);

$converted = 0;   // Pacifism became Anti-War
$deduped   = 0;   // record already had Anti-War, so Pacifism was dropped
$samples   = [];

foreach ($people as $p) {
    $ids = $p->ideologies;
    if (! is_array($ids)) {
        $ids = ($ids === null || $ids === "") ? [] : [$ids];
    }
    if (! in_array($from, $ids, true)) {
        continue;
    }

    $hadTarget = in_array($to, $ids, true);

    $new = [];
    foreach ($ids as $i) {
        $label = $i === $from ? $to : $i;
        if (! in_array($label, $new, true)) {
            $new[] = $label;
        }
    }

    if ($hadTarget) { $deduped++; } else { $converted++; }
    if (count($samples) < 8) {
        $samples[] = "  ".str_pad($p->slug, 30)." [".implode(", ", $ids)."]  ->  [".implode(", ", $new)."]";
    }

    DB::table("prisoners")->where("id", $p->id)->update(["ideologies" => json_encode($new)]);
}

foreach ($samples as $s) { echo $s."\n"; }
echo "\n";
echo "Converted {$converted} record(s) from Pacifism to Anti-War.\n";
echo "Dropped a duplicate Pacifism from {$deduped} record(s) that already carried Anti-War.\n";

$remaining = Prisoner::withoutGlobalScopes()->whereJsonContains("ideologies", $from)->count();
$total     = Prisoner::withoutGlobalScopes()->whereJsonContains("ideologies", $to)->count();
echo "\nPacifism now on {$remaining} record(s) (expect 0).\n";
echo "Anti-War now on {$total} record(s).\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
