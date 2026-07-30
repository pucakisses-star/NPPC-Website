#!/usr/bin/env bash
#
# JOHN LETCHER -- Virginia’s wartime governor, held without charge for
# fifty-one days after the Confederate surrender.
#
# He is a new record. The database already holds his cellmate: Zebulon
# Vance, the wartime governor of North Carolina, whose entry says he was
# "delivered to the Old Capitol Prison in Washington on May 20, where he
# shared a cell with John Letcher, the former Governor of Virginia."
# Letcher’s entry now names Vance in return, so the two accounts agree
# from both directions. Vance reached Washington on May 20 and Letcher on
# May 24, which is consistent -- Letcher joined him four days later.
#
# CUSTODY. Arrested at Lexington, Virginia on May 20, 1865 by Union
# cavalry acting on General Grant’s order naming him a "particularly
# obnoxious" political leader; confined from May 24 in Washington;
# paroled July 10, 1865. Two spans are defensible and both are recorded:
# fifty-one days of total federal custody from the arrest, and
# forty-seven in Washington itself. The record uses the arrest date, so
# the day counter computes 51 -- which matches the figure in the sources
# exactly, with no interval-versus-inclusive discrepancy to explain.
#
# NO CHARGE, NO TRIAL. Convicted is recorded as No and charges as "None
# filed." -- the same treatment as Vance. From Carroll Prison on June 29
# Letcher wrote that he did not know why he had been arrested or when he
# would be discharged. The threat of a treason prosecution persisted
# until President Johnson pardoned him on January 15, 1867.
#
# THE FACILITY NAME. Archival records say Carroll Prison; other accounts
# call it the Old Capitol Prison. Carroll Prison was a set of adjoining
# buildings run as part of the Old Capitol military-prison complex, so
# the institution is recorded as Carroll Prison -- the specific name --
# and the sentence text explains the relationship. That leaves Vance
# under Old Capitol Prison and Letcher under Carroll Prison, which is
# what the respective sources say rather than a normalisation of one to
# the other.
#
# The payload carries curly apostrophes throughout, so it contains no
# straight quotes and is safe inside a single-quoted shell argument.
#
# prisoner:add refuses to create a duplicate name, so re-running this is
# safe: the second run reports that John Letcher already exists and
# changes nothing.
#
# Run from the repo root:
#   bash database/data/add-john-letcher.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name": "John Letcher", "first_name": "John", "last_name": "Letcher", "description": "John Letcher was a Virginia lawyer, newspaper editor, congressman, and the state’s governor during the first three years of the American Civil War. Although he initially resisted secession, after Virginia left the Union he supported the Confederate government and directed the mobilization of Virginia’s military personnel and material resources.\n\nFollowing the collapse of the Confederacy, Union cavalry arrested Letcher at Lexington, Virginia, on May 20, 1865, under an order from General Ulysses S. Grant identifying him as a “particularly obnoxious” political leader. He was taken to Washington, D.C., and confined beginning May 24 in Carroll Prison, an annex of the Old Capitol military prison, where he shared a cell with Zebulon Vance, the wartime governor of North Carolina, who had been delivered there four days earlier.\n\nNo formal criminal charge appears to have been prosecuted against him. While imprisoned, Letcher wrote on June 29 that he did not know precisely why he had been arrested or when he would be released. He was paroled on July 10, 1865, after approximately fifty-one days in federal custody. The threat of prosecution for treason continued until President Andrew Johnson granted him a full pardon on January 15, 1867.\n\nAfter returning to Lexington, Letcher resumed practicing law. He served on the Virginia Military Institute Board of Visitors and later represented Rockbridge County in the Virginia House of Delegates. He died in Lexington on January 26, 1884.", "state": "Virginia", "race": "White", "gender": "Male", "birthdate": "1813-03-29", "death_date": "1884-01-26", "era": "1800s", "in_custody": false, "released": true, "cases": [{"institution_name": "Carroll Prison", "institution_city": "Washington", "institution_state": "District of Columbia", "charges": "None filed.", "convicted": "No", "arrest_date": "1865-05-20", "incarceration_date": "1865-05-20", "release_date": "1865-07-10", "sentence": "No charges were brought and there was no trial. Arrested at Lexington, Virginia on May 20, 1865 by Union cavalry acting on General Ulysses S. Grant’s order naming him a “particularly obnoxious” political leader, he was taken to Washington and confined from May 24 in Carroll Prison, an annex of the Old Capitol military-prison complex — archival records name Carroll Prison specifically, while other accounts call the whole complex the Old Capitol. He was paroled on July 10, 1865 after fifty-one days in federal custody, of which forty-seven were in Washington. President Andrew Johnson granted him a full pardon on January 15, 1867."}]}'

echo
echo "Done."
