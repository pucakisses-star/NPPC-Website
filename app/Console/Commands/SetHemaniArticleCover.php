<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets the cover image on the "United States v. Hemani" article from the
 * committed source image (a SCOTUS photo). Copies the file to the public disk
 * and points the article's `image` column at it. Matches the live record by
 * slug/title; sets only the image (the body etc. are left untouched).
 * Idempotent.
 */
final class SetHemaniArticleCover extends Command
{
    protected $signature = 'articles:set-hemani-cover';

    protected $description = 'Set the cover image on the United States v. Hemani article';

    private const SLUG = 'united-states-v-hemani-scotus-drug-user-gun-ban-june-2026';

    private const SOURCE = 'images/articles/united-states-v-hemani-scotus-drug-user-gun-ban-june-2026.jpg';

    private const IMAGE = 'articles/united-states-v-hemani-scotus-drug-user-gun-ban-june-2026.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::IMAGE, file_get_contents($source));
        $this->info('Copied cover to public disk: '.self::IMAGE);

        $article = Article::where('slug', self::SLUG)
            ->orWhere('title', 'like', '%Hemani%')
            ->first();

        if (! $article) {
            $this->error('No "Hemani" article found.');

            return self::FAILURE;
        }

        $article->image = self::IMAGE;
        $article->save();
        $this->info("Set cover on: {$article->title}");
        $this->info("View: {$article->url}");

        return self::SUCCESS;
    }
}
