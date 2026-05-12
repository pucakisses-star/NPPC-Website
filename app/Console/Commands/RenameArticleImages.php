<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Renames each Article.image file in storage/app/public/articles/ to use
 * the article's slug, e.g.
 *   articles/01KQPH52N7NHS42BFCVHPXA907.jpg
 *     -> articles/my-article-slug.jpg
 * Updates Article.image accordingly. Handles collisions by appending
 * -2, -3, ... Supports --dry-run.
 */
final class RenameArticleImages extends Command {
    protected $signature = 'articles:rename-images {--dry-run : Preview without changing anything}';
    protected $description = 'Rename article image files to match the article slug';

    public function handle(): int {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');

        $renamed = 0;
        $alreadyClean = 0;
        $missing = 0;
        $collisions = 0;

        $articles = Article::whereNotNull('image')->where('image', '!=', '')->get();

        foreach ($articles as $article) {
            $current = ltrim($article->image, '/');
            if (! $disk->exists($current)) {
                $this->warn("Missing file for \"{$article->title}\": {$current}");
                $missing++;

                continue;
            }

            $ext = strtolower(pathinfo($current, PATHINFO_EXTENSION));
            $slug = $article->slug;
            if (empty($slug)) {
                $this->warn("Empty slug for article id={$article->id} title=\"{$article->title}\" — skipping.");

                continue;
            }
            $target = "articles/{$slug}.{$ext}";

            if ($target === $current) {
                $alreadyClean++;

                continue;
            }

            $finalTarget = $target;
            if ($disk->exists($finalTarget)) {
                $n = 2;
                do {
                    $finalTarget = "articles/{$slug}-{$n}.{$ext}";
                    $n++;
                } while ($disk->exists($finalTarget));
                $collisions++;
                $this->warn("Collision: {$target} exists; using {$finalTarget}");
            }

            $this->line(($dryRun ? '[dry-run] ' : '')."mv {$current} -> {$finalTarget}  ({$article->title})");

            if (! $dryRun) {
                $disk->move($current, $finalTarget);
                $article->image = $finalTarget;
                $article->save();
            }
            $renamed++;
        }

        $this->info("\nDone. Renamed={$renamed} AlreadyClean={$alreadyClean} Missing={$missing} Collisions={$collisions}".($dryRun ? ' (DRY RUN)' : ''));

        return self::SUCCESS;
    }
}
