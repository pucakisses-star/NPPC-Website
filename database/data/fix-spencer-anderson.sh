#!/usr/bin/env bash
#
# SPENCER ANDERSON: booking photo attached, record marked UNDER REVIEW.
#
# From the curator's verified findings on the Waterford Township ALPR
# case:
#
#   THE PHOTOGRAPH is the booking photo FOX 2 Detroit published,
#   supplied by Waterford Police; WDIV credits the same image to the
#   Oakland County Jail. Two outlets, both crediting law-enforcement
#   sources, both naming him — a high-confidence identification. The
#   FOX 2 web crop letterboxes the portrait; the gray bars are cropped
#   off and the panel sized to the 525x700 convention.
#
#   UNDER REVIEW, because the record cannot currently be verified as a
#   record of incarceration. Anderson was arrested February 26, 2026,
#   arraigned the next day before Judge Todd Fox in the 51st District
#   Court, and released on a $500 cash bond — at most one night of
#   arrest-and-booking custody, with NO evidence of a jail sentence or
#   pretrial detention beyond it. The scheduled March 11 probable-cause
#   conference and March 18 preliminary examination were scheduled
#   dates only; proceedings appear to have been rescheduled and no
#   reliable docket establishes the current disposition. The
#   under_review flag hides him from the public site while the case
#   resolves; Filament still shows him.
#
#   THE CUSTODY SPAN goes in as what the record supports: arrest and
#   incarceration February 26, release February 27 — one day, which
#   also earns the minor_case duration flag (the admin-side filter
#   from the batch 42 sweep; a duration statement, not a significance
#   judgment).
#
#   THE BIOGRAPHY is the curator's recommended text verbatim, which
#   also fixes two things the old bio asserted too strongly: the
#   stacked "up to 12 years" maximum (now stated per count, as police
#   put it) and the supporters-frame-it-as-civil-disobedience claim —
#   no statement from Anderson himself documents an anti-surveillance
#   motive, so the bio now says the motive is not conclusively
#   established. The Anti-Surveillance ideology tag stays as a
#   plausible contextual category, with the bio carrying the caveat.
#
#   AGE 25 -> 24, as reported at arrest; no birthdate is stored.
#
# The prose carries apostrophes, so the payload lives in
# database/data/fixes/spencer-anderson-review.json.
#
# Idempotent: matched by slug through withUnderReview so a re-run
# still finds him after the flag is set; every field compared before
# writing.
#
# Run from the repo root:
#   bash database/data/fix-spencer-anderson.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

if [ -f "database/data/photos/spencer-anderson.jpg" ]; then
    cp -f "database/data/photos/spencer-anderson.jpg" "${DST_DIR}/spencer-anderson.jpg"
    echo "copied spencer-anderson.jpg"
fi

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$row = json_decode(File::get(base_path("database/data/fixes/spencer-anderson-review.json")), true);

if (! $row) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

if (! $p) {
    echo "NOT FOUND: ", $row["slug"], "\n";
    return;
}

echo $p->slug, "\n";

$notes = [];

foreach (["under_review", "age", "released", "minor_case", "description"] as $field) {
    if ($p->{$field} != $row[$field]) {
        $p->{$field} = $row[$field];
        $notes[] = $field.(is_bool($row[$field]) ? "=".($row[$field] ? "true" : "false") : "");
    }
}

$rel = "prisoners/".$row["slug"].".jpg";
if (is_file(storage_path("app/public/".$rel)) && $p->photo !== $rel) {
    $p->photo = $rel;
    $notes[] = "photo attached";
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

    $spec = $row["case"];
    $caseNotes = [];

    foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date", "release" => "release_date"] as $k => $field) {
        if (! array_key_exists($k, $spec)) {
            continue;
        }
        [$y, $m, $d] = array_pad($spec[$k], 3, null);
        $was = $case->{$field} ? $case->{$field}->format("Y-m-d") : null;
        $case->setPartialDate($field, $y, $m, $d);
        if ($was !== ($case->{$field} ? $case->{$field}->format("Y-m-d") : null)) {
            $caseNotes[] = $field."=".$case->{$field}->format("Y-m-d");
        }
    }

    foreach (["judge", "convicted", "sentence"] as $field) {
        if (array_key_exists($field, $spec) && $case->{$field} != $spec[$field]) {
            $case->{$field} = $spec[$field];
            $caseNotes[] = $field;
        }
    }

    if ($caseNotes) {
        $case->save();
    }

    echo "  case: ", ($caseNotes ? implode("; ", $caseNotes) : "already correct"),
         "   days=", ($case->imprisoned_for_days === null ? "null" : $case->imprisoned_for_days), "\n";
}

echo "  public visibility: ", ($p->under_review ? "HIDDEN (under review)" : "visible"), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
