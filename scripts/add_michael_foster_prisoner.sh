#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_michael_foster_prisoner.sh
set -e

php artisan prisoner:add '{
  "name": "Michael Foster",
  "first_name": "Michael",
  "last_name": "Foster",
  "description": "Michael Foster is a Seattle-based climate activist and one of five coordinated \"valve turners\" who on October 11, 2016 simultaneously shut off tar sands oil pipelines entering the United States from Canada. Foster physically turned the emergency shut-off valve on TransCanada'\''s Keystone pipeline near Walhalla, North Dakota. The coordinated action was intended to draw attention to the climate emergency caused by tar sands extraction and pipeline infrastructure, and to demonstrate that pipelines could be safely stopped in the event of environmental catastrophe. Foster and his co-defendants were denied the \"necessity defense\" — which would have allowed them to argue that breaking the law was justified to prevent greater climate harm — and Foster was convicted on charges of felony criminal mischief, conspiracy to commit criminal mischief, and misdemeanor trespass. In February 2018 he was sentenced to three years in prison with two years deferred, effectively serving approximately one year of incarceration.",
  "state": "Washington",
  "gender": "Male",
  "ideologies": ["Climate justice", "Environmental activism", "Anti-tar sands", "Direct action"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "North Dakota State Penitentiary",
      "institution_city": "Bismarck",
      "institution_state": "North Dakota",
      "charges": "Felony criminal mischief, conspiracy to commit criminal mischief, misdemeanor trespass; shut off Keystone pipeline valve near Walhalla, ND as part of coordinated climate direct action",
      "arrest_date": "2016-10-11",
      "incarceration_date": "2018-02-07",
      "sentence": "3 years (2 years deferred); served approximately 1 year"
    }
  ]
}'
