#!/usr/bin/env bash
#
# BATCH 239 -- the Centralia sentencing date for the two Blands.
#
#   13 MARCH 1920, on Bert Bland and O. C. Bland. They were the only two of
#   the seven convicted Centralia defendants in this archive without it;
#   Britt Smith, Eugene Barnett, John Lamb, Loren Roberts and Ray Becker
#   all already carried it.
#
#   THE DATE IS NOT INFERRED FROM THE CODEFENDANTS. The Alaska Daily
#   Empire of Monday 15 March 1920, page 1, an Associated Press dispatch
#   datelined Montesano, names both Blands among the convicted: "James
#   McInerney, Ray Becker, 0. C. Bland, Bert Bland and Britt Smith found
#   guilty of murder in the second degree." The verdict came in "at 9:25 p.
#   m. Saturday night" -- the Saturday before a Monday paper of 15 March is
#   13 March. The OCR was fetched from the Library of Congress and read.
#
#   NO JUDGE IS ADDED. The dispatch says "the Court" and never names him.
#   This batch writes what the source states and nothing else, so the
#   Centralia judge stays empty on all fourteen related records.
#
#   THREE THINGS THE SAME PAGE THROWS UP, FLAGGED AND NOT CHANGED.
#
#     Loren Roberts is recorded here as convicted with a 25-to-40-year
#     sentence and a jury finding of insanity. The dispatch says he was
#     acquitted because the jury found him insane, with a recommendation
#     that he be confined. One of the two is wrong and a second source
#     would settle it.
#
#     Elmer Smith carries a sentencing date of 5 April 1920 on a record
#     that says acquitted -- which the dispatch confirms. A sentencing date
#     on an acquittal is a contradiction whatever that date refers to.
#
#     James McInerney is named among the Centralia convicted, but the only
#     McInerney record here holds the Everett case: arrested 5 November
#     1916, Snohomish County jail, charges dismissed in 1917. If that is
#     the same man he is missing an entire second case.
#
#   Idempotent: the date is written only where the field is empty.
#
# Run from the repo root, after git pull, after batch 238:
#   bash database/data/run-batch-239.sh

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
echo "  Batch 239 — Centralia, the two Blands"
echo "==================================================================="

BLAND_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch239.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$date = $payload["sentenced_date"];
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

    if ($case->sentenced_date) {
        echo "  ", str_pad($p->name, 14), " already reads ",
            $case->formatPartialDate("sentenced_date"), " — left alone\n";
        $already++;

        continue;
    }

    $case->sentenced_date = $date;
    $case->date_precision = array_merge($case->date_precision ?? [], ["sentenced_date" => $payload["precision"]]);
    $case->save();
    $case->refresh();
    $written++;

    echo "  ", str_pad($p->name, 14), " sentenced -> ", $case->formatPartialDate("sentenced_date"),
        "   arrested ", ($case->arrest_date ? $case->formatPartialDate("arrest_date") : "—"),
        "   released ", ($case->release_date ? $case->formatPartialDate("release_date") : "—"), "\n";
}

echo "\n  written ", $written, "   already had one ", $already, "\n";

// The rest of the Centralia group, so the two are visibly in line with it.
echo "\n  the Centralia group as it now stands:\n";

foreach (["britt-smith", "eugene-barnett", "john-lamb", "loren-roberts", "ray-becker", "bert-bland", "o-c-bland", "elmer-smith"] as $slug) {
    $q = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

    if (! $q) { continue; }

    $c = $q->cases()->first();

    if (! $c) { continue; }

    echo "    ", str_pad($q->name, 16), " sentenced ", str_pad($c->sentenced_date ? $c->formatPartialDate("sentenced_date") : "—", 16),
        " [", mb_substr((string) $c->convicted, 0, 30), "]\n";
}

echo "\n  SOURCE\n  ", wordwrap($payload["source"], 70, "\n  "), "\n";
echo "\n  ", wordwrap($payload["why_the_date"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["no_judge"], 72, "\n  "), "\n";

echo "\n  FLAGGED, NOT CHANGED\n";

foreach ($payload["flags"] as $i => $f) {
    echo "\n  ", ($i + 1), ". ", wordwrap($f, 69, "\n     "), "\n";
}

echo "\n  problems: ", count($bad), "\n";

foreach ($bad as $b) { echo "    !! ", $b, "\n"; }

if (count($bad) === 0 && ($written + $already) === (int) $payload["expected"]["count"]) { echo "\nB239-OK\n"; }
'

run_tinker "centralia-blands" "B239-OK" "$BLAND_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 239 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
