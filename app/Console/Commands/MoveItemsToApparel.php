<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

/**
 * Moves the Free Them All Bandana and NPPC Knit Beanie out of Accessories and
 * into the Apparel category. Idempotent; safe to re-run.
 */
final class MoveItemsToApparel extends Command
{
    protected $signature = 'store:move-to-apparel';

    protected $description = 'Move the Free Them All Bandana and NPPC Knit Beanie into the Apparel category';

    private const NAMES = ['Free Them All Bandana', 'NPPC Knit Beanie'];

    public function handle(): int
    {
        $moved = 0;
        foreach (self::NAMES as $name) {
            $product = Product::where('name', $name)->first();
            if (! $product) {
                $this->warn("Not found: {$name}");

                continue;
            }
            if ($product->category === 'Apparel') {
                $this->line("Already in Apparel: {$name}");

                continue;
            }
            $product->category = 'Apparel';
            $product->save();
            $this->info("Moved to Apparel: {$name}");
            $moved++;
        }

        $this->info("\nDone. Moved {$moved} item(s) into Apparel.");

        return self::SUCCESS;
    }
}
