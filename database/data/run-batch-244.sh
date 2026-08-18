#!/usr/bin/env bash
#
# BATCH 244 -- David Webb is released.
#
#   RELEASE DATE 18 AUGUST 2026, at day precision. He was taken into
#   custody on 18 June 2026, which makes 61 days.
#
#   THE TWO FLAGS FOLLOW THE DATE. in_custody goes false and released goes
#   true. Left as they were, this record would carry a release date while
#   still reading in custody, and would keep turning up in the
#   currently-imprisoned lists the site builds from those flags.
#
#   TWO COLUMNS ARE DELIBERATELY NOT WRITTEN. imprisoned_or_exiled is
#   derived by the prisoner model on every save from in_custody and
#   currently_in_exile, and imprisoned_for_days is recomputed by the case
#   model from the two dates. Writing either by hand would only give them
#   something to disagree with. Both are asserted afterwards instead.
#
#   THE CASE STILL HAS NO DISPOSITION, and that is flagged rather than
#   filled. No conviction, no plea, no sentence; the description says jury
#   trials in several of the cases were scheduled for mid-June 2026. A
#   release with nothing recorded about the outcome is a normal state for a
#   case that ended in dismissal, acquittal or time served -- but which of
#   those it was is not in this record.
#
#   Idempotent: each field is written only when it differs.
#
# Run from the repo root, after git pull, after batch 243:
#   bash database/data/run-batch-244.sh

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
echo "  Batch 244 — David Webb released"
echo "==================================================================="

WEBB_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch244.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withoutGlobalScopes()->where("slug", $payload["prisoner"]["slug"])->first();

if (! $p) { echo "  !! no record at that slug\n"; return; }

if ($p->name !== $payload["prisoner"]["expect_name"]) {
    echo "  !! that slug holds ", $p->name, " — stopping.\n";

    return;
}

$case = $p->cases()->first();

if (! $case) { echo "  !! no case row — stopping.\n"; return; }

echo "\n  before\n";
echo "    in_custody   ", var_export((bool) $p->in_custody, true), "\n";
echo "    released     ", var_export((bool) $p->released, true), "\n";
echo "    release_date ", ($case->release_date ? $case->formatPartialDate("release_date") : "(none)"), "\n";

$was = optional($case->release_date)->toDateString() ?: "(none)";

if ($was !== $payload["release_date"]) {
    $case->release_date = $payload["release_date"];
    echo "\n  release_date   ", $was, "  ->  ", $payload["release_date"], "\n";
}

if (($case->date_precision["release_date"] ?? null) !== $payload["precision"]) {
    $case->date_precision = array_merge($case->date_precision ?? [], ["release_date" => $payload["precision"]]);
}

// imprisoned_for_days is recomputed by the case model on save.
$case->save();
$case->refresh();

foreach ($payload["flags_follow"] as $field => $value) {
    if ((bool) $p->{$field} !== (bool) $value) {
        echo "  ", str_pad($field, 14), " ", var_export((bool) $p->{$field}, true),
            "  ->  ", var_export((bool) $value, true), "\n";
        $p->{$field} = $value;
    }
}

// imprisoned_or_exiled is derived by the prisoner model on save.
$p->save();
$p->refresh();

echo "\n  ", $p->name, "   [/prisoner/", $p->slug, "]\n";
echo "    arrested            ", ($case->arrest_date ? $case->formatPartialDate("arrest_date") : "(none)"), "\n";
echo "    incarcerated        ", ($case->incarceration_date ? $case->formatPartialDate("incarceration_date") : "(none)"), "\n";
echo "    released            ", $case->formatPartialDate("release_date"), "\n";
echo "    time in custody     ", $case->imprisoned_for_days, " days   (derived)\n";
echo "    in_custody          ", var_export((bool) $p->in_custody, true), "\n";
echo "    released flag       ", var_export((bool) $p->released, true), "\n";
echo "    imprisoned_or_exiled ", var_export((bool) $p->imprisoned_or_exiled, true), "   (derived)\n";
echo "    disposition         ", ($case->convicted ?: "(still none — see the note)"), "\n";
echo "    state / era         ", $p->state, "   ", $p->era, "   (untouched)\n";

echo "\n  ", wordwrap($payload["why_the_flags"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["duration"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["no_disposition"], 72, "\n  "), "\n";

$e = $payload["expected"];

$ok = optional($case->release_date)->toDateString() === $e["release_date"]
    && ($case->date_precision["release_date"] ?? null) === "day"
    && (int) $case->imprisoned_for_days === (int) $e["days"]
    && ! $p->in_custody
    && $p->released
    && ! $p->imprisoned_or_exiled;

if ($ok) { echo "\nB244-OK\n"; }
'

run_tinker "release-david-webb" "B244-OK" "$WEBB_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 244 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
