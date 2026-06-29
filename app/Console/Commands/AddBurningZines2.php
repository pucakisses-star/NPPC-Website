<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Second batch of curated political-prisoner / movement zines for the store
 * (Zines category), each with its cover image and a short original description.
 * Covers are committed under public/images/products/zine-*.jpg and copied to the
 * public disk here. Idempotent: a product already present (matched by name) is
 * ensured published in Zines and given the cover if it has none; its
 * description/price are left as-is so admin edits are preserved.
 */
final class AddBurningZines2 extends Command
{
    protected $signature = 'store:add-burning-zines-2';

    protected $description = 'Add a second batch of political-prisoner zines (Zines category) with covers';

    /** @var array<int, array{name:string, slug:string, price:float, description:string}> */
    private array $zines = [
        [
            'name' => 'ALF on Trial: Capitalism Under Attack in the 1980s',
            'slug' => 'zine-alf-on-trial-capitalism-under-attack-in-the-1980s',
            'price' => 4.50,
            'description' => 'A facsimile reprint giving an underground account of the first major Animal Liberation Front trial in 1980s Britain, where ten activists faced prosecution.',
        ],
        [
            'name' => 'Essays from the Minister of Defense',
            'slug' => 'zine-essays-from-the-minister-of-defense',
            'price' => 3.00,
            'description' => 'Huey P. Newton\'s late-1960s essays on armed self-defense, written as Minister of Defense of the Black Panther Party.',
        ],
        [
            'name' => 'Essays and Letters',
            'slug' => 'zine-essays-and-letters',
            'price' => 5.00,
            'description' => 'Essays and prison letters by anarchist Dan Baker, written during his incarceration in Tennessee.',
        ],
        [
            'name' => 'Civil Disobedience',
            'slug' => 'zine-civil-disobedience',
            'price' => 4.00,
            'description' => 'Henry David Thoreau\'s 1849 essay on the duty of civil disobedience and refusing to support unjust government.',
        ],
        [
            'name' => 'G8: Globalisation, Sweatshops, Activist Response',
            'slug' => 'zine-g8-globalisation-sweatshops-activist-response',
            'price' => 3.50,
            'description' => 'A No Sweat pamphlet on neoliberalism and anti-sweatshop organizing, written in response to the 2005 G8 summit in Scotland.',
        ],
        [
            'name' => 'The Animal Liberation Movement',
            'slug' => 'zine-the-animal-liberation-movement',
            'price' => 3.50,
            'description' => 'Peter Singer\'s classic short introduction to speciesism and the ideas behind animal liberation.',
        ],
        [
            'name' => 'Anarchist Survival Guide for Understanding Gestapo Swine Interrogation Mind Games: Stay Free by Shutting the Fuck Up!',
            'slug' => 'zine-anarchist-survival-guide-for-understanding-gestapo',
            'price' => 4.95,
            'description' => 'Imprisoned anarchist Harold H. Thompson\'s guide to withstanding police interrogation and refusing to inform.',
        ],
        [
            'name' => '25 Years on the MOVE',
            'slug' => 'zine-25-years-on-the-move',
            'price' => 10.00,
            'description' => 'An in-depth history of the MOVE Organization in their own words — an expanded edition of \'20 Years on the MOVE.\'',
        ],
        [
            'name' => 'How Not to Get Arrested at a Demonstration',
            'slug' => 'zine-how-not-to-get-arrested-at-a-demonstration',
            'price' => 4.95,
            'description' => 'Bill DiPaola\'s illustrated zine of practical survival tips for demonstrators, from first-timers to seasoned activists.',
        ],
    ];

    public function handle(): int
    {
        $created = 0;
        $updated = 0;

        foreach ($this->zines as $z) {
            $imagePath = "products/{$z['slug']}.jpg";
            $source = public_path("images/products/{$z['slug']}.jpg");
            if (is_file($source)) {
                Storage::disk('public')->put($imagePath, file_get_contents($source));
            }

            $product = Product::where('name', $z['name'])->first();

            if (! $product) {
                $base = Str::slug($z['name']);
                $slug = $base;
                $i = 2;
                while (Product::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                Product::create([
                    'name' => $z['name'],
                    'description' => $z['description'],
                    'price' => $z['price'],
                    'category' => 'Zines',
                    'published' => true,
                    'featured' => false,
                    'image' => is_file($source) ? $imagePath : null,
                    'slug' => $slug,
                ]);
                $this->info("Added: {$z['name']}");
                $created++;

                continue;
            }

            $product->category = 'Zines';
            $product->published = true;
            if (! $product->image && is_file($source)) {
                $product->image = $imagePath;
            }
            $product->save();
            $this->line("Updated: {$z['name']}");
            $updated++;
        }

        $this->info("\nDone. Added {$created}, updated {$updated}.");

        return self::SUCCESS;
    }
}
