<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Re-classify every Article currently in the "Repression" category
 * as "News", then delete the empty Repression category. One-shot
 * cleanup; idempotent (safe to re-run — reports "nothing to do").
 */
final class DropRepressionCategory extends Command {
    protected $signature = 'categories:drop-repression';
    protected $description = 'Move all Repression-category articles to News and delete the Repression category';

    public function handle(): int {
        $repression = Category::where('title', 'Repression')->first();
        if (! $repression) {
            $this->line('No "Repression" category — nothing to do.');
            return self::SUCCESS;
        }

        $news = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);

        DB::transaction(function () use ($repression, $news) {
            $moved = Article::where('category_id', $repression->id)
                ->update(['category_id' => $news->id]);
            $this->info('Re-classified '.$moved.' article(s) from Repression → News.');

            $repression->delete();
            $this->info('Deleted the empty "Repression" category.');
        });

        return self::SUCCESS;
    }
}
