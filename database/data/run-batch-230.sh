#!/usr/bin/env bash
#
# BATCH 230 -- Carolyn Hart gets a birth year and a face.
#
#   NEITHER WAS ON THE RECORD. She was already here as one of the
#   McKeesport, Pennsylvania anti-war "riot" defendants of 1937, sentenced
#   to two years at the Muncy women's reformatory, with no birth date and
#   no photograph.
#
#   THE PHOTOGRAPH IS THE ONE THAT RAN WITH HER RELEASE -- a single
#   full-length studio picture of her, captioned with her name, from a
#   page of The Pittsburgh Press of Friday 28 May 1937. There is no
#   identification to resolve here: one photograph, one person, captioned.
#   Cropped to head and shoulders and reduced to 400x447; the headline,
#   the caption and the article text are not reproduced.
#
#   FILED NON-FREE. Published in 1937, so its copyright turns on renewal,
#   which is not established here. Filed on that uncertainty rather than
#   assumed public domain by age -- the same call made for the Aurelio
#   Tolentino portrait -- and credited in CREDITS-nonfree.md.
#
#   1912 CONTRADICTS HER OWN DESCRIPTION, and the contradiction is left
#   standing rather than quietly resolved. The description calls her a
#   twenty-two-year-old in the 1937 case, which puts her birth around 1914
#   or 1915; 1912 would make her twenty-four or twenty-five that year. The
#   year was supplied and is applied as given, but one of the two is wrong
#   and only a source can settle which. The batch prints both so the
#   conflict is visible on the page rather than buried in a commit.
#
#   Stored at year precision, so the page renders 1912 and not a January
#   day nobody claims.
#
#   THE CASE DATES: 6 October 1936 to 28 May 1937, 234 days of a two-year
#   sentence. The release date is confirmed by the source itself -- the
#   newspaper page carrying her photograph is dated Friday 28 May 1937 and
#   its headline is that she has been freed by pardon.
#
#   THE ARREST DATE IS LEFT ALONE AND FLAGGED. The case row already holds
#   1937, which would fall after an incarceration beginning in October
#   1936. That bare year looks like the trial or the appeal rather than
#   the arrest, but guessing which is not something the record supports.
#
#   Idempotent: the file is copied only when absent or different, and the
#   fields written only when they differ.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-230.sh

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
echo "  Batch 230 — Carolyn Hart"
echo "==================================================================="

SRC="database/data/photos/nonfree/carolyn-hart.jpg"
DEST_DIR="storage/app/public/prisoners"

echo
echo "--- install-photo"
install_ok=1
mkdir -p "$DEST_DIR"
if [ ! -f "$SRC" ]; then
    echo "  missing source file: $SRC"; install_ok=0
else
    dest="$DEST_DIR/carolyn-hart.jpg"
    if [ -f "$dest" ] && cmp -s "$SRC" "$dest"; then
        echo "  carolyn-hart.jpg — already installed, identical"
    else
        cp "$SRC" "$dest"
        echo "  carolyn-hart.jpg — $(stat -c%s "$dest") bytes installed"
    fi
    if [ ! -e "public/storage/prisoners/carolyn-hart.jpg" ]; then
        echo "  !! not reachable through the public symlink — run php artisan storage:link"
        install_ok=0
    fi
fi
[ "$install_ok" -eq 1 ] || FAILED+=("install-photo")

HART_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch230.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withoutGlobalScopes()->where("slug", $payload["prisoner"]["slug"])->first();

if (! $p) { echo "  !! no prisoner at that slug\n"; return; }

if ($p->name !== $payload["prisoner"]["expect_name"]) {
    echo "  !! that slug holds ", $p->name, " — stopping.\n";

    return;
}

$b = $payload["birthdate"];
$wasBirth = optional($p->birthdate)->toDateString() ?: "(none)";

if ($wasBirth !== $b["value"]) {
    $p->birthdate = $b["value"];
    echo "  birthdate     ", $wasBirth, "  ->  ", $b["value"], "\n";
}

if (($p->date_precision["birthdate"] ?? null) !== $b["precision"]) {
    $p->date_precision = array_merge($p->date_precision ?? [], ["birthdate" => $b["precision"]]);
    echo "  precision     -> ", $b["precision"], "\n";
}

$ph = $payload["photo"];
$wasPhoto = $p->photo ?: "(none)";

if (Storage::disk("public")->exists($ph["to"]) && $p->photo !== $ph["to"]) {
    $p->photo = $ph["to"];
    echo "  photo         ", $wasPhoto, "  ->  ", $ph["to"], "\n";
}

$p->save();
$p->refresh();

// Case dates.
$c = $payload["case"];
$case = $p->cases()->first();

if ($case) {
    foreach ([["incarceration_date", "incarceration_precision"], ["release_date", "release_precision"]] as [$field, $precKey]) {
        $was = optional($case->{$field})->toDateString() ?: "(none)";

        if ($was !== $c[$field]) {
            $case->{$field} = $c[$field];
            echo "  ", str_pad($field, 20), " ", str_pad($was, 12), " -> ", $c[$field], "\n";
        }

        if (($case->date_precision[$field] ?? null) !== $c[$precKey]) {
            $case->date_precision = array_merge($case->date_precision ?? [], [$field => $c[$precKey]]);
        }
    }

    $case->save();
    $case->refresh();
}

$onDisk = $p->photo && Storage::disk("public")->exists($p->photo);
$credits = File::get(base_path("database/data/photos/CREDITS-nonfree.md"));
$credited = str_contains($credits, "`".$ph["file"]."`");

echo "\n  ", $p->name, "  [/prisoner/", $p->slug, "]\n";
echo "    born          ", $p->formatPartialDate("birthdate"), "\n";
echo "    photo         ", ($p->photo ?: "(none)"), "   ",
    ($onDisk ? number_format(Storage::disk("public")->size($p->photo) / 1024, 1)." KB" : "MISSING ON DISK"),
    "   ", ($credited ? "credited" : "NOT CREDITED"), "\n";
echo "    from          ", $ph["source"], "\n";
echo "    era / state   ", $p->era, "   ", $p->state, "   (untouched)\n";

if ($case) {
    echo "    incarcerated  ", $case->formatPartialDate("incarceration_date"), "\n";
    echo "    released      ", $case->formatPartialDate("release_date"), "\n";
    echo "    duration      ", $case->imprisoned_for_days, " days   (of a two-year sentence)\n";
    echo "    arrest date   ", ($case->arrest_date ? $case->formatPartialDate("arrest_date") : "(none)"),
        "   (untouched — see the note below)\n";
}

// The conflict, printed rather than described: the description is the other
// half of it and it is left exactly as it stands.
echo "\n  !! the description still reads:\n     ",
    wordwrap(mb_substr((string) $p->description, 0, 200), 68, "\n     "), "\n";

echo "\n  ", wordwrap($payload["age_conflict"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["case_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["arrest_conflict"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["rights"], 72, "\n  "), "\n";

$ok = optional($p->birthdate)->toDateString() === $payload["expected"]["birthdate"]
    && ($p->date_precision["birthdate"] ?? null) === $payload["expected"]["precision"]
    && $p->photo === $ph["to"]
    && $onDisk
    && $credited
    && $case
    && optional($case->incarceration_date)->toDateString() === $c["incarceration_date"]
    && optional($case->release_date)->toDateString() === $c["release_date"]
    && (int) $case->imprisoned_for_days === (int) $payload["expected"]["days"];

if ($ok) { echo "\nB230-OK\n"; }
'

run_tinker "carolyn-hart" "B230-OK" "$HART_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 230 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
