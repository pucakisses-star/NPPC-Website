@php
    use App\Models\ArchiveRecord;
    /** @var \Illuminate\Pagination\LengthAwarePaginator $records */
    /** @var array $facets */
    /** @var int $total */

    $hasFilters = $q !== '' || $collection || $recordType || $sourceFormat || $year || $subject;

    $filterUrl = function (array $overrides) use ($q, $collection, $recordType, $sourceFormat, $year, $subject, $sort, $includeNonDigitized) {
        $params = array_filter([
            'q' => $q,
            'collection' => $collection,
            'record_type' => $recordType,
            'source_format' => $sourceFormat,
            'year' => $year,
            'subject' => $subject,
            'sort' => $sort !== 'relevance' ? $sort : null,
            'include_nondigitized' => $includeNonDigitized ? '1' : null,
        ], fn ($v) => $v !== null && $v !== '');

        foreach ($overrides as $k => $v) {
            if ($v === null || $v === '' || $v === false) {
                unset($params[$k]);
            } else {
                $params[$k] = $v;
            }
        }

        return '/archive-records'.($params ? '?'.http_build_query($params) : '');
    };
@endphp

@extends('app')

@section('body')
    <div class="line mt-8"></div>

    <div class="a1-welcome">
        <b>Welcome to the NPPC Archive.</b> Archive1 is a searchable index of the National Political Prisoner Coalition&rsquo;s records: prisoner profiles, case files, movement periodicals, court documents, and oral histories. It collects, in one place, the materials that document the history of U.S. political imprisonment from the late nineteenth century to the present.
    </div>

    <h1 class="text-6xl mt-12">Records</h1>

    <style>
        .a1r { --a1-accent: #5660fe; --a1-line: rgba(255,255,255,0.12); --a1-pill-bg: rgba(255,255,255,0.04); }
        .a1-welcome { background: rgba(255, 235, 165, 0.08); border: 1px solid rgba(255, 235, 165, 0.25); border-left: 4px solid #f5d061; padding: 20px 24px; border-radius: 4px; margin: 32px 0 16px; font-size: 15px; line-height: 1.65; color: rgba(255,255,255,0.85); }
        .a1-welcome b { font-weight: 800; color: #fff; }
        /* The global app.blade.php sets .container { overflow: hidden }
           which clips the viewport-escape trick below. Override it on
           this page (and on body) so .a1r's 100vw width can render. */
        body.page-archive-records { overflow-x: hidden; }
        body.page-archive-records main.container,
        body.page-archive-records .container { max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; overflow: visible !important; }
        body.page-archive-records .a1r {
            position: relative;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
            width: 100vw;
            max-width: 100vw;
            box-sizing: border-box;
            /* 2-col / mobile mode: inner content max 1032 — middle column 720px */
            padding: 0 max(32px, calc((100vw - 1032px) / 2));
        }
        /* 3-col mode (rail beside main): expand inner content to fit
           the 280 + 32 + 720 + 32 + 280 = 1344 layout. */
        @media (min-width: 1381px) {
            body.page-archive-records .a1r { padding: 0 max(32px, calc((100vw - 1344px) / 2)); }
        }

        /* On wide enough screens, place the right rail next to the main
           column. Below 1380 the window can't fit (280 + 32 + 720 + 32 + 280 + padding)
           without squeezing, so drop the rail underneath. */
        .a1r-grid { display: grid; grid-template-columns: 280px minmax(0, 720px) 280px; gap: 32px; align-items: start; margin-top: 32px; justify-content: center; }
        @media (max-width: 1380px) { .a1r-grid { grid-template-columns: 280px minmax(0, 720px); } .a1r-right { grid-column: 1 / -1; } }
        @media (max-width: 900px) {
            .a1r-grid { grid-template-columns: 1fr; }
            body.page-archive-records .a1r { padding-left: 16px; padding-right: 16px; }
        }

        /* Right rail */
        .a1r-right { display: flex; flex-direction: column; gap: 36px; }
        .a1r-rhead { font-size: 22px; font-weight: 900; letter-spacing: 0.08em; text-transform: uppercase; color: var(--a1-accent); margin: 0 0 18px; line-height: 1.1; }
        .a1r-tags { display: flex; flex-wrap: wrap; gap: 10px 14px; }
        .a1r-tags a { color: var(--a1-accent); text-decoration: none; font-weight: 800; line-height: 1.2; transition: opacity 0.15s; }
        .a1r-tags a:hover { opacity: 0.7; }
        .a1r-tags .t-xl { font-size: 22px; }
        .a1r-tags .t-lg { font-size: 18px; }
        .a1r-tags .t-md { font-size: 15px; }
        .a1r-tags .t-sm { font-size: 13px; font-weight: 700; }
        .a1r-tags .t-xs { font-size: 12px; font-weight: 700; }

        .a1r-divider { height: 1px; background: var(--a1-line); margin: 8px 0; }

        .a1r-feat-list { display: flex; flex-direction: column; }
        .a1r-feat { padding: 14px 0; border-top: 1px solid var(--a1-line); display: block; text-decoration: none; color: inherit; }
        .a1r-feat:first-child { border-top: 0; padding-top: 0; }
        .a1r-feat:hover .a1r-feat-title { color: #fff; }
        .a1r-feat-title { font-size: 15px; font-weight: 800; color: var(--a1-accent); line-height: 1.25; margin-bottom: 6px; }
        .a1r-feat-desc { font-size: 12.5px; color: rgba(255,255,255,0.55); line-height: 1.55; }
        .a1r-side { border: 1px solid var(--a1-line); border-radius: 6px; padding: 18px; }
        .a1r-side-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--a1-line); }
        .a1r-side-head h4 { margin: 0; font-size: 15px; font-weight: 700; }
        .a1r-clear { color: var(--a1-accent); font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; text-decoration: none; }
        .a1r-fgroup { padding: 14px 0; border-bottom: 1px solid var(--a1-line); }
        .a1r-fgroup:last-child { border-bottom: none; }
        .a1r-fhead { font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; opacity: 0.6; margin-bottom: 10px; }
        .a1r-filter-row { display: flex; align-items: center; justify-content: space-between; padding: 6px 4px; color: var(--a1-accent); font-size: 14px; text-decoration: none; border-radius: 3px; }
        .a1r-filter-row:hover { background: rgba(86, 96, 254, 0.08); }
        .a1r-filter-row.is-active { background: rgba(86, 96, 254, 0.15); font-weight: 700; }
        .a1r-pill { background: var(--a1-pill-bg); border: 1px solid var(--a1-line); color: rgba(255,255,255,0.7); border-radius: 999px; padding: 2px 10px; font-size: 11px; font-weight: 600; }
        .a1r-empty { font-size: 13px; opacity: 0.5; padding: 4px; font-style: italic; }

        /* All facet groups: hide rows past the 20th until expanded */
        .a1r-fgroup .a1r-filter-row:nth-of-type(n+21) { display: none; }
        .a1r-fgroup.is-expanded .a1r-filter-row { display: flex; }
        /* Hierarchical sub-collections (e.g. Anarchist Black Cross — *) */
        .a1r-has-children::after { content: '▾'; margin-left: 6px; opacity: 0.4; font-size: 11px; }
        .a1r-children { display: none; padding-left: 14px; border-left: 1px solid rgba(86, 96, 254, 0.2); margin-left: 4px; }
        .a1r-children.is-open { display: block; }
        .a1r-child-row { font-size: 13px; opacity: 0.85; }
        .a1r-child-row:hover { opacity: 1; }
        .a1r-filter-row.is-parent-active { background: rgba(86, 96, 254, 0.06); }
        .a1r-show-more { background: transparent; border: none; color: var(--a1-accent); font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; padding: 6px 4px; cursor: pointer; margin-top: 2px; }
        .a1r-show-more:hover { color: #fff; }
        .a1r-show-more[hidden] { display: none; }

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
        .a1r-card { display: flex; gap: 18px; padding: 18px; border: 1px solid var(--a1-line); border-radius: 6px; background: rgba(255,255,255,0.02); color: inherit; text-decoration: none; transition: border-color 0.15s ease, background 0.15s ease; }
        .a1r-card:hover { border-color: var(--a1-accent); background: rgba(86, 96, 254, 0.04); }
        .a1r-thumb { flex: 0 0 80px; width: 80px; height: 100px; display: flex; align-items: center; justify-content: center; border-radius: 4px; overflow: hidden; }
        .a1r-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .a1r-thumb-doc { background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); border: 1px solid var(--a1-line); }
        .a1r-thumb-audio, .a1r-thumb-video { background: var(--a1-accent); color: #fff; }
        .a1r-body { flex: 1; min-width: 0; }
        .a1r-body h3 { margin: 0 0 10px; font-size: 18px; font-weight: 700; }
        .a1r-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
        .a1r-tag { display: inline-flex; align-items: stretch; border: 1px solid var(--a1-line); border-radius: 999px; overflow: hidden; font-size: 12px; }
        .a1r-tag-label { background: var(--a1-pill-bg); padding: 3px 9px; opacity: 0.75; font-weight: 600; }
        .a1r-tag-value { padding: 3px 11px; }
        .a1r-body p { margin: 0; font-size: 14px; line-height: 1.55; opacity: 0.85; }
        .a1r-pager { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--a1-line); }
        .a1r-pager-info { font-size: 13px; opacity: 0.7; }
        .a1r-pager-info strong { color: #fff; font-weight: 700; }
        .a1r-pager-list { display: flex; align-items: center; gap: 4px; list-style: none; padding: 0; margin: 0; flex-wrap: wrap; }
        .a1r-pager-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border: 1px solid var(--a1-line); border-radius: 4px; color: #fff; text-decoration: none; font-size: 14px; font-weight: 600; transition: border-color 0.15s ease, background 0.15s ease; }
        .a1r-pager-btn:hover { border-color: var(--a1-accent); background: rgba(86, 96, 254, 0.08); }
        .a1r-pager-btn.is-active { background: var(--a1-accent); border-color: var(--a1-accent); color: #fff; }
        .a1r-pager-btn.is-disabled { opacity: 0.35; cursor: not-allowed; }
        .a1r-pager-btn.is-disabled:hover { border-color: var(--a1-line); background: transparent; }
        .a1r-pager-ellipsis { display: inline-flex; align-items: center; justify-content: center; min-width: 24px; height: 36px; color: rgba(255,255,255,0.4); font-size: 14px; }
        .a1r-empty-state { padding: 48px 24px; text-align: center; opacity: 0.6; border: 1px dashed var(--a1-line); border-radius: 6px; }
        @media (max-width: 768px) {
            .a1r-toolbar { border-radius: 12px; padding: 12px; gap: 8px; }
            .a1r-card { flex-direction: column; gap: 12px; padding: 14px; }
            .a1r-pager { gap: 8px; }
            .a1r-pager-btn { min-width: 32px; height: 32px; font-size: 13px; padding: 0 8px; }
        }
        @media (max-width: 420px) {
            .a1r-toolbar { padding: 10px 12px; }
        }
    </style>

    <div class="a1r">
        <div class="a1r-grid">
            <aside class="a1r-side">
                <div class="a1r-side-head">
                    <h4>Filter Results</h4>
                    @if ($hasFilters || $collection || $recordType || $sourceFormat || $year || $subject)
                        <a class="a1r-clear" href="/archive-records">&times; Clear filters</a>
                    @endif
                </div>

                @php
                    $groups = [
                        'collection' => ['title' => 'Collection', 'active' => $collection],
                        'record_type' => ['title' => 'Record Type', 'active' => $recordType],
                        'source_format' => ['title' => 'Source Format', 'active' => $sourceFormat],
                        'year' => ['title' => 'Year', 'active' => $year],
                        'subject' => ['title' => 'Subject', 'active' => $subject],
                    ];
                @endphp

                @foreach ($groups as $key => $meta)
                    @php $facetItems = $facets[$key] ?? []; @endphp
                    <div class="a1r-fgroup">
                        <div class="a1r-fhead">{{ $meta['title'] }}</div>
                        @forelse ($facetItems as $f)
                            @php
                                $isActive = (string) $meta['active'] === (string) $f['label'];
                                $href = $isActive ? $filterUrl([$key => null]) : $filterUrl([$key => $f['label']]);
                                $children = $f['children'] ?? [];
                                $hasChildExpanded = false;
                                foreach ($children as $c) {
                                    if ((string) $meta['active'] === (string) $c['label']) {
                                        $hasChildExpanded = true;
                                    }
                                }
                            @endphp
                            <a class="a1r-filter-row {{ $isActive ? 'is-active' : '' }} {{ ! empty($children) ? 'a1r-has-children' : '' }} {{ $hasChildExpanded ? 'is-parent-active' : '' }}" href="{{ $href }}">
                                <span>{{ ucwords(str_replace('_', ' ', $f['label'])) }}</span>
                                <span class="a1r-pill">{{ $f['count'] }}</span>
                            </a>
                            @if (! empty($children))
                                <div class="a1r-children {{ ($isActive || $hasChildExpanded) ? 'is-open' : '' }}">
                                    @foreach ($children as $c)
                                        @php
                                            $cActive = (string) $meta['active'] === (string) $c['label'];
                                            $cLabel = trim(preg_replace('/^Anarchist Black Cross\s*[—\-]\s*/u', '', (string) $c['label']));
                                            $cHref = $cActive ? $filterUrl([$key => null]) : $filterUrl([$key => $c['label']]);
                                        @endphp
                                        <a class="a1r-filter-row a1r-child-row {{ $cActive ? 'is-active' : '' }}" href="{{ $cHref }}">
                                            <span>{{ $cLabel }}</span>
                                            <span class="a1r-pill">{{ $c['count'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        @empty
                            <div class="a1r-empty">No options yet.</div>
                        @endforelse
                        @if (count($facetItems) > 20)
                            <button type="button" class="a1r-show-more" data-show-more
                                data-more="Show {{ count($facetItems) - 20 }} more"
                                data-less="Show less">
                                Show {{ count($facetItems) - 20 }} more
                            </button>
                        @endif
                    </div>
                @endforeach
                <script>
                    document.querySelectorAll('[data-show-more]').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const group = btn.closest('.a1r-fgroup');
                            const expanded = group.classList.toggle('is-expanded');
                            btn.textContent = expanded ? btn.dataset.less : btn.dataset.more;
                        });
                    });
                </script>
            </aside>

            <div>
                <form action="/archive-records" method="GET" class="a1r-toolbar">
                    <label class="a1r-search">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="16.5" y1="16.5" x2="21" y2="21"/></svg>
                        <input type="text" name="q" placeholder="Search archives&hellip;" value="{{ $q }}">
                    </label>
                    <a href="/faq" class="a1r-help" title="Search help" aria-label="Search help">?</a>
                    <select class="a1r-sort" name="sort" onchange="this.form.submit()">
                        <option value="relevance" @selected($sort === 'relevance')>Relevance</option>
                        <option value="newest" @selected($sort === 'newest')>Newest first</option>
                        <option value="oldest" @selected($sort === 'oldest')>Oldest first</option>
                        <option value="title" @selected($sort === 'title')>Title A&ndash;Z</option>
                    </select>
                    <label class="a1r-toggle">
                        <input type="checkbox" name="include_nondigitized" value="1" @checked($includeNonDigitized) onchange="this.form.submit()">
                        Include non-digitized records
                    </label>
                    @foreach (['collection' => $collection, 'record_type' => $recordType, 'source_format' => $sourceFormat, 'year' => $year, 'subject' => $subject] as $k => $v)
                        @if ($v)
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endif
                    @endforeach
                </form>

                <div class="a1r-count">Found {{ number_format($total) }} {{ \Illuminate\Support\Str::plural('record', $total) }}</div>

                @if ($total === 0)
                    <div class="a1r-empty-state">
                        No records found.
                        @if ($hasFilters)
                            <br><br><a class="a1r-clear" href="/archive-records">Clear filters</a>
                        @else
                            <br><br><span style="font-size: 13px;">Once you upload archive records in the admin panel, they will appear here.</span>
                        @endif
                    </div>
                @else
                    <div class="a1r-list">
                        @foreach ($records as $r)
                            @php
                                $tags = array_filter([
                                    $r->collection ? ['label' => 'Collection', 'value' => $r->collection] : null,
                                    $r->date ? ['label' => 'Date', 'value' => $r->date->format('F j, Y')] : ($r->year ? ['label' => 'Year', 'value' => $r->year] : null),
                                    $r->authors ? ['label' => 'Authors', 'value' => $r->authors] : null,
                                    $r->publisher ? ['label' => 'Publishers', 'value' => $r->publisher] : null,
                                    $r->volume ? ['label' => 'Volume', 'value' => $r->volume] : null,
                                ]);
                            @endphp
                            @php
                                $isPdf = $r->file && str_ends_with(strtolower($r->file), '.pdf');
                                $cardHref = $r->file_url
                                    ? ($isPdf ? '/archive/view/'.$r->id : $r->file_url)
                                    : '#';
                                $cardNewTab = $r->file_url && ! $isPdf;
                            @endphp
                            <a class="a1r-card" href="{{ $cardHref }}" @if ($cardNewTab) target="_blank" rel="noopener" @endif>
                                <div class="a1r-thumb a1r-thumb-{{ $r->record_type }}">
                                    @if ($r->thumbnail_url)
                                        <img src="{{ $r->thumbnail_url }}" alt="" loading="lazy" decoding="async">
                                    @elseif ($r->record_type === 'audio')
                                        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                                    @elseif ($r->record_type === 'video')
                                        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                                    @else
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                    @endif
                                </div>
                                <div class="a1r-body">
                                    <h3>{{ $r->title }}</h3>
                                    @if (count($tags))
                                        <div class="a1r-tags">
                                            @foreach ($tags as $t)
                                                <span class="a1r-tag"><span class="a1r-tag-label">{{ $t['label'] }}</span><span class="a1r-tag-value">{{ $t['value'] }}</span></span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($r->description)
                                        <p>{{ \Illuminate\Support\Str::limit($r->description, 360) }}</p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>

                    @if ($records->hasPages())
                        <nav class="a1r-pager" role="navigation" aria-label="Pagination">
                            <span class="a1r-pager-info">
                                Showing
                                <strong>{{ number_format($records->firstItem()) }}</strong>&ndash;<strong>{{ number_format($records->lastItem()) }}</strong>
                                of <strong>{{ number_format($records->total()) }}</strong>
                            </span>
                            <ul class="a1r-pager-list">
                                @if ($records->onFirstPage())
                                    <li class="a1r-pager-btn is-disabled" aria-disabled="true">&lsaquo;</li>
                                @else
                                    <li><a class="a1r-pager-btn" href="{{ $records->previousPageUrl() }}" rel="prev" aria-label="Previous page">&lsaquo;</a></li>
                                @endif

                                @php
                                    $current = $records->currentPage();
                                    $last = $records->lastPage();
                                    $window = 2;
                                    $pages = [];
                                    $pages[] = 1;
                                    for ($i = max(2, $current - $window); $i <= min($last - 1, $current + $window); $i++) {
                                        $pages[] = $i;
                                    }
                                    if ($last > 1) {
                                        $pages[] = $last;
                                    }
                                    $pages = array_values(array_unique($pages));
                                    sort($pages);
                                    $prev = null;
                                @endphp
                                @foreach ($pages as $page)
                                    @if ($prev !== null && $page - $prev > 1)
                                        <li class="a1r-pager-ellipsis" aria-hidden="true">&hellip;</li>
                                    @endif
                                    @if ($page === $current)
                                        <li class="a1r-pager-btn is-active" aria-current="page">{{ $page }}</li>
                                    @else
                                        <li><a class="a1r-pager-btn" href="{{ $records->url($page) }}">{{ $page }}</a></li>
                                    @endif
                                    @php $prev = $page; @endphp
                                @endforeach

                                @if ($records->hasMorePages())
                                    <li><a class="a1r-pager-btn" href="{{ $records->nextPageUrl() }}" rel="next" aria-label="Next page">&rsaquo;</a></li>
                                @else
                                    <li class="a1r-pager-btn is-disabled" aria-disabled="true">&rsaquo;</li>
                                @endif
                            </ul>
                        </nav>
                    @endif
                @endif
            </div>

            <aside class="a1r-right">
                <section>
                    <h3 class="a1r-rhead">Common<br>Search Terms</h3>
                    <div class="a1r-tags">
                        <a class="t-xl" href="/archive-records?q=political+prisoners">Political Prisoners</a>
                        <a class="t-xl" href="/archive-records?q=black+liberation">Black Liberation</a>
                        <a class="t-xl" href="/archive-records?q=national+liberation">National Liberation</a>
                        <a class="t-lg" href="/archive-records?q=anti-imperialism">Anti-Imperialism</a>
                        <a class="t-lg" href="/archive-records?q=resistance">Resistance</a>
                        <a class="t-lg" href="/archive-records?q=prison">Prison</a>
                        <a class="t-md" href="/archive-records?q=cointelpro">COINTELPRO</a>
                        <a class="t-md" href="/archive-records?q=civil+rights">Civil Rights</a>
                        <a class="t-md" href="/archive-records?q=latin+america">Latin America</a>
                        <a class="t-md" href="/archive-records?q=palestine">Palestine</a>
                        <a class="t-md" href="/archive-records?q=puerto+rico">Puerto Rico</a>
                        <a class="t-md" href="/archive-records?q=women">Women</a>
                        <a class="t-sm" href="/archive-records?q=anti-racism">Anti-Racism</a>
                        <a class="t-sm" href="/archive-records?q=black+panther+party">Black Panther Party</a>
                        <a class="t-sm" href="/archive-records?q=poetry">Poetry</a>
                        <a class="t-sm" href="/archive-records?q=human+rights">Human Rights</a>
                        <a class="t-sm" href="/archive-records?q=anti-war">Anti-War</a>
                        <a class="t-sm" href="/archive-records?q=africa">Africa</a>
                        <a class="t-sm" href="/archive-records?q=african+american">African American</a>
                        <a class="t-sm" href="/archive-records?q=cuba">Cuba</a>
                        <a class="t-xs" href="/archive-records?q=mexico">Mexico</a>
                        <a class="t-xs" href="/archive-records?q=nicaragua">Nicaragua</a>
                        <a class="t-xs" href="/archive-records?q=vietnam">Viet Nam</a>
                        <a class="t-xs" href="/archive-records?q=middle+east">Middle East</a>
                        <a class="t-xs" href="/archive-records?q=chicano">Chicano</a>
                        <a class="t-xs" href="/archive-records?q=israel">Israel</a>
                        <a class="t-xs" href="/archive-records?q=chile">Chile</a>
                        <a class="t-xs" href="/archive-records?q=music">Music</a>
                    </div>
                </section>

                <div class="a1r-divider"></div>

                <section>
                    <h3 class="a1r-rhead">Featured<br>Content</h3>
                    <div class="a1r-feat-list">
                        <a class="a1r-feat" href="/prisoner/mumia-abu-jamal">
                            <div class="a1r-feat-title">Mumia Abu-Jamal</div>
                            <div class="a1r-feat-desc">Award-winning journalist and former Black Panther; on death row from 1982 until 2011 and serving life since.</div>
                        </a>
                        <a class="a1r-feat" href="/prisoner/leonard-peltier">
                            <div class="a1r-feat-title">Leonard Peltier</div>
                            <div class="a1r-feat-desc">American Indian Movement member imprisoned since 1977 in a case the FBI itself has been forced to acknowledge as flawed.</div>
                        </a>
                        <a class="a1r-feat" href="/prisoner/ojore-lutalo">
                            <div class="a1r-feat-title">Ojore Lutalo</div>
                            <div class="a1r-feat-desc">New Afrikan anarchist; served 28 years in New Jersey, much of it in solitary; now an acclaimed collage artist.</div>
                        </a>
                        <a class="a1r-feat" href="/prisoner/oscar-lopez-rivera">
                            <div class="a1r-feat-title">Oscar L&oacute;pez Rivera</div>
                            <div class="a1r-feat-desc">Puerto Rican independentista who served 36 years before President Obama commuted his sentence in 2017.</div>
                        </a>
                        <a class="a1r-feat" href="/archive-records?collection=4StruggleMag">
                            <div class="a1r-feat-title">Movement Press Archive</div>
                            <div class="a1r-feat-desc">Digitized issues of 4StruggleMag and other prisoner-support periodicals, going back to the 1970s.</div>
                        </a>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
