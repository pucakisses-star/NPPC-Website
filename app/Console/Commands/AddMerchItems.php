<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Adds individually-requested store products (currently a jacket, a water
 * bottle, and an NPPC bumper sticker). Dedupes by name and is safe to re-run —
 * items appended to the list below are created on the next run while existing
 * ones are skipped. Each product is created without an image so an admin can
 * upload a real photo through the panel.
 */
final class AddMerchItems extends Command
{
    protected $signature = 'store:add-merch-items';

    protected $description = 'Add requested store products (jacket, bottle, NPPC bumper sticker, button bundle)';

    public function handle(): int
    {
        $products = [
            [
                'name' => 'NPPC Jacket',
                'description' => 'Water-resistant full-zip jacket with the NPPC logo embroidered on the left chest. Lightweight lined shell with zip hand pockets and an adjustable hem. Unisex sizing S–3XL.',
                'price' => 65.00, 'category' => 'Apparel', 'sort_order' => 18,
            ],
            [
                'name' => 'Free All Political Prisoners Bottle',
                'description' => '20oz double-wall insulated stainless-steel bottle printed with "FREE ALL POLITICAL PRISONERS." Keeps drinks cold 24 hours, hot 12 — built for the rally, the courthouse steps, or the desk.',
                'price' => 28.00, 'category' => 'Accessories', 'sort_order' => 30,
            ],
            [
                'name' => 'NPPC Bumper Sticker',
                'description' => 'Weatherproof 11.5" × 3" vinyl bumper sticker with the National Political Prisoner Coalition logo and name. UV- and water-resistant — made for a bumper, a laptop lid, or a water bottle.',
                'price' => 4.00, 'category' => 'Stickers', 'sort_order' => 54,
            ],
            [
                'name' => 'NPPC Button Bundle (5)',
                'description' => 'Set of five 1.25-inch pin-back buttons, each a different design — the NPPC logo, "Free Them All", "Abolition", "Letter Writing Saves Lives", and "Free Mumia". Steel shells with safety-pin backs; save $3 versus buying singly.',
                'price' => 12.00, 'category' => 'Bundles', 'sort_order' => 82,
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($products as $entry) {
            DB::transaction(function () use ($entry, &$created, &$skipped) {
                $name = $entry['name'];
                if (Product::where('name', $name)->exists()) {
                    $this->warn("Skipping {$name} — already exists.");
                    $skipped++;

                    return;
                }

                // HasSlug reads $model->title, which Product lacks, so set the
                // slug explicitly to avoid empty-slug unique collisions.
                $base = Str::slug($name);
                $slug = $base;
                $i = 2;
                while (Product::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i++;
                }

                Product::create([
                    'name' => $name,
                    'description' => $entry['description'],
                    'price' => $entry['price'],
                    'category' => $entry['category'],
                    'sort_order' => $entry['sort_order'],
                    'published' => true,
                    'featured' => false,
                    'slug' => $slug,
                ]);

                $this->info("Added {$name}  (\${$entry['price']})  [{$entry['category']}]");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
