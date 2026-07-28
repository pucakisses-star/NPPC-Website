#!/usr/bin/env bash
#
# William F. Dunne is at the top of the database -- sort_order 2, sandwiched
# between the 2026 arrests -- for a case from May 1927. The list runs
# newest-first, so a 1927 record belongs deep in the 1920s block, around
# sort 4860 where the rest of the 1927 cohort ends. How he got to the top is
# not recorded; most likely a drag in the admin or an insert when the record
# was created.
#
# THE FIX reuses the standard machinery rather than hand-picking a number:
# his sort_order is reset to 0, then prisoners:place-zero-sort-by-year
# re-places him. His affiliations (Workers (Communist) Party, Daily Worker)
# match no positioned cluster -- nobody else carries either string -- so the
# command falls through to chronology and slots him at the end of the 1927
# cohort, currently after Rothschild Francis at 4863. That also lands him
# near David Gordon at 4815, the author of the poem America that Dunne was
# jailed for publishing.
#
# The command is dry-run by default; this script calls it with --apply and
# prints the placement with its neighbours, so the result is checkable in the
# output. Idempotent: on a second run his year puts him back in the same
# cohort.
#
# Run from the repo root:
#   bash database/data/fix-william-f-dunne-sort.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "william-f-dunne")->first();
if (! $p) { echo "NOT FOUND: william-f-dunne\n"; exit(1); }

echo "current position: sort {$p->sort_order}\n";
$above = Prisoner::withoutGlobalScopes()->where("sort_order", "<", $p->sort_order)->where("sort_order", "!=", 0)->orderByDesc("sort_order")->first();
$below = Prisoner::withoutGlobalScopes()->where("sort_order", ">", $p->sort_order)->orderBy("sort_order")->first();
echo "  above: ".($above ? $above->name : "(top)")."\n";
echo "  below: ".($below ? $below->name : "(bottom)")."\n";

$p->sort_order = 0;
$p->save();
echo "reset to sort 0 -- handing over to prisoners:place-zero-sort-by-year\n";
'

php artisan prisoners:place-zero-sort-by-year --apply

echo
echo "Done."
