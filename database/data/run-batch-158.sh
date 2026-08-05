#!/usr/bin/env bash
#
# BATCH 158 -- A. J. Muste: four missing detentions, and the taxonomy.
#
#   His record held ONE case row — Lawrence 1919, with an arrest date
#   of just "1919" and no custody dates at all, so the profile
#   published nothing. The curator supplies five episodes:
#
#     Feb 1919        Lawrence textile strike, about a week
#     ~2 Sep 1931     Paterson silk strike, length unknown
#     15 Jun 1955     Operation Alert, New York, one or two days
#     1-9 Jul 1959    Mead missile base, Nebraska, EIGHT NIGHTS
#     21 Apr 1966     Saigon, brief detention then deportation
#
#   HE DID NOT SERVE SIX MONTHS. The Mead judge imposed six months and
#   a $500 fine and then suspended both. Later summaries confuse that
#   suspended sentence with the imprisonment of other Omaha Action
#   defendants, so the record says so in as many words.
#
#   THE COUNTER WILL READ EIGHT DAYS, for a man detained on five
#   occasions across forty-seven years. Only Mead has both endpoints
#   documented. Lawrence is about a week with no dates; Paterson has a
#   start and no end; New York is one or two days with the sources
#   disagreeing, and the difference is the whole length of the
#   detention, so picking one would invent the figure. Those are
#   narrated in the case rows instead of counted, and the payload
#   flags the schema gap that forces the choice.
#
#   ALSO ADDED: his full name as an aka, four ideologies and five
#   affiliations, and a biography covering all five episodes. Pacifism
#   is NOT added as an ideology — no record in the archive carries it,
#   while Anti-War carries 1,709 and Anti-Militarism 857, which is how
#   this vocabulary has been saying the same thing. See the flags.
#
#   Idempotent: new rows are matched on their charges before creation,
#   so a re-run updates rather than duplicating.
#
# Run from the repo root, after git pull (after batch 157):
#   bash database/data/run-batch-158.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 158 — A. J. Muste: five detentions, not one"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch158.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$d = fn ($v) => $v ? $v->format("Y-m-d") : "----------";

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases")->first();

if (! $p) { echo "  ", $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

echo "  ", $p->name, "  [", $p->slug, "]\n";
echo "  before: ", $p->cases->count(), " case row(s), ",
    (int) $p->cases->sum("imprisoned_for_days"), " days\n";

foreach ($p->cases as $c) {
    echo "    arrest=", $d($c->arrest_date), " in=", $d($c->incarceration_date),
        " out=", $d($c->release_date), "\n";
}

// ---------------------------------------------------------------- prisoner
$pf = $payload["prisoner"];

foreach (["aka", "ideologies", "affiliation", "description"] as $f) {
    if (array_key_exists($f, $pf)) { $p->{$f} = $pf[$f]; }
}

$p->save();
$p->refresh();

echo "\n  aka:          ", ($p->aka ?: "-"), "\n";
echo "  slug now:     ", $p->slug, " (name untouched, so this cannot have moved)\n";
echo "  ideologies:   ", implode(", ", $p->ideologies ?: []), "\n";
echo "  affiliations: ", implode(", ", $p->affiliation ?: []), "\n";

// ------------------------------------------------------------------- cases
foreach ($payload["cases"] as $spec) {
    if ($spec["role"] === "existing") {
        $case = $p->cases->first(fn ($c) => mb_strpos((string) $c->charges, $spec["match_charges"]) !== false)
            ?: ($p->cases->count() === 1 ? $p->cases->first() : null);

        if (! $case) { echo "\n  [", $spec["label"], "] existing row not matched — skipped\n"; continue; }

        echo "\n  [", $spec["label"], "] on the existing row\n";
    } else {
        $case = $p->cases->first(fn ($c) => (string) $c->charges === (string) $spec["charges"]);

        if ($case) { echo "\n  [", $spec["label"], "] already present — updated\n"; }
        else {
            $case = new PrisonerCase(["prisoner_id" => $p->id]);
            echo "\n  [", $spec["label"], "] created\n";
        }
    }

    foreach (["charges", "convicted", "sentence"] as $f) {
        if (array_key_exists($f, $spec)) { $case->{$f} = $spec[$f]; }
    }

    foreach (($spec["dates"] ?? []) as $field => $parts) {
        if ($parts === null) { $case->setPartialDate($field, null); continue; }

        $case->setPartialDate($field, $parts[0], $parts[1] ?? null, $parts[2] ?? null);
    }

    $case->prisoner_id = $p->id;
    $case->save();
    $case->refresh();

    foreach (["arrest_date", "incarceration_date", "sentenced_date", "release_date"] as $f) {
        if ($case->{$f}) {
            echo "      ", str_pad($f, 20), $case->formatPartialDate($f),
                "  [", $case->datePrecisionFor($f), "]\n";
        }
    }

    echo "      days = ", ($case->imprisoned_for_days ?? "null"), "\n";
}

// ----------------------------------------------------------------- summary
$p->refresh()->load("cases");

$total = (int) $p->cases->sum("imprisoned_for_days");
$start = $p->cases->map(fn ($c) => $c->incarceration_date ?: $c->arrest_date)->filter()->sort()->first();

echo "\n  after: ", $p->cases->count(), " case row(s), ", $total, " days\n";
echo "  counter: ", ($total > 0
    ? \App\Support\ImprisonmentDuration::phrase($start, $total,
        \App\Support\ImprisonmentDuration::documentedMonths($p->cases))
    : "(none)"), "\n";
echo "  ", wordwrap("Eight days is the Mead term alone. It is the only one of the five with both "
    ."endpoints documented, so the counter understates a life of repeated short detentions. That is "
    ."deliberate: the alternative is inventing the other four.", 84, "\n  "), "\n";

echo "\n", str_repeat("=", 67), "\nFLAGGED FOR THE CURATOR, NOT ACTED ON\n";

foreach ($payload["flagged"] as $f) {
    echo "\n  ", $f["name"], "\n  ", wordwrap($f["reason"], 84, "\n  "), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "aj-muste-detentions" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 158 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "Expected: 5 case rows, 8 days total, from Mead alone."
