#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_trita_parsi_prisoner.sh
set -e

php artisan prisoner:add '{
  "name": "Trita Parsi",
  "first_name": "Trita",
  "last_name": "Parsi",
  "description": "Trita Parsi is an Iranian-born Swedish-American political analyst and foreign policy advocate who co-founded the National Iranian American Council (NIAC) in 2002 and the Quincy Institute for Responsible Statecraft in 2019. A prominent critic of U.S. military intervention against Iran and a leading voice for diplomatic engagement, Parsi appeared regularly in U.S. media as an opponent of the Trump administration'\''s hawkish Iran policy. In June 2026, the State Department opened an investigation into Parsi — a lawful permanent resident who had lived in the United States for more than two decades — reportedly seeking grounds to revoke his green card and initiate deportation proceedings. The probe followed pressure from far-right influencer Laura Loomer, who called Parsi a mouthpiece for the Iranian regime and predicted his deportation. Critics including the Quincy Institute, which pledged to cover his legal costs, condemned the investigation as a direct attack on First Amendment protections for permanent residents and an attempt to silence dissent against the administration'\''s Iran war policy through immigration enforcement.",
  "state": "District of Columbia",
  "gender": "Male",
  "race": "Middle Eastern",
  "ideologies": ["Anti-war", "Diplomatic engagement", "Anti-imperialism"],
  "era": "2020s",
  "in_custody": false,
  "released": false,
  "cases": [
    {
      "institution_name": "State Department Investigation",
      "institution_state": "District of Columbia",
      "charges": "Investigated for possible green card revocation and deportation based on public criticism of U.S. military intervention against Iran and advocacy for diplomatic engagement with Iran",
      "arrest_date": "2026-06-11"
    }
  ]
}'
