#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_robert_hoopes_prisoner.sh
# Robert Jacob Hoopes, Portland anti-ICE protester, sentenced June 2026 to 30 months federal prison
set +e

echo "Adding Robert Jacob Hoopes..."
php artisan prisoner:add '{
  "name": "Robert Hoopes",
  "first_name": "Robert",
  "last_name": "Hoopes",
  "description": "Robert Jacob Hoopes, 25, is an activist from Portland, Oregon with ties to the Quaker community. He was among the protesters who gathered regularly outside the U.S. Immigration and Customs Enforcement building in Portland in 2025 to protest the Trump administration'\''s mass deportation campaign. During a protest in June 2025, Hoopes threw a rock that struck an ICE officer in the head, opening a gash over the officer'\''s eye; he also used a stop sign as a battering ram causing approximately $7,000 in damage to the ICE facility. He was identified through FBI facial recognition technology. Under a plea agreement, Hoopes pleaded guilty to aggravated assault of a federal employee with a dangerous weapon; charges related to the property damage were dismissed. On June 11, 2026, U.S. District Judge Adrienne Nelson sentenced him to 30 months in federal prison plus three years of supervised release and ordered him to pay over $8,000 in restitution.",
  "state": "Oregon",
  "gender": "Male",
  "ideologies": ["Anti-deportation", "Anti-ICE", "Immigrant rights"],
  "era": "2020s",
  "in_custody": true,
  "released": false,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons",
      "institution_state": "Oregon",
      "charges": "Aggravated assault of a federal employee with a dangerous weapon; guilty plea; threw rock at ICE officer during Portland anti-ICE protests, June 2025",
      "incarceration_date": "2026-06-11",
      "sentence": "30 months federal prison plus 3 years supervised release"
    }
  ]
}'

echo "Robert Hoopes added."
