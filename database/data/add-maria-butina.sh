#!/usr/bin/env bash
#
# Add Maria Butina — Russian gun-rights activist jailed in the US
# (BBC: "Maria Butina: The Russian gun activist who was jailed in the US").
#
# Arrested July 15, 2018 in Washington, D.C.; charged with conspiracy to
# act as an agent of a foreign government (18 U.S.C. §§ 371/951) and acting
# as an unregistered agent of the Russian Federation (18 U.S.C. § 951).
# Pleaded guilty December 13, 2018 to the conspiracy count (the § 951
# count was dropped); sentenced April 26, 2019 by U.S. District Judge
# Tanya S. Chutkan (D.D.C., 1:18-cr-00218-TSC) to 18 months with credit
# for 9 months of pretrial detention; served at FCI Tallahassee; released
# October 25, 2019 and immediately deported to Russia.
#
# prisoner:add checks for duplicates by name, so this is safe to re-run.
#
# Run from the repo root:  bash database/data/add-maria-butina.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{
  "name": "Maria Butina",
  "first_name": "Maria",
  "last_name": "Butina",
  "description": "Maria Valeryevna Butina, a Russian gun-rights activist and American University graduate student born in Barnaul, Siberia, founded the Russian organization Right to Bear Arms and built ties with American gun-rights groups. She was arrested in Washington, D.C. on July 15, 2018 and charged with conspiracy to act as an agent of a foreign government and acting as an unregistered agent of the Russian Federation. Held without bail for nine months, she pleaded guilty on December 13, 2018 to the conspiracy count and was sentenced on April 26, 2019 to 18 months in federal prison. Her prosecution was widely covered as a test of how far 18 U.S.C. section 951 reaches into political networking and influence activity. She served her remaining term at FCI Tallahassee, was released on October 25, 2019, and was immediately deported to Russia, where she was later elected to the State Duma.",
  "state": "District of Columbia",
  "race": "White",
  "gender": "Female",
  "birthdate": "1988-11-10",
  "ideologies": ["Gun rights"],
  "affiliation": ["Right to Bear Arms (Russia)"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Federal Correctional Institution, Tallahassee",
      "institution_city": "Tallahassee",
      "institution_state": "Florida",
      "charges": "Conspiracy to act as an agent of a foreign government (18 U.S.C. sections 371, 951); acting as an unregistered agent of the Russian Federation (18 U.S.C. section 951) — dropped under the plea agreement",
      "arrest_date": "2018-07-15",
      "incarceration_date": "2018-07-15",
      "release_date": "2019-10-25",
      "convicted": "Yes — guilty plea (December 13, 2018, conspiracy count)",
      "judge": "Tanya S. Chutkan (U.S. District Judge, D.D.C.)",
      "sentence": "18 months federal prison with credit for 9 months pretrial detention; released October 25, 2019 and deported to Russia",
      "imprisoned_for_days": 467
    }
  ]
}'

echo
echo "Done. Maria Butina added (or already present)."
