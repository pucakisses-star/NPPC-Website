<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Replaces the blurry cover for the store product "Media Bias, Perspective &
 * State Repression: The Black Panther Party" with the higher-resolution committed
 * image, copying it to the public disk and force-setting it on the product.
 * Idempotent.
 */
final class ReplaceBlackPantherCover extends Command
{
    protected $signature = 'store:replace-black-panther-cover';

    protected $description = 'Replace the blurry Black Panther Party book cover with the higher-res image';

    private const NAME = 'Media Bias, Perspective & State Repression: The Black Panther Party';

    private const SLUG = 'book-media-bias-perspective-state-repression-the-black-pant';

    public function handle(): int
    {
        $imagePath = 'products/'.self::SLUG.'.jpg';
        $source = public_path('images/products/'.self::SLUG.'.jpg');

        if (! is_file($source)) {
            $this->error('Source image not found: public/images/products/'.self::SLUG.'.jpg');

            return self::FAILURE;
        }

        Storage::disk('public')->put($imagePath, file_get_contents($source));
        $this->info('Copied higher-res cover to public disk: '.$imagePath);

        $product = Product::where('name', self::NAME)->first()
            ?? Product::where('slug', 'like', self::SLUG.'%')->first();

        if (! $product) {
            $this->warn('Product not found — image copied, but no product to attach it to.');

            return self::SUCCESS;
        }

        $product->image = $imagePath;
        $product->save();
        $this->info("Set cover on: {$product->name}");

        return self::SUCCESS;
    }
}
