<?php

namespace App\Livewire;

use App\Models\Article;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class ArticlesGrid extends Component {
    use WithPagination;

    public $categories;
    public $selectedCategory = 'Latest';
    public $limit;

    protected $paginationTheme = 'tailwind';

    public function mount($limit = null) {
        $this->categories = Category::all();
        $this->limit      = $limit;
    }

    public function selectCategory($category) {
        $this->selectedCategory = $category;
        $this->resetPage();
    }

    public function render() {
        if ($this->selectedCategory === 'Latest') {
            $query = Article::with('category')->orderBy('published_at', 'desc');
        } else {
            $query = Article::with('category')
                ->whereHas('category', function ($q) {
                    $q->where('title', $this->selectedCategory);
                })
                ->orderBy('published_at', 'desc');
        }

        // Homepage embeds the grid with a hard limit (e.g. top 6 articles)
        // and doesn't want pagination. The standalone /news page uses no
        // limit and gets paginated at 18 articles per page.
        if ($this->limit) {
            $articles = $query->limit($this->limit)->get();
        } else {
            $articles = $query->paginate(18);
        }

        return view('livewire.articles-grid', [
            'categories' => $this->categories,
            'articles'   => $articles,
        ]);
    }
}
