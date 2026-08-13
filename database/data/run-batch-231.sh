#!/usr/bin/env bash
#
# BATCH 231 -- Carolyn Hart: the birth year was right, the case date was
# not.
#
#   1912 STANDS. It was checked against two independent sources and needs
#   no change. What was wrong is the thing that made it look wrong.
#
#   THE ARREST WAS 1 SEPTEMBER 1934, NOT 1937. The record dated the
#   McKeesport case to 1937, which is the year of her pardon. She was
#   arrested at a gathering of about two thousand people near the
#   McKeesport train station on 1 September 1934, aged 22; twenty
#   defendants were convicted of riot in the spring of 1935.
#
#   THE ARITHMETIC IS THE WHOLE STORY. Twenty-two at a demonstration on 1
#   September 1934 puts her birth in 1911 or 1912, so the supplied year
#   holds. Read against the record as it stood -- a twenty-two-year-old in
#   a case OF 1937 -- the same age implied 1915. The age was right and the
#   year was wrong.
#
#   ALICE C. BURKHART was her real name; Carolyn Hart is a pseudonym, and
#   the record carried no alias at all. Not asked for, added anyway: this
#   archive matches people across sources through the aka field -- it is
#   how William J. Turk was found to be Sekou Kambui -- so a missing real
#   name is a person who cannot be found.
#
#   THE DESCRIPTION IS LEFT ALONE AND FLAGGED. It calls this a case of
#   1937 and says she was sentenced to two years, where both sources
#   describe a minimum of eighteen months. Rewriting prose nobody asked
#   about has gone wrong on this branch before.
#
#   ONE THING THE SOURCES DO NOT SETTLE. Red Pittsburgh says she had
#   served more than two years from her initial conviction, reaching back
#   to 1935, while the Pittsburgh exhibit says she was sent to Muncy late
#   in 1936, which matches the 6 October 1936 already recorded. Whether
#   she was held elsewhere in between is not established, so the
#   incarceration date is untouched and the 234 days stands as the Muncy
#   confinement rather than as her whole time in custody.
#
#   Idempotent: each field is written only when it differs.
#
# Run from the repo root, after git pull, after batch 230:
#   bash database/data/run-batch-231.sh

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
echo "  Batch 231 — Carolyn Hart, arrest date and real name"
echo "==================================================================="

HART_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch231.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withoutGlobalScopes()->where("slug", $payload["prisoner"]["slug"])->first();

if (! $p) { echo "  !! no prisoner at that slug\n"; return; }

if ($p->name !== $payload["prisoner"]["expect_name"]) {
    echo "  !! that slug holds ", $p->name, " — stopping.\n";

    return;
}

if (! $p->aka || ! str_contains($p->aka, $payload["aka"])) {
    $was = $p->aka ?: "(none)";
    $p->aka = $p->aka ? $p->aka." / ".$payload["aka"] : $payload["aka"];
    $p->save();
    $p->refresh();
    echo "  aka           ", $was, "  ->  ", $p->aka, "\n";
} else {
    echo "  aka already carries ", $payload["aka"], "\n";
}

$c = $payload["case"];
$case = $p->cases()->first();

if (! $case) { echo "  !! no case row — stopping.\n"; return; }

$wasArrest = optional($case->arrest_date)->toDateString() ?: "(none)";

if ($wasArrest !== $c["arrest_date"]) {
    $case->arrest_date = $c["arrest_date"];
    echo "  arrest_date   ", $wasArrest, "  ->  ", $c["arrest_date"], "\n";
}

if (($case->date_precision["arrest_date"] ?? null) !== $c["arrest_precision"]) {
    $case->date_precision = array_merge($case->date_precision ?? [], ["arrest_date" => $c["arrest_precision"]]);
}

$case->save();
$case->refresh();

echo "\n  ", $p->name, "  [/prisoner/", $p->slug, "]\n";
echo "    aka           ", ($p->aka ?: "(none)"), "\n";
echo "    born          ", ($p->birthdate ? $p->formatPartialDate("birthdate") : "(none — batch 230 has not run)"),
    "   (confirmed, unchanged)\n";
echo "    arrested      ", $case->formatPartialDate("arrest_date"), "\n";
echo "    incarcerated  ", ($case->incarceration_date ? $case->formatPartialDate("incarceration_date") : "(none)"), "\n";
echo "    released      ", ($case->release_date ? $case->formatPartialDate("release_date") : "(none)"), "\n";
echo "    duration      ", $case->imprisoned_for_days, " days   (the Muncy confinement)\n";

// The prose that still dates the case to 1937, printed rather than described.
if (str_contains((string) $p->description, "1937")) {
    echo "\n  !! the description still reads:\n     ",
        wordwrap(mb_substr((string) $p->description, 0, 220), 68, "\n     "), "\n";
}

echo "\n  ", wordwrap($payload["arithmetic"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["sources"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["custody_caveat"], 72, "\n  "), "\n";

$ok = optional($case->arrest_date)->toDateString() === $payload["expected"]["arrest"]
    && str_contains((string) $p->aka, $payload["expected"]["aka"]);

if ($ok) { echo "\nB231-OK\n"; }
'

run_tinker "carolyn-hart-arrest" "B231-OK" "$HART_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 231 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
