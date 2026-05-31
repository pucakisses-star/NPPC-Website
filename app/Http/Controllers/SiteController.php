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
use App\Support\IncarcerationCostRates;
use Carbon\Carbon;
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
        // Sections hidden from the topics explorer. They stay in the database
        // but are excluded from the nav, the A–Z index, and direct URLs.
        $hiddenRootSlugs = ['repressive-tools'];
        $hiddenRootIds = Topic::whereIn('slug', $hiddenRootSlugs)->pluck('id');

        $rootTopics = Topic::published()->roots()
            ->whereNotIn('slug', $hiddenRootSlugs)
            ->with('children')->orderBy('sort_order')->get();

        $activeTopic = null;
        $activeChild = null;
        $showIndex = ($slug === 'index');
        $indexGroups = collect();

        if ($showIndex) {
            // Alphabetical index of every sub-topic (leaf), grouped by first
            // letter. A leading article ("The ...") is ignored for sorting.
            $indexGroups = Topic::published()
                ->whereNotNull('parent_id')
                ->whereNotIn('parent_id', $hiddenRootIds)
                ->get()
                ->sortBy(fn ($t) => $this->indexSortKey($t->title), SORT_NATURAL | SORT_FLAG_CASE)
                ->groupBy(fn ($t) => strtoupper(mb_substr($this->indexSortKey($t->title), 0, 1)));
        } elseif ($slug) {
            // Try to find as root topic. A hidden section resolves to nothing
            // and falls back to the default first section below.
            $activeTopic = Topic::published()
                ->whereNotIn('slug', $hiddenRootSlugs)
                ->where('slug', $slug)->first();

            if ($activeTopic && $activeTopic->parent_id) {
                // It's a child — hide it too if it belongs to a hidden section.
                if ($hiddenRootIds->contains($activeTopic->parent_id)) {
                    $activeTopic = null;
                } else {
                    $activeChild = $activeTopic;
                    $activeTopic = $activeChild->parent;
                }
            }
        }

        if (! $showIndex && ! $activeTopic && $rootTopics->isNotEmpty()) {
            $activeTopic = $rootTopics->first();
        }

        // Related prisoners — only for leaf topics (sub-topics / content
        // pages). Section pages and the Introduction have children or are
        // overviews, so they show their essay rather than a case list.
        $relatedPrisoners = collect();
        $displayTopic = $activeChild ?: $activeTopic;
        if ($displayTopic && $displayTopic->children->isEmpty()) {
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

        return view('pages.topics', compact('rootTopics', 'activeTopic', 'activeChild', 'relatedPrisoners', 'showIndex', 'indexGroups'));
    }

    /** Sort/group key for the topic index: drops a leading article. */
    private function indexSortKey(string $title): string {
        return ltrim(preg_replace('/^(the|a|an)\s+/i', '', trim($title)));
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

        // ── Cost model ────────────────────────────────────────────────
        // Year-aware, state-aware. See App\Support\IncarcerationCostRates
        // for the underlying data tables (state DOC budgets, BOP Federal
        // Register annual rates, BJS jail averages) and the year-of-
        // incarceration adjustment factor.
        // ─────────────────────────────────────────────────────────────

        $totalDaysImprisoned = (int) $cases->sum('imprisoned_for_days');
        $totalDaysInExile = (int) $cases->sum('in_exile_for_days');

        $totalPrisoners = $prisoners->count();
        $totalCases = $cases->count();

        // Helper: classify a case into federal / state / local from its
        // institution (preferring the structured institution_id columns,
        // falling back to name regex if state is empty on the row).
        $classify = function ($case): array {
            $inst = $case->institution;
            $name = (string) optional($inst)->name;
            $state = strtoupper((string) optional($inst)->state);

            if ($name !== '' && preg_match('/\b(federal|FCI|USP|ADX|FMC|FDC|MDC|MCC|FCC|U\.S\.\s*Penit|United States Penit|U\.S\. District|Bureau of Prisons|BOP)\b/i', $name)) {
                return ['bucket' => 'federal', 'state' => null];
            }
            if ($name !== '' && preg_match('/\b(county jail|city jail|municipal|holding facility)\b/i', $name)) {
                return ['bucket' => 'local', 'state' => $state ?: null];
            }
            return ['bucket' => 'state', 'state' => $state ?: null];
        };

        $costFederalIncarceration = 0.0;
        $costStateIncarceration   = 0.0;
        $costLocalIncarceration   = 0.0;
        $costOfInvestigation      = 0.0;
        $costOfProsecution        = 0.0;
        $costOfAppeals            = 0.0;
        $federalDays = 0; $stateDays = 0; $localDays = 0;
        $convictedCases = 0;

        // Charge-frequency taxonomy: each entry is [label, regex]. A case
        // can match more than one. We tally how many cases hit each
        // category and the total per-case cost across them, then derive
        // an average cost per case at the end.
        $chargeCats = [
            ['Material support',            '/material\s+support|providing\s+support/i'],
            ['Conspiracy',                  '/conspirac|conspir(e|ed|ing)/i'],
            ['Murder / attempted murder',   '/murder|homicide|manslaughter/i'],
            ['Assault',                     '/assault|battery|aggravated\s+(assault|battery)/i'],
            ['Firearms / weapons',          '/firearm|weapon|unlawful\s+use\s+of\s+a?\s*(weapon|firearm)|possession\s+of\s+a?\s*(weapon|firearm)/i'],
            ['Explosives / bombing',        '/explosive|bomb|incendiary|destructive\s+device|arson/i'],
            ['Seditious conspiracy',        '/sedition|seditious/i'],
            ['Espionage',                   '/espionage|spy|classified|national\s+defense\s+information|18\s*u\.?s\.?c\.?\s*793|794/i'],
            ['Racketeering / RICO',         '/rico|racketeer|continuing\s+criminal\s+enterprise|\bcce\b/i'],
            ['Drug offenses',               '/drug|narcotic|controlled\s+substance|trafficking|distribution\s+of\s+(cocaine|heroin|marijuana)/i'],
            ['Robbery / expropriation',     '/robbery|bank\s+(robbery|expropriat)|armed\s+robbery|expropriat/i'],
            ['Property destruction / sabotage', '/sabotage|destruction\s+of\s+(government\s+)?property|criminal\s+mischief|vandalism/i'],
            ['Kidnapping',                  '/kidnap|abduction|hostage/i'],
            ['Immigration violations',      '/immigration|unlawful\s+(entry|reentry|presence)|visa\s+fraud|harbor(ing)?\s+aliens?/i'],
            ['Fraud / financial',           '/fraud|money\s+launder|embezzle|wire\s+fraud|mail\s+fraud|financial/i'],
            ['False statements / perjury',  '/false\s+statement|perjury|lying\s+to|making\s+false/i'],
            ['Obstruction / contempt',      '/obstruct|contempt|interfer(e|ing)\s+with/i'],
            ['Trespass / disorderly',       '/trespass|disorderly|unlawful\s+assembly|disturbing\s+the\s+peace/i'],
            ['Tax violations',              '/tax\s+(evasion|fraud|violation)|failure\s+to\s+(file|pay)\s+tax/i'],
            ['Theft / stolen property',     '/theft|larceny|stolen\s+property|burglar|receiving\s+stolen/i'],
        ];
        $chargeCount = array_fill_keys(array_column($chargeCats, 0), 0);
        $chargeCost  = array_fill_keys(array_column($chargeCats, 0), 0.0);

        foreach ($cases as $c) {
            $cls = $classify($c);
            $bucket = $cls['bucket'];
            $state  = $cls['state'];

            $days = (int) ($c->imprisoned_for_days ?? 0);
            if ($days > 0) {
                if ($bucket === 'federal')   $federalDays += $days;
                elseif ($bucket === 'local') $localDays   += $days;
                else                          $stateDays   += $days;
            }

            // Anchor dates: incarceration_date → release_date (or today),
            // falling back to arrest_date if incarceration_date is null.
            $start = $c->incarceration_date ?: $c->arrest_date;
            $end   = $c->release_date ?: null;

            $startC = $start ? Carbon::parse($start) : null;
            $endC   = $end   ? Carbon::parse($end)   : null;

            $incCost = IncarcerationCostRates::costForPeriod($bucket, $state, $startC, $endC, $days);
            if ($bucket === 'federal')   $costFederalIncarceration += $incCost;
            elseif ($bucket === 'local') $costLocalIncarceration   += $incCost;
            else                         $costStateIncarceration   += $incCost;

            // Prosecution + appeals priced by charge tier (capital,
            // complex federal, federal felony, state violent / non-
            // violent, misdemeanor) AND year so an old or low-severity
            // case isn't billed at modern capital-trial rates.
            $arrestYear = $c->arrest_date ? (int) Carbon::parse($c->arrest_date)->year : (int) date('Y');
            $costOfInvestigation += IncarcerationCostRates::investigationCost($bucket, $c->charges, $c->sentence, $arrestYear);
            $costOfProsecution += IncarcerationCostRates::prosecutionCost($bucket, $c->charges, $c->sentence, $arrestYear);

            $convicted = (string) ($c->convicted ?? '') !== ''
                || (string) ($c->plead ?? '') !== ''
                || (string) ($c->sentence ?? '') !== '';
            $caseAppeals = 0.0;
            if ($convicted) {
                $caseAppeals = IncarcerationCostRates::appealsCost($bucket, $c->charges, $c->sentence, $arrestYear);
                $costOfAppeals += $caseAppeals;
                $convictedCases++;
            }

            // Per-charge average uses the PROSECUTION cost only — what the
            // trial itself costs — not the all-in incarceration total.
            $caseProsecution = IncarcerationCostRates::prosecutionCost($bucket, $c->charges, $c->sentence, $arrestYear);

            // Tally charge categories this case matches.
            $chargeText = (string) ($c->charges ?? '');
            if ($chargeText !== '') {
                foreach ($chargeCats as [$label, $regex]) {
                    if (preg_match($regex, $chargeText)) {
                        $chargeCount[$label]++;
                        $chargeCost[$label] += $caseProsecution;
                    }
                }
            }
        }

        // Build the charge-frequency dataset: count + average per-case cost,
        // sorted by count descending, dropping any category with no hits.
        $chargeStats = [];
        foreach ($chargeCount as $label => $count) {
            if ($count <= 0) continue;
            $chargeStats[] = [
                'label' => $label,
                'count' => $count,
                'avgCost' => (int) round($chargeCost[$label] / $count),
            ];
        }
        usort($chargeStats, fn ($a, $b) => $b['count'] <=> $a['count']);
        $chargeStats = array_slice($chargeStats, 0, 20);
        $maxChargeCount = $chargeStats ? max(array_column($chargeStats, 'count')) : 1;

        // ── Affiliation over time ─────────────────────────────────────
        // Sum the prosecution-and-incarceration cost of each prisoner
        // into the year of their earliest documented arrest, grouped by
        // purported affiliation (counted once per affiliation they're
        // tagged with). Uses the same cost model as the by-ideology
        // breakdown, so this chart reads in dollars, not head count.
        $casesByPrisonerTmp = $cases->groupBy('prisoner_id');
        $affByYear = [];   // [affiliation][year] => dollars
        $affTotals = [];   // [affiliation]       => dollars
        foreach ($prisoners as $p) {
            $set = $casesByPrisonerTmp->get($p->id);
            $arrest = $set?->whereNotNull('arrest_date')->min('arrest_date');
            if (! $arrest) continue;
            $yr = (int) Carbon::parse($arrest)->year;

            $cost = 0.0;
            foreach ($set ?? collect() as $c) {
                $cls    = $classify($c);
                $days   = (int) ($c->imprisoned_for_days ?? 0);
                $start  = $c->incarceration_date ?: $c->arrest_date;
                $startC = $start ? Carbon::parse($start) : null;
                $endC   = $c->release_date ? Carbon::parse($c->release_date) : null;
                $cost  += IncarcerationCostRates::costForPeriod($cls['bucket'], $cls['state'], $startC, $endC, $days);
                $arrestYear = $c->arrest_date ? (int) Carbon::parse($c->arrest_date)->year : (int) date('Y');
                $cost  += IncarcerationCostRates::investigationCost($cls['bucket'], $c->charges, $c->sentence, $arrestYear);
                $cost  += IncarcerationCostRates::prosecutionCost($cls['bucket'], $c->charges, $c->sentence, $arrestYear);
                if ((string) ($c->convicted ?? '') !== '' || (string) ($c->plead ?? '') !== '' || (string) ($c->sentence ?? '') !== '') {
                    $cost += IncarcerationCostRates::appealsCost($cls['bucket'], $c->charges, $c->sentence, $arrestYear);
                }
            }
            $cost = (int) round($cost);
            if ($cost <= 0) continue;

            $affs = array_values(array_filter((array) $p->affiliation, fn ($a) => trim((string) $a) !== ''));
            if (empty($affs)) $affs = ['Unaffiliated'];
            foreach ($affs as $aff) {
                $aff = trim((string) $aff);
                $affByYear[$aff][$yr] = ($affByYear[$aff][$yr] ?? 0) + $cost;
                $affTotals[$aff] = ($affTotals[$aff] ?? 0) + $cost;
            }
        }
        arsort($affTotals);
        $topAffiliations = array_slice(array_keys($affTotals), 0, 6);
        $affYears = range($cutoffYear, (int) date('Y'));
        $affiliationSeries = [];
        foreach ($topAffiliations as $aff) {
            $row = [];
            foreach ($affYears as $y) {
                $row[] = $affByYear[$aff][$y] ?? 0;
            }
            $affiliationSeries[] = [
                'label' => $aff,
                'total' => $affTotals[$aff],
                'data'  => $row,
            ];
        }

        // Round once for display; internal sums kept as floats above.
        $costFederalIncarceration = (int) round($costFederalIncarceration);
        $costStateIncarceration   = (int) round($costStateIncarceration);
        $costLocalIncarceration   = (int) round($costLocalIncarceration);
        $costOfInvestigation      = (int) round($costOfInvestigation);
        $costOfProsecution        = (int) round($costOfProsecution);
        $costOfAppeals            = (int) round($costOfAppeals);

        $totalCost = $costFederalIncarceration + $costStateIncarceration + $costLocalIncarceration
                   + $costOfInvestigation + $costOfProsecution + $costOfAppeals;

        // Ongoing daily burn — the per-day incarceration cost of every
        // currently in-custody prisoner at this year's rate. Used by
        // the live ticker on the hero counter so the total keeps
        // climbing in real time at a verifiable rate.
        $thisYear = (int) date('Y');
        $dailyOngoingCost = 0.0;
        $prisonerById = $prisoners->keyBy('id');
        foreach ($cases as $c) {
            $p = $prisonerById->get($c->prisoner_id);
            if (! $p || ! $p->in_custody) continue;
            if ($c->release_date) continue; // case has ended
            $cls = $classify($c);
            $rate = match ($cls['bucket']) {
                'federal' => IncarcerationCostRates::federalDaily($thisYear),
                'local'   => IncarcerationCostRates::localDaily($thisYear),
                default   => IncarcerationCostRates::stateDaily($cls['state'], $thisYear),
            };
            $dailyOngoingCost += $rate;
        }
        $perSecondOngoingCost = $dailyOngoingCost / 86400.0;

        // Bubbles in the middle of the page — sorted descending for visual hierarchy.
        $costBubbles = collect([
            ['label' => 'Federal incarceration', 'value' => $costFederalIncarceration, 'shade' => 'a'],
            ['label' => 'State incarceration',   'value' => $costStateIncarceration,   'shade' => 'b'],
            ['label' => 'Local jail time',       'value' => $costLocalIncarceration,   'shade' => 'c'],
            ['label' => 'Investigations',        'value' => $costOfInvestigation,      'shade' => 'f'],
            ['label' => 'Prosecution',           'value' => $costOfProsecution,        'shade' => 'd'],
            ['label' => 'Appeals & post-conviction', 'value' => $costOfAppeals,        'shade' => 'e'],
        ])->where('value', '>', 0)->sortByDesc('value')->values();

        // CAP-style "where the money goes" cards — same six buckets, with
        // explanatory copy and an emblem key for the diamond artwork.
        $costCards = collect([
            ['key' => 'federal', 'label' => 'Federal detention', 'value' => $costFederalIncarceration,
             'blurb' => "Days served in Bureau of Prisons custody, priced year by year at the BOP's own published per-inmate rate. Federal political cases — espionage, material support, RICO conspiracy — carry the longest sentences and the steepest daily cost."],
            ['key' => 'state', 'label' => 'State detention', 'value' => $costStateIncarceration,
             'blurb' => "Time in state prison systems, priced at each state's annual per-prisoner cost adjusted to the year served. Most movement-era convictions — Black Panther, AIM, Puerto Rican independentista — ran through state custody."],
            ['key' => 'local', 'label' => 'Local & county jails', 'value' => $costLocalIncarceration,
             'blurb' => 'Pretrial detention and short sentences in county and city jails — the most common first stop for protesters and organizers, priced at the national per-inmate local-jail rate.'],
            ['key' => 'investigation', 'label' => 'Investigations', 'value' => $costOfInvestigation,
             'blurb' => 'The surveillance that precedes the charge: FBI field work, Joint Terrorism Task Force stings, COINTELPRO-style infiltration. Years of informants, wiretaps, and grand juries, billed before a single day is served.'],
            ['key' => 'prosecution', 'label' => 'Prosecution & court costs', 'value' => $costOfProsecution,
             'blurb' => 'The trial itself — prosecutors, expert witnesses, and court time. A capital conspiracy case costs orders of magnitude more than a trespassing charge, so every case is tiered by charge severity.'],
            ['key' => 'appeals', 'label' => 'Appeals & post-conviction', 'value' => $costOfAppeals,
             'blurb' => 'Appellate and habeas litigation after conviction — the years of motions, briefs, and federal review that follow a political sentence.'],
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
            $cost = 0.0;
            foreach ($set as $c) {
                $cls = $classify($c);
                $days = (int) ($c->imprisoned_for_days ?? 0);
                $start = $c->incarceration_date ?: $c->arrest_date;
                $startC = $start ? Carbon::parse($start) : null;
                $endC   = $c->release_date ? Carbon::parse($c->release_date) : null;
                $cost  += IncarcerationCostRates::costForPeriod($cls['bucket'], $cls['state'], $startC, $endC, $days);
                $arrestYear = $c->arrest_date ? (int) Carbon::parse($c->arrest_date)->year : (int) date('Y');
                $cost  += IncarcerationCostRates::investigationCost($cls['bucket'], $c->charges, $c->sentence, $arrestYear);
                $cost  += IncarcerationCostRates::prosecutionCost($cls['bucket'], $c->charges, $c->sentence, $arrestYear);
                if ((string) ($c->convicted ?? '') !== '' || (string) ($c->plead ?? '') !== '' || (string) ($c->sentence ?? '') !== '') {
                    $cost += IncarcerationCostRates::appealsCost($cls['bucket'], $c->charges, $c->sentence, $arrestYear);
                }
            }
            if (! $cost) continue;
            foreach (((array) $p->ideologies) ?: ['Unclassified'] as $ideology) {
                $costByIdeology[$ideology] = ($costByIdeology[$ideology] ?? 0) + (int) round($cost);
            }
        }
        arsort($costByIdeology);
        $costByIdeology = array_slice($costByIdeology, 0, 10, true);

        // Active cases — currently incarcerated prisoners with their case
        $activeCases = $prisoners->where('in_custody', true)
            ->sortByDesc(fn ($p) => $casesByPrisoner->get($p->id)?->min('arrest_date') ?? '')
            ->take(8)
            ->values();

        // Per-prisoner cost for the active-cards (year-aware, state-aware).
        $activeCaseCosts = [];
        foreach ($activeCases as $p) {
            $set = $casesByPrisoner->get($p->id) ?? collect();
            $cost = 0.0;
            foreach ($set as $c) {
                $cls   = $classify($c);
                $days  = (int) ($c->imprisoned_for_days ?? 0);
                $start = $c->incarceration_date ?: $c->arrest_date;
                $startC = $start ? Carbon::parse($start) : null;
                $endC   = $c->release_date ? Carbon::parse($c->release_date) : null;
                $cost += IncarcerationCostRates::costForPeriod($cls['bucket'], $cls['state'], $startC, $endC, $days);
                $arrestYear = $c->arrest_date ? (int) Carbon::parse($c->arrest_date)->year : (int) date('Y');
                $cost += IncarcerationCostRates::investigationCost($cls['bucket'], $c->charges, $c->sentence, $arrestYear);
                $cost += IncarcerationCostRates::prosecutionCost($cls['bucket'], $c->charges, $c->sentence, $arrestYear);
                if ((string) ($c->convicted ?? '') !== '' || (string) ($c->plead ?? '') !== '' || (string) ($c->sentence ?? '') !== '') {
                    $cost += IncarcerationCostRates::appealsCost($cls['bucket'], $c->charges, $c->sentence, $arrestYear);
                }
            }
            $activeCaseCosts[$p->id] = (int) round($cost);
        }

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

        // Display-only sample rates surfaced in the methodology copy.
        $methodFedRateRange  = ['min' => 36, 'max' => (int) round(IncarcerationCostRates::federalDaily((int) date('Y'))), 'minYear' => 1985, 'maxYear' => (int) date('Y')];

        return view('pages.tracker', compact(
            'totalDaysImprisoned', 'totalDaysInExile',
            'inCustody', 'inExile', 'released', 'awaitingTrial',
            'costByIdeology', 'activeCases', 'totalPrisoners', 'totalCases', 'firstYear',
            'casesByPrisoner', 'activeCaseCosts',
            'earliestCase', 'earliestPrisoner', 'newestActiveCase', 'newestActivePrisoner',
            'heroPrisoners',
            'costOfIncarceration', 'costOfProsecution', 'totalCost',
            'costFederalIncarceration', 'costStateIncarceration', 'costLocalIncarceration',
            'costOfInvestigation', 'costOfAppeals',
            'dailyOngoingCost', 'perSecondOngoingCost',
            'costBubbles', 'costCards', 'windowYears', 'methodFedRateRange',
            'chargeStats', 'maxChargeCount',
            'affiliationSeries', 'affYears',
            'federalDays', 'stateDays', 'localDays',
        ));
    }

    public function faq() {
        return view('pages.faq');
    }

    /**
     * Slugs of "pointer" articles: cards that appear in the news grid for
     * discovery but send the reader to a standalone feature page rather
     * than a /news/{slug} story.
     */
    private const FEATURE_REDIRECTS = [
        'the-price-of-political-prosecution' => '/feature-political-prisoner-cost',
        'under-cover-of-war' => '/iran-war-political-prisoners',
        'detained-for-dissent' => '/student-visa-revocations-and-ice-arrests',
    ];

    public function article(string $slug) {
        if ($target = self::FEATURE_REDIRECTS[$slug] ?? null) {
            return redirect($target);
        }

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
