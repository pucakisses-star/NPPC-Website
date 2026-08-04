#!/usr/bin/env bash
#
# BATCH 136 -- Nancy Epling: split the custody into the two segments
# actually served, per the curator.
#
#   Her record held ONE case row pairing the March 10, 2024 arrest
#   with a November 26, 2024 release. The model read that as 261 days
#   of continuous custody — eight and a half months for a thirty-day
#   sentence — and that is what the profile has been publishing. She
#   was not in jail between March and September.
#
#   The curator's two segments replace it:
#
#     27 Sep 2024 - 12 Oct 2024   15 days   first segment
#     11 Nov 2024 - ~27 Nov 2024  16 days   second segment
#
#   The existing row keeps the March 10 arrest date and takes the
#   first segment as its custody; a second row is created for the
#   second. Both rows keep the charges and the Licking County Jail
#   institution, which is reused rather than duplicated.
#
#   ON THE SECOND RELEASE DATE. The curator gives entry November 11
#   and release at approximately November 27, and notes one movement
#   source has her beginning November 12. Those do not quite
#   reconcile: fifteen days from November 11 ends November 26,
#   fifteen from November 12 ends November 27. The curator figure is
#   stored rather than an arithmetic one, so this row measures
#   sixteen days and the record totals thirty-one against a sentence
#   described as about thirty. The tension is written into the
#   sentence text rather than smoothed away.
#
#   Idempotent: the second row is matched on its incarceration date
#   before being created, so a re-run does not duplicate it.
#
# Run from the repo root, after git pull (after batch 135):
#   bash database/data/run-batch-136.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then
        return 0
    fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 136 — Nancy Epling: two custody segments, not 261 days"
echo "==================================================================="

fix_epling() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch136.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases")->first();

if (! $p) { echo $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

echo "record: ", $p->name, "  [", $p->slug, "]\n";
echo "  before: ", $p->cases->count(), " case row(s), imprisoned_for_days total = ",
    ($p->cases->sum("imprisoned_for_days") ?: "null"), "\n";

foreach ($p->cases as $c) {
    echo "    case ", $c->id, "  arrest=", ($c->arrest_date ? $c->arrest_date->format("Y-m-d") : "-"),
        "  in=", ($c->incarceration_date ? $c->incarceration_date->format("Y-m-d") : "-"),
        "  out=", ($c->release_date ? $c->release_date->format("Y-m-d") : "-"),
        "  days=", ($c->imprisoned_for_days ?? "null"), "\n";
}

$first = null;

foreach ($payload["segments"] as $seg) {
    if ($seg["role"] === "existing") {
        $case = $p->cases->first(function ($c) use ($seg) {
            return $c->arrest_date
                && $c->arrest_date->format("Y-m-d") === $seg["match_arrest_date"];
        });

        if (! $case) {
            echo "\n  no case row with arrest date ", $seg["match_arrest_date"], " — first segment not applied\n";

            continue;
        }

        $wasIn = $case->incarceration_date ? $case->incarceration_date->format("Y-m-d") : "empty";
        $wasOut = $case->release_date ? $case->release_date->format("Y-m-d") : "empty";

        $case->incarceration_date = $seg["incarceration_date"];
        $case->release_date = $seg["release_date"];
        $case->sentence = $seg["sentence"];
        $case->save();

        $first = $case->refresh();

        echo "\n  SEGMENT 1 on the existing row [", $case->id, "]\n";
        echo "    incarceration ", $seg["incarceration_date"], " (was ", $wasIn, ")\n";
        echo "    release       ", $seg["release_date"], " (was ", $wasOut, ")\n";
        echo "    arrest date retained: ", $case->arrest_date->format("Y-m-d"), "\n";
        echo "    days = ", ($case->imprisoned_for_days ?? "null"), "\n";

        continue;
    }

    // Second segment: matched on its incarceration date so a re-run
    // cannot create it twice.
    $existing = $p->cases->first(function ($c) use ($seg) {
        return $c->incarceration_date
            && $c->incarceration_date->format("Y-m-d") === $seg["incarceration_date"];
    });

    if ($existing) {
        echo "\n  SEGMENT 2 already present on row [", $existing->id, "] — not recreated\n";

        continue;
    }

    if (! $first) { echo "\n  segment 1 row not resolved — segment 2 not created\n"; continue; }

    $case = PrisonerCase::create([
        "prisoner_id" => $p->id,
        "institution_id" => $first->institution_id,
        "charges" => $first->charges,
        "convicted" => $first->convicted,
        "incarceration_date" => $seg["incarceration_date"],
        "release_date" => $seg["release_date"],
        "sentence" => $seg["sentence"],
    ]);

    echo "\n  SEGMENT 2 created [", $case->id, "]  ",
        $seg["incarceration_date"], " to ", $seg["release_date"],
        "  days = ", ($case->refresh()->imprisoned_for_days ?? "null"), "\n";
}

// Biography: appended, nothing replaced.
if (! empty($payload["bio_append"])
    && strpos((string) $p->description, "two fifteen-day segments") === false) {
    $p->description = trim((string) $p->description)." ".$payload["bio_append"];
    $p->save();
    echo "\n  biography: segment note appended\n";
}

$p->refresh()->load("cases");

$total = (int) $p->cases->sum("imprisoned_for_days");
$start = $p->cases->map(fn ($c) => $c->incarceration_date ?: $c->arrest_date)->filter()->sort()->first();

echo "\n  after:  ", $p->cases->count(), " case row(s), imprisoned_for_days total = ", $total, "\n";
echo "  public counter will read: Time Imprisoned: ",
    \App\Support\ImprisonmentDuration::phrase($start, $total,
        \App\Support\ImprisonmentDuration::documentedMonths($p->cases)), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "epling-custody-segments" fix_epling

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 136 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
