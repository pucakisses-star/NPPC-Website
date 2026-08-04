#!/usr/bin/env bash
#
# BATCH 145 -- remove Martin S. Kimber, per the curator.
#
#   Flagged as Tier 1 in database/data/POLITICAL-MOTIVATION-AUDIT.md:
#   arrested for planting two pounds of mercury around an Albany, New
#   York hospital, including on food served to patients, and pleaded
#   guilty to chemical-weapon and consumer-product-tampering charges.
#   The record states no motive of any kind, political or otherwise,
#   and carries no ideology and no affiliation.
#
#   The removal deletes the record, its case rows and any
#   auto-generated calendar entries. The record is located by slug,
#   falling back to an exact case-insensitive name match; a name
#   matching more than one record aborts rather than guessing, and the
#   record is printed in full before it goes.
#
# Run from the repo root, after git pull (after batch 144):
#   bash database/data/run-batch-145.sh

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
echo "  Batch 145 — remove Martin S. Kimber"
echo "==================================================================="

remove_records() {
    php artisan tinker --execute='
use App\Models\CalendarEntry;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch145.json")), true);

if (! $payload || empty($payload["removals"])) {
    echo "Could not read the payload — nothing changed.\n";

    return;
}

$removed = 0;
$absent = 0;

foreach ($payload["removals"] as $row) {
    echo "\nREMOVE ", $row["slug"], "\n";

    $matches = Prisoner::withUnderReview()
        ->where(fn ($q) => $q->where("slug", $row["slug"])
            ->orWhereRaw("LOWER(name) = ?", [mb_strtolower($row["name"])]))
        ->with("cases")
        ->get();

    if ($matches->isEmpty()) {
        echo "  not found (already removed?)\n";
        $absent++;

        continue;
    }

    if ($matches->count() > 1) {
        echo "  ABORT: ", $matches->count(), " records match. Refusing to guess:\n";
        foreach ($matches as $m) { echo "    ", $m->slug, "  ", $m->name, "\n"; }

        continue;
    }

    $p = $matches->first();

    echo "  record:      ", $p->name, "  [", $p->slug, "]\n";
    echo "  era:         ", ($p->era ?: "-"), "\n";
    echo "  affiliation: ", (is_array($p->affiliation) ? implode(", ", $p->affiliation) : "-"), "\n";
    echo "  ideologies:  ", (is_array($p->ideologies) ? implode(", ", $p->ideologies) : "-"), "\n";
    echo "  photo:       ", ($p->photo ?: "(none)"), "\n";

    $cases = $p->cases->count();
    foreach ($p->cases as $c) { $c->delete(); }

    $cal = CalendarEntry::where("prisoner_id", $p->id)->delete();

    $p->delete();
    $removed++;

    echo "  deleted (", $cases, " case rows, ", $cal, " calendar entries) — ", $row["reason"], "\n";
}

echo "\n", $removed, " removed, ", $absent, " already absent, out of ",
    count($payload["removals"]), " in the payload.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "remove-kimber" remove_records

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 145 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
