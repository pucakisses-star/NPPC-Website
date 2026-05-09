#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_salah_sarsour.sh
#
# Salah Sarsour — Palestinian-American, lawful permanent resident,
# president of the Islamic Society of Milwaukee (the largest mosque
# in Wisconsin). On April 1, 2026 nearly a dozen ICE agents
# surrounded his car as he left his Milwaukee home and detained him.
# The government cited Section 237(a)(4)(C)(i) of the INA — the
# "foreign policy" deportability ground — claiming his presence
# poses a foreign-policy threat. He was transferred to a county jail
# in Indiana under ICE contract. Supporters (CAIR, the Muslim Legal
# Fund of America, ten Muslim civil-rights groups, and Wisconsin
# elected officials and clergy) say the detention is retaliation for
# his Palestinian advocacy and his decades-old Israeli military-court
# conviction (he was 15 at the time, charged in the West Bank with
# throwing rocks at Israeli soldiers). He has no U.S. criminal record;
# his wife and four adult children are U.S. citizens.
set -e

php artisan prisoner:add '{"name":"Salah Sarsour","first_name":"Salah","last_name":"Sarsour","description":"Salah Sarsour, age 53, is a Palestinian-American lawful permanent resident and the president of the Islamic Society of Milwaukee, the largest mosque in Wisconsin. Born in the Israeli-occupied West Bank, he came to the United States in 1993 and has lived here for over thirty years. His wife and four adult children are U.S. citizens. He has no U.S. criminal record.\n\nOn the morning of April 1, 2026, nearly a dozen ICE agents surrounded his car as he left his Milwaukee home and detained him. He was transferred to a county jail in Indiana operating under ICE contract. The government invoked Section 237(a)(4)(C)(i) of the Immigration and Nationality Act — the so-called foreign policy deportability ground — claiming Secretary of State decision-making had determined his presence in the country poses a foreign-policy threat. The same statutory provision was used against Mahmoud Khalil, Rumeysa Ozturk, and other Palestinian-solidarity organizers in 2025-2026.\n\nThe Department of Homeland Security cited a juvenile-era Israeli military-court conviction from when Sarsour was 15 (allegedly throwing rocks at Israeli soldiers in the occupied West Bank) — a record the U.S. government has known about for thirty years. The Council on American-Islamic Relations, the Muslim Legal Fund of America, and ten Muslim civil-rights organizations issued a joint letter denouncing the arrest as targeting Sarsour for his Palestinian and Muslim background. Wisconsin elected officials and Milwaukee clergy joined calls for his release. His attorneys filed a habeas petition seeking immediate release.","state":"Wisconsin","race":"Arab","gender":"Male","ideologies":["Palestine solidarity","First Amendment"],"affiliation":["Islamic Society of Milwaukee"],"era":"2020s","in_custody":true,"released":false,"cases":[{"institution_name":"ICE detention (county jail, Indiana)","institution_state":"Indiana","charges":"Civil immigration detention under INA § 237(a)(4)(C)(i) — foreign-policy deportability ground; DHS cited a 1980s Israeli military-court juvenile conviction for allegedly throwing rocks at Israeli soldiers in the West Bank. No U.S. criminal charges.","arrest_date":"2026-04-01","incarceration_date":"2026-04-01","sentence":"Civil ICE custody pending removal proceedings; habeas petition pending.","convicted":"No — civil immigration detention; no U.S. conviction"}]}' || true

echo
echo "Salah Sarsour prisoner entry added."
