<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Adds the NPPC Bookmark to the store (Accessories) with its cover image,
 * committed at public/images/products/bookmark-nppc.jpg and copied to the
 * public disk here. Idempotent: a product already present (matched by name) is
 * ensured published in Accessories and given the cover if it has none; price
 * and description are left as-is so admin edits are preserved.
 */
final class AddBookmarkProduct extends Command
{
    protected $signature = 'store:add-bookmark';

    protected $description = 'Add the NPPC Bookmark product (Accessories) with cover';

    private string $productName = 'NPPC Bookmark';

    private string $imageSlug = 'bookmark-nppc';

    private float $price = 3.00;

    private string $desc = 'A sleek black bookmark featuring the National Political Prisoner Coalition logo — a small way to support the work and keep your place.';

    public function handle(): int
    {
        $imagePath = "products/{$this->imageSlug}.jpg";
        $source = public_path("images/products/{$this->imageSlug}.jpg");
        if (is_file($source)) {
            Storage::disk('public')->put($imagePath, file_get_contents($source));
        }

        $product = Product::where('name', $this->productName)->first();

        if (! $product) {
            $base = Str::slug($this->productName);
            $slug = $base;
            $i = 2;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            Product::create([
                'name' => $this->productName,
                'description' => $this->desc,
                'price' => $this->price,
                'category' => 'Accessories',
                'published' => true,
                'featured' => false,
                'image' => is_file($source) ? $imagePath : null,
                'slug' => $slug,
            ]);
            $this->info("Added: {$this->productName}");

            return self::SUCCESS;
        }

        $product->category = 'Accessories';
        $product->published = true;
        if (! $product->image && is_file($source)) {
            $product->image = $imagePath;
        }
        $product->save();
        $this->line("Updated: {$this->productName}");

        return self::SUCCESS;
    }
}
