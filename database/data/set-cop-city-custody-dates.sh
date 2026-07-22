#!/usr/bin/env bash
#
# Fill in the actual incarceration and release dates for Cop City / Defend the
# Atlanta Forest defendants whose custody dates were supplied by the site
# owner (the December 2022, January/March/April 2023 forest arrests, the
# Atlanta Solidarity Fund three, the Weelaunee Three, Mazurek and Kloth).
# Data in database/data/cop-city-custody-dates.json.
#
# For each person the loader updates their existing (undated) case with the
# incarceration/release dates at their true precision, the day count where a
# finite span or explicit count is known, and a note. Abeeku Vassail's April
# 2023 flyer arrest is a SEPARATE event from his September 2023 RICO case, so
# it is added as a new case (new_case). Most of these bookings were at the
# DeKalb County Jail.
#
# NOTE: run this AFTER add-cop-city-rico-missing.sh (which creates the Vassail
# record). Idempotent: dates are set to the same values on re-run, and a note
# is appended only if not already present.
#   bash database/data/set-cop-city-custody-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$rows = json_decode(file_get_contents(base_path("database/data/cop-city-custody-dates.json")), true);
if (! is_array($rows)) { echo "Could not read JSON\n"; return; }

$setDate = function ($case, $field, $val, $prec) {
    $parts = explode("-", $val);
    $y = (int) ($parts[0] ?? 0); $m = (int) ($parts[1] ?? 1); $d = (int) ($parts[2] ?? 1);
    if ($prec === "month") { $case->setPartialDate($field, $y, $m); }
    else { $case->setPartialDate($field, $y, $m, $d); }
};

$updated = 0; $missing = 0;
foreach ($rows as $r) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $r["slug"])->first();
    if (! $p) { echo "  not found: {$r["slug"]}\n"; $missing++; continue; }

    if (! empty($r["new_case"])) {
        $c = new \App\Models\PrisonerCase();
        $c->prisoner_id = $p->id;
    } else {
        $c = $p->cases()->first();
        if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; }
    }

    if (empty($c->institution_id) && empty($r["no_inst"])) {
        $instName = $r["institution"] ?? "DeKalb County Jail";
        $city = $instName === "DeKalb County Jail" ? "Decatur" : null;
        $inst = \App\Models\Institution::firstOrCreate(["name" => $instName], array_filter(["city" => $city, "state" => "Georgia"]));
        $c->institution_id = $inst->id;
    }

    $setDate($c, "incarceration_date", $r["inc"], $r["inc_prec"] ?? "day");
    $setDate($c, "release_date", $r["rel"], $r["rel_prec"] ?? "day");
    if (isset($r["days"])) { $c->imprisoned_for_days = (int) $r["days"]; }

    $note = $r["note"] ?? "";
    $s = (string) $c->sentence;
    if ($note !== "" && mb_stripos($s, $note) === false) {
        $c->sentence = trim($s === "" ? $note : $s . " " . $note);
    }
    $c->save();

    if (! empty($r["aka"])) {
        $akas = array_filter(array_map("trim", preg_split("#\s*/\s*#", (string) $p->aka)));
        if (! in_array($r["aka"], $akas, true) && mb_strtolower($r["aka"]) !== mb_strtolower((string) $p->name)) {
            $akas[] = $r["aka"];
            $p->aka = implode(" / ", $akas);
            $p->save();
        }
    }

    echo "  updated {$p->slug} ({$r["inc"]} -> {$r["rel"]})\n";
    $updated++;
}

echo "Updated {$updated}; not found {$missing}.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Cop City custody dates set."
