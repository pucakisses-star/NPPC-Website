<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Adds the "Abolish Prisons, Invest in Communities" vinyl sticker to the store
 * (Stickers + Accessories) with a main product photo and a second lifestyle
 * photo as its gallery image. Covers are committed under
 * public/images/products/sticker-abolish-prisons-invest-in-communities*.jpg and
 * copied to the public disk here. Idempotent: a product already present (matched
 * by name) is ensured published with the right categories and given the images
 * if it has none; price/description are left as-is so admin edits are preserved.
 */
final class AddAbolishSticker extends Command
{
    protected $signature = 'store:add-abolish-sticker';

    protected $description = 'Add the Abolish Prisons, Invest in Communities sticker (with two photos)';

    private string $name = 'Abolish Prisons, Invest in Communities Sticker';

    private string $slug = 'sticker-abolish-prisons-invest-in-communities';

    private float $price = 3.52;

    private string $desc = 'Waterproof premium matte vinyl sticker, perfect for indoor and outdoor use. Eco-Solvent printed, 2 × 3.2 inches (5.8 × 7.5 cm), with an outdoor life of 3–4 years and an indoor life forever and ever. Super sticky! Quantity: one sticker. Materials: vinyl, eco-solvent ink.';

    public function handle(): int
    {
        // Copy the committed source images onto the public disk.
        $mainPath = "products/{$this->slug}.jpg";
        $galleryPath = "products/{$this->slug}-2.jpg";
        $mainSource = public_path("images/products/{$this->slug}.jpg");
        $gallerySource = public_path("images/products/{$this->slug}-2.jpg");

        if (is_file($mainSource)) {
            Storage::disk('public')->put($mainPath, file_get_contents($mainSource));
        }
        if (is_file($gallerySource)) {
            Storage::disk('public')->put($galleryPath, file_get_contents($gallerySource));
        }

        $gallery = is_file($gallerySource) ? [$galleryPath] : [];

        // High sort_order so it sits at the back of the merch tier with the other
        // political-prisoner stickers (ahead of Books/Zines).
        $sortOrder = 507;

        $product = Product::where('name', $this->name)->first();

        if (! $product) {
            $base = Str::slug($this->name);
            $slug = $base;
            $i = 2;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            Product::create([
                'name' => $this->name,
                'description' => $this->desc,
                'price' => $this->price,
                'category' => 'Stickers',
                'categories' => ['Accessories'],
                'sort_order' => $sortOrder,
                'published' => true,
                'featured' => false,
                'image' => is_file($mainSource) ? $mainPath : null,
                'gallery' => $gallery ?: null,
                'slug' => $slug,
            ]);
            $this->info("Added: {$this->name}");

            return self::SUCCESS;
        }

        $product->category = 'Stickers';
        $product->categories = ['Accessories'];
        $product->sort_order = $sortOrder;
        $product->published = true;
        if (! $product->image && is_file($mainSource)) {
            $product->image = $mainPath;
        }
        if (empty($product->gallery) && $gallery) {
            $product->gallery = $gallery;
        }
        $product->save();
        $this->line("Updated: {$this->name}");

        return self::SUCCESS;
    }
}
