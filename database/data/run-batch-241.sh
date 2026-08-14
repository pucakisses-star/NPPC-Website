#!/usr/bin/env bash
#
# BATCH 241 -- forty-one records get the disposition their own text already
# gave them.
#
#   NOT ONE OF THEM HAD A DISPOSITION RECORDED, and all forty-one already
#   carried the facts that settle it. Each describes a sentence in its own
#   words -- received a three-year prison sentence, received a four-year
#   prison sentence -- and each has an entry date, a release date and San
#   Quentin. A term served in a state penitentiary follows a conviction;
#   county jails are where the unconvicted wait.
#
#   THIS WAS THE THREAD THAT LOOKED LIKE IT NEEDED A NEWSPAPER PER NAME.
#   It did not. The prior question -- was this person convicted -- was
#   already answered inside the archive, in prose that had never been
#   carried across into the field the site reads. Forty-one rows of the
#   disposition column sat empty beside descriptions saying plainly what
#   the disposition was.
#
#   THE VALUE SAYS WHAT THE EVIDENCE IS and not more: the record states a
#   sentence received and a term served at the named prison. No charge, no
#   date and no court is invented.
#
#   TWO THINGS FLAGGED. Harry Williams has a book page header spliced into
#   his biography -- received a one- to four- State Anti-Sedition and
#   Criminal Syndicalism Prisoners 169 teen-year prison sentence -- which
#   is a one-to-fourteen-year term broken across a page in whatever was
#   scanned. He is included because the meaning is recoverable, but the
#   description needs repair and he will not be the only one. And Frances
#   Hart is in this set; her photograph was cleared in batch 226 as the
#   wrong person, and this touches only her disposition.
#
#   Idempotent: written only where the disposition is empty.
#
# Run from the repo root, after git pull, after batch 240:
#   bash database/data/run-batch-241.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

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
echo "  Batch 241 — dispositions already present in the records"
echo "==================================================================="

DISP_CODE='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch241.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$written = 0; $already = 0; $bad = [];

echo "\n";

foreach ($payload["people"] as $e) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $p) { echo "  !! no record at ", $e["slug"], "\n"; $bad[] = $e["slug"]; continue; }

    if ($p->name !== $e["expect_name"]) {
        echo "  !! ", $e["slug"], " now holds ", $p->name, " — skipped\n";
        $bad[] = $e["slug"];

        continue;
    }

    $case = $p->cases()->first();

    if (! $case) { echo "  !! no case row for ", $p->name, "\n"; $bad[] = $e["slug"]; continue; }

    if (trim((string) $case->convicted) !== "") {
        echo "  ", str_pad($p->name, 22), " already reads: ", mb_substr($case->convicted, 0, 40), " — left alone\n";
        $already++;

        continue;
    }

    // The evidence, printed beside the write rather than asserted.
    $why = "";

    if (preg_match("/(received a [^.]{0,70}sentence|was sentenced[^.]{0,50}|sentenced to [^.]{0,50})/i", (string) $p->description, $m)) {
        $why = trim($m[0]);
    }

    $case->convicted = $e["convicted"];
    $case->save();
    $written++;

    echo "  ", str_pad($p->name, 22), " ",
        ($case->incarceration_date ? $case->formatPartialDate("incarceration_date") : "—"), " to ",
        ($case->release_date ? $case->formatPartialDate("release_date") : "—"), "\n";
    echo "      its own words: ", ($why ?: "(no sentence phrase matched — included on the prison term)"), "\n";
}

echo "\n  dispositions written ", $written, "   already had one ", $already, "\n";

$rows = PrisonerCase::count();
$noDisp = PrisonerCase::whereNull("convicted")->orWhere("convicted", "")->count();

echo "\n  case rows in all              ", $rows, "\n";
echo "  still without a disposition   ", $noDisp, "   (", number_format(100 * $noDisp / max($rows, 1), 1), " percent)\n";

echo "\n  ", wordwrap($payload["evidence"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["no_research_needed"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["wording"], 72, "\n  "), "\n";

echo "\n  FLAGGED, NOT CHANGED\n";

foreach ($payload["flags"] as $i => $f) {
    echo "\n  ", ($i + 1), ". ", wordwrap($f, 69, "\n     "), "\n";
}

echo "\n  problems: ", count($bad), "\n";

foreach ($bad as $b) { echo "    !! ", $b, "\n"; }

if (count($bad) === 0 && ($written + $already) === (int) $payload["expected"]["count"]) { echo "\nB241-OK\n"; }
'

run_tinker "fill-dispositions" "B241-OK" "$DISP_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 241 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
