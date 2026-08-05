#!/usr/bin/env bash
#
# BATCH 163 -- the four missing donation FAQ entries.
#
#   NONE OF THE EXISTING FAQ ANSWERS WAS BLANK. All twenty-two carry
#   between 380 and 2,200 characters. So nothing is being filled in:
#   these are the questions from the curator's list that do not exist
#   on the site yet.
#
#   TWO OF THE SEVEN ARE ALREADY ANSWERED under different wording and
#   are not duplicated. "Are donations tax deductible?" exists as "Is
#   my donation tax-deductible?", and "Can I make a donation with
#   Bitcoin or cryptocurrency?" exists as "Do you accept donations of
#   stocks, crypto, or DAFs?", which already names BTC, ETH and USDC.
#   "Where your donation goes" and "How we use your donations" were
#   two headings for one thing and are answered once.
#
#   NOTHING IS COPIED FROM THE SOURCE TEXT. The curator's paste is the
#   Innocence Project's FAQ — it names that organisation, asserts its
#   501(c)(3) status and gives its Tax ID, 32-0077563. Reproducing any
#   of it here would have published another organisation's EIN as
#   NPPC's. The answers are written instead from what this site
#   already states about itself, and from what the code does: Stripe
#   processes payments, and the donation form offers one-time, monthly
#   and yearly intervals.
#
#   The type and sort order are read from the existing
#   tax-deductibility entry rather than guessed, so the new questions
#   land in the same section of the page.
#
#   Idempotent: a question that already exists is left alone.
#
# Run from the repo root, after git pull (after batch 162):
#   bash database/data/run-batch-163.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"; shift
    echo; echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}"); return 0
}

echo "==================================================================="
echo "  Batch 163 — donation FAQ entries"
echo "==================================================================="

add_faqs() {
    php artisan tinker --execute='
use App\Models\Faq;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch163.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$anchor = Faq::where("question", $payload["anchor_question"])->first();

if (! $anchor) {
    echo "  anchor question not found: ", $payload["anchor_question"], "\n";
    echo "  refusing to guess a type — the new entries would land in the wrong section.\n";

    return;
}

echo "  anchor: ", $anchor->question, "\n";
echo "  type=", $anchor->type, "  sort_order=", $anchor->sort_order, "\n\n";

$order = (int) $anchor->sort_order;
$made = 0;
$had = 0;

foreach ($payload["faqs"] as $row) {
    $existing = Faq::where("question", $row["question"])->first();

    if ($existing) {
        echo "  already present, left alone: ", $row["question"], "\n";
        $had++;

        continue;
    }

    $order++;

    Faq::create([
        "question" => $row["question"],
        "answer" => $row["answer"],
        "type" => $anchor->type,
        "sort_order" => $order,
    ]);

    $made++;

    echo "  added [", $order, "] ", $row["question"], "\n";
    echo "         ", mb_strimwidth(str_replace("\n", " ", $row["answer"]), 0, 76, "..."), "\n";
}

echo "\n  ", $made, " added, ", $had, " already present.\n";

$blank = Faq::whereNull("answer")->orWhere("answer", "")->get();

echo "\n  FAQ entries with an empty answer: ", $blank->count(), "\n";

foreach ($blank as $b) { echo "    ", $b->question, "\n"; }

echo "  total FAQ entries of type ", $anchor->type, ": ",
    Faq::where("type", $anchor->type)->count(), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "donation-faqs" add_faqs

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then echo "  Batch 163 applied. No failures."
else echo "  Finished with ${#FAILED[@]} failed step(s):"; for f in "${FAILED[@]}"; do echo "    - ${f}"; done; fi
echo "==================================================================="
echo
echo "Check the cancellation answer before relying on it: it says to email"
echo "donations@nppc.org, because no self-service cancellation exists in the"
echo "code. If there is a customer portal, that answer should point at it."
