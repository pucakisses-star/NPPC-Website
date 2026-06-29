<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds the back-view photo as an additional gallery image on the NPPC Canvas
 * Tote Bag product. Copies the committed image to the public disk and appends
 * its path to the product's gallery (leaving the primary image as-is).
 * Idempotent: the path is only added once. Requires the products.gallery
 * column (migration add_gallery_to_products).
 */
final class AddToteBagGalleryImage extends Command
{
    protected $signature = 'store:add-tote-bag-image';

    protected $description = "Add the back-view image to the NPPC Canvas Tote Bag's gallery";

    private const SLUG = 'nppc-canvas-tote-bag';

    private const SOURCE = 'images/products/nppc-canvas-tote-bag-back.jpg';

    private const STORED = 'products/nppc-canvas-tote-bag-back.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        $product = Product::where('slug', self::SLUG)->first();
        if (! $product) {
            $this->error('No product found for slug '.self::SLUG.'.');

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::STORED, file_get_contents($source));

        $gallery = $product->gallery ?? [];
        if (in_array(self::STORED, $gallery, true)) {
            $this->info('Image already in the gallery — nothing to add.');

            return self::SUCCESS;
        }

        $gallery[] = self::STORED;
        $product->gallery = $gallery;
        $product->save();

        $this->info("Added gallery image to {$product->name}. View: /store/{$product->slug}");

        return self::SUCCESS;
    }
}
