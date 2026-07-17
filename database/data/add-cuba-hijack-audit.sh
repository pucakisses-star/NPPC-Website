#!/usr/bin/env bash
#
# Cuba-hijacking spreadsheet audit (July 2026). Reviewed all 92 incidents in
# the 1968-1972 U.S.-to-Cuba hijacking reconstruction for politically
# motivated actors. Already in the database: Raymond Johnson, William Lee
# Brent, Charette & Allard, and the TWA 106 trio (Finney, Goodwin, Hill).
#
# This script:
#  1. Merges the Lorenzo Kom'boa Ervin duplicate pair surfaced by the audit
#     (the canonical has his photo and case; the dup is a caseless roster
#     stub whose description folds in).
#  2. Adds Anthony Bryant, the one politically identified hijacker missing:
#     a Black Panther who hijacked National Airlines Flight 97 to Havana on
#     March 5, 1969 and then spent about eleven years in Cuban prisons.
#
# Idempotent throughout.
#
# Run from the repo root:  bash database/data/add-cuba-hijack-audit.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoners:merge-duplicates --only=lorenzo-komboa-ervin --apply

php artisan prisoner:add '{"name":"Anthony Bryant","first_name":"Anthony","last_name":"Bryant","aka":"Anthony Garnet Bryant","description":"Anthony Garnet Bryant was a Black Panther Party member from California who hijacked National Airlines Flight 97 from New York to Havana on March 5, 1969, expecting revolutionary asylum. Instead the Cuban government imprisoned him, and he spent roughly the next eleven years in Castro'"'"'s prisons before being allowed to return to the United States in 1980. He faced federal air-piracy charges on his return and was treated leniently in light of his years in Cuban custody; he became an outspoken anti-Castro activist and told his story in the memoir Hijack! He died in 1999.","state":"California","race":"Black","gender":"Male","death_date":"1999-01-01","ideologies":["Black liberation"],"affiliation":["Black Panther Party"],"era":"1960s","released":true,"cases":[{"charges":"Air piracy — the March 5, 1969 hijacking of National Airlines Flight 97 to Havana; imprisoned about eleven years in Cuba on arrival","arrest_date":"1969-03-05","convicted":"Imprisoned in Cuba (~11 years); faced U.S. charges on his 1980 return and was sentenced leniently in light of that custody","imprisoned_for_days":4000}]}' || true

php artisan tinker --execute='
// Anthony Bryant died in 1999 but no exact date is well documented — store
// the year with year precision so the site displays just "1999".
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "anthony-bryant")->first();
if ($p && $p->death_date && $p->formatPartialDate("death_date") !== "1999") {
    $p->date_precision = array_merge($p->date_precision ?? [], ["death_date" => "year"]);
    $p->save();
    echo "SET anthony-bryant death precision = year\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
'

echo
echo "Done. Cuba-hijacking audit changes applied."
