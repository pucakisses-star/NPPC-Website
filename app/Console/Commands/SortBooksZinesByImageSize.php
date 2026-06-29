<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Orders the Books and Zines so their cover images run largest → smallest as
 * displayed in the store grid. Each cover sits in a fixed 2:3 box with
 * object-fit: contain, so "size" here means the rendered area of the cover
 * within that box: a near-2:3 cover fills it and looks biggest, while a squarer
 * or wider cover is letterboxed and looks smaller. Books and Zines stay in their
 * own groups (the store query already ranks Books before Zines); within each
 * group they are ordered largest cover first. Products whose image is missing or
 * unreadable sort to the end. Idempotent — safe to re-run.
 */
final class SortBooksZinesByImageSize extends Command
{
    protected $signature = 'store:sort-books-zines-by-image-size';

    protected $description = 'Sort Books and Zines by cover image size (largest first)';

    public function handle(): int
    {
        $items = Product::whereIn('category', ['Books', 'Zines'])->get()
            ->map(fn ($p) => ['product' => $p, 'area' => $this->renderedArea($p->image)])
            ->sortByDesc('area')
            ->values();

        $order = 0;
        foreach ($items as $row) {
            $row['product']->sort_order = $order++;
            $row['product']->save();
        }

        $this->info("Sorted {$items->count()} Books/Zines by cover image size (largest first).");

        return self::SUCCESS;
    }

    /**
     * Rendered area of a cover when contained in the grid's 2:3 box. Larger means
     * the cover looks bigger on the page. Returns 0 when the image is missing.
     */
    private function renderedArea(?string $image): float
    {
        if (! $image) {
            return 0.0;
        }

        $path = Storage::disk('public')->path($image);
        if (! is_file($path)) {
            // Fall back to the committed source under public/images/products.
            $path = public_path('images/products/'.basename($image));
        }
        if (! is_file($path)) {
            return 0.0;
        }

        $size = @getimagesize($path);
        if (! $size || $size[0] <= 0 || $size[1] <= 0) {
            return 0.0;
        }

        [$w, $h] = $size;
        $scale = min(2 / $w, 3 / $h);

        return ($w * $scale) * ($h * $scale);
    }
}
