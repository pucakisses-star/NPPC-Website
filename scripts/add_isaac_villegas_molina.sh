#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_isaac_villegas_molina.sh
#
# Isaac Antonio Villegas Molina — Los Angeles day laborer, named
# plaintiff in Vasquez-Perdomo v. Noem (the Southern California ICE
# raids racial-profiling lawsuit that reached the Supreme Court in
# September 2025 on the shadow docket). First detained at a bus stop
# in June 2025 during the LA ICE-raid surge; released on bond by an
# immigration judge. ICE re-detained him on April 16, 2026 without
# prior notice to him or his counsel, transferring him through the
# B18 ICE facility in the basement of the LA Metropolitan Detention
# Center to the Adelanto ICE Processing Center. The ACLU of Southern
# California and NDLON characterized the second arrest as the first
# documented retaliatory arrest of a plaintiff challenging ICE raids
# in the country. He was released a week later on April 23, 2026.
set -e

php artisan prisoner:add '{"name":"Isaac Antonio Villegas Molina","first_name":"Isaac","middle_name":"Antonio","last_name":"Villegas Molina","aka":"Isaac Villegas","description":"Isaac Antonio Villegas Molina is a Los Angeles day laborer and a named plaintiff in Vasquez-Perdomo v. Noem, the Southern California civil-rights lawsuit alleging that the Department of Homeland Security has unconstitutionally arrested and detained Latino day laborers, bus-stop riders, and street vendors to meet arbitrary arrest quotas without reasonable suspicion. The case reached the Supreme Court on the shadow docket in September 2025; the Court lifted the lower-court injunction restricting race-based stops, drawing dissents calling the ruling a license for blatant racial profiling.\n\nVillegas Molina was first detained by ICE in June 2025 while waiting at a bus stop outside a store in Los Angeles during the LA ICE-raid surge. An immigration judge ordered him released on bond. On April 16, 2026 — months after he became a named plaintiff in the lawsuit — ICE re-detained him without prior notice to him or to his attorneys, despite the standing bond order. He was first held at the B18 ICE facility in the basement of the Los Angeles Metropolitan Detention Center, then transferred to the Adelanto ICE Processing Center in the high desert.\n\nThe ACLU of Southern California issued a public statement calling the second detention retaliatory. NDLON attorney Cal Soto said: His arrests were unconstitutional, and his imprisonment in Adelanto was unlawfully cruel and punitive. Civil-rights advocates described it as the first documented retaliatory ICE arrest of a plaintiff challenging immigration raids in the United States. He was released from the Adelanto facility on April 23, 2026 after a week of community pressure, news coverage, and litigation.","state":"California","race":"Latino","gender":"Male","ideologies":["Immigrant rights","First Amendment"],"affiliation":["National Day Laborer Organizing Network (NDLON)"],"era":"2020s","in_custody":false,"released":true,"cases":[{"institution_name":"Adelanto ICE Processing Center","institution_city":"Adelanto","institution_state":"California","charges":"Civil immigration custody — first detention (June 2025) during the LA ICE-raid surge; second detention (April 16-23, 2026) carried out despite a standing bond release order, which the ACLU and NDLON described as retaliation for his role as a named plaintiff in Vasquez-Perdomo v. Noem.","arrest_date":"2026-04-16","incarceration_date":"2026-04-16","release_date":"2026-04-23","sentence":"Two ICE detentions in less than a year. Second detention was 7 days at Adelanto (after brief booking at the B18 LA-MDC ICE facility) before bond was finally honored.","convicted":"No — civil immigration custody; no criminal conviction"}]}' || true

echo
echo "Isaac Antonio Villegas Molina prisoner entry added."
