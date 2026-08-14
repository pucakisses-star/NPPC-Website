#!/usr/bin/env bash
#
# BATCH 240 -- fifteen state criminal syndicalism cases stop being federal
# Espionage Act cases.
#
#   ALL FIFTEEN READ: Federal prosecution under the Espionage Act of 1917
#   and/or the Sedition Act of 1918. The same boilerplate corrected on
#   James Larkin in batch 234, where the record described a New York
#   criminal anarchy prosecution. It sits on 608 case rows.
#
#   TWO INDEPENDENT SIGNALS CONTRADICT IT ON THESE FIFTEEN, which is why
#   these and not the other 593. Each record own description says the
#   person was convicted under a state criminal syndicalism law, several
#   naming the California Criminal Syndicalism Act outright. And each was
#   held in a state prison -- San Quentin, or the Washington State
#   Penitentiary at Walla Walla. A federal Espionage Act conviction does
#   not send a man to San Quentin.
#
#   TWO CARRIED THE ERROR TWICE. Tom Connors read Three years in prison
#   (Espionage/Sedition Act conviction) and Nick Steelik Five years in
#   prison (Espionage/Sedition Act conviction). The term is left exactly as
#   it was; only the parenthesis naming the wrong statute changes.
#
#   THE OTHER 593 ARE NOT TOUCHED. 170 are in federal prisons, where the
#   charge is plausible and may be right. 331 record no institution at all,
#   so there is nothing to check against. Changing those would be guessing,
#   and guessing wholesale is how the wrong charge got there.
#
#   THE OLD TEXT IS PRINTED IN FULL before each write, so it stays
#   recoverable from this log.
#
#   Idempotent: written only where the charge still says Espionage.
#
# Run from the repo root, after git pull, after batch 239:
#   bash database/data/run-batch-240.sh

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
echo "  Batch 240 — state syndicalism, not the federal Espionage Act"
echo "==================================================================="

CHARGE_CODE='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch240.json")), true);

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

    if (! str_contains((string) $case->charges, "Espionage")) {
        echo "  ", str_pad($p->name, 20), " charge no longer says Espionage — left alone\n";
        $already++;

        continue;
    }

    echo "  ", $p->name, "   [", $e["prison"], "]\n";
    echo "      was : ", trim((string) $case->charges), "\n";
    echo "      now : ", $e["charges"], "\n";

    $case->charges = $e["charges"];

    if (isset($e["sentence"]) && str_contains((string) $case->sentence, "Espionage")) {
        echo "      sentence text also corrected:\n";
        echo "        was : ", mb_substr(trim((string) $case->sentence), 0, 76), "\n";
        echo "        now : ", $e["sentence"], "\n";
        $case->sentence = $e["sentence"];
    }

    $case->save();
    $written++;
}

echo "\n  charges rewritten ", $written, "   already corrected ", $already, "\n";

// What is left of the boilerplate, so the scale of the remainder stays visible.
$still = PrisonerCase::where("charges", "like", "%Espionage Act of 1917 and/or%")->count();
$echoed = PrisonerCase::where("sentence", "like", "%Espionage/Sedition Act conviction%")->count();

echo "\n  case rows still carrying that charge boilerplate : ", $still, "\n";
echo "  case rows whose sentence text still says it      : ", $echoed, "\n";

echo "\n  ", wordwrap($payload["evidence"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["what_was_there"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["sentence_text_too"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["not_the_other_593"], 72, "\n  "), "\n";

echo "\n  FLAGGED, NOT CHANGED\n";

foreach ($payload["flags"] as $i => $f) {
    echo "\n  ", ($i + 1), ". ", wordwrap($f, 69, "\n     "), "\n";
}

echo "\n  problems: ", count($bad), "\n";

foreach ($bad as $b) { echo "    !! ", $b, "\n"; }

if (count($bad) === 0 && ($written + $already) === (int) $payload["expected"]["count"]) { echo "\nB240-OK\n"; }
'

run_tinker "fix-syndicalism-charges" "B240-OK" "$CHARGE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 240 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
