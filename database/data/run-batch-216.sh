#!/usr/bin/env bash
#
# BATCH 216 -- what the Black Panther handout has that this archive did not.
#
#   THE SOURCE is the Black Panther Political Prisoners sheet at
#   itsabouttimebpp.com, a c.2012 advocacy handout listing 27 people. All
#   27 are already in this database. What it carries that the records did
#   not: fifteen of them had no sentence recorded at all, fourteen had no
#   arrest date, nineteen had no campaign website.
#
#   EVERY WRITE ONLY FILLS A BLANK. Each field is checked for emptiness
#   first, so nothing already recorded is overwritten. Where the archive
#   and the handout disagree, the archive wins by default and the
#   disagreement is left for a human to settle. That matters here: the
#   handout is wrong in at least three places. It dates Mumia at 7/3/1982,
#   which is his sentencing rather than his arrest; it dates Mutulu Shakur
#   at 1981, when he was a fugitive until February 1986; and it gives
#   Warren Wells 1984-2001 against a 1968 arrest on the record. All three
#   are already filled here, so all three are skipped automatically.
#
#   DEAD LINKS ARE NOT PUBLISHED. Ten campaign URLs appear in the handout.
#   Every one was requested before this batch was written and only the
#   five that still answer are included. mxgm.org/abdul-majid,
#   sekouodinga.com, freeeddieconway.org, freethesf8.org and
#   kersplebedeb.com/sethhayes are dead or erroring; they are left out
#   rather than added as broken links.
#
#   TWO DATES DELIBERATELY OMITTED. The handout puts David Rice at
#   8/27/1970 while his co-defendant Ed Poindexter, arrested with him,
#   already carries 1970-08-21; and it puts Kenneth Whitmore at 3/14/1977
#   against his own 3/14/1978 incarceration. Those contradictions are
#   worth more as open questions than as silent overwrites.
#
#   ONE DATE IS NOT FROM THE HANDOUT. Teddy Heath gets 1973-05-02 because
#   his own description on this site already says he was arrested on May
#   2, 1973. The handout gives only the year. The record is the better
#   source, so it is the one used.
#
#   PARTIAL DATES are stored the way batch 206 stored Audrey Hendricks:
#   the first of the period plus a precision flag, so a month-only capture
#   renders as the month and not as a day nobody claims.
#
#   Idempotent: every write is conditional on the field being empty.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-216.sh

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
echo "  Batch 216 — sentences, websites and arrest dates from the handout"
echo "==================================================================="

FILL_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch216.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$wrote = 0; $skipped = 0; $missing = 0;

$find = function (string $slug) use (&$missing) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

    if (! $p) { echo "  !! no prisoner at slug ", $slug, "\n"; $missing++; }

    return $p;
};

echo "\n  SENTENCES\n";

foreach ($payload["sentences"] as $e) {
    $p = $find($e["slug"]);

    if (! $p) { continue; }

    // Fill the first case that has no sentence; a person with two cases
    // should not get the same text written onto both.
    $case = $p->cases()->get()->first(fn ($c) => blank($c->sentence));

    if (! $case) {
        echo "    ", str_pad($p->name, 24), " already has a sentence — skipped\n";
        $skipped++;
        continue;
    }

    $case->sentence = $e["sentence"];
    $case->save();
    echo "    ", str_pad($p->name, 24), " ", $e["sentence"], "\n";
    $wrote++;
}

echo "\n  WEBSITES\n";

foreach ($payload["websites"] as $e) {
    $p = $find($e["slug"]);

    if (! $p) { continue; }

    if (filled($p->website)) {
        echo "    ", str_pad($p->name, 24), " already has ", $p->website, " — skipped\n";
        $skipped++;
        continue;
    }

    $p->website = $e["website"];
    $p->save();
    echo "    ", str_pad($p->name, 24), " ", $e["website"], "\n";
    $wrote++;
}

echo "\n  ARREST DATES\n";

foreach ($payload["arrest_dates"] as $e) {
    $p = $find($e["slug"]);

    if (! $p) { continue; }

    $case = $p->cases()->get()->first(fn ($c) => blank($c->arrest_date));

    if (! $case) {
        echo "    ", str_pad($p->name, 24), " already has an arrest date — skipped\n";
        $skipped++;
        continue;
    }

    $case->arrest_date = $e["date"];
    $case->date_precision = array_merge($case->date_precision ?? [], ["arrest_date" => $e["precision"]]);
    $case->save();
    $case->refresh();

    echo "    ", str_pad($p->name, 24), " ", $case->formatPartialDate("arrest_date"),
        "   (", $e["precision"], " precision)\n";
    $wrote++;
}

echo "\n  wrote ", $wrote, ", skipped ", $skipped, ", missing prisoners ", $missing, "\n";

// Read everything back rather than trusting the writes above.
echo "\n  the twenty-seven, as they now stand:\n";

$slugs = array_unique(array_merge(
    array_column($payload["sentences"], "slug"),
    array_column($payload["websites"], "slug"),
    array_column($payload["arrest_dates"], "slug")
));

sort($slugs);

$bad = 0;

foreach ($slugs as $slug) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

    if (! $p) { $bad++; continue; }

    $case = $p->cases()->get()->first();
    $sent = $case && filled($case->sentence) ? mb_substr($case->sentence, 0, 34) : "(none)";
    $arr = $case && filled($case->arrest_date) ? $case->formatPartialDate("arrest_date") : "(none)";

    echo "    ", str_pad($p->name, 24), " arrest ", str_pad($arr, 14),
        " sentence ", str_pad($sent, 36), " ", ($p->website ?: "-"), "\n";
}

echo "\n  ", wordwrap($payload["only_fills_blanks"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["dead_links_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["not_included"], 72, "\n  "), "\n";

if ($missing === 0 && $bad === 0) { echo "\nB216-OK\n"; }
'

run_tinker "fill-from-handout" "B216-OK" "$FILL_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 216 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
