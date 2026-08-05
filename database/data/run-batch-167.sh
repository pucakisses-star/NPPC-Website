#!/usr/bin/env bash
#
# BATCH 167 -- Mark Rudd: a portrait, and a prison sentence he never served.
#
#   THE RECORD SAID HE WAS IMPRISONED IN 1977. He was not. The row was
#   dated September 13, 1977, its charges mixed the Federal Anti-Riot Act
#   conspiracy with the Columbia trespass, and the derived year list
#   published a single entry: 1977. What happened on September 14, 1977
#   was a voluntary surrender at the Manhattan criminal courthouse
#   ending in release on his own recognizance about nine hours later.
#   The federal indictment produced no custody at all -- it was
#   dismissed with prejudice in October 1973 when prosecutors chose to
#   drop it rather than disclose how the evidence had been gathered.
#
#   THE SENTENCE WAS ON THE WRONG CASE. Two years probation and a
#   two-thousand-dollar fine was the CHICAGO disposition of January 19,
#   1978. It sat on a row labelled New York. New York gave him an
#   unconditional discharge: no jail, no probation, no supervision.
#
#   SIX ROWS REPLACE TWO, and the custody totals TWO MEASURED DAYS:
#
#     14-15 Nov 1967   1 day    Tombs, after the Dean Rusk demonstration
#     30 Apr 1968      null     Tombs, the Columbia buildings cleared
#     18 May 1968      null     Tombs, 618 West 114th Street
#     11-12 Oct 1969   1 day    Cook County Jail, Days of Rage
#     Dec 1969         null     two days, bail-condition violation
#     14 Sep 1977      0 days   nine hours in courthouse cells
#
#   THREE OF THE SIX MEASURE NOTHING ON PURPOSE. Two 1968 detentions
#   have a documented start and no documented end, and a same-day
#   release is an assumption, not a record. The December 1969 stint is
#   stated as two days with no dates given, and month precision would
#   resolve the release to December 1 and measure zero -- so the two
#   days are written in words in the row instead of being manufactured
#   out of the date fields.
#
#   NOT ENTERED: the Niagara Falls episode, about two days at Lockport
#   around May 19-21, 1969. The curator flags the date as inferred from
#   the day Leary v. United States came down rather than taken from a
#   booking register, and the allegation was marijuana possession at a
#   border crossing, which is not on its face a political prosecution.
#   It fails both tests this archive uses. Easily added if wanted.
#
#   NOT RECORDED AS EXILE. Seven and a half years underground, all of it
#   inside the United States. Dohrn, Ayers, Wilkerson and Boudin all
#   carry in_exile false for the same reason. The one Weather fugitive
#   here who does carry exile, John Jacobs, spent his underground years
#   in Vancouver.
#
#   THE PHOTOGRAPH is the Columbia Magazine portrait the curator sent,
#   captioned "Mark Rudd" by the publisher. Uncredited; see
#   database/data/photos/CREDITS-mark-rudd.md.
#
#   Idempotent: rows are matched by incarceration date, and the two
#   legacy rows are retargeted once by their old dates and thereafter
#   matched normally.
#
# Run from the repo root, after git pull (after batch 166):
#   bash database/data/run-batch-167.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"; shift
    echo; echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}"); return 0
}

echo "==================================================================="
echo "  Batch 167 — Mark Rudd: the custody rebuilt, and a portrait"
echo "==================================================================="

FILE="mark-rudd.jpg"
SRC="database/data/files/${FILE}"
DEST="storage/app/public/prisoners/${FILE}"

install_photo() {
    if [ ! -f "$SRC" ]; then echo "  source missing: $SRC"; return 1; fi
    mkdir -p storage/app/public/prisoners
    if [ -f "$DEST" ] && cmp -s "$SRC" "$DEST"; then
        echo "  already installed, identical"
    else
        cp "$SRC" "$DEST"
        echo "  installed: $DEST"
    fi
    ls -l "$DEST"
    head -c 2 "$DEST" | od -An -tx1 | grep -q 'ff d8' && echo "  header check: JPEG" \
        || { echo "  !! not a JPEG"; return 1; }
    [ -e "public/storage/prisoners/${FILE}" ] && echo "  reachable through the public symlink" \
        || { echo "  !! NOT reachable — run php artisan storage:link"; return 1; }
}

update_record() {
    php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch167.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$d = fn ($v) => $v ? $v->format("Y-m-d") : "----------";

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases.institution")->first();

if (! $p) { echo "  ", $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

echo "  record: ", $p->name, "  [", $p->slug, "]\n";
echo "  photo before: ", ($p->photo ?: "(none)"), "\n";
echo "  before: ", $p->cases->count(), " case row(s), ",
    (int) $p->cases->sum("imprisoned_for_days"), " measured day(s)\n";

foreach ($p->cases as $c) {
    echo "    in=", $d($c->incarceration_date), " out=", $d($c->release_date),
        "  inst=", ($c->institution ? $c->institution->name : "(none)"), "\n";
}

echo "  years published before: ", implode(", ", $p->getIncarcerationYearsArray()) ?: "(none)", "\n";

$pf = $payload["prisoner"];

$p->photo = $payload["photo_path"];

foreach (["middle_name", "description", "affiliation"] as $f) {
    if (array_key_exists($f, $pf)) { $p->{$f} = $pf[$f]; }
}

$p->save();

// Rows are claimed at most once per run, so a legacy retarget cannot steal a
// row another spec has already taken.
$claimed = [];

$fmt = function ($parts) {
    if (! $parts) { return null; }

    return sprintf("%04d-%02d-%02d", $parts[0], $parts[1] ?? 1, $parts[2] ?? 1);
};

echo "\n";

foreach ($payload["cases"] as $spec) {
    $wantIn = $fmt($spec["dates"]["incarceration_date"] ?? null);

    $case = $p->cases->first(function ($c) use ($wantIn, $claimed) {
        return $wantIn && $c->incarceration_date
            && ! in_array($c->id, $claimed, true)
            && $c->incarceration_date->format("Y-m-d") === $wantIn;
    });

    $how = "matched on the incarceration date";

    if (! $case && ! empty($spec["legacy"])) {
        $L = $spec["legacy"];

        $case = $p->cases->first(function ($c) use ($L, $claimed) {
            if (in_array($c->id, $claimed, true)) { return false; }

            foreach ($L as $field => $want) {
                $have = $c->{$field} ? $c->{$field}->format("Y-m-d") : null;
                if ($have !== $want) { return false; }
            }

            return true;
        });

        if ($case) { $how = "RETARGETED from the old row"; }
    }

    if (! $case) {
        $case = new PrisonerCase(["prisoner_id" => $p->id]);
        $how = "created";
    }

    echo "  ", str_pad($spec["label"], 22), $how, "\n";

    if ($case->exists && $case->institution) {
        $keep = $spec["institution"]["name"] ?? null;
        if ($case->institution->name !== $keep) {
            echo "    detaching ", $case->institution->name, "\n";
        }
    }

    $case->institution_id = null;

    if (! empty($spec["institution"])) {
        $inst = Institution::firstOrCreate(
            ["name" => $spec["institution"]["name"]],
            ["city" => $spec["institution"]["city"], "state" => $spec["institution"]["state"]]
        );
        $case->institution_id = $inst->id;
        echo "    institution ", $inst->name, ($inst->wasRecentlyCreated ? " (CREATED)" : " (existing)"), "\n";
    }

    // Every partial-date field is written on every run, so a stale value left
    // on a retargeted row (the old sentenced_date, for one) is cleared rather
    // than surviving underneath the new dates.
    foreach ($case->partialDateFields() as $f) {
        $parts = $spec["dates"][$f] ?? null;
        $case->setPartialDate($f, $parts[0] ?? null, $parts[1] ?? null, $parts[2] ?? null);
    }

    $case->charges = $spec["charges"];
    $case->convicted = $spec["convicted"];
    $case->sentence = $spec["sentence"];
    $case->judge = $spec["judge"] ?? null;
    $case->prisoner_id = $p->id;
    $case->save();
    $case->refresh();

    $claimed[] = $case->id;

    echo "    ", $case->formatPartialDate("incarceration_date") ?: "(no start)",
        "  ->  ", $case->formatPartialDate("release_date") ?: "(no end)",
        "   days = ", ($case->imprisoned_for_days ?? "null"), "\n";
}

$p->refresh()->load("cases");

$total = (int) $p->cases->sum("imprisoned_for_days");
$start = $p->cases->map(fn ($c) => $c->incarceration_date ?: $c->arrest_date)->filter()->sort()->first();

echo "\n  after: ", $p->cases->count(), " case row(s), ", $total, " measured day(s)\n";
echo "  counter: ", ($total > 0
    ? \App\Support\ImprisonmentDuration::phrase($start, $total,
        \App\Support\ImprisonmentDuration::documentedMonths($p->cases))
    : "(none)"), "\n";
echo "  years published after: ", implode(", ", $p->getIncarcerationYearsArray()) ?: "(none)", "\n";
echo "  photo after: ", $p->photo, "\n";
echo "  middle name: ", ($p->middle_name ?: "-"), "   affiliations: ", implode(", ", $p->affiliation ?: []), "\n";

$courts = $p->cases->filter(fn ($c) => $c->institution
    && str_contains(strtolower($c->institution->name), "court"))->count();

echo "  rows still attached to a court: ", $courts, " (want 0)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "install-photo" install_photo
run "update-record" update_record

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 167 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "Expected: 6 rows, 2 measured days, years 1967 1968 1969 1977, and"
echo "no imprisonment sentence anywhere on the profile."
