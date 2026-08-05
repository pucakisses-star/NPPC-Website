#!/usr/bin/env bash
#
# BATCH 180 -- Clara Lemlich: the Wikipedia biography, a photograph, and a
# counter that says she served 117 years.
#
#   SHE WAS ALREADY HERE, as a four-sentence record with no dates, no
#   photograph, no gender and no affiliations — and one number that is
#   badly wrong.
#
#   HER PROFILE PUBLISHES "IMPRISONED FOR 117 YEARS 7 MONTHS 2 DAYS."
#   The case row holds incarceration_date 1909, no release date, and a
#   stored day count of 42,948 — 1909 measured to roughly the present.
#   This is the stale-counter shape DURATION-SWEEP.md describes, and it is
#   on a public page.
#
#   THE FIX IS NOT JUST TO RECOMPUTE IT. computeImprisonedForDays()
#   already returns null for a released prisoner with no release date, so
#   a recompute alone would clear the number and leave the cause in place:
#   a bare incarceration_date with no end, waiting to be counted again.
#   The year moves to arrest_date instead, which is what the source
#   actually supports — she was arrested about seventeen times and no
#   custody chronology is recorded. An arrest with no recorded end is not
#   a sentence, and this archive does not count it as one.
#
#   THE BIOGRAPHY comes from the Wikipedia article the curator supplied.
#   Born March 28, 1886 in Gorodok, then Russian Empire and now Ukraine;
#   died July 12, 1982 aged 96. ILGWU Local 25 executive board, Communist
#   Party USA, the United Council of Working-Class Women, the Progressive
#   Women's Councils, the Emma Lazarus Federation.
#
#   WHAT THE ARTICLE DOES NOT SAY, and so is not added: it records no
#   arrests at all. The seventeen already on this record came from an
#   earlier source and are left as they were, neither corroborated nor
#   contradicted by this one. What Wikipedia does add to the custody
#   picture is not police: employers hired gangsters who attacked the
#   picket line and broke several of her ribs, and she went back to it.
#
#   AND THE STATE TOOK HER PASSPORT after a 1951 trip to the Soviet
#   Union. That belongs on a political-prisoner record even though it is
#   not a jail term, and it was missing.
#
#   THE PHOTOGRAPH. The curator supplied a 220x300 Google thumbnail. It
#   is a crop of a studio portrait of about 1910 held by the ILGWU
#   Archives in the Kheel Center at Cornell, which is on Wikimedia
#   Commons at 4639x6448 and in the public domain. So the same picture
#   goes in at 733x1000, cropped to the same head-and-shoulders framing
#   the curator chose — eleven times the pixels, a real credit line, and
#   no fair-use argument needed.
#
#   THE TWO WERE CHECKED AGAINST EACH OTHER, not assumed to match. A
#   numeric comparison said they were unrelated, scoring 0.11 where 1.0 is
#   identical, because one is a tight crop of the other and almost none
#   of the frame is shared. They were then put side by side and looked at,
#   which settled it: same face, same hair, same shirtwaist, same pose.
#   The number was measuring framing, not identity.
#
#   Idempotent: the file is copied only when it differs, the dates and
#   fields are fixed values, and the case fix is a no-op once applied.
#
# Run from the repo root, after git pull (after batch 179):
#   bash database/data/run-batch-180.sh

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
echo "  Batch 180 — Clara Lemlich"
echo "==================================================================="

SRC="database/data/files/lemlich/clara-lemlich.jpg"
DEST_DIR="storage/app/public/prisoners"

install_photo() {
    mkdir -p "$DEST_DIR"
    local dest="$DEST_DIR/clara-lemlich.jpg"

    [ -f "$SRC" ] || { echo "  missing source file: $SRC"; return 1; }

    if [ -f "$dest" ] && cmp -s "$SRC" "$dest"; then
        echo "  clara-lemlich.jpg — already installed, identical"
    else
        [ -f "$dest" ] && echo "  clara-lemlich.jpg — $(stat -c%s "$dest") bytes -> $(stat -c%s "$SRC")" \
                       || echo "  clara-lemlich.jpg — new file (the record had no photograph)"
        cp "$SRC" "$dest"
    fi

    [ -e "public/storage/prisoners/clara-lemlich.jpg" ] \
        || { echo "  !! not reachable through the public symlink — run php artisan storage:link"; return 1; }

    echo "  $(stat -c%s "$dest") bytes in place"
}

apply_record() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch180.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases")->first();

if (! $p) { echo "  ", $payload["slug"], " not found — nothing changed.\n"; return; }

echo "  source: ", $payload["source"]["text"], "\n\n";

foreach ($payload["fields"] as $f => $v) { $p->{$f} = $v; }

foreach ($payload["dates"] as $f => $d) {
    $p->setPartialDate($f, $d[0], $d[1] ?? null, $d[2] ?? null);
}

// The photo column was empty; the file is installed at this path by the step
// before this one.
$photo = $payload["photo"];

if ($p->photo !== $photo["path"]) { $p->photo = $photo["path"]; }

$p->save();
$p->refresh();

echo "  ", $p->name, "\n";
echo "    aka         ", $p->aka, "\n";
echo "    gender      ", $p->gender, "\n";
echo "    born        ", $p->formatPartialDate("birthdate"), " [", $p->datePrecisionFor("birthdate"), "]\n";
echo "    died        ", $p->formatPartialDate("death_date"), " [", $p->datePrecisionFor("death_date"), "]\n";
echo "    age         ", ($p->age ?? "-"), "\n";
echo "    ideologies  ", implode(", ", $p->ideologies ?: []), "\n";
echo "    affiliation ", implode("; ", $p->affiliation ?: []), "\n";
echo "    description ", mb_strlen((string) $p->description), " characters\n";

$ok = $p->photo && Storage::disk("public")->exists($p->photo);

echo "    photo       ", $p->photo, "  ",
    ($ok ? Storage::disk("public")->size($p->photo)." bytes" : "MISSING ON DISK"), "\n";
echo "                ", wordwrap($payload["source"]["photo"], 60, "\n                "), "\n";
'
}

fix_counter() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch180.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases")->first();

if (! $p || $p->cases->isEmpty()) { echo "  no case row — nothing to fix.\n"; return; }

$fix = $payload["case_fix"];
$case = $p->cases->first();

echo "  before:\n";
echo "    incarceration_date  ", ($case->incarceration_date ? $case->formatPartialDate("incarceration_date") : "-"), "\n";
echo "    arrest_date         ", ($case->arrest_date ? $case->formatPartialDate("arrest_date") : "-"), "\n";
echo "    imprisoned_for_days ", ($case->imprisoned_for_days ?? "null"), "\n";
echo "    public profile said ", $fix["publishes_now"], "\n";

// The year is what the source supports — an arrest, not a term. Moving it off
// incarceration_date is what stops this being recounted later; clearing the
// day count alone would leave the cause in place.
if ($case->incarceration_date && ! $case->arrest_date) {
    $case->setPartialDate("arrest_date", $fix["arrest_year"]);
}

$case->setPartialDate("incarceration_date", null);
$case->sentence = $fix["sentence"];
$case->save();
$case->refresh();

echo "\n  after:\n";
echo "    incarceration_date  ", ($case->incarceration_date ? $case->formatPartialDate("incarceration_date") : "-"), "\n";
echo "    arrest_date         ", ($case->arrest_date ? $case->formatPartialDate("arrest_date") : "-"),
    " [", $case->datePrecisionFor("arrest_date"), "]\n";
echo "    imprisoned_for_days ", ($case->imprisoned_for_days ?? "null"), "  (want null)\n";

$p->refresh()->load("cases");

// calculatePunishment() lives in the API controller and is private, so the
// published string is rebuilt here from the same two inputs it uses: the
// summed day count across the cases, and any documented months.
$days = (int) $p->cases->sum("imprisoned_for_days");
$months = \App\Support\ImprisonmentDuration::documentedMonths($p->cases);

echo "    summed days         ", $days, "\n";
echo "    profile now says    ", ($days > 0
    ? "Imprisoned For ".\App\Support\ImprisonmentDuration::phrase($case->incarceration_date ?? $case->arrest_date, $days, $months)
    : "(nothing — which is correct)"), "\n";

echo "\n", wordwrap("  ".$fix["why"], 74, "\n  "), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "install-photo" install_photo
run "apply-record"  apply_record
run "fix-counter"   fix_counter

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 180 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "One record gained a life, a face and a passport revocation, and lost"
echo "a prison sentence of 117 years it never served."
