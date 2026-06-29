<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

/**
 * Folds the Magnets, Stickers, and Pins product categories into Accessories:
 * every product in those three categories is recategorized to Accessories, so
 * the store's category list (built dynamically from products) no longer shows
 * them. Nothing is deleted. Prompts first (pass --force to skip). Idempotent.
 */
final class MergeStoreCategoriesIntoAccessories extends Command
{
    protected $signature = 'store:merge-into-accessories {--force : Skip the confirmation prompt}';

    protected $description = 'Move all Magnets/Stickers/Pins products into Accessories (removes those three category filters)';

    private const FROM = ['Magnets', 'Stickers', 'Pins'];

    private const TO = 'Accessories';

    public function handle(): int
    {
        $products = Product::whereIn('category', self::FROM)->orderBy('category')->get();

        if ($products->isEmpty()) {
            $this->info('No products in Magnets, Stickers, or Pins — nothing to move.');

            return self::SUCCESS;
        }

        $this->warn($products->count().' product(s) will be recategorized into '.self::TO.':');
        foreach ($products as $p) {
            $this->line("  • {$p->category} → ".self::TO." : {$p->name}");
        }

        if (! $this->option('force') && ! $this->confirm('Proceed?', true)) {
            $this->info('Aborted.');

            return self::SUCCESS;
        }

        $moved = Product::whereIn('category', self::FROM)->update(['category' => self::TO]);

        $this->info("\nDone. Moved {$moved} product(s) into ".self::TO.'. The Magnets, Stickers, and Pins categories will no longer appear.');

        return self::SUCCESS;
    }
}
