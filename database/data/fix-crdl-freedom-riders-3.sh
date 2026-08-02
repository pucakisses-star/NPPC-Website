#!/usr/bin/env bash
#
# THE CRDL MINING COHORT, THIRD WAVE -- eleven new records and eight
# enriched ones from the curator's continued CRDL ledger (entries
# 426-485, deduplicated against the first two waves): the June 20,
# 1961 Illinois Central group (Rita Carter, Robert Martinson, William
# Wagoner, Lestra Peterson, Margaret Ann Kerr, Paul Duncan McConnell,
# and the Reverend Grant Harland Muse Jr.), the June 23 and June 25
# riders Mary Magdalene Harrison, Mary Lucille Hamilton, Norma
# Libson, and Robert William Mason.
#
# ENRICHMENTS, never clobbered:
#   - MIRIAM FEINGOLD (already in the database with her Swarthmore /
#     31-days-at-Parchman record) gains her Sovereignty Commission
#     mugshot (2-55-5-48, placard 21048, 6-21-61) in her empty photo
#     slot, and her arrest date tightens to June 21, 1961 — only if
#     the stored date is not already day-precise.
#   - JAMES ROBERT WAHLSTROM gains his SECOND documented arrest: the
#     catalog holds two records for him — June 6, 1961 (Trailways)
#     and July 31, 1961 (Greyhound). This resolves the second wave's
#     placard conflict: the placard 20953 / 6-6-61 photograph is the
#     June 6 arrest, and the July 31 arrest enters as a new case row.
#   - BUREN LEWIS TEALE and FREDERICK DEAN MUNTEAN take their CRDL
#     authority-page name forms as primary (the photograph spellings
#     Buron and Fredrick become AKAs).
#   - Year-precision birth dates enter ONLY where the field is empty:
#     Palmer (1943), Caston (1943), Griffin (1940), Harris (1942),
#     Wahlstrom (circa 1938), Muntean (circa 1938).
#
# EVERY DIRECT CRDL RECORD ID RE-VERIFIED against the live catalog
# (title fetched and name-checked); the five ids the dossier lacked
# were recovered by live search, and every placard in the cropped
# photographs was read against the ledger date on a contact sheet.
#
# The payload lives in database/data/fixes/crdl-freedom-riders-3.json.
# Photographs in database/data/photos/crdl3/ (MDAH large scans,
# frontal panels at 525x700). The attach loop only fills EMPTY slots.
#
# Idempotent: people matched by slug, case rows matched by arrest
# year+month, appends guarded by str_contains, every field compared
# before writing.
#
# Run from the repo root:
#   bash database/data/fix-crdl-freedom-riders-3.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

copied=0
for SRC in database/data/photos/crdl3/*.jpg; do
    [ -e "$SRC" ] || continue
    base="$(basename "$SRC")"
    cp -f "$SRC" "${DST_DIR}/${base}"
    copied=$((copied+1))
done
echo "copied ${copied} portrait(s) from database/data/photos/crdl3/"

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/crdl-freedom-riders-3.json")), true);

if (! $payload || empty($payload["new"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$applyDate = function ($model, string $field, $spec): bool {
    if ($spec === null) {
        return false;
    }

    [$y, $m, $d, $circa] = array_pad($spec, 4, null);
    $wasDate = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d, (bool) $circa);

    return $wasDate !== ($model->{$field} ? $model->{$field}->format("Y-m-d") : null)
        || $wasPrec !== $model->datePrecisionFor($field);
};

$applyCase = function (Prisoner $p, array $spec, ?PrisonerCase $case) use ($applyDate): array {
    $isNew = ! $case;

    if ($isNew) {
        $case = new PrisonerCase;
        $case->prisoner_id = $p->id;
    }

    $case->setRelation("prisoner", $p);
    $notes = [];

    foreach (["arrest" => "arrest_date"] as $k => $field) {
        if (array_key_exists($k, $spec) && $applyDate($case, $field, $spec[$k])) {
            $notes[] = $field."=".$case->{$field}->format("Y-m-d");
        }
    }

    foreach (["charges", "convicted", "sentence"] as $field) {
        if (array_key_exists($field, $spec) && $case->{$field} != $spec[$field]) {
            $case->{$field} = $spec[$field];
            $notes[] = $field;
        }
    }

    if ($isNew || $notes) {
        $case->save();
    }

    return [$isNew, $notes];
};

// ---- the new records ---------------------------------------------------

foreach ($payload["new"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();
    $isNew = ! $p;

    if ($isNew) {
        $p = new Prisoner;
        $p->slug = $row["slug"];
    }

    foreach (["name", "first_name", "middle_name", "last_name", "gender", "era", "state", "aka", "description"] as $f) {
        if (array_key_exists($f, $row) && $p->{$f} !== $row[$f]) {
            $p->{$f} = $row[$f];
        }
    }

    foreach (["affiliation", "ideologies"] as $f) {
        if (array_key_exists($f, $row) && $p->{$f} != $row[$f]) {
            $p->{$f} = $row[$f];
        }
    }

    foreach (["birth" => "birthdate", "death" => "death_date"] as $k => $field) {
        if (array_key_exists($k, $row)) {
            $applyDate($p, $field, $row[$k]);
        }
    }

    $p->in_custody = false;
    $p->released = true;

    $rel = "prisoners/".$row["slug"].".jpg";
    if (is_file(storage_path("app/public/".$rel)) && ! $p->photo) {
        $p->photo = $rel;
    }

    $p->save();
    $p->load("cases");

    echo str_pad($row["slug"], 30), ($isNew ? "CREATED" : "updated in place"),
         ($p->photo ? "  [photo]" : "  [no photo yet]"), "\n";

    $existing = $p->cases->all();
    foreach ($row["cases"] as $spec) {
        $match = null;
        foreach ($existing as $c) {
            if ($c->arrest_date && ! empty($spec["arrest"][0])
                && (int) $c->arrest_date->format("Y") === $spec["arrest"][0]
                && ((int) $c->arrest_date->format("n") === (int) ($spec["arrest"][1] ?? (int) $c->arrest_date->format("n")))) {
                $match = $c;
                break;
            }
        }
        if (! $match && count($row["cases"]) === 1) {
            $match = $existing[0] ?? null;
        }
        [$cNew, $cNotes] = $applyCase($p, $spec, $match);
        echo "    case ", ($cNew ? "NEW  " : "     "), ($cNotes ? implode(", ", $cNotes) : "unchanged"), "\n";
    }
}

// ---- the existing-record enrichments ----------------------------------

foreach ($payload["updates"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) {
        echo "\nNOT FOUND: ", $row["slug"], " — skipped\n";
        continue;
    }

    echo "\n", $row["slug"], "\n";
    $notes = [];

    if (! empty($row["rename"]) && $p->name !== $row["rename"]) {
        $p->name = $row["rename"];
        $notes[] = "name=".$row["rename"];
    }

    foreach (["first_name", "middle_name", "last_name"] as $f) {
        if (! empty($row[$f]) && $p->{$f} !== $row[$f]) {
            $p->{$f} = $row[$f];
            $notes[] = $f;
        }
    }

    if (! empty($row["aka"]) && ! str_contains((string) $p->aka, $row["aka"])) {
        $p->aka = trim(($p->aka ? $p->aka."; " : "").$row["aka"], "; ");
        $notes[] = "aka";
    }

    // Birth dates enter only where the field is EMPTY — never lowering precision.
    if (array_key_exists("birth", $row) && $p->birthdate === null) {
        if ($applyDate($p, "birthdate", $row["birth"])) {
            $notes[] = "birthdate=".$p->birthdate->format("Y-m-d")." (".($p->datePrecisionFor("birthdate") ?: "day").")";
        }
    }

    if (! empty($row["append"]) && ! str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
        $p->description = trim((string) $p->description)."\n\n".$row["append"];
        $notes[] = "description appended";
    }

    if (! empty($row["photo_slug"])) {
        $rel = "prisoners/".$row["photo_slug"].".jpg";
        if (is_file(storage_path("app/public/".$rel)) && ! $p->photo) {
            $p->photo = $rel;
            $notes[] = "photo attached";
        }
    }

    if ($notes) {
        $p->save();
    }

    echo "  ", ($notes ? implode("; ", $notes) : "person unchanged"), "\n";

    if (! empty($row["case_arrest"])) {
        $case = $p->cases->sortBy("created_at")->first();
        if ($case && $case->datePrecisionFor("arrest_date") !== "day") {
            $case->setRelation("prisoner", $p);
            if ($applyDate($case, "arrest_date", $row["case_arrest"])) {
                $case->save();
                echo "  case: arrest_date=", $case->arrest_date->format("Y-m-d"), "\n";
            }
        } elseif ($case) {
            echo "  case: arrest date already day-precise (", $case->arrest_date->format("Y-m-d"), ") — left alone\n";
        }
    }

    if (! empty($row["add_case"])) {
        $spec = $row["add_case"];
        $already = $p->cases->first(function ($c) use ($spec) {
            return $c->charges && str_contains($c->charges, $spec["match_missing_charges"]);
        });
        if ($already) {
            echo "  added case already present — skipped\n";
        } else {
            [$cNew, $cNotes] = $applyCase($p, $spec, null);
            echo "  case NEW  ", implode(", ", $cNotes), "\n";
        }
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
