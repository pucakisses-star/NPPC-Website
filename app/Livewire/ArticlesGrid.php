<?php

namespace App\Livewire;

use App\Models\Article;
use App\Models\Category;
use Livewire\Component;

class ArticlesGrid extends Component {
    public $categories;
    public $selectedCategory = 'Latest';
    public $articles;
    public $limit;
    public int $page = 1;
    public int $perPage = 12;
    public int $totalPages = 1;
    public int $totalArticles = 0;

    public function mount($limit = null) {
        $this->categories = Category::all();
        $this->limit      = $limit;
        $this->loadArticles();
    }

    public function loadArticles() {
        $query = Article::with('category')->orderBy('published_at', 'desc');
        if ($this->selectedCategory !== 'Latest') {
            $query = $query->whereHas('category', function ($q) {
                $q->where('title', $this->selectedCategory);
            });
        }

        if ($this->limit) {
            // Home-page mode: hard limit, no pagination.
            $this->articles      = $query->limit($this->limit)->get();
            $this->totalArticles = $this->articles->count();
            $this->totalPages    = 1;
            $this->page          = 1;
            return;
        }

        // /news mode: paginate.
        $this->totalArticles = (clone $query)->count();
        $this->totalPages    = max(1, (int) ceil($this->totalArticles / $this->perPage));
        $this->page          = max(1, min($this->page, $this->totalPages));
        $offset              = ($this->page - 1) * $this->perPage;
        $this->articles      = $query->skip($offset)->take($this->perPage)->get();
    }

    public function selectCategory($category) {
        $this->selectedCategory = $category;
        $this->page = 1;
        $this->loadArticles();
    }

    public function nextPage() {
        if ($this->page < $this->totalPages) {
            $this->page++;
            $this->loadArticles();
        }
    }

    public function prevPage() {
        if ($this->page > 1) {
            $this->page--;
            $this->loadArticles();
        }
    }

    public function gotoPage(int $page) {
        $this->page = max(1, min($page, $this->totalPages));
        $this->loadArticles();
    }

    public function render() {
        return view('livewire.articles-grid', [
            'categories' => $this->categories,
            'articles'   => $this->articles,
        ]);
    }
}
