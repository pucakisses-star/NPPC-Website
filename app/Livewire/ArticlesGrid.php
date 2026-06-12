<?php

namespace App\Livewire;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
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

        if ($this->limit) {
            // Homepage embeds the grid with a hard limit (e.g. top 6 articles)
            // and doesn't want pagination.
            $articles = $query->limit($this->limit)->get();
        } else {
            // Standalone /news page: 20 articles on page 1, then 15 on every
            // page after. Laravel's paginate() is uniform, so build the
            // paginator by hand. The synthetic total (real total minus the
            // 5-article page-1 surplus) makes LengthAwarePaginator's
            // ceil(total / 15) land on the correct last page, since
            //   1 + ceil((total - 20) / 15) === ceil((total - 5) / 15).
            $firstPageSize = 20;
            $perPage       = 15;

            $total = (clone $query)->count();
            $page  = max(1, (int) Paginator::resolveCurrentPage());

            $offset = $page === 1 ? 0 : $firstPageSize + ($page - 2) * $perPage;
            $take   = $page === 1 ? $firstPageSize : $perPage;

            $items = (clone $query)->offset($offset)->limit($take)->get();

            $articles = new LengthAwarePaginator(
                $items,
                max(0, $total - ($firstPageSize - $perPage)),
                $perPage,
                $page,
                [
                    'path'     => Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
        }

        return view('livewire.articles-grid', [
            'categories' => $this->categories,
            'articles'   => $articles,
        ]);
    }
}
