<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Console\Command;

/**
 * Consolidates the singular "Press Release" category into "Press Releases"
 * (the canonical category seeded by CategorySeeder). The singular one was
 * created in error by earlier firstOrCreate(['title' => 'Press Release'])
 * calls. If both exist, every article under "Press Release" is reassigned to
 * "Press Releases" and the singular is deleted; if only the singular exists, it
 * is renamed. Idempotent.
 */
final class MergePressReleaseCategory extends Command
{
    protected $signature = 'articles:merge-press-release-category';

    protected $description = "Merge the singular 'Press Release' category into 'Press Releases'";

    public function handle(): int
    {
        $singular = Category::where('slug', 'press-release')->orWhere('title', 'Press Release')->first();
        if (! $singular) {
            $this->info("No singular 'Press Release' category found — nothing to do.");

            return self::SUCCESS;
        }

        $plural = Category::where('id', '!=', $singular->id)
            ->where(fn ($q) => $q->where('slug', 'press-releases')->orWhere('title', 'Press Releases'))
            ->first();

        if (! $plural) {
            // No "Press Releases" yet — just rename the singular.
            $singular->title = 'Press Releases';
            $singular->slug = $this->uniqueSlug('press-releases', $singular->id);
            $singular->save();
            $this->info("Renamed 'Press Release' → 'Press Releases'.");

            return self::SUCCESS;
        }

        $moved = Article::where('category_id', $singular->id)->update(['category_id' => $plural->id]);
        $singular->delete();
        $this->info("Merged 'Press Release' into 'Press Releases' — {$moved} article(s) moved, singular deleted.");

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
