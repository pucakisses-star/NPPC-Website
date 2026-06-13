<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

/**
 * Trims the store to the merch mix carried by peer advocacy shops (Vera
 * Institute, Innocence Project, Working Families) — apparel, accessories,
 * stickers, pins, and merch bundles — by unpublishing the non-merch lines:
 * books, prints/posters, letter-writing & holiday-card kits, and the
 * book-based "Prisoner Memoir Reading Bundle". Unpublish (not delete) so the
 * records and their images are preserved and can be restored by setting
 * published = true again. Idempotent.
 */
class RemoveNonMerchStoreProducts extends Command {
    protected $signature = 'store:remove-nonmerch';
    protected $description = 'Unpublish non-merch store products (books, prints, letter-writing kits, book bundle)';

    public function handle(): int {
        $query = Product::where(function ($q) {
            $q->whereIn('category', ['Books', 'Prints', 'Letter Writing'])
                ->orWhere('name', 'Prisoner Memoir Reading Bundle');
        });

        $names = (clone $query)->where('published', true)->pluck('name');

        if ($names->isEmpty()) {
            $this->info('Nothing to unpublish — already trimmed.');

            return self::SUCCESS;
        }

        // Query-builder update: bypasses model events, only flips the flag.
        $count = $query->update(['published' => false]);

        foreach ($names as $n) {
            $this->line("Unpublished: {$n}");
        }
        $this->info("\nDone. Unpublished {$count} product(s). Re-publish any by setting published = true.");

        return self::SUCCESS;
    }
}
