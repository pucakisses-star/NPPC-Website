#!/usr/bin/env bash
#
# Fill in custody and charge data for the three Texas STATE-only Prairieland
# defendants (already in the database), per case info supplied by the site owner
# and the support committee:
#
#   Dario Sanchez     — hindering prosecution of terrorism; tampering with /
#                       fabricating evidence. Three detentions Jul 15-Aug 20,
#                       Aug 28-Sep 2, and Sep 22-24, 2025 (~43 days combined);
#                       now released on bond, case pending.
#   Janette Goering   — arrested Oct 21, 2025; hindering prosecution of
#                       terrorism; bond 5,000,000 dollars reduced to 275,000 on
#                       appeal; released ~June 2026 (~233-241 days).
#   Lucy Fowlkes      — arrested Jan 5, 2026 (Johnson County Jail); hindering
#                       prosecution of terrorism and tampering with / fabricating
#                       evidence; bond 5,000,000 then 10,000,000, reduced to
#                       150,000 on June 11, 2026; still listed in custody.
#
# Idempotent (description notes appended only if absent). Run from the repo root:
#   bash database/data/update-prairieland-state-defendants.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$appendNote = function ($p, $note) {
    if ($p->description && strpos($p->description, $note) === false) {
        $p->description = rtrim($p->description) . " " . $note;
    }
};

// --- Dario Sanchez ---
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "dario-sanchez")->first();
if ($p) {
    $c = $p->cases()->first() ?: (new \App\Models\PrisonerCase(["prisoner_id" => $p->id]));
    $c->prisoner_id = $p->id;
    $c->charges = "Hindering prosecution of terrorism; tampering with or fabricating physical evidence (Texas state)";
    $c->setPartialDate("arrest_date", 2025, 7, 15);
    $c->setPartialDate("incarceration_date", 2025, 7, 15);
    $c->setPartialDate("release_date", 2025, 9, 24);
    $c->imprisoned_for_days = 43;
    $c->save();
    $appendNote($p, "Across three separate detentions between July 15 and September 24, 2025 he spent roughly 43 days in custody before being released on bond; his case remains pending.");
    $p->in_custody = false; $p->released = true; $p->save();
    echo "  dario-sanchez updated (~43 days)\n";
}

// --- Janette Goering ---
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "janette-goering")->first();
if ($p) {
    $c = $p->cases()->first() ?: (new \App\Models\PrisonerCase(["prisoner_id" => $p->id]));
    $c->prisoner_id = $p->id;
    $c->charges = "Hindering prosecution of terrorism (Texas state)";
    $c->setPartialDate("arrest_date", 2025, 10, 21);
    $c->setPartialDate("incarceration_date", 2025, 10, 21);
    $c->setPartialDate("release_date", 2026, 6, 15);
    $c->imprisoned_for_days = 237;
    $c->save();
    $appendNote($p, "Her bond was set at 5,000,000 dollars, reduced to 275,000 dollars after an appellate ruling; she was released in mid-June 2026 after roughly eight months (about 233 to 241 days) in custody. Her state case remains pending.");
    $p->in_custody = false; $p->released = true; $p->save();
    echo "  janette-goering updated (~237 days)\n";
}

// --- Lucy Fowlkes ---
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "lucy-fowlkes")->first();
if ($p) {
    $c = $p->cases()->first() ?: (new \App\Models\PrisonerCase(["prisoner_id" => $p->id]));
    $c->prisoner_id = $p->id;
    $c->charges = "Hindering prosecution of terrorism; tampering with or fabricating physical evidence (Texas state)";
    $c->setPartialDate("arrest_date", 2026, 1, 5);
    $c->setPartialDate("incarceration_date", 2026, 1, 5);
    $c->release_date = null;
    $c->save();
    $appendNote($p, "Her bond was reported at 5,000,000 and then 10,000,000 dollars, reduced to 150,000 dollars on June 11, 2026; the support campaign continued to list her at the Johnson County Jail after that, amounting to at least 200 days in custody. Her state case remains pending.");
    $p->in_custody = true; $p->released = false; $p->save();
    echo "  lucy-fowlkes updated (still in custody)\n";
}

echo "Done.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
'

echo
echo "Done. Prairieland state defendants updated."
