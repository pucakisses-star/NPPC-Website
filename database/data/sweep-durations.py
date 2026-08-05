#!/usr/bin/env python3
"""Sweep for custody durations stated in prose but not counted by the dates.

The Mark Rudd shape: the source records HOW LONG somebody was held but not
WHEN, so the row carries an incarceration date and no release date, the
duration calculation returns null, and a documented custody publishes nothing.
The row looks correctly cautious rather than wrong, which is why no earlier
audit caught it.

The hard part is telling TIME SERVED from SENTENCE IMPOSED. "Five years in
prison" is what a judge ordered; "he spent five years in prison" is what
happened. Only the second is a duration this archive has failed to count, and
the first outnumbers it heavily, so the matcher demands an explicit
served-time verb and throws away anything sitting near sentencing language.

Class A -- prose states a served duration, dates cannot produce one.
Class B -- prose states a served duration, dates produce one that materially
           disagrees with it.
"""
import json, re, sys
from datetime import date

WORDS = {
    'a': 1, 'an': 1, 'one': 1, 'two': 2, 'three': 3, 'four': 4, 'five': 5,
    'six': 6, 'seven': 7, 'eight': 8, 'nine': 9, 'ten': 10, 'eleven': 11,
    'twelve': 12, 'thirteen': 13, 'fourteen': 14, 'fifteen': 15,
    'sixteen': 16, 'seventeen': 17, 'eighteen': 18, 'nineteen': 19,
    'twenty': 20, 'thirty': 30, 'forty': 40, 'fifty': 50, 'sixty': 60,
    'seventy': 70, 'eighty': 80, 'ninety': 90,
}
UNIT_DAYS = {'day': 1, 'night': 1, 'week': 7, 'month': 30.436875, 'year': 365.25}

NUM = r'(?:\d{1,4}(?:,\d{3})*|' + '|'.join(WORDS) + r')'
UNIT = r'(day|night|week|month|year)s?'
FUZZ = r'(?:about|approximately|roughly|nearly|almost|some|over|more\s+than|' \
       r'at\s+least|another|a\s+further|an\s+additional|additional|further|just)\s+'

# The verb has to say the time was actually undergone, and it has to sit
# within a few words of the number.
SERVED = re.compile(
    r'\b(spent|served|serving|was\s+held|were\s+held|held\s+for|held\s+him\s+for|'
    r'jailed\s+for|imprisoned\s+for|incarcerated\s+for|detained\s+for|'
    r'confined\s+for|locked\s+up\s+for|inside\s+for|remained\s+in\s+custody\s+for|'
    r'released\s+after|freed\s+after|bailed\s+out\s+after|out\s+after)'
    r'((?:\s+(?:' + FUZZ.strip().replace(r'\s+', ' ') + r'|\w+)){0,4}?\s+)'
    r'(' + NUM + r')\s+(?:' + FUZZ + r')?' + UNIT,
    re.I)

# Sentencing language anywhere near a hit disqualifies it: what a court
# ordered is not what the person underwent.
SENTENCE_CTX = re.compile(
    r'\b(?:sentenc\w*|to\s+serve|term\s+of|probation|parole|suspended|'
    r'maximum|minimum|up\s+to|faces?|facing|could\s+face|carries|carrying|'
    r'punishable|statute|plea\s+deal|deferred|consecutive|concurrent|'
    r'to\s+life|commuted|reduced\s+to|eligible\s+for)\b', re.I)

# "30 to 90 days", "five to ten years" -- an indeterminate sentence range.
RANGE = re.compile(r'\b' + NUM + r'\s*(?:to|-|–|or)\s*' + NUM + r'\s+' + UNIT, re.I)


def to_num(tok):
    tok = tok.lower().replace(',', '')
    return int(tok) if tok.isdigit() else WORDS.get(tok)


def parse_day(v):
    """The API emits partial dates truncated to precision: 1892, 1892-06 or
    1892-06-15. Missing parts default to 1, matching how the column stores
    them. Treating a bare year as unparseable makes every year-precision
    release look like a missing one."""
    if not v:
        return None
    m = re.match(r'^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?', str(v))
    if not m:
        return None
    try:
        return date(int(m.group(1)), int(m.group(2) or 1), int(m.group(3) or 1))
    except Exception:
        return None


def find_durations(text):
    if not text:
        return []
    out = []
    for m in SERVED.finditer(text):
        n = to_num(m.group(3))
        if n is None:
            continue
        unit = m.group(4).lower()
        before = text[max(0, m.start() - 70):m.start() + len(m.group(1))]
        around = text[max(0, m.start() - 20):m.end() + 25]
        if SENTENCE_CTX.search(before) or RANGE.search(around):
            continue
        days = n * UNIT_DAYS[unit]
        if days <= 0 or days > 365.25 * 60:
            continue
        out.append((days, re.sub(r'\s+', ' ', m.group(0)).strip()))
    seen, uniq = set(), []
    for days, phrase in sorted(out, key=lambda t: -t[0]):
        k = round(days)
        if k in seen:
            continue
        seen.add(k)
        uniq.append((days, phrase))
    return uniq


def main(path):
    data = json.load(open(path))
    recs = data['records'] if isinstance(data, dict) and 'records' in data else data
    class_a, class_b = [], []

    for r in recs:
        held = r.get('inCustody') or r.get('awaitingTrial')
        for idx, c in enumerate(r.get('cases') or []):
            ch = c.get('Charges')
            prose = ' '.join(filter(None, [
                c.get('Sentence') or '',
                ' '.join(ch) if isinstance(ch, list) else (ch or ''),
            ]))
            hits = find_durations(prose)
            if not hits:
                continue

            inc_real = parse_day(c.get('Incarceration Date'))
            inc = inc_real or parse_day(c.get('Arrest Date'))
            rel = parse_day(c.get('Release Date'))

            computed = None
            if inc and rel and rel >= inc:
                computed = (rel - inc).days
            elif inc and not rel and held:
                computed = (date(2026, 8, 3) - inc).days

            claimed, phrase = hits[0]
            row = {
                'slug': r.get('slug'), 'name': r.get('name'), 'case': idx,
                'claimed_days': round(claimed), 'phrase': phrase,
                'computed': computed, 'inc': str(inc) if inc else None,
                'rel': str(rel) if rel else None, 'has_inc_date': bool(inc_real),
                'p_days': r.get('imprisonedFor'), 'p_months': r.get('imprisonedForMonths'),
                'ncases': len(r.get('cases') or []), 'prose': prose[:400],
            }
            if computed is None:
                class_a.append(row)
            elif claimed >= 2 and (computed < claimed * 0.5 or computed > claimed * 2.0):
                row['ratio'] = round(computed / claimed, 2)
                class_b.append(row)

    json.dump({'class_a': class_a, 'class_b': class_b},
              open('sweep_result.json', 'w'), indent=1)
    print(f'class A (no computable interval): {len(class_a)}')
    print(f'   with an incarceration date:    '
          f'{sum(1 for r in class_a if r["has_inc_date"])}')
    print(f'class B (computed disagrees):     {len(class_b)}')


if __name__ == '__main__':
    main(sys.argv[1])
