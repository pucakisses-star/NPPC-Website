#!/usr/bin/env bash
#
# Retire the Veterans' Rights ideology.
#
# Nine records carry it. NONE is left without an ideology: eight also
# carry Anti-War and the ninth Communism, so every one of them keeps a
# label describing the same politics.
#
# BOTH CASINGS ARE RETIRED in the consolidation command's REMOVE list.
# The corpus carries the title-cased "Veterans' Rights", while the MAP
# funnels "Veterans' organizing" into a lower-cased "Veterans' rights"
# that no record actually uses -- the same casing mismatch already found
# behind Self-Defense, Labor Organizing and the two affiliation
# reversals. Retiring one spelling alone would leave the other free to
# reappear on the next consolidation run.
#
# ORDER IS PRESERVED: the label is filtered out in place, so a record
# reading ["Anti-War", "Veterans' Rights", "Anti-Imperialism"] becomes
# ["Anti-War", "Anti-Imperialism"] rather than being rebuilt.
#
# THE LABEL CONTAINS A STRAIGHT APOSTROPHE, which cannot appear inside
# the single-quoted tinker argument, so it is assembled with chr(39) --
# the same idiom used for Blackwell{39}s Island and the Arecibo
# Women{39}s Jail elsewhere in this directory.
#
# Idempotent: a second run reports zero records to change. Run from the
# repo root:
#   bash database/data/remove-veterans-rights-ideology.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;

$apos   = chr(39);
$retire = ["Veterans".$apos." Rights", "Veterans".$apos." rights", "Veterans".$apos." organizing"];

$people = Prisoner::withoutGlobalScopes()->get(["id", "name", "slug", "ideologies"]);

$changed = 0;
$dropped = 0;
$emptied = [];
$samples = [];

foreach ($people as $p) {
    $ids = $p->ideologies;
    if (! is_array($ids)) {
        $ids = ($ids === null || $ids === "") ? [] : [$ids];
    }

    $new = array_values(array_filter($ids, fn ($i) => ! in_array($i, $retire, true)));
    if (count($new) === count($ids)) {
        continue;
    }

    $changed++;
    $dropped += count($ids) - count($new);
    if (! $new) {
        $emptied[] = $p->slug;
    }
    if (count($samples) < 12) {
        $samples[] = "  ".str_pad($p->slug, 28)." [".implode(", ", $ids)."]  ->  [".implode(", ", $new)."]";
    }

    DB::table("prisoners")->where("id", $p->id)->update(["ideologies" => json_encode($new)]);
}

foreach ($samples as $s) { echo $s."\n"; }
echo "\n";
echo "Changed {$changed} record(s) (expect 9 on the first run); {$dropped} label(s) dropped.\n\n";

foreach ($retire as $label) {
    $n = Prisoner::withoutGlobalScopes()->whereJsonContains("ideologies", $label)->count();
    echo "  ".str_pad($label, 24)." now on {$n} record(s)  (expect 0)\n";
}

if ($emptied) {
    echo "\nRecords now carrying no ideology (listed for the record, no action needed):\n";
    foreach ($emptied as $slug) { echo "  {$slug}\n"; }
} else {
    echo "\nNo record was left without an ideology — every one kept Anti-War or Communism.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
