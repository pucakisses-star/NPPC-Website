#!/usr/bin/env bash
#
# BATCH 229 -- Pío del Pilar: new biography, and real confinement dates.
#
#   THE DATES GO FROM A BARE 1901 TO 1903 to 15 January 1901 to 27 August
#   1902. Recorded plainly at day precision. Circa was considered and
#   rejected: in this archive circa collapses to a bare year, so it would
#   have thrown the month and the day away and rendered c. 1902.
#
#   THE DATES AND THE NEW BIOGRAPHY AGREE, which is the check worth
#   having. 15 January 1901 to 27 August 1902 is 589 days; the biography
#   says over a year and a half, and 589 days is one year and about seven
#   and a half months. The batch asserts the 589.
#
#   THIS REPLACES THE BIOGRAPHY RATHER THAN APPENDING, which is the
#   exception to the rule this archive normally follows -- nothing is
#   deleted from descriptions -- because replacing it is what was asked
#   for. The previous 485 characters are printed in full before the write,
#   so they stay recoverable from the run log. Little is lost in substance
#   either: the old text said much the same about the deportation and
#   added only that most deportees were released after taking the oath in
#   1902-1903, which the new text says of him specifically. What the new
#   text adds is that the other prisoners elected him their leader.
#
#   TWO THINGS FLAGGED AND NOT TOUCHED. The case sentence text still reads
#   "released to return home after the war (1902-1903)" and now sits
#   beside a release of 27 August 1902. And the supplied biography opens
#   "Pío del Pilar, was a Philippine revolutionary general", with a comma
#   between subject and verb; it is written exactly as supplied, since no
#   grammar fix was asked for this time.
#
#   Idempotent: every field is written only when it differs.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-229.sh

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
echo "  Batch 229 — Pío del Pilar"
echo "==================================================================="

PILAR_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch229.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withoutGlobalScopes()->where("slug", $payload["prisoner"]["slug"])->first();

if (! $p) { echo "  !! no prisoner at that slug\n"; return; }

if ($p->name !== $payload["prisoner"]["expect_name"]) {
    echo "  !! that slug holds ", $p->name, " — stopping.\n";

    return;
}

// Printed in full before it is replaced, so the old text stays recoverable
// from this log.
echo "\n  THE BIOGRAPHY BEING REPLACED (", mb_strlen((string) $p->description), " chars):\n\n  ",
    wordwrap((string) $p->description, 72, "\n  "), "\n";

if ($p->description !== $payload["description"]) {
    $p->description = $payload["description"];
    $p->save();
    $p->refresh();
    echo "\n  replaced with ", mb_strlen($p->description), " chars\n";
} else {
    echo "\n  biography already matches — nothing to do\n";
}

$c = $payload["case"];
$case = $p->cases()->first();

if (! $case) { echo "  !! no case row — stopping.\n"; return; }

foreach ([["incarceration_date", "incarceration_precision"], ["release_date", "release_precision"]] as [$field, $precKey]) {
    $was = optional($case->{$field})->toDateString() ?: "(none)";

    if ($was !== $c[$field]) {
        $case->{$field} = $c[$field];
        echo "  ", str_pad($field, 20), " ", str_pad($was, 12), " -> ", $c[$field], "\n";
    }

    if (($case->date_precision[$field] ?? null) !== $c[$precKey]) {
        $case->date_precision = array_merge($case->date_precision ?? [], [$field => $c[$precKey]]);
    }
}

$case->save();
$case->refresh();

echo "\n  ", $p->name, "  [/prisoner/", $p->slug, "]\n";
echo "    incarcerated  ", $case->formatPartialDate("incarceration_date"), "\n";
echo "    released      ", $case->formatPartialDate("release_date"), "\n";
echo "    duration      ", $case->imprisoned_for_days, " days   (the biography says over a year and a half)\n";
echo "    institution   ", ($case->institution?->name ?: "(none)"), "   (untouched)\n";
echo "    flags         in_custody ", var_export((bool) $p->in_custody, true),
     "   released ", var_export((bool) $p->released, true), "   (untouched)\n";
echo "    photo         ", ($p->photo ?: "(none)"), "   (untouched)\n";

echo "\n  the biography now reads:\n\n  ", wordwrap($p->description, 72, "\n  "), "\n";

// The prose that still says 1902-1903, printed rather than described.
if (str_contains((string) $case->sentence, "1902")) {
    echo "\n  !! the case sentence text still reads:\n     ",
        wordwrap(explode("\n", (string) $case->sentence)[0], 68, "\n     "), "\n";
}

echo "\n  ", wordwrap($payload["date_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["replacement_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["flag_comma"], 72, "\n  "), "\n";

$ok = $p->description === $payload["description"]
    && mb_strlen($p->description) === (int) $payload["expected"]["chars"]
    && optional($case->incarceration_date)->toDateString() === $c["incarceration_date"]
    && optional($case->release_date)->toDateString() === $c["release_date"]
    && (int) $case->imprisoned_for_days === (int) $payload["expected"]["days"];

if ($ok) { echo "\nB229-OK\n"; }
'

run_tinker "pio-del-pilar" "B229-OK" "$PILAR_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 229 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
