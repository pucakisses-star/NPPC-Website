<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Second batch of political-prisoner stickers/patches for the store. Patches are
 * no longer a unique category, so everything here lives under Accessories (the
 * sticker also carries the Stickers tag). Cover images are committed under
 * public/images/products/{sticker,patch}-*.jpg and copied to the public disk
 * here. This command also migrates any product still filed under the retired
 * "Patches" category to "Accessories". Idempotent: a product already present
 * (matched by name) is ensured published with the right categories and given the
 * cover if it has none; description/price are left as-is so admin edits persist.
 */
final class AddBurningStickers2 extends Command
{
    protected $signature = 'store:add-burning-stickers-2';

    protected $description = 'Add more political-prisoner stickers/patches and retire the Patches category';

    /** @var array<int, array{name:string, slug:string, price:float, category:string, categories:array<int,string>, description:string}> */
    private array $items = [
        [
            'name' => 'In Solidarity With The Earth Liberation Front Patch',
            'slug' => 'patch-in-solidarity-with-the-earth-liberation-front',
            'price' => 4.00,
            'category' => 'Accessories',
            'categories' => [],
            'description' => 'A 3-inch embroidered iron-on patch in solidarity with the Earth Liberation Front, featuring imagery from its 1998 arson at the Vail ski resort.',
        ],
        [
            'name' => 'Disability Rights Sticker',
            'slug' => 'sticker-disability-rights',
            'price' => 4.00,
            'category' => 'Stickers',
            'categories' => ['Accessories'],
            'description' => 'A die-cut vinyl sticker in support of disability rights.',
        ],
    ];

    public function handle(): int
    {
        // Retire the Patches category: anything still filed there moves to
        // Accessories so it remains discoverable now that the Patches pill is gone.
        $migrated = Product::where('category', 'Patches')->update(['category' => 'Accessories']);
        if ($migrated > 0) {
            $this->info("Migrated {$migrated} product(s) from Patches to Accessories.");
        }

        $created = 0;
        $updated = 0;

        // High sort_order so these PP stickers/patches fall to the back of the
        // regular-merch tier — still ahead of Books and Zines (separate ranks).
        foreach ($this->items as $index => $it) {
            $sortOrder = 505 + $index;
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
