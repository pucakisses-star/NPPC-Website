#!/usr/bin/env python3
"""Detect padding around the real picture in a photograph.

The Geoffrey Parsons shape: the file is a 16:9 broadcast frame, the mugshot
stands in the middle at under half the width, and the side panels are a
blurred, stretched copy of the same picture. The image is not corrupt and its
dimensions look unremarkable, so nothing flags it — but on a profile page the
face renders small between two grey smears.

Two kinds of padding are found:

  BLURRED PILLARBOX  the bars are a defocused copy of the picture, so they
                     carry colour but almost no high-frequency detail.
  FLAT PADDING       the bars are a solid or near-solid colour, so they carry
                     almost no variation at all.

Both are found the same way, by scanning column energy (and row energy for
letterboxing) and looking for a low-energy margin either side of a
high-energy core. A photograph that is simply landscape has energy spread
across the whole frame and is left alone.
"""
import json, os, sys
from PIL import Image, ImageFilter

# The scan is on a reduced copy: bar geometry survives downscaling and this
# keeps 185 images to a few seconds.
MAX = 480


def profiles(g):
    """Per-column and per-row edge energy.

    Done through PIL rather than a Python loop: FIND_EDGES gives the local
    gradient, and a BOX resize to a single row or column averages it along the
    other axis. Both run in C, which is the difference between seconds and
    twenty minutes over two thousand images. The one-pixel inset drops the
    frame FIND_EDGES leaves around the border.
    """
    w, h = g.size
    e = g.filter(ImageFilter.FIND_EDGES).crop((1, 1, w - 1, h - 1))
    ew, eh = e.size
    cols = list(e.resize((ew, 1), Image.BOX).getdata())
    rows = list(e.resize((1, eh), Image.BOX).getdata())
    return cols, rows


def core(energy, frac=0.25, run_len=4):
    """Where the picture starts and stops, scanning inward from each edge.

    Two ways to set the threshold both fail, and the fix is to use neither
    extreme:

      A FRACTION OF THE PEAK collapses whenever one very sharp feature
      dominates the profile — a burnt-in caption, a hard border rule, a white
      frame. It then reports that 99% of the image is padding, which is a
      broken measurement rather than a finding.

      A CUMULATIVE TAIL is too forgiving in the other direction. A blurred
      pillarbox is not inert, just soft, so it still carries a percent or two
      of the total and never gets trimmed. Geoffrey Parsons measured 73% kept
      against a true 44%.

    So the threshold is a quarter of the 75th percentile: high enough to
    ignore soft bars, robust enough that a single bright column cannot move
    it. The scan then works inward from each edge and stops at the first run
    of `run_len` consecutive columns above it, so an isolated spike inside a
    bar does not end the margin early.
    """
    n = len(energy)
    if n == 0:
        return 0, 0

    ordered = sorted(energy)
    q75 = ordered[int(0.75 * (n - 1))]
    thr = q75 * frac

    if thr <= 0:
        return 0, n - 1

    lo = 0
    run = 0
    for i in range(n):
        if energy[i] > thr:
            run += 1
            if run >= run_len:
                lo = i - run_len + 1
                break
        else:
            run = 0

    hi = n - 1
    run = 0
    for i in range(n - 1, -1, -1):
        if energy[i] > thr:
            run += 1
            if run >= run_len:
                hi = i + run_len - 1
                break
        else:
            run = 0

    return (lo, hi) if hi >= lo else (0, n - 1)


def analyse(path):
    im = Image.open(path)
    W, H = im.size
    g = im.convert('L')
    g.thumbnail((MAX, MAX))
    sw, sh = g.size[0] - 2, g.size[1] - 2

    cx, cy = profiles(g)
    x0, x1 = core(cx)
    y0, y1 = core(cy)

    kept_w = (x1 - x0 + 1) / sw
    kept_h = (y1 - y0 + 1) / sh
    kept = kept_w * kept_h

    # THE FALSE POSITIVE THIS GUARDS AGAINST: a studio portrait of somebody
    # standing against a plain wall also has low edge energy near its left and
    # right edges, and a core-detection scan alone calls that padding. It is
    # not padding, it is composition, and cropping it would cut into the
    # photograph.
    #
    # What separates them is how DEAD the margin is. A pillarbox bar is solid
    # colour or a heavy blur and carries almost no gradient at all; a plain
    # background still has shading, vignetting and a shoulder creeping in. So
    # the margin energy is measured against the core energy on each axis and a
    # margin only counts as padding when it is nearly inert.
    def deadness(energy, a, b):
        inside = energy[a:b + 1]
        outside = energy[:a] + energy[b + 1:]
        if not outside or not inside or not sum(inside):
            return 1.0
        return (sum(outside) / len(outside)) / (sum(inside) / len(inside))

    ratio = deadness(cx, x0, x1)
    ratio_y = deadness(cy, y0, y1)

    return {
        'w': W, 'h': H, 'aspect': round(W / H, 3),
        'x0': round(x0 / sw, 4), 'x1': round((x1 + 1) / sw, 4),
        'y0': round(y0 / sh, 4), 'y1': round((y1 + 1) / sh, 4),
        'kept_w': round(kept_w, 3), 'kept_h': round(kept_h, 3),
        'kept': round(kept, 3), 'margin_ratio': round(ratio, 3),
        'margin_ratio_y': round(ratio_y, 3),
        'crop_w': int(round((x1 - x0 + 1) / sw * W)),
        'crop_h': int(round((y1 - y0 + 1) / sh * H)),
    }


# A margin has to be this much deader than the picture to count as padding
# rather than as a plain background.
INERT = 0.12


def classify(a):
    """What kind of fix, if any, this image needs."""
    pill = a['kept_w'] < 0.88 and a['margin_ratio'] < INERT
    letter = a['kept_h'] < 0.88 and a['margin_ratio_y'] < INERT

    if not pill and not letter:
        return 'clean', 'no detectable padding'

    parts = []
    if pill:
        parts.append(f"{(1 - a['kept_w']) * 100:.0f}% of the width is padding")
    if letter:
        parts.append(f"{(1 - a['kept_h']) * 100:.0f}% of the height is padding")
    why = '; '.join(parts)

    # Only the padded axes count towards how much of the frame is wasted.
    kept = (a['kept_w'] if pill else 1.0) * (a['kept_h'] if letter else 1.0)

    if kept < 0.45:
        return 'severe', why
    if kept < 0.72:
        return 'moderate', why
    return 'slight', why


def main(listfile, imgdir):
    out = []
    for line in open(listfile).read().strip().split('\n'):
        slug, url = line.split('\t')
        p = os.path.join(imgdir, slug + '.img')
        if not os.path.exists(p) or os.path.getsize(p) == 0:
            out.append({'slug': slug, 'url': url, 'verdict': 'unreadable',
                        'why': 'file missing or empty'})
            continue
        try:
            a = analyse(p)
        except Exception as e:
            out.append({'slug': slug, 'url': url, 'verdict': 'unreadable',
                        'why': str(e)[:80]})
            continue
        v, why = classify(a)
        a.update({'slug': slug, 'url': url, 'verdict': v, 'why': why})
        out.append(a)

    out.sort(key=lambda r: r.get('kept', 1))
    json.dump(out, open('bars_result.json', 'w'), indent=1)

    import collections
    c = collections.Counter(r['verdict'] for r in out)
    print(f'analysed {len(out)}')
    for k in ('severe', 'moderate', 'slight', 'clean', 'unreadable'):
        if c[k]:
            print(f'  {k:11} {c[k]:>4}')


if __name__ == '__main__':
    main(sys.argv[1], sys.argv[2])
