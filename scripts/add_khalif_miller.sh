#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_khalif_miller.sh
# Khalif Miller — Black father and small business owner from North Philadelphia;
# arrested Oct 28, 2020 after attending a George Floyd protest; sentenced April
# 2023 to 61 months federal prison (USP Big Sandy, KY) + 1–10 years Pennsylvania
# state sentence for a probation violation. Still in PA state custody as of Dec 2024.
set +e

echo "Adding Khalif Miller (Philadelphia, George Floyd protest, 2020)..."
php artisan prisoner:add '{
  "name": "Khalif Miller",
  "first_name": "Khalif",
  "last_name": "Miller",
  "description": "Khalif Miller is a father and small business owner from North Philadelphia who was arrested on October 28, 2020 — five months after attending a George Floyd protest near Philadelphia City Hall on May 30, 2020. A photograph showed him with his fist raised in a Black Power salute in front of a burning Philadelphia Police Department cruiser; prosecutors alleged he threw papers into the already-burning vehicle, while Miller maintained he was photographing the scene and never met his alleged co-conspirators. When federal agents came to arrest him they found firearms in his home, adding a felon-in-possession charge because of a prior involuntary manslaughter conviction. On April 3, 2023, U.S. District Chief Judge Juan R. Sanchez sentenced him to 61 months in federal prison at USP Big Sandy in Kentucky; he subsequently received an additional 1-10 year Pennsylvania state sentence for a probation violation and remained incarcerated as of December 2024.",
  "state": "Pennsylvania",
  "race": "Black",
  "gender": "Male",
  "ideologies": ["Black Liberation", "Anti-Police Brutality"],
  "era": "2020s",
  "in_custody": true,
  "released": false,
  "cases": [
    {
      "institution_name": "U.S. Penitentiary Big Sandy",
      "institution_city": "Inez",
      "institution_state": "Kentucky",
      "charges": "Obstruction of law enforcement during civil disorder (18 U.S.C. § 231); illegal possession of firearms as a prohibited person (18 U.S.C. § 922(g)(1)); original arson charges (18 U.S.C. § 844) dropped in plea agreement; arose from May 30, 2020 George Floyd protest near Philadelphia City Hall where a Philadelphia Police Department cruiser was burned",
      "arrest_date": "2020-10-28",
      "incarceration_date": "2020-10-28",
      "imprisoned_for_days": 2079,
      "convicted": "Yes — guilty plea (obstruction and firearms); arson counts dropped",
      "judge": "U.S. District Chief Judge Juan R. Sanchez, Eastern District of Pennsylvania",
      "prosecutor": "U.S. Attorney Bill McSwain",
      "sentence": "61 months (5 years, 1 month) federal prison; separately, 1-10 years Pennsylvania state imprisonment for probation violation (served at SCI Forest, Marienville, PA)"
    }
  ]
}'

echo ""
echo "Done."
