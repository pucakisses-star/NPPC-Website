#!/usr/bin/env bash
#
# Add four Anonymous / LulzSec-affiliated hackers who were missing from the
# database, via the preferred prisoner:add command. Only dates that are
# documented are recorded; where the exact prison-entry/release date is not
# established (Colby, Kretsinger, Rivera), the field is left blank rather
# than guessed.
#
#   - Ross Colby            (Anonymous; 2015 California newspaper defacements)
#   - Hector Xavier Monsegur "Sabu" (Anonymous/LulzSec; custody 2012-05-25 to 2012-12-18)
#   - Cody Andrew Kretsinger "recursion" (LulzSec/Anonymous; Sony Pictures 2011)
#   - Raynaldo Rivera        "neuron" (LulzSec/Anonymous; Sony Pictures 2011)
#
# prisoner:add refuses to create a duplicate name, so this is safe to re-run.
# Run from the repo root:
#   bash database/data/add-anonymous-lulzsec-prisoners.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

add() {
    php artisan prisoner:add "$1" || echo "  (skipped — prisoner likely already exists)"
    echo
}

add '{"name":"Ross Colby","first_name":"Ross","last_name":"Colby","description":"Ross Colby was a hacker associated with Anonymous who in 2015 broke into the content systems of several California newspapers and replaced their websites with a Guy Fawkes image linked to Anonymous and an Anonymous-style announcement. Prosecuted in federal court in the Northern District of California, he spent almost six months in federal custody, and in June 2019 he was sentenced to time served, one year of home incarceration, three years of supervised release and restitution.","gender":"Male","affiliation":["Anonymous"],"ideologies":["Hacktivism"],"era":"2010s","released":true,"cases":[{"charges":"Computer hacking — unauthorized impairment of protected computers; defaced several California newspaper websites with a Guy Fawkes / Anonymous message (2015)","convicted":"Yes","sentenced_date":"2019-06-12","release_date":"2019-06-12","sentence":"Time served (almost six months), one year of home incarceration, three years of supervised release and restitution"}]}'

add '{"name":"Hector Xavier Monsegur","first_name":"Hector","middle_name":"Xavier","last_name":"Monsegur","aka":"Sabu","description":"Hector Xavier Monsegur, known online as Sabu, was a central organizer of Anonymous and its offshoot LulzSec before he was arrested in 2011 and became an FBI cooperator. After pleading guilty, his bail was revoked on May 25, 2012 over unauthorized internet activity, and he remained in federal custody until revised bail was granted on December 18, 2012 — roughly seven months. On May 27, 2014 he was sentenced to time served and one year of supervised release.","gender":"Male","race":"Hispanic","state":"New York","affiliation":["Anonymous","LulzSec"],"ideologies":["Hacktivism"],"era":"2010s","released":true,"cases":[{"charges":"Computer-hacking conspiracy and related computer-intrusion counts as an organizer of Anonymous and LulzSec","plead":"Guilty","convicted":"Yes — guilty plea (2011)","arrest_date":"2011-06-07","incarceration_date":"2012-05-25","release_date":"2012-12-18","sentenced_date":"2014-05-27","sentence":"Time served (approximately seven months) and one year of supervised release","imprisoned_for_days":207}]}'

add '{"name":"Cody Andrew Kretsinger","first_name":"Cody","middle_name":"Andrew","last_name":"Kretsinger","aka":"recursion","description":"Cody Andrew Kretsinger, known online as recursion, was a member of LulzSec, an offshoot the Justice Department described as affiliated with Anonymous. He took part in the 2011 intrusion into Sony Pictures. He pleaded guilty and on April 18, 2013 was sentenced to a year and a day in federal prison, one year of home detention, 1,000 hours of community service and restitution. His exact self-surrender and release dates are not established in the public record.","gender":"Male","state":"Arizona","affiliation":["Anonymous","LulzSec"],"ideologies":["Hacktivism"],"era":"2010s","released":true,"cases":[{"charges":"Conspiracy and unauthorized impairment of a protected computer — 2011 LulzSec intrusion into Sony Pictures","plead":"Guilty","convicted":"Yes — guilty plea","sentenced_date":"2013-04-18","sentence":"One year and one day in federal prison, one year of home detention, 1,000 community-service hours and $605,663 restitution"}]}'

add '{"name":"Raynaldo Rivera","first_name":"Raynaldo","last_name":"Rivera","aka":"neuron","description":"Raynaldo Rivera, known online as neuron, royal and wildicv, was a member of LulzSec, an offshoot the Justice Department described as affiliated with Anonymous. He surrendered to the FBI in Phoenix in August 2012 and pleaded guilty for his role in the 2011 intrusion into Sony Pictures. On August 8, 2013 he was sentenced to a year and a day in federal prison, thirteen months of home detention, 1,000 hours of community service and restitution. Whether he remained continuously detained after surrendering is not established in the public record, so his prison-entry date is left blank.","gender":"Male","state":"Arizona","affiliation":["Anonymous","LulzSec"],"ideologies":["Hacktivism"],"era":"2010s","released":true,"cases":[{"charges":"Conspiracy and unauthorized impairment of a protected computer — 2011 LulzSec intrusion into Sony Pictures","plead":"Guilty","convicted":"Yes — guilty plea","arrest_date":"2012-08-28","sentenced_date":"2013-08-08","sentence":"One year and one day in federal prison, thirteen months of home detention, 1,000 community-service hours and $605,663 restitution"}]}'

echo "Done. Four Anonymous / LulzSec prisoners added (existing ones skipped)."
