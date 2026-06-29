<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

/**
 * Removes four specific legacy book product entries (the author-suffixed
 * versions seeded by store:add-books / store:add-products) by their exact
 * names. Three of them now duplicate the cleaner Burning Books editions, and
 * "Prison Memoirs of an Anarchist" is being dropped outright. Matches by exact
 * name only — nothing else (e.g. "Assata: An Autobiography" without the author
 * suffix, "Soledad Brother — George Jackson", and the Berkman reader are left
 * untouched). Safe to run more than once: a name already gone is reported and
 * skipped.
 */
final class RemoveDuplicateBooks extends Command
{
    protected $signature = 'store:remove-duplicate-books';

    protected $description = 'Remove four specific legacy/duplicate book entries by exact name';

    /** @var array<int, string> */
    private array $names = [
        'Live From Death Row — Mumia Abu-Jamal',
        'Prison Writings: My Life Is My Sun Dance — Leonard Peltier',
        'Assata: An Autobiography — Assata Shakur',
        'Prison Memoirs of an Anarchist — Alexander Berkman',
    ];

    public function handle(): int
    {
        $removed = 0;

        foreach ($this->names as $name) {
            $products = Product::where('name', $name)->get();

            if ($products->isEmpty()) {
                $this->line("Not found (skipped): {$name}");

                continue;
            }

            foreach ($products as $product) {
                $product->delete();
                $this->info("Removed: {$name}");
                $removed++;
            }
        }

        $this->info("\nDone. Removed {$removed} product(s).");

        return self::SUCCESS;
    }
}
