<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

final class CreateArchive1RecordsPage extends Command {
    protected $signature = 'archive:create-archive1-records';
    protected $description = 'Create or refresh the /archive1-records page (collection-detail layout modeled on search.freedomarchives.org/collections/19)';

    public function handle(): int {
        $body = $this->renderBody();

        $page = Page::updateOrCreate(
            ['slug' => 'archive1-records'],
            [
                'title' => 'Records',
                'body' => $body,
            ]
        );

        $this->info("Page saved (id={$page->id}, slug={$page->slug}). View at /archive1-records");

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{label: string, count: int, term?: string}>
     */
    private function filter(string $group): array {
        return match ($group) {
            'collection' => [
                ['label' => 'Black Liberation Movement', 'count' => 131],
                ['label' => 'Anti-Imperialist Tradition', 'count' => 57],
                ['label' => 'Earth & Animal Liberation', 'count' => 39],
                ['label' => 'MOVE Organization', 'count' => 37],
                ['label' => 'Palestine Solidarity', 'count' => 33],
            ],
            'record_type' => [
                ['label' => 'Document', 'count' => 571],
                ['label' => 'Audio', 'count' => 49],
                ['label' => 'Video', 'count' => 24],
            ],
            'source_format' => [
                ['label' => 'Periodical', 'count' => 352],
                ['label' => 'Monograph', 'count' => 50],
                ['label' => 'mp3', 'count' => 44],
                ['label' => 'Flyer', 'count' => 32],
                ['label' => 'Article', 'count' => 22],
            ],
            'year' => [
                ['label' => '1971', 'count' => 51],
                ['label' => '1968', 'count' => 48],
                ['label' => '1972', 'count' => 37],
                ['label' => '1969', 'count' => 35],
                ['label' => '1970', 'count' => 34],
            ],
            'subject' => [
                ['label' => 'Black Liberation', 'count' => 498],
                ['label' => 'Civil Rights', 'count' => 199],
                ['label' => 'Self-Defense', 'count' => 168],
                ['label' => 'Anti-Imperialism', 'count' => 165],
                ['label' => 'COINTELPRO', 'count' => 147],
            ],
            default => [],
        };
    }

    /**
     * @return list<array{title: string, type: 'doc'|'audio'|'video', tags: array<int, array{label: string, value: string}>, blurb: string, expandable?: bool}>
     */
    private function records(): array {
        return [
            ['title' => 'A Benefit – Geronimo Pratt', 'type' => 'doc', 'tags' => [['label' => 'Collection', 'value' => 'Geronimo Ji-Jaga (Pratt)']], 'blurb' => 'Flyer for benefit for Geronimo at La Pena, with filmed interview of Geronimo, speaker on COINTELPRO, and music by Marron.'],
            ['title' => 'Afeni Shakur: Joining Black Panther Party', 'type' => 'audio', 'tags' => [['label' => 'Collection', 'value' => 'Panther 21'], ['label' => 'Authors', 'value' => 'Afeni Shakur']], 'blurb' => 'Afeni Shakur recollects the first time meeting Bobby Seale in New York City who was speaking out against the police, and acknowledges that what attracted her to the Black Panther Party in the beginning was the romanization of liberation struggle.'],
            ['title' => 'Afeni Shakur: Lessons from Panther 21 Trial', 'type' => 'audio', 'tags' => [['label' => 'Collection', 'value' => 'Panther 21'], ['label' => 'Date', 'value' => 'January 27, 1972']], 'blurb' => 'Afeni Shakur draws lessons from the Panther 21 trial and encourages people to support political prisoners by attending court sessions as witnesses.'],
            ['title' => 'Afeni Shakur: Racial Solidarity in Prisons', 'type' => 'audio', 'tags' => [['label' => 'Collection', 'value' => 'Panther 21'], ['label' => 'Date', 'value' => 'January 27, 1972']], 'blurb' => 'Afeni Shakur speaks about the importance of being in solidarity with political prisoners, Sam Melville at Attica prison, how the Attica uprising is a prime example of how racial solidarity can be effective and how even prison guards can be supportive of incarcerated revolutionaries.'],
            ['title' => 'Afeni Shakur: Solidarity during Panther 21 Trial', 'type' => 'audio', 'tags' => [['label' => 'Collection', 'value' => 'Panther 21'], ['label' => 'Date', 'value' => 'January 27, 1972']], 'blurb' => 'Afeni Shakur speaks about the constant support the 21 Panthers received from people who showed up every day to the trials, including school children, and how this affected the jury positively. In addition, she addresses the complex relation between political prisoners and guards.'],
            ['title' => 'Afro-American Dignity News', 'type' => 'doc', 'tags' => [['label' => 'Collection', 'value' => 'Various Black Liberation Movement Publications'], ['label' => 'Date', 'value' => 'November 2, 1964'], ['label' => 'Publishers', 'value' => 'Afro-American Association'], ['label' => 'Volume', 'value' => 'Vol. 1–3']], 'blurb' => 'Local Oakland African-American newspaper. Cover story: integration success without violence.'],
            ['title' => 'Against Revisionism: A Defense of the Black Panther Party, 1966–1970', 'type' => 'doc', 'tags' => [['label' => 'Collection', 'value' => 'Black Panther Party general'], ['label' => 'Publishers', 'value' => 'Venceremos']], 'blurb' => 'First published as a position paper in September 1971. Discusses certain critiques of the Black Panther Party. Venceremos\'s Central Committee, made up of six Third World members and three white members, conducted an investigation of the shifting Oakland Panther line, and particularly of its effects on Venceremos\'s own revolutionary practice. This is a reprint of an internal document.', 'expandable' => true],
            ['title' => 'Ahmed Obafemi: Dedicated New Afrikan Grassroots Organizer', 'type' => 'doc', 'tags' => [['label' => 'Collection', 'value' => 'New Afrika: General'], ['label' => 'Publishers', 'value' => 'New Afrikan Institute']], 'blurb' => 'Includes biographical sketch.'],
        ];
    }

    private function renderFilterGroup(string $title, string $key): string {
        $rows = '';
        foreach ($this->filter($key) as $f) {
            $rows .= sprintf(
                '<a class="a1r-filter-row" href="#"><span>%s</span><span class="a1r-pill">%d</span></a>',
                htmlspecialchars($f['label'], ENT_QUOTES),
                $f['count']
            );
        }

        return sprintf(
            '<div class="a1r-fgroup"><div class="a1r-fhead">%s</div>%s<a href="#" class="a1r-show-more">+ Show more&hellip;</a></div>',
            htmlspecialchars($title, ENT_QUOTES),
            $rows
        );
    }

    private function renderRecord(array $r): string {
        $thumb = match ($r['type']) {
            'audio' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>',
            'video' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>',
            default => '<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>',
        };

        $tagsHtml = '';
        foreach ($r['tags'] as $t) {
            $tagsHtml .= sprintf(
                '<span class="a1r-tag"><span class="a1r-tag-label">%s</span><span class="a1r-tag-value">%s</span></span>',
                htmlspecialchars($t['label'], ENT_QUOTES),
                htmlspecialchars($t['value'], ENT_QUOTES)
            );
        }

        $expand = ! empty($r['expandable'])
            ? '<a class="a1r-view-more" href="#">View more <span style="font-size: 18px;">&#9662;</span></a>'
            : '';

        return sprintf(
            '<article class="a1r-card"><div class="a1r-thumb a1r-thumb-%s">%s</div><div class="a1r-body"><h3>%s</h3><div class="a1r-tags">%s</div><p>%s</p>%s</div></article>',
            htmlspecialchars($r['type'], ENT_QUOTES),
            $thumb,
            htmlspecialchars($r['title'], ENT_QUOTES),
            $tagsHtml,
            htmlspecialchars($r['blurb'], ENT_QUOTES),
            $expand
        );
    }

    private function renderBody(): string {
        $filters = '';
        $filters .= $this->renderFilterGroup('Collection', 'collection');
        $filters .= $this->renderFilterGroup('Record Type', 'record_type');
        $filters .= $this->renderFilterGroup('Source Format', 'source_format');
        $filters .= $this->renderFilterGroup('Year', 'year');
        $filters .= $this->renderFilterGroup('Subject', 'subject');

        $records = '';
        foreach ($this->records() as $r) {
            $records .= $this->renderRecord($r);
        }

        $count = number_format(644);

        return <<<HTML
<style>
  .a1r { --a1-accent: #c2410c; --a1-line: rgba(255,255,255,0.12); --a1-pill-bg: rgba(255,255,255,0.04); }
  .a1r-records-head { font-size: 28px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--a1-accent); margin: 0 0 24px; font-family: 'Verlag', 'Helvetica Neue', sans-serif; }
  .a1r-grid { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 32px; align-items: start; }
  @media (max-width: 900px) { .a1r-grid { grid-template-columns: 1fr; } }
  .a1r-side { border: 1px solid var(--a1-line); border-radius: 6px; padding: 18px; }
  .a1r-side-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--a1-line); }
  .a1r-side-head h4 { margin: 0; font-size: 15px; font-weight: 700; }
  .a1r-clear { background: none; border: none; color: var(--a1-accent); font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer; padding: 0; }
  .a1r-fgroup { padding: 14px 0; border-bottom: 1px solid var(--a1-line); }
  .a1r-fgroup:last-child { border-bottom: none; }
  .a1r-fhead { font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; opacity: 0.6; margin-bottom: 10px; }
  .a1r-filter-row { display: flex; align-items: center; justify-content: space-between; padding: 6px 4px; color: var(--a1-accent); font-size: 14px; text-decoration: none; border-radius: 3px; }
  .a1r-filter-row:hover { background: rgba(194, 65, 12, 0.08); }
  .a1r-pill { background: var(--a1-pill-bg); border: 1px solid var(--a1-line); color: rgba(255,255,255,0.7); border-radius: 999px; padding: 2px 10px; font-size: 11px; font-weight: 600; }
  .a1r-show-more { display: block; padding: 10px 4px 0; color: var(--a1-accent); font-size: 13px; text-decoration: none; }
  .a1r-show-more:hover { text-decoration: underline; }

  .a1r-toolbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 8px; padding: 12px 16px; border: 1px solid var(--a1-line); border-radius: 999px; background: rgba(255,255,255,0.02); }
  .a1r-search { flex: 1; min-width: 240px; display: flex; align-items: center; gap: 10px; }
  .a1r-search svg { opacity: 0.55; }
  .a1r-search input { flex: 1; background: transparent; border: none; outline: none; color: #fff; font-size: 15px; }
  .a1r-search input::placeholder { color: rgba(255,255,255,0.4); }
  .a1r-help { width: 24px; height: 24px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.3); display: inline-flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.6); font-size: 12px; font-weight: 700; text-decoration: none; }
  .a1r-sort { background: transparent; border: 1px solid var(--a1-line); border-radius: 4px; padding: 6px 10px; color: #fff; font-size: 13px; }
  .a1r-toggle { display: inline-flex; align-items: center; gap: 8px; color: var(--a1-accent); font-size: 13px; cursor: pointer; }
  .a1r-toggle input { accent-color: var(--a1-accent); }
  .a1r-count { font-size: 13px; opacity: 0.7; padding: 8px 4px 16px; }

  .a1r-list { display: flex; flex-direction: column; gap: 16px; }
  .a1r-card { display: flex; gap: 18px; padding: 18px; border: 1px solid var(--a1-line); border-radius: 6px; background: rgba(255,255,255,0.02); }
  .a1r-thumb { flex: 0 0 80px; width: 80px; height: 100px; display: flex; align-items: center; justify-content: center; border-radius: 4px; }
  .a1r-thumb-doc { background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); border: 1px solid var(--a1-line); }
  .a1r-thumb-audio, .a1r-thumb-video { background: var(--a1-accent); color: #fff; }
  .a1r-body { flex: 1; min-width: 0; }
  .a1r-body h3 { margin: 0 0 10px; font-size: 18px; font-weight: 700; }
  .a1r-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
  .a1r-tag { display: inline-flex; align-items: stretch; border: 1px solid var(--a1-line); border-radius: 999px; overflow: hidden; font-size: 12px; }
  .a1r-tag-label { background: var(--a1-pill-bg); padding: 3px 9px; opacity: 0.75; font-weight: 600; }
  .a1r-tag-value { padding: 3px 11px; }
  .a1r-body p { margin: 0; font-size: 14px; line-height: 1.55; opacity: 0.85; }
  .a1r-view-more { display: inline-flex; align-items: center; gap: 4px; margin-top: 12px; color: var(--a1-accent); font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; text-decoration: none; }
  .a1r-view-more:hover { text-decoration: underline; }
</style>

<div class="a1r">
  <h2 class="a1r-records-head">Records</h2>

  <div class="a1r-grid">
    <aside class="a1r-side">
      <div class="a1r-side-head">
        <h4>Filter Results</h4>
        <button type="button" class="a1r-clear">&times; Clear filters</button>
      </div>
      {$filters}
    </aside>

    <div>
      <form action="/search" method="GET" role="search" class="a1r-toolbar">
        <label class="a1r-search">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="16.5" y1="16.5" x2="21" y2="21"/></svg>
          <input type="text" name="q" placeholder="Search archives&hellip;">
        </label>
        <a href="/faq" class="a1r-help" title="Search help" aria-label="Search help">?</a>
        <select class="a1r-sort" name="sort">
          <option>Relevance</option>
          <option>Newest first</option>
          <option>Oldest first</option>
          <option>Title A&ndash;Z</option>
        </select>
        <label class="a1r-toggle"><input type="checkbox" name="include_nondigitized"> Include non-digitized records</label>
      </form>

      <div class="a1r-count">Found {$count} records</div>

      <div class="a1r-list">{$records}</div>
    </div>
  </div>
</div>
HTML;
    }
}
