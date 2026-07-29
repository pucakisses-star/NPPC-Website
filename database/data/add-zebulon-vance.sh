#!/usr/bin/env bash
#
# Add Zebulon Baird Vance -- Confederate-era Governor of North Carolina,
# held without charge for seven weeks after the war and released on
# parole under the Johnson amnesty.
#
# CUSTODY. Arrested at Statesville on May 13, 1865 -- his thirty-fifth
# birthday -- by General Hugh Judson Kilpatrick. Held briefly at
# Raleigh, then delivered to the Old Capitol Prison in Washington, D.C.
# on May 20, 1865, where he shared a small cell with John Letcher, the
# former Governor of Virginia; each man had an iron bed and a chair and
# had to pay for meals sent in from a local restaurant. He applied for
# parole on June 3, 1865 under President Johnson amnesty proclamation,
# and Johnson paroled him on July 6, 1865.
#
#   May 13 to July 6, 1865 = 54 days.
#
# The counter is set from those two dates. Raleigh is not given a
# separate case row: the sources describe one continuous detention that
# began at the arrest and ended at the parole, with Old Capitol as the
# institution of record.
#
# NO CHARGES WERE FILED and there was no trial, so convicted is No.
# That is the whole basis for the record: an executive detention of a
# state governor without process. The description does not editorialise
# about the cause he served -- it states what he did and what was done
# to him.
#
# LIFE DATES. Born May 13, 1830 at Reems Creek, Buncombe County, North
# Carolina; died April 14, 1894 in Washington, D.C. The arrest date and
# the birthday are the same May 13 by coincidence, and the sources make
# a point of it.
#
# The payload uses a quoted heredoc so the prose can carry real
# apostrophes without fighting the shell.
#
# Run from the repo root:
#   bash database/data/add-zebulon-vance.sh
#
# Afterwards, place the new record in the sort order:
#   php artisan prisoners:place-zero-sort-by-year --apply

set -euo pipefail
cd "$(dirname "$0")/../.."

read -r -d '' VANCE <<'JSON' || true
{
  "name": "Zebulon Vance",
  "first_name": "Zebulon",
  "middle_name": "Baird",
  "last_name": "Vance",
  "description": "Zebulon Baird Vance was Governor of North Carolina during the Civil War and was imprisoned for seven weeks after it ended, without charges and without a trial. A lawyer from Buncombe County who had served in the United States House of Representatives before secession, he opposed leaving the Union until Lincoln's call for troops, then raised a regiment, fought as colonel of the 26th North Carolina at New Bern and Malvern Hill, and was elected governor in 1862 at the age of thirty-two. In office he quarrelled constantly with the Confederate government in Richmond over conscription, habeas corpus and the impressment of supplies, and he ran the state's own blockade-running operation to clothe its troops. On May 13, 1865, his thirty-fifth birthday, General Hugh Judson Kilpatrick arrested him at Statesville. Samuel Wittkowsky, who gave him a wagon ride to the train station, recalled that Vance rode in silence shedding tears, and then, wiping his eyes, spoke of his wife and children, who had no money to live on, and of the indignities he feared North Carolina would suffer in the aftermath of the war. After a short imprisonment at Raleigh he reached the Old Capitol Prison in Washington, D.C. on May 20, sharing a small cell with John Letcher, the former Governor of Virginia; each man had an iron bed and a chair, and they paid for meals sent in from a restaurant nearby. Vance applied for parole on June 3, 1865 under President Johnson's amnesty program, and with his wife gravely ill and Johnson's sympathies running toward reuniting the family, the President paroled him on July 6, 1865. He was never tried for anything. Barred from office by federal law, he returned to the practice of law in Charlotte, was elected to the United States Senate in 1870 but denied his seat, won the governorship again in 1876, and served in the Senate from 1879 until his death in Washington on April 14, 1894.",
  "state": "North Carolina",
  "race": "White",
  "gender": "Male",
  "birthdate": "1830-05-13",
  "death_date": "1894-04-14",
  "era": "1800s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Old Capitol Prison",
      "institution_city": "Washington",
      "institution_state": "District of Columbia",
      "charges": "None filed.",
      "convicted": "No",
      "arrest_date": "1865-05-13",
      "incarceration_date": "1865-05-13",
      "release_date": "1865-07-06",
      "imprisoned_for_days": 54,
      "sentence": "No charges were brought and there was no trial. Arrested at Statesville on May 13, 1865 by General Hugh Judson Kilpatrick, held briefly at Raleigh, and delivered to the Old Capitol Prison in Washington on May 20, where he shared a cell with John Letcher, the former Governor of Virginia. He applied for parole on June 3, 1865 under President Johnson's amnesty proclamation and was paroled on July 6, 1865, after fifty-four days in custody."
    }
  ]
}
JSON

php artisan prisoner:add "$VANCE" || echo "  (skipped -- already exists)"

echo
echo "Done."
