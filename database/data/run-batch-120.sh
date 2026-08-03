#!/usr/bin/env bash
#
# BATCH 120 -- the June 1925 labor-press political-prisoner birthday
# notice, per the curator.
#
#   Nine of the ten prisoners named already have records, including
#   three resolved through spelling variants: the notice's B.
#   Johanson is b-johansen, Tom Harty is thomas-harty, and Pedro
#   Paroles is pedro-perales — the Rangel-Cline defendant still on a
#   Texas prison farm twelve years after the 1913 convictions,
#   alongside his co-defendants Jesus Gonzales and Leonardo Vasquez
#   at the Senior Farm, Dewalt.
#
#   ONE NEW RECORD via prisoner:add: JOHN BURNS — San Quentin 40054,
#   June 16 birthday, everything else honestly unresolved (distinct
#   from the Ed Burns and William Burns records).
#
#   ENRICHMENTS (empty fields only): prisoner numbers for Johansen
#   (38364), Ryan (35567), Suhr (9266), and Gonzalez (36458); the
#   notice appearance and yearless June birthdays appended to eight
#   biographies (a date field cannot hold a day without a year).
#
#   NOT changed, flagged in fixes/batch120.json: Vanzetti (the
#   notice prints June 19 against his documented June 11, 1888 — a
#   presumed printing error), the Suhr middle-initial question
#   (O. vs D.), and Martel/Brunton (Detroit arrestees, not
#   confirmed sentenced — no records created).
#
# Run from the repo root, after git pull (after the CRDL fifth wave):
#   bash database/data/run-batch-120.sh

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
echo "  Batch 120 — June 1925 birthday-notice enrichments"
echo "==================================================================="

create_burns() {
    php artisan prisoner:add "$(cat database/data/fixes/john-burns-sq.json)"
}

fix_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch120.json")), true);

if (! $payload || empty($payload["updates"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

foreach ($payload["updates"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    echo "\nFIX ", $row["slug"], "\n";

    if (! $p) { echo "  NOT FOUND — skipped\n"; continue; }

    $notes = [];

    if (! empty($row["aka_add"]) && ! str_contains((string) $p->aka, $row["aka_add"])) {
        $p->aka = trim(($p->aka ? $p->aka."; " : "").$row["aka_add"], "; ");
        $notes[] = "aka added";
    }

    if (! empty($row["inmate_set"]) && ! $p->inmate_number) {
        $p->inmate_number = $row["inmate_set"];
        $notes[] = "inmate_number=".$row["inmate_set"];
    }

    if (! empty($row["append"]) && ! str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
        $p->description = trim((string) $p->description)."\n\n".$row["append"];
        $notes[] = "notice appended";
    }

    if ($notes) { $p->save(); }

    echo "  ", ($notes ? implode("; ", $notes) : "already enriched"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "create-john-burns" create_burns
run "birthday-notice-enrichments" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 120 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
