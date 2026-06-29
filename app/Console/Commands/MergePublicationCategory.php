<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Console\Command;

/**
 * Consolidates the singular "Publication" category into "Publications" (the
 * canonical category seeded by CategorySeeder). Every article tagged
 * "Publication" is re-tagged to "Publications" first, and only the now-empty
 * singular category is removed — no articles are ever deleted. If only the
 * singular exists, it is simply renamed. Idempotent and safe to re-run.
 */
final class MergePublicationCategory extends Command
{
    protected $signature = 'articles:merge-publication-category';

    protected $description = "Merge the singular 'Publication' category into 'Publications'";

    public function handle(): int
    {
        $singular = Category::where('slug', 'publication')->orWhere('title', 'Publication')->first();
        if (! $singular) {
            $this->info("No singular 'Publication' category found — nothing to do.");

            return self::SUCCESS;
        }

        $plural = Category::where('id', '!=', $singular->id)
            ->where(fn ($q) => $q->where('slug', 'publications')->orWhere('title', 'Publications'))
            ->first();

        if (! $plural) {
            // No "Publications" yet — just rename the singular.
            $singular->title = 'Publications';
            $singular->slug = $this->uniqueSlug('publications', $singular->id);
            $singular->save();
            $this->info("Renamed 'Publication' → 'Publications'.");

            return self::SUCCESS;
        }

        // Both exist — re-tag every "Publication" article to "Publications",
        // then remove only the empty singular category. No articles deleted.
        $moved = Article::where('category_id', $singular->id)->update(['category_id' => $plural->id]);
        $singular->delete();
        $this->info("Merged 'Publication' into 'Publications' — {$moved} article(s) re-tagged, empty 'Publication' removed.");

        return self::SUCCESS;
    }

    private function uniqueSlug(string $base, string $ignoreId): string
    {
        $slug = $base;
        $i = 2;
        while (Category::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
