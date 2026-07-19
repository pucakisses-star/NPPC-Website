#!/usr/bin/env bash
#
# Audit of two source documents (July 2026):
#
#  1. JBAKC "Stop the Grand Jury!" (Nov 1984, Freedom Archives scan) —
#     identical to the issue already audited in add-jbakc-grand-jury-audit.sh;
#     no further gaps.
#  2. The Militant, April 6, 1984 (vol. 48 no. 12) — its custody stories
#     (Kathy Boudin pretrial isolation, the five sentenced FALN grand-jury
#     resisters, Pam Fadem's criminal-contempt charge, Héctor Marroquín's
#     deportation fight) all concern people already in the database, except
#     one: the back-page interview subject Alberto de Jesús Berríos, added
#     here. (The New Bedford trial and the British miners' strike arrests
#     are out of the site's scope; James David Autry's execution was not a
#     political case.)
#
# Idempotent: prisoner:add refuses duplicates (|| true).
#
# Run from the repo root:  bash database/data/add-militant-apr1984.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Alberto de Jesús Berríos","first_name":"Alberto","last_name":"de Jesús Berríos","description":"Alberto de Jesús Berríos was a Puerto Rican independence activist — for years a distributor of the Puerto Rican Socialist Party newspaper and an activist in the National Support Committee for Vieques — who became the first name on the FBI list of 25 people and groups drawn up after the December 3, 1979 attack on a U.S. Navy bus at Sabana Seca. Subjected to constant FBI surveillance and harassment though never charged in the attack, he left the island in 1980 and settled in Green Bay, Wisconsin, working as a cabinet maker. On January 17, 1983 he was arrested as a supposed fugitive and brought before the federal grand jury investigating Sabana Seca; when he refused to testify he was jailed nine months for civil contempt, first in New York City and then in Miami. Two days before his contempt term was up, the government charged him with misuse of a social security number — from the false identity he had used to escape the harassment — and set bail at two million dollars, later reduced. He was convicted in January 1984 and was awaiting sentencing when The Militant interviewed him that spring; a De Jesús Support Association organized in Wisconsin around his defense. Reconstructed from The Militant, April 6, 1984; the sentencing outcome has not been located.","state":"Wisconsin","gender":"Male","ideologies":["Puerto Rican independence"],"era":"1980s","released":true,"cases":[{"charges":"Civil contempt — refusing to testify before the federal grand jury investigating the December 1979 Sabana Seca attack, in which he was accused of no crime","arrest_date":"1983-01-17","incarceration_date":"1983-01-17","convicted":"No — jailed for civil contempt, never charged in the attack","sentence":"Jailed nine months for the life of the grand jury, first in New York City and then in Miami","imprisoned_for_days":270},{"charges":"Misuse of a social security number — from the identity he used to escape FBI harassment; brought two days before his contempt release","convicted":"Yes — convicted January 1984; bail had been set at $2 million and later reduced","sentence":"Awaiting sentencing as of April 1984; outcome not located"}]}' || true

php artisan tinker --execute='
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Militant April 1984 addition applied."
