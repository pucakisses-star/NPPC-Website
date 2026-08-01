#!/usr/bin/env bash
#
# BATCH 67 -- Catherine Marie Kerkow: birth date, exile date, and the
# FBI photograph.
#
#   - BIRTHDATE October 6, 1951 — the primary date of birth on her
#     own FBI wanted poster (which also lists October 7, 1951 and an
#     April 23, 1946 alias date; the curator-s date matches the
#     poster-s primary).
#   - IN EXILE SINCE June 2, 1972 — the day she and Roger Holder
#     hijacked Western Airlines Flight 701 and flew to Algeria; the
#     in_exile_since date on her case starts the exile counter, which
#     has now run more than fifty-four years.
#   - PHOTOGRAPH: the DMV photograph from her FBI Domestic Terrorism
#     wanted listing (fbi.gov/wanted/dt/catherine-marie-kerkow,
#     kerkowdmv.jpg — the poster labels it "Photograph taken in
#     1975"). fbi.gov challenges this environment-s requests, so the
#     image was extracted from the FBI-s own downloadable wanted
#     poster PDF at the same listing — the identical official source,
#     self-labeled. Cropped to 525x700.
#
# The attach fills an empty slot only; idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-67.sh

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
echo "  Batch 67 — Catherine Marie Kerkow: DOB, exile date, FBI photo"
echo "==================================================================="

fix_kerkow() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ -f "database/data/photos/catherine-marie-kerkow.jpg" ]; then
        cp -f "database/data/photos/catherine-marie-kerkow.jpg" "${DST_DIR}/catherine-marie-kerkow.jpg"
        echo "copied catherine-marie-kerkow.jpg"
    fi

    php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withUnderReview()->where("slug", "catherine-marie-kerkow")->with("cases")->first();

if (! $p) {
    echo "NOT FOUND: catherine-marie-kerkow\n";
    return;
}

echo $p->slug, "\n";

$notes = [];

$was = $p->birthdate ? $p->birthdate->format("Y-m-d") : null;
$p->setPartialDate("birthdate", 1951, 10, 6);
if ($was !== $p->birthdate->format("Y-m-d")) {
    $notes[] = "birthdate=1951-10-06";
}

$rel = "prisoners/catherine-marie-kerkow.jpg";
if (is_file(storage_path("app/public/".$rel)) && ! $p->photo) {
    $p->photo = $rel;
    $notes[] = "photo attached (FBI wanted-poster DMV photograph)";
}

if ($notes) {
    $p->save();
}

echo "  ", ($notes ? implode("; ", $notes) : "person already correct"), "\n";

$case = $p->cases->sortBy("created_at")->first();

if (! $case) {
    echo "  no case row — nothing more to do\n";
} else {
    $case->setRelation("prisoner", $p);
    $wasEx = $case->in_exile_since ? $case->in_exile_since->format("Y-m-d") : null;
    $case->setPartialDate("in_exile_since", 1972, 6, 2);

    if ($wasEx !== $case->in_exile_since->format("Y-m-d")) {
        $case->save();
        echo "  case: in_exile_since=1972-06-02", ($wasEx ? " (was ".$wasEx.")" : ""), "\n";
    } else {
        echo "  case: exile date already correct\n";
    }

    echo "  in exile for ", ($case->in_exile_for_days ?? "?"), " day(s)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-catherine-marie-kerkow" fix_kerkow

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 67 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
