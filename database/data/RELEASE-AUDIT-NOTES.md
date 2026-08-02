# Release audit — curator notes (batch 106, August 2, 2026)

All 188 in-custody records audited: the 37 with BOP register numbers
checked directly against the federal inmate locator, the rest through
twelve research passes over state DOC locators, court records, news,
and support-committee announcements, with the two-fact identity rule
throughout. Outcome: **29 released, 2 died in custody, 152 confirmed
or presumed still in, 5 unresolved.** The releases and deaths apply in
`run-batch-106.sh`; everything below is flagged, not changed.

## Unresolved — could not confirm either way (left in custody)

- **erich-louis-yach** — CDCR since 11/29/2022 on 56 months;
  parole-eligible 2024, likely paroled, but the CDCR locator was
  unreachable and no announcement exists. Full term ~mid-2027.
- **jennifer-rose** — a 2021 pen-pal ad listed projected release
  7/2025; her support committee has posted no release news and CDCR
  could not be checked.
- **christian-frazee** — 10-year DuPage County terrorism-conspiracy
  sentence from June 2020 custody; day-for-day math suggests parole
  around mid-2025 but the IDOC lookups were unusable. (Note: his
  sentence also included a concurrent child-pornography count the
  record does not mention — curator judgment.)
- **david-webb** — arraigned June 10, 2026 on misdemeanors; nothing
  affirms detention or release.
- **tony-alexander-hamilton** — Utah 5-to-life from 2000; he is ~85
  now, the parole window passed years ago, and neither the
  CAPTCHA-gated Utah locator nor any news source says anything.

## Duplicate records (merge candidates — not touched)

- `jacob-hoopes` + `robert-hoopes` — both are Robert Jacob Hoopes
  (BOP 98782-511, FCI Sheridan, projected release 9/5/2028).
- `daniel-sanchez-estrada` + `daniel-rolando-sanchez-estrada` — both
  are the Prairieland 30-year defendant (95099-511, Victorville II).
- `ricardo-palmera` + `juvenal-ovidio-ricardo-palmera-pineda` — both
  are Simón Trinidad (27896-016, Florence ADMAX).

## Wrong case data found along the way (not changed, except Coleman)

- **carlos-coleman** — the record said he pleaded guilty; he was
  acquitted (Rule 29, 11/13/2012). FIXED in batch 106 alongside his
  status change.
- **alexander-stokes-contompasis** — case labeled "Draft Evasion";
  the conviction is assault/weapons (Albany Capitol stabbing).
- **fran-thompson** — same "Draft Evasion" mislabel; her conviction
  is first-degree murder.
- **kenneth-whitmore** — a note says "paroled 2016"; he was never
  paroled — he left ~37 years of solitary into general population.
- **eric-brandt** — sentence recorded as 48 months; the Colorado
  sentence was 12 years (three consecutive 4-year terms), and he now
  also faces a federal E.D. La. threats indictment.
- **jeffrey-weinhaus** — inmate number field holds a case number;
  MODOC is 1261778.
- **ines-soto** — sentenced July 1, 2026, not June 23 as the bio says.

## Location/facility drift (records say one place, person is in another)

- Fountain Valley trio (**abdul-azeez**, **hanif-shabazz-bey**,
  **malik-el-amin**): State=Florida, actually held at John A. Bell
  ACF, St. Croix, USVI (confirmed 7/31/2026).
- **fergie-chambers**: State=Massachusetts, actually in Spanish
  custody pending extradition.
- **william-lonergan-hill**: State empty; he is at FCI Otisville, NY.
- **malik-fard-muhammad**: interstate-transferred to Kirkland CI,
  South Carolina (March 2026).
- **kevin-rashid-johnson**: back in Virginia DOC custody (6/24/2026).
- **julio-zuniga**: now at TDCJ Connally Unit.
- **andrew-mickel**: "San Quentin Death Row" may be stale given
  death-row dispersal; still incarcerated regardless.

## Re-check soon

- **fornandous-henderson** — BOP projected release **September 20,
  2026** (~7 weeks out).
- **nathan-baumann** — 22-month Prairieland sentence projects release
  around spring 2027.
- **abdul-olugbala-shakur** — parole hearing was held July 16, 2026;
  outcome not yet public.
- **sean-swain** — parole hearing September 2026.
- **ronald-reed** — clemency-commission hearing August 7, 2026.
- **aubrey-cottle** — Ontario sentence runs out around December 2026.
- **pascale-cecile-veronique-ferrier** — absent from the BOP locator
  under every name variant despite a 262-month sentence; possibly a
  treaty transfer to Canada or France, or a records quirk. Needs a
  manual check before anything changes.
- **roberto-rivera** — NJ DOC locator is bot-gated; parole
  eligibility is approximately now.
- **john-wade** — released federally 6/24/2024, rearrested at a
  supervision check-in, now in Georgia state custody (GDC 1003510744)
  with the Fulton County arson case pending; his case rows could
  carry that chronology.
