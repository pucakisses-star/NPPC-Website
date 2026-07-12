<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Replaces the Broadview Six article's cover with an edited composite of the
 * Chicago Tribune's grid of the six defendants (database/data/photos/
 * broadview-six-grid.jpg — tone-matched, uniformly cropped, and reassembled
 * from the Tribune original at the site owner's direction). Overwrites the
 * previous Wikimedia cover and sets a caption naming the defendants with a
 * Tribune credit. Idempotent: skips when the stored file and caption already
 * match.
 */
final class SetBroadviewSixCover extends Command
{
    protected $signature = 'articles:set-broadview-six-cover';

    protected $description = 'Set the Broadview Six article cover to the edited Tribune defendants grid';

    private const SLUG = 'broadview-six-ice-protest-case-collapses-2026';

    private const CAPTION = 'The six Broadview defendants: Kat Abughazaleh, Andre Martin, Michael Rabbitt, Catherine "Cat" Sharp, Brian Straw, and Joselyn Walsh. Photos: Chicago Tribune';

    public function handle(): int
    {
        $article = Article::where('slug', self::SLUG)->first();
        if (! $article) {
            $this->error('Article not found: '.self::SLUG);

            return self::FAILURE;
        }

        $src = database_path('data/photos/broadview-six-grid.jpg');
        if (! is_file($src)) {
            $this->error('Missing image file: database/data/photos/broadview-six-grid.jpg');

            return self::FAILURE;
        }

        $dest = 'articles/covers/'.self::SLUG.'.jpg';
        $bytes = file_get_contents($src);
        $disk = Storage::disk('public');

        $fileSame = $disk->exists($dest) && md5($disk->get($dest)) === md5($bytes);
        $metaSame = $article->image === $dest && $article->image_caption === self::CAPTION;
        if ($fileSame && $metaSame) {
            $this->info('Already applied; nothing to do.');

            return self::SUCCESS;
        }

        $disk->makeDirectory('articles/covers');
        $disk->put($dest, $bytes);
        $article->image = $dest;
        $article->image_caption = self::CAPTION;
        $article->save();

        $this->info('Cover replaced and caption set for: '.self::SLUG);

        return self::SUCCESS;
    }
}
