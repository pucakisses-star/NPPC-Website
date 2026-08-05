# Duration sweep — custody described in prose, counted as zero

Every case row in the archive, swept for the shape that batch 168 found on
Mark Rudd: **a source records how long somebody was held but not when.** The
row carries no usable pair of dates, `computeImprisonedForDays()` returns
null, and a documented imprisonment publishes nothing. The prose above the
figure describes it in full.

This shape is invisible to every earlier audit because the row does not look
wrong. It looks correctly cautious. Nothing is fabricated, nothing is
contradictory — the custody is simply absent from the number.

Swept against the cached API dump of **August 3, 2026** (8,570 records, 9,017
case rows). Batches 124–169 are queued and undeployed, so the stored counters
quoted below are pre-batch values.

---

## Method, and the two ways it was wrong first

The matcher has to separate **time served** from **sentence imposed**. "Five
years in prison" is what a judge ordered; "he spent five years in prison" is
what happened. Only the second is a duration this archive failed to count, and
the first outnumbers it heavily.

**First pass: 515 hits, mostly garbage.** A rule matching `N years in prison`
swept up every sentence description in the archive. Rewritten to demand an
explicit served-time verb (`spent`, `held for`, `released after`, `jailed
for`…) within a few words of the number, to discard anything sitting near
sentencing language, and to reject indeterminate ranges like "30 to 90 days".
That took it to 58.

**Second pass: a bug in the sweep, not the data.** The API emits partial dates
truncated to their precision — a year-precision release is the bare string
`1892`, a month-precision one is `1951-04`. The date parser expected
`YYYY-MM-DD` and treated both as *missing*, so rows that already had a
perfectly good year- or month-precision release date were reported as having
none.

That error was not academic. It put **15 records into the fix list that needed
no fix at all**, including a proposed group of five "missing" release dates —
Trumbo, Cole, Bessie, Saxe, Power — every one of which was already entered and
already counting correctly (Trumbo 296 days, Saxe 2,472). Writing that batch
would have overwritten correct data with the same values at best, and set
`imprisoned_for_months` over a working date pair at worst.

Final counts after both fixes: **41 class A, 14 class B.**

---

## Class A — the duration is stated, the dates cannot produce it

41 rows. The information loss here is large and one-directional: these
profiles publish **zero**.

| record | stated | published |
|---|---|---|
| marshall-conway | about 44 years | 0 |
| mujahid-farid | 33 years | 0 |
| eddie-ellis | 23 years | 0 |
| rubin-carter | roughly 19 years | 0 |
| george-merritt-jr | roughly twelve years | 0 |
| nate-saunsoci | seven years | 0 |
| randolph-jennings | more than six years | 0 |
| kevin-poulsen | about 5 years | 0 |
| patricia-swinton | more than five years | 0 |
| warren-kimbro | about four years | 0 |

…and 31 more, down to four days.

**32 are fixed in batch 169** by setting `imprisoned_for_months` — the column
the archive already has for exactly this case. It outranks date arithmetic in
`computeImprisonedForDays()`, and unlike an anchored date it survives the
nightly recompute. Every value is read off the row's own prose. Total
recovered: **2,205 months, about 184 years.**

### Nine are deliberately left alone

**Four would double-count or are ambiguous.**

- `elmer-geronimo-pratt` — three case rows for one imprisonment. Row 1 already
  computes 9,083 days (1972-07-28 → 1997-06-10). Row 2 says "served 27 years"
  about the same 27 years. Adding months would publish 52 years.
- `carl-marzani` — two rows for one imprisonment. Row 0 computes 1,150 days;
  row 1 is a dateless duplicate saying "entered prison in March 1949 and
  served 32 months". The two also disagree about when it started (1947 vs
  1949).
- `carl-braden` — one row says "served about nine months", another
  "approximately eight months". Neither computes. Which imprisonment each
  describes is not clear from the rows.
- `joseph-aceto` — "served **under** three years" is a ceiling, not a floor.
  There is no honest figure to enter.

**Five are shorter than a month**, and `imprisoned_for_months` is an integer,
so it cannot hold them: `mark-comfort` (44 days), `eugene-keyes` (17),
`lyda-conley` (10), `a-j-muste` (about a week), `max-dezettel` (about four
days). The batch-168 anchor trick can express these, but only on a row that
already has an incarceration date, and these do not. **A `imprisoned_for_days`
override, or making the months column accept a fractional value, is the
structural fix** — worth considering, since the sub-month custody is the
single most common kind in a protest archive.

### Four are recorded as floors

`randolph-jennings` (72), `patricia-swinton` (60), `robert-klonsky` (12),
`joseph-palmer` (12). Each source says "more than" without saying how much
more, so the figure entered is the least the custody can have been, and each
row gets a sentence saying so. This follows the convention already used for
Van Lydegraf, whose 773 days are measured to the first day his release year
allows.

---

## Class B — the dates produce a figure that contradicts the prose

14 rows, and this class is **worse than class A**, because it overstates. Most
are a single case row holding *several separate imprisonments*: the arrest
date of the first and the release date of the last, with every free year in
between counted as custody.

| record | dates say | prose says |
|---|---|---|
| kathy-kelly | **9,527 d — 26 years** | 9 months + 3 months + 3 months |
| herman-ferguson | **9,065 d — 24.8 years** | served 3 years (after 20 years' exile in Guyana) |
| dennis-banks | **4,711 d — 12.9 years** | served 18 months |
| russell-means | **2,764 d — 7.6 years** | approximately one year |
| wendy-yoshimura | 1,803 d | roughly 13 months |
| marissa-alexander | 1,722 d | jail, then 2 years *home detention* |
| david-j-miller | 1,536 d | served 22 months |
| david-eberhardt | 1,451 d | served 21 months |
| mary-anne-grady-flores | 888 d | served 6 months |
| alexander-trachtenberg | 101 d | served 2 years (*under*-stated) |

Kathy Kelly's profile currently tells readers she spent twenty-six years in
prison. Herman Ferguson's counts his twenty years of exile in Guyana as
custody.

**Not fixed here.** Each needs its case row split into the separate
imprisonments it describes, which is research, not a script. Two of the
fourteen are false positives worth recording: `charles-liteky` ("spent his
last 70 days in solitary" is a subset of a correctly counted year) and
`eldridge-cleaver` ("served approximately 8 years **on the run**" is exile,
not custody — though that raises whether his exile should be recorded as
such, since `in_exile` is false on his record).

---

## Separately: the stale counters are still stale

The sweep also re-measured what batch 137 diagnosed. In the August 3 dump:

- **460 records publish a counter of 45 years or more.**
- Henry David Thoreau: **180 years.** He spent one night in jail.
- julia-emory 323 years, bradford-lyttle 293, fred-shuttlesworth 265, and ten
  identical 231-year figures on a single group of records.
- Archive total: **58,384 prisoner-years**, against a model-computed figure
  that is a small fraction of it.

These are residue from the deleted `cases:update-imprisoned-days`, which wrote
with `saveQuietly()` and bypassed the model guards. **No new work is needed —
batch 137 already replaced the nightly job with
`prisoners:recompute-imprisonment --apply`, which recomputes through the same
method the model hook uses.** It has simply never been deployed. Running it
once clears all 460.

That deployment should happen *before* batch 169 is judged, because it changes
almost every number on this page.

---

## Recommended order

```
git pull origin main
bash database/data/run-batch-169.sh
php artisan prisoners:recompute-imprisonment --apply
```

## Still open

1. The 10 class-B rows that need splitting — the largest remaining
   misstatement in the archive, and the only one that overstates.
2. A mechanism for sub-month custody without dates (5 records here, and
   certainly more that state no duration at all).
3. Duplicate case rows, found incidentally: Pratt (3 for 1), Marzani (2 for
   1), Conway (2 for 1), Comfort. A dedicated duplicate sweep would likely
   find many more.
4. Whether Eldridge Cleaver's eight years abroad should be recorded as exile.
