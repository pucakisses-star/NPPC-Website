#!/usr/bin/env bash
#
# Remove five September calendar entries unrelated to political prisoners,
# per the audit of /calendar?month=9:
#
#   Sept  1, 1974  Puerto Rican Labour Day riot in Newark, NJ
#                  A police-brutality riot; the entry mentions no arrests,
#                  prosecutions or prisoners.
#   Sept 14, 1989  ACT UP infiltrates the New York Stock Exchange
#                  The text ends at the AZT price cut and never mentions the
#                  arrests; as written it is protest history, not prisoner
#                  history.
#   Sept 15, 1954  Lincoln Brigade veterans hauled before SACB
#                  An administrative hearing that imprisoned nobody.
#   Sept 21, 1976  Orlando Letelier assassinated by Pinochet agents in DC
#                  An assassination by a foreign secret police; no U.S.
#                  political prisoner in the story.
#   Sept 25, 1968  Seattle passes gun law to disarm Black Panthers
#                  A city council vote; nobody arrested, prosecuted or jailed.
#
# Matching is by month plus a title fragment (chosen to avoid apostrophes and
# to survive small wording edits), so a day that hosts multiple entries only
# loses the one named. Each entry is printed before deletion, so the output is
# a receipt. The unique (month, day) index means at most one row can match per
# fragment anyway, but the script still aborts if a fragment somehow matches
# more than one row.
#
# The seven related September entries -- the IWW raids, the Carter
# commutations, both Attica entries, the Oscar Lopez Rivera release, the
# Milwaukee 14 and the Elaine massacre -- are untouched.
#
# Idempotent: already-removed entries report as not found. Run from the repo
# root:
#   bash database/data/remove-september-calendar-entries.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\CalendarEntry;

$targets = [
    "Labour Day riot in Newark",
    "ACT UP infiltrates the New York Stock Exchange",
    "Lincoln Brigade veterans hauled before SACB",
    "Letelier assassinated",
    "gun law to disarm Black Panthers",
];

$removed = 0;
foreach ($targets as $fragment) {
    $matches = CalendarEntry::where("month", 9)
        ->where("title", "like", "%".$fragment."%")
        ->get();

    if ($matches->isEmpty()) {
        echo "not found (already removed?): ...{$fragment}...\n";
        continue;
    }
    if ($matches->count() > 1) {
        echo "ABORT: fragment matches ".$matches->count()." entries: {$fragment}\n";
        foreach ($matches as $m) { echo "  Sept {$m->day}, {$m->year}: {$m->title}\n"; }
        exit(1);
    }

    $e = $matches->first();
    echo "removing  Sept ".str_pad((string) $e->day, 2)." {$e->year}  {$e->title}\n";
    echo "          published=".($e->published ? "yes" : "no")
        ."  image=".($e->image ?: "(none)")
        ."  prisoner link=".($e->prisoner_id ?: "(none)")."\n";
    $e->delete();
    $removed++;
}

echo "\nRemoved {$removed} of ".count($targets)." target entries.\n";
echo "The seven related September entries are untouched.\n";
'

echo
echo "Done."
