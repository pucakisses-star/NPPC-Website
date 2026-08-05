#!/usr/bin/env python3
"""Derive birth years from ages stated in descriptions, where no birthdate exists.

An age of N reported on a known date D means a birth in the 365-day window
D - (N+1) years + 1 day .. D - N years. That window straddles two calendar
years, so the answer is a likelier year rather than a known one, and it is
stored at 'circa' precision, which renders "c. 2002" and means plus or minus
one. HasPartialDates documents that precision for exactly this case.

FOUR WAYS THIS GOES WRONG, each of which cost a rewrite:

1. THE AGE BELONGS TO SOMEONE ELSE. Ola Mae Davis is described as an elderly,
   blind, diabetic woman whose case involved police shooting "a 16-year-old
   Black youth". A loose match reads that 16 as hers and files her as born
   c. 1960. Ages are therefore only read from the opening clause, where the
   subject is the grammatical subject.

2. THE AGE IS PINNED TO ANOTHER EVENT. "Came from the Philippines with his
   family in 2001 at age 14" anchored to a 2025 arrest is out by 24 years.
   Rejected whenever a competing anchor verb or a stray year is nearby.

3. THE ANCHOR IS NOT A DAY. The API emits partial dates truncated to
   precision, so a year-precision arrest arrives as "1979" and parses to
   January 1. A January 1 anchor puts the whole 365-day window inside one
   calendar year and reports ~100% confidence in what is really a coin toss.
   Only day-precision anchors are used, and January 1-2 anchors are dropped
   as well, because that is the value a year-precision date collapses to.

4. ^ WITH re.MULTILINE matches the start of every line, not the start of the
   description, which quietly turns the opening-clause rule into no rule.
"""
import json, re, sys
from datetime import date, timedelta

# The age must sit in the opening clause, where the subject of the sentence is
# the person the record is about.
LEAD = 130

# The dump these ages were read from. Used only to tell a present-tense age
# ("is a 38-year-old") from a stale one.
DUMP = date(2026, 8, 3)

PATTERNS = [
    # "Age 20 at the time of arrest", "aged 31 when he was arrested"
    (r'\bage[d]?\s+(\d{1,2})\b[^.;]{0,40}?\b(?:at\s+the\s+time\s+of\s+(?:his\s+|her\s+|their\s+)?'
     r'(?:arrest|the\s+arrest)|when\s+(?:he|she|they)\s+(?:was|were)\s+(?:arrested|detained|'
     r'seized|taken\s+into\s+custody)|at\s+(?:his|her|their)\s+arrest)', 'explicit'),
    (r'\b(\d{1,2})\s+years?\s+old\b[^.;]{0,40}?\b(?:at\s+the\s+time\s+of\s+(?:his\s+|her\s+|their\s+)?'
     r'(?:arrest|the\s+arrest)|when\s+(?:he|she|they)\s+(?:was|were)\s+arrested)', 'explicit'),
    (r'\bwas\s+(\d{1,2})\s+when\s+(?:he|she|they\s+were|arrested)', 'explicit'),
    # Wire-style appositive on the subject: "Donald Zepeda, age 35, of Maryland,"
    (r'\A[^.;]{0,90}?,\s*age[d]?\s+(\d{1,2})\s*,', 'appositive'),
    # "A 27-year-old climate activist ..." / "X is a 37-year-old activist"
    (r'\A[^.;]{0,60}?\bis\s+an?\s+(\d{1,2})[-‑]year[-‑]old\b', 'present'),
    (r'\A(?:[^.;]{0,60}?\b(?:was|were)\s+)?an?\s+(\d{1,2})[-‑]year[-‑]old\b', 'appositive'),
]

# Anything that pins the age to a different moment.
COMPETING = re.compile(
    r'\b(?:came|come|arrived|moved|emigrat\w*|immigrat\w*|fled|left|brought|'
    r'born|joined|enlisted|enrolled|graduat\w*|married|began|start\w*|founded|'
    r'since|by\s+the\s+age|until|dropped\s+out|converted|first|shot|killed|'
    r'beat|struck|son|daughter|brother|sister|father|mother|child)\b', re.I)

YEAR = re.compile(r'\b(1[6-9]\d{2}|20\d{2})\b')


def parse_day(v):
    """Only a full YYYY-MM-DD is a day. Anything shorter is a truncated partial
    date and cannot anchor an age."""
    v = str(v or '')
    if not re.fullmatch(r'\d{4}-\d{2}-\d{2}', v):
        return None
    try:
        return date(int(v[:4]), int(v[5:7]), int(v[8:10]))
    except ValueError:
        return None


def find_age(text):
    if not text:
        return None
    for rx, kind in PATTERNS:
        # "at the time of arrest" says which moment it means, so it is trusted
        # anywhere in the text. An appositive is only trusted in the opening
        # clause, where the subject of the sentence is the person themselves.
        hay = text if kind == 'explicit' else text[:LEAD]
        m = re.search(rx, hay, re.I)           # no re.M: \A and ^ mean the start
        if not m:
            continue
        n = int(m.group(1))
        if not (5 <= n <= 99):
            continue
        before = hay[max(0, m.start() - 55):m.start()]
        if COMPETING.search(before):
            continue
        near = text[max(0, m.start() - 45):m.end() + 25]
        if YEAR.search(near):
            continue
        return n, kind, re.sub(r'\s+', ' ', m.group(0)).strip()
    return None


def window(anchor, age):
    hi = date(anchor.year - age, anchor.month, anchor.day)
    lo = date(anchor.year - age - 1, anchor.month, anchor.day) + timedelta(days=1)
    span = (hi - lo).days + 1
    early = (date(lo.year, 12, 31) - lo).days + 1
    late = span - early
    year, share = (lo.year, early / span) if early >= late else (hi.year, late / span)
    return lo, hi, year, share


def main(path):
    data = json.load(open(path))
    recs = data['records'] if isinstance(data, dict) and 'records' in data else data
    ok, review = [], []

    for r in recs:
        if r.get('Birthdate'):
            continue
        found = find_age(r.get('Description') or '')
        if not found:
            continue
        age, kind, phrase = found
        desc = re.sub(r'\s+', ' ', r.get('Description') or '')

        raw, anchors = [], []
        for c in r.get('cases') or []:
            for f in ('Arrest Date', 'Incarceration Date'):
                v = c.get(f)
                if v:
                    raw.append(str(v))
                    a = parse_day(v)
                    if a:
                        anchors.append(a)
                    break

        base = {'slug': r['slug'], 'name': r.get('name'), 'age': age, 'kind': kind,
                'phrase': phrase, 'raw_dates': raw, 'desc': desc[:300]}

        if not anchors:
            base['why'] = ('no day-precision arrest date to anchor to'
                           if raw else 'no arrest or incarceration date at all')
            review.append(base)
            continue

        anchor = min(anchors)

        if (anchor.month, anchor.day) in ((1, 1), (1, 2)):
            base['why'] = f'anchor {anchor} is January 1-2, the value a year-precision date collapses to'
            review.append(base)
            continue

        if len({a.year for a in anchors}) > 1:
            base['why'] = f'arrests in {sorted({a.year for a in anchors})} — which one is the age reported at?'
            review.append(base)
            continue

        lo, hi, year, share = window(anchor, age)
        death = parse_day(r.get('Death date')) or (
            re.match(r'^(\d{4})', str(r.get('Death date') or '')) and
            date(int(str(r.get('Death date'))[:4]), 12, 31))

        base.update({'anchor': str(anchor), 'lo': str(lo), 'hi': str(hi),
                     'year': year, 'share': round(share, 3)})

        # "is a 38-year-old" is the age when the entry was WRITTEN. That only
        # equals the age at arrest while the arrest is recent; on an older case
        # the two drift apart by however long the record went unwritten.
        if kind == 'present' and (DUMP.year - anchor.year) > 2:
            base['why'] = (f'present tense "is a {age}-year-old" against a {anchor.year} arrest — '
                           f'that is the age when the entry was written, not at the arrest')
            review.append(base)
        elif death and death.year < year:
            base['why'] = 'derived birth year is after the recorded death date'
            review.append(base)
        else:
            ok.append(base)

    ok.sort(key=lambda x: (-x['share'], x['slug']))
    json.dump({'apply': ok, 'review': review}, open('sweep_ages.json', 'w'), indent=1)
    print(f'derivable: {len(ok)}   held back: {len(review)}')
    if ok:
        print(f'  explicit: {sum(1 for r in ok if r["kind"] == "explicit")}'
              f'   appositive: {sum(1 for r in ok if r["kind"] == "appositive")}')
        print(f'  confidence in the chosen year: {min(r["share"] for r in ok):.0%} worst, '
              f'{sum(r["share"] for r in ok) / len(ok):.0%} mean')
        for lo, hi in ((0.9, 1.01), (0.75, 0.9), (0.6, 0.75), (0, 0.6)):
            n = sum(1 for r in ok if lo <= r['share'] < hi)
            print(f'    {lo:.0%}-{hi if hi <= 1 else 1:.0%}: {n}')


if __name__ == '__main__':
    main(sys.argv[1])
