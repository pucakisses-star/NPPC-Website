#!/usr/bin/env bash
#
# THE TWO LARRY JACKSONS: splitting Karim Njabafudi from John England
# Morris Jr.
#
# A major identity correction from the curator's dossier. The database
# held two records that BETWEEN THEM conflated two different men who
# shared the alias "Larry Jackson":
#
#   karim-njabafundi — a thin RNA-11 record, name misspelled with an
#   extra "n" (the Amistad Research Center booklet and the federal
#   record spell it Njabafudi), arrest August 18, 1971, no conviction
#   detail at all.
#
#   larry-jackson — the conflated record: AKA "Karim Njabafudi",
#   affiliations Black Panther Party + NCCF + RNA, and TWO case rows —
#   an RNA row with a wrong arrest of August 17, 1971, an institution
#   reading "Mississippi state and federal courts" (not a prison), a
#   stray FCI Marianna mailing address, and the invented sentence
#   "2-10 years (typical for RNA-11 convicted defendants)"; plus the
#   November 26, 1970 New Orleans Desire-raid Panther case.
#
# THE TWO MEN. Karim Hekima Oman Wadu Njabafudi was born Larry Jackson
# in New Orleans and was about FIFTEEN when arrested in the August 18,
# 1971 raid on the RNA residence in Jackson, Mississippi. The "Larry
# Jackson" of the November 26, 1970 Desire-project raid was JOHN
# ENGLAND MORRIS JR., an adult Panther the federal appellate record
# names as "John England Morris Jr., a/k/a Larry Jackson" — and whom
# contemporary research describes as a fugitive wanted in California,
# a description incompatible with a fifteen-year-old.
#
# WHAT THIS RUN DOES:
#
#   KARIM keeps the karim record. Name corrected to Njabafudi — the
#   rename regenerates the slug, breaking the old URL, accepted for a
#   demonstrable error (the Herkner rule). He gains the aliases, a
#   circa-1955 birth year (the Mississippi Supreme Court put him at
#   fifteen and two-thirds on the day of the raid; a federal footnote
#   said sixteen; NO exact date is inferred, per the dossier), the full
#   biography, and the custody record the thin row lacked: arrest and
#   custody August 18, 1971, convicted and sentenced to life
#   September 25, 1972 after a trial moved to Lauderdale County,
#   affirmed March 24, 1975, rehearing denied May 12, 1975, released
#   November 1979 at MONTH precision, at Parchman. The fatal shot was
#   attributed at trial to Hekima Ana; Karim was convicted as an aider
#   and abettor. The claimed federal fifteen-year term is NOT recorded:
#   he was dismissed from the adult federal case as an unwaived
#   juvenile, and no judgment proving a separate juvenile prosecution
#   has been located — the sentence text says exactly that.
#
#   MORRIS keeps the larry-jackson record, renamed John England
#   Morris Jr. (slug regenerates; "Larry Jackson" stays as the AKA).
#   The RNA case row is DELETED from his record — its wrong arrest
#   date, its court-as-institution, its Marianna address and its
#   invented "2-10 years" go with it. The RNA affiliation and the New
#   Afrikan Independence ideology come off; Black Panther Party and
#   NCCF stay. His Desire case keeps its documented facts — arrest
#   November 26, 1970, Orleans Parish Prison, attempted-murder and
#   federal firearms charges, conviction for possessing an
#   unregistered automatic rifle — and states plainly that his
#   sentence and custody span are not established, so no release is
#   entered.
#
# THE PHOTOGRAPH IS PURSUED, NOT ATTACHED. The strongest authenticated
# image is the Associated Press group photograph of the RNA-11 after
# their August 18, 1971 arraignment, displayed by the Mississippi
# Civil Rights Museum — but it shows eleven chained defendants with no
# per-figure caption, so no crop can be anchored to Karim by anything
# better than guessing which figure is the youngest. That fails the
# identification rule. Both slugs are pre-listed in the attach loop
# below; drop karim-njabafudi.jpg or john-england-morris-jr.jpg into
# database/data/photos/ and re-run to complete.
#
# The prose carries apostrophes, so the payload lives in
# database/data/fixes/njabafudi-morris-split.json.
#
# Idempotent: each man is matched by his old slug OR his new one, so a
# second run after the renames finds them again; the RNA-row delete
# matches by its wrong arrest date and finds nothing on a re-run;
# every field is compared before writing.
#
# Run from the repo root:
#   bash database/data/fix-njabafudi-morris.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

for slug in karim-njabafudi john-england-morris-jr; do
    SRC="database/data/photos/${slug}.jpg"
    if [ -f "$SRC" ]; then
        cp -f "$SRC" "${DST_DIR}/${slug}.jpg"
        echo "copied ${slug}.jpg"
    else
        echo "no source image for ${slug} — skipped (the AP group photo cannot anchor an individual crop)"
    fi
done

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/njabafudi-morris-split.json")), true);

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

    [$y, $m, $d, $circa] = array_pad($spec, 4, null);
    $wasDate = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d, (bool) $circa);

    return $wasDate !== ($model->{$field} ? $model->{$field}->format("Y-m-d") : null)
        || $wasPrec !== $model->datePrecisionFor($field);
};

$findBySlugs = function (array $slugs): ?Prisoner {
    foreach ($slugs as $slug) {
        $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->with("cases.institution")->first();
        if ($p) {
            return $p;
        }
    }

    return null;
};

// ---- Karim Njabafudi ---------------------------------------------------

$k = $payload["karim"];
$p = $findBySlugs([$k["slug"], "karim-njabafudi"]);

if (! $p) {
    echo "NOT FOUND: ", $k["slug"], "\n";
} else {
    echo $p->slug, "\n";

    $notes = [];

    if ($p->name !== $k["name"]) {
        $oldSlug = $p->slug;
        $p->name = $k["name"];
        $p->last_name = "Njabafudi";
        $notes[] = "name corrected (slug will regenerate from ".$oldSlug.")";
    }

    if ($p->aka !== $k["aka"]) {
        $p->aka = $k["aka"];
        $notes[] = "aka";
    }

    if ($p->description !== $k["description"]) {
        $p->description = $k["description"];
        $notes[] = "description";
    }

    if ($applyDate($p, "birthdate", $k["birth"])) {
        $notes[] = "birthdate=".$p->birthdate->format("Y-m-d")." (".($p->datePrecisionFor("birthdate") ?: "day").")";
    }

    $rel = "prisoners/karim-njabafudi.jpg";
    if (is_file(storage_path("app/public/".$rel)) && $p->photo !== $rel) {
        $p->photo = $rel;
        $notes[] = "photo attached";
    }

    if ($notes) {
        $p->save();
        $p->refresh();
    }

    echo "  ", ($notes ? implode("; ", $notes) : "person already correct"), "  slug now: ", $p->slug, "\n";

    $case = $p->cases->sortBy("created_at")->first();

    if (! $case) {
        echo "  no case row — skipped\n";
    } else {
        $case->setRelation("prisoner", $p);

        $spec = $k["case"];
        $caseNotes = [];

        foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date", "sentenced" => "sentenced_date", "release" => "release_date"] as $key => $field) {
            if (array_key_exists($key, $spec) && $applyDate($case, $field, $spec[$key])) {
                $caseNotes[] = $field."=".($case->{$field} ? $case->{$field}->format("Y-m-d") : "null")
                    ." (".($case->datePrecisionFor($field) ?: "day").")";
            }
        }

        foreach (["charges", "convicted", "sentence"] as $field) {
            if (array_key_exists($field, $spec) && $case->{$field} != $spec[$field]) {
                $case->{$field} = $spec[$field];
                $caseNotes[] = $field;
            }
        }

        $inst = Institution::firstOrCreate(
            ["name" => $spec["institution"]],
            ["city" => $spec["institution_city"], "state" => $spec["institution_state"]]
        );
        if ($case->institution_id !== $inst->id) {
            $was = $case->institution?->name;
            $case->institution_id = $inst->id;
            $caseNotes[] = "institution=".$inst->name.($was ? " (was ".$was.")" : "");
        }

        if ($caseNotes) {
            $case->save();
        }

        echo "  case: ", ($caseNotes ? implode("; ", $caseNotes) : "already correct"),
             "   days=", ($case->imprisoned_for_days === null ? "null" : $case->imprisoned_for_days), "\n";
    }
}

// ---- John England Morris Jr. ------------------------------------------

$m = $payload["morris"];
$p = $findBySlugs([$m["slug"], "john-england-morris-jr"]);

if (! $p) {
    echo "\nNOT FOUND: ", $m["slug"], "\n";
} else {
    echo "\n", $p->slug, "\n";

    $notes = [];

    if ($p->name !== $m["name"]) {
        $oldSlug = $p->slug;
        $p->name = $m["name"];
        $p->first_name = "John";
        $p->middle_name = "England";
        $p->last_name = "Morris";
        $notes[] = "renamed (slug will regenerate from ".$oldSlug.")";
    }

    if ($p->aka !== $m["aka"]) {
        $p->aka = $m["aka"];
        $notes[] = "aka";
    }

    if ($p->state !== $m["state"]) {
        $p->state = $m["state"];
        $notes[] = "state";
    }

    if ($p->description !== $m["description"]) {
        $p->description = $m["description"];
        $notes[] = "description";
    }

    if ((array) $p->affiliation != $m["affiliation"]) {
        $removed = array_diff((array) $p->affiliation, $m["affiliation"]);
        $p->affiliation = $m["affiliation"];
        $notes[] = "affiliation".($removed ? " -= ".implode(", ", $removed) : " set");
    }

    if ((array) $p->ideologies != $m["ideologies"]) {
        $removed = array_diff((array) $p->ideologies, $m["ideologies"]);
        $p->ideologies = $m["ideologies"];
        $notes[] = "ideologies".($removed ? " -= ".implode(", ", $removed) : " set");
    }

    $rel = "prisoners/john-england-morris-jr.jpg";
    if (is_file(storage_path("app/public/".$rel)) && $p->photo !== $rel) {
        $p->photo = $rel;
        $notes[] = "photo attached";
    }

    if ($notes) {
        $p->save();
        $p->refresh();
    }

    echo "  ", ($notes ? implode("; ", $notes) : "person already correct"), "  slug now: ", $p->slug, "\n";

    // Delete the RNA case row — the wrong arrest date marks it.
    $p->load("cases.institution");

    foreach ($p->cases as $c) {
        if ($c->arrest_date && $c->arrest_date->format("Y-m-d") === $m["drop_case_arrest"]) {
            echo "  deleted the RNA case row (arrest ", $c->arrest_date->format("Y-m-d"),
                 ", institution ", ($c->institution?->name ?? "none"),
                 ", sentence ", json_encode($c->sentence), ") — those facts belong to Karim Njabafudi\n";
            $c->delete();
        }
    }

    $p->load("cases.institution");
    $case = $p->cases->first(function ($c) use ($m) {
        return $c->arrest_date && $c->arrest_date->format("Y-m-d") === implode("-", [
            $m["case"]["arrest"][0],
            str_pad((string) $m["case"]["arrest"][1], 2, "0", STR_PAD_LEFT),
            str_pad((string) $m["case"]["arrest"][2], 2, "0", STR_PAD_LEFT),
        ]);
    }) ?? $p->cases->first();

    if (! $case) {
        echo "  no Desire case row — skipped\n";
    } else {
        $case->setRelation("prisoner", $p);

        $spec = $m["case"];
        $caseNotes = [];

        if ($applyDate($case, "arrest_date", $spec["arrest"])) {
            $caseNotes[] = "arrest_date=".$case->arrest_date->format("Y-m-d");
        }

        if ($case->release_date) {
            $caseNotes[] = "release cleared (was ".$case->release_date->format("Y-m-d").") — custody span not established";
            $case->setPartialDate("release_date", null);
        }

        foreach (["charges", "convicted", "sentence"] as $field) {
            if (array_key_exists($field, $spec) && $case->{$field} != $spec[$field]) {
                $case->{$field} = $spec[$field];
                $caseNotes[] = $field;
            }
        }

        if (! $case->institution_id) {
            $inst = Institution::firstOrCreate(
                ["name" => $spec["institution"]],
                ["city" => $spec["institution_city"], "state" => $spec["institution_state"]]
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

    echo "  now ", $p->cases()->count(), " case row(s)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
