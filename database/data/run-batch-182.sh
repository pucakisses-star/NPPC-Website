#!/usr/bin/env bash
#
# BATCH 182 -- Henry Maki, chained to a pole in Telluride, March 2, 1904.
#
#   A NEW RECORD. He was not in this archive, which is a strange gap: the
#   photograph of him handcuffed around that pole is one of the most
#   reproduced images in American labour history, and the archive already
#   holds the Telluride and Idaho men around him — Moyer, Haywood,
#   Pettibone, Steve Adams, Annie Adams.
#
#   WHAT WAS DONE TO HIM. Thirty-four striking miners were swept up for
#   vagrancy; twenty-seven were convicted and offered three choices — pay
#   $25 and costs, leave San Miguel County, or work under the sheriff.
#   The men produced $1,148.25 between them in open court and had their
#   union behind them, which disposes of the claim that they were
#   destitute. Sixteen took the labour and were put on the municipal
#   sewers. Maki refused, and deputy sheriff Willard Runnels handcuffed
#   him around a telephone pole in high wind, blowing snow and near-zero
#   temperatures for at least ninety minutes, after which a union history
#   records he was refused food for thirty-six hours.
#
#   THE NAME IS SPLIT ON PURPOSE, at the curator's instruction. The
#   display name stays Henry Maki, which is how he is known and cited,
#   while first, middle and last hold the name he was born with — Henrik
#   Iivari Maentaka. The full birth form and Heikki also go in the aka
#   field so a search for either finds him.
#
#   FIVE DAYS, AND A DAY THAT IS NOT SETTLED. Arrest February 29, 1904;
#   conviction March 1; chained March 2; released March 5 on County Judge
#   J. M. Wardlaw's order. That is five days and it is what goes in. But
#   Emma Langdon's 1905 history of the strike puts the mass arrest AND the
#   justice-court proceedings both on March 1, which would make it four.
#   What settles the conviction date is a wire report of March 2 saying
#   the men had been convicted "yesterday". The conflict is recorded on
#   the case rather than quietly resolved, because a four-or-five-day
#   custody is exactly the kind of thing a later source can fix.
#
#   February 29, 1904 is a real date — 1904 was a leap year. Worth saying
#   because a February 29 in a non-leap year is the classic sign of a
#   mistranscribed record, and this one checks out.
#
#   NO INSTITUTION IS RECORDED, deliberately. The custody was at the
#   Telluride city jail or the San Miguel County jail and the sources do
#   not say which. "Telluride City Jail" already exists in this archive
#   from Charles Moyer's record, so picking it would have been one line
#   and might have been wrong.
#
#   THE PHOTOGRAPH IS USED WHOLE, caption and all. Unlike the Annie Adams
#   clipping in batch 181, there is one subject and nothing ambiguous to
#   crop away, and the line the union printed under it — UNDER THE FOLDS
#   OF THE AMERICAN FLAG IN COLORADO! — is part of the object rather than
#   a stranger's typography dropped on a mugshot. Resized from 952x1652
#   to 691x1200.
#
#   Idempotent: the record is created only when the name is absent and the
#   file is copied only when it differs.
#
# Run from the repo root, after git pull (after batch 181):
#   bash database/data/run-batch-182.sh

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
echo "  Batch 182 — Henry Maki, Telluride, 1904"
echo "==================================================================="

SRC="database/data/files/maki/henry-maki.jpg"
DEST_DIR="storage/app/public/prisoners"

install_photo() {
    mkdir -p "$DEST_DIR"
    local dest="$DEST_DIR/henry-maki.jpg"

    [ -f "$SRC" ] || { echo "  missing source file: $SRC"; return 1; }

    if [ -f "$dest" ] && cmp -s "$SRC" "$dest"; then
        echo "  henry-maki.jpg — already installed, identical"
    else
        [ -f "$dest" ] && echo "  henry-maki.jpg — $(stat -c%s "$dest") bytes -> $(stat -c%s "$SRC")" \
                       || echo "  henry-maki.jpg — new file"
        cp "$SRC" "$dest"
    fi

    [ -e "public/storage/prisoners/henry-maki.jpg" ] \
        || { echo "  !! not reachable through the public symlink — run php artisan storage:link"; return 1; }

    echo "  $(stat -c%s "$dest") bytes in place"
}

add_record() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch182.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$row = $payload["add"];

$p = Prisoner::withUnderReview()->where("name", $row["name"])->first();

if ($p) {
    echo "  ", $row["name"], " already exists [", $p->slug, "] — not created again.\n";
} else {
    $spec = $row;
    unset($spec["slug"]);
    Artisan::call("prisoner:add", ["json" => json_encode($spec)]);
    echo Artisan::output();

    $p = Prisoner::withUnderReview()->where("name", $row["name"])->first();
}

if (! $p) { echo "  the record was not created — nothing further to do.\n"; return; }

// prisoner:add takes no photo field, so the column is set here against the
// file the previous step installed.
if ($p->photo !== $payload["photo"]["path"]) {
    $p->photo = $payload["photo"]["path"];
    $p->save();
}

$p->refresh()->load("cases");

$e = $payload["expect"];

echo "\n  ", $p->name, "  [", $p->slug, "]\n";
echo "    name split   first ", $p->first_name, " · middle ", $p->middle_name,
    " · last ", $p->last_name, "\n";
echo "    aka          ", $p->aka, "\n";
echo "    born         ", $p->formatPartialDate("birthdate"), " [", $p->datePrecisionFor("birthdate"), "]\n";
echo "    died         ", $p->formatPartialDate("death_date"), " [", $p->datePrecisionFor("death_date"), "]\n";
echo "    age          ", ($p->age ?? "-"), "  (expected ", $e["age_at_death"], ")\n";
echo "    affiliation  ", implode(", ", $p->affiliation ?: []), "\n";

$case = $p->cases->first();

if (! $case) { echo "    NO CASE ROW\n"; return; }

foreach (["arrest_date", "incarceration_date", "sentenced_date", "release_date"] as $f) {
    echo "    ", str_pad($f, 13), ($case->{$f} ? $case->formatPartialDate($f) : "-"), "\n";
}

$days = (int) $case->imprisoned_for_days;

echo "    days         ", $days, "  (expected ", $e["days"], ")",
    ($days === (int) $e["days"] ? "" : "   !! MISMATCH"), "\n";
echo "                 Langdon dating would give ", $e["days_if_langdon"], "; the conflict is on the case row\n";
echo "    institution  ", ($case->institution ? $case->institution->name : "(none — deliberately)"), "\n";

$ok = $p->photo && Storage::disk("public")->exists($p->photo);

echo "    photo        ", $p->photo, "  ",
    ($ok ? Storage::disk("public")->size($p->photo)." bytes" : "MISSING ON DISK"), "\n";

echo "\n  on the institution:\n    ", wordwrap($payload["institution_note"], 70, "\n    "), "\n";
echo "\n  on the photograph:\n    ", wordwrap($payload["photo"]["note"], 70, "\n    "), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "install-photo" install_photo
run "add-record"    add_record

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 182 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "He was jailed five days for having no job during a strike his union"
echo "was funding, and the picture of what was done to him outlasted"
echo "everyone who did it."
