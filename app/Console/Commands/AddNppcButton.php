<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Adds a single product: the NPPC Button — a pin-back button listed in the Pins
 * category alongside the enamel pins. Dedupes by name and is safe to re-run.
 * After inserting the row it calls store:generate-mockups so the button gets a
 * studio mock image matching the rest of the catalog (its name and a "button"
 * shape are registered there).
 */
final class AddNppcButton extends Command
{
    protected $signature = 'store:add-button';

    protected $description = 'Add the NPPC Button (pin-back button) to the store';

    public function handle(): int
    {
        $name = 'NPPC Button';

        if (Product::where('name', $name)->exists()) {
            $this->warn("{$name} already exists — refreshing its mock image only.");
        } else {
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
                    'category' => 'Pins',
                    'sort_order' => 63,
                    'published' => true,
                    'featured' => false,
                    'image' => "products/{$slug}.svg",
                    'slug' => $slug,
                ]);
            });

            $this->info("Added {$name}  (\$3.00)  [Pins]");
        }

        // Render/refresh the studio mock image — the name and "button" shape are
        // registered in store:generate-mockups.
        $this->call('store:generate-mockups');

        return self::SUCCESS;
    }
}
