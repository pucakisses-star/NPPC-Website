<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Adds a single product: the NPPC Button — a pin-back button in the Accessories
 * category. Dedupes by name and is safe to re-run. The product is created
 * without an image so an admin can upload a real photo through the panel.
 */
final class AddNppcButton extends Command
{
    protected $signature = 'store:add-button';

    protected $description = 'Add the NPPC Button (pin-back button) to the store';

    public function handle(): int
    {
        $name = 'NPPC Button';

        if (Product::where('name', $name)->exists()) {
            $this->warn("{$name} already exists — nothing to do.");

            return self::SUCCESS;
        }

        // HasSlug reads $model->title, which Product lacks, so set the slug
        // explicitly to avoid empty-slug unique collisions.
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        DB::transaction(function () use ($name, $slug) {
            Product::create([
                'name' => $name,
                'description' => 'Classic 2.25" pin-back button with the National Political Prisoner Coalition logo. Sturdy steel shell and a safety-pin back — wear it on a jacket, a tote, or a backpack.',
                'price' => 3.00,
                'category' => 'Accessories',
                'sort_order' => 63,
                'published' => true,
                'featured' => false,
                'slug' => $slug,
            ]);
        });

        $this->info("Added {$name}  (\$3.00)  [Pins]");

        return self::SUCCESS;
    }
}
