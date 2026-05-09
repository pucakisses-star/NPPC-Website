#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_mario_guevara.sh
#
# Mario Guevara — Salvadoran-American journalist (Atlanta, GA), founder
# of MG News. On June 14, 2025 DeKalb County police arrested him while
# he was livestreaming the "No Kings" anti-Trump protest. The criminal
# charges were dropped, but he was transferred to ICE custody and held
# at the Folkston ICE Processing Center in Folkston, GA. The Committee
# to Protect Journalists, Reporters Without Borders, and PEN America
# all condemned his detention as press-freedom retaliation. He was the
# first U.S. journalist arrested while reporting since 2022 to be held
# by ICE. He was deported to El Salvador in late 2025.
set -e

php artisan prisoner:add '{"name":"Mario Guevara","first_name":"Mario","last_name":"Guevara","description":"Mario Guevara is a Salvadoran-American journalist based in Atlanta, Georgia, and the founder of MG News (formerly a reporter for the Spanish-language paper Mundo Hispánico). On June 14, 2025, DeKalb County police arrested him while he was livestreaming the No Kings anti-Trump protest in Chamblee, Georgia. He was charged with pedestrian on or along the roadway, unlawful assembly, and obstruction; the DeKalb County district attorney later declined to prosecute the criminal case.\n\nWhile the criminal case was being dismissed, ICE took him into custody on June 18, 2025 and held him at the Folkston ICE Processing Center in Folkston, Georgia. Guevara had Temporary Protected Status (TPS) and a pending green-card application sponsored by his U.S.-citizen son. His attorneys argued the immigration detention was First Amendment retaliation against a working journalist; the Committee to Protect Journalists, Reporters Without Borders, and PEN America all called publicly for his release. An immigration judge ordered him released on bond, but ICE invoked a discretionary stay and kept him detained. He was ultimately deported to El Salvador in late October 2025, separating him from his wife and three U.S.-citizen children. He continues reporting from El Salvador.","state":"Georgia","race":"Latino","gender":"Male","ideologies":["Press Freedom","First Amendment"],"affiliation":["MG News"],"era":"2020s","in_custody":false,"released":true,"currently_in_exile":true,"in_exile":true,"cases":[{"institution_name":"Folkston ICE Processing Center","institution_city":"Folkston","institution_state":"Georgia","charges":"DeKalb County: pedestrian on/along roadway, unlawful assembly, obstruction (criminal charges declined for prosecution by DA). ICE: civil immigration detention based on alleged status grounds despite active TPS and pending green-card sponsorship.","arrest_date":"2025-06-14","incarceration_date":"2025-06-18","release_date":"2025-10-30","sentence":"~4.5 months in ICE custody (June 18 – late Oct 2025) before deportation to El Salvador. Subject of Committee to Protect Journalists, Reporters Without Borders, and PEN America press-freedom campaigns.","convicted":"No — criminal charges declined by DeKalb DA; ICE detention was civil immigration custody followed by deportation"}]}' || true

echo
echo "Mario Guevara prisoner entry added."
