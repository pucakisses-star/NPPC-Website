#!/usr/bin/env bash
#
# Two removals, at the site owner's direction.
#
# 1. DELETE Manuel Esteban Paez Terán (Tortuguita)
#    The forest defender killed by Georgia State Patrol officers during the
#    January 18, 2023 raid on the Weelaunee Forest encampment. The record
#    shows why it is being removed rather than corrected: its one case has no
#    arrest date and no incarceration date -- only a release date equal to the
#    date of death -- because Tortuguita was never in custody. Killed, not
#    imprisoned; on the same footing as Sid Hatfield and Ed Chambers, removed
#    earlier for the same reason.
#
#    The record is printed in full first, and any calendar entries pointing at
#    the record are unlinked and reported (the calendar table has no cascade,
#    so they would otherwise keep a dangling prisoner_id). Podcast episodes
#    detach themselves via their own set-null foreign key.
#
#    REVIEW=1 hides the record (under_review = true) instead of deleting --
#    reversible from the admin.
#
# 2. REMOVE "Anti-Feudalism" as an ideology
#    Four records carry it, all from the 1840s New York Anti-Rent War, and all
#    four also carry "Tenant Rights", so nobody is left with no ideology:
#
#      Edward O'Connor, John Van Steenburgh, Moses Earle, Smith A. Boughton
#
#    The tag is dropped from the array; Tenant Rights stays.
#
# Idempotent. Run from the repo root:
#   bash database/data/remove-tortuguita-and-anti-feudalism.sh
#   REVIEW=1 bash database/data/remove-tortuguita-and-anti-feudalism.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

REVIEW="${REVIEW:-0}" php artisan tinker --execute='
use App\Models\CalendarEntry;
use App\Models\Prisoner;

$review = getenv("REVIEW") === "1";

// ---- 1. Tortuguita ---------------------------------------------------------
$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("slug", "manuel-esteban-paez-teran")
        ->orWhereRaw("LOWER(name) = ?", ["manuel esteban paez terán"])
        ->orWhere("aka", "like", "%Tortuguita%"))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) {
    echo "Not found: Tortuguita / Manuel Esteban Paez Terán (already removed?)\n";
} elseif ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
} else {
    $p = $matches->first();
    echo "{$p->name}  [{$p->slug}]  sort={$p->sort_order}  cases=".$p->cases->count()."\n";
    echo "  state: ".($p->state ?: "-")."  era: ".($p->era ?: "-")."  died: ".($p->death_date ? $p->death_date->toDateString() : "-")."\n";
    echo "  photo: ".($p->photo ?: "(none)")."\n";
    foreach ($p->cases as $c) {
        echo "  case  arr=".($c->arrest_date ? $c->arrest_date->toDateString() : "-")
            ."  inc=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
            ."  rel=".($c->release_date ? $c->release_date->toDateString() : "-")
            ."  -- no custody dates: killed, not imprisoned\n";
    }

    $cal = CalendarEntry::where("prisoner_id", $p->id)->get();
    foreach ($cal as $entry) {
        $entry->prisoner_id = null;
        $entry->save();
    }
    if ($cal->count()) {
        echo "  unlinked ".$cal->count()." calendar entrie(s) that pointed at the record\n";
    }

    if ($review) {
        $p->under_review = true;
        $p->save();
        echo "\nHidden from the public site (under_review = true). Data kept; reverse it in the admin.\n";
    } else {
        $n = $p->cases()->count();
        $p->delete();
        echo "\nDeleted the record and its {$n} case(s).\n";
    }
}

// ---- 2. Anti-Feudalism -----------------------------------------------------
echo "\n---- removing the Anti-Feudalism ideology ----\n";
$carriers = Prisoner::withoutGlobalScopes()
    ->where("ideologies", "like", "%Feudal%")
    ->get();

if ($carriers->isEmpty()) {
    echo "No record carries it (already removed?)\n";
}

$emptied = 0;
foreach ($carriers as $c) {
    $before = is_array($c->ideologies) ? $c->ideologies : [];
    $kept = array_values(array_filter(
        $before,
        fn ($v) => strtolower(trim((string) $v)) !== "anti-feudalism",
    ));
    if ($kept === $before) { continue; }

    $c->ideologies = $kept;
    $c->save();
    echo "  {$c->slug}: ".implode(", ", $before)."  ->  ".(implode(", ", $kept) ?: "(none)")."\n";
    if (! $kept) { $emptied++; }
}
if ($emptied) {
    echo "WARNING: {$emptied} record(s) left with no ideology at all.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
