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
                'name' => 'Live From Death Row — Mumia Abu-Jamal',
                'description' => "Mumia Abu-Jamal's collection of essays written from death row, where he was held from 1982 until his sentence was commuted to life in 2011. A foundational text of contemporary U.S. abolitionist writing.",
                'price' => 20.00, 'sort_order' => 30,
            ],
            [
                'name' => 'Prison Writings: My Life Is My Sun Dance — Leonard Peltier',
                'description' => "Leonard Peltier's memoir, written from federal prison, weaving Indigenous spirituality with the political history of the American Indian Movement.",
                'price' => 25.00, 'sort_order' => 31,
            ],
            [
                'name' => 'Assata: An Autobiography — Assata Shakur',
                'description' => "Assata Shakur's autobiography of her life in the Black Panther Party, the Black Liberation Army, the 1973 New Jersey Turnpike shootout, her conviction, escape from prison, and exile in Cuba.",
                'price' => 20.00, 'sort_order' => 32,
            ],
            [
                'name' => 'Prison Memoirs of an Anarchist — Alexander Berkman',
                'description' => "Alexander Berkman's 1912 account of fourteen years in the Western Penitentiary of Pennsylvania after his attempted assassination of Henry Clay Frick during the Homestead Strike. One of the foundational texts of American prison literature.",
                'price' => 20.00, 'sort_order' => 33,
            ],
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
