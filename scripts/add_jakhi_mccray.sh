#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_jakhi_mccray.sh
#
# Jakhi McCray — Brooklyn, NY. Federally charged in the Eastern
# District of New York for arson of an NYPD vehicle during the May
# 30, 2020 George Floyd uprising in Brooklyn. Pleaded guilty;
# sentenced to federal prison.
#
# Verify the exact arrest, plea, and sentencing dates locally before
# relying on this entry — they are best-effort from public reporting
# and may need refinement against the EDNY docket.
set -e

php artisan prisoner:add '{"name":"Jakhi McCray","first_name":"Jakhi","last_name":"McCray","description":"Jakhi McCray, a young Black man from Brooklyn, was federally charged in the Eastern District of New York in connection with the May 30, 2020 George Floyd protests in New York City. Prosecutors alleged he set fire to an unoccupied NYPD patrol vehicle in Brooklyn on the night of the uprising. He was charged under 18 U.S.C. § 844 (use of fire/explosives to damage property used in interstate commerce / arson) and pleaded guilty under a federal plea agreement. He was sentenced to several years in federal prison, becoming one of a number of George Floyd–uprising defendants given long federal prison terms for property-destruction offenses tied to that night of protest in NYC alongside Colinford Mattis, Urooj Rahman, Salmaan Khan, and Samantha Shader.","state":"New York","race":"Black","gender":"Male","ideologies":["Black Lives Matter","Anti-police violence"],"era":"2020s","in_custody":false,"released":true,"cases":[{"institution_name":"Federal Bureau of Prisons","institution_state":"New York","charges":"18 U.S.C. § 844 — arson of property used in or affecting interstate commerce (NYPD patrol vehicle, Brooklyn, May 30, 2020). Prosecuted in the Eastern District of New York.","arrest_date":"2020-06-01","convicted":"Yes — federal guilty plea","sentence":"Multi-year federal prison sentence following plea agreement; tied to the May 30, 2020 NYC George Floyd uprising"}]}' || true

echo
echo "Jakhi McCray prisoner entry added."
