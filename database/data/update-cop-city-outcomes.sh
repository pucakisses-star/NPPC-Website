#!/usr/bin/env bash
#
# Stop Cop City case-outcome updates from Fire Ant's case links:
#
#   Ayla King      — July 2025 trial ended in a mistrial; the RICO charge was
#                    then dismissed on December 30, 2025 along with the other 60
#                    defendants after a judge found the Georgia attorney general
#                    lacked authorization to bring the case; the state appealed.
#   Priscilla Grim — RICO charge dismissed December 30, 2025; she and thirteen
#                    other defendants separately sought dismissal of long-
#                    unindicted domestic-terrorism allegations at a March 6,
#                    2026 hearing.
#   John Mazurek   — records the structured outcome of his March 2026 Alford
#                    plea (10 years probation) already noted in his description.
#
# Description edits are surgical/idempotent (str_replace or append-if-absent).
# Run from the repo root:
#   bash database/data/update-cop-city-outcomes.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$appendNote = function ($p, $note) {
    if ($p->description && strpos($p->description, $note) === false) {
        $p->description = rtrim($p->description) . " " . $note;
        return true;
    }
    return false;
};

// --- Ayla King ---
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "ayla-king")->first();
if ($p) {
    $note = "On December 30, 2025 the RICO charge against King was dismissed along with the charges against the other 60 defendants, after a judge found the Georgia attorney general lacked the required authorization to bring the case; the state appealed the dismissal in early 2026.";
    if ($appendNote($p, $note)) { $p->save(); echo "  ayla-king: RICO dismissal noted\n"; }
    $c = $p->cases()->first() ?: (new \App\Models\PrisonerCase(["prisoner_id" => $p->id]));
    $c->prisoner_id = $p->id;
    $c->convicted = "No — July 2025 trial ended in a mistrial; RICO charge dismissed December 30, 2025 (state appealing)";
    $c->save();
}

// --- Priscilla Grim ---
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "priscilla-grim")->first();
if ($p) {
    $desc = $p->description;
    $desc = str_replace(
        "The RICO case against her remains pending.",
        "The RICO charge against her was dismissed on December 30, 2025 along with the charges against the other 60 defendants, after a judge found the Georgia attorney general lacked authorization to bring the case; the state appealed. Grim and thirteen other defendants separately sought dismissal of long-unindicted domestic-terrorism allegations at a hearing on March 6, 2026.",
        $desc
    );
    if ($desc !== $p->description) { $p->description = $desc; $p->save(); echo "  priscilla-grim: RICO dismissal updated\n"; }
    $c = $p->cases()->first() ?: (new \App\Models\PrisonerCase(["prisoner_id" => $p->id]));
    $c->prisoner_id = $p->id;
    $c->convicted = "No — RICO charge dismissed December 30, 2025 (state appealing); domestic-terrorism allegations contested at a March 6, 2026 hearing";
    $c->save();
}

// --- John Mazurek (structured outcome; description already updated) ---
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "john-mazurek")->first();
if ($p) {
    $c = $p->cases()->first() ?: (new \App\Models\PrisonerCase(["prisoner_id" => $p->id]));
    $c->prisoner_id = $p->id;
    $c->convicted = "No prison term — Alford plea (March 2026) to reduced charge of criminal damage to property";
    $c->sentence = "10 years probation, with credit for jail time served";
    $c->save();
    echo "  john-mazurek: Alford-plea outcome recorded\n";
}

echo "Done.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
'

echo
echo "Done. Stop Cop City outcomes updated."
