#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_steve_adams.sh
#
# Adds Steve Adams (Western Federation of Miners), the would-be corroborating
# witness in the 1905 assassination of former Idaho governor Frank Steunenberg,
# whose recantation of the McParland-coerced confession decisively undermined
# the prosecution's case against Bill Haywood, Charles Moyer, and George
# Pettibone. Held in pretrial custody for ~4 years across three trials,
# never convicted.
#
# Sources / dates:
#   - February 1906 arrest in Oregon, extradited to Idaho; held at Caldwell
#     and Wallace jails
#   - Confession to Pinkerton Det. James McParland, recanted via secret note
#     passed by his wife Annie during a jail visit
#   - 1st trial (Wallace ID, Fred Tyler murder) - November 1907 hung jury
#   - 2nd trial (Rathdrum ID, retrial) - July 1908 hung jury (8-4 acquit)
#   - 3rd trial (Colorado, Arthur Collins / Telluride) - early 1910;
#     prosecution declined to retry; Adams discharged
#   - Library of Congress Chronicling America newspaper archive corroborates
#     the November 1907, July 1908, and February 1910 wire-service spikes
set -e

php artisan prisoner:add '{"name":"Steve Adams","first_name":"Steve","last_name":"Adams","description":"Steve Adams was a Western Federation of Miners member who in February 1906 was arrested by the Pinkerton National Detective Agency in eastern Oregon and extradited to Idaho as the prosecutions intended corroborating witness in the case against WFM leaders Bill Haywood, Charles Moyer, and George Pettibone for the December 30, 1905 assassination by mail-bomb of former Idaho governor Frank Steunenberg.\n\nThe principal informant in the case, Harry Orchard (Albert Horsley), had confessed to the bombing under interrogation by Pinkerton detective James McParland and named Adams as a fellow WFM hitman responsible for several earlier western mining-strike killings. McParland obtained a parallel confession from Adams at the Caldwell, Idaho jail by threatening him with hanging. Adams recanted that confession via a note passed in secret by his wife Annie during a jail visit; the note read: This is to certify that the statement that I signed was made up by James McParland, detective, and Harry Orchard, alias Tom Hogan. I signed it because I was threatened by Governor Gooding, saying I would be hanged if I did not corroborate Orchards story against the officers of the federation union of miners.\n\nWithout Adamss corroborating testimony, the prosecutions case against Haywood (defended by Clarence Darrow) collapsed; Haywood was acquitted on July 28, 1907. Pettibone was acquitted in early 1908; Moyer was released without trial. Adams himself remained in pretrial custody and was tried three times for murders Orchard had alleged on his behalf:\n\n- November 1907 in Wallace, Idaho for the murder of Fred Tyler — hung jury\n- July 1908 in Rathdrum, Idaho on retrial — hung jury, 8-4 for acquittal\n- early 1910 in Colorado on the Arthur Collins / Telluride charge — discharged after the prosecution declined to retry\n\nHe spent approximately four years in pretrial detention across the Caldwell, Wallace, and Rathdrum, Idaho jails without ever being convicted. He returned to mining-region life after release.","state":"Idaho","race":"White","gender":"Male","ideologies":["Labor","Western Federation of Miners"],"affiliation":["Western Federation of Miners"],"era":"1900s","in_custody":false,"released":true,"cases":[{"institution_name":"Idaho county jails (Caldwell, Wallace, Rathdrum)","institution_state":"Idaho","charges":"Multiple murder charges levied on the basis of Harry Orchards McParland-obtained confession naming Adams as a fellow Western Federation of Miners hitman: Idaho murder of Fred Tyler, and Colorado murder of Telluride mine manager Arthur Collins. Adams recanted his own confession; tried three times, never convicted","arrest_date":"1906-02-20","incarceration_date":"1906-02-20","release_date":"1910-02-28","sentence":"~4 years pretrial detention; tried three times (Wallace ID Nov 1907 hung jury; Rathdrum ID Jul 1908 hung jury; Colorado early 1910 discharged); never convicted","convicted":"No - three trials, two hung juries followed by a discharge"}]}'

echo
echo "Steve Adams add complete."
