<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Adds (or re-publishes) the political-prisoner book line in the Books category,
 * so the Books category appears in the store. Idempotent: a book that already
 * exists is simply ensured published and categorized as Books (its image and
 * other fields left untouched); a missing book is created without an image so an
 * admin can upload a cover through the panel.
 */
final class AddStoreBooks extends Command
{
    protected $signature = 'store:add-books';

    protected $description = 'Add / re-publish the political-prisoner book line (Books category)';

    public function handle(): int
    {
        $books = [
            [
                'name' => 'Soledad Brother — George Jackson',
                'description' => "George Jackson's prison letters, written from 1964 to 1970, foundational to the Black liberation prison movement.",
                'price' => 20.00, 'sort_order' => 34,
            ],
        ];

        $created = 0;
        $republished = 0;

        foreach ($books as $b) {
            DB::transaction(function () use ($b, &$created, &$republished) {
                $product = Product::where('name', $b['name'])->first();

                if (! $product) {
                    $base = Str::slug($b['name']);
                    $slug = $base;
                    $i = 2;
                    while (Product::where('slug', $slug)->exists()) {
                        $slug = $base.'-'.$i++;
                    }
                    Product::create([
                        'name' => $b['name'],
                        'description' => $b['description'],
                        'price' => $b['price'],
                        'category' => 'Books',
                        'sort_order' => $b['sort_order'],
                        'published' => true,
                        'featured' => false,
                        'slug' => $slug,
                    ]);
                    $this->info("Added: {$b['name']}");
                    $created++;

                    return;
                }

                // Already exists — just ensure it's visible in the Books category.
                $product->category = 'Books';
                $product->published = true;
                $product->save();
                $this->line("Re-published: {$b['name']}");
                $republished++;
            });
        }

        $this->info("\nDone. Added {$created}, re-published {$republished}. The Books category is now live.");

        return self::SUCCESS;
    }
}
