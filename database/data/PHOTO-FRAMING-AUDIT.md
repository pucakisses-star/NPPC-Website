# Photo framing audit — how many more look like Geoffrey Parsons

Batch 172 found that Geoffrey Parsons's portrait was a 16:9 television frame
with his mugshot standing in the middle at under half the width and the side
panels filled with a blurred, stretched copy of itself. This is the sweep for
every other photograph with that shape.

**Every photograph in the archive was fetched and measured — 2,160 URLs across
2,187 records.** Not a sample.

## The answer

**17 photographs have Parsons's framing.** Four were fixed in batch 172, so
**13 remain.** A further **3** have a related problem that cropping cannot fix.

| | count |
|---|---|
| padded, croppable — fixed in batch 172 | 4 |
| padded, croppable — **outstanding** | **13** |
| zoomed-in broadcast frame, not croppable | 3 (2 since replaced, batch 178) |
| broken photo link | 1 |
| everything else | 2,139 |

---

## The 13 outstanding

Each was looked at, not just measured. Three distinct shapes:

### Blurred pillarbox — the exact Parsons pattern (3)

The bars are a defocused, stretched copy of the picture itself.

| record | file | picture is |
|---|---|---|
| `robert-majure` | 680×383 | ~41% of the width |
| `nicholas-lucia` | 1024×576 | ~45% |
| `damion-zachary-feller` | 680×382 | ~47% |

### Solid bars — same waste, flat colour instead of blur (4)

| record | file | bars | picture is |
|---|---|---|---|
| `chase-vladamir-spencer` | 910×512 | black | ~35% of the width |
| `christopher-tindal` | 648×364 | white | ~42% |
| `dylan-robinson` | 910×512 | white | ~54% |
| `deyanna-davis` | 1920×1080 | black | ~69%, plus a `wgrz.com` watermark |

`christopher-tindal` is worth a second look beyond the crop: the picture inside
the bars is a scan of a printed court exhibit showing a street scene, not a
portrait of him.

### Graphic cards — a small mugshot on a designed background with the name burnt in (6)

These are the worst of the set, because the caption is part of the image and a
naive crop keeps it.

| record | file | what it is |
|---|---|---|
| `jackson-patton` | 1200×630 | mugshot on blurred police lights, "Jackson Patton" set in white type |
| `semaj-pigram` | — | mugshot in a red frame, "PIGRAM" in a caption bar |
| `walter-stewart` | — | same red template, "STEWART" |
| `gilberto-castillo` | — | mugshot on blue, "Gilberto Castillo" |
| `vida-jones` | — | mugshot on white, "Vida Jones D.O.B. 4/7/2002" |
| `jabari-davis` | — | mugshot inset on a white and blue graphic |

The name captions are a second-order problem: the archive prints the person's
name above the photograph already, so the card repeats it in a stranger's
typography, and Vida Jones's card publishes a date of birth the record does not
otherwise carry.

---

## The 3 that cropping cannot fix

Not padded. The broadcast frame is zoomed **into** the mugshot, so the missing
part of the face is not in the file at all and there is nothing to trim.

- ~~`emily-murphy` (932×524) — top of the head and the chin cut off~~ — **replaced in
  batch 178** with the Fulton County booking photograph (400×337)
- ~~`henri-feola` (900×506) — ends just below the eyes; also carries a station
  watermark~~ — **replaced in batch 178**, and moved off a file path that carried a
  name the record does not use
- `henry-parker` (640×360) — top of the head clipped — **not fixable, closed.** The
  frame in this archive is the one every outlet ran; there is no better version in
  circulation. The clipping is slight and the face is complete.

These needed a replacement source image rather than a tighter crop of a broken
frame. Two got one: the Atlanta Police Department released the January 21, 2023
booking photographs as a single six-up composite, and cutting it into cells gives
the whole head where the broadcast frame gave two thirds of it — at lower
resolution, which is the right trade when the alternative is a cropped face.

The other three of those six (`francis-carroll`, `graham-evatt`, `ivan-ferguson`)
were **not** re-cut from the composite. Batch 172 cropped them from broadcast
frames 524 pixels tall, which beats the composite's 400×337. The official
photograph only wins where the frame was zoomed *into* the face.

## One broken photo link

`william-tanner` — the photo URL returns an HTML error page rather than an
image. His profile has been showing a broken image, and no dimension check
would ever have caught it because nothing about the record looks wrong.

---

## Method, and what it got wrong twice

The detector measures per-column and per-row edge energy and finds where the
picture starts, using PIL's `FIND_EDGES` plus a BOX resize to a single row or
column so both run in C — 2,159 images in 32 seconds rather than twenty
minutes.

Setting the threshold is the whole problem, and both obvious choices fail:

**A fraction of the peak collapses on a single sharp feature.** A burnt-in
caption or a hard border rule dominates the profile, and the scan then reports
99% of the image as padding. That run produced 53 "severe" findings, of which
49 were portraits measuring `keepW 1%` — a broken measurement, not a finding.

**A cumulative tail is too forgiving.** A blurred pillarbox is not inert, just
soft, so it still carries a percent or two of the total and never gets trimmed.
Parsons measured 73% kept against a true 44%.

The working threshold is a quarter of the 75th percentile — high enough to
ignore soft bars, robust enough that one bright column cannot move it — with an
inward scan that requires four consecutive columns above it, so a spike inside
a bar does not end the margin early. Measured against twelve images checked by
eye, it lands within a few points every time (Parsons 45% against a true 44%,
Chase 37% against 35%, Carroll 52% against 50%).

### Why the verdicts are hand-checked and not automated

Even with accurate measurements, **no threshold separates a pillarbox bar from
a plain studio background.** A portrait shot against a grey wall genuinely has
low edge energy at its left and right edges. An "inert margin" test was tried
and does not work: Parsons's blurred bars score 0.20 on it and Henri Feola's
plain wall scores 0.16, so any cut-off either loses real cases or invents them.

So the detector ranks candidates and a person decides. 361 photographs measure
more than 20% margin; the top 120 were reviewed on contact sheets, and the true
cases are concentrated in the top 50 with a long tail of false positives after
it. The counts in this document are of images that were looked at.

### The largest false-positive group, deliberately left alone

Roughly forty of the flagged candidates are the **Jackson, Mississippi Freedom
Rider mugshots** — Wyatt T. Walker, Pete Seeger's fellow riders, and dozens
more, all with a white margin down one side. That margin is part of the
archival print, not padding added afterwards, and cropping it would be cropping
the artefact rather than the picture. They are not findings.

Also correctly excluded: engravings and line drawings on white grounds, cutout
portraits on white, and antique photographs with natural vignettes.

## Formats, incidentally

1,927 JPEG, 193 PNG, 35 WebP, 4 GIF. Nothing here needs action; recorded
because the sweep had the numbers.

## Re-running it

```
python3 database/data/detect-photo-framing.py <list.tsv> <image-dir>
```

The list is `slug<TAB>url` per line. Output is `bars_result.json`, sorted by
how much of the frame is margin.
