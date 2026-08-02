#!/usr/bin/env bash
#
# BATCH 108 -- the duplicate merges and case-data corrections from
# the release audit (RELEASE-AUDIT-NOTES.md), per the curator, with
# the standing rule: NOTHING IS DELETED FROM DESCRIPTIONS.
#
#   THREE MERGES — each pair is one person twice:
#     jacob-hoopes            <- robert-hoopes         (Robert Jacob Hoopes)
#     daniel-sanchez-estrada  <- daniel-rolando-sanchez-estrada
#     ricardo-palmera         <- juvenal-ovidio-ricardo-palmera-pineda
#   The kept record gains the dropped record's ENTIRE biography as an
#   appended passage (guarded, verbatim — no prose is lost), plus any
#   AKA, ideology, and case fields the kept record lacked. The
#   dropped row and its redundant case rows then delete.
#
#   SIX CORRECTIONS:
#     - Contompasis and Fran Thompson lose their mislabeled "Draft
#       Evasion" charges for what the convictions actually were (the
#       Albany Capitol stabbing; first-degree murder).
#     - Kenneth Whitmore was NEVER paroled: the false 2016 release
#       date clears from his case, the sentence text corrects, and
#       the biography keeps its original text with a correction
#       appended (per the no-deletion rule).
#     - Eric Brandt's sentence corrects from 48 months to the actual
#       12 years, with the new federal case noted.
#     - Jeffrey Weinhaus gets his real MODOC number (1261778).
#     - Ines Soto's sentencing date corrects June 23 -> July 1, 2026
#       (a surgical in-place date fix; nothing else touched).
#
# Run from the repo root, after git pull (after batch 106):
#   bash database/data/run-batch-108.sh

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
echo "  Batch 108 — duplicate merges + case-data corrections"
echo "==================================================================="

fix_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch108.json")), true);

if (! $payload || empty($payload["merges"])) {
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

    if (! empty($m["ideology_add"])) {
        $ideos = (array) ($keep->ideologies ?? []);
        if (! in_array($m["ideology_add"], $ideos, true)) {
            $ideos[] = $m["ideology_add"];
            $keep->ideologies = array_values($ideos);
            $notes[] = "ideology added";
        }
    }

    $case = $keep->cases->first();

    if ($case) {
        $case->setRelation("prisoner", $keep);
        $caseDirty = false;

        if (! empty($m["copy_case_incarceration"]) && $case->incarceration_date === null) {
            [$y, $mo, $d] = array_pad($m["copy_case_incarceration"], 3, null);
            $case->setPartialDate("incarceration_date", $y, $mo, $d);
            $caseDirty = true;
            $notes[] = "case incarceration copied";
        }

        if (! empty($m["copy_case_convicted"]) && ! $case->convicted) {
            $case->convicted = $m["copy_case_convicted"];
            $caseDirty = true;
            $notes[] = "case convicted copied";
        }

        if (! empty($m["copy_case_sentence"]) && ! $case->sentence) {
            $case->sentence = $m["copy_case_sentence"];
            $caseDirty = true;
            $notes[] = "case sentence copied";
        }

        if ($caseDirty) { $case->save(); }
    }

    if ($notes) { $keep->save(); }

    if ($drop) {
        foreach ($drop->cases as $c) { $c->delete(); }
        $drop->delete();
        $notes[] = "duplicate row deleted";
    }

    echo "  ", ($notes ? implode("; ", $notes) : "nothing to do"), "\n";
}

foreach ($payload["corrections"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    echo "\nFIX ", $row["slug"], "\n";

    if (! $p) { echo "  NOT FOUND — skipped\n"; continue; }

    $notes = [];

    if (! empty($row["inmate_number"]) && $p->inmate_number !== $row["inmate_number"]) {
        $p->inmate_number = $row["inmate_number"];
        $notes[] = "inmate_number=".$row["inmate_number"];
    }

    if (! empty($row["desc_replace"])) {
        [$from, $to] = $row["desc_replace"];
        if (str_contains((string) $p->description, $from)) {
            $p->description = str_replace($from, $to, (string) $p->description);
            $notes[] = "description date corrected";
        } elseif (! str_contains((string) $p->description, $to)) {
            $notes[] = "desc_replace target not found — left alone";
        }
    }

    if (! empty($row["append"]) && ! str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
        $p->description = trim((string) $p->description)."\n\n".$row["append"];
        $notes[] = "correction appended";
    }

    if ($notes) { $p->save(); }

    $case = null;
    if (! empty($row["case_match_charges"])) {
        $case = $p->cases->first(fn ($c) => $c->charges && str_contains($c->charges, $row["case_match_charges"]));
    } elseif (! empty($row["case_match_sentence"])) {
        $case = $p->cases->first(fn ($c) => $c->sentence && str_contains($c->sentence, $row["case_match_sentence"]));
    }

    if ($case) {
        $case->setRelation("prisoner", $p);
        $caseDirty = false;

        if (! empty($row["case_set_charges"]) && $case->charges !== $row["case_set_charges"]) {
            $case->charges = $row["case_set_charges"];
            $caseDirty = true;
            $notes[] = "charges corrected";
        }

        if (! empty($row["case_set_sentence"]) && $case->sentence !== $row["case_set_sentence"]) {
            $case->sentence = $row["case_set_sentence"];
            $caseDirty = true;
            $notes[] = "sentence corrected";
        }

        if (! empty($row["case_sentence_replace"])) {
            [$from, $to] = $row["case_sentence_replace"];
            if ($case->sentence && str_contains($case->sentence, $from)) {
                $case->sentence = str_replace($from, $to, $case->sentence);
                $caseDirty = true;
                $notes[] = "sentence text corrected";
            }
        }

        if (! empty($row["case_clear_release"]) && $case->release_date !== null) {
            $case->setPartialDate("release_date", null);
            $caseDirty = true;
            $notes[] = "false release date cleared";
        }

        if ($caseDirty) { $case->save(); }
    } elseif (! empty($row["case_match_charges"]) || ! empty($row["case_match_sentence"])) {
        $notes[] = "matching case not found (already corrected?)";
    }

    echo "  ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "merges-and-corrections" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 108 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
