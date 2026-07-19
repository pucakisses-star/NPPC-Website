#!/usr/bin/env bash
#
# Adds Ronald Bridgeforth — the ninth man charged in the San Francisco 8
# case (the 2007 prosecutions over the 1971 killing of SFPD Sgt. John
# Young covered in the supplied New York Post article). All eight SF8
# defendants and Gabriel Torres were already in the database; Bridgeforth,
# charged in absentia after four decades underground, was the only person
# connected to the case still missing.
#
# Idempotent: prisoner:add refuses duplicates.
#
# Run from the repo root:  bash database/data/add-ronald-bridgeforth.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Ronald Bridgeforth","first_name":"Ronald","last_name":"Bridgeforth","description":"Ronald Bridgeforth was a Black Panther Party member in San Francisco who spent more than forty years underground. In September 1968 he was arrested after a confrontation with police at a White Front discount store in San Francisco and pleaded guilty to assault with a deadly weapon; in 1969, rather than face sentencing, he fled and disappeared. Living under the name Cole Jordan, he earned bachelor'"'"'s and master'"'"'s degrees, raised a family, and worked for years as a community-college counselor in Michigan. In January 2007 he was charged in absentia as the ninth defendant in the San Francisco 8 case — the revived prosecution of former Panthers over the August 29, 1971 shotgun killing of SFPD Sergeant John Young at Ingleside station — while the eight others were arrested across the country. After the SF8 case largely collapsed, Bridgeforth surrendered voluntarily in San Francisco in November 2011, telling the press he had come back to account for his past and live openly. The murder charge was not pursued; he pleaded guilty to the 1968 assault charge and in March 2012, at age 67, was sentenced to a year in county jail. Reconstructed from coverage of the 2007 San Francisco 8 arrests and his 2011 surrender.","state":"California","race":"Black","gender":"Male","ideologies":["Black liberation"],"affiliation":["Black Panther Party"],"era":"2000s","in_custody":false,"released":true,"cases":[{"charges":"Assault with a deadly weapon — September 1968 confrontation with police at a White Front store in San Francisco; pleaded guilty, then fled before sentencing in 1969 and lived underground as Cole Jordan for 42 years","arrest_date":"1968-09-01","convicted":"Yes — plea; surrendered November 2011 and was sentenced in March 2012 to one year in county jail","sentence":"One year in county jail (March 2012)"},{"charges":"Charged in absentia in January 2007 as the ninth San Francisco 8 defendant in the 1971 killing of SFPD Sgt. John Young","convicted":"No — the charge was not pursued after his 2011 surrender; the SF8 prosecution had largely collapsed"}]}' || true

php artisan tinker --execute='
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Ronald Bridgeforth added."
