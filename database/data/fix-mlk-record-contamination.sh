#!/usr/bin/env bash
#
# Repair Martin Luther King Jr.'s prisoner record, which has been contaminated
# with another prisoner's data (Eric Brandt, inmate 191131, at the Trinidad
# Correctional Facility in Model, Colorado). Symptoms on the public page:
#   - AGE 97 and "IMPRISONED FOR 58 YEARS ..." (King has no death date, is
#     flagged in custody, and carries an open-ended Colorado case that counts
#     to today);
#   - a bogus "Civil Disorder / Trinidad Correctional Facility / Colorado" case;
#   - a mailing/physical address reading "Eric Brandt, 191131 Trinidad
#     Correctional Facility ... Model, CO 81059".
#
# This script:
#   1) sets King's death date (April 4, 1968) and clears the in-custody flag;
#   2) clears the contaminated address / inmate-number / map coordinates;
#   3) removes the bogus Colorado / Trinidad / "Civil Disorder" case — reassigning
#      it to an "Eric Brandt" prisoner if one exists, otherwise deleting it.
#
# It prints King's cases and flags BEFORE and AFTER so the change can be
# verified. It never touches his two real Alabama cases (anti-boycott statute;
# Walker v. City of Birmingham criminal contempt). Idempotent.
#
# Run from the repo root:
#   bash database/data/fix-mlk-record-contamination.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$mlk = \App\Models\Prisoner::withoutGlobalScopes()->where("slug","martin-luther-king-jr")->first();
if (! $mlk) { $mlk = \App\Models\Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) IN (?,?)", ["martin luther king jr.","martin luther king"])->first(); }
if (! $mlk) { echo "Martin Luther King Jr. not found.\n"; return; }

$dump = function ($mlk, $label) {
    echo "== {$label} ==\n";
    echo "  death_date={$mlk->death_date} in_custody=".var_export($mlk->in_custody,true)." released=".var_export($mlk->released,true)."\n";
    echo "  inmate_number=".var_export($mlk->inmate_number,true)." address=".var_export($mlk->address,true)." lat=".var_export($mlk->lat,true)." lng=".var_export($mlk->lng,true)."\n";
    foreach ($mlk->cases()->get() as $c) {
        $inst = $c->institution;
        echo "  case {$c->id}: charges=".var_export($c->charges,true)." | inst=".var_export($inst?->name,true)." state=".var_export($inst?->state,true)."\n";
    }
};

$dump($mlk, "BEFORE");

// 1) He died April 4, 1968 (assassinated) — not in custody.
if (empty($mlk->death_date)) { $mlk->death_date = "1968-04-04"; }
$mlk->in_custody = false;
$mlk->released = true;

// 2) Clear the contaminated address / inmate number / map coordinates.
$contam = function ($v) { return $v !== null && $v !== "" && preg_match("/brandt|trinidad|191131|model,?\\s*co|us-?350/i", (string) $v); };
if ($contam($mlk->address) || $contam($mlk->inmate_number)) {
    $mlk->address = null;
    $mlk->inmate_number = null;
    $mlk->lat = null;
    $mlk->lng = null;
    echo "Cleared contaminated address / inmate_number / lat / lng.\n";
}
$mlk->save();

// 3) Remove the bogus Colorado / Trinidad / Civil Disorder case.
$brandt = \App\Models\Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", ["eric brandt"])->first();
foreach ($mlk->cases()->get() as $c) {
    $inst = $c->institution;
    $bogus = (stripos((string) $c->charges, "civil disorder") !== false)
        || ($inst && (stripos((string) $inst->name, "trinidad") !== false || strcasecmp((string) $inst->state, "Colorado") === 0));
    if (! $bogus) { continue; }
    if ($brandt && $brandt->id !== $mlk->id) {
        $c->prisoner_id = $brandt->id; $c->save();
        echo "Reassigned bogus case {$c->id} to existing prisoner Eric Brandt ({$brandt->slug}).\n";
    } else {
        $c->delete();
        echo "Deleted bogus case {$c->id} (no Eric Brandt record to hold it).\n";
    }
}

$mlk->refresh();
$dump($mlk, "AFTER");

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Martin Luther King Jr. record repaired."
