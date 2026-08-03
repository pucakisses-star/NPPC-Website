#!/usr/bin/env bash
#
# THE CRDL MINING COHORT, FIFTH WAVE -- the curator's ledger entries
# 531-590 dedupe ENTIRELY against the existing records: entries
# 531-560 were already handled in the fourth wave, and all thirty of
# entries 561-590 (the July 9/15/21/23/29 and June 25 groups) were
# shipped in the earlier waves under the same slugs. The whole yield
# of this sixty-entry drop:
#
#   - ralph-robert-rogers gains his 1928 birth year (empty field
#     only) from the CRDL authority metadata.
#   - tommie-eldridge-brashear gains the ledger-reported authority
#     spelling "Tommie Eldrige Brashear" as an AKA (the photograph
#     reads Eldredge; the stored form stays primary).
#   - jeanne-h-herrick gains her mugshot-inscription form "Jeanne
#     Forry Herrick" as an AKA.
#
# No new records, no photographs. The photos/crdl5 directory does
# not exist and the copy loop is a no-op.
#
# Idempotent: appends guarded, every field compared before writing.
#
# Run from the repo root (after batch 119):
#   bash database/data/fix-crdl-freedom-riders-5.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

copied=0
for SRC in database/data/photos/crdl5/*.jpg; do
    [ -e "$SRC" ] || continue
    base="$(basename "$SRC")"
    cp -f "$SRC" "${DST_DIR}/${base}"
    copied=$((copied+1))
done
echo "copied ${copied} portrait(s) from database/data/photos/crdl5/"

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/crdl-freedom-riders-5.json")), true);

if (! $payload || empty($payload["updates"])) {
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
