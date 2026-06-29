<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

/**
 * Sorts every store product into its categories, allowing more than one per
 * product (e.g. a bumper sticker is Stickers AND Accessories; the button bundle
 * is Bundles AND Pins). Sets the primary `category` (first) and the additional
 * `categories` array. Known products use an explicit map; anything else is
 * classified by name keywords, and any product that can't be classified is
 * reported so it can be sorted by hand in the admin. Idempotent; safe to re-run.
 * Supersedes store:resplit-categories.
 */
final class SortStoreCategories extends Command
{
    protected $signature = 'store:sort-categories';

    protected $description = 'Sort products into one or more categories (e.g. Stickers + Accessories, Bundles + Pins)';

    /** @var array<string, array<int, string>> primary category first */
    private array $map = [
        // Apparel
        'NPPC Logo T-Shirt' => ['Apparel'],
        'Free Them All T-Shirt' => ['Apparel'],
        'Free Leonard Peltier T-Shirt' => ['Apparel'],
        'Free Mumia Abu-Jamal T-Shirt' => ['Apparel'],
        'Letter Writing Saves Lives T-Shirt' => ['Apparel'],
        'Abolition T-Shirt' => ['Apparel'],
        'NPPC Heavyweight Hoodie' => ['Apparel'],
        'Free Them All Hoodie' => ['Apparel'],
        'NPPC Embroidered Dad Cap' => ['Apparel'],
        'Free Them All Long-Sleeve Tee' => ['Apparel'],
        'NPPC Crewneck Sweatshirt' => ['Apparel'],
        'NPPC Knit Beanie' => ['Apparel'],
        'Free Them All Bandana' => ['Apparel'],
        'NPPC Jacket' => ['Apparel'],
        // Accessories
        'NPPC Canvas Tote Bag' => ['Accessories'],
        'Free Them All Tote Bag' => ['Accessories'],
        'Letter Writing Saves Lives Mug' => ['Accessories'],
        'Abolition Enamel Mug' => ['Accessories'],
        'NPPC Insulated Water Bottle' => ['Accessories'],
        'Free All Political Prisoners Bottle' => ['Accessories'],
        // Stickers (also Accessories)
        'NPPC Sticker Pack (10)' => ['Stickers', 'Accessories'],
        'Free Them All Sticker' => ['Stickers', 'Accessories'],
        'Black Liberation Slogan Sticker Set (5)' => ['Stickers', 'Accessories'],
        'Free Them All Bumper Sticker' => ['Stickers', 'Accessories'],
        'NPPC Bumper Sticker' => ['Stickers', 'Accessories'],
        // Magnets (also Accessories)
        'Free Them All Car Magnet' => ['Magnets', 'Accessories'],
        'NPPC Fridge Magnet' => ['Magnets', 'Accessories'],
        // Pins (also Accessories)
        'NPPC Enamel Pin' => ['Pins', 'Accessories'],
        'Free Mumia Enamel Pin' => ['Pins', 'Accessories'],
        'Free Leonard Peltier Enamel Pin' => ['Pins', 'Accessories'],
        'NPPC Button' => ['Pins', 'Accessories'],
        // Bundles
        'NPPC Button Bundle (5)' => ['Bundles', 'Pins'],
        'NPPC Solidarity Bundle' => ['Bundles', 'Apparel', 'Stickers', 'Pins'],
        'Prisoner Memoir Reading Bundle' => ['Bundles', 'Books'],
        // Books
        'Live From Death Row — Mumia Abu-Jamal' => ['Books'],
        'Prison Writings: My Life Is My Sun Dance — Leonard Peltier' => ['Books'],
        'Assata: An Autobiography — Assata Shakur' => ['Books'],
        'Prison Memoirs of an Anarchist — Alexander Berkman' => ['Books'],
        'Soledad Brother — George Jackson' => ['Books'],
    ];

    public function handle(): int
    {
        $sorted = 0;
        $unsorted = [];

        foreach (Product::all() as $product) {
            $cats = $this->map[$product->name] ?? $this->classify($product->name);

            if (empty($cats)) {
                $unsorted[] = $product->name;

                continue;
            }

            $product->category = $cats[0];
            $product->categories = array_values(array_slice($cats, 1)) ?: null;
            $product->save();

            $this->info($product->name.'  →  '.implode(', ', $cats));
            $sorted++;
        }

        $this->info("\nDone. Sorted {$sorted} product(s).");
        if ($unsorted) {
            $this->warn(count($unsorted).' could not be auto-sorted (set a category in the admin):');
            foreach ($unsorted as $name) {
                $this->line('  • '.$name);
            }
        }

        return self::SUCCESS;
    }

    /**
     * Best-effort keyword classifier for products not in the explicit map.
     *
     * @return array<int, string>
     */
    private function classify(string $name): array
    {
        $n = strtolower($name);
        $cats = [];
        $add = function (string $c) use (&$cats) {
            if (! in_array($c, $cats, true)) {
                $cats[] = $c;
            }
        };

        $isButtonBundle = str_contains($n, 'button') && str_contains($n, 'bundle');

        foreach (['t-shirt', 'tshirt', 'tee', 'hoodie', 'sweatshirt', 'crewneck', 'cap', 'beanie', 'bandana', 'jacket'] as $k) {
            if (str_contains($n, $k)) {
                $add('Apparel');
                break;
            }
        }
        if (str_contains($n, 'zine')) {
            $add('Zines');
        }
        if (str_contains($n, 'sticker')) {
            $add('Stickers');
            $add('Accessories');
        }
        if (str_contains($n, 'magnet')) {
            $add('Magnets');
            $add('Accessories');
        }
        if ($isButtonBundle) {
            $add('Bundles');
            $add('Pins');
        } elseif (str_contains($n, 'enamel pin') || str_contains($n, ' pin') || str_contains($n, 'button')) {
            $add('Pins');
            $add('Accessories');
        }
        if (! $isButtonBundle && str_contains($n, 'bundle')) {
            $add('Bundles');
        }
        if (str_contains($n, 'book') || str_contains($n, 'memoir')) {
            $add('Books');
        }
        foreach (['tote', 'mug', 'bottle', 'tumbler', 'pouch', 'keychain', 'patch', 'poster', 'print'] as $k) {
            if (str_contains($n, $k)) {
                $add('Accessories');
                break;
            }
        }

        return $cats;
    }
}
