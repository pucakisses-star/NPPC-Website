<?php

namespace App\Http\Controllers;

use App\Models\AnnualReport;
use App\Models\ArchiveRecord;
use App\Models\Article;
use App\Models\CalendarEntry;
use App\Models\Event;
use App\Models\Faq;
use App\Models\HistoryEra;
use App\Models\Page;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Product;
use App\Models\Staff;
use App\Models\Timeline;
use App\Models\Topic;
use Illuminate\Http\Request;

final class SiteController extends Controller {
    public function page(string $slug) {
        // Custom Blade views take priority over DB pages so that
        // admin-created pages (used for nav placement) don't override
        // hand-crafted designs like careers-internships, about, etc.
        if (view()->exists('pages.'.$slug)) {
            return view('pages.'.$slug);
        }

        if ($page = Page::getBySlug($slug)) {
            return view('page', compact('page'));
        }

        abort(404);
    }

    public function timeline() {
        return view('pages.timeline', ['timelines' => Timeline::query()->orderBy('year')->get()]);
    }

    public function archiveRecords(Request $request) {
        $q = trim((string) $request->query('q', ''));
        $collection = $request->query('collection');
        $recordType = $request->query('record_type');
        $sourceFormat = $request->query('source_format');
        $year = $request->query('year');
        $subject = $request->query('subject');
        $sort = $request->query('sort', 'relevance');
        $includeNonDigitized = filter_var($request->query('include_nondigitized'), FILTER_VALIDATE_BOOLEAN);

        $base = ArchiveRecord::published();
        if (! $includeNonDigitized) {
            $base->digitized();
        }

        $facetQuery = (clone $base);
        if ($q !== '') {
            $facetQuery->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('authors', 'like', "%{$q}%")
                    ->orWhere('publisher', 'like', "%{$q}%");
            });
        }

        $countBy = function (string $column) use ($facetQuery) {
            return (clone $facetQuery)
                ->whereNotNull($column)
                ->selectRaw("{$column} as label, COUNT(*) as count")
                ->groupBy($column)
                ->orderByDesc('count')
                ->limit(200)
                ->get()
                ->map(fn ($r) => ['label' => (string) $r->label, 'count' => (int) $r->count])
                ->all();
        };

        // Year facet shows every year sorted DESC by year (not top-N
        // by count) so historical years (1886 Haymarket, 1918 Debs,
        // 1927 Sacco-Vanzetti) aren't knocked out by densely-
        // populated 2010s/2020s buckets.
        $yearFacet = (clone $facetQuery)
            ->whereNotNull('year')
            ->selectRaw('year as label, COUNT(*) as count')
            ->groupBy('year')
            ->orderByDesc('year')
            ->get()
            ->map(fn ($r) => ['label' => (string) $r->label, 'count' => (int) $r->count])
            ->all();

        $collectionFacet = $countBy('collection');

        // Collapse "Anarchist Black Cross — X" sub-collections into a
        // single parent ABC entry whose count is the sum of all
        // chapters. The children are kept on the parent so the
        // template can render them indented.
        $abcChildren = [];
        $abcCount = 0;
        $collectionFacetFiltered = [];
        foreach ($collectionFacet as $f) {
            if ($f['label'] === 'Anarchist Black Cross' || str_starts_with($f['label'], 'Anarchist Black Cross —') || str_starts_with($f['label'], 'Anarchist Black Cross -')) {
                $abcCount += $f['count'];
                if ($f['label'] !== 'Anarchist Black Cross') {
                    $abcChildren[] = $f;
                }
            } else {
                $collectionFacetFiltered[] = $f;
            }
        }
        if ($abcCount > 0) {
            usort($abcChildren, fn ($a, $b) => $b['count'] <=> $a['count']);
            array_unshift($collectionFacetFiltered, [
                'label' => 'Anarchist Black Cross',
                'count' => $abcCount,
                'children' => $abcChildren,
            ]);
        }

        $facets = [
            'collection' => $collectionFacetFiltered,
            'record_type' => $countBy('record_type'),
            'source_format' => $countBy('source_format'),
            'year' => $yearFacet,
        ];

        $subjectCounts = [];
        foreach ((clone $facetQuery)->whereNotNull('subjects')->pluck('subjects') as $list) {
            foreach ((array) $list as $s) {
                $s = trim((string) $s);
                if ($s === '') {
                    continue;
                }
                $subjectCounts[$s] = ($subjectCounts[$s] ?? 0) + 1;
            }
        }
        arsort($subjectCounts);
        $facets['subject'] = array_map(
            fn ($label, $count) => ['label' => (string) $label, 'count' => (int) $count],
            array_keys($subjectCounts),
            array_values($subjectCounts)
        );
        $facets['subject'] = array_slice($facets['subject'], 0, 200);

        $query = (clone $base);
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('authors', 'like', "%{$q}%")
                    ->orWhere('publisher', 'like', "%{$q}%");
            });
        }
        if ($collection) {
            // Filtering by the synthetic ABC parent matches every
            // chapter sub-collection.
            if ($collection === 'Anarchist Black Cross') {
                $query->where(function ($w) {
                    $w->where('collection', 'Anarchist Black Cross')
                        ->orWhere('collection', 'like', 'Anarchist Black Cross —%')
                        ->orWhere('collection', 'like', 'Anarchist Black Cross -%');
                });
            } else {
                $query->where('collection', $collection);
            }
        }
        if ($recordType) {
            $query->where('record_type', $recordType);
        }
        if ($sourceFormat) {
            $query->where('source_format', $sourceFormat);
        }
        if ($year) {
            $query->where('year', (int) $year);
        }
        if ($subject) {
            $query->where('subjects', 'like', '%"'.$subject.'"%');
        }

        match ($sort) {
            'newest' => $query->orderByDesc('date')->orderByDesc('year'),
            'oldest' => $query->orderBy('date')->orderBy('year'),
            'title' => $query->orderBy('title'),
            default => $query->orderBy('sort_order')->orderBy('title'),
        };

        $records = $query->paginate(25)->withQueryString();
        $total = $records->total();

        return view('pages.archive', compact(
            'records',
            'facets',
            'total',
            'q',
            'collection',
            'recordType',
            'sourceFormat',
            'year',
            'subject',
            'sort',
            'includeNonDigitized'
        ));
    }

    public function history() {
        return view('pages.history', ['eras' => HistoryEra::with('topics')->orderBy('sort_order')->get()]);
    }

    public function topics(Request $request, ?string $slug = null) {
        $rootTopics = Topic::published()->roots()->with('children')->orderBy('sort_order')->get();

        $activeTopic = null;
        $activeChild = null;

        if ($slug) {
            // Try to find as root topic
            $activeTopic = Topic::published()->where('slug', $slug)->first();

            if ($activeTopic && $activeTopic->parent_id) {
                // It's a child — find its parent
                $activeChild = $activeTopic;
                $activeTopic = $activeChild->parent;
            }
        }

        if (! $activeTopic && $rootTopics->isNotEmpty()) {
            $activeTopic = $rootTopics->first();
        }

        // Get related prisoners for this topic
        $relatedPrisoners = collect();
        if ($activeTopic || $activeChild) {
            $displayTopic = $activeChild ?: $activeTopic;
            $searchTerms = [strtolower($displayTopic->title)];

            $relatedPrisoners = Prisoner::where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->orWhere('ideologies', 'like', "%{$term}%")
                      ->orWhere('affiliation', 'like', "%{$term}%")
                      ->orWhere('era', 'like', "%{$term}%")
                      ->orWhere('description', 'like', "%{$term}%");
                }
            })->limit(20)->get();
        }

        return view('pages.topics', compact('rootTopics', 'activeTopic', 'activeChild', 'relatedPrisoners'));
    }

    public function birthdays(Request $request) {
        // Currently-incarcerated prisoners with a known birthdate, for
        // the letter-writing birthday calendar.
        $prisoners = Prisoner::whereNotNull('birthdate')
            ->whereNull('death_date')
            ->where('in_custody', true)
            ->orderBy('birthdate')
            ->get();

        $byMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $byMonth[$m] = [];
        }
        foreach ($prisoners as $p) {
            try {
                $d = \Carbon\Carbon::parse($p->birthdate);
            } catch (\Throwable $e) {
                continue;
            }
            $byMonth[(int) $d->month][] = ['prisoner' => $p, 'day' => (int) $d->day];
        }
        foreach ($byMonth as $m => &$entries) {
            usort($entries, fn ($a, $b) => $a['day'] <=> $b['day']);
        }
        unset($entries);

        $month = (int) ($request->input('month', date('n')));
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }

        return view('pages.birthdays', [
            'byMonth' => $byMonth,
            'month' => $month,
            'todayMonth' => (int) date('n'),
            'todayDay' => (int) date('j'),
            'totalCount' => $prisoners->count(),
        ]);
    }

    public function calendar(Request $request) {
        $month = (int) ($request->input('month', date('n')));
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }

        $day = $request->input('day');
        $view = $request->input('view', 'month');

        $entries = CalendarEntry::with('prisoner')
            ->where('month', $month)
            ->where('published', true)
            ->orderBy('day')
            ->orderBy('year')
            ->get();

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $today = (int) date('j');
        $currentMonth = (int) date('n');

        // Day view: collect ALL entries for the selected day (a single
        // date can host multiple historically-significant events; e.g.
        // May 4 = both Kent State 1970 and the Original 13 Freedom
        // Riders 1961).
        $dayEntries = collect();
        $selectedDay = null;
        if ($view === 'day' && $day) {
            $selectedDay = (int) $day;
            $dayEntries = $entries->where('day', $selectedDay)->values();
        }
        if ($view === 'day' && $dayEntries->isEmpty() && $month === $currentMonth) {
            $selectedDay = $today;
            $dayEntries = $entries->where('day', $today)->values();
        }
        if ($view === 'day' && $dayEntries->isEmpty() && $entries->isNotEmpty()) {
            $selectedDay = (int) $entries->first()->day;
            $dayEntries = $entries->where('day', $selectedDay)->values();
        }

        // Back-compat: views that still reference $dayEntry get the first.
        $dayEntry = $dayEntries->first();

        return view('pages.calendar', compact('entries', 'month', 'monthName', 'today', 'currentMonth', 'view', 'dayEntry', 'dayEntries', 'selectedDay'));
    }

    public function store(Request $request) {
        $category = $request->input('category');
        $products = Product::published()->orderBy('sort_order')->get();
        $categories = Product::published()->whereNotNull('category')->where('category', '!=', '')->distinct()->pluck('category');
        $featured = Product::published()->featured()->first();

        return view('pages.store', compact('products', 'categories', 'featured', 'category'));
    }

    public function events(Request $request) {
        $tab = $request->input('tab', 'upcoming');
        $upcoming = Event::published()->upcoming()->get();
        $past = Event::published()->past()->get();
        $series = Event::published()->whereNotNull('series')->where('series', '!=', '')->distinct()->pluck('series');

        return view('pages.events', compact('upcoming', 'past', 'series', 'tab'));
    }

    public function volunteer() {
        return view('pages.volunteer');
    }

    public function prisonerOutreach() {
        $prisoners = Prisoner::where('in_custody', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'name', 'slug', 'last_name', 'first_name']);

        return view('pages.prisoner-outreach', compact('prisoners'));
    }

    public function staff(Request $request) {
        $group = $request->input('group');
        if ($group === 'board') {
            $staff = Staff::getBoardMembers();
        } elseif ($group === 'staff') {
            $staff = Staff::getStaffMembers();
        } else {
            $staff = Staff::where('published', true)->get();
        }

        return view('pages.staff', ['staff' => $staff]);
    }

    public function boardOfDirectors() {
        return view('pages.board-of-directors', ['directors' => Staff::getBoardMembers()]);
    }

    public function partners() {
        $partners = \App\Models\Partner::published()->orderBy('sort_order')->get();

        return view('pages.partners', compact('partners'));
    }

    public function about() {
        return view('pages.about');
    }

    public function signUp(Request $request) {
        $request->validate(['email' => 'required|email']);
        \App\Models\EmailSubscriber::firstOrCreate(['email' => $request->input('email')]);

        return redirect()->back()->with('subscribed', true);
    }

    public function annualReport() {
        return view('pages.annual_reports', ['reports' => AnnualReport::all()]);
    }

    public function map() {
        return view('pages.map');
    }

    public function tracker() {
        // Modern-era cutoff. The pre-WWII archive material is largely
        // labor and anarchist cases whose incarceration day counts and
        // dollar-cost figures are too speculative to mix into a real-time
        // dollar tracker; constrain to 1950→ so the running total tracks
        // the contemporary political-prosecution period the page is
        // actually about.
        // Rolling 50-year window — recomputed on every request so the
        // page always reflects "the past 50 years" of political prosecution
        // rather than a fixed start date that ages out.
        $windowYears = 50;
        $cutoffYear = (int) date('Y') - $windowYears;
        $cutoffDate = $cutoffYear.'-01-01';

        $allPrisoners = Prisoner::all();
        $allCases = PrisonerCase::with('institution')->get();

        // Filter cases to those with an arrest_date on/after the cutoff.
        // A case with no arrest_date is excluded (we can't place it).
        $cases = $allCases->filter(function ($c) use ($cutoffDate) {
            return $c->arrest_date && (string) $c->arrest_date >= $cutoffDate;
        })->values();

        // Filter prisoners to those who have at least one in-scope case.
        $inScopePrisonerIds = $cases->pluck('prisoner_id')->unique()->flip();
        $prisoners = $allPrisoners->filter(fn ($p) => isset($inScopePrisonerIds[$p->id]))->values();

        // ── Cost assumptions ─────────────────────────────────────────────
        // Per-day incarceration rates (blended from Vera Institute's
        // "Price of Prisons" series, BOP's Annual Determination of
        // Average Cost of Incarceration Fee, and BJS county-jail data).
        $federalDailyCost = 130;   // ~$47,400/year — BOP FY24 figure
        $stateDailyCost   = 105;   // ~$38,300/year — Vera 50-state median
        $localDailyCost   = 95;    // ~$34,700/year — BJS jail average

        // Per-case prosecution cost (federal+state felony blend, BJS).
        // Political cases typically run higher — this floor understates.
        $costPerProsecution = 80000;

        // Post-conviction legal cost: appeals, habeas, civil-rights
        // suits. Applied only to cases that resulted in a conviction or
        // sentence, since acquittals/dismissals stop the meter.
        $costPerAppeal = 45000;
        // ─────────────────────────────────────────────────────────────────

        $totalDaysImprisoned = (int) $cases->sum('imprisoned_for_days');
        $totalDaysInExile = (int) $cases->sum('in_exile_for_days');

        $totalPrisoners = $prisoners->count();
        $totalCases = $cases->count();

        // Classify each case as federal / state / local from its
        // institution name, then sum days into the matching bucket.
        $federalDays = 0; $stateDays = 0; $localDays = 0;
        $convictedCases = 0;
        foreach ($cases as $c) {
            $days = (int) ($c->imprisoned_for_days ?? 0);
            $instName = (string) optional($c->institution)->name;
            if ($days > 0) {
                if (preg_match('/\b(federal|FCI|USP|ADX|FMC|FDC|MDC|MCC|FCC|U\.S\.\s*Penit|United States Penit|U\.S\. District|Bureau of Prisons|BOP)\b/i', $instName)) {
                    $federalDays += $days;
                } elseif (preg_match('/\b(county jail|city jail|municipal|MDC|department of correction.*county|holding facility)\b/i', $instName)) {
                    $localDays += $days;
                } else {
                    $stateDays += $days; // default bucket
                }
            }
            $convicted = (string) ($c->convicted ?? '') !== '' || (string) ($c->plead ?? '') !== '' || (string) ($c->sentence ?? '') !== '';
            if ($convicted) $convictedCases++;
        }

        $costFederalIncarceration = $federalDays * $federalDailyCost;
        $costStateIncarceration   = $stateDays   * $stateDailyCost;
        $costLocalIncarceration   = $localDays   * $localDailyCost;
        $costOfProsecution        = $totalCases     * $costPerProsecution;
        $costOfAppeals            = $convictedCases * $costPerAppeal;

        $totalCost = $costFederalIncarceration + $costStateIncarceration + $costLocalIncarceration
                   + $costOfProsecution + $costOfAppeals;

        // Bubbles in the middle of the page — sorted descending for visual hierarchy.
        $costBubbles = collect([
            ['label' => 'Federal incarceration', 'value' => $costFederalIncarceration, 'shade' => 'a'],
            ['label' => 'State incarceration',   'value' => $costStateIncarceration,   'shade' => 'b'],
            ['label' => 'Local jail time',       'value' => $costLocalIncarceration,   'shade' => 'c'],
            ['label' => 'Prosecution',           'value' => $costOfProsecution,        'shade' => 'd'],
            ['label' => 'Appeals & post-conviction', 'value' => $costOfAppeals,        'shade' => 'e'],
        ])->where('value', '>', 0)->sortByDesc('value')->values();

        $costOfIncarceration = $costFederalIncarceration + $costStateIncarceration + $costLocalIncarceration;

        $inCustody = $prisoners->where('in_custody', true)->count();
        $inExile = $prisoners->where('in_exile', true)->count();
        $released = $prisoners->where('released', true)->count();
        $awaitingTrial = $prisoners->where('awaiting_trial', true)->count();

        // Cost by ideology — sum each prisoner's full case cost into
        // every ideology they're tagged with, sort descending.
        $casesByPrisoner = $cases->groupBy('prisoner_id');
        $costByIdeology = [];
        foreach ($prisoners as $p) {
            $set = $casesByPrisoner->get($p->id) ?? collect();
            $cost = 0;
            foreach ($set as $c) {
                $days = (int) ($c->imprisoned_for_days ?? 0);
                $instName = (string) optional($c->institution)->name;
                if (preg_match('/\b(federal|FCI|USP|ADX|FMC|FDC|MDC|MCC|FCC|U\.S\.\s*Penit|United States Penit|U\.S\. District|Bureau of Prisons|BOP)\b/i', $instName)) {
                    $cost += $days * $federalDailyCost;
                } elseif (preg_match('/\b(county jail|city jail|municipal|holding facility)\b/i', $instName)) {
                    $cost += $days * $localDailyCost;
                } else {
                    $cost += $days * $stateDailyCost;
                }
                $cost += $costPerProsecution;
                if ((string) ($c->convicted ?? '') !== '' || (string) ($c->plead ?? '') !== '' || (string) ($c->sentence ?? '') !== '') {
                    $cost += $costPerAppeal;
                }
            }
            if (! $cost) continue;
            foreach (((array) $p->ideologies) ?: ['Unclassified'] as $ideology) {
                $costByIdeology[$ideology] = ($costByIdeology[$ideology] ?? 0) + $cost;
            }
        }
        arsort($costByIdeology);
        $costByIdeology = array_slice($costByIdeology, 0, 10, true);

        // Active cases — currently incarcerated prisoners with their case
        $activeCases = $prisoners->where('in_custody', true)
            ->sortByDesc(fn ($p) => $casesByPrisoner->get($p->id)?->min('arrest_date') ?? '')
            ->take(8)
            ->values();

        // firstYear is fixed at the cutoff — every figure on the page is
        // scoped to cases on or after this date.
        $firstYear = $cutoffYear;

        // Then & Now comparison: earliest documented case (oldest arrest)
        // vs the most recent active case. Both used as visual anchors.
        $earliestCase = $cases->whereNotNull('arrest_date')->sortBy('arrest_date')->first();
        $earliestPrisoner = $earliestCase ? $prisoners->firstWhere('id', $earliestCase->prisoner_id) : null;
        $newestActiveCase = $cases->whereNotNull('arrest_date')->sortByDesc('arrest_date')
            ->first(fn ($c) => optional($prisoners->firstWhere('id', $c->prisoner_id))->in_custody);
        $newestActivePrisoner = $newestActiveCase ? $prisoners->firstWhere('id', $newestActiveCase->prisoner_id) : null;

        // Hero photo strip: 4 prisoners with photos, preferring the
        // currently incarcerated (so the page leads with active cases).
        $heroPrisoners = $prisoners
            ->filter(fn ($p) => ! empty($p->photo))
            ->sortByDesc('in_custody')
            ->take(4)
            ->values();

        return view('pages.tracker', compact(
            'totalDaysImprisoned', 'totalDaysInExile',
            'inCustody', 'inExile', 'released', 'awaitingTrial',
            'costByIdeology', 'activeCases', 'totalPrisoners', 'totalCases', 'firstYear',
            'casesByPrisoner',
            'earliestCase', 'earliestPrisoner', 'newestActiveCase', 'newestActivePrisoner',
            'heroPrisoners',
            'costOfIncarceration', 'costOfProsecution', 'totalCost',
            'costFederalIncarceration', 'costStateIncarceration', 'costLocalIncarceration',
            'costOfAppeals',
            'costBubbles',
            'federalDailyCost', 'stateDailyCost', 'localDailyCost',
            'costPerProsecution', 'costPerAppeal',
        ));
    }

    public function faq() {
        return view('pages.faq');
    }

    public function article(string $slug) {
        $article = Article::getBySlug($slug);

        if (! $article) {
            abort(404);
        }

        return view('article', compact('article'));
    }

    public function search(Request $request) {
        $q = trim($request->input('q', ''));

        if (! $q) {
            return view('pages.search', ['query' => '', 'results' => []]);
        }

        $results = [];

        // Search articles
        $articles = Article::where('title', 'like', "%{$q}%")
            ->orWhere('body', 'like', "%{$q}%")
            ->limit(20)
            ->get();

        foreach ($articles as $article) {
            $results[] = [
                'type'  => 'Article',
                'title' => $article->title,
                'url'   => $article->url,
                'excerpt' => substr(strip_tags($article->body ?? ''), 0, 200),
            ];
        }

        // Search pages
        $pages = Page::where('title', 'like', "%{$q}%")
            ->orWhere('body', 'like', "%{$q}%")
            ->limit(20)
            ->get();

        foreach ($pages as $page) {
            $results[] = [
                'type'  => 'Page',
                'title' => $page->title,
                'url'   => $page->url,
                'excerpt' => substr(strip_tags($page->body ?? ''), 0, 200),
            ];
        }

        // Search prisoners
        $prisoners = Prisoner::where('name', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->orWhere('aka', 'like', "%{$q}%")
            ->limit(20)
            ->get();

        foreach ($prisoners as $prisoner) {
            $results[] = [
                'type'  => 'Prisoner',
                'title' => $prisoner->name,
                'url'   => '/database',
                'excerpt' => substr($prisoner->description ?? '', 0, 200),
            ];
        }

        // Search FAQs
        $faqs = Faq::where('question', 'like', "%{$q}%")
            ->orWhere('answer', 'like', "%{$q}%")
            ->limit(10)
            ->get();

        foreach ($faqs as $faq) {
            $results[] = [
                'type'  => 'FAQ',
                'title' => $faq->question,
                'url'   => '/faq',
                'excerpt' => substr(strip_tags($faq->answer ?? ''), 0, 200),
            ];
        }

        // Search static pages by name
        $staticPages = [
            'history'           => ['title' => 'History', 'url' => '/history'],
            'volunteer'         => ['title' => 'Volunteer', 'url' => '/volunteer'],
            'prisoner outreach' => ['title' => 'Prisoner Outreach', 'url' => '/prisoner-outreach'],
            'staff'             => ['title' => 'Staff', 'url' => '/staff'],
            'board of directors' => ['title' => 'Board of Directors', 'url' => '/board-of-directors'],
            'annual report'     => ['title' => 'Annual Report', 'url' => '/annual-report'],
            'map'               => ['title' => 'Map', 'url' => '/map'],
            'faq'               => ['title' => 'FAQ', 'url' => '/faq'],
            'donate'            => ['title' => 'Donate', 'url' => '/donate'],
            'contact'           => ['title' => 'Contact Us', 'url' => '/contact'],
            'database'          => ['title' => 'Prisoner Database', 'url' => '/database'],
            'news'              => ['title' => 'News', 'url' => '/news'],
        ];

        foreach ($staticPages as $keyword => $page) {
            if (stripos($keyword, $q) !== false) {
                $results[] = [
                    'type'    => 'Page',
                    'title'   => $page['title'],
                    'url'     => $page['url'],
                    'excerpt' => '',
                ];
            }
        }

        return view('pages.search', ['query' => $q, 'results' => $results]);
    }

    public function podcast() {
        $episodes = \App\Models\PodcastEpisode::published()->orderBy('sort_order')->get();

        return view('pages.podcast', compact('episodes'));
    }

    public function archiveView(\App\Models\ArchiveRecord $record) {
        if (! $record->file_url) {
            abort(404);
        }

        return view('pages.archive-view', compact('record'));
    }

    public function petitionsIndex() {
        $petitions = \App\Models\Petition::where('published', true)
            ->withCount('signatures')
            ->orderByDesc('created_at')
            ->get();

        return view('pages.petitions-index', compact('petitions'));
    }

    public function petitionPage(string $slug) {
        $petition = \App\Models\Petition::where('slug', $slug)->where('published', true)->firstOrFail();
        $recentSigners = $petition->signatures()->where('display_publicly', true)->latest()->limit(5)->get();

        return view('pages.petition', compact('petition', 'recentSigners'));
    }

    public function petitionSign(Request $request, string $slug) {
        $petition = \App\Models\Petition::where('slug', $slug)->where('published', true)->firstOrFail();

        $request->validate([
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'email'            => 'required|email|max:255',
            'city'             => 'nullable|string|max:100',
            'state'            => 'nullable|string|max:100',
            'zip_code'         => 'nullable|string|max:20',
            'phone'            => 'nullable|string|max:30',
            'custom_message'   => 'nullable|string|max:2000',
            'display_publicly' => 'nullable|boolean',
            'email_optin'      => 'nullable|boolean',
        ]);

        // Prevent duplicate signatures from the same email
        $alreadySigned = \App\Models\PetitionSignature::where('petition_id', $petition->id)
            ->where('email', $request->input('email'))
            ->exists();

        if ($alreadySigned) {
            return redirect("/petition/{$slug}?signed=true");
        }

        \App\Models\PetitionSignature::create([
            'petition_id'      => $petition->id,
            'first_name'       => $request->input('first_name'),
            'last_name'        => $request->input('last_name'),
            'email'            => $request->input('email'),
            'city'             => $request->input('city'),
            'state'            => $request->input('state'),
            'zip_code'         => $request->input('zip_code'),
            'phone'            => $request->input('phone'),
            'custom_message'   => $request->input('custom_message'),
            'display_publicly' => $request->boolean('display_publicly'),
        ]);

        // Subscribe to the newsletter if the signer opted in.
        if ($request->boolean('email_optin')) {
            \App\Models\EmailSubscriber::firstOrCreate(
                ['email' => $request->input('email')],
                ['status' => 'active']
            );
        }

        return redirect("/petition/{$slug}?signed=true");
    }

    public function prisoner(string $slug) {
        // Try slug first, fall back to ID for backwards compatibility
        $prisoner = Prisoner::with(['cases.institution'])->where('slug', $slug)->first()
            ?? Prisoner::with(['cases.institution'])->findOrFail($slug);

        // Redirect old ID URLs to slug URLs for SEO
        if ($prisoner->slug && $slug !== $prisoner->slug) {
            return redirect('/prisoner/'.$prisoner->slug, 301);
        }

        return view('pages.prisoner', compact('prisoner'));
    }

    public function home() {
        return view('home');
    }
}
