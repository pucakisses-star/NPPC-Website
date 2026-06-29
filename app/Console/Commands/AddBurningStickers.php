<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Adds political-prisoner stickers and patches to the store, each with its
 * cover image and a short description. Stickers go in Stickers (+ Accessories);
 * the patch goes in the Patches category (+ Accessories). Cover images are
 * committed under public/images/products/{sticker,patch}-*.jpg and copied to the
 * public disk here. Idempotent: a product already present (matched by name) is
 * ensured published with the right categories and given the cover if it has
 * none; description/price are left as-is so admin edits are preserved.
 */
final class AddBurningStickers extends Command
{
    protected $signature = 'store:add-burning-stickers';

    protected $description = 'Add curated political-prisoner stickers and patches with covers';

    /** @var array<int, array{name:string, slug:string, price:float, category:string, categories:array<int,string>, description:string}> */
    private array $items = [
        [
            'name' => 'Free All Political Prisoners Sticker',
            'slug' => 'sticker-free-all-political-prisoners',
            'price' => 1.00,
            'category' => 'Stickers',
            'categories' => ['Accessories'],
            'description' => "A 'Free All Political Prisoners' vinyl sticker, reproducing a design found in old support literature for the Ohio 7.",
        ],
        [
            'name' => 'Free Leonard Peltier - AIM Logo Patch',
            'slug' => 'patch-free-leonard-peltier-aim-logo',
            'price' => 4.00,
            'category' => 'Patches',
            'categories' => ['Accessories'],
            'description' => 'An embroidered patch supporting Leonard Peltier — American Indian Movement activist imprisoned for nearly fifty years before his 2025 release to home confinement — with the AIM logo.',
        ],
        [
            'name' => 'Martin Sostre Sticker',
            'slug' => 'sticker-martin-sostre',
            'price' => 1.00,
            'category' => 'Stickers',
            'categories' => ['Accessories'],
            'description' => 'A sticker honoring Martin Sostre — Black Puerto Rican anarchist, bookstore owner, jailhouse lawyer, and political prisoner.',
        ],
        [
            'name' => 'Martin Sostre Propaganda of the Deed Sticker',
            'slug' => 'sticker-martin-sostre-propaganda-of-the-deed',
            'price' => 1.00,
            'category' => 'Stickers',
            'categories' => ['Accessories'],
            'description' => 'A sticker featuring original art of Martin Sostre, the Black and Puerto Rican political prisoner and jailhouse lawyer.',
        ],
        [
            'name' => 'Rehabilitation Not Incarceration Sticker',
            'slug' => 'sticker-rehabilitation-not-incarceration',
            'price' => 4.00,
            'category' => 'Stickers',
            'categories' => ['Accessories'],
            'description' => "A transparent vinyl sticker with the message 'Rehabilitation Not Incarceration.'",
        ],
    ];

    public function handle(): int
    {
        $created = 0;
        $updated = 0;

        // High sort_order so these PP stickers/patches fall to the back of the
        // regular-merch tier — still ahead of Books and Zines (separate ranks).
        foreach ($this->items as $index => $it) {
            $sortOrder = 500 + $index;
            $imagePath = "products/{$it['slug']}.jpg";
            $source = public_path("images/products/{$it['slug']}.jpg");
            if (is_file($source)) {
                Storage::disk('public')->put($imagePath, file_get_contents($source));
            }

            $product = Product::where('name', $it['name'])->first();

            if (! $product) {
                $base = Str::slug($it['name']);
                $slug = $base;
                $i = 2;
                while (Product::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                Product::create([
                    'name' => $it['name'],
                    'description' => $it['description'],
                    'price' => $it['price'],
                    'category' => $it['category'],
                    'categories' => $it['categories'],
                    'sort_order' => $sortOrder,
                    'published' => true,
                    'featured' => false,
                    'image' => is_file($source) ? $imagePath : null,
                    'slug' => $slug,
                ]);
                $this->info("Added: {$it['name']}");
                $created++;

                continue;
            }

            $product->category = $it['category'];
            $product->categories = $it['categories'];
            $product->sort_order = $sortOrder;
            $product->published = true;
            if (! $product->image && is_file($source)) {
                $product->image = $imagePath;
            }
            $product->save();
            $this->line("Updated: {$it['name']}");
            $updated++;
        }

        $this->info("\nDone. Added {$created}, updated {$updated}.");

        return self::SUCCESS;
    }
}
