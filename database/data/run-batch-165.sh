#!/usr/bin/env bash
#
# BATCH 165 -- John Gregory Jacobs: two wrong dates, the canonical name,
# and twenty-seven years underground recorded as exile.
#
#   THE BIRTH DATE WAS WRONG. The record carried February 2, 1947. The
#   curator gives September 30, 1947, from the Vancouver Magazine
#   investigation built on interviews with his brother, his partner and
#   former associates — the same reporting that established he had been
#   living in East Vancouver as Wayne Curry.
#
#   THE DEATH DATE WAS ALSO WRONG, and nobody asked about it. The record
#   said September 8, 1997; the same source puts the death at October
#   20, 1997, after police were called to his home on the 19th and he
#   died about eighteen hours later at Vancouver General. That matters
#   here beyond tidiness, because the death date is the end of the
#   exile: with the old value the span would be short by six weeks.
#
#   THE EXILE IS THE POINT OF THIS BATCH. He went underground around the
#   March 6, 1970 Greenwich Village townhouse explosion and was
#   concealed until he died: 10,090 days, a little over twenty-seven and
#   a half years. The record had in_exile false and no exile dates at
#   all, so the single longest fact about his life was absent from it.
#   in_exile is set true and currently_in_exile left false — he is dead,
#   and currently_in_exile drives the "currently active" lists.
#
#   NO RELEASE DATE, DELIBERATELY. The sources put his release on bail in
#   October 1969 without a day. Stored at month precision that becomes
#   October 1 — ten days BEFORE the October 11 arrest. The model would
#   read that as a mismatched pair and discard it, and a reader would
#   see a release preceding the arrest. Most Days of Rage defendants
#   were bailed inside a week, so the custody was probably a few days,
#   but no booking or jail record for him has been found. The row keeps
#   a start, gets no end, and publishes no imprisonment figure. A
#   plausible number is still a number nobody counted.
#
#   THE SLUG IS HELD FIXED. Prisoner::updating regenerates the slug
#   whenever the name is dirty, so renaming him to John Gregory Jacobs
#   would move the page to /prisoner/john-gregory-jacobs and 404 every
#   existing link to /prisoner/john-jacobs. The rename is applied and
#   the slug written back in a second save.
#
#   Idempotent: fields are set to fixed values, so a re-run is a no-op.
#
# Run from the repo root, after git pull (after batch 164):
#   bash database/data/run-batch-165.sh

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
echo "  Batch 165 — John Gregory Jacobs: dates, name, and the exile"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch165.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases")->first();

if (! $p) { echo "  ", $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

$originalSlug = $p->slug;

$show = function ($p, $when) {
    echo "  ", $when, ": ", $p->name, "  [", $p->slug, "]\n";
    echo "    aka          ", ($p->aka ?: "-"), "\n";
    echo "    born         ", ($p->birthdate ? $p->formatPartialDate("birthdate") : "-"), "\n";
    echo "    died         ", ($p->death_date ? $p->formatPartialDate("death_date") : "-"), "\n";
    echo "    affiliation  ", implode(", ", $p->affiliation ?: []), "\n";
    echo "    in_exile     ", ($p->in_exile ? "yes" : "no"),
        "   currently_in_exile ", ($p->currently_in_exile ? "yes" : "no"), "\n";
    echo "    exile days   ", ((int) $p->cases->sum("in_exile_for_days") ?: 0), "\n";
};

$show($p, "before");

$pr = $payload["prisoner"];

$p->name = $pr["name"];
$p->aka = $pr["aka"];
$p->description = $pr["description"];
$p->affiliation = $pr["affiliation"];
$p->in_exile = $pr["in_exile"];
$p->currently_in_exile = $pr["currently_in_exile"];

foreach (["birthdate", "death_date"] as $f) {
    $parts = $pr[$f] ?? null;
    $p->setPartialDate($f, $parts[0] ?? null, $parts[1] ?? null, $parts[2] ?? null);
}

$p->save();

// The rename makes name dirty, and the updating hook rewrites the slug from
// it. Put the old slug back so the public URL survives the correction.
if (! empty($payload["preserve_slug"]) && $p->slug !== $originalSlug) {
    echo "\n  slug regenerated as ", $p->slug, " by the rename — writing back ", $originalSlug, "\n";
    $p->slug = $originalSlug;
    $p->save();
}

$case = $p->cases->first();

if (! $case) {
    echo "\n  NO CASE ROW — arrest and exile dates NOT applied.\n";
} else {
    echo "\n  case row [", $case->id, "]\n";

    foreach ($payload["case"]["dates"] as $field => $parts) {
        $case->setPartialDate($field, $parts[0] ?? null, $parts[1] ?? null, $parts[2] ?? null);
    }

    $case->sentence = $payload["case"]["sentence"];
    $case->save();
    $case->refresh();

    foreach (["arrest_date", "incarceration_date", "release_date", "in_exile_since", "end_of_exile"] as $f) {
        echo "    ", str_pad($f, 20),
            ($case->{$f} ? $case->formatPartialDate($f)." [".$case->datePrecisionFor($f)."]" : "(none)"), "\n";
    }

    echo "    ", str_pad("imprisoned_for_days", 20), ($case->imprisoned_for_days ?? "null"), "\n";
    echo "    ", str_pad("in_exile_for_days", 20), ($case->in_exile_for_days ?? "null"), "\n";
}

$p->refresh()->load("cases");

echo "\n";
$show($p, "after ");

$days = (int) $p->cases->sum("in_exile_for_days");
echo "\n  exile: ", $days, " days = ", round($days / 365.25, 1), " years (want 10090)\n";
echo "  slug:  ", $p->slug, " (want ", $originalSlug, ")\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "jacobs-corrections" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 165 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
echo
echo "Check /prisoner/john-jacobs still resolves and now reads John"
echo "Gregory Jacobs, born September 30, 1947, died October 20, 1997,"
echo "with 10,090 days in exile and no imprisonment figure."
