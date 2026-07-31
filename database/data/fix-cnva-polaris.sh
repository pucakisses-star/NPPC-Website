#!/usr/bin/env bash
#
# THE CNVA POLARIS ACTION AND PEACE WALK NINE -- corrections from the
# curator's dossier, and seven portraits.
#
# ------------------------------------------------------------------
# THE CORRECTIONS
# ------------------------------------------------------------------
#
# ALLAN HOFFMAN'S INCARCERATION DATE IS DERIVED, as instructed: he was
# released on May 26, 1961 having served eighteen days, so custody began
# May 8, 1961 — eighteen days counted back from the documented release.
# Arrest April 28, 1961, the drill itself. The sixty-day sentence is NOT
# recorded as time served; the day counter now reads 18 from the dates.
#
# BILL HENRY stops being merely "jailed on trespassing charges": he
# boarded the Ethan Allen with Donald Martin on November 22, 1960, took
# a one-year sentence and is documented as having served a year at
# Danbury. No custody span is entered because his admission and release
# dates are not located.
#
# DONALD MARTIN refused bail, was held at Danbury awaiting trial by
# February 1961, and received an INDEFINITE YOUTH-OFFENDER SENTENCE that
# could have held him up to four years. His release is unresolved and
# none is entered.
#
# ED GUERARD HAS NO DOCUMENTED PRISON TERM and his record now says so:
# arrested March 11, 1961 after paddling toward the Abraham Lincoln at
# Portsmouth; released within hours after the June 15 Thomas Edison
# swim. His "Sentenced with the Polaris Action pacifists" verdict was
# wrong. He gains the minor_case flag for the brief detentions.
#
# SANDERS AND KEYES HAD THEIR COLLEGES SWAPPED. Sanders was the NYU
# student, Keyes the 19-year-old Harvard student. Both were released
# within hours after the June 15 swim; both were jailed for the LATER
# August 1961 Ethan Allen commissioning protest — Sanders sentenced to
# 77 days (days served unconfirmed, so no span is entered), Keyes
# reported to have served 17.
#
# JERRY WHEELER'S base gets its name — Davis-Monthan — and his six
# months, though firmly documented, is not recorded as served: no
# release record proves he sat the whole term. The contemporary Peace
# Walk caption ("Jerry Wheeler is now serving a six month term in
# prison") corroborates the sentence itself.
#
# MARJORIE SWANN was already correct to the day (August 10, 1959 to
# January 11, 1960, 154 days); she gains the Alderson institution.
#
# RICHARD ZINK keeps no custody dates: the thirty-days-of-six-months
# order of June 1961 is definite, but no admission or release record
# has been located.
#
# ------------------------------------------------------------------
# THE PORTRAITS -- seven attached, two pursued and not obtained
# ------------------------------------------------------------------
#
# Provenance in database/data/photos/CREDITS-cnva.md. Anchors, briefly:
# Hoffman and Wheeler from David Rich's Walk for Peace archive, both
# captioned by position; Keyes, Sanders, Guerard from Gene Keyes's own
# captioned 1961 archive; Henry and Swann from the captioned 2010 CNVA
# reunion photographs in the same archive.
#
#   GUERARD'S IS FACE-AVERTED — he is looking down writing — and is
#   kept on the Berthe Arnold rule: an identified contemporary image
#   beats an unidentified clear one. Replace it if a face-forward
#   photograph surfaces.
#
#   NOT OBTAINED: Donald Martin (the AP photograph of him on the Ethan
#   Allen's hull ran in the November 23, 1960 New York Times; no open
#   scan located — Swarthmore's CNVA files are the target) and Richard
#   Zink (his Dignity Memorial obituary is Cloudflare-blocked from this
#   environment and not in the Wayback Machine; the National Guardian
#   of November 28, 1960, page 6, also pictures him). Both are listed
#   in the attach loop and will report missing until a file is dropped
#   in.
#
# Idempotent throughout: fields compared before writing, the single
# case row on each record is matched in place, photos copy over
# themselves harmlessly.
#
# Run from the repo root:
#   bash database/data/fix-cnva-polaris.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

for slug in allan-hoffman jerry-wheeler eugene-keyes edward-sanders ed-guerard bill-henry marjorie-swann donald-martin richard-zink; do
    SRC="database/data/photos/${slug}.jpg"
    if [ -f "$SRC" ]; then
        cp -f "$SRC" "${DST_DIR}/${slug}.jpg"
        echo "copied ${slug}.jpg"
    else
        echo "no source image for ${slug} — skipped (see CREDITS-cnva.md)"
    fi
done

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/cnva-polaris-corrections.json")), true);

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

    [$y, $m, $d] = array_pad($spec, 3, null);
    $wasDate = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d);

    return $wasDate !== ($model->{$field} ? $model->{$field}->format("Y-m-d") : null)
        || $wasPrec !== $model->datePrecisionFor($field);
};

foreach ($payload["people"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) {
        echo "\nNOT FOUND: ", $row["slug"], " — skipped\n";
        continue;
    }

    echo "\n", $row["slug"], "\n";

    $notes = [];

    if (array_key_exists("description", $row) && $p->description !== $row["description"]) {
        $p->description = $row["description"];
        $notes[] = "description";
    }

    if (array_key_exists("minor_case", $row) && $p->minor_case != $row["minor_case"]) {
        $p->minor_case = $row["minor_case"];
        $notes[] = "minor_case=".($row["minor_case"] ? "true" : "false");
    }

    $rel = "prisoners/".$row["slug"].".jpg";
    if (is_file(storage_path("app/public/".$rel)) && $p->photo !== $rel) {
        $was = $p->photo;
        $p->photo = $rel;
        $notes[] = "photo".($was ? " (replaced ".$was.")" : " attached");
    }

    if ($notes) {
        $p->save();
    }

    echo "  ", ($notes ? implode("; ", $notes) : "person unchanged"), "\n";

    $case = $p->cases->sortBy("created_at")->first();

    if (! $case) {
        echo "  no case row — skipped\n";
        continue;
    }

    $case->setRelation("prisoner", $p);

    $caseNotes = [];

    foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date", "release" => "release_date"] as $k => $field) {
        if (array_key_exists($k, $row) && $applyDate($case, $field, $row[$k])) {
            $caseNotes[] = $field."=".($case->{$field} ? $case->{$field}->format("Y-m-d") : "null")
                ." (".($case->datePrecisionFor($field) ?: "day").")";
        }
    }

    foreach (["convicted", "sentence"] as $field) {
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

    echo "  case: ", ($caseNotes ? implode("; ", $caseNotes) : "unchanged"),
         "   days=", ($case->imprisoned_for_days === null ? "null" : $case->imprisoned_for_days), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
