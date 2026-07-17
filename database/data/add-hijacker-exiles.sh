#!/usr/bin/env bash
#
# Political hijacker-exiles audit (July 2026). Of the documented U.S.
# political-asylum hijackers, four were already in the database (William
# Lee Brent, Charlie Hill, Ralph Goodwin, Michael Finney — precedents that
# establish the convention for asylum-exile records, alongside Catherine
# Kerkow and George Wright). This adds the three missing:
#
#   Raymond Johnson (Black Panther, National 186, 1968), and FLQ members
#   Jean-Pierre Charette and Alain Allard (National 91, 1969 — hijacked a
#   U.S. airliner from U.S. soil and were wanted on U.S. federal
#   air-piracy charges, giving their cases the U.S. nexus; they returned
#   to Canada in 1979 and served time there).
#
# Idempotent: prisoner:add refuses duplicates (|| true keeps going).
#
# Run from the repo root:  bash database/data/add-hijacker-exiles.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Raymond Johnson","first_name":"Raymond","last_name":"Johnson","description":"Raymond Johnson was a young Black Panther and Black nationalist from Louisiana who hijacked National Airlines Flight 186 to Havana on November 4, 1968, seeking political asylum from what he described as racist persecution in the United States. He remained in Cuban exile until 1986, one of the earliest of the era'"'"'s Black-liberation asylum hijackers.","state":"Louisiana","race":"Black","gender":"Male","ideologies":["Black liberation"],"affiliation":["Black Panther Party"],"era":"1960s","in_exile":true,"released":false,"cases":[{"charges":"Air piracy — the November 4, 1968 hijacking of National Airlines Flight 186 to Havana","convicted":"No — granted political asylum in Cuba","in_exile_since":"1968-11-04"}]}' || true

php artisan prisoner:add '{"name":"Jean-Pierre Charette","first_name":"Jean-Pierre","last_name":"Charette","description":"Jean-Pierre Charette was a Front de libération du Québec (FLQ) separatist who, with fellow FLQ member Alain Allard, hijacked National Airlines Flight 91 from New York to Havana on May 5, 1969, fleeing the Canadian police crackdown on the FLQ bombing campaign — the act put them on U.S. federal air-piracy wanted lists. After about ten years of exile in Cuba (and a period in France), the pair returned to Canada in 1979, pleaded guilty to FLQ-related charges, and served roughly two years in prison.","gender":"Male","ideologies":["Quebec separatism"],"affiliation":["Front de libération du Québec (FLQ)"],"era":"1960s","in_exile":true,"released":true,"cases":[{"charges":"U.S. federal air-piracy charges — the May 5, 1969 hijacking of National Airlines Flight 91 from New York to Havana","convicted":"Not tried in the United States — returned to Canada in 1979, pleaded guilty there and served about two years","in_exile_since":"1969-05-05"}]}' || true

php artisan prisoner:add '{"name":"Alain Allard","first_name":"Alain","last_name":"Allard","description":"Alain Allard was a Front de libération du Québec (FLQ) separatist who, with fellow FLQ member Jean-Pierre Charette, hijacked National Airlines Flight 91 from New York to Havana on May 5, 1969, fleeing the Canadian police crackdown on the FLQ bombing campaign — the act put them on U.S. federal air-piracy wanted lists. After about ten years of exile in Cuba (and a period in France), the pair returned to Canada in 1979, pleaded guilty to FLQ-related charges, and served roughly two years in prison.","gender":"Male","ideologies":["Quebec separatism"],"affiliation":["Front de libération du Québec (FLQ)"],"era":"1960s","in_exile":true,"released":true,"cases":[{"charges":"U.S. federal air-piracy charges — the May 5, 1969 hijacking of National Airlines Flight 91 from New York to Havana","convicted":"Not tried in the United States — returned to Canada in 1979, pleaded guilty there and served about two years","in_exile_since":"1969-05-05"}]}' || true

echo
echo "Done. Hijacker-exile additions applied."
