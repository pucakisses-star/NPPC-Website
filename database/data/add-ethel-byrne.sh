#!/usr/bin/env bash
#
# Add Ethel Byrne.
#
# She was not in the database at all. Her sister Margaret Sanger is (and
# their brother-in-law William Sanger was added earlier), but the nurse who
# ran the Brownsville clinic examinations, went on hunger strike in the
# Blackwell's Island Workhouse and was force-fed until the governor
# pardoned her was missing.
#
# Two cases, because there were two separate periods of custody:
#
#   PRETRIAL   Oct 26, 1916  arrested at the Brownsville clinic with
#                            Margaret Sanger and Fania Mindell; held
#                            overnight at the Liberty Avenue police
#                            station in Brooklyn (Sanger and Mindell went
#                            to Raymond Street Jail)
#              Oct 27, 1916  released on $500 bail
#                            = 1 day
#
#   SENTENCE   Jan 22, 1917  sentenced to 30 days and taken into custody
#                            the same day; hunger strike begins
#              Feb  1, 1917  pardoned by Governor Charles S. Whitman at
#                            about 7 p.m. and removed from the island at
#                            about 10:30 p.m.
#                            = 10 days
#
#   Total documented custody: 11 days.
#
# ONE CLINIC ARREST, NOT TWO. The November 14, 1916 reopening produced a
# second arrest of Margaret Sanger, but no surviving account puts Byrne
# among those arrested that day, so only the October arrest is recorded.
#
# THE RELEASE DATE IS FEBRUARY 1, not February 2. Secondary sources give
# the 2nd because the pardon was reported in February 2 papers and her
# medical care ran into that morning, but Dorothy Day's contemporaneous
# report in the New York Call puts her physical removal from the island at
# about 10:30 p.m. on the 1st. The case text records the conflict.
#
# THE 185 HOURS FIGURE IS QUALIFIED, NOT ASSERTED. Contemporary reporting
# puts the first forced feeding shortly before midnight on January 26,
# after roughly 106 hours without food, and about twelve feedings by
# February 1. The widely repeated 185-hour figure probably describes the
# whole attempted fast including the period of forced feeding, so the bio
# gives both numbers and says which is which rather than printing the
# larger one as fact.
#
# LIFE DATES are year precision: born 1883 in Corning, New York, died 1955
# in Massachusetts. No source gives a day or month, so none is invented.
#
# The conviction date of January 8, 1917 is recorded as reconstructed --
# the verdict was reported on January 9.
#
# The payload is built with a QUOTED heredoc rather than a single-quoted
# argument so that it can contain real apostrophes: the workhouse is
# already in the institutions table as "Workhouse, Blackwell's Island"
# with a straight apostrophe, and spelling it any other way would create a
# duplicate institution instead of matching the existing row.
#
# The command refuses duplicates by name. Run from the repo root:
#   bash database/data/add-ethel-byrne.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

read -r -d '' PAYLOAD <<'JSON' || true
{
  "name": "Ethel Byrne",
  "first_name": "Ethel",
  "last_name": "Byrne",
  "aka": "Ethel Higgins",
  "description": "Ethel Byrne was a registered nurse and one of the three principal operators of the Brownsville birth-control clinic, the first in the United States, which opened at 46 Amboy Street in Brooklyn on October 16, 1916. Byrne, a trained nurse and the younger sister of Margaret Sanger, instructed women in contraceptive methods and supplied or demonstrated contraceptive articles. Police arrested her with Sanger and Fania Mindell on October 26, 1916 under New York Penal Law §1142, which forbade selling, giving away, possessing for distribution, or giving information about articles intended to prevent conception. Byrne spent the night at the Liberty Avenue police station in Brooklyn — Sanger and Mindell were held separately at Raymond Street Jail — and was released the next morning on $500 bail. Her trial opened in the Court of Special Sessions on January 4, 1917; the guilty verdict came on January 8 and was reported the following day, and on January 22 she was sentenced to thirty days in the Workhouse on Blackwell's Island. She entered custody the same day and announced that she would refuse both food and prison labour. A federal habeas corpus application brought her back from the island on January 23 before Judge Augustus N. Hand denied it, and she was placed in Cell 139, near the workhouse hospital, on January 24. Contemporary reporting says prison physicians began force-feeding her shortly before midnight on January 26, after roughly 106 hours without food, and that she had been fed by force about twelve times by February 1; the widely repeated figure of 185 unbroken hours without food or water is best understood as the length of her attempted fast overall, including the days she was being fed forcibly. Governor Charles S. Whitman pardoned her at about 7 p.m. on February 1, 1917, on the understanding that she would not break the law again — an assurance Margaret Sanger gave on her behalf, believing her sister close to death — and she was carried off Blackwell's Island at about 10:30 that night, having served about ten days of the thirty. Her documented custody in the case came to roughly eleven days, counting the night after her arrest. She was born in Corning, New York in 1883 and died in Massachusetts in 1955.",
  "state": "New York",
  "race": "White",
  "gender": "Female",
  "ideologies": ["Reproductive Rights"],
  "era": "1910s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Liberty Avenue Police Station",
      "institution_city": "Brooklyn",
      "institution_state": "New York",
      "charges": "Violation of New York Penal Code §1142 — as a registered nurse at the Brownsville clinic at 46 Amboy Street, instructing women in contraceptive methods and supplying or demonstrating contraceptive articles. Arrested with Margaret Sanger and Fania Mindell in the raid of October 26, 1916.",
      "arrest_date": "1916-10-26",
      "incarceration_date": "1916-10-26",
      "release_date": "1916-10-27",
      "sentence": "Pretrial detention. Byrne was held overnight at the Liberty Avenue police station in Brooklyn — Sanger and Mindell were taken to Raymond Street Jail — and released the next morning on $500 bail. The clinic reopened on November 14, 1916 and Margaret Sanger was arrested again that day, but no surviving account places Byrne among those arrested, so this is her one documented clinic arrest."
    },
    {
      "institution_name": "Workhouse, Blackwell's Island",
      "institution_city": "New York",
      "institution_state": "New York",
      "charges": "Violation of New York Penal Code §1142 — distribution of a contraceptive article and related contraceptive instruction at the Brownsville clinic. Tried in the New York City Court of Special Sessions.",
      "convicted": "Yes — the guilty verdict came on January 8, 1917 (the day is reconstructed from the January 9 press report). The trial had opened on January 4 and sentencing was postponed to January 22.",
      "sentenced_date": "1917-01-22",
      "incarceration_date": "1917-01-22",
      "release_date": "1917-02-01",
      "sentence": "Thirty days in the Workhouse on Blackwell's Island, imposed January 22, 1917, of which she served about ten. She entered custody the same day and refused food and prison labour. A federal habeas corpus application brought her back to court on January 23; Judge Augustus N. Hand denied it, and on January 24 she was placed in Cell 139 near the workhouse hospital. Forced feeding began shortly before midnight on January 26, after roughly 106 hours without food, and was repeated about twelve times by February 1. Governor Charles S. Whitman pardoned her at about 7 p.m. on February 1, 1917, conditional on her not repeating the offence, and she was removed from the island at about 10:30 that night. The sentence ended by pardon — not by completion and not by reversal of the conviction. Some sources date the release February 2 because the pardon was reported in that morning's papers and her care continued into the early hours; Dorothy Day's contemporaneous report in the New York Call puts the removal at 10:30 p.m. on February 1, which is the date used here."
    }
  ]
}
JSON

php artisan prisoner:add "$PAYLOAD"

# Life dates are set separately: prisoner:add writes the column straight
# through, which cannot record that only the year is known. setPartialDate
# stores the year-precision flag so the profile prints "1883" rather than a
# defaulted January 1.
php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "ethel-byrne")->first();
if (! $p) {
    echo "NOT FOUND: ethel-byrne -- did prisoner:add succeed?\n";
    exit(1);
}
$p->setPartialDate("birthdate", 1883);
$p->setPartialDate("death_date", 1955);
$p->save();

$p->refresh()->load("cases");
echo "Ethel Byrne  [{$p->slug}]\n";
echo "  born ".($p->formatPartialDate("birthdate") ?: "-")."   died ".($p->formatPartialDate("death_date") ?: "-")."   age ".($p->age ?? "-")."  (expect 72)\n";
$total = 0;
foreach ($p->cases as $c) {
    $total += (int) $c->imprisoned_for_days;
    echo "  inc=".$c->incarceration_date->toDateString()."  rel=".$c->release_date->toDateString()
        ."  days=".($c->imprisoned_for_days ?? "null")."\n";
}
echo "  TOTAL days = {$total}  (expect 11: 1 + 10)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
