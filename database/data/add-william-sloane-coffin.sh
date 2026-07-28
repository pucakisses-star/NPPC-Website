#!/usr/bin/env bash
#
# Add William Sloane Coffin -- the second half of the un-merged record.
#
# fix-bio-date-conflicts.sh separated the two William Coffins by removing the
# Sloane Coffin material grafted onto the Everett Massacre Wobbly record
# (slug william-coffin, which keeps that name). This script gives the
# chaplain his own record, slug william-sloane-coffin.
#
# He qualifies on his own custody, not just the Boston Five prosecution: on
# May 25, 1961 he was arrested with six other Freedom Riders at the
# Montgomery Greyhound terminal and JAILED OVERNIGHT, released the next day
# on a \$1,000 cash bond (contemporary wire reporting: "left jail at
# Montgomery after spending the night behind bars"). The breach-of-peace
# convictions from that arrest were later overturned on appeal.
#
#   Born  June 1, 1924, New York City
#   Died  April 12, 2006
#
# Two cases:
#   Montgomery 1961  arrest + incarceration May 25, release May 26 -- the
#                    only documented custody, so the counter shows 1 day.
#                    No institution attached: the sources say only "jail
#                    at Montgomery," and guessing city vs county would be
#                    fabrication.
#   Boston Five 1968 conspiracy to counsel draft resistance; convicted
#                    June 14, 1968, sentenced to two years July 10;
#                    REVERSED July 11, 1969; free throughout, served no
#                    time. NO custody dates -- adds nothing to the counter,
#                    exactly like the existing Benjamin Spock and Mitchell
#                    Goodman records, whose Boston Five affiliation this
#                    record shares.
#
# The command refuses duplicates by name; "William Sloane Coffin" does not
# collide with the Wobbly record "William Coffin". Run from the repo root:
#   bash database/data/add-william-sloane-coffin.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{
  "name": "William Sloane Coffin",
  "first_name": "William",
  "middle_name": "Sloane",
  "last_name": "Coffin",
  "aka": "William Sloane Coffin Jr.",
  "description": "William Sloane Coffin Jr. was an American clergyman and one of the most prominent liberal religious voices of the postwar era. Born in New York City, he served in the Army in the Second World War, spent three years in the CIA, and in 1958 became chaplain of Yale University. In May 1961 he organized a group of professors, chaplains and students who rode an interstate bus from Atlanta into Montgomery, Alabama as Freedom Riders days after a mob had beaten the first riders there. Arrested at the Montgomery Greyhound terminal on May 25, 1961 when the group asked for service at the lunch counter, Coffin and his companions were charged with breach of the peace and jailed overnight, released the next day on $1,000 cash bonds; the convictions were later overturned on appeal. In January 1968 he was indicted in Boston with Dr. Benjamin Spock, Mitchell Goodman, Michael Ferber and Marcus Raskin — the Boston Five — for conspiracy to counsel, aid and abet draft resistance, after championing the statement A Call to Resist Illegitimate Authority and accepting draft cards from resisters. Convicted on June 14, 1968 and sentenced to two years, he remained free pending appeal; the First Circuit reversed the conviction on July 11, 1969 and the government abandoned the case, so he served no prison time. He went on to lead Riverside Church in New York and the nuclear-freeze movement as president of SANE/Freeze. He died on April 12, 2006.",
  "state": "Connecticut",
  "race": "White",
  "gender": "Male",
  "birthdate": "1924-06-01",
  "death_date": "2006-04-12",
  "ideologies": ["Civil Rights", "Anti-War"],
  "affiliation": ["Boston Five"],
  "era": "1960s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "charges": "Breach of the peace — arrested May 25, 1961 at the Montgomery, Alabama Greyhound terminal with six other Freedom Riders after the group requested service at the bus-station lunch counter.",
      "convicted": "Yes — the breach-of-peace convictions from the Montgomery arrests were later overturned on appeal.",
      "arrest_date": "1961-05-25",
      "incarceration_date": "1961-05-25",
      "release_date": "1961-05-26",
      "sentence": "Jailed overnight in Montgomery and released the next day on a $1,000 cash bond, posted for Coffin and his companions, among them Gaylord Noyce, David Swift and John Maguire. Contemporary wire reporting records the group leaving jail after a night behind bars. No jail is named in the sources, so no institution is attached."
    },
    {
      "charges": "Conspiracy to counsel, aid and abet draft resistance during the Vietnam War — the Boston Five indictment of January 1968, with Dr. Benjamin Spock, Mitchell Goodman, Michael Ferber and Marcus Raskin, over A Call to Resist Illegitimate Authority and the collection of draft cards from resisters.",
      "convicted": "Yes — convicted June 14, 1968; REVERSED by the First Circuit on July 11, 1969, and the government abandoned the prosecution. He remained free pending appeal throughout.",
      "sentenced_date": "1968-07-10",
      "sentence": "Two years, imposed July 10, 1968, never served: he remained free pending appeal and the conviction was reversed the following year. This case carries no custody dates and adds nothing to the time-imprisoned figure — the same treatment as the Benjamin Spock and Mitchell Goodman records."
    }
  ]
}'

echo
echo "Done."
