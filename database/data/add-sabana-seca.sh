#!/usr/bin/env bash
#
# Additions from the 1979 Sabana Seca ambush (December 3, 1979 Machetero /
# FARP / OVRP attack on a U.S. Navy bus that killed two sailors and wounded
# ten). Already in the database: Juan Segarra-Palmer and Filiberto Ojeda
# Ríos (via their Wells Fargo / Machetero cases). The victims are out of
# scope, and the two suspects reportedly assassinated by right-wing
# paramilitaries in 1980 are unnamed in available sources.
#
#  1. Juan Galloza Acevedo — the only person ever convicted for the attack
#     (2014 guilty plea, five years, thirty-five years after the fact).
#     The record states the violence plainly and the political framing
#     honestly, per the site convention for violent cases.
#  2. Ángel Rodríguez Cristóbal — the Vieques civil-disobedience prisoner
#     whose death in FCI Tallahassee weeks earlier was the attack's stated
#     motive. The database holds 219 Vieques-related records but was
#     missing its most famous prisoner.
#
# Idempotent: prisoner:add refuses duplicates (|| true).
#
# Run from the repo root:  bash database/data/add-sabana-seca.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Ángel Rodríguez Cristóbal","first_name":"Ángel","last_name":"Rodríguez Cristóbal","description":"Ángel Rodríguez Cristóbal was a Puerto Rican independence activist from Ciales and a leader of the Liga Socialista Puertorriqueña who joined the fishermen-led civil disobedience against the U.S. Navy'"'"'s use of Vieques as a bombing range. Arrested on May 19, 1979 with twenty other demonstrators for trespassing on Navy-restricted land, he refused to recognize the U.S. court'"'"'s authority over Puerto Rico and was sentenced to six months in federal prison. On November 11, 1979 he was found hanged in his cell at the Federal Correctional Institution in Tallahassee, Florida. Authorities ruled the death a suicide; his supporters pointed to reports of bruising on his body and have regarded his death as a murder in custody ever since. His funeral drew thousands, and his death became a rallying point of the Vieques and independence movements — three weeks later the Sabana Seca ambush was carried out in his name.","state":"Puerto Rico","gender":"Male","death_date":"1979-11-11","ideologies":["Puerto Rican independence","Socialism"],"affiliation":["Liga Socialista Puertorriqueña"],"era":"1970s","released":false,"cases":[{"institution_name":"FCI Tallahassee","institution_city":"Tallahassee","institution_state":"Florida","charges":"Trespassing on U.S. Navy restricted lands during the May 19, 1979 Vieques civil-disobedience action","arrest_date":"1979-05-19","convicted":"Yes — he refused to recognize the court'"'"'s jurisdiction","sentence":"Six months in federal prison; died in custody on November 11, 1979, officially ruled a suicide and widely disputed"}]}' || true

php artisan prisoner:add '{"name":"Juan Galloza Acevedo","first_name":"Juan","last_name":"Galloza Acevedo","description":"Juan Galloza Acevedo is the only person ever convicted for the December 3, 1979 Sabana Seca ambush, in which Machetero, FARP and OVRP militants opened fire on a U.S. Navy bus near the Sabana Seca communications station, killing petty officers John Ball and Emil White and wounding ten other sailors — an attack claimed as retaliation for the death of Ángel Rodríguez Cristóbal in federal custody weeks earlier. A Machetero sympathizer from about 1969 who became active around 1978, Galloza rode in the front passenger seat of the van from which the gunmen fired; he left the group three weeks after the attack over its tactics and spent decades working in a purse factory. DNA evidence recovered after the case was reopened in 2001 led investigators to him; he admitted his role to naval investigators in July 2013 and cooperated. In May 2014, at age 78 — thirty-five years after the attack — he pleaded guilty in Brooklyn federal court to racketeering conspiracy, murder and robbery conspiracy and was sentenced to five years in prison, the judge crediting his minor role, remorse and cooperation. The killing of the two sailors is stated plainly here; the attack was politically framed by its authors, and his case is included as a politically motivated prosecution outcome of that event.","state":"Puerto Rico","gender":"Male","ideologies":["Puerto Rican independence"],"affiliation":["Los Macheteros"],"era":"2010s","released":true,"cases":[{"charges":"Racketeering conspiracy, murder, and robbery conspiracy — guilty plea for his role in the December 3, 1979 Sabana Seca ambush that killed two U.S. Navy sailors","convicted":"Yes — guilty plea (2013-14), after admitting his role to naval investigators and cooperating","sentence":"Five years in federal prison, imposed May 2014 in Brooklyn federal court, thirty-five years after the attack","imprisoned_for_days":1825}]}' || true

php artisan tinker --execute='
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Sabana Seca additions applied."
