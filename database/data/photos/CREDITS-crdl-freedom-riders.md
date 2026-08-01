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
- **NOT attached — identification failed**: the dossier's record ids
  for **Henry Schwarzschild** (…5-53) and **Woollcott Smith** (…6-85)
  were sequence inferences, and the images at those ids show other
  people (both women; Schwarzschild and Smith were men). Their crops
  are excluded; their records are pre-listed for drop-in completion
  once the correct records are identified.

The full slug→record-id table is in
`database/data/fixes/crdl-freedom-riders.json` (the `photo_ids` key).
