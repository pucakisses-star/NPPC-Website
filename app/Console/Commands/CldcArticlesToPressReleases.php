<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;

/**
 * Refiles every article authored by the Civil Liberties Defense Center (the
 * republished CLDC press releases) under the "Press Release" category. Matches
 * any author whose name contains "Civil Liberties Defense Center" so both the
 * plain and "(republished)" variants are covered. Idempotent.
 */
final class CldcArticlesToPressReleases extends Command
{
    protected $signature = 'articles:cldc-to-press-release';

    protected $description = 'File all Civil Liberties Defense Center articles under the Press Release category';

    public function handle(): int
    {
        $authorIds = Author::withoutGlobalScopes()
            ->where('name', 'like', '%Civil Liberties Defense Center%')
            ->pluck('id');

        if ($authorIds->isEmpty()) {
            $this->warn('No "Civil Liberties Defense Center" author found — nothing to do.');

            return self::SUCCESS;
        }

        $category = Category::firstOrCreate(['title' => 'Press Release']);

        $count = Article::withoutGlobalScopes()
            ->whereIn('author_id', $authorIds)
            ->update(['category_id' => $category->id]);

        $this->info("Filed {$count} Civil Liberties Defense Center article(s) under '{$category->title}'.");

        return self::SUCCESS;
    }
}
