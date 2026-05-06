#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_briana_waters_release.sh
#
# Corrects Briana Waterss release_date. The previous value
# (2014-03-31) came from an early data-entry approximation and is
# wrong. Per the U.S. Attorneys press release for the Western
# District of Washington dated June 22, 2012:
#
#   "Waters was sentenced to 48 months, meaning she will return to
#    prison for 11 months because of the approximately 37 months
#    she has already served."
#
# That puts her release date approximately mid-May 2013 (about
# 11 months after the June 22, 2012 resentencing, less standard
# federal good-time credit). She had been free on appeal bond
# from the October 13, 2010 Ninth Circuit reversal until the
# resentencing, so the actual time-in-custody is the sum of two
# windows, not a single continuous span:
#   - 2008-03-06 to ~2010-10-13 (original sentence, ~37 months)
#   - ~2012-06-22 to ~2013-05-22 (post-plea remainder, ~11 months)
#
# This script sets release_date to 2013-05-22 (approximate) and
# updates the sentence narrative to reflect the discontinuous
# custody and the 9th Circuit reversal.
#
# Sources:
# - https://www.justice.gov/archive/usao/waw/press/2012/June/waters.html
# - https://www.seattletimes.com/seattle-news/appellate-court-overturns-conviction-in-2001-uw-arson-1/
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Briana Waters")->first();
if (!$p) {
    echo "ERROR: Briana Waters not found\n";
    exit(1);
}

$case = $p->cases()->orderBy("created_at")->first();
if (!$case) {
    echo "ERROR: Briana Waters has no case row\n";
    exit(1);
}

$case->release_date   = "2013-05-22";
$case->sentenced_date = "2012-06-22"; // resentencing date is the canonical one
$case->sentence       = "Originally sentenced March 6, 2008 to 6 years federal prison; began serving immediately. The Ninth Circuit reversed her conviction on October 13, 2010 (improper admission of anarchist-literature evidence), and she was released on appeal bond after about 37 months in custody. She entered a new guilty plea on retrial and was resentenced June 22, 2012 to a total of 48 months, returning to federal prison for the remaining ~11 months and being released approximately May 22, 2013.";
$case->convicted      = "Yes - 2008 federal jury verdict (reversed October 13, 2010 by the Ninth Circuit); 2012 guilty plea on retrial";
$case->save();

echo "Updated case id={$case->id}: release_date=2013-05-22, imprisoned_for_days={$case->imprisoned_for_days}\n";
'

echo
echo "Briana Waters release-date correction complete."
