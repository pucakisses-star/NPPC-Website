# Scope audit — records whose own description says the person does not belong

Read-only audit of all 8,570 prisoner descriptions, looking for text that argues
the person is not a political prisoner or should not be listed. **No data was
changed.** Every action below is a recommendation.

---

## The finding

**Four records have no biography at all. Their entire description is a private
note to the editors arguing the person should not be on the list — and that note
is what the public profile displays.**

The profile page renders `description` directly. There is no separate notes
field on the prisoner model, so a note written for the curator is published to
the same place a biography would go.

### 1. `rasmea-odeh` — Rasmea Odeh

> Note to editors: She does not seem to be political in nature. She is merely an
> immigrant (granted, from a politically marginalized group) who got arrested for
> failing to disclose an arrest which is not an inherently political thing. I can
> see that the courts don't want these types of people immigrating into the US for
> reasons other than political

That is the whole record. 348 characters, ending mid-thought. It is the only text
on the public page of a Palestinian woman whose prosecution was a sustained
campaigning cause, and the phrase "these types of people" is published under the
coalition's name. Charges are recorded as "Immigration Offenses False
Statements"; no sentence, no dates, no imprisonment figure.

Worth noting for the decision: the imprisonment in her case is the ten years she
served in Israeli custody from 1969, not the American immigration conviction the
note is about. The note argues against listing her for the wrong custody.

### 2. `kevin-mitnick` — Kevin Mitnick

> Note to editors: Kevin Mitnick does not seem political in nature. He was charged
> and convicted for crimes that had absolutely nothing political about them. He did
> not do what he did for a specific ideology, as far as I could tell.

230 characters, the whole record. No sentence, no dates, no counter.

On the merits this is the most defensible of the four: Mitnick's offences were
not ideological, and the archive's other computer cases — the Anonymous and
LulzSec records — are hacktivism, which his was not. The argument on the other
side is that the "Free Kevin" campaign was a civil-liberties cause about his
pretrial detention rather than about his politics, and this archive does hold
people whose claim rests on how they were held rather than why. That is a
judgment call, not a data error.

### 3. `jorge-rodriguez-mendieta` — Jorge Rodríguez Mendieta

> Note to editors: From what I could find, this doesn't seem to be political. I'm
> not sure if my Ivan Vargas (his other name) is the same as yours, but I'm pretty
> sure this guy had no political affiliations and was sentenced for conspiracy to
> smuggle cocaine into the US

followed by two sentences of actual biography. **The record contradicts its own
note**: its affiliation field says FARC and its ideology field says Communism,
while the note says "no political affiliations". The note also says out loud that
the writer could not confirm the identification.

Two separate questions here, and the note conflates them: whether this is the
right man, and whether a FARC narcotics conviction is a political imprisonment.

### 4. `raphael-joseph` — Raphael Joseph

> Note to editors: This seems not to fit your definition of a political prisoner.
> He murdered eight white people in the Virgin Islands due to white resentment
> (which is political, but the murders could hardly be described as political). He
> did not receive an abnormally long sentence (eight life sentences for murdering
> eight people), and if he did, he was not a political activist or anything; there
> was not anything inherently political about him or his case.

**This one is decided by the archive's own contents.** Raphael Joseph is the
fifth of the Fountain Valley Five, and the other four are all in the database
with full biographies describing them as Virgin Islands independence activists:

| Record | Chars | Counter | How it describes him |
|---|---|---|---|
| `abdul-azeez` (Warren Ballantine) | 2,872 | 19,687 days | "independence activist" |
| `hanif-shabazz-bey` (Beaumont Gereau) | 3,161 | 19,687 days | "independence activist" |
| `ishmail-muslim-ali` | 2,100 | 19,687 days | "independence activist and political asylee in Cuba" |
| `malik-el-amin` (Meral Smith) | 2,289 | 19,687 days | "independence activist" |
| **`raphael-joseph`** | **459** | **8,139 days** | **"not a political activist or anything"** |

One conviction, one trial, five defendants — and the archive treats it as
political for four of them and not for the fifth. Whatever the right answer is,
it cannot differ between co-defendants in the same case. Either Joseph belongs
with the other four or the other four need re-examining; the present state is
the one position that cannot be defended.

His counter is also out of line with theirs — 8,139 days against 19,687 — with no
biography to explain why.

---

## Also found: the Fountain Valley Five occupy seven records

| Record | Appears to be |
|---|---|
| `abdul-azeez` — "Abdul Azeez (formerly Warren Ballantine)", 2,872 chars, 19,687 days | the fuller record |
| `abdul-aziz` — "One of the Virgin Islands 5", 321 chars, 0 days, no photo | the same man |
| `malik-el-amin` — "Malik El-Amin (formerly Meral Smith)", 2,289 chars, 19,687 days | the fuller record |
| `malik-smith` — "Malik Smith... one of the group", 306 chars, 0 days | the same man |

Both duplicates carry a stub biography and a zero counter, so on the public site
each of these two men appears twice, once substantially and once emptily. Same
shape as the `steve-bratich` / `steve-bradich` pair merged in batch 143.

---

## What the audit did not find

No record says "included in error", "should be removed", "out of scope", or
"candidate for removal". No placeholder or test entries. The four notes above are
the whole of the self-disqualifying material.

Thirty-four other records matched the search patterns and are **false positives**,
recorded here so a later audit does not re-raise them:

- **"No political activity" in the Red Scare deportee records** (`leo-haskevich`,
  `vito-mariani`, `adolfo-lorenzini`, `angelo-varricchio`, `salvatore-zumpano`,
  `salvatore-sergi`, `pietro-bianchi`) — these all describe surveillance findings
  *after* deportation or *before* emigration, from Kenyon Zimmer's INS case-file
  research. They are evidence about the rest of the person's life, not about the
  imprisonment.
- **`mohammed-rafiq-butt`** — "He had engaged in no political activity" is the
  point of the entry: he died in immigration detention after September 11 having
  done nothing at all. The absence of politics is the argument for inclusion.
- **`jose-padilla`** ("without ordinary criminal charge"), **`benjamin-weiss`**
  ("criminal conspiracy rather than political association"),
  **`juan-segarra-palmer`** ("political conflict or ordinary criminal conduct") —
  all describing the government's framing, not the archive's.
- **`sundiata-jawanza`** — "originally incarcerated for ordinary criminal charges
  who has become a political prisoner through subsequent organizing inside" is the
  Jericho Movement's *politicized prisoner* category, stated deliberately. Not an
  error, though it is worth deciding whether the archive adopts that category
  explicitly, since it is doing so implicitly here.
- **`mary-anne-grady-flores`** — "domestic violence" describes the Order of
  Protection used against her, which is the notable fact of the case.
- **`konstantin-loban`** — "does not belong to Americans" is inside a quotation
  from his deportation hearing.

## Limits of this audit

This searched for explicit statements. A biography that quietly describes an
ordinary crime without ever saying so would not be caught, and 8,570 free-text
biographies cannot be checked for that by pattern — it needs reading. What this
audit can say is that the archive contains exactly four records that argue
against themselves in writing, and that all four are published.

---

## Recommendations, separated by who decides

**Mechanical, and safe to do now.** Set `under_review = true` on the four
records. `NotUnderReviewScope` already hides such records from every public
query while leaving them fully visible in the admin, so this stops the notes
being published without deleting anybody or settling the classification. It is a
single reversible flag.

**Curatorial, and not mine to make.**

1. Whether each of the four belongs in the archive at all.
2. `raphael-joseph` specifically: he cannot stay as he is. Either he gets a
   biography consistent with his four co-defendants, or the Fountain Valley Five
   are reconsidered as a group.
3. Whether "politicized prisoner" — imprisoned for something else, became
   political inside — is a category the archive accepts. It currently holds at
   least one such record on that basis.
4. Merging `abdul-aziz` into `abdul-azeez` and `malik-smith` into `malik-el-amin`,
   which needs someone to confirm the identifications.

**Structural, worth considering.** There is nowhere to write a note to a curator
except the public biography. An `internal_notes` column on `prisoners`, shown in
Filament and never rendered by the profile page or the API, would have prevented
all four of these from reaching the site.
