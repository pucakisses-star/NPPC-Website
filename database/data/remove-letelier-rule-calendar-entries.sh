#!/usr/bin/env bash
#
# Remove eleven calendar entries per the full-year audit, extending the
# September removals across the calendar.
#
# Nine fall under the Letelier rule -- assassinations and killings with no
# prisoner in the story:
#
#   Jan 17, 1969  Black Panthers Bunchy Carter and John Huggins murdered
#   Jan 18, 2023  Tortuguita killed by Georgia State Patrol in Atlanta forest
#                 (his prisoner record was removed on the same ground)
#   Feb 21, 1965  Malcolm X assassinated in New York
#   Mar  5, 1770  Boston Massacre; Crispus Attucks killed
#   Apr  4, 1968  Martin Luther King Jr assassinated in Memphis
#   May 19, 1920  Matewan WV shootout between miners and Pinkertons
#                 (Sid Hatfield was removed because no one was jailed)
#   Jul 28, 1932  Hoover troops attack Bonus Army encampment
#   Nov  3, 1970  UFW union office bombed in California
#   Nov 13, 1938  Jean Seberg born; later destroyed by COINTELPRO
#                 (hounded by COINTELPRO but never arrested or imprisoned --
#                 the only birthday entry for a non-prisoner)
#
# Two fall under the no-custody rules:
#
#   Feb  3, 1931  US anarchist Michele Schirru arrested in Rome
#                 (arrested, imprisoned and executed by Italian fascist
#                 authorities -- the foreign-authorities rule)
#   May 17, 1946  US government seizes the railroads to break strike
#                 (a strike broken by threat of conscription; nobody jailed)
#
# Matching is by month plus a title fragment with no apostrophes, so the
# stacked days lose only the entry named: the Obama/Lopez Rivera commutation
# on Jan 17, the Maile Hampton arrest on Jan 18 and the Catonsville Nine on
# May 17 are untouched. Each entry is printed before deletion, so the output
# is a receipt, and the script aborts if a fragment matches more than one row.
#
# Idempotent: already-removed entries report as not found. Run from the repo
# root:
#   bash database/data/remove-letelier-rule-calendar-entries.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\CalendarEntry;

$targets = [
    [1,  "Bunchy Carter and John Huggins murdered"],
    [1,  "Tortuguita killed by Georgia State Patrol"],
    [2,  "Michele Schirru arrested in Rome"],
    [2,  "Malcolm X assassinated"],
    [3,  "Boston Massacre"],
    [4,  "Martin Luther King Jr assassinated"],
    [5,  "seizes the railroads to break strike"],
    [5,  "Matewan WV shootout"],
    [7,  "troops attack Bonus Army"],
    [11, "UFW union office bombed"],
    [11, "Jean Seberg born"],
];

$removed = 0;
foreach ($targets as [$month, $fragment]) {
    $matches = CalendarEntry::where("month", $month)
        ->where("title", "like", "%".$fragment."%")
        ->get();

    if ($matches->isEmpty()) {
        echo "not found (already removed?): month {$month} ...{$fragment}...\n";
        continue;
    }
    if ($matches->count() > 1) {
        echo "ABORT: fragment matches ".$matches->count()." entries: {$fragment}\n";
        foreach ($matches as $m) { echo "  {$m->month}/{$m->day}, {$m->year}: {$m->title}\n"; }
        exit(1);
    }

    $e = $matches->first();
    echo "removing  ".str_pad("{$e->month}/{$e->day}", 5)." {$e->year}  {$e->title}\n";
    echo "          published=".($e->published ? "yes" : "no")
        ."  image=".($e->image ?: "(none)")
        ."  prisoner link=".($e->prisoner_id ?: "(none)")."\n";
    $e->delete();
    $removed++;
}

echo "\nRemoved {$removed} of ".count($targets)." target entries.\n";
echo "Stacked-day survivors -- the Lopez Rivera commutation (Jan 17), the\n";
echo "Maile Hampton arrest (Jan 18) and the Catonsville Nine (May 17) -- are\n";
echo "untouched.\n";
'

echo
echo "Done."
