# CRDL mining cohort — photo provenance (batch 61)

All files in `database/data/photos/crdl/` are Mississippi State
Sovereignty Commission arrest identification photographs (Jackson
Police Department mugshot pairs, or the East Baton Rouge sheriff's
photograph for Diamond's second arrest), served by the Mississippi
Department of Archives and History (da.mdah.ms.gov, sovcom series) and
individually catalogued by the Civil Rights Digital Library
(crdl.usg.edu). **Each image's CRDL catalog title names the person**
— the identification anchor — and each frontal-panel crop keeps the
Jackson PD placard with its booking number and date, which matches the
dossier's arrest date in every attached case. Scans are 300px at
source (the largest size MDAH serves openly), cropped to the frontal
panel, padded to 3:4 with the scan margin where needed, 525×700,
autocontrast.

Notable entries:

- **dion-diamond.jpg** (placard 20897, **5-24-61**) and the John Lewis
  record (placard 20886, **5-24-61**): the placards read May 24 where
  the dossier said May 25 — the placard dates are entered.
- **dion-diamond-batonrouge.jpg** — his second custody episode
  (Sheriff's Office, Baton Rouge, 2-1-62, no. 55652); stored as an
  alternate, since a record carries one portrait.
- **felix-singer.jpg** (20938, 6-2-61) and **wyatt-t-walker.jpg**
  (21049, 6-21-61) attach to existing records that had no photo.
- **john-lewis** — his mugshot (20886) was verified but NOT copied or
  attached; his record already carries a portrait.
- **Two dossier ids corrected by title verification**: the dossier's
  sequence-inferred records for **Henry Schwarzschild** (…5-53) and
  **Woollcott Smith** (…6-85) actually catalog **Theresa E. Walker**
  (Wyatt Tee Walker's fellow June 21 arrestee) and **Catherine Jo
  Prensky** (a July 29 rider). The correct records were located by
  scanning the catalog directly: Schwarzschild = …5-55 (placard
  21041, 6-21-61), Woollcott Smith = …6-89 (placard 21255, 7-29-61).
  Both now attach.
- **Nine mugshots found by direct catalog hunt** for people the
  dossier listed without URLs: Jerome H. Smith (…2-88), Claire
  O'Connor (…4-55), David Kerr Morton (…4-57), John Charles Taylor
  Jr. (…5-109), Kredelle Petway (…6-39), Leo Vernon Washington
  (…6-15), Francis L. Geddes (…6-60), plus the two corrections above.
- **Pursued, not obtained**: C. T. Vivian, Jean Catherine Thompson,
  Glenda Jean Gaither, Raymond B. Randolph Jr., Edward William Kale,
  Peter Harry Stoner, and Janice Louise Rogers were not found in the
  scanned Sovereignty Commission ranges; their records enter without
  photos, slug-named files will attach on a re-run. The non-sovcom
  candidates (Braden, Roodenko, the Americus pair, Hairston, Charles
  Jones) were not pursued in this pass.
- **First-wave dates**: the catalog titles date the May 24/25 group
  (Lewis, Diamond, Castle, Jerome Smith) uniformly "May 25"; the
  Lewis and Diamond placards read 5-24-61, matching the standard
  history of the first Jackson arrests, and the placard dates are
  entered for those two.

The catalog scan also surfaced dozens of riders not yet in the
curator's ledger (Stokely Carmichael …3-83, James L. Farmer …2-86,
Joan Trumpauer …3-85, Hezekiah Watkins …5-76, Theresa E. Walker
…5-53, Catherine Jo Prensky …6-85, Matthew Petway …6-38, Russell F.
Jorgensen …6-68, and many more) — ready targets for the next mining
segment.

The full slug→record-id table is in
`database/data/fixes/crdl-freedom-riders.json` (the `photo_ids` key).
