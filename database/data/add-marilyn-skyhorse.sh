#!/usr/bin/env bash
#
# Completes the mining of the NASC "Free Paul Skyhorse and Richard
# Mohawk" pamphlet (already in the site archive via
# archive:add-skyhorse-mohawk-pamphlet, with the two defendants' portraits
# attached). One further person in it meets the custody standard:
#
#  Marilyn Skyhorse — Paul Skyhorse's wife, "continually harassed,
#  briefly jailed and threatened with the possibility of the State
#  taking custody of her children" for refusing to testify against her
#  husband, after California refused to recognize their marriage under
#  Indian law and with it her spousal privilege.
#
# Not added, with reasons: Marvin Red Shirt, Holly Broussard and Marcella
# Eaglestaff (the immunized original suspects — arrested at the scene but
# freed as prosecution witnesses; the movement account treats them as the
# actual perpetrators, not political prisoners); Ken Littlefish (defense
# coordinator, beaten but not jailed); Douglas Durham (FBI informant);
# Sarah Bad Heart Bull (already present).
#
# Idempotent: prisoner:add refuses duplicates (|| true).
#
# Run from the repo root:  bash database/data/add-marilyn-skyhorse.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Marilyn Skyhorse","first_name":"Marilyn","last_name":"Skyhorse","description":"Marilyn Skyhorse was the wife of Paul Skyhorse, the American Indian Movement activist held nearly four years awaiting trial in the Skyhorse-Mohawk case. Because she and Paul were married according to Indian law rather than California law, the State of California refused to recognize her spousal privilege not to testify against her husband. She refused anyway, standing with her husband and contesting any ruling against her marriage as an attack on Indian sovereignty — and for it she was, in the words of the Native American Solidarity Committee'"'"'s defense pamphlet (ca. 1977), \"continually harassed, briefly jailed and threatened with the possibility of the State taking custody of her children.\" Reconstructed from the pamphlet Free Paul Skyhorse and Richard Mohawk (Freedom Archives DOC44); the duration of her jailing has not been located.","state":"California","gender":"Female","ideologies":["Native American rights"],"era":"1970s","released":true,"cases":[{"charges":"Jailed for refusing to testify against her husband in the Skyhorse-Mohawk case, after California refused to recognize her Indian-law marriage and spousal privilege","convicted":"No — briefly jailed for her refusal; never charged with a crime"}]}' || true

php artisan tinker --execute='
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Marilyn Skyhorse added."
