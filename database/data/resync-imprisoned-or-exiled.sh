#!/usr/bin/env bash
#
# RESYNC imprisoned_or_exiled -- 82 currently-imprisoned people are
# missing from the public "currently active" lists.
#
# THE COLUMN IS DERIVED, NOT ENTERED. The Prisoner saving hook sets it
# on every save:
#
#     imprisoned_or_exiled = in_custody || currently_in_exile
#
# and the comment above that hook says exactly why it exists: the
# column feeds the public currently-active lists, and if it desyncs
# "released prisoners can leak back into those lists."
#
# WHAT THE LIVE DATA SHOWS. 82 records disagree with that definition,
# and the direction is the harmful one:
#
#     flagged active while NOT in custody or exile  ....  0
#     in custody or exile while NOT flagged active  ...  82
#
# So nothing is leaking in. Instead 82 people the database says are in
# custody are being EXCLUDED from the lists that exist to show who is
# in custody -- Elias Rodriguez, Jacob Hoopes, Trenten Barker and 79
# others. That is the failure that matters more of the two.
#
# WHY IT HAPPENED. These rows have not been saved through the model
# since the hook was added, so the derivation never ran on them.
# Anything written by a bulk SQL update, an import, or a seeder
# bypasses Eloquent events the same way.
#
# THIS NEEDS NO JUDGEMENT. The column is DEFINED as that OR, so there
# is nothing to research and nothing to decide: any row where the
# stored value differs from the definition is wrong by construction.
# That is what makes this safe to apply in bulk, unlike the ten records
# flagged both in custody and released, which are contradictions in the
# source data and are being researched individually instead.
#
# It repairs BOTH directions, not just the 82, so it stays useful if a
# future import desyncs rows the other way.
#
# ONLY THE ONE COLUMN IS WRITTEN. The script sets the attribute and
# saves; the saving hook then recomputes the same value, so the result
# is stable. Records already correct are not touched at all, so
# updated_at is left alone across the other ~8,200 rows.
#
# Idempotent: a second run finds nothing to fix and says so.
#
# Run from the repo root:
#   bash database/data/resync-imprisoned-or-exiled.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$fixedIn = 0;   // should be active, was not flagged
$fixedOut = 0;  // was flagged active, should not be
$shown = 0;

foreach (Prisoner::withoutGlobalScopes()->cursor() as $p) {
    $should = ($p->in_custody || $p->currently_in_exile) ? 1 : 0;
    $stored = (int) $p->imprisoned_or_exiled;

    if ($stored === $should) {
        continue;
    }

    $p->imprisoned_or_exiled = $should;
    $p->save();

    if ($should === 1) {
        $fixedIn++;
    } else {
        $fixedOut++;
    }

    if ($shown < 20) {
        echo "  ", str_pad($p->slug, 32),
             " stored=", $stored, " -> ", $should,
             "  (in_custody=", (int) $p->in_custody,
             ", in_exile=", (int) $p->currently_in_exile, ")\n";
        $shown++;
    }
}

$total = $fixedIn + $fixedOut;
echo "\nAdded to the active lists (in custody or exile, wrongly hidden): {$fixedIn}\n";
echo "Removed from the active lists (not in custody or exile):         {$fixedOut}\n";
echo "Total rows repaired: {$total}\n";

if ($total === 0) {
    echo "Nothing to fix — every row already matches in_custody || currently_in_exile.\n";
}

$remaining = 0;
foreach (Prisoner::withoutGlobalScopes()->cursor() as $p) {
    if ((int) $p->imprisoned_or_exiled !== (($p->in_custody || $p->currently_in_exile) ? 1 : 0)) {
        $remaining++;
    }
}
echo "Verification pass — rows still out of sync: {$remaining}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
