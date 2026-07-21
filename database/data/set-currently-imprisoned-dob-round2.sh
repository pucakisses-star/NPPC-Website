#!/usr/bin/env bash
#
# Round 2 of filling in dates of birth for currently-imprisoned prisoners who
# were missing one. This covers people the first pass could not resolve, found
# on a deeper search (court dockets, OFAC SDN entries for extradited foreign
# nationals, name-based DOC locators, and Spanish-language coverage). The
# roster lives in database/data/currently-imprisoned-dob-round2.json.
#
# Several extradited Colombian / FARC figures now have full dates of birth
# from OFAC records (Cuevas Cabrera, Aguilar Ramirez, Martinez Vega, Leal
# Garcia, "El Loco" Barrera); the rest are birth years derived from a
# documented age and stored at YEAR precision so the site shows only the year.
# Low-confidence / unconfirmed matches were left out.
#
# NOTE: run this AFTER prisoners:merge-duplicates has folded sofia-johnson
# into sofia-deferrari, so the DOB lands on the surviving record.
#
# Idempotent and non-destructive: a birthdate is written ONLY where the field
# is still empty. Run from the repo root:
#   bash database/data/set-currently-imprisoned-dob-round2.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$path = base_path("database/data/currently-imprisoned-dob-round2.json");
$rows = json_decode(file_get_contents($path), true);
if (! is_array($rows)) { echo "Could not read DOB JSON\n"; return; }

$set = 0; $skipHasDob = 0; $missing = 0;

foreach ($rows as $r) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $r["slug"])->first();
    if (! $p) { echo "  not found: {$r["slug"]}\n"; $missing++; continue; }
    if (! empty($p->birthdate)) { $skipHasDob++; continue; }

    $parts = explode("-", $r["dob"]);
    $y = (int) ($parts[0] ?? 0);
    $m = (int) ($parts[1] ?? 1);
    $d = (int) ($parts[2] ?? 1);
    $prec = $r["precision"] ?? "day";

    if ($prec === "year")  { $p->setPartialDate("birthdate", $y); }
    elseif ($prec === "month") { $p->setPartialDate("birthdate", $y, $m); }
    else { $p->setPartialDate("birthdate", $y, $m, $d); }

    $p->save();
    echo "  set {$r["slug"]} = {$r["dob"]} ({$prec})\n";
    $set++;
}

echo "Set {$set} birthdate(s); skipped {$skipHasDob} that already had one; {$missing} slug(s) not found.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Round-2 dates of birth filled in."
