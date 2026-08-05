#!/usr/bin/env bash
#
# BATCH 155 -- remove the ideology "Anti-Trump", per the curator.
#
#   Seven records carry it. It names the object of an act rather than
#   a politics, which is why it reads oddly beside Labor Organizing,
#   Anarchism or Anti-War.
#
#   WORTH KNOWING. Six of the seven have no other ideology and no
#   affiliation, so removing it leaves them with an empty taxonomy.
#   That is not only untidy: the audit in
#   database/data/POLITICAL-MOTIVATION-AUDIT.md uses exactly that
#   signature — no ideology, no affiliation, no political vocabulary —
#   to find records that may not belong, so these six will surface in
#   the next such sweep. Most are the threats-against-elected-officials
#   cluster that audit already raised as an open category question.
#   Removing the tag does not answer the question; it removes the only
#   thing that was answering it by assertion.
#
#   Only the tag is touched. No record is deleted, no other ideology is
#   disturbed, and a record left with nothing keeps nothing rather than
#   being handed a replacement nobody chose.
#
#   Idempotent: a second run finds no record carrying the tag.
#
# Run from the repo root, after git pull (after batch 154):
#   bash database/data/run-batch-155.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 155 — remove the ideology Anti-Trump"
echo "==================================================================="

strip_ideology() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch155.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$tag = $payload["ideology"];

$hits = Prisoner::withUnderReview()->get()
    ->filter(fn ($p) => is_array($p->ideologies) && in_array($tag, $p->ideologies, true));

echo "records carrying ", $tag, ": ", $hits->count(), "\n\n";

$emptied = 0;

foreach ($hits as $p) {
    $before = $p->ideologies;
    $after = array_values(array_filter($before, fn ($i) => $i !== $tag));

    $p->ideologies = $after;
    $p->save();

    echo "  ", str_pad($p->slug, 34), " [", implode(", ", $before), "]  ->  ",
        ($after ? "[".implode(", ", $after)."]" : "(none)"), "\n";

    if (! $after) {
        $emptied++;
        $aff = is_array($p->affiliation) ? $p->affiliation : [];
        echo "  ", str_pad("", 34), "   no ideology left; affiliation: ",
            ($aff ? implode(", ", $aff) : "(none)"), "\n";
    }
}

$left = Prisoner::withUnderReview()->get()
    ->filter(fn ($p) => is_array($p->ideologies) && in_array($tag, $p->ideologies, true))
    ->count();

echo "\n  records still carrying ", $tag, ": ", $left, " (want 0)\n";
echo "  records left with no ideology at all: ", $emptied, "\n";

if ($emptied > 0) {
    echo "\n  ", wordwrap(
        "Those records now match the signature the political-motivation audit uses to "
        ."find entries that may not belong: no ideology, no affiliation. They are not "
        ."wrong, but they will come up in the next sweep, and most of them are the "
        ."threats-against-officials cluster that audit already flagged as an open "
        ."category question.", 84, "\n  "), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "remove-anti-trump-ideology" strip_ideology

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 155 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
