#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_valve_turners_remaining.sh
# Adds the four remaining valve turners from the Oct 11, 2016 coordinated action.
# Michael Foster (the fifth) was added separately via add_michael_foster_prisoner.sh
set -e

echo "Adding Ken Ward..."
php artisan prisoner:add '{
  "name": "Ken Ward",
  "first_name": "Ken",
  "last_name": "Ward",
  "description": "Ken Ward is a climate activist from Corbett, Oregon and former Greenpeace campaigner who was one of five coordinated \"valve turners\" who on October 11, 2016 simultaneously shut off tar sands oil pipelines entering the United States from Canada. Ward turned off a Kinder Morgan Trans Mountain pipeline valve in Skagit County, Washington. He and his co-defendants argued the necessity defense — that their actions were justified to prevent the greater harm of catastrophic climate change — but judges in most of the cases, including his, refused to allow the defense at trial. Ward'\''s first trial, in January 2017, ended in a hung jury. His second trial resulted in conviction on a charge of second-degree burglary. A Washington judge sentenced him to time served (two days) plus 30 days of community service and six months of probation. The Washington Supreme Court later ruled that necessity defense arguments must be allowed in climate direct action cases, establishing significant legal precedent.",
  "state": "Oregon",
  "gender": "Male",
  "ideologies": ["Climate justice", "Environmental activism", "Anti-tar sands", "Direct action"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Skagit County Superior Court",
      "institution_city": "Mount Vernon",
      "institution_state": "Washington",
      "charges": "Second-degree burglary; shut off Kinder Morgan Trans Mountain pipeline valve in Skagit County, WA as part of coordinated climate direct action",
      "arrest_date": "2016-10-11",
      "sentence": "2 days time served, 30 days community service, 6 months probation; no prison time"
    }
  ]
}'

echo "Adding Emily Johnston..."
php artisan prisoner:add '{
  "name": "Emily Johnston",
  "first_name": "Emily",
  "last_name": "Johnston",
  "description": "Emily Johnston is a Seattle-based poet and climate activist who co-founded 350 Seattle and was one of five coordinated \"valve turners\" who on October 11, 2016 simultaneously shut off tar sands oil pipelines entering the United States from Canada. Along with Annette Klapstein, Johnston turned off two Enbridge Energy petroleum pipelines at a valve station near Leonard, in Clearwater County, Minnesota. They were charged with felony criminal damage to property, aiding and abetting felony criminal damage, gross misdemeanor trespassing, and aiding and abetting trespassing — charges carrying potential penalties of over 20 years in prison and $40,000 in fines. In a significant legal development, a Minnesota judge allowed Johnston and Klapstein to present the necessity defense at trial, permitting them to introduce climate science evidence to justify their actions. In October 2018 a judge acquitted both women of all charges, finding that the prosecution had failed to prove any damage to the pipelines.",
  "state": "Washington",
  "gender": "Female",
  "ideologies": ["Climate justice", "Environmental activism", "Anti-tar sands", "Direct action"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Clearwater County District Court",
      "institution_city": "Bagley",
      "institution_state": "Minnesota",
      "charges": "Felony criminal damage to property, aiding and abetting felony criminal damage, gross misdemeanor trespassing; shut off two Enbridge Energy pipelines near Leonard, MN — acquitted of all charges October 2018",
      "arrest_date": "2016-10-11",
      "convicted": "No — acquitted"
    }
  ]
}'

echo "Adding Annette Klapstein..."
php artisan prisoner:add '{
  "name": "Annette Klapstein",
  "first_name": "Annette",
  "last_name": "Klapstein",
  "description": "Annette Klapstein is a retired attorney and climate activist who was one of five coordinated \"valve turners\" who on October 11, 2016 simultaneously shut off tar sands oil pipelines entering the United States from Canada. Along with Emily Johnston, Klapstein turned off two Enbridge Energy petroleum pipelines at a valve station near Leonard, in Clearwater County, Minnesota. They were charged with felony criminal damage to property, aiding and abetting felony criminal damage, gross misdemeanor trespassing, and aiding and abetting trespassing — charges carrying potential penalties of over 20 years in prison and $40,000 in fines. A Minnesota judge allowed Johnston and Klapstein to present the necessity defense at trial, permitting them to introduce climate science evidence to justify their actions. In October 2018 a judge acquitted both women of all charges, finding that the prosecution had failed to prove any damage to the pipelines.",
  "state": "Minnesota",
  "gender": "Female",
  "ideologies": ["Climate justice", "Environmental activism", "Anti-tar sands", "Direct action"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Clearwater County District Court",
      "institution_city": "Bagley",
      "institution_state": "Minnesota",
      "charges": "Felony criminal damage to property, aiding and abetting felony criminal damage, gross misdemeanor trespassing; shut off two Enbridge Energy pipelines near Leonard, MN — acquitted of all charges October 2018",
      "arrest_date": "2016-10-11",
      "convicted": "No — acquitted"
    }
  ]
}'

echo "Adding Leonard Higgins..."
php artisan prisoner:add '{
  "name": "Leonard Higgins",
  "first_name": "Leonard",
  "last_name": "Higgins",
  "description": "Leonard Higgins is a retired Oregon state worker and climate activist from Corvallis, Oregon who was one of five coordinated \"valve turners\" who on October 11, 2016 simultaneously shut off tar sands oil pipelines entering the United States from Canada. Higgins cut chains at an Enbridge tar sands pipeline valve station near Coal Banks, Montana and manually turned the emergency shutoff valve, briefly halting the flow of tar sands oil. Like his co-defendants, Higgins was denied the right to present a necessity defense at trial — barring him from explaining to the jury his climate change rationale. In November 2017, he was convicted of felony criminal mischief and misdemeanor criminal trespass, facing up to 10 years in prison and $50,000 in fines. A judge sentenced him to a three-year deferred sentence and probation, meaning he served no active prison time.",
  "state": "Oregon",
  "gender": "Male",
  "ideologies": ["Climate justice", "Environmental activism", "Anti-tar sands", "Direct action"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Montana District Court",
      "institution_state": "Montana",
      "charges": "Felony criminal mischief, misdemeanor criminal trespass; shut off Enbridge tar sands pipeline valve near Coal Banks, MT as part of coordinated climate direct action",
      "arrest_date": "2016-10-11",
      "sentence": "3-year deferred sentence, probation; no active prison time"
    }
  ]
}'

echo "All four valve turners added."
