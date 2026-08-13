#!/usr/bin/env bash
#
# BATCH 228 -- confinement dates for David Eichel and Bruno Grunzig, and
# Grunzig's initial, birth and death.
#
#   THE RELEASE WAS 30 APRIL 1920, NOT 1 MAY. Both records carried 1 May.
#   They are already tied to each other -- Grunzig's own description says
#   he was released the same day as David Eichel -- so the correction has
#   to move on both or on neither.
#
#     David Eichel    16 July 1919  ->  30 April 1920      289 days
#     Bruno Grunzig   August 1918   ->  30 April 1920      638 days
#
#   GRUNZIG ALSO GETS his middle initial F., a birth date of 13 January
#   1896 and a death date of March 1965, the last at month precision so
#   the page says March 1965 rather than asserting the first.
#
#   THREE PIECES OF PROSE STILL SAY MAY 1 and are deliberately left alone:
#   Eichel's biography, Eichel's case sentence text, and Grunzig's case
#   sentence text, which reads "Released 1920 (May 1)". They now
#   contradict the dates beside them. Rewriting prose nobody asked about
#   has gone wrong on this branch before, so they are flagged instead --
#   a one-line follow-up on each if wanted.
#
#   THIS SHORTENS EICHEL'S RECORDED IMPRISONMENT from 851 days to 289.
#   His case previously held a bare 1918, and his biography describes
#   custody running through the Tombs, Camp Upton, Fort Riley, Fort
#   Leavenworth and Fort Douglas -- an arc starting well before July 1919.
#   The supplied dates look like the Fort Douglas confinement rather than
#   his whole time inside. Applied as given and flagged rather than
#   reconciled, because which reading is right is a curator call.
#
#   NEW YORK CITY AND AVENEL CANNOT BE STORED. The prisoner table has a
#   state column but no birthplace and no place of death, and state is for
#   the case. Grunzig's state stays empty rather than being guessed at:
#   born in New York, sent toward Camp Meade in Maryland, held at Fort
#   Douglas in Utah, died in New Jersey.
#
#   THE PHOTOGRAPH is the Swarthmore "Big Four", Fort Douglas, 18 October
#   1919. Grunzig is the leftmost of the four, standing raised above the
#   others. Cropped to him alone and filed nonfree: the collection states
#   that copyright is retained by the authors of items in these papers.
#
#   Idempotent: every field is written only when it differs.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-228.sh

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
echo "  Batch 228 — Eichel and Grunzig confinement dates"
echo "==================================================================="

SRC="database/data/photos/nonfree/bruno-grunzig.jpg"
DEST_DIR="storage/app/public/prisoners"

echo
echo "--- install-photo"
install_ok=1
mkdir -p "$DEST_DIR"
if [ ! -f "$SRC" ]; then
    echo "  missing source file: $SRC"; install_ok=0
else
    dest="$DEST_DIR/bruno-grunzig.jpg"
    if [ -f "$dest" ] && cmp -s "$SRC" "$dest"; then
        echo "  bruno-grunzig.jpg — already installed, identical"
    else
        cp "$SRC" "$dest"
        echo "  bruno-grunzig.jpg — $(stat -c%s "$dest") bytes installed"
    fi
    if [ ! -e "public/storage/prisoners/bruno-grunzig.jpg" ]; then
        echo "  !! not reachable through the public symlink — run php artisan storage:link"
        install_ok=0
    fi
fi
[ "$install_ok" -eq 1 ] || FAILED+=("install-photo")

DATES_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch228.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$bad = [];

foreach ($payload["people"] as $e) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $p) { echo "  !! no prisoner at slug ", $e["slug"], "\n"; $bad[] = $e["slug"]; continue; }

    if ($p->name !== $e["expect_name"]) {
        echo "  !! ", $e["slug"], " holds ", $p->name, " — skipping\n";
        $bad[] = $e["slug"];

        continue;
    }

    echo "\n  ", $p->name, "  [/prisoner/", $p->slug, "]\n";

    // Prisoner-level fields, where the payload carries any.
    foreach (($e["prisoner"] ?? []) as $field => $value) {
        $was = $p->{$field};
        $wasStr = $was instanceof \DateTimeInterface ? $was->format("Y-m-d") : (string) $was;

        if ($wasStr !== $value) {
            $p->{$field} = $value;
            echo "    ", str_pad($field, 20), " ", str_pad($wasStr ?: "(none)", 12), " -> ", $value, "\n";
        }
    }

    foreach (($e["prisoner_precision"] ?? []) as $field => $prec) {
        if (($p->date_precision[$field] ?? null) !== $prec) {
            $p->date_precision = array_merge($p->date_precision ?? [], [$field => $prec]);
            echo "    ", str_pad($field." precision", 20), " -> ", $prec, "\n";
        }
    }

    $p->save();
    $p->refresh();

    $case = $p->cases()->first();

    if (! $case) { echo "    !! no case row\n"; $bad[] = $e["slug"]." no case"; continue; }

    foreach ($e["fields"] as $field => $value) {
        $was = optional($case->{$field})->toDateString() ?: "(none)";

        if ($was !== $value) {
            $case->{$field} = $value;
            echo "    ", str_pad($field, 20), " ", str_pad($was, 12), " -> ", $value, "\n";
        }
    }

    foreach ($e["precision"] as $field => $prec) {
        if (($case->date_precision[$field] ?? null) !== $prec) {
            $case->date_precision = array_merge($case->date_precision ?? [], [$field => $prec]);
        }
    }

    $case->save();
    $case->refresh();

    echo "    incarcerated  ", $case->formatPartialDate("incarceration_date"), "\n";
    echo "    released      ", $case->formatPartialDate("release_date"), "\n";
    echo "    duration      ", $case->imprisoned_for_days, " days\n";

    if ($p->birthdate) { echo "    born          ", $p->formatPartialDate("birthdate"), "\n"; }
    if ($p->death_date) { echo "    died          ", $p->formatPartialDate("death_date"), "\n"; }
    if ($p->middle_name) { echo "    middle name   ", $p->middle_name, "   (name still reads ", $p->name, ")\n"; }

    echo "    flags         in_custody ", var_export((bool) $p->in_custody, true),
        "   released ", var_export((bool) $p->released, true), "   (untouched)\n";

    // The prose that still says May 1, printed so the contradiction is seen
    // rather than described.
    if (str_contains((string) $case->sentence, "May 1")) {
        echo "    !! case sentence text still says May 1: ", mb_substr((string) $case->sentence, 0, 72), "\n";
    }

    if (str_contains((string) $p->description, "May 1")) {
        echo "    !! biography still says May 1\n";
    }

    if ((int) $case->imprisoned_for_days !== (int) $e["days"]) {
        $bad[] = $e["slug"]." duration is ".$case->imprisoned_for_days.", expected ".$e["days"];
    }

    foreach ($e["fields"] as $field => $value) {
        if (optional($case->{$field})->toDateString() !== $value) { $bad[] = $e["slug"]." ".$field; }
    }
}

// The photograph, attached after the date work so a failure here is
// distinguishable from a failure there.
$ph = $payload["photo"];
$gp = Prisoner::withoutGlobalScopes()->where("slug", $ph["slug"])->first();

if ($gp && Storage::disk("public")->exists($ph["to"])) {
    $wasPhoto = $gp->photo ?: "(none)";

    if ($gp->photo !== $ph["to"]) {
        $gp->photo = $ph["to"];
        $gp->save();
        $gp->refresh();
    }

    $credits = File::get(base_path("database/data/photos/CREDITS-nonfree.md"));
    $credited = str_contains($credits, "`".$ph["file"]."`");

    if (! $credited) { $bad[] = "grunzig photo not credited"; }

    echo "\n  photo         ", $wasPhoto, "  ->  ", $gp->photo, "   ",
        number_format(Storage::disk("public")->size($gp->photo) / 1024, 1), " KB   ",
        ($credited ? "credited" : "NOT CREDITED"), "\n";
    echo "    from        ", $ph["source"], "\n";
    echo "    position    ", $ph["position"], "\n";
} else {
    $bad[] = "grunzig photo not attached";
}

echo "\n  problems: ", count($bad), "\n";

foreach ($bad as $b) { echo "    !! ", $b, "\n"; }

echo "\n  ", wordwrap($payload["flag_may_first"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["flag_eichel_span"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["flag_places"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["photo_note"], 72, "\n  "), "\n";

if (count($bad) === 0) { echo "\nB228-OK\n"; }
'

run_tinker "set-eichel-grunzig-dates" "B228-OK" "$DATES_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 228 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
