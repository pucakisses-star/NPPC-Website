#!/usr/bin/env bash
#
# Remove the Denmark Vesey Conspiracy records.
#
# Five records carry the "Denmark Vesey Conspiracy" affiliation, all lieutenants
# in the 1822 Charleston plot:
#
#   Gullah Jack      Angolan-born obeah; hanged
#   Ned Bennett      enslaved by Governor Bennett; hanged July 2, 1822
#   Peter Poyas      ship carpenter, Vesey’s top lieutenant; hanged June 1822
#   Rolla Bennett    hanged July 2, 1822
#   Monday Gell      harness-maker; death sentence commuted to transportation
#
# None carries an incarceration or release date. Four were executed and the
# fifth was transported out of South Carolina -- the outcome in every case was
# death or banishment rather than a prison term, which is the same ground on
# which Sid Hatfield, Ed Chambers and Tortuguita were removed.
#
# Selection is by AFFILIATION, which is exact here -- all five carry it and
# nothing else does. Every record is printed in full first, with its case and
# sentence, so the output is a receipt of what was removed. The script reports
# loudly if the count is not the five verified against the live database, in
# case the tag has since been applied more widely.
#
# Calendar entries pointing at any of the records are unlinked first: that
# table has no cascade and would otherwise keep a dangling prisoner_id.
# Podcast episodes detach themselves via their own set-null foreign key.
#
# Deletes by default. REVIEW=1 hides them instead (under_review = true), which
# keeps the data and is reversible from the admin.
#
#   bash database/data/remove-denmark-vesey-conspiracy.sh
#   REVIEW=1 bash database/data/remove-denmark-vesey-conspiracy.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

REVIEW="${REVIEW:-0}" php artisan tinker --execute='
use App\Models\CalendarEntry;
use App\Models\Prisoner;

$review = getenv("REVIEW") === "1";

$people = Prisoner::withoutGlobalScopes()
    ->where("affiliation", "like", "%Denmark Vesey Conspiracy%")
    ->with("cases")
    ->orderBy("name")
    ->get();

if ($people->isEmpty()) {
    echo "No record carries the Denmark Vesey Conspiracy affiliation (already removed?)\n";
    exit(0);
}

echo $people->count()." record(s) to remove:\n\n";
foreach ($people as $p) {
    echo "  {$p->name}  [{$p->slug}]  sort={$p->sort_order}  photo=".($p->photo ? "yes" : "no")."\n";
    echo "     state: ".($p->state ?: "-")."   ideologies: ".implode(", ", $p->ideologies ?: [])."\n";
    foreach ($p->cases as $c) {
        echo "     case  arr=".($c->arrest_date ? $c->arrest_date->toDateString() : "-")
            ."  inc=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
            ."  rel=".($c->release_date ? $c->release_date->toDateString() : "-")
            ."  days=".($c->imprisoned_for_days ?? "null")."\n";
        echo "           ".substr((string) $c->sentence, 0, 80)."\n";
    }
}

if ($people->count() !== 5) {
    echo "\nNOTE: five records carried this affiliation when the change was written.\n";
    echo "This run found ".$people->count().". Check the list above before trusting it.\n";
}

$unlinked = 0;
foreach ($people as $p) {
    foreach (CalendarEntry::where("prisoner_id", $p->id)->get() as $entry) {
        $entry->prisoner_id = null;
        $entry->save();
        $unlinked++;
    }
}
if ($unlinked) {
    echo "\nUnlinked {$unlinked} calendar entrie(s) that pointed at these records.\n";
}

$n = 0;
foreach ($people as $p) {
    if ($review) {
        $p->under_review = true;
        $p->save();
    } else {
        $p->delete();
    }
    $n++;
}

echo "\n";
if ($review) {
    echo "Hidden {$n} record(s) from the public site (under_review = true). Data kept; reverse it in the admin.\n";
} else {
    echo "Deleted {$n} record(s) and their cases.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
