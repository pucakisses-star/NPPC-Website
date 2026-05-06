#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_moyer_pettibone.sh
#
# Adds Charles H. Moyer and George A. Pettibone as new prisoner
# records. They are Bill Haywoods two co-defendants in the 1906-1907
# Steunenberg assassination case.
#
# History: On December 30, 1905, former Idaho Governor Frank
# Steunenberg was killed by a bomb at his Caldwell, Idaho home.
# Pinkerton detective James McParland obtained a confession from Harry
# Orchard naming Western Federation of Miners leaders Bill Haywood
# (secretary-treasurer), Charles Moyer (president), and George
# Pettibone (former WFM executive board member) as having ordered the
# assassination. On February 17, 1906, Pinkerton agents seized
# Haywood, Moyer, and Pettibone in Denver in violation of normal
# extradition procedures and put them on a special train to Boise,
# where all three were held without bail in the Ada County Jail.
#
# After Haywood was acquitted on July 29, 1907, Pettibone was tried
# separately in late 1907 and acquitted by jury on January 4, 1908.
# Charges against Moyer were then dismissed without trial in March
# 1908. All three were defended by Clarence Darrow.
#
# Source: Western Federation of Miners; Bill Haywood, Wikipedia;
# Library of Congress Chronicling America Haywood Trial guide; The
# Bill Haywood Trial - Clarence Darrow Digital Collection (University
# of Minnesota Law School).
set -e

php artisan prisoner:add '{
  "name": "Charles Moyer",
  "first_name": "Charles",
  "middle_name": "Hartwig",
  "last_name": "Moyer",
  "description": "President of the Western Federation of Miners (WFM) from 1902 to 1926, a militant industrial union representing hard-rock miners in the American West. On February 17, 1906, Pinkerton agents seized Moyer along with WFM secretary-treasurer Bill Haywood and former WFM executive board member George Pettibone in Denver and rendered them to Idaho without normal extradition proceedings, on charges of conspiring to murder former Idaho Governor Frank Steunenberg. The prosecution case rested entirely on a confession by Harry Orchard, obtained by Pinkerton detective James McParland. After Haywood was acquitted in July 1907 and Pettibone in January 1908, charges against Moyer were dismissed without trial in March 1908. He had been held without bail in the Ada County Jail in Boise for over two years.",
  "state": "Idaho",
  "race": "White",
  "gender": "Male",
  "birthdate": "1866-07-04",
  "death_date": "1929-06-02",
  "ideologies": ["Labor", "Socialism", "Industrial Unionism"],
  "affiliation": ["Western Federation of Miners"],
  "era": "Early Labor Movement",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Ada County Jail",
      "institution_city": "Boise",
      "institution_state": "Idaho",
      "charges": "Idaho state conspiracy to commit murder of former Idaho Governor Frank Steunenberg, killed by a bomb at his Caldwell home December 30, 1905. Prosecution case rested on the confession of Harry Orchard, obtained by Pinkerton detective James McParland, naming Moyer (WFM president), Bill Haywood, and George Pettibone of the Western Federation of Miners as having ordered the assassination. Pinkerton agents seized all three in Denver on February 17, 1906 and rendered them to Idaho without normal extradition proceedings.",
      "arrest_date": "1906-02-17",
      "incarceration_date": "1906-02-17",
      "release_date": "1908-03-10",
      "convicted": "No - charges dismissed without trial, Ada County District Court, Boise, March 1908, after Haywood acquittal (July 29, 1907) and Pettibone acquittal (January 4, 1908).",
      "judge": "Fremont Wood",
      "prosecutor": "James Hawley and Senator William Borah",
      "sentence": "Held without bail in the Ada County Jail, Boise, Idaho from February 17, 1906 until charges were dismissed in March 1908 (over two years pretrial detention without conviction). Defense counsel: Clarence Darrow."
    }
  ]
}' || true

php artisan prisoner:add '{
  "name": "George Pettibone",
  "first_name": "George",
  "middle_name": "A.",
  "last_name": "Pettibone",
  "description": "Denver storekeeper and former Western Federation of Miners executive board member, blacklisted from the mines after the 1892 Coeur dAlene strike. On February 17, 1906, Pinkerton agents seized Pettibone along with WFM president Charles Moyer and WFM secretary-treasurer Bill Haywood in Denver and rendered them to Idaho without normal extradition proceedings, on charges of conspiring to murder former Idaho Governor Frank Steunenberg. The prosecution case rested entirely on a confession by Harry Orchard, obtained by Pinkerton detective James McParland. Pettibone was tried separately beginning in late 1907 and acquitted by an Ada County jury on January 4, 1908, after nearly two years of pretrial detention without bail. He died of cancer seven months after his acquittal.",
  "state": "Idaho",
  "race": "White",
  "gender": "Male",
  "death_date": "1908-08-04",
  "ideologies": ["Labor", "Socialism", "Industrial Unionism"],
  "affiliation": ["Western Federation of Miners"],
  "era": "Early Labor Movement",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Ada County Jail",
      "institution_city": "Boise",
      "institution_state": "Idaho",
      "charges": "Idaho state conspiracy to commit murder of former Idaho Governor Frank Steunenberg, killed by a bomb at his Caldwell home December 30, 1905. Prosecution case rested on the confession of Harry Orchard, obtained by Pinkerton detective James McParland, naming Pettibone, WFM president Charles Moyer, and WFM secretary-treasurer Bill Haywood as having ordered the assassination. Pinkerton agents seized all three in Denver on February 17, 1906 and rendered them to Idaho without normal extradition proceedings.",
      "arrest_date": "1906-02-17",
      "incarceration_date": "1906-02-17",
      "release_date": "1908-01-04",
      "convicted": "No - acquitted by jury, Ada County District Court, Boise, January 4, 1908.",
      "judge": "Fremont Wood",
      "prosecutor": "James Hawley and Senator William Borah",
      "sentence": "Held without bail in the Ada County Jail, Boise, Idaho from February 17, 1906 to January 4, 1908 (687 days pretrial). Tried separately after Haywood acquittal; defense by Clarence Darrow and Edmund Richardson. Jury acquitted on January 4, 1908. Pettibone died of cancer seven months later on August 4, 1908."
    }
  ]
}' || true

echo
echo "Moyer and Pettibone add complete."
