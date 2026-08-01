#!/usr/bin/env bash
#
# THE RIDGLAN FARMS DEFENDANTS WERE JAILED TWICE, and their entries
# combined the two custody episodes into one; GABRIELA SALDANA had the
# wrong arrest date and the wrong jail.
#
# ONE PROSECUTION, TWO CUSTODY EPISODES for Aswani, Wyrzykowski and
# Lunsky. The existing single case row conflated them: the arrest of
# March 15, 2026 with the felony counts, plea and trial that in fact
# came later. The split:
#
#   CASE 1 — THE MARCH RESCUE DETENTION. Arrest and custody March 15,
#   2026, Dane County Jail, released WITHOUT formal charges being
#   filed at that time: Aswani March 17 (among the final five
#   released), Wyrzykowski March 17 (two nights, by his own on-camera
#   account), Lunsky by March 16 — she was NOT among the final five,
#   so she was already out, but no booking record distinguishes the
#   15th from the 16th, and her release enters at APPROXIMATE
#   precision on the curator-s database-entry line of March 16.
#
#   CASE 2 — THE APRIL RETURN TO JAIL, NEW ROW. Arrested April 18,
#   2026 (sheriff-confirmed) on the four felony counts filed over the
#   March rescue; held approximately three days; $10,000 cash bond set
#   April 21 and all three released that evening. The felony charges,
#   the not-guilty plea of May 21, and the September 28 trial before
#   Judge Hyland MOVE from the old conflated row to this one, where
#   they belong — the $10,000 bond in the biographies was always this
#   custody-s bond.
#
# FLAGS: all three become released=true (they are out on bond;
# awaiting_trial stays true), and all three gain the minor_case
# duration flag — about five days of total custody, the batch 42
# duration filter, not a significance judgment.
#
# GABRIELA SALDANA: the booking record dates her arrest SHORTLY AFTER
# 2:10 A.M. ON APRIL 16, 2026 — not April 15 as her entry said — with
# jail admission at 4:15 a.m.; the jail was the TURNER GUILFORD
# KNIGHT CORRECTIONAL CENTER, not the Miami-Dade Pre-Trial Detention
# Center (the old institution is detached, never deleted). Released
# on the $5,000 bond by April 20, 2026 per FIU student reporting; the
# exact discharge timestamp is not established, so the release enters
# at approximate precision. She too becomes released=true with the
# minor_case duration flag.
#
# The prose carries apostrophes, so the payload lives in
# database/data/fixes/ridglan-two-jailings.json.
#
# Idempotent: the March row is matched by its 2026-03-15 arrest, the
# April row by its 2026-04-18 arrest (created only if absent), and
# every field is compared before writing.
#
# Run from the repo root:
#   bash database/data/fix-ridglan-two-jailings.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/ridglan-two-jailings.json")), true);

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

$dane = Institution::firstOrCreate(["name" => "Dane County Jail"], ["city" => "Madison", "state" => "Wisconsin"]);

foreach ($payload["ridglan"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) {
        echo "\nNOT FOUND: ", $row["slug"], " — skipped\n";
        continue;
    }

    echo "\n", $p->slug, "\n";

    $notes = [];

    if (! empty($row["aka"]) && $p->aka !== $row["aka"]) {
        $p->aka = $row["aka"];
        $notes[] = "aka";
    }

    if (! $p->released) {
        $p->released = true;
        $notes[] = "released=true (out on bond)";
    }

    if (! $p->minor_case) {
        $p->minor_case = true;
        $notes[] = "minor_case=true (duration filter)";
    }

    if (! empty($row["append"]) && ! str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
        $p->description = trim((string) $p->description)."\n\n".$row["append"];
        $notes[] = "description appended";
    }

    if ($notes) {
        $p->save();
    }

    echo "  ", ($notes ? implode("; ", $notes) : "person unchanged"), "\n";

    // Case 1: the March rescue detention (the existing conflated row).
    $march = $p->cases->first(fn ($c) => $c->arrest_date && $c->arrest_date->format("Y-m-d") === "2026-03-15");

    if (! $march) {
        echo "  MARCH ROW NOT FOUND — skipped\n";
        continue;
    }

    $march->setRelation("prisoner", $p);
    $mNotes = [];

    if ($applyDate($march, "incarceration_date", [2026, 3, 15])) {
        $mNotes[] = "incarceration=2026-03-15";
    }
    if ($applyDate($march, "release_date", $row["march_release"])) {
        $mNotes[] = "release=".$march->release_date->format("Y-m-d")." (".($march->datePrecisionFor("release_date") ?: "day").")";
    }

    foreach (["charges" => $payload["march_charges"], "convicted" => $payload["march_convicted"], "sentence" => $row["march_sentence"]] as $f => $v) {
        if ($march->{$f} != $v) {
            $march->{$f} = $v;
            $mNotes[] = $f;
        }
    }

    if (! $march->institution_id) {
        $march->institution_id = $dane->id;
        $mNotes[] = "institution=Dane County Jail";
    }

    if ($mNotes) {
        $march->save();
    }

    echo "  March case: ", ($mNotes ? implode("; ", $mNotes) : "already correct"),
         "   days=", ($march->imprisoned_for_days ?? "null"), "\n";

    // Case 2: the April return to jail on the felony counts.
    $april = $p->cases->first(fn ($c) => $c->arrest_date && $c->arrest_date->format("Y-m-d") === "2026-04-18");
    $isNew = ! $april;

    if ($isNew) {
        $april = new PrisonerCase;
        $april->prisoner_id = $p->id;
    }

    $april->setRelation("prisoner", $p);
    $aNotes = [];

    foreach (["arrest_date" => [2026, 4, 18], "incarceration_date" => [2026, 4, 18], "release_date" => [2026, 4, 21]] as $f => $spec) {
        if ($applyDate($april, $f, $spec)) {
            $aNotes[] = $f."=".$april->{$f}->format("Y-m-d");
        }
    }

    foreach (["charges" => $payload["april_charges"], "convicted" => $payload["april_convicted"], "sentence" => $row["april_sentence"]] as $f => $v) {
        if ($april->{$f} != $v) {
            $april->{$f} = $v;
            $aNotes[] = $f;
        }
    }

    if (! $april->institution_id) {
        $april->institution_id = $dane->id;
        $aNotes[] = "institution=Dane County Jail";
    }

    if ($isNew || $aNotes) {
        $april->save();
    }

    echo "  April case ", ($isNew ? "NEW: " : ": "), ($aNotes ? implode("; ", $aNotes) : "already correct"),
         "   days=", ($april->imprisoned_for_days ?? "null"), "\n";
}

// ---- Saldana -----------------------------------------------------------

$s = $payload["saldana"];
$p = Prisoner::withUnderReview()->where("slug", $s["slug"])->with("cases.institution")->first();

if (! $p) {
    echo "\nNOT FOUND: ", $s["slug"], "\n";
} else {
    echo "\n", $p->slug, "\n";

    $notes = [];

    if (! $p->released) {
        $p->released = true;
        $notes[] = "released=true (out on bond)";
    }

    if (! $p->minor_case) {
        $p->minor_case = true;
        $notes[] = "minor_case=true (duration filter)";
    }

    if (! empty($s["append"]) && ! str_contains((string) $p->description, mb_substr($s["append"], 0, 60))) {
        $p->description = trim((string) $p->description)."\n\n".$s["append"];
        $notes[] = "description appended";
    }

    if ($notes) {
        $p->save();
    }

    echo "  ", ($notes ? implode("; ", $notes) : "person unchanged"), "\n";

    $case = $p->cases->sortBy("created_at")->first();

    if ($case) {
        $case->setRelation("prisoner", $p);
        $cNotes = [];

        foreach (["arrest_date" => $s["arrest"], "incarceration_date" => $s["incarceration"], "release_date" => $s["release"]] as $f => $spec) {
            if ($applyDate($case, $f, $spec)) {
                $cNotes[] = $f."=".$case->{$f}->format("Y-m-d")." (".($case->datePrecisionFor($f) ?: "day").")";
            }
        }

        if ($case->sentence != $s["sentence"]) {
            $case->sentence = $s["sentence"];
            $cNotes[] = "sentence";
        }

        $tgk = Institution::firstOrCreate(
            ["name" => $s["institution"]],
            ["city" => $s["institution_city"], "state" => $s["institution_state"]]
        );
        if ($case->institution_id !== $tgk->id) {
            $was = $case->institution?->name;
            $case->institution_id = $tgk->id;
            $cNotes[] = "institution=".$tgk->name.($was ? " (was ".$was.")" : "");
        }

        if ($cNotes) {
            $case->save();
        }

        echo "  case: ", ($cNotes ? implode("; ", $cNotes) : "already correct"),
             "   days=", ($case->imprisoned_for_days ?? "null"), "\n";
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
