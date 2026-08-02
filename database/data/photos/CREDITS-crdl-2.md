# CRDL mining cohort, second wave — photo provenance (batch 103)

All files in `database/data/photos/crdl2/` are Mississippi State
Sovereignty Commission arrest identification photographs (Jackson
Police Department mugshot pairs), served by the Mississippi Department
of Archives and History (da.mdah.ms.gov, sovcom series) and
individually catalogued by the Civil Rights Digital Library
(crdl.usg.edu). **Each image's CRDL catalog title names the person** —
the identification anchor — and each frontal-panel crop keeps the
Jackson PD placard with its booking number and date.

Unlike the first wave (batch 61), which worked from the 300px scans,
these crops come from MDAH's **large scan series (~800px)** — roughly
twice the source resolution — cropped to the frontal panel, padded to
3:4 where needed, 525×700, autocontrast.

Verification: every direct CRDL record id in this batch was
**re-fetched from the live catalog** and its title checked against the
person's name before the image was downloaded. The catalog's own
misspellings ([sic] forms such as Frankhauser, Gwendalyn, Edmon,
Mitaritenna, Huddleson, Trumpower, Earnest, Hirshfeld, Maztkin,
Svanoe, Eldredge, Ester, Janis, Doland, Leavarn-Dee, Sedgewick,
Joesph) are preserved as AKAs on the records, never as primary forms.
Two records carry a documented internal CRDL date conflict, entered at
the value the ledger and title evidence best supports and flagged in
the biography: Charles Biggers (bio July 7 vs photo July 8) and Rick
Stanley Ogilvie Sheviakov (title July 21 vs date field July 29).

The full slug→record-id table is in
`database/data/fixes/crdl-freedom-riders-2.json` (the `photo_ids`
key). Slugs listed there without a file in `photos/crdl2/` had no
retrievable or verifiable image in this pass and will attach on a
re-run when a slug-named file is dropped in.

## Third wave (batch 105, `photos/crdl3/`)

Same provenance and method as above (MDAH large scans, frontal panels
with placards, 525×700, all placards read against ledger dates on a
contact sheet): the June 20, 1961 Illinois Central group (Carter
21027, Martinson 21037, Wagoner 21039, Peterson 21035, Kerr 21031,
McConnell 21038, Rev. Muse 21033), Harrison 21058 (6-23), Hamilton
21068, Libson 21082, Mason 21072 (6-25), and Miriam Feingold 21048
(6-21), whose mugshot fills the empty slot on her existing record.
The Wahlstrom scans at …4-4 and …6-112 both carry placard 20953,
6-6-61 — MDAH serves the June 6 image for both of his records; his
July 31 arrest is documented in the …6-112 catalog data, and no
distinct July 31 photograph is retrievable.
