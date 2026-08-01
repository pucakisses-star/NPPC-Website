#!/usr/bin/env bash
#
# FIVE SIT-IN AND FREEDOM RIDE RECORDS, verified and corrected: Due,
# Chatham, Jenkins, Sullivan, Gaither.
#
# From the curator's verified dates and corrections:
#
# PATRICIA GLORIA STEPHENS DUE — renamed from Patricia Stephens to her
# canonical name (the slug regenerates; she was still Patricia
# Stephens during the jail-in, kept as the AKA). Birth December 9,
# 1939, Quincy, Florida; death February 7, 2012, Smyrna, Georgia. The
# custody record enters whole: arrested February 20, 1960 at the
# Tallahassee Woolworth sit-in, convicted March 17, entered the Leon
# County Jail AROUND March 18 (approximate precision), released
# May 5, 1960 — 49 days of a 60-day sentence. The biography is the
# curator-s database-ready text, which also renames the jail letter:
# "Letter from Leon County Jail" as scholarship generally has it,
# with Florida Memory-s catalog title noted. HER PHOTO IS REPLACED —
# the curator-s finding names the State Archives of Florida portrait
# DUE053 for this record; the previous image was a later-life press
# photograph.
#
# PRICE CHATHAM — born June 5, 1931 in New Gulf, TEXAS, per the
# Freedom Rides Museum; East Rockaway, Long Island was his residence,
# not his birthplace, and the biography now says so. Arrested June 2,
# 1961 at the Jackson Trailways terminal; Parchman; hunger strike of
# approximately twenty-four days; released AROUND July 11, 1961 —
# entered as probable (approximate precision), the discharge register
# still being needed for confirmation.
#
# ROBERT LEE JENKINS — renamed from Robert Jenkins to the
# authoritative form (slug regenerates). A 27-year-old St. Louis
# University student (CRDL-verified), so born about 1934 at circa
# precision. Arrested June 7, 1961 at the Jackson Municipal Airport.
# THE OLD RECORD ASSERTED TOO MUCH: the St. Louis CORE chairmanship
# is now stated as reported-not-confirmed, the acquittal-on-a-
# technicality as unresolved, and NO release date or institution is
# entered — his custody end is one of the dossier-s named open
# questions.
#
# TERRY SULLIVAN — THE AGE WAS WRONG: a contemporary press account
# called him 19 (the likely source of the error), but the Mississippi
# surveillance record identifies him as 23, with a Chicago address.
# Born about 1938 at circa precision; AKAs gain Terry John Sullivan
# and John Terry Sullivan. Arrested June 6, 1961; served his FULL
# four-month sentence at Parchman; release entered at MONTH precision
# (approximately October 1961, per Catholic Worker material on his
# return, no exact discharge day located). The well-supported abuse
# stays: with Felix Singer he was put in "wrist breakers" and shocked
# with an electric cattle prod for refusing orders.
#
# THOMAS GAITHER — birth November 12, 1938, Great Falls, South
# Carolina; death December 23, 2024, Prospect, Pennsylvania. THE OLD
# BIOGRAPHY HAD HIM AS ONE OF THE STUDENTS — he was the Claflin
# College graduate and CORE field secretary who ORGANIZED AND TRAINED
# the Friendship Junior College students. Ten men were arrested at
# McCrory-s on January 31, 1961; one paid his fine; Gaither and eight
# Friendship students served the hard-labor sentence and became the
# Friendship Nine. Custody to the day: convicted February 1, began at
# the York County Prison Farm February 2, released March 2, 1961 —
# two days before the nominal completion of the thirty-day term.
#
# PHOTOGRAPHS — provenance in CREDITS-civil-rights-five.md. Due:
# State Archives of Florida DUE053 (via the Wayback Machine; Florida
# Memory Cloudflare-blocks this environment). Chatham, Sullivan,
# Jenkins: the Mississippi State Sovereignty Commission arrest
# photographs the dossier names — Jackson police mugshot pairs whose
# placards read 20932/6-2-61, 20950/6-6-61 and 20964/6-7-61, matching
# the dossier-s arrest dates exactly; the frontal panels are used.
# Gaither: the lead photograph of his Times and Democrat obituary,
# young Gaither in his "JIM CROW MUST GO" tie.
#
# The prose carries apostrophes, so the payload lives in
# database/data/fixes/sixties-civil-rights-five.json.
#
# Idempotent: each person is matched by the old slug OR the new one,
# every field is compared before writing, photos re-copy harmlessly.
#
# Run from the repo root:
#   bash database/data/fix-civil-rights-five.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

for slug in patricia-gloria-stephens-due price-chatham robert-lee-jenkins terry-sullivan thomas-gaither; do
    SRC="database/data/photos/${slug}.jpg"
    if [ -f "$SRC" ]; then
        cp -f "$SRC" "${DST_DIR}/${slug}.jpg"
        echo "copied ${slug}.jpg"
    else
        echo "no source image for ${slug} — skipped (see CREDITS-civil-rights-five.md)"
    fi
done

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/sixties-civil-rights-five.json")), true);

if (! $payload || empty($payload["people"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$applyDate = function ($model, string $field, $spec): bool {
    if ($spec === null) {
        if ($model->{$field} === null) {
            return false;
        }
        $model->setPartialDate($field, null);

        return true;
    }

    [$y, $m, $d, $circa] = array_pad($spec, 4, null);
    $wasDate = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d, (bool) $circa);

    return $wasDate !== ($model->{$field} ? $model->{$field}->format("Y-m-d") : null)
        || $wasPrec !== $model->datePrecisionFor($field);
};

foreach ($payload["people"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases.institution")->first();

    if (! $p && ! empty($row["photo_slug"])) {
        $p = Prisoner::withoutGlobalScopes()->where("slug", $row["photo_slug"])->with("cases.institution")->first();
    }

    if (! $p) {
        echo "\nNOT FOUND: ", $row["slug"], " — skipped\n";
        continue;
    }

    echo "\n", $p->slug, "\n";

    $notes = [];

    if (! empty($row["name"]) && $p->name !== $row["name"]) {
        $oldSlug = $p->slug;
        $p->name = $row["name"];
        foreach (["first_name", "middle_name", "last_name"] as $f) {
            if (! empty($row[$f])) {
                $p->{$f} = $row[$f];
            }
        }
        $notes[] = "renamed (slug will regenerate from ".$oldSlug.")";
    }

    if (! empty($row["aka"]) && $p->aka !== $row["aka"]) {
        $p->aka = $row["aka"];
        $notes[] = "aka";
    }

    foreach (["birth" => "birthdate", "death" => "death_date"] as $k => $field) {
        if (array_key_exists($k, $row) && $applyDate($p, $field, $row[$k])) {
            $notes[] = $field."=".$p->{$field}->format("Y-m-d")." (".($p->datePrecisionFor($field) ?: "day").")";
        }
    }

    if (! empty($row["description"]) && $p->description !== $row["description"]) {
        $p->description = $row["description"];
        $notes[] = "description";
    }

    $rel = "prisoners/".$row["photo_slug"].".jpg";
    if (is_file(storage_path("app/public/".$rel)) && $p->photo !== $rel && (! $p->photo || ! empty($row["replace_photo"]))) {
        $was = $p->photo;
        $p->photo = $rel;
        $notes[] = "photo".($was ? " (replaced ".$was.")" : " attached");
    }

    if ($notes) {
        $p->save();
        $p->refresh();
    }

    echo "  ", ($notes ? implode("; ", $notes) : "person already correct"), "  slug now: ", $p->slug, "\n";

    $case = $p->cases->sortBy("created_at")->first();

    if (! $case) {
        echo "  no case row — skipped\n";
        continue;
    }

    $case->setRelation("prisoner", $p);

    $caseNotes = [];

    foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date", "sentenced" => "sentenced_date", "release" => "release_date"] as $k => $field) {
        if (array_key_exists($k, $row) && $applyDate($case, $field, $row[$k])) {
            $caseNotes[] = $field."=".$case->{$field}->format("Y-m-d")." (".($case->datePrecisionFor($field) ?: "day").")";
        }
    }

    foreach (["charges", "convicted", "sentence"] as $field) {
        if (array_key_exists($field, $row) && $case->{$field} != $row[$field]) {
            $case->{$field} = $row[$field];
            $caseNotes[] = $field;
        }
    }

    if (! empty($row["institution"]) && ! $case->institution_id) {
        $inst = Institution::firstOrCreate(
            ["name" => $row["institution"]],
            ["city" => $row["institution_city"] ?? null, "state" => $row["institution_state"] ?? null]
        );
        $case->institution_id = $inst->id;
        $caseNotes[] = "institution=".$inst->name;
    }

    if ($caseNotes) {
        $case->save();
    }

    echo "  case: ", ($caseNotes ? implode("; ", $caseNotes) : "already correct"),
         "   days=", ($case->imprisoned_for_days === null ? "null" : $case->imprisoned_for_days), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
