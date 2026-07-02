#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_animal_liberation_demuth.sh
# Scott DeMuth — Minneapolis anarchist and ALF activist; pleaded guilty to
# conspiracy to commit animal enterprise terrorism for a 2004 University of
# Iowa lab raid; served approximately 6 months federal prison in 2011.
set +e

echo "Adding Scott DeMuth (Animal Liberation Front, University of Iowa, 2011)..."
php artisan prisoner:add '{
  "name": "Scott DeMuth",
  "first_name": "Scott",
  "last_name": "DeMuth",
  "description": "Scott DeMuth is an anarchist and animal liberation activist from Minneapolis, Minnesota. In May 2004, he was part of an Animal Liberation Front action at the University of Iowa in Iowa City in which several hundred research animals were freed and approximately $400,000–$450,000 in damages were caused to laboratory facilities. DeMuth was subpoenaed in 2009 to testify before a federal grand jury investigating the raid. He refused to cooperate, citing concerns about self-incrimination and opposition to grand jury proceedings as a tool of political repression. He was subsequently indicted in the Southern District of Iowa on a charge of conspiracy to commit animal enterprise terrorism under the Animal Enterprise Terrorism Act (AETA). He pleaded guilty and in February 2011 began serving a six-month federal prison sentence. He was released approximately 165 days later in late July 2011. His prosecution was criticized by civil liberties advocates as an example of the AETA being used to target animal rights activism.",
  "state": "Minnesota",
  "gender": "Male",
  "ideologies": ["Animal liberation", "Anarchism", "Environmental justice"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Federal Correctional Institution",
      "institution_city": "Oxford",
      "institution_state": "Wisconsin",
      "charges": "Conspiracy to commit animal enterprise terrorism (Animal Enterprise Terrorism Act); guilty plea; 2004 ALF raid on University of Iowa animal research labs freed hundreds of animals and caused ~$400,000 in damages",
      "arrest_date": "2011-02-14",
      "incarceration_date": "2011-02-14",
      "release_date": "2011-07-29",
      "imprisoned_for_days": 165,
      "convicted": "Yes — guilty plea",
      "sentence": "6 months federal prison"
    }
  ]
}'

echo ""
echo "Done."
