#!/usr/bin/env bash
#
# BATCH 245 -- Jesús M. Balzac, added.
#
#   EDITOR OF EL BALUARTE IN ARECIBO, prosecuted for criminal libel on two
#   informations over articles of 16 and 23 April 1918 attacking Arthur
#   Yager, the American colonial governor of Puerto Rico. He demanded a
#   jury in each case and was refused -- the Puerto Rico code allowed juries
#   in felonies, not misdemeanours -- was tried by the court, convicted on
#   both, and sentenced to five months in the district jail on the first
#   and four on the second, with costs.
#
#   THE APPEAL IS WHY THE NAME SURVIVES. Balzac v. Porto Rico, 258 U.S.
#   298, decided 10 April 1922: a unanimous Court under Chief Justice Taft
#   held that the Sixth Amendment jury right does not reach an
#   unincorporated territory, and that the citizenship the Jones Act of
#   1917 gave Puerto Ricans had not incorporated the island. It is one of
#   the Insular Cases and it is still cited. The facts underneath that
#   doctrine are nine months for a newspaper editor who insulted a
#   governor.
#
#   THE CASE FACTS COME FROM THE OPINION, read rather than summarised --
#   the Arecibo district court, the two informations, the jury refused in
#   each case, the five months and the four. The byline The Knight-Errant,
#   the governor by name, and Taft writing for a unanimous Court are from
#   the Wikipedia article on the case, which agrees with the opinion
#   everywhere the two overlap.
#
#   NO DATES ARE RECORDED ON THE CASE. The publications are dated and the
#   decision is dated, and both are in the description, but the arrest, the
#   trial and the nine months actually served are not established by what
#   was read. An arrest date inferred from a publication date is a guess,
#   and guesses have gone wrong on this branch before.
#
#   THE PRISON IS A NEW INSTITUTION, District Jail, Arecibo. A bare
#   District Jail already exists here and it is Katherine Morey, a Silent
#   Sentinel held in Washington. Hanging an Arecibo sentence on a
#   Washington jail because the names look alike is the contamination that
#   batch 215 spent thirteen rows undoing.
#
#   Idempotent: the create step is skipped when he is already there.
#
# Run from the repo root, after git pull, after batch 244:
#   bash database/data/run-batch-245.sh

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
echo "  Batch 245 — Jesús M. Balzac"
echo "==================================================================="

CREATE_JSON="database/data/fixes/batch245-create.json"

echo
echo "--- check-existing"
CHECK_CODE='
use App\Models\Prisoner;
$n = Prisoner::withoutGlobalScopes()
    ->where("name", "Jesús M. Balzac")
    ->orWhere("slug", "jesus-m-balzac")
    ->count();
echo $n === 0 ? "BALZAC-ABSENT\n" : "BALZAC-PRESENT (".$n.")\n";
'
CHECK_OUT=$(php artisan tinker --execute="$CHECK_CODE" 2>&1) || true
printf '%s\n' "$CHECK_OUT"

if grep -q "BALZAC-ABSENT" <<<"$CHECK_OUT"; then
    echo
    echo "--- create"
    if [ ! -f "$CREATE_JSON" ]; then
        echo "  !! missing $CREATE_JSON"
        FAILED+=("create")
    elif ! php artisan prisoner:add "$(cat "$CREATE_JSON")"; then
        echo "  !! prisoner:add failed"
        FAILED+=("create")
    fi
elif grep -q "BALZAC-PRESENT" <<<"$CHECK_OUT"; then
    echo "  already present — skipping the create step"
else
    echo "  !! could not determine whether he exists"
    FAILED+=("check-existing")
fi

VERIFY_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch245.json")), true);

if (! $payload) { echo "Could not read the payload.\n"; return; }

$e = $payload["expected"];
$p = Prisoner::withoutGlobalScopes()->where("name", $e["name"])->first();

if (! $p) { echo "  !! he is not there — the create step must have failed\n"; return; }

$case = $p->cases()->first();

echo "\n  ", $p->name, "  [/prisoner/", $p->slug, "]\n";
echo "    full name     ", trim($p->first_name." ".$p->middle_name." ".$p->last_name), "\n";
echo "    aka           ", ($p->aka ?: "(none)"), "\n";
echo "    era / state   ", $p->era, "   ", $p->state, "\n";
echo "    ideologies    ", implode(", ", (array) $p->ideologies), "\n";
echo "    affiliation   ", (($p->affiliation) ? implode(", ", (array) $p->affiliation) : "(none — he was an editor, not a member of anything documented)"), "\n";
echo "    flags         in_custody ", var_export((bool) $p->in_custody, true),
    "   released ", var_export((bool) $p->released, true), "\n";
echo "    cases         ", $p->cases()->count(), "\n";

if ($case) {
    echo "    institution   ", ($case->institution?->name ?: "(none)"),
        "   ", ($case->institution?->city ?: ""), " ", ($case->institution?->state ?: ""), "\n";
    echo "    charges       ", wordwrap((string) $case->charges, 66, "\n                  "), "\n";
    echo "    dates         arrest ", ($case->arrest_date ?: "(none)"),
        "  incarceration ", ($case->incarceration_date ?: "(none)"),
        "  release ", ($case->release_date ?: "(none)"), "\n";
}

// The Arecibo jail must be its own row, not the Washington one.
$dc = Prisoner::withoutGlobalScopes()->whereHas("cases.institution", function ($q) {
    $q->where("name", "District Jail");
})->pluck("name");

echo "\n  institutions named District Jail (unchanged, for contrast): ", $dc->implode(", "), "\n";

echo "\n  the biography reads:\n\n  ", wordwrap((string) $p->description, 72, "\n  "), "\n";

echo "\n  ", wordwrap($payload["sources"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["no_dates"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["new_institution"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["ideologies"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["why_here"], 72, "\n  "), "\n";

$ok = $p->slug === $e["slug"]
    && $p->cases()->count() === (int) $e["cases"]
    && $case
    && ($case->institution?->name) === $e["institution"];

if ($ok) { echo "\nB245-OK\n"; }
'

run_tinker "verify-balzac" "B245-OK" "$VERIFY_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 245 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
