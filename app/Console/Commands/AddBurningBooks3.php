<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Third batch of curated political-prisoner / movement books for the store
 * (Books category), each with its cover image and a short original description.
 * Covers are committed under public/images/products/book-*.jpg and copied to the
 * public disk here. Idempotent: a product already present (matched by name) is
 * ensured published in Books and given the cover if it has none; its
 * description/price are left as-is so admin edits are preserved.
 */
final class AddBurningBooks3 extends Command
{
    protected $signature = 'store:add-burning-books-3';

    protected $description = 'Add a third batch of political-prisoner books (Books category) with covers';

    /** @var array<int, array{name:string, slug:string, price:float, description:string}> */
    private array $books = [
        [
            'name' => 'Prison Writings: My Life is My Sun Dance',
            'slug' => 'book-prison-writings-my-life-is-my-sun-dance',
            'price' => 18.99,
            'description' => 'The prison writings of Leonard Peltier, the American Indian Movement activist who served nearly fifty years over the 1975 deaths of two FBI agents he always maintained he did not cause.',
        ],
        [
            'name' => 'Assata: An Autobiography',
            'slug' => 'book-assata-an-autobiography',
            'price' => 19.99,
            'description' => 'The autobiography of Assata Shakur — Black Panther and Black Liberation Army member who escaped prison and was granted asylum in Cuba — with forewords by Angela Davis and Lennox S. Hinds.',
        ],
        [
            'name' => 'Assata Taught Me: State Violence, Racial Capitalism, and the Movement for Black Lives',
            'slug' => 'book-assata-taught-me-state-violence-racial-capitalism',
            'price' => 18.95,
            'description' => 'Donna Murch\'s essays on state violence, racial capitalism, mass incarceration, and the movement for Black lives, taking their title from Assata Shakur.',
        ],
        [
            'name' => 'Prison Memoirs of a Japanese Woman',
            'slug' => 'book-prison-memoirs-of-a-japanese-woman',
            'price' => 14.00,
            'description' => 'The prison memoir of Fumiko Kaneko, the Japanese anarchist convicted in the 1920s of plotting against the emperor, written before her death in custody at 23.',
        ],
        [
            'name' => 'Life of an Anarchist: The Alexander Berkman Reader',
            'slug' => 'book-life-of-an-anarchist-the-alexander-berkman-reader',
            'price' => 16.95,
            'description' => 'A reader of anarchist Alexander Berkman\'s writings — including his prison memoirs and account of the Russian Revolution — with an introduction by Howard Zinn.',
        ],
        [
            'name' => 'Direct Action: Memoirs of an Urban Guerrilla',
            'slug' => 'book-direct-action-memoirs-of-an-urban-guerrilla',
            'price' => 19.95,
            'description' => 'Ann Hansen\'s firsthand account of the Canadian anarchist guerrilla group known as the Vancouver Five, for which she served seven years in prison.',
        ],
        [
            'name' => 'Outrage: An Anarchist Memoir of the Penal Colony',
            'slug' => 'book-outrage-an-anarchist-memoir-of-the-penal-colony',
            'price' => 20.00,
            'description' => 'The memoir of anarchist Clement Duval, sentenced in 1887 to France\'s penal colony in French Guiana, from which he ultimately escaped.',
        ],
    ];

    public function handle(): int
    {
        $created = 0;
        $updated = 0;

        foreach ($this->books as $b) {
            $imagePath = "products/{$b['slug']}.jpg";
            $source = public_path("images/products/{$b['slug']}.jpg");
            if (is_file($source)) {
                Storage::disk('public')->put($imagePath, file_get_contents($source));
            }

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
                    'published' => true,
                    'featured' => false,
                    'image' => is_file($source) ? $imagePath : null,
                    'slug' => $slug,
                ]);
                $this->info("Added: {$b['name']}");
                $created++;

                continue;
            }

            $product->category = 'Books';
            $product->published = true;
            if (! $product->image && is_file($source)) {
                $product->image = $imagePath;
            }
            $product->save();
            $this->line("Updated: {$b['name']}");
            $updated++;
        }

        $this->info("\nDone. Added {$created}, updated {$updated}.");

        return self::SUCCESS;
    }
}
