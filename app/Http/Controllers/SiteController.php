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

        return view('pages.archive-records', compact(
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
