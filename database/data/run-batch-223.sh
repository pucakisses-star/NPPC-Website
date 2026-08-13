#!/usr/bin/env bash
#
# BATCH 223 -- the Blumbergs get their dates.
#
#   BOTH WERE ALREADY HERE. Dorothy Rose Blumberg was one of the six
#   Maryland Communist Party leaders arrested under the Smith Act in 1951,
#   held in Baltimore on bail she could not raise. Albert Blumberg was
#   arrested in 1954 under the membership clause. Neither record carried a
#   birth date or a death date.
#
#   ONE CHECKED, ONE TAKEN ON TRUST, and the difference is recorded rather
#   than smoothed over. Albert Emanuel Blumberg, 10 August 1906 to 8
#   October 1997, born in Baltimore, is confirmed independently against
#   Wikipedia and the Rutgers philosophy department memorial, which agree
#   on both dates and on the middle name. Dorothy Rose Blumberg was not
#   separately confirmed; her dates are as supplied. Albert matching to
#   the day is good evidence the source is sound, but it is not the same
#   thing as having checked hers.
#
#   NEITHER DISPLAYED NAME CHANGES. Albert stays Albert Blumberg, with
#   Emanuel in the middle_name column where the site keeps a middle name
#   it does not display -- the same treatment as Roberto Epifanio Rivera,
#   Emanuel Theodore Bronner and Clennon Washington King. Renaming records
#   to a longer form nobody asked for has been a recurring mistake here
#   and this batch does not repeat it.
#
#   DEATH DATE ONLY, NO FLAG CHANGES. Both were released and both died
#   decades later at liberty -- Dorothy at 84, Albert at 91. This is the
#   shape batch 217 used for the six who died free, not the
#   died-in-custody shape.
#
#   THREE THINGS LEFT FOR A DECISION. They were married to each other and
#   neither description says so. Albert is filed under the state New York
#   and Dorothy under Maryland, which is defensible -- he was a Manhattan
#   Democratic district leader by the end -- but it puts a married pair
#   from the same Baltimore prosecution in two different states. And
#   Dorothy has no photograph while Albert does.
#
#   Idempotent: every field is written only when it differs.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-223.sh

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
echo "  Batch 223 — Dorothy Rose and Albert Emanuel Blumberg"
echo "==================================================================="

DATES_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch223.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$bad = [];

foreach ($payload["people"] as $e) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $p) { echo "  !! no prisoner at slug ", $e["slug"], "\n"; $bad[] = $e["slug"]; continue; }

    // Guard: the slug must still hold the person this batch was written for.
    if ($p->name !== $e["name"]) {
        echo "  !! ", $e["slug"], " holds ", $p->name, ", not ", $e["name"], " — skipping\n";
        $bad[] = $e["slug"];

        continue;
    }

    echo "\n  ", $p->name, "  [/prisoner/", $p->slug, "]\n";

    foreach (["first_name", "middle_name", "last_name", "birthdate", "death_date"] as $f) {
        $was = $p->{$f};
        $wasStr = $was instanceof \DateTimeInterface ? $was->format("Y-m-d") : (string) $was;

        if ($wasStr === $e[$f]) { continue; }

        $p->{$f} = $e[$f];
        echo "    ", str_pad($f, 12), " ", str_pad($wasStr ?: "(none)", 12), " -> ", $e[$f], "\n";
    }

    $p->save();
    $p->refresh();

    echo "    born         ", $p->formatPartialDate("birthdate"), "   ", $e["born"], "\n";
    echo "    died         ", $p->formatPartialDate("death_date"), "   ", $e["died"],
        "   aged ", $e["age"], "\n";
    echo "    full name    ", trim($p->first_name." ".$p->middle_name." ".$p->last_name),
        "   (displayed as ", $p->name, ")\n";
    echo "    state        ", $p->state, "\n";
    echo "    in_custody   ", var_export((bool) $p->in_custody, true),
        "   released ", var_export((bool) $p->released, true), "   (untouched)\n";
    echo "    photo        ", ($p->photo ?: "(none)"), "\n";

    // The age the record now implies must match the age the source states,
    // which is the cheapest check that the two dates belong together.
    $computed = $p->birthdate && $p->death_date
        ? $p->birthdate->diff($p->death_date)->y
        : null;

    echo "    age check    computed ", ($computed ?? "n/a"), " vs stated ", $e["age"],
        ($computed === (int) $e["age"] ? "   agrees" : "   !! DISAGREES"), "\n";

    if ($computed !== (int) $e["age"]) { $bad[] = $e["slug"]." age mismatch"; }

    if (optional($p->birthdate)->toDateString() !== $e["birthdate"]
        || optional($p->death_date)->toDateString() !== $e["death_date"]
        || $p->middle_name !== $e["middle_name"]
        || $p->name !== $e["name"]) {
        $bad[] = $e["slug"]." fields did not stick";
    }
}

echo "\n  problems: ", count($bad), "\n";

foreach ($bad as $b) { echo "    !! ", $b, "\n"; }

echo "\n  ", wordwrap($payload["verification"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["not_done"], 72, "\n  "), "\n";

if (count($bad) === 0) { echo "\nB223-OK\n"; }
'

run_tinker "set-blumberg-dates" "B223-OK" "$DATES_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 223 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
