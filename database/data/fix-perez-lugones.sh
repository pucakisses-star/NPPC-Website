#!/usr/bin/env bash
#
# AURELIO LUIS PEREZ-LUGONES IS NO LONGER JAILED, and his record had the
# wrong court, a wrong facility, an unproven age and an inferred race.
#
# THE MAJOR CORRECTION: a federal judge ordered him released on May 4,
# 2026 to strict pretrial home detention with electronic monitoring and
# restrictions on internet-connected devices. His record listed him as
# presently incarcerated with a running counter — reading about six
# months and climbing — when he was physically detained for 116 days,
# January 8 to May 4, 2026. The release date ends the counter at 116.
# Flags move to released-pending-trial: in_custody false, released true,
# awaiting_trial STILL TRUE.
#
# THE COURT WAS WRONG. The criminal prosecution is in the U.S. District
# Court for the DISTRICT OF MARYLAND. The Eastern District of Virginia
# proceedings are the separate unsealing litigation over the devices
# seized from the reporter — the record had conflated the two, and the
# prosecutor field said E.D. Va. It now says D. Md.
#
# THE FACILITY IS REMOVED, not corrected: the record linked the
# Alexandria Detention Center, which came in with the E.D. Va. error,
# and the public record does not yet reliably identify where he was
# actually held. An unknown is stored as an unknown.
#
# THE JUDGE gains his right name: U.S. District Judge MATTHEW J. Maddox
# — several news reports miscalled him Michael. Plea moves from "not
# entered" to NOT GUILTY, confirmed by the May 4 reporting.
#
# AGE 62 -> 61. He was reported as sixty-one in January 2026 and no
# exact birthdate is located, so the as-reported figure is stored and
# the biography anchors it to the arrest. RACE IS CLEARED: "Hispanic"
# was documented nowhere and inferred from his name, which is not
# evidence — the Jacob Riis lesson applies to fields as well as to
# photographs.
#
# THE BIOGRAPHY IS REWRITTEN to keep the allegations phrased as
# allegations: the indictment names only "Reporter 1" and an unnamed
# foreign country; the identification of the reporter and the tie to
# Venezuela are attributed to filings and press reporting, and the
# reporter has not confirmed he was a source. The five-plus-one Section
# 793 counts are described with their ten-year statutory maximums and
# the explicit caveat that this is not an automatic consecutive sixty
# years.
#
# THE PHOTOGRAPH is the FBI surveillance still from page 33 of the
# redacted warrant affidavit unsealed in the RCFP litigation
# (Case 1:26-sw-00054-WBP, Doc. 39): Perez-Lugones in the workplace
# parking garage, timestamped January 6, 2026, two days before his
# arrest. THE AFFIDAVIT ITSELF IDENTIFIES THE FIGURE — the paragraph
# above the still reads "PEREZ-LUGONES was observed looking around the
# workplace parking garage before entering his vehicle". It is a
# degraded photocopy halftone and his face is capped and small; the
# full still with its date stamp is used rather than an illegible face
# closeup. Replace it if a better-sourced portrait surfaces.
#
# The payload lives in database/data/fixes/perez-lugones-release.json.
# Idempotent: every field is compared before writing.
#
# Run from the repo root:
#   bash database/data/fix-perez-lugones.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

if [ -f "database/data/photos/aurelio-luis-perez-lugones.jpg" ]; then
    cp -f "database/data/photos/aurelio-luis-perez-lugones.jpg" "${DST_DIR}/aurelio-luis-perez-lugones.jpg"
    echo "copied aurelio-luis-perez-lugones.jpg"
fi

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$row = json_decode(File::get(base_path("database/data/fixes/perez-lugones-release.json")), true);

if (! $row) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();

if (! $p) {
    echo "NOT FOUND: ", $row["slug"], "\n";
    return;
}

echo $p->slug, "\n";

$notes = [];

foreach (["age" => $row["age"], "in_custody" => $row["in_custody"], "released" => $row["released"], "awaiting_trial" => $row["awaiting_trial"], "description" => $row["description"]] as $field => $value) {
    if ($p->{$field} != $value) {
        $p->{$field} = $value;
        $notes[] = $field;
    }
}

if (! empty($row["clear_race"]) && $p->race !== null) {
    $notes[] = "race cleared (was ".$p->race.")";
    $p->race = null;
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

    if (! empty($spec["release"])) {
        [$y, $m, $d] = array_pad($spec["release"], 3, null);
        $was = $case->release_date ? $case->release_date->format("Y-m-d") : null;
        $case->setPartialDate("release_date", $y, $m, $d);
        if ($was !== $case->release_date->format("Y-m-d")) {
            $caseNotes[] = "release_date=".$case->release_date->format("Y-m-d");
        }
    }

    if (! empty($spec["clear_institution"]) && $case->institution_id) {
        $caseNotes[] = "institution cleared (was ".($case->institution?->name ?? "unknown").") — the facility is not reliably identified";
        $case->institution_id = null;
    }

    foreach (["judge", "prosecutor", "plead", "convicted", "sentence"] as $field) {
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

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
