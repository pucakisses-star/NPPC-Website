#!/usr/bin/env bash
#
# BATCH 218 -- group tags, an alias, an escape timeline, one more bad
# institution.
#
#   CHECKING THE WIDER itsabouttimebpp COVERAGE against this database
#   found almost everyone already here and correctly described. What was
#   missing was the thread connecting them. The Soledad Brothers and the
#   San Francisco 8 had no group tag at all, the San Quentin Six was
#   missing Fleeta Drumgo, and four of the MOVE 9 were tagged only MOVE.
#   Every one of those people already had a page; the group was invisible
#   as a group.
#
#   TWO GROUPS ARE DELIBERATELY LEFT ALONE. The New Haven Panther
#   prosecution has ten plausible members here and the New Haven Nine is a
#   specific set of defendants -- tagging ten people into a group of nine
#   would be inventing a fact. Panther 21 carries twenty; the
#   twenty-first is probably Sekou Odinga or Kuwasi Balagoon, both present
#   and both untagged, but that belongs against the indictment, not a
#   guess.
#
#   ROBERT KING WILKERSON is the name Robert Hillary King was tried and
#   imprisoned under. His record carried neither it nor any other alias,
#   so a search for Wilkerson returned nothing.
#
#   THE ESCAPES. Russell Maroon Shoatz escaped SCI Huntingdon on 14
#   September 1977 and was out for 27 days, and escaped Fairview State
#   Hospital in March 1980, recaptured three days later after a gun
#   battle. None of that was on the record, and it is the reason his
#   imprisonment is not one unbroken span. Appended, not substituted --
#   nothing is deleted from descriptions.
#
#   ONE MORE CONTAMINATED INSTITUTION. Robert Hillary King is filed at FCI
#   Oxford, a federal prison in Wisconsin, and belongs at Angola. Batch
#   215 caught Woodfox and Wallace; King was missed because that batch
#   worked from the 27-name handout and he is not on it. Same fourteen
#   institutions, same problem, still roughly 165 rows outstanding.
#
#   Idempotent: tags are added only when absent, the alias and the
#   appended paragraph only when not already present.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-218.sh

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
echo "  Batch 218 — group tags, alias, escapes, one institution"
echo "==================================================================="

TAG_CODE='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch218.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$missing = 0; $wrote = 0;

$find = function (string $slug) use (&$missing) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

    if (! $p) { echo "  !! no prisoner at slug ", $slug, "\n"; $missing++; }

    return $p;
};

echo "\n  GROUP TAGS\n";

foreach ($payload["tags"] as $g) {
    echo "\n    ", $g["tag"], "\n";

    foreach ($g["slugs"] as $slug) {
        $p = $find($slug);

        if (! $p) { continue; }

        $aff = (array) ($p->affiliation ?? []);

        if (in_array($g["tag"], $aff, true)) {
            echo "      ", str_pad($p->name, 26), " already tagged\n";
            continue;
        }

        $aff[] = $g["tag"];
        $p->affiliation = array_values($aff);
        $p->save();
        $wrote++;

        echo "      ", str_pad($p->name, 26), " -> ", implode(", ", $aff), "\n";
    }
}

echo "\n  ALIAS\n";

foreach ($payload["aka"] as $e) {
    $p = $find($e["slug"]);

    if (! $p) { continue; }

    if ($p->aka && str_contains($p->aka, $e["add"])) {
        echo "    ", str_pad($p->name, 26), " already carries it\n";
    } else {
        $p->aka = $p->aka ? $p->aka." / ".$e["add"] : $e["add"];
        $p->save();
        $wrote++;
        echo "    ", str_pad($p->name, 26), " aka = ", $p->aka, "\n";
    }
}

echo "\n  DESCRIPTION APPEND\n";

foreach ($payload["append_description"] as $e) {
    $p = $find($e["slug"]);

    if (! $p) { continue; }

    // Match on a distinctive fragment rather than the whole paragraph, so a
    // later reword of the surrounding text does not cause a second append.
    if (str_contains((string) $p->description, "SCI Huntingdon")) {
        echo "    ", str_pad($p->name, 26), " escapes already recorded\n";
    } else {
        $was = mb_strlen((string) $p->description);
        $p->description = rtrim((string) $p->description)."\n\n".$e["text"];
        $p->save();
        $p->refresh();
        $wrote++;
        echo "    ", str_pad($p->name, 26), " ", $was, " -> ", mb_strlen($p->description), " chars\n";
    }
}

echo "\n  INSTITUTION\n";

foreach ($payload["institutions"] as $e) {
    $p = $find($e["slug"]);

    if (! $p) { continue; }

    $hit = null;

    foreach ($p->cases()->with("institution")->get() as $case) {
        if ($case->institution && $case->institution->name === $e["wrong"]) { $hit = $case; break; }
    }

    if (! $hit) {
        echo "    ", str_pad($p->name, 26), " no case at ", $e["wrong"], " — already done\n";
    } else {
        $t = $e["to"];
        $inst = Institution::where("name", $t["name"])->first()
            ?: Institution::create(["name" => $t["name"], "city" => $t["city"], "state" => $t["state"]]);

        $hit->institution_id = $inst->getKey();
        $hit->save();
        $wrote++;

        echo "    ", str_pad($p->name, 26), " ", $e["wrong"], "  ->  ", $inst->name, "\n";
    }
}

echo "\n  wrote ", $wrote, " change(s), missing prisoners ", $missing, "\n";

// Read it all back rather than trusting the writes.
echo "\n  verification\n";

$bad = [];

foreach ($payload["tags"] as $g) {
    foreach ($g["slugs"] as $slug) {
        $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

        if (! $p || ! in_array($g["tag"], (array) ($p->affiliation ?? []), true)) {
            $bad[] = $slug." missing tag ".$g["tag"];
        }
    }
}

foreach ($payload["aka"] as $e) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $p || ! str_contains((string) $p->aka, $e["add"])) { $bad[] = $e["slug"]." alias not set"; }
}

foreach ($payload["append_description"] as $e) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $p || ! str_contains((string) $p->description, "SCI Huntingdon")) { $bad[] = $e["slug"]." append missing"; }
}

foreach ($payload["institutions"] as $e) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $p) { $bad[] = $e["slug"]." missing"; continue; }

    foreach ($p->cases()->with("institution")->get() as $case) {
        if ($case->institution && $case->institution->name === $e["wrong"]) { $bad[] = $e["slug"]." still at ".$e["wrong"]; }
    }
}

echo "    problems: ", count($bad), "\n";

foreach ($bad as $b) { echo "      !! ", $b, "\n"; }

// Group rosters, so the tags can be read as rosters rather than taken on faith.
echo "\n  the groups now read:\n";

foreach ($payload["tags"] as $g) {
    $members = Prisoner::withoutGlobalScopes()
        ->get(["name", "slug", "affiliation"])
        ->filter(fn ($p) => in_array($g["tag"], (array) ($p->affiliation ?? []), true))
        ->sortBy("name");

    echo "    ", str_pad($g["tag"], 18), " ", $members->count(), "  ",
        $members->pluck("name")->implode(", "), "\n";
}

echo "\n  ", wordwrap($payload["not_tagged"], 72, "\n  "), "\n";

if (count($bad) === 0 && $missing === 0) { echo "\nB218-OK\n"; }
'

run_tinker "tags-and-fixes" "B218-OK" "$TAG_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 218 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
