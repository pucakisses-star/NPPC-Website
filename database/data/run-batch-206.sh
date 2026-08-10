#!/usr/bin/env bash
#
# BATCH 206 -- Audrey Faye Hendricks, Birmingham, May 1963.
#
#   NINE YEARS OLD, and the youngest known person jailed in the American
#   civil rights movement. Arrested on May 2, 1963, the first day of the
#   Children's Crusade, after walking out of Center Street Elementary
#   alone -- the only child from her school -- and marching from the
#   Sixteenth Street Baptist Church. Charged with parading without a
#   permit. Released with about five hundred other children on May 8.
#
#   BIRMINGHAM CITY JAIL, WHICH ALREADY EXISTS HERE with eighteen cases,
#   checked before writing. AddPrisoner matches institutions on name
#   alone, so passing that exact name reuses the existing record and puts
#   her on the same institution page as the other Birmingham prisoners,
#   instead of minting an 813th institution called Birmingham Juvenile
#   Hall. The Juvenile Hall detail lives in the charges text, where it
#   belongs.
#
#   1953 AT YEAR PRECISION, so the page renders 1953 rather than a
#   January day nobody claims. No source gives her exact birth date --
#   the museum heads the biography "1953 - 2009", and Wikipedia's own
#   infobox computes her age as 55-56, which is what it does when it has
#   only a year. The stored 1953-01-01 is a carrier for the year; the
#   precision flag is what stops the page asserting a day. Her death date
#   is exact: March 1, 2009.
#
#   SIX DAYS OR SEVEN. Six is the arithmetic between the two dates
#   supplied, and the National Civil Rights Museum says six. Some
#   accounts say seven, which is the same span counted inclusively. There
#   is no conflict: the record stores the dates and lets the duration
#   follow.
#
#   THE PHOTOGRAPH is the National Women's History Museum portrait, taken
#   from their 600x600 original rather than the 300x300 derivative the
#   page displays -- the same trick that got Packard a better scan in
#   batch 205. It is a later-life colour photograph, not the 1963 child;
#   she died in 2009. Copyrighted, so it goes in photos/nonfree/ with a
#   credits row, on the same basis as the other Birmingham movement
#   portraits already there: Shuttlesworth, Billups, Woods.
#
#   Idempotent: prisoner:add refuses on a duplicate name; the photo and
#   precision steps are safe to repeat.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-206.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

# tinker exits 0 even when the code inside throws; success is a sentinel
# the step prints as its last act.
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
echo "  Batch 206 — Audrey Faye Hendricks"
echo "==================================================================="

SRC="database/data/photos/nonfree/audrey-faye-hendricks.jpg"
DEST_DIR="storage/app/public/prisoners"

echo
echo "--- install-photo"
install_ok=1
mkdir -p "$DEST_DIR"
if [ ! -f "$SRC" ]; then
    echo "  missing source file: $SRC"; install_ok=0
else
    dest="$DEST_DIR/audrey-faye-hendricks.jpg"
    if [ -f "$dest" ] && cmp -s "$SRC" "$dest"; then
        echo "  audrey-faye-hendricks.jpg — already installed, identical"
    else
        cp "$SRC" "$dest"
        echo "  audrey-faye-hendricks.jpg — $(stat -c%s "$dest") bytes installed"
    fi
    if [ ! -e "public/storage/prisoners/audrey-faye-hendricks.jpg" ]; then
        echo "  !! not reachable through the public symlink — run php artisan storage:link"
        install_ok=0
    fi
fi
[ "$install_ok" -eq 1 ] || FAILED+=("install-photo")

ADD_CODE='
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch206.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = $payload["prisoner"];

// How many Birmingham City Jail records exist before this runs; there should
// still be exactly one afterwards.
$instBefore = Institution::where("name", $payload["expected"]["institution"])->count();

$prisoner = Prisoner::withoutGlobalScopes()->where("name", $p["name"])->first();

if ($prisoner) {
    echo "  ", $p["name"], " already exists [", $prisoner->slug, "] — not created again.\n";
} else {
    Artisan::call("prisoner:add", ["json" => json_encode($p)]);
    echo Artisan::output();

    $prisoner = Prisoner::withoutGlobalScopes()->where("name", $p["name"])->first();
}

if (! $prisoner) { echo "  !! prisoner was not created — stopping.\n"; return; }

// 1953, not the first of January.
if (($prisoner->date_precision["birthdate"] ?? null) !== "year") {
    $prisoner->date_precision = array_merge($prisoner->date_precision ?? [], ["birthdate" => "year"]);
    $prisoner->save();
    $prisoner->refresh();
    echo "  birthdate set to year precision — renders as 1953\n";
}

$ph = $payload["photo"];

if (Storage::disk("public")->exists($ph["to"]) && $prisoner->photo !== $ph["to"]) {
    $prisoner->photo = $ph["to"];
    $prisoner->save();
    $prisoner->refresh();
}

$prisoner->load("cases.institution");
$case = $prisoner->cases->first();
$onDisk = $prisoner->photo && Storage::disk("public")->exists($prisoner->photo);

echo "\n  ", $prisoner->name, "  [/prisoner/", $prisoner->slug, "]\n";
echo "    born         ", $prisoner->formatPartialDate("birthdate"), "   died ", $prisoner->formatPartialDate("death_date"), "\n";
echo "    era / state  ", $prisoner->era, "   ", $prisoner->state, "\n";
echo "    ideologies   ", implode(", ", (array) $prisoner->ideologies), "\n";
echo "    sort_order   ", $prisoner->sort_order, "\n";
echo "    photo        ", ($prisoner->photo ?: "(none)"), "  ",
    ($onDisk ? Storage::disk("public")->size($prisoner->photo)." bytes" : "MISSING ON DISK"), "\n";

if ($case) {
    echo "    institution  ", $case->institution?->name, " — ", $case->institution?->city, ", ", $case->institution?->state, "\n";
    echo "    arrested     ", optional($case->arrest_date)->toDateString(), "\n";
    echo "    confined     ", optional($case->incarceration_date)->toDateString(), " -> ",
        optional($case->release_date)->toDateString(), "   ", $case->imprisoned_for_days, " days\n";
}

$instAfter = Institution::where("name", $payload["expected"]["institution"])->count();

// Institution has no relations defined, so the case count is asked of
// PrisonerCase rather than of the institution.
$instCases = $case && $case->institution
    ? PrisonerCase::where("institution_id", $case->institution->id)->count()
    : 0;

echo "\n    cases now at that institution: ", $instCases, "\n";
echo "    Birmingham City Jail records: ", $instBefore, " before, ", $instAfter, " after",
    ($instAfter === 1 ? "  (reused, not duplicated)" : "   !! SHOULD BE ONE"), "\n";

$credits = File::get(base_path("database/data/photos/CREDITS-nonfree.md"));
$credited = str_contains($credits, "`".$ph["file"]."`");

echo "    credited in CREDITS-nonfree.md: ", ($credited ? "yes" : "NO — the rights record is missing"), "\n";

echo "\n  ", wordwrap($payload["birthdate_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["institution_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["six_or_seven_note"], 72, "\n  "), "\n";

$ok = $onDisk
    && $credited
    && $instAfter === 1
    && $case
    && (int) $case->imprisoned_for_days === (int) $payload["expected"]["days"]
    && ($prisoner->date_precision["birthdate"] ?? null) === "year"
    && $prisoner->sort_order > 0;

if ($ok) { echo "\nB206-OK\n"; }
'

run_tinker "add-hendricks" "B206-OK" "$ADD_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 206 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
