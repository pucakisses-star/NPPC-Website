<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Adds three small-format merch items to the store: a bumper sticker (Stickers
 * category), plus a car magnet and a fridge magnet (a new Magnets category).
 * Dedupes by name and is safe to re-run. Products are created without an image
 * so an admin can upload a real photo through the panel.
 */
final class AddStoreMagnets extends Command
{
    protected $signature = 'store:add-magnets';

    protected $description = 'Add a bumper sticker, a car magnet, and a fridge magnet to the store';

    public function handle(): int
    {
        $products = [
            [
                'name' => 'Free Them All Bumper Sticker',
                'description' => 'Weatherproof 11.5" × 3" vinyl bumper sticker with "FREE THEM ALL" in bold type. UV- and water-resistant — made for a bumper, a laptop lid, or a water bottle.',
                'price' => 4.00, 'category' => 'Stickers', 'sort_order' => 53,
            ],
            [
                'name' => 'Free Them All Car Magnet',
                'description' => 'Durable 8" oval car magnet reading "FREE THEM ALL." A strong, flexible magnet that holds at highway speed and peels off clean with no residue.',
                'price' => 8.00, 'category' => 'Magnets', 'sort_order' => 63,
            ],
            [
                'name' => 'NPPC Fridge Magnet',
                'description' => '2.5" square flexible magnet with the NPPC logo. Keep the cause in view on the fridge, a filing cabinet, or your letter-writing station.',
                'price' => 5.00, 'category' => 'Magnets', 'sort_order' => 64,
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

                // HasSlug reads $model->title, which Product lacks, so set the slug
                // explicitly to avoid empty-slug unique collisions.
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
