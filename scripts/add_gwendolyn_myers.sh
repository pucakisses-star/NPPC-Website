#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_gwendolyn_myers.sh
#
# Adds Gwendolyn "Gwen" Steingraber Myers, wife and co-defendant of
# former U.S. State Department analyst Walter Kendall Myers, in the
# 2009-2010 federal Cuba-espionage prosecution.
#
# Source-derived facts:
#   - Arrested June 4, 2009 in Washington, DC alongside her husband
#     after a Department of Justice undercover sting in which a
#     federal agent posed as a Cuban intelligence officer.
#   - Pleaded guilty November 20, 2009 to conspiracy to act as an
#     illegal agent of a foreign government and to wire fraud.
#   - Sentenced July 16, 2010 in U.S. District Court for the District
#     of Columbia to 81 months (6 years 9 months) federal prison
#     (Kendall received life).
#   - Released from federal custody in 2015 after serving roughly
#     5 1/2 years.
#
# Sources: ABC News; CNN; NPR; Seattle Times; Wikipedia (Kendall
# Myers entry); U.S. Department of State announcement (2009).
set -e

php artisan prisoner:add '{
  "name": "Gwendolyn Myers",
  "first_name": "Gwendolyn",
  "middle_name": "Steingraber",
  "last_name": "Myers",
  "aka": "Gwen Myers",
  "description": "Gwendolyn \"Gwen\" Steingraber Myers was the wife and co-defendant of former U.S. State Department analyst Walter Kendall Myers in the 2009-2010 federal Cuba-espionage prosecution. The couple were arrested in Washington, DC on June 4, 2009 after a Justice Department undercover sting in which a federal agent posed as a Cuban intelligence officer. Prosecutors alleged that the Myerses had spied for Cuban intelligence for nearly 30 years, with Gwendolyn serving as her husband’s primary courier and meeting Cuban handlers in third countries. On November 20, 2009 she pleaded guilty in U.S. District Court for the District of Columbia to conspiracy to act as an illegal agent of a foreign government and to wire fraud. On July 16, 2010 Senior Judge Reggie B. Walton sentenced her to 81 months (6 years 9 months) of federal imprisonment under a binding plea agreement. Her husband Kendall was sentenced the same day to life imprisonment without parole. Gwendolyn was released from federal custody in 2015 after serving roughly five and a half years. She has since lived quietly with family. (Sources: ABC News; CNN; NPR; Seattle Times; Department of State 2009 announcement.)",
  "state": "District of Columbia",
  "race": "White",
  "gender": "Female",
  "birthdate": "1939-01-01",
  "ideologies": ["Cuba solidarity"],
  "era": "2000s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons (location varied)",
      "charges": "Conspiracy to act as an illegal agent of a foreign government (Cuba) and wire fraud, in connection with nearly 30 years of espionage carried out jointly with her husband, former State Department analyst Walter Kendall Myers. Arrested June 4, 2009 in Washington, DC after a DOJ undercover sting; pleaded guilty November 20, 2009 in U.S. District Court for the District of Columbia.",
      "arrest_date": "2009-06-04",
      "incarceration_date": "2009-06-04",
      "sentenced_date": "2010-07-16",
      "release_date": "2015-12-15",
      "sentence": "81 months (6 years 9 months) federal prison under a binding plea agreement; sentenced July 16, 2010 by Senior Judge Reggie B. Walton (D.D.C.). Released from federal custody in 2015 after roughly 5 1/2 years served.",
      "convicted": "Yes - guilty plea, U.S. District Court for the District of Columbia, November 20, 2009",
      "judge": "Reggie B. Walton",
      "prosecutor": "U.S. Attorneys Office for the District of Columbia"
    }
  ]
}' || true

echo
echo "Gwendolyn Myers add complete."
