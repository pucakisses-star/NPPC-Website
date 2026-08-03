#!/usr/bin/env bash
#
# BATCH 119 -- the February prisoner-birthday-list corrections, per
# the curator's OCR reconstruction.
#
#   TWO NEW RECORDS via prisoner:add (refuses duplicates by name):
#     ANTON KARACHUN (b. February 6, 1897) — the Russian-born 31st
#       Infantry soldier who deserted in Siberia, joined
#       Bolshevik-aligned forces, was court-martialed (20 or 22
#       years by contemporary reports), served at McNeil Island, and
#       left for the Soviet Union on his March 1925 release.
#     NEILS RANDQUIST (birthday February 11; OCR form Nell
#       Randqulst; also Niels Rundquist) — the seaman held at the
#       Maine State Prison, Thomaston; everything else honestly
#       unresolved.
#
#   ONE MERGE: james-p-gordon <- p-j-gordon — one man printed two
#   ways. The dropped record's biography is appended so NOTHING IS
#   DELETED FROM DESCRIPTIONS.
#
#   UPDATES:
#     eugene-barnett — stored dates (b. 1888-12-01, d. 1948-02-02)
#       conflict with the UW finding aid (1892-1973): FORCED to
#       birth February 21, 1892 and death 1973 (year precision),
#       old values echoed; Walla Walla number 9414.
#     frank-nash — number 9516 (fills only if empty).
#     frank-sherman — number 35768; February 5 birthday (yearless,
#       so recorded in the biography).
#     f-e-mcclennigan — renamed Francis E. McClennigan (the OCR had
#       Francis K. McClenwgan); number 38125; February 27 birthday.
#     james-p-gordon — number 38113 (the OCR 88113 was wrong);
#       February 13 birthday.
#     earl-firey — birth February 1895 at month precision (the list
#       says Feb 26, a genealogy index says Feb 29, 1895 — a date
#       that cannot exist); Folsom number 12640; case dates fill:
#       incarcerated February 17, 1922, released 1925 (year).
#
# Run from the repo root, after git pull (after batch 118):
#   bash database/data/run-batch-119.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then
        return 0
    fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 119 — February birthday-list corrections"
echo "==================================================================="

create_new() {
    php artisan prisoner:add "$(cat database/data/fixes/anton-karachun.json)"
    php artisan prisoner:add "$(cat database/data/fixes/neils-randquist.json)"
}

fix_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch119.json")), true);

if (! $payload || empty($payload["updates"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

foreach ($payload["merges"] as $m) {
    $keep = Prisoner::withUnderReview()->where("slug", $m["keep"])->with("cases")->first();
    $drop = Prisoner::withUnderReview()->where("slug", $m["drop"])->with("cases")->first();

    echo "\nMERGE ", $m["keep"], " <- ", $m["drop"], "\n";

    if (! $keep) { echo "  keep record NOT FOUND — skipped\n"; continue; }
    if (! $drop) { echo "  drop record not found (already merged?)\n"; }

    $notes = [];

    if ($drop) {
        $dropDesc = trim((string) $drop->description);
        if ($dropDesc !== "" && ! str_contains((string) $keep->description, mb_substr($dropDesc, 0, 60))) {
            $keep->description = trim((string) $keep->description)
                ."\n\nFrom a second record of the same person, merged here so nothing is lost:\n\n".$dropDesc;
            $notes[] = "description merged";
        }

        if ($drop->photo && ! $keep->photo) {
            $keep->photo = $drop->photo;
            $notes[] = "photo carried over";
        }

        if ($drop->inmate_number && ! $keep->inmate_number) {
            $keep->inmate_number = $drop->inmate_number;
            $notes[] = "inmate_number carried over";
        }
    }

    if (! empty($m["aka_add"]) && ! str_contains((string) $keep->aka, $m["aka_add"])) {
        $keep->aka = trim(($keep->aka ? $keep->aka."; " : "").$m["aka_add"], "; ");
        $notes[] = "aka added";
    }

    if ($notes) { $keep->save(); }

    if ($drop) {
        foreach ($drop->cases as $c) { $c->delete(); }
        $drop->delete();
        $notes[] = "duplicate row deleted";
    }

    echo "  ", ($notes ? implode("; ", $notes) : "nothing to do"), "\n";
}

foreach ($payload["updates"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    echo "\nFIX ", $row["slug"], "\n";

    if (! $p) { echo "  NOT FOUND — skipped\n"; continue; }

    $notes = [];

    if (! empty($row["rename"]) && $p->name !== $row["rename"]) {
        $p->name = $row["rename"];
        $notes[] = "renamed";
    }

    if (! empty($row["first_name"]) && $p->first_name !== $row["first_name"]) {
        $p->first_name = $row["first_name"];
        $notes[] = "first_name";
    }

    if (! empty($row["aka_add"]) && ! str_contains((string) $p->aka, $row["aka_add"])) {
        $p->aka = trim(($p->aka ? $p->aka."; " : "").$row["aka_add"], "; ");
        $notes[] = "aka added";
    }

    if (! empty($row["inmate_set"]) && ! $p->inmate_number) {
        $p->inmate_number = $row["inmate_set"];
        $notes[] = "inmate_number=".$row["inmate_set"];
    }

    if (! empty($row["birth_fill"]) && $p->birthdate === null) {
        [$y, $mo, $d] = array_pad($row["birth_fill"], 3, null);
        $p->setPartialDate("birthdate", $y, $mo, $d);
        $notes[] = "birthdate filled";
    }

    if (! empty($row["birth_force"])) {
        [$y, $mo, $d] = array_pad($row["birth_force"], 3, null);
        $old = $p->birthdate ? $p->birthdate->format("Y-m-d") : "empty";
        $p->setPartialDate("birthdate", $y, $mo, $d);
        $new = $p->birthdate->format("Y-m-d");
        if ($old !== $new) { $notes[] = "birthdate: ".$old." -> ".$new; }
    }

    if (! empty($row["death_force"])) {
        [$y, $mo, $d] = array_pad($row["death_force"], 3, null);
        $old = $p->death_date ? $p->death_date->format("Y-m-d") : "empty";
        $p->setPartialDate("death_date", $y, $mo, $d);
        $new = $p->death_date->format("Y-m-d");
        if ($old !== $new) { $notes[] = "death_date: ".$old." -> ".$new; }
    }

    if (! empty($row["append"]) && ! str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
        $p->description = trim((string) $p->description)."\n\n".$row["append"];
        $notes[] = "appended";
    }

    if ($notes) { $p->save(); }

    $case = $p->cases->first();

    if ($case && (! empty($row["case_fill_incarceration"]) || ! empty($row["case_fill_release"]))) {
        $case->setRelation("prisoner", $p);
        $caseDirty = false;

        foreach (["case_fill_incarceration" => "incarceration_date", "case_fill_release" => "release_date"] as $key => $field) {
            if (! empty($row[$key]) && $case->{$field} === null) {
                [$y, $mo, $d] = array_pad($row[$key], 3, null);
                $case->setPartialDate($field, $y, $mo, $d);
                $caseDirty = true;
                $notes[] = $field." filled";
            }
        }

        if ($caseDirty) { $case->save(); }
    }

    echo "  ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "create-karachun-randquist" create_new
run "merge-and-updates" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 119 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
