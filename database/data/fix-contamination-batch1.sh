#!/usr/bin/env bash
#
# First cleanup pass for the record-contamination found by
# audit-record-contamination.sh. Only the UNAMBIGUOUS problems are fixed here;
# the ambiguous state-mismatch-at-a-real-prison cases are left for review.
#
#   1) Martin Luther King Jr.: his 1967 contempt case was linked to the
#      COLORADO "Jefferson County Jail" (a firstOrCreate name collision with the
#      Birmingham jail). Repointed to "Jefferson County Jail (Birmingham)", AL.
#   2) Deceased-but-in-custody: clear the in_custody flag (a dead person is not
#      currently imprisoned; this is what inflated ages/time to the present).
#   3) Brandt/Trinidad/191131 fingerprint in a prisoner address or inmate number:
#      cleared (address, inmate_number, lat, lng).
#   4) Bogus institution links — cases at "Trinidad Correctional Facility",
#      "Guantanamo Bay Detention Camp", or "Charlestown State Prison" whose
#      prisoner is NOT in that institution's state (e.g. Mumia Abu-Jamal shown at
#      a Colorado youth prison). The institution link is cleared; the case, its
#      charges and dates are kept. Genuinely same-state prisoners (Eric Brandt,
#      Dylan Robinson, Grant Barnes at Trinidad) are left alone.
#
# Idempotent. Prints every change. Run from the repo root:
#   bash database/data/fix-contamination-batch1.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$P = \App\Models\Prisoner::class;

// 1) MLK contempt case -> Alabama Jefferson County Jail.
$mlk = $P::withoutGlobalScopes()->where("slug","martin-luther-king-jr")->first();
if ($mlk) {
    $al = \App\Models\Institution::firstOrCreate(["name"=>"Jefferson County Jail (Birmingham)"], ["city"=>"Birmingham","state"=>"Alabama"]);
    foreach ($mlk->cases()->get() as $c) {
        if (stripos((string) $c->charges, "Walker v. City of Birmingham") !== false && (int) $c->institution_id !== (int) $al->id) {
            $c->institution_id = $al->id; $c->save();
            echo "1) MLK contempt case repointed to Jefferson County Jail (Birmingham), Alabama.\n";
        }
    }
}

// 2) Deceased but flagged in custody.
$dead = $P::withoutGlobalScopes()->whereNotNull("death_date")->where("in_custody", true)->get();
foreach ($dead as $p) {
    $p->in_custody = false; $p->released = true; $p->save();
    echo "2) Cleared in_custody on deceased: {$p->slug} ({$p->name}, died {$p->death_date}).\n";
}
echo "2) deceased-but-in-custody fixed: ".$dead->count()."\n";

// 3) Address / inmate fingerprint.
$fp = $P::withoutGlobalScopes()->where(function ($q) {
    foreach (["Trinidad","191131","Brandt","US-350","US 350","Model, CO","Model,CO"] as $t) { $q->orWhere("address","like","%{$t}%"); }
    $q->orWhere("inmate_number","like","%191131%");
})->get();
foreach ($fp as $p) {
    $p->address = null; $p->inmate_number = null; $p->lat = null; $p->lng = null; $p->save();
    echo "3) Cleared contaminated address/inmate/coords: {$p->slug} ({$p->name}).\n";
}
echo "3) address/inmate fingerprints cleared: ".$fp->count()."\n";

// 4) Bogus institution links (state mismatch at a clearly-wrong facility).
$bogus = ["Trinidad Correctional Facility","Guantanamo Bay Detention Camp","Charlestown State Prison"];
$cleared = 0;
foreach (\App\Models\Institution::whereIn("name", $bogus)->get() as $inst) {
    $istate = trim((string) $inst->state);
    foreach (\App\Models\PrisonerCase::where("institution_id", $inst->id)->get() as $c) {
        $p = $P::withoutGlobalScopes()->find($c->prisoner_id);
        if ($p && strcasecmp(trim((string) $p->state), $istate) === 0) { continue; } // same state -> legitimate
        $c->institution_id = null; $c->save();
        echo "4) Cleared \"{$inst->name}\" from case of ".($p ? $p->name : "?")." (state ".var_export($p?->state,true).").\n";
        $cleared++;
    }
}
echo "4) bogus institution links cleared: {$cleared}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Contamination cleanup batch 1 applied."
