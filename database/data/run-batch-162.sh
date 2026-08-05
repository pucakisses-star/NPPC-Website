#!/usr/bin/env bash
#
# BATCH 162 -- Clayton Van Lydegraf: corrected research, and a
# photograph cropped from a two-man press picture.
#
#   THE PHOTOGRAPH. HistoryLink's picture of the University of
#   Washington Board of Regents hearing of January 23, 1949 shows two
#   men: Professor Herbert J. Phillips on the left and Van Lydegraf on
#   the right. Cropped to him alone, 1024x1075 down to 469x740, about
#   64 KB.
#
#   THE INSTITUTION WAS WRONG. The row recorded the Federal Bureau of
#   Prisons. The federal charges were dismissed under speedy-trial
#   requirements and California went on holding all five on state
#   charges: he was in the Los Angeles County Central Jail as prisoner
#   4614177.
#
#   THE AFFILIATION WAS TOO NARROW. He is listed simply as Weather
#   Underground, but he had been expelled from the principal Weather
#   organisation around 1974. At the time of this arrest he belonged
#   to the Prairie Fire Organizing Committee and the Revolutionary
#   Committee faction. All six affiliations are recorded, and the
#   biography explains which one is right for 1977.
#
#   THE COUNTER. Arrest and custody both November 19, 1977; release
#   1980 at year precision, no day being established. That measures
#   773 days — about two years and a month — from the arrest to
#   January 1, 1980, which is the earliest the year allows and so the
#   least the custody can have been. The curator gives approximately
#   two years, which agrees.
#
#   ON THE ARREST DATE: the strongest contemporaneous source gives
#   Saturday November 19. Later histories sometimes give the 20th,
#   apparently because the arrest photographs ran in the Sunday,
#   November 20 Houston Post. Both are in the biography.
#
#   Idempotent.
#
# Run from the repo root, after git pull (after batch 161):
#   bash database/data/run-batch-162.sh

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
echo "  Batch 162 — Clayton Van Lydegraf"
echo "==================================================================="

FILE="clayton-van-lydegraf.jpg"
SRC="database/data/files/${FILE}"
DEST="storage/app/public/prisoners/${FILE}"

install_photo() {
    if [ ! -f "$SRC" ]; then echo "  source missing: $SRC"; return 1; fi
    mkdir -p storage/app/public/prisoners
    if [ -f "$DEST" ] && cmp -s "$SRC" "$DEST"; then echo "  already installed, identical"; else cp "$SRC" "$DEST"; echo "  installed: $DEST"; fi
    ls -l "$DEST"
    head -c 2 "$DEST" | od -An -tx1 | grep -q 'ff d8' && echo "  header check: JPEG" || { echo "  !! not a JPEG"; return 1; }
    [ -e "public/storage/prisoners/${FILE}" ] && echo "  reachable through the public symlink" \
        || { echo "  !! NOT reachable — run php artisan storage:link"; return 1; }
}

update_record() {
    php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch162.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$d = fn ($v) => $v ? $v->format("Y-m-d") : "----------";

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases")->first();

if (! $p) { echo "  ", $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

$pf = $payload["prisoner"];

echo "  ", $p->name, "\n";
echo "  photo before: ", ($p->photo ?: "(none)"), "\n";

$p->photo = $payload["photo_path"];

foreach (["description", "inmate_number", "state", "affiliation"] as $f) {
    if (array_key_exists($f, $pf)) { $p->{$f} = $pf[$f]; }
}

foreach (["birthdate", "death_date"] as $f) {
    if (! empty($pf[$f])) { $p->setPartialDate($f, $pf[$f][0], $pf[$f][1] ?? null, $pf[$f][2] ?? null); }
}

$p->save();
$p->refresh();

echo "  photo after:  ", $p->photo, "\n";
echo "  born ", $p->formatPartialDate("birthdate"), "   died ", $p->formatPartialDate("death_date"),
    "   age ", ($p->age ?: "-"), "\n";
echo "  inmate ", $p->inmate_number, "\n";
echo "  affiliations: ", implode(", ", $p->affiliation ?: []), "\n";

$cu = $payload["case"];
$case = $p->cases->first();

if (! $case) { echo "  no case row — nothing else to do\n"; return; }

if ($case->institution && $case->institution->name === $cu["detach_institution"]) {
    echo "  detaching wrong institution: ", $case->institution->name, "\n";
    $case->institution_id = null;
}

if (! $case->institution_id) {
    $inst = Institution::firstOrCreate(["name" => $cu["institution"]["name"]],
        ["city" => $cu["institution"]["city"], "state" => $cu["institution"]["state"]]);
    $case->institution_id = $inst->id;
    echo "  institution: ", $inst->name, ($inst->wasRecentlyCreated ? " (created)" : " (existing)"), "\n";
}

$case->charges = $cu["charges"];
$case->convicted = $cu["convicted"];
$case->sentence = $cu["sentence"];

foreach ($cu["dates"] as $f => $parts) {
    $case->setPartialDate($f, $parts[0], $parts[1] ?? null, $parts[2] ?? null);
}

$case->save();
$case->refresh();

echo "\n  case: arrest=", $d($case->arrest_date), " in=", $d($case->incarceration_date),
    " out=", $case->formatPartialDate("release_date"), " [", $case->datePrecisionFor("release_date"), "]\n";
echo "  days = ", ($case->imprisoned_for_days ?? "null"), "\n";

$p->refresh()->load("cases");

$total = (int) $p->cases->sum("imprisoned_for_days");
$start = $p->cases->map(fn ($c) => $c->incarceration_date ?: $c->arrest_date)->filter()->sort()->first();

echo "  counter: ", ($total > 0
    ? \App\Support\ImprisonmentDuration::phrase($start, $total,
        \App\Support\ImprisonmentDuration::documentedMonths($p->cases))
    : "(none)"), "\n";
echo "  ", wordwrap("773 days is measured to January 1, 1980, the earliest the year allows, so it is the "
    ."least the custody can have been. The curator gives approximately two years.", 84, "\n  "), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "install-photo" install_photo
run "update-record" update_record

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then echo "  Batch 162 applied. No failures."
else echo "  Finished with ${#FAILED[@]} failed step(s):"; for f in "${FAILED[@]}"; do echo "    - ${f}"; done; fi
echo "==================================================================="
