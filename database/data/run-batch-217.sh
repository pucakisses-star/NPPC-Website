#!/usr/bin/env bash
#
# BATCH 217 -- deaths. Eight missing, six recorded as releases.
#
#   THE WORST ERROR IN THIS SET is the one already found on Roberto Rivera
#   in batch 210: a case row carrying a release dated the day before the
#   person died. Abdul Majid released 2016-04-02, died 2016-04-03. David
#   Rice released 2016-03-10, died 2016-03-11. Basheer Hameed released
#   2008-08-29, died 2008-08-30. Ed Poindexter released 2023-12-06, died
#   2023-12-07. Kuwasi Balagoon released 1986-12-13, which is the day he
#   died. None of them were released. The state held all five until they
#   died, and the record says otherwise.
#
#   SIX DIED FREE and simply have no death date: Sekou Odinga, Russell
#   Maroon Shoatz, Delbert Africa, Herman Wallace, Robert Seth Hayes and
#   Mutulu Shakur. Four of those six died within a year of release --
#   Wallace three days after, Shoatz fifty-two days after -- which is its
#   own fact and one the archive should be able to show.
#
#   EVERY DATE WAS CHECKED against published sources before it was written
#   here, and each one carries its source in the payload. The handout that
#   started this is a c.2012 advocacy sheet and was not treated as
#   authority for anything.
#
#   DIED IN CUSTODY IS STORED the way Prisoner::getIncarcerationYearsArray
#   tests for it: in_custody false and released FALSE with a death date
#   set -- the same shape as Luis Rodriguez and Romaine Fitzgerald, whose
#   comment in that method is the precedent. death_in_custody_date goes on
#   the case, where the saving hook mirrors it into release_date, so the
#   imprisonment clock stops at the death instead of running to today.
#
#   WARREN WELLS IS LEFT ALONE. The handout lists him as having died in
#   custody 1984-2001; the record has him arrested 6 April 1968 at Folsom
#   and released. Nothing found resolves it, and an unresolved conflict is
#   better left visible than settled by guesswork.
#
#   Idempotent: each field is written only when it differs.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-217.sh

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
echo "  Batch 217 — death dates and deaths recorded as releases"
echo "==================================================================="

DEATH_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch217.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$missing = 0;

$find = function (string $slug) use (&$missing) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

    if (! $p) { echo "  !! no prisoner at slug ", $slug, "\n"; $missing++; }

    return $p;
};

echo "\n  DIED AFTER RELEASE — death date only, flags untouched\n";

foreach ($payload["died_free"] as $e) {
    $p = $find($e["slug"]);

    if (! $p) { continue; }

    $was = optional($p->death_date)->toDateString() ?: "(none)";

    if ($was !== $e["death_date"]) {
        $p->death_date = $e["death_date"];
        $p->save();
        $p->refresh();
    }

    echo "    ", str_pad($p->name, 24), " ", str_pad($was, 12), " -> ",
        optional($p->death_date)->toDateString(),
        "   released=", var_export((bool) $p->released, true), "\n";
}

echo "\n  DIED IN CUSTODY — death date, both flags off, death on the case\n";

foreach ($payload["died_in_custody"] as $e) {
    $p = $find($e["slug"]);

    if (! $p) { continue; }

    $wasDeath = optional($p->death_date)->toDateString() ?: "(none)";
    $wasRel = (bool) $p->released;

    $p->death_date = $e["death_date"];
    $p->in_custody = false;
    $p->released = false;
    $p->save();
    $p->refresh();

    // The case carries the false release. Put the death on the case and let
    // the saving hook mirror it into release_date, which overwrites the
    // day-before value rather than leaving two contradictory dates.
    $case = $p->cases()->get()->sortByDesc(fn ($c) => (string) $c->release_date)->first();

    $caseWas = "(no case)";

    if ($case) {
        $caseWas = optional($case->release_date)->toDateString() ?: "(none)";
        $case->death_in_custody_date = $e["death_date"];
        $case->save();
        $case->refresh();
    }

    echo "    ", str_pad($p->name, 24), " death ", str_pad($wasDeath, 12), " -> ",
        optional($p->death_date)->toDateString(), "\n";
    echo "    ", str_pad("", 24), " released ", var_export($wasRel, true), " -> false",
        "   case release ", $caseWas, " -> ",
        ($case ? optional($case->release_date)->toDateString() : "(no case)"), "\n";
}

// Read everything back. The died-in-custody shape is what the stats chart
// reads, so it is asserted rather than assumed.
echo "\n  verification\n";

$bad = [];

foreach ($payload["died_free"] as $e) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $p || optional($p->death_date)->toDateString() !== $e["death_date"]) {
        $bad[] = $e["slug"]." death date not set";
    }
}

foreach ($payload["died_in_custody"] as $e) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $p) { $bad[] = $e["slug"]." missing"; continue; }

    if (optional($p->death_date)->toDateString() !== $e["death_date"]) { $bad[] = $e["slug"]." death date"; }
    if ($p->released || $p->in_custody) { $bad[] = $e["slug"]." flags still wrong"; }

    $case = $p->cases()->get()->first(fn ($c) => optional($c->death_in_custody_date)->toDateString() === $e["death_date"]);

    if (! $case) { $bad[] = $e["slug"]." death_in_custody_date not on any case"; continue; }

    // The whole point: the case that ends in the death must no longer claim a
    // release before it. Only that case is checked -- someone with an earlier,
    // genuine release on a separate case should not trip this.
    $rel = optional($case->release_date)->toDateString();

    if ($rel !== $e["death_date"]) {
        $bad[] = $e["slug"]." case release is ".($rel ?: "empty").", expected ".$e["death_date"];
    }
}

echo "    problems: ", count($bad), "\n";

foreach ($bad as $b) { echo "      !! ", $b, "\n"; }

echo "\n  ", wordwrap($payload["why"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["not_changed"], 72, "\n  "), "\n";

if (count($bad) === 0 && $missing === 0) { echo "\nB217-OK\n"; }
'

run_tinker "fix-deaths" "B217-OK" "$DEATH_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 217 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
