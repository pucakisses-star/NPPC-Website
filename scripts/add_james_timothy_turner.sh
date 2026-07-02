#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_james_timothy_turner.sh
# James Timothy Turner (a.k.a. Tim Turner) — founder of the Republic for the
# united States of America (RuSA) sovereign-citizen movement; convicted March
# 2013 on 10 counts of tax fraud and related charges; sentenced July 2013 to
# 18 years federal prison; released December 2024.
set +e

echo "Adding James Timothy Turner (Alabama, sovereign citizen, 2012)..."
php artisan prisoner:add '{
  "name": "James Timothy Turner",
  "first_name": "James",
  "last_name": "Turner",
  "description": "James Timothy Turner, known as Tim Turner, is an Alabama man who founded and led the Republic for the united States of America (RuSA), a sovereign-citizen movement that claimed to be a de facto replacement for the U.S. federal government. He was arrested on September 18, 2012, in Skipperville, Alabama, and charged with conspiracy to defraud the United States, passing fictitious financial instruments (a purported $300 million bond presented to the Internal Revenue Service), multiple counts of aiding others in submitting fictitious obligations, filing false IRS Form 1096 returns to impede the IRS, failure to file a personal income tax return for 2009, and perjury in a bankruptcy proceeding. Turner represented himself at trial and was convicted by a jury on all ten counts on March 22, 2013. On July 31, 2013, U.S. District Judge Myron Thompson of the Middle District of Alabama sentenced him to 18 years in federal prison and ordered him to pay $26,021 in restitution and serve five years of supervised release. The Eleventh Circuit Court of Appeals affirmed his conviction on August 15, 2014 (No. 13-13613). Turner was released in December 2024, possibly through First Step Act earned-time credits, after serving approximately 12 years. He resigned from RuSA in January 2025.",
  "state": "Alabama",
  "race": "White",
  "gender": "Male",
  "ideologies": ["Sovereign citizen", "Tax resistance", "Anti-government"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons",
      "charges": "Conspiracy to defraud the United States (18 U.S.C. § 371); passing fictitious financial instruments — $300 million bond submitted to the IRS (18 U.S.C. § 514(a)(2)); five counts of aiding and abetting the submission of fictitious obligations; filing false IRS Form 1096 and impeding the IRS; failure to file a 2009 federal income tax return (26 U.S.C. § 7203); perjury in a bankruptcy proceeding — 10 counts total; arose from activities as founder and leader of the Republic for the united States of America (RuSA) sovereign-citizen movement",
      "arrest_date": "2012-09-18",
      "incarceration_date": "2013-07-31",
      "release_date": "2024-12-01",
      "imprisoned_for_days": 4141,
      "convicted": "Yes — jury verdict (all 10 counts); self-represented at trial",
      "judge": "U.S. District Judge Myron Thompson, Middle District of Alabama",
      "prosecutor": "Justin Gelfand (DOJ Tax Division); Gray Borden (AUSA, Middle District of Alabama)",
      "sentence": "18 years federal prison; $26,021 restitution; 5 years supervised release"
    }
  ]
}'

echo ""
echo "Done."
