#!/usr/bin/env bash
#
# THE CRDL MINING COHORT, FOURTH WAVE -- the curator's ledger entries
# 516-560 turned out to be almost entirely ground already shipped in
# the earlier waves: 44 of the 45 names dedupe cleanly against the
# existing records (including every spelling variant — Svanoe, the
# Singletons, Sellers, Townsend, Mitaritonna/Mitarotonda). What is
# genuinely new:
#
#   RICHARD LEE HALEY (entry 527) — arrested July 19, 1961, while
#   picketing the Heidelberg Hotel during the Southern Governors
#   Conference. His record id (mus_sovcom_2-55-6-32-1-1-1), which the
#   ledger lacked, was recovered by live CRDL search and the title
#   verified. One anomaly is preserved in the bio rather than
#   resolved: the booking placard reads 21209 / 7-16-61 while the
#   catalog title and the handwritten margin notation both say July
#   19 — possibly a placard error or a reused earlier booking photo.
#
#   ENRICHMENTS, never clobbered: year-precision birth dates enter
#   ONLY where the field is empty — Dennis (1935), Havey (1930),
#   Steward (1939) — and Mitaritonna gains the ledger's normalized
#   "Rudolph Mitarotonda" as an additional AKA.
#
# The payload lives in database/data/fixes/crdl-freedom-riders-4.json.
# Photograph in database/data/photos/crdl4/ (MDAH large scan, frontal
# panel at 525x700). The attach loop only fills EMPTY slots.
#
# Idempotent: people matched by slug, case rows matched by arrest
# year+month, appends guarded by str_contains, every field compared
# before writing.
#
# Run from the repo root (after batch 114):
#   bash database/data/fix-crdl-freedom-riders-4.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

copied=0
for SRC in database/data/photos/crdl4/*.jpg; do
    [ -e "$SRC" ] || continue
    base="$(basename "$SRC")"
    cp -f "$SRC" "${DST_DIR}/${base}"
    copied=$((copied+1))
done
echo "copied ${copied} portrait(s) from database/data/photos/crdl4/"

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/crdl-freedom-riders-4.json")), true);

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
