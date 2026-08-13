#!/usr/bin/env bash
#
# BATCH 220 -- two people who each have two records, and a man filed under
# his cover name.
#
#   CONSUEWELLA DOTSON AFRICA appears twice and neither record spells her
#   name right. Consuella has the 1990 Tribunal detail and no case;
#   Consusuella has the case and a sentence saying she died in prison in
#   1998 -- which is Merle Africa, who did. Consuewella was paroled in
#   1994 and died on 16 June 2021 at the Hospital of the University of
#   Pennsylvania, aged 67. The record with the case is kept, the name is
#   corrected, the other is folded in.
#
#   MICHAEL DAVIS AFRICA appears twice and the kept record says so itself:
#   its own description reads "often reported as Mike Africa Sr." The
#   Mike Africa Sr record held two near-identical cases of its own.
#   Michael Davis Africa is kept -- it has the photograph, the birth date
#   and the MOVE 9 tag -- the sentence text comes across, and its Norfolk
#   County Jail institution is cleared. Norfolk County Jail is a
#   Massachusetts jail and one of the fourteen contaminated sets; he was
#   in Pennsylvania.
#
#   RAMON LABANINO is filed as Luis Medina, which was his cover name. His
#   own aka field and the first sentence of his own description both give
#   the real one. The alias was the headline and the man was underneath
#   it.
#
#   THE CORRECTION IS APPENDED, NOT SUBSTITUTED. The false 1998 sentence
#   stays and a correction naming it follows, which is the rule batch 108
#   set for this archive: nothing is deleted from descriptions,
#   corrections are added.
#
#   NO SLUG MOVES. HasSlug generates only on create, so luis-medina and
#   consusuella-dotson-africa stay put and no existing link breaks. Both
#   are now aliases or misspellings of the displayed name, which is a
#   smaller problem than a dead URL.
#
#   THIS BATCH DELETES TWO PRISONER RECORDS. prisoner_cases cascades, so
#   their cases go with them. Neither model soft-deletes: this is
#   permanent. Everything on both records is printed in full before
#   anything is removed, and that output is the only remaining record of
#   what was there. Read it before you close the terminal.
#
#   Idempotent: if the record to remove is already gone the merge is
#   reported as done and nothing is written.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-220.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

# tinker exits 0 even when the code inside throws; success is a sentinel
# the step prints as its last act.
run_tinker() {
    local label="$1" sentinel="$2" code="$3" out
    echo; echo "--- ${label}"
    out=$(php artisan tinker --execute="$code" 2>&1) || true
    printf '%s\n' "$out"
    if ! grep -q "$sentinel" <<<"$out"; then
        echo "  !! FAILED: ${label} — sentinel ${sentinel} missing (exception above?)"
        FAILED+=("${label}")
    fi
}

echo "==================================================================="
echo "  Batch 220 — duplicate records merged, one name restored"
echo "==================================================================="

MERGE_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch220.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$problems = [];

$dump = function (?Prisoner $p, string $label) {
    if (! $p) { echo "    ", $label, ": (gone)\n"; return; }

    echo "    ", $label, ": ", $p->name, " [", $p->slug, "]\n";
    echo "      aka ", ($p->aka ?: "-"), " | born ", ($p->birthdate ?: "-"),
        " | died ", ($p->death_date ?: "-"), " | photo ", ($p->photo ? "yes" : "no"),
        " | aff ", implode(", ", (array) $p->affiliation), "\n";

    foreach ($p->cases()->with("institution")->get() as $c) {
        echo "      case: ", ($c->institution?->name ?: "(no institution)"),
            " | arrest ", ($c->arrest_date ?: "-"),
            " | release ", ($c->release_date ?: "-"),
            " | sentence ", (mb_substr((string) $c->sentence, 0, 46) ?: "-"), "\n";
    }

    echo "      description (", mb_strlen((string) $p->description), " chars):\n";
    echo "        ", wordwrap((string) $p->description, 68, "\n        "), "\n";
};

foreach ($payload["merges"] as $m) {
    echo "\n  === ", $m["keep"], "  <-  ", $m["remove"], "\n\n";

    $keep = Prisoner::withoutGlobalScopes()->where("slug", $m["keep"])->first();
    $remove = Prisoner::withoutGlobalScopes()->where("slug", $m["remove"])->first();

    if (! $keep) { $problems[] = $m["keep"]." not found"; continue; }

    echo "  BEFORE\n";
    $dump($keep, "keep  ");
    $dump($remove, "remove");

    if (! $remove) {
        echo "\n    the duplicate is already gone — merge previously applied\n";
    }

    // Fold the removed description in before deleting anything.
    if ($remove && filled($remove->description)) {
        $probe = mb_substr((string) $remove->description, 0, 60);

        if (! str_contains((string) $keep->description, $probe)) {
            $keep->description = rtrim((string) $keep->description)."\n\n".$remove->description;
            echo "\n    folded in the other description\n";
        }
    }

    if (isset($m["rename"]) && $keep->name !== $m["rename"]) {
        echo "    name: ", $keep->name, "  ->  ", $m["rename"], "\n";
        $keep->aka = $keep->aka ? $keep->aka." / ".$keep->name : $keep->name;
        $keep->name = $m["rename"];
    }

    if (isset($m["aka_add"]) && ! str_contains((string) $keep->aka, $m["aka_add"])) {
        $keep->aka = $keep->aka ? $keep->aka." / ".$m["aka_add"] : $m["aka_add"];
        echo "    aka: ", $keep->aka, "\n";
    }

    if (isset($m["death_date"]) && optional($keep->death_date)->toDateString() !== $m["death_date"]) {
        $keep->death_date = $m["death_date"];
        $keep->in_custody = false;
        $keep->released = true;
        echo "    death date: ", $m["death_date"], " (released, so the flag stays released)\n";
    }

    if (isset($m["correction"]) && ! str_contains((string) $keep->description, "Correction, added on review")) {
        $keep->description = rtrim((string) $keep->description)."\n\n".$m["correction"];
        echo "    correction appended\n";
    }

    $keep->save();
    $keep->refresh();

    $case = $keep->cases()->with("institution")->first();

    if ($case && isset($m["case_sentence"]) && blank($case->sentence)) {
        $case->sentence = $m["case_sentence"];
        $case->save();
        echo "    case sentence: ", $m["case_sentence"], "\n";
    }

    if ($case && isset($m["clear_institution"]) && $case->institution?->name === $m["clear_institution"]) {
        $case->institution_id = null;
        $case->save();
        echo "    institution ", $m["clear_institution"], " cleared (contaminated)\n";
    }

    if ($remove) {
        $n = $remove->cases()->count();
        $remove->delete();
        echo "    deleted ", $m["remove"], " and its ", $n, " case(s)\n";
    }

    echo "\n  AFTER\n";
    $dump($keep->fresh(), "kept  ");

    if (Prisoner::withoutGlobalScopes()->where("slug", $m["remove"])->exists()) {
        $problems[] = $m["remove"]." still exists";
    }
}

echo "\n  === renames\n";

foreach ($payload["renames"] as $r) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $r["slug"])->first();

    if (! $p) { $problems[] = $r["slug"]." not found"; continue; }

    if ($p->name === $r["to"]) {
        echo "    already named ", $p->name, "\n";
    } else {
        echo "    ", $p->name, "  ->  ", $r["to"], "   [slug stays ", $p->slug, "]\n";
        $p->name = $r["to"];
        $p->save();
    }

    $p->refresh();
    echo "      aka ", ($p->aka ?: "-"), "\n";

    if ($p->name !== $r["to"]) { $problems[] = $r["slug"]." rename did not stick"; }
}

echo "\n  problems: ", count($problems), "\n";

foreach ($problems as $b) { echo "    !! ", $b, "\n"; }

echo "\n  ", wordwrap($payload["deletion_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["slug_note"], 72, "\n  "), "\n";

if (count($problems) === 0) { echo "\nB220-OK\n"; }
'

run_tinker "merge-duplicates" "B220-OK" "$MERGE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 220 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
