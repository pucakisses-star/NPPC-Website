#!/usr/bin/env bash
#
# Adds Rep. LaMonica McIver (D-NJ-10), federally prosecuted over the May 9,
# 2025 oversight visit to the Delaney Hall ICE detention facility in Newark.
# Charges are pending — she is awaiting trial, not in custody.
#
# Safe to re-run: prisoner:add refuses to create a duplicate by name.
#
# Run from the repo root:  bash database/data/add-lamonica-mciver.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"LaMonica McIver","first_name":"LaMonica","last_name":"McIver","description":"LaMonica McIver is the U.S. Representative for New Jerseys 10th congressional district and the first Black woman to hold the seat. A Newark native and former president of the Newark Municipal Council, she was elected to Congress in 2024. On May 9, 2025, McIver and two other members of Congress conducted an oversight inspection of Delaney Hall, a privately run ICE detention facility in Newark. When federal agents moved to arrest Newark Mayor Ras Baraka at the site, a scuffle broke out, and the interim U.S. Attorney for New Jersey, a Trump appointee, charged McIver with assaulting and impeding federal officers. A June 10, 2025 grand jury indictment charged her with three counts carrying up to 17 years in prison. McIver pleaded not guilty and calls the prosecution political intimidation aimed at congressional oversight of immigration detention. After the district court declined to dismiss the case, she took an interlocutory appeal to the Third Circuit, arguing the Constitutions speech or debate clause immunizes her oversight visit and that the prosecution is selective and vindictive. The appeals court heard argument on June 23, 2026. She is the first sitting member of Congress prosecuted over conduct arising from congressional oversight itself.","state":"New Jersey","race":"Black","gender":"Female","birthdate":"1986-06-20","ideologies":["Immigrant rights"],"affiliation":["Democratic Party","U.S. House of Representatives"],"era":"2020s","in_custody":false,"released":false,"awaiting_trial":true,"cases":[{"charges":"Three federal counts of assaulting, resisting, and impeding federal officers (18 U.S.C. 111) during a congressional oversight visit to the Delaney Hall ICE detention facility on May 9, 2025","convicted":"No — charges pending; interlocutory appeal argued before the Third Circuit on June 23, 2026 (speech-or-debate immunity and selective prosecution claims)","prosecutor":"Alina Habba (interim U.S. Attorney, District of New Jersey)","sentence":"Faces up to 17 years if convicted; not detained"}]}'

echo
echo "Done. LaMonica McIver added (duplicates are skipped automatically)."
