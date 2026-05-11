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

        return '/archive1-records'.($params ? '?'.http_build_query($params) : '');
    };
@endphp

@extends('app')

@section('body')
    <div class="line mt-8"></div>
    <h1 class="text-6xl mt-12">Records</h1>

    <style>
        .a1r { --a1-accent: #c2410c; --a1-line: rgba(255,255,255,0.12); --a1-pill-bg: rgba(255,255,255,0.04); }
        .a1r-grid { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 32px; align-items: start; margin-top: 32px; }
        @media (max-width: 900px) { .a1r-grid { grid-template-columns: 1fr; } }
        .a1r-side { border: 1px solid var(--a1-line); border-radius: 6px; padding: 18px; }
        .a1r-side-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--a1-line); }
        .a1r-side-head h4 { margin: 0; font-size: 15px; font-weight: 700; }
        .a1r-clear { color: var(--a1-accent); font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; text-decoration: none; }
        .a1r-fgroup { padding: 14px 0; border-bottom: 1px solid var(--a1-line); }
        .a1r-fgroup:last-child { border-bottom: none; }
        .a1r-fhead { font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; opacity: 0.6; margin-bottom: 10px; }
        .a1r-filter-row { display: flex; align-items: center; justify-content: space-between; padding: 6px 4px; color: var(--a1-accent); font-size: 14px; text-decoration: none; border-radius: 3px; }
        .a1r-filter-row:hover { background: rgba(194, 65, 12, 0.08); }
        .a1r-filter-row.is-active { background: rgba(194, 65, 12, 0.15); font-weight: 700; }
        .a1r-pill { background: var(--a1-pill-bg); border: 1px solid var(--a1-line); color: rgba(255,255,255,0.7); border-radius: 999px; padding: 2px 10px; font-size: 11px; font-weight: 600; }
        .a1r-empty { font-size: 13px; opacity: 0.5; padding: 4px; font-style: italic; }

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
        .a1r-card:hover { border-color: var(--a1-accent); background: rgba(194, 65, 12, 0.04); }
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
        .a1r-pager { margin-top: 32px; }
        .a1r-empty-state { padding: 48px 24px; text-align: center; opacity: 0.6; border: 1px dashed var(--a1-line); border-radius: 6px; }
    </style>

    <div class="a1r">
        <div class="a1r-grid">
            <aside class="a1r-side">
                <div class="a1r-side-head">
                    <h4>Filter Results</h4>
                    @if ($hasFilters || $collection || $recordType || $sourceFormat || $year || $subject)
                        <a class="a1r-clear" href="/archive1-records">&times; Clear filters</a>
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
                    <div class="a1r-fgroup">
                        <div class="a1r-fhead">{{ $meta['title'] }}</div>
                        @forelse ($facets[$key] ?? [] as $f)
                            @php
                                $isActive = (string) $meta['active'] === (string) $f['label'];
                                $href = $isActive ? $filterUrl([$key => null]) : $filterUrl([$key => $f['label']]);
                            @endphp
                            <a class="a1r-filter-row {{ $isActive ? 'is-active' : '' }}" href="{{ $href }}">
                                <span>{{ ucwords(str_replace('_', ' ', $f['label'])) }}</span>
                                <span class="a1r-pill">{{ $f['count'] }}</span>
                            </a>
                        @empty
                            <div class="a1r-empty">No options yet.</div>
                        @endforelse
                    </div>
                @endforeach
            </aside>

            <div>
                <form action="/archive1-records" method="GET" class="a1r-toolbar">
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
                            <br><br><a class="a1r-clear" href="/archive1-records">Clear filters</a>
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
                            <a class="a1r-card" href="{{ $r->file_url ?: '#' }}" @if ($r->file_url) target="_blank" rel="noopener" @endif>
                                <div class="a1r-thumb a1r-thumb-{{ $r->record_type }}">
                                    @if ($r->thumbnail_url)
                                        <img src="{{ $r->thumbnail_url }}" alt="">
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

                    <div class="a1r-pager">{{ $records->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
