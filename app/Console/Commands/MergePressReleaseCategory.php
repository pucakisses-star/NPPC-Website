<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Console\Command;

/**
 * Consolidates the singular "Press Release" category into "Press Releases"
 * (the canonical category seeded by CategorySeeder). If both exist, every
 * article under "Press Release" is reassigned to "Press Releases" and the
 * singular is deleted; if only the singular exists, it is renamed. No
 * article is ever deleted. Idempotent.
 *
 * Both categories showing at once puts BOTH on the /news tab bar, because
 * ArticlesGrid builds its tabs from Category::all() — every category is a
 * tab whether or not it duplicates another.
 *
 * The singular was minted by firstOrCreate(['title' => 'Press Release'])
 * in AddMobileAppPressRelease, AddStorePressRelease and
 * AddNppcQuizPressRelease. Those three now key on the SLUG instead, so
 * running them can no longer recreate it — before that fix this merge
 * would silently undo itself the next time any of them ran.
 *
 * Merged articles move from /press-release/{slug} to /press-releases/{slug},
 * since Article::getUrlAttribute() derives the prefix from the category
 * slug. Old links keep working: SiteController::article() resolves an
 * article by slug alone and 301s a mismatched prefix to the canonical URL.
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
