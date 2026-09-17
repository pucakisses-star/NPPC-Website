#!/usr/bin/env bash
#
# BATCH 346 -- site-text audit, the database-content half.
#
#   The FAQ "Where does my donation go?" spells organisations the
#   British way while the funding FAQ a few entries up uses
#   organizations — a surgical one-word replace in the stored
#   answer, guarded and idempotent.
#
#   (The template-side fixes from the same audit ship in this
#   commit as code changes: the hidden leftover Ataturk demo header
#   on /timeline, the "numerous various" doubling on /database, and
#   the stale "22 years" headline and wrong nppc.org domain on the
#   Imam Jamil Al-Amin story page.)
#
# Run from the repo root, after git pull (after batch 345):
#   bash database/data/run-batch-346.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then
        return 0
    fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 346 — FAQ spelling consistency"
echo "==================================================================="

fix_batch() {
    php artisan tinker --execute='
use App\Models\Faq;

$faq = Faq::where("answer", "like", "%law-enforcement organisations%")->first();

if (! $faq) {
    echo "No FAQ carries the British spelling — already fixed or wording changed.\n";
    return;
}

$faq->answer = str_replace("law-enforcement organisations", "law-enforcement organizations", $faq->answer);
$faq->save();

echo "Fixed: ", $faq->question, "\n";
'
}

run "faq-organisations-spelling" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 346 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
