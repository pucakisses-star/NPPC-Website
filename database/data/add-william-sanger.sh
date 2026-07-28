#!/usr/bin/env bash
#
# Add William Sanger.
#
# Margaret Sanger is already in the database (Queens County Penitentiary,
# 1917); her husband was not, although he went to jail for the birth-control
# campaign eighteen months before she did.
#
#   Born      November 12, 1873
#   Died      July 25, 1961
#   Custody   September 10 – October 10, 1915 — 30 days
#
# In January 1915 an agent of Anthony Comstock’s New York Society for the
# Suppression of Vice called at Sanger’s studio posing as a sympathizer and
# obtained a copy of Family Limitation, the contraception pamphlet written by
# his wife, who had gone to England to avoid her own federal obscenity
# prosecution. Sanger was charged under section 1142 of the New York Penal
# Code — the same statute Margaret was convicted under two years later —
# tried in the Court of Special Sessions on September 10, 1915, and offered
# the choice of a \$150 fine or thirty days in jail. He refused to pay and
# served the full term. Comstock died on September 21, eleven days into the
# sentence.
#
# WHAT IS NOT RECORDED. The arrest date is not set: the decoy visit and
# arrest are firmly placed in January 1915, but no day is documented here, and
# a made-up day would start the counter in the wrong place. No institution is
# attached either — the jail he served the thirty days in is not established,
# and guessing Queens County because that is where Margaret went would be a
# fabrication. The counter therefore runs on the confirmed span alone:
# September 10 to October 10, 1915, thirty days.
#
# Ideology is Reproductive Rights, the term Margaret Sanger, Ben Reitman and
# Elmina Slenker carry. Era 1910s, state New York.
#
# The command refuses to create a duplicate, so a second run is a no-op that
# says so. Run from the repo root:
#   bash database/data/add-william-sanger.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{
  "name": "William Sanger",
  "first_name": "William",
  "last_name": "Sanger",
  "description": "William Sanger (November 12, 1873 – July 25, 1961) was an American architect and painter, and the husband of the birth-control campaigner Margaret Sanger. In January 1915, while Margaret was in England avoiding her own federal obscenity prosecution, an agent of Anthony Comstock’s New York Society for the Suppression of Vice called at Sanger’s studio posing as a sympathizer and asked for a copy of Family Limitation, the contraception pamphlet she had written. Sanger gave him one and was arrested for it, charged under section 1142 of the New York Penal Code with distributing contraceptive information — the same statute under which Margaret would be convicted two years later. Tried before the Court of Special Sessions in New York on September 10, 1915, he was convicted and offered the choice of a $150 fine or thirty days in jail. He refused to pay, saying he would not buy his way out of a law he considered unjust, and served the full thirty days, from September 10 to October 10, 1915. Comstock died on September 21, eleven days into the sentence. The prosecution — a man jailed for handing a pamphlet to a decoy agent — drew wide attention to the birth-control campaign and to the use of entrapment against it.",
  "state": "New York",
  "race": "White",
  "gender": "Male",
  "birthdate": "1873-11-12",
  "death_date": "1961-07-25",
  "ideologies": ["Reproductive Rights"],
  "era": "1910s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "charges": "Violation of New York Penal Code §1142 — distributing contraceptive information, for giving a copy of Family Limitation to an agent of Anthony Comstock’s New York Society for the Suppression of Vice who had called at his studio posing as a sympathizer.",
      "convicted": "Yes — convicted September 10, 1915 by the Court of Special Sessions in New York.",
      "sentenced_date": "1915-09-10",
      "incarceration_date": "1915-09-10",
      "release_date": "1915-10-10",
      "sentence": "A $150 fine or thirty days in jail. Sanger refused to pay the fine and served the full thirty days, September 10 to October 10, 1915. Anthony Comstock, whose agent had obtained the pamphlet, died on September 21, eleven days into the term. The jail is not recorded here because it is not established; the arrest, in January 1915, is likewise left undated rather than guessed."
    }
  ]
}'

echo
echo "Done."
