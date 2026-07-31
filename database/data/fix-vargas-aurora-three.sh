#!/usr/bin/env bash
#
# DIEGO VARGAS IS NO LONGER INCARCERATED, and his two co-defendants from
# the Aurora ATM complaint join the database with the little that is
# actually documented about them.
#
# ------------------------------------------------------------------
# DIEGO VARGAS
# ------------------------------------------------------------------
#
# His record was a stub — "federal arson", no dates but a release of
# January 10, 2025, flags still IN CUSTODY at FCI Schuylkill. Everything
# moves to the dossier:
#
#   - RELEASED. A BOP-derived inmate index reports February 4, 2025 for
#     register 55070-424 — high confidence, pending direct BOP
#     confirmation, and that hedge is stored in the sentence text. The
#     old stored release of January 10 disagreed with it and with the
#     custody flag simultaneously.
#   - THE MAIN CASE gains its full chronology: arrested October 7, 2020
#     on the explosion indictment, MCC Chicago pretrial, guilty plea
#     June 10, 2021, sentenced March 3, 2022 by Judge Elaine E. Bucklo
#     to the mandatory-minimum sixty months, Allenwood Medium then
#     Schuylkill, released February 4, 2025. The counter reads 1,581
#     days — the dossier arithmetic of four years, three months and
#     twenty-eight days.
#   - THE CONVICTION IS ONE COUNT — maliciously attempting to damage or
#     destroy a building by an explosive device, for the June 1, 2020
#     Egg Harbor Cafe explosion — not "federal arson" generically and
#     not multiple arson convictions. The ATM incident was admitted as
#     RELEVANT CONDUCT.
#   - A SECOND CASE ROW carries the July 1, 2020 ATM arrest, with no
#     span entered: public records do not establish how long he was held
#     after that first arrest, and an unknown is stored as an unknown.
#   - Register number, Illinois, and the booking photo the complaint
#     itself labels his: paragraph 18, "The booking photo of VARGAS is
#     shown below."
#
# ------------------------------------------------------------------
# THE CO-DEFENDANTS: what the record supports, and no more
# ------------------------------------------------------------------
#
# The curator asked whether Fermin Ocampo-Tellez and Michael Gomez were
# incarcerated and for how long. THE HONEST ANSWER IS THAT THE PUBLIC
# RECORD DOES NOT SAY. What it does establish:
#
#   - Both were ARRESTED July 1, 2020 on the same conspiracy-to-commit-
#     bank-theft complaint (five-year maximum) and made initial
#     appearances in federal court in Chicago — DOJ press release,
#     July 2, 2020.
#   - The docket (1:20-cr-00331, Judge Matthew F. Kennelly) ran long:
#     RECAP fragments show trial-date continuances, a sealed pretrial
#     status report on Gomez, an appeal in forma pauperis, and a
#     petition for a writ of habeas corpus ad prosequendum to PRODUCE
#     OCAMPO-TELLEZ — which means he was in custody somewhere else at
#     that point, though where and for how long is not established.
#   - NO sentencing record, custody span or release for either man was
#     located in the accessible record.
#
# So both enter with the arrest, the charge, the judge, and dispositions
# marked unresolved — NO custody spans, NO invented outcomes, NO
# minor-case flags (the flag asserts a short custody; their custody is
# simply unknown). Ages are stated as reported in July 2020 in the
# biographies and not stored as birthdates. RACE IS NOT SET on either:
# nothing documents it, and a surname is not evidence.
#
# GOMEZ'S SLUG IS michael-gomez-aurora, although the plain slug is
# free, because the name is among the most common in the database's
# orbit and the bare slug would seed the next duplicate audit — the
# james-johnson-everett rule.
#
# PHOTOS: all three come from the criminal complaint the DOJ published,
# each labeled by the document itself — paragraph 17 "the state
# identification photograph of OCAMPO-TELLEZ", paragraph 18 "the
# booking photo of VARGAS", paragraph 19 "the state identification
# photograph of GOMEZ". The two ID photos are small at source (about
# 228 pixels wide) and are enlarged to the site convention; the
# identification could not be stronger.
#
# Idempotent: people matched by slug, Vargas cases matched by year so a
# second run updates rather than duplicates, every field compared.
#
# Run from the repo root:
#   bash database/data/fix-vargas-aurora-three.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

for slug in diego-vargas fermin-ocampo-tellez michael-gomez-aurora; do
    SRC="database/data/photos/${slug}.jpg"
    if [ -f "$SRC" ]; then
        cp -f "$SRC" "${DST_DIR}/${slug}.jpg"
        echo "copied ${slug}.jpg"
    fi
done

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/vargas-aurora-three.json")), true);

if (! $payload) {
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
    $model->setPartialDate($field, $y, $m, $d);

    return $wasDate !== ($model->{$field} ? $model->{$field}->format("Y-m-d") : null);
};

$applyCase = function (Prisoner $p, array $spec, ?PrisonerCase $case) use ($applyDate): void {
    $isNew = ! $case;

    if ($isNew) {
        $case = new PrisonerCase;
        $case->prisoner_id = $p->id;
    }

    $case->setRelation("prisoner", $p);

    $notes = [];

    foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date", "sentenced" => "sentenced_date", "release" => "release_date"] as $k => $field) {
        if (array_key_exists($k, $spec) && $applyDate($case, $field, $spec[$k])) {
            $notes[] = $field."=".($case->{$field} ? $case->{$field}->format("Y-m-d") : "null");
        }
    }

    foreach (["judge", "charges", "convicted", "sentence"] as $field) {
        if (array_key_exists($field, $spec) && $case->{$field} != $spec[$field]) {
            $case->{$field} = $spec[$field];
            $notes[] = $field;
        }
    }

    if ($isNew || $notes) {
        $case->save();
    }

    echo "      case ", ($isNew ? "NEW  " : "     "),
         ($notes ? implode(", ", $notes) : "unchanged"),
         "   days=", ($case->imprisoned_for_days === null ? "null" : $case->imprisoned_for_days), "\n";
};

// ---- Vargas ------------------------------------------------------------

$v = $payload["vargas"];
$p = Prisoner::withoutGlobalScopes()->where("slug", $v["slug"])->with("cases")->first();

if (! $p) {
    echo "NOT FOUND: ", $v["slug"], "\n";
} else {
    echo $p->slug, "\n";

    $notes = [];

    foreach (["in_custody", "released", "inmate_number", "state", "description"] as $f) {
        if ($p->{$f} != $v[$f]) {
            $p->{$f} = $v[$f];
            $notes[] = $f;
        }
    }

    $rel = "prisoners/diego-vargas.jpg";
    if (is_file(storage_path("app/public/".$rel)) && $p->photo !== $rel) {
        $p->photo = $rel;
        $notes[] = "photo attached";
    }

    if ($notes) {
        $p->save();
    }

    echo "  ", ($notes ? implode("; ", $notes) : "person already correct"), "\n";

    // Main case = the existing row (it carried the wrong release), matched as
    // the row whose year is 2020-2025 or the oldest row; ATM case matched by
    // its July 2020 arrest.
    $p->load("cases");
    $main = $p->cases->first(function ($c) {
        return ! $c->arrest_date || $c->arrest_date->format("Y-m-d") !== "2020-07-01";
    });
    $atm = $p->cases->first(function ($c) {
        return $c->arrest_date && $c->arrest_date->format("Y-m-d") === "2020-07-01";
    });

    $applyCase($p, $v["main_case"], $main);
    $applyCase($p, $v["atm_case"], $atm);
}

// ---- the two co-defendants --------------------------------------------

foreach ($payload["new"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();
    $isNew = ! $p;

    if ($isNew) {
        $p = new Prisoner;
        $p->slug = $row["slug"];
    }

    foreach (["name", "first_name", "last_name", "gender", "era", "state", "description"] as $f) {
        if ($p->{$f} !== $row[$f]) {
            $p->{$f} = $row[$f];
        }
    }

    if ($p->ideologies != $row["ideologies"]) {
        $p->ideologies = $row["ideologies"];
    }

    $p->in_custody = false;
    $p->released = true;

    $rel = "prisoners/".$row["slug"].".jpg";
    if (is_file(storage_path("app/public/".$rel)) && $p->photo !== $rel) {
        $p->photo = $rel;
    }

    $p->save();
    $p->load("cases");

    echo "\n", str_pad($p->slug, 24), ($isNew ? "CREATED" : "already exists — updated in place"), "\n";

    $applyCase($p, $row["cases"][0], $p->cases->first());
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
