<?php

namespace App\Console\Commands;

use App\Models\Author;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Backfills the authors.slug column for rows created before the column
 * existed (HasSlug only fires on create). Idempotent: authors that already
 * have a slug are left untouched; collisions get a -2/-3/… suffix, matching
 * the trait's behaviour.
 */
class GenerateAuthorSlugs extends Command {
    protected $signature = 'authors:generate-slugs';
    protected $description = 'Backfill URL slugs for authors created before the slug column existed';

    public function handle(): int {
        $done = 0;

        foreach (Author::whereNull('slug')->get() as $author) {
            $base = Str::slug($author->name) ?: 'author-'.Str::lower(Str::random(6));
            $slug = $base;
            $i = 2;
            while (Author::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i;
                $i++;
            }
            $author->slug = $slug;
            $author->save();
            $this->info("{$author->name} -> /author/{$slug}");
            $done++;
        }

        $this->info("Done. {$done} slug(s) generated.");

        return self::SUCCESS;
    }
}
