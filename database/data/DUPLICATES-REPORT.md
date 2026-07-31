# Duplicate prisoner records — audit findings

Produced by `php artisan prisoners:audit-duplicate-names`, then read by
hand pair by pair. The command ranks candidates; it does not decide them.

**Why this was needed.** `prisoners:audit-duplicates` already existed, but
it groups on a shared **birthdate**. That is a good signal when a birthdate
exists — and most of this table has none. Charles Moyer and George
Pettibone were each in the database twice and neither pair shared a
birthdate, so nothing caught them until somebody looked by hand. The new
command matches on name shape instead.

Scanned 8,321 records. 51 pairs scored at or above the threshold, plus 3
found only by the reordered-name pass. **19 pairs merged** in batch 45, and
one record split. The rest are below, with the reason.

---

## NOT duplicates — ruled out by a veto

The audit refuses these outright. They are the cases where a looser matcher
would confidently merge two different people.

### Fathers and sons

| | |
|---|---|
| Abraham Isaak / Abraham Isaak **Jr.** | Both anarchists caught up in the 1901 arrests after the McKinley assassination. Two men. |
| Billy Frank **Sr.** / Billy Frank **Jr.** | Nisqually fish-war generations. |
| Fred Shuttlesworth / Fred Shuttlesworth **Jr.** | The father has 11 case rows. |

Stripping "Jr." as though it were an honorific merges a man into his son.
The command keeps generational suffixes for exactly this reason.

### Middle names or initials that disagree

An initial **absent** on one record is a name variant. An initial that
**contradicts** is a second person.

Michael **Hill** Africa / Michael **Davis** Africa · Robert **Hillary** King
/ Robert **Edwin** King · William **L.** Patterson / William **Patrick**
Patterson · John **Howard** Lawson / John **R.** Lawson · Ana **Lucía**
Gelabert / Ana **María** Gelabert · James **P.** Thompson / James **A.**
Thompson · Thomas **Todd** Edwards / Thomas **W.** Edwards · Ricardo
**Santos** Ortiz / Ricardo **Chávez**-Ortiz · Jorge **Cruz** Hernández /
Jorge **Maysonet**-Hernández · Manuel **Antonio** Díaz / Manuel **Cuevas**
Díaz · Héctor **M.** Otero / Héctor **Xavier** Otero · plus C. L./C. J.
Smith, Charles M./H. Walker, David A./R. Troyer, Enos Melvin/N. Hooley,
Gerhard J./M. Klippenstein, J.B./J. J. Johnson, John J./D. Ford, John L./H.
Scott, Ross H./R. Gillman, William E./M. Martin, William O. Smith /
William Drewet Smith, Andrew Adolf/Anna Hofer.

---

## NOT duplicates — the records disambiguate themselves

**William Drewet Smith / William Smith.** Both Virginia Exiles banished
from Philadelphia in September 1777, same dates, same charge. But one
biography says *a Philadelphia druggist* and the other says *a Philadelphia
broker (so distinguished in the records from the other men of that common
name)*. Whoever wrote them had already done this work. Two men.

**José Perez Rivera / José Román Rivera.** Both Vieques trespass, both
Puerto Rico, and their biographies are 99% identical — because the
biography is templated across a four-year campaign. The **cases** separate
them: arrested 2001-04-26 for 45 days, against 2001-05-22 for 90 days.
Different men. (`jose-rivera` and `jose-roman-rivera`, whose cases match
exactly, *were* merged.)

**Mariah De Los Santos**, 23, of Texas, in the Greenpeace 28 import, has no
counterpart in the federal 22. Left alone.

---

## Probably duplicates — NOT merged, needs a curator

These scored high and I could not settle them from the records alone.

**Lucy G. Branham / Lucy Gwynne Branham** — *the most dangerous pair here.*
There were **two** National Woman's Party suffragists of this name, a
mother and a daughter, and both were arrested. Stevens gives one a
three-day term in January 1919; the other record has a September 4, 1917
incarceration and describes a noted hunger striker who toured on the Prison
Special. That reads like two people, not one — but it could equally be one
woman's two arrests. **Do not merge without a source that separates them.**

**James Earl Grant / James Grant** — almost certainly one man; `james-grant`
says "often identified as Dr. James Earl Grant", and both are the Charlotte
Three. Not merged only because the two rows disagree on the arrest year
(1971 against 1972) and on the release (1979-07 against 1979), so a merge
has to choose, and the choice needs a source.

**Matthew White / Matthew Scott White** — both Minnesota, both George Floyd
uprising. But one is charged with *threats* and held at **SCI Dallas**, a
Pennsylvania state prison, and the other with *federal arson* at Terre
Haute for the St. Paul Enterprise Rent-A-Car fire. Those do not reconcile.
Either two men, or one record has the wrong facility.

**Shabazz A. Watson / Shabazz Akeem Isiah Watson** — same Charleston arson,
same four businesses, same night. Not merged because the birthdate on one
(2002-05-09) makes him 18 in May 2020 while its own biography says 24.
Merging would carry a birthdate that contradicts the text.

**Tyre Wayne Means Jr. / Tyre Means**, **William Joe Wright / William
Wright Jr.**, **William V. Schneiderman / William Schneiderman** — in each,
one row carries a generational suffix and the other does not. Same person
loosely recorded, or two generations. Unresolved.

**Others in the same position**, all scoring 6–9: Daniel Sanchez Estrada /
Daniel Rolando Sanchez-Estrada · James E. Jackson / James Edward Jackson ·
Charles 2X Beasley / Charles Beasley · Marcus A. Murphy / Marcus Murphy ·
Emmett Brown / Emmett Calvin Brown · Bryce Michael Williams / Bryce
Williams · Jabari Davis / Jabari Devon Davis · Judah Bailey / Judah Coleman
Bailey · José Jacques Medina / Jose Medina · Frank Smith / Frank "Big
Black" Smith · David O'Brien / David Paul O'Brien · Herbert J. Phillips /
Herbert Phillips · Howard Moore / Howard Wilbur Moore · Dora Lewis / Dora
Kelly Lewis.

Several of these are near-certain — Frank "Big Black" Smith of Attica and
David Paul O'Brien of the draft-card case are each plainly one man — but
merging costs a URL and can cost a photograph, so they are listed rather
than done.

---

## Near-miss spellings worth a second look

The veto rejected these on a conflicting middle, but the conflict is a
single letter and may be a transcription variant rather than two people:

- **Sang Ryun Park / Sang Ryup Park** — `n` against `p`.
- **Andrew Anna Hofer / Andrew Ana Hofer** — one `n`. There is also
  *Andrew Adolf Hofer*, which is genuinely a different man. The Hofer
  brothers were Hutterite conscientious objectors and two of them died at
  Fort Leavenworth in 1918; this cluster deserves proper attention.

---

## Re-running this

```bash
php artisan prisoners:audit-duplicate-names
php artisan prisoners:audit-duplicate-names --min-score=8
php artisan prisoners:audit-duplicate-names --show-ruled-out
```

Read-only. It never writes.
