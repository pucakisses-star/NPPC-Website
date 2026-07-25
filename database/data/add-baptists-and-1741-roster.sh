#!/usr/bin/env bash
#
# Add two researched groups of missing prisoners from a JSON roster:
#   * Virginia Baptist ministers jailed for unlicensed preaching (1768-1774) --
#     only the men with a documented incarceration; undated names are omitted.
#   * Defendants EXECUTED in the 1741 New York conspiracy panic -- enslaved
#     people are named with their enslaver in parentheses to keep the many
#     shared single names (Caesar, Quack, Cato, Fortune) distinct.
#
# Data lives in database/data/rosters/baptists-and-1741-executed.json (kept in a
# file so the biographies can contain apostrophes safely). "executed" is recorded
# as a death in custody; "released" gets a release date when one is documented.
#
# Idempotent: a prisoner whose name already exists is skipped and reported. Run
# from the repo root:
#   bash database/data/add-baptists-and-1741-roster.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;

$path = base_path("database/data/rosters/baptists-and-1741-executed.json");
$rows = json_decode(file_get_contents($path), true);
if (! is_array($rows)) { echo "Could not read roster JSON.\n"; return; }

$parts = function (?string $iso): ?array {
    if (! $iso) { return null; }
    $len = strlen($iso);
    $y = (int) substr($iso, 0, 4);
    if ($len === 4) { return [$y, null, null]; }
    if ($len === 7) { return [$y, (int) substr($iso, 5, 2), null]; }
    return [$y, (int) substr($iso, 5, 2), (int) substr($iso, 8, 2)];
};

$created = 0; $skipped = 0; $collisions = [];
foreach ($rows as $r) {
    $exists = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [strtolower($r["name"])])->first();
    if ($exists) { $skipped++; $collisions[] = "  ".$r["name"]." (slug ".$exists->slug.")"; continue; }

    $fate = $r["fate"];
    $end = $r["end_iso"] ?? null;

    $p = new Prisoner();
    $p->name = $r["name"]; $p->first_name = $r["first_name"]; $p->last_name = ($r["last_name"] ?? "") ?: null;
    $p->description = $r["description"]; $p->race = $r["race"]; $p->gender = $r["gender"];
    $p->state = $r["state"]; $p->era = $r["era"];
    $p->in_custody = false; $p->awaiting_trial = false;
    if ($fate === "executed" || $fate === "died") { $p->death_date = $end; $p->released = false; }
    else { $p->released = ($fate === "released"); }
    $p->save();

    $inst = null;
    if (! empty($r["institution"]) && ! empty($r["institution"][0])) {
        $inst = Institution::firstOrCreate(["name" => $r["institution"][0]], ["city" => $r["institution"][1] ?? null, "state" => $r["institution"][2] ?? null]);
    }

    $c = new PrisonerCase();
    $c->prisoner_id = $p->id;
    if ($inst) { $c->institution_id = $inst->id; }
    $c->charges = $r["charges"] ?? null;
    $c->sentence = $r["sentence"] ?? null;
    if ($ip = $parts($r["incarceration_iso"] ?? null)) { $c->setPartialDate("incarceration_date", $ip[0], $ip[1], $ip[2]); }
    if ($fate === "executed" || $fate === "died") {
        if ($ep = $parts($end)) { $c->setPartialDate("death_in_custody_date", $ep[0], $ep[1], $ep[2]); }
    } elseif ($ep = $parts($end)) {
        $c->setPartialDate("release_date", $ep[0], $ep[1], $ep[2]);
    }
    $c->save();

    $created++;
    $len = $c->imprisoned_for_days;
    echo "created ".$p->slug." | ".$p->name." | ".($c->partialDateIso("incarceration_date") ?? "?")." -> ".($c->partialDateIso("release_date") ?? "?")." (".($len !== null ? $len." d" : "n/a").") [".$fate."]\n";
}

echo "\n=== Summary ===\n";
echo "Created: {$created}\n";
echo "Skipped (name already exists -- verify not a different person): {$skipped}\n";
if ($collisions) { echo implode("\n", $collisions)."\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done. Baptist ministers and 1741 executed roster added."
