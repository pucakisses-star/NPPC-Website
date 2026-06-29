<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

/**
 * Re-splits store products into their own categories: the bumper stickers and
 * sticker packs into Stickers, the magnets into Magnets, and the enamel pins and
 * NPPC Button into Pins. (Reverses the earlier fold-into-Accessories so these can
 * be browsed as filter pills.) Sets each product's category by name, so it is
 * correct whether or not the products were previously merged into Accessories.
 * Idempotent; safe to re-run.
 */
final class ResplitStoreCategories extends Command
{
    protected $signature = 'store:resplit-categories';

    protected $description = 'Put sticker/magnet/pin products into their own Stickers/Magnets/Pins categories';

    /** @var array<string, array<int, string>> */
    private array $map = [
        'Stickers' => [
            'NPPC Sticker Pack (10)',
            'Free Them All Sticker',
            'Black Liberation Slogan Sticker Set (5)',
            'Free Them All Bumper Sticker',
            'NPPC Bumper Sticker',
        ],
        'Magnets' => [
            'Free Them All Car Magnet',
            'NPPC Fridge Magnet',
        ],
        'Pins' => [
            'NPPC Enamel Pin',
            'Free Mumia Enamel Pin',
            'Free Leonard Peltier Enamel Pin',
            'NPPC Button',
        ],
    ];

    public function handle(): int
    {
        $moved = 0;
        $missing = 0;
        foreach ($this->map as $category => $names) {
            foreach ($names as $name) {
                $product = Product::where('name', $name)->first();
                if (! $product) {
                    $this->warn("Not found: {$name}");
                    $missing++;

                    continue;
                }
                if ($product->category === $category) {
                    $this->line("Already {$category}: {$name}");

                    continue;
                }
                $product->category = $category;
                $product->save();
                $this->info("{$name} → {$category}");
                $moved++;
            }
        }
        $this->info("\nDone. Re-categorized {$moved} product(s)".($missing ? ", {$missing} not found." : '.'));

        return self::SUCCESS;
    }
}
