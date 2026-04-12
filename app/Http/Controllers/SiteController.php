<?php

namespace App\Http\Controllers;

use App\Models\AnnualReport;
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
            ->get();

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $today = (int) date('j');
        $currentMonth = (int) date('n');

        // Day view: find specific entry
        $dayEntry = null;
        if ($view === 'day' && $day) {
            $dayEntry = $entries->where('day', (int) $day)->first();
        }
        // Default to today's entry if no day specified in day view
        if ($view === 'day' && ! $dayEntry && $month === $currentMonth) {
            $dayEntry = $entries->where('day', $today)->first();
        }
        // Fall back to first entry if nothing found
        if ($view === 'day' && ! $dayEntry && $entries->isNotEmpty()) {
            $dayEntry = $entries->first();
        }

        return view('pages.calendar', compact('entries', 'month', 'monthName', 'today', 'currentMonth', 'view', 'dayEntry'));
    }

    public function store(Request $request) {
        $category = $request->input('category');
        $query = Product::published()->orderBy('sort_order');
        if ($category) {
            $query->where('category', $category);
        }
        $products = $query->get();
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
        return view('pages.prisoner-outreach');
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

    public function petitionPage(string $slug) {
        $petition = \App\Models\Petition::where('slug', $slug)->where('published', true)->firstOrFail();
        $recentSigners = $petition->signatures()->latest()->limit(5)->get();

        return view('pages.petition', compact('petition', 'recentSigners'));
    }

    public function petitionSign(Request $request, string $slug) {
        $petition = \App\Models\Petition::where('slug', $slug)->where('published', true)->firstOrFail();

        $request->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => 'required|email|max:255',
            'city'           => 'nullable|string|max:100',
            'state'          => 'nullable|string|max:100',
            'zip_code'       => 'nullable|string|max:20',
            'phone'          => 'nullable|string|max:30',
            'custom_message' => 'nullable|string|max:2000',
        ]);

        // Prevent duplicate signatures from the same email
        $alreadySigned = \App\Models\PetitionSignature::where('petition_id', $petition->id)
            ->where('email', $request->input('email'))
            ->exists();

        if ($alreadySigned) {
            return redirect("/petition/{$slug}?signed=true");
        }

        \App\Models\PetitionSignature::create([
            'petition_id'    => $petition->id,
            'first_name'     => $request->input('first_name'),
            'last_name'      => $request->input('last_name'),
            'email'          => $request->input('email'),
            'city'           => $request->input('city'),
            'state'          => $request->input('state'),
            'zip_code'       => $request->input('zip_code'),
            'phone'          => $request->input('phone'),
            'custom_message' => $request->input('custom_message'),
        ]);

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
