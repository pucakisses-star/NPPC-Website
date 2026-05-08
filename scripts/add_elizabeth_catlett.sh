#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_elizabeth_catlett.sh
#
# Adds Elizabeth Catlett (1915-2012), African American sculptor and
# printmaker who became a political exile from the United States during
# the McCarthy era. After moving to Mexico in 1946 to study with the
# leftist printmaking collective Taller de Gráfica Popular, Catlett was
# placed under U.S. State Department surveillance, declared an
# "undesirable alien," briefly arrested by Mexican authorities in 1959
# during a state crackdown on the Mexican Communist Party and railway
# workers' strike, and barred from re-entering the United States. She
# was prevented from attending her mother's funeral. She became a
# Mexican citizen in 1962 and was unable to return to the U.S. until
# 1971, when the State Department lifted the ban.
#
# Sources:
#   - https://en.wikipedia.org/wiki/Elizabeth_Catlett
#   - https://nmaahc.si.edu/explore/stories/elizabeth-catlett-art-life-resistance
#   - https://www.nytimes.com/2012/04/04/arts/design/elizabeth-catlett-sculptor-with-eye-on-social-issues-dies-at-96.html

set -e

php artisan prisoner:add '{
  "name": "Elizabeth Catlett",
  "first_name": "Elizabeth",
  "last_name": "Catlett",
  "aka": "Elizabeth Catlett Mora; Mora Catlett",
  "description": "Elizabeth Catlett (April 15, 1915 - April 2, 2012) was a Black American sculptor and printmaker whose work foregrounded the lives of African American and Mexican working-class women. After earning the first MFA in sculpture awarded by the University of Iowa (1940) under Grant Wood, she moved to Mexico City in 1946 on a Rosenwald Fellowship and joined the Taller de Gráfica Popular, a leftist printmaking collective that included Pablo OHiggins, Leopoldo Méndez, and Alfredo Zalce. The State Department and FBI placed her under surveillance through HUAC-era files, branded her an \"undesirable alien,\" and revoked her ability to return to the United States. In 1959, during the Mexican governments crackdown on the railway workers strike and the Mexican Communist Party, she was briefly arrested and held by Mexican authorities under U.S. pressure. She was prevented from attending her mothers funeral. Catlett became a Mexican citizen in 1962 and was barred from setting foot in the United States until 1971, when the State Department lifted the prohibition. She continued to teach at the Universidad Nacional Autónoma de México, where she was the first woman to head the sculpture department, until her retirement in 1976. She died in Cuernavaca, Morelos in 2012.",
  "state": "Mexico (in exile)",
  "race": "Black",
  "gender": "Female",
  "birthdate": "1915-04-15",
  "death_date": "2012-04-02",
  "ideologies": ["Pan-Africanism", "Black liberation", "Socialism", "Feminism"],
  "affiliation": ["Taller de Gráfica Popular", "Universidad Nacional Autónoma de México"],
  "era": "1950s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "U.S. State Department exclusion / Mexican custody (1959 PCM crackdown)",
      "institution_city": "Mexico City",
      "institution_state": "Mexico",
      "charges": "Declared an \"undesirable alien\" by the U.S. State Department for her membership in the Taller de Gráfica Popular and association with the Mexican Communist Party; placed under FBI/HUAC surveillance and barred from re-entering the United States. Briefly detained by Mexican authorities in 1959 during the governments crackdown on the railway workers strike and the Partido Comunista Mexicano. Prohibition on U.S. entry lifted in 1971.",
      "arrest_date": "1959-01-01",
      "release_date": "1971-01-01",
      "convicted": "No - administrative immigration exclusion, no criminal conviction",
      "sentence": "Effective political exile from the United States, ~12 years (1959-1971)"
    }
  ]
}'
