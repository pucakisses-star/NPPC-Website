#!/usr/bin/env bash
#
# BATCH 191 -- Monsour Owolabi's author bio, rewritten from the bio the
# archive already holds for him.
#
#   THERE ARE TWO BIOS OF HIM IN HERE and they do not say the same
#   things. His PRISONER record carries a description added by
#   add-abolition-media-audit.sh; his AUTHOR record carries a shorter one
#   written by batch 189 when the byline was created. The prisoner
#   description is the fuller of the two, and four things in it never
#   made it across: that he has been imprisoned since 2011, that he was
#   politicized inside, the two venues he has published in (Scalawag,
#   Texas Letters), and the two ideologies recorded on his prisoner
#   record (New Afrikan independence, prison abolition). This batch
#   rewrites the author bio from that description so the byline says what
#   the archive already knows.
#
#   NOTHING NEW IS ASSERTED. Every claim in the new text is already in
#   one of the two records. No fresh research, no campaign copy.
#
#   THE PRISONER RECORD IS NOT TOUCHED. Only the author about changes.
#
#   SINCE 2011, NOT NEARLY TWENTY YEARS. The campaign's material says
#   nearly twenty years imprisoned. That cannot be reconciled with the
#   TDCJ-derived date of birth of 1991-10-03 in
#   currently-imprisoned-dob.json -- it would have him imprisoned as a
#   child -- and the archive's own case record gives 2011 at year
#   precision. 2011 is used deliberately. Do not raise it to match
#   campaign copy without a source that settles the conflict.
#
#   LABOR, NOT LABOUR. Batch 189 wrote the British spelling; his prisoner
#   record uses the American one. Aligned to the prisoner record.
#
#   THE PRISONER-PAGE CROSS-REFERENCE IS A BARE PATH, not a link, because
#   about renders as escaped plain text everywhere it appears -- a
#   paragraph on the author page, the body of the about-the-author box
#   under each article, and truncated to 60 characters as the role line
#   in the homepage featured card. There is no markup available. Drop the
#   final sentence if the bare path reads badly; it is one field in the
#   JSON.
#
#   IF THE AUTHOR IS ABSENT this batch stops rather than creating one --
#   run batch 189 first. Order against batch 190 does not matter: 190
#   sets the avatar, this sets the about, neither reads the other.
#
#   Idempotent: the text is written only when it differs.
#
# Run from the repo root, after git pull (after batches 189 and 190):
#   bash database/data/run-batch-191.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

# tinker exits 0 even when the code inside throws; success is a sentinel
# the step prints as its last act.
run_tinker() {
    local label="$1" sentinel="$2" code="$3" out
    echo; echo "--- ${label}"
    out=$(php artisan tinker --execute="$code" 2>&1) || true
    printf '%s\n' "$out"
    if ! grep -q "$sentinel" <<<"$out"; then
        echo "  !! FAILED: ${label} — sentinel ${sentinel} missing (exception above?)"
        FAILED+=("${label}")
    fi
}

echo "==================================================================="
echo "  Batch 191 — Monsour Owolabi, author bio from the prisoner record"
echo "==================================================================="

UPDATE_CODE='
use App\Models\Author;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch191.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$a = $payload["author"];

$author = Author::where("slug", $a["slug"])->first();

// Deliberately not created here: an author with no article is half a record,
// and batch 189 is the batch that makes the whole one.
if (! $author) {
    echo "  no author at slug ", $a["slug"], " — run batch 189 first. Nothing changed.\n";

    return;
}

$was = $author->about;

if ($author->about !== $a["about"]) {
    $author->about = $a["about"];
    $author->save();
    $author->refresh();
    echo "  about rewritten (", mb_strlen((string) $was), " chars -> ", mb_strlen($author->about), " chars)\n";
} else {
    echo "  about already matches the payload — nothing to do.\n";
}

echo "\n  was:\n  ", wordwrap((string) ($was ?: "(none)"), 70, "\n  "), "\n";
echo "\n  now:\n  ", wordwrap($author->about, 70, "\n  "), "\n";

echo "\n  ", $author->name, "  [/author/", $author->slug, "]\n";
echo "    avatar:   ", ($author->avatar ?: "(none — batch 190 sets it)"), "  (untouched)\n";
echo "    articles: ", $author->articles()->count(), " (untouched)\n";

// The homepage featured card cuts the about at 60 characters; show what a
// reader actually sees there rather than assuming it reads well.
echo "    featured-card role line: ", mb_strimwidth($author->about, 0, 60, "..."), "\n";

// The prisoner record is the source this was written from and must come
// through unchanged; report it rather than trusting that it did.
$prisoner = Prisoner::withoutGlobalScopes()->where("slug", "monsour-owolabi")->first();

if ($prisoner) {
    echo "\n    prisoner record /prisoner/", $prisoner->slug, " — untouched, description ",
        mb_strlen((string) $prisoner->description), " chars\n";
    echo "    cross-reference in the bio resolves: ",
        (mb_strpos($author->about, "/prisoner/".$prisoner->slug) !== false ? "yes" : "NO — path does not match the slug"), "\n";
} else {
    echo "\n    !! no prisoner record at slug monsour-owolabi — the bio cross-references a page that does not exist.\n";
}

echo "\n  ", wordwrap($payload["source_note"], 70, "\n  "), "\n";
echo "\n  ", wordwrap($payload["date_note"], 70, "\n  "), "\n";

$ok = $author->about === $a["about"]
    && $prisoner
    && mb_strpos($author->about, "/prisoner/".$prisoner->slug) !== false;

if ($ok) { echo "\nB191-OK\n"; }
'

run_tinker "rewrite-about" "B191-OK" "$UPDATE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 191 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
