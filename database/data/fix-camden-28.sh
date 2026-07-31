#!/usr/bin/env bash
#
# THE CAMDEN 28, CORRECTED FROM THE CURATOR'S RESEARCH REPORT.
#
# One merge, one rename, eleven outcome corrections, and vital dates
# across the whole roster. The payload notes print as the run goes;
# what needs explaining up front:
#
# THE BIGGEST ERROR WAS THE OUTCOMES. Every record said "acquitted",
# because popular accounts collapse the case into "all 28 were
# acquitted". The federal court's February 7, 1973 opinion names the
# SEVENTEEN people actually tried, and the jury acquitted those
# seventeen on May 20, 1973. TEN defendants were severed and had their
# charges dismissed afterward — Abdoo, Anderson, Billman, Dixon,
# Forsyth, Madden, Moccia, Pommersheim, Shemeley, Tosi — and their
# records now say so instead of claiming an acquittal that never
# happened to them. ANITA RICCI pleaded guilty to a lesser charge
# before the trial; her exact disposition is unresolved and her record
# now says that instead of "acquitted".
#
# COOKIE RIDOLFI AND KATHLEEN RIDOLFI ARE ONE WOMAN, merged into
# kathleen-ridolfi with Cookie as the AKA. The deleted row held the
# only arrest date, which is written onto the survivor first. It also
# called her the youngest member — that was Jayma Abdoo — and the
# merged biography fixes that.
#
# MARGARET INNES BECOMES MARGARET INNESS, the spelling in the federal
# court opinion and in her obituary. The rename regenerates the slug,
# which breaks the old URL — accepted for a demonstrable error, the
# Herkner rule from batch 30. She died on August 22, 2021, fifty years
# to the day after the raid.
#
# FATHER MICHAEL DOYLE'S CASE HAD HIM JAILED FOR THE WHOLE TRIAL. His
# release date was 1973-05-19, the eve of the verdict, as though he sat
# in custody for twenty-one months; the defendants were all out on bail
# by about mid-September 1971. The release date is removed and the
# custody left open, with the roughly-three-weeks span in the text. His
# arrest moves to August 22, his death from November 23 to November 4,
# 2022, and he regains the Camden 28 affiliation the batch 45 split
# left empty.
#
# BIRTH YEARS ARE DERIVED FROM AGE AT INDICTMENT, on the curator's
# instruction, wherever no better date exists: birth year = 1971 minus
# the reported age, entered at CIRCA precision — the database's "may be
# off by one" — because a person aged N in August 1971 was born in
# either 1971-N or 1970-N. Exact dates go in where the dossier
# established them: McGowan (1935-05-07), Swinglish (1944-03-25),
# Inness (1944-11-06), Williamson (1949-08-16), Doyle (already held).
# Abdoo (1951) and Pommersheim (1943) are stated years, entered at year
# precision without the circa hedge.
#
# CUSTODY DATES FOLLOW THE DOSSIER'S OWN WARNING. Twenty were arrested
# on August 22, 1971 and eight more around August 27, but the public
# record does not reliably assign the tranches by name — so arrests go
# in at MONTH precision, August 1971, except where day evidence exists:
# McGowan (obituary), Doyle (the raid), Ridolfi and Grady (already
# recorded). Nobody gets an individual release date, because none is
# documented; the collective-bail story and the approximately three
# weeks live in the sentence texts. The Swarthmore College Peace
# Collection's nondigitized Camden 28 files are the named next source
# for resolving the per-person chronology.
#
# FLAGGED, NOT CHANGED, printed by the run: edward-murphy's stored
# death of 2012-04-04 and sarah-tosi's of 2006-04-15, both dates the
# dossier could not resolve, both names common enough for a false
# match. Kept, but they need re-sourcing.
#
# JOHN GRADY'S 1982 PLOWSHARES CASE IS NOT TOUCHED, and per the dossier
# its November 13, 1982 date must never be read back onto Camden. Only
# his 1971 case row is updated, matched by year.
#
# NO PHOTOGRAPHS ARE ATTACHED. The dossier names verified portrait
# sources for a dozen defendants (obituaries, the documentary's
# participant updates, university faculty pages); most are recent
# photographs with unclear reuse rights, and nothing is fetched on this
# pass.
#
# Idempotent: the merge skips if the duplicate is gone, every field is
# compared before writing, and case updates match the 1971 case by
# year.
#
# Run from the repo root:
#   bash database/data/fix-camden-28.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/camden-28-corrections.json")), true);

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

// ---- the Ridolfi merge, survivor first ---------------------------------

$mg = $payload["merge"];
$keep = Prisoner::withoutGlobalScopes()->where("slug", $mg["keep"])->first();
$drop = Prisoner::withoutGlobalScopes()->where("slug", $mg["drop"])->with("cases")->first();

echo $mg["keep"], " <- ", $mg["drop"], "\n";

if ($drop && $keep) {
    if ($drop->photo && ! $keep->photo) {
        echo "  REFUSING the merge: the duplicate has the only photo. Nothing deleted.\n";
    } else {
        $cases = $drop->cases->count();
        $drop->delete();
        echo "  merged — duplicate deleted (", $cases, " case row went with it; its arrest date is re-applied below)\n";
    }
} elseif (! $drop) {
    echo "  duplicate already gone\n";
} else {
    echo "  SURVIVOR MISSING — nothing deleted\n";
}

// ---- the roster --------------------------------------------------------

foreach ($payload["people"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) {
        echo "\nNOT FOUND: ", $row["slug"], " — skipped\n";
        continue;
    }

    echo "\n", $row["slug"], "\n";
    if (! empty($row["note"])) {
        echo "  NOTE: ", str_replace("\n", "\n  ", wordwrap($row["note"], 68)), "\n";
    }

    $notes = [];

    if (! empty($row["name"]) && $p->name !== $row["name"]) {
        $oldSlug = $p->slug;
        $p->name = $row["name"];
        $notes[] = "name (slug will regenerate from ".$oldSlug.")";
    }

    if (! empty($row["aka"]) && $p->aka !== $row["aka"]) {
        $p->aka = $row["aka"];
        $notes[] = "aka";
    }

    if (! empty($row["description"]) && $p->description !== $row["description"]) {
        $p->description = $row["description"];
        $notes[] = "description";
    }

    if (! empty($row["affiliation_add"])) {
        $aff = (array) $p->affiliation;
        $new = array_values(array_unique(array_merge($aff, $row["affiliation_add"])));
        if ($new != $aff) {
            $p->affiliation = $new;
            $notes[] = "affiliation += ".implode(", ", array_diff($new, $aff));
        }
    }

    foreach (["birth" => "birthdate", "death" => "death_date"] as $k => $field) {
        if (array_key_exists($k, $row) && $applyDate($p, $field, $row[$k])) {
            $notes[] = $field."=".$p->{$field}->format("Y-m-d")." (".($p->datePrecisionFor($field) ?: "day").")";
        }
    }

    if ($notes) {
        $p->save();
        $p->refresh();
    }

    echo "  ", ($notes ? implode("; ", $notes) : "person already correct"), "\n";

    // The 1971 case — matched by year so Grady-s 1982 row is never touched.
    $case = $p->cases->first(function ($c) {
        foreach (["arrest_date", "incarceration_date", "release_date"] as $f) {
            if ($c->{$f} && (int) $c->{$f}->format("Y") === 1971) {
                return true;
            }
        }

        return false;
    }) ?? $p->cases->first(function ($c) {
        foreach (["arrest_date", "incarceration_date", "release_date"] as $f) {
            if ($c->{$f}) {
                return false;
            }
        }

        return true;
    });

    if (! $case) {
        echo "  no matchable case row — skipped\n";
        continue;
    }

    $case->setRelation("prisoner", $p);

    $caseNotes = [];

    foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date"] as $k => $field) {
        if (array_key_exists($k, $row) && $applyDate($case, $field, $row[$k])) {
            $caseNotes[] = $field."=".$case->{$field}->format("Y-m-d")." (".($case->datePrecisionFor($field) ?: "day").")";
        }
    }

    if (! empty($row["clear_release"]) && $case->release_date) {
        $caseNotes[] = "release cleared (was ".$case->release_date->format("Y-m-d").")";
        $case->setPartialDate("release_date", null);
    }

    foreach (["convicted", "sentence"] as $field) {
        if (array_key_exists($field, $row) && $case->{$field} != $row[$field]) {
            $case->{$field} = $row[$field];
            $caseNotes[] = $field;
        }
    }

    if ($caseNotes) {
        $case->save();
    }

    echo "  case: ", ($caseNotes ? implode("; ", $caseNotes) : "already correct"), "\n";
}

$cohort = Prisoner::withoutGlobalScopes()->get()->filter(
    fn ($p) => in_array("Camden 28", (array) $p->affiliation)
);
echo "\nCamden 28 cohort: ", $cohort->count(),
     "  with a birthdate: ", $cohort->filter(fn ($x) => $x->birthdate)->count(),
     "  with an arrest date: ", $cohort->filter(fn ($x) => $x->cases->whereNotNull("arrest_date")->count())->count(), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
