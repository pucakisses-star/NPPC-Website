<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Adds a curated set of political-prisoner books to the store (Books category),
 * each with its cover image and a short description. Cover images are committed
 * under public/images/products/book-*.jpg and copied to the public disk here.
 * Idempotent: a product already present (matched by name) is ensured published
 * in Books and given the cover if it has none; its description/price are left
 * as-is so admin edits are preserved.
 */
final class AddBurningBooks extends Command
{
    protected $signature = 'store:add-burning-books';

    protected $description = 'Add curated political-prisoner books (Books category) with covers';

    /** @var array<int, array{name:string, slug:string, price:float, description:string}> */
    private array $books = [
        [
            'name' => 'I Am Maroon: The True Story of an American Political Prisoner',
            'slug' => 'book-i-am-maroon-the-true-story',
            'price' => 32.50,
            'description' => "Memoir of Russell 'Maroon' Shoatz — Black Panther, Black Liberation Army soldier, and political prisoner held in Pennsylvania prisons for over four decades, including roughly twenty-two years in solitary confinement, before his release shortly before his death in 2021. Written with Kanya D'Almeida.",
        ],
        [
            'name' => 'Rattling the Cages: Oral Histories of North American Political Prisoners',
            'slug' => 'book-rattling-the-cages-oral-histories-of',
            'price' => 28.00,
            'description' => 'Oral histories of North American political prisoners in their own words — interviews with imprisoned revolutionaries and their loved ones documenting a reality the official story denies: that the United States holds political prisoners. Edited by Josh Davidson with Eric King.',
        ],
        [
            'name' => 'Beneath the Mountain: An Anti-Prison Reader',
            'slug' => 'book-beneath-the-mountain-an-anti-prison',
            'price' => 24.95,
            'description' => 'An anthology of radical writing by enslaved, jailed, and imprisoned people across American history, edited by political prisoner and journalist Mumia Abu-Jamal with Jennifer Black.',
        ],
        [
            'name' => 'I Cannot Submit to Injustices: Collected Works of Martin Sostre',
            'slug' => 'book-i-cannot-submit-to-injustices-collected',
            'price' => 24.00,
            'description' => "Collected writings of Martin Sostre — Black Puerto Rican anarchist, bookstore owner, jailhouse lawyer, and political prisoner whose lawsuits reshaped prisoners' rights. Edited by Garrett Felber.",
        ],
        [
            'name' => 'A Continuous Struggle: The Revolutionary Life of Martin Sostre',
            'slug' => 'book-a-continuous-struggle-the-revolutionary-life',
            'price' => 32.00,
            'description' => 'A political biography of Martin Sostre, one of the most important and underappreciated figures in the history of the anti-prison and Black freedom movements. By Garrett Felber.',
        ],
        [
            'name' => 'Black Panther Woman: The Political and Spiritual Life of Ericka Huggins',
            'slug' => 'book-black-panther-woman-the-political-and',
            'price' => 35.00,
            'description' => 'The first biography of Ericka Huggins — Black Panther Party leader, former political prisoner, and educator who brought meditation and spiritual care to the Party. By Mary Frances Phillips.',
        ],
        [
            'name' => 'Revolution in These Times: Black Panther Party Veteran Dhoruba Bin Wahad on Antifascism, Black Liberation, and a Culture of Resistance',
            'slug' => 'book-revolution-in-these-times-black-panther',
            'price' => 20.00,
            'description' => 'Black Panther Party and Black Liberation Army veteran Dhoruba Bin Wahad — who spent nineteen years as a political prisoner before COINTELPRO evidence won his release — on antifascism, Black liberation, and a culture of resistance.',
        ],
        [
            'name' => 'An Amerikan Family',
            'slug' => 'book-an-amerikan-family',
            'price' => 19.99,
            'description' => 'The story of the Shakur family — from the Black Panther Party and Black Liberation Army to Assata, Mutulu, and Tupac — across more than fifty years of Black revolutionary struggle. By Santi Elijah Holley.',
        ],
        [
            'name' => 'Co-Conspirator for Justice: The Revolutionary Life of Dr. Alan Berkman',
            'slug' => 'book-co-conspirator-for-justice-the-revolutionary',
            'price' => 29.95,
            'description' => 'The life of Dr. Alan Berkman, the radical physician imprisoned for aiding revolutionary movements, who went on to lead global fights for access to AIDS treatment. By Susan M. Reverby.',
        ],
        [
            'name' => 'Ingrid Schubert: Letters from Prison 1970-1977',
            'slug' => 'book-ingrid-schubert-letters-from-prison-1970',
            'price' => 24.95,
            'description' => 'The prison letters of Ingrid Schubert, revealing the daily struggle of a political prisoner resisting repression in 1970s West Germany.',
        ],
        [
            'name' => 'Be the Light: How She Became Angela Davis',
            'slug' => 'book-be-the-light-how-she-became',
            'price' => 19.99,
            'description' => 'A picture-book biography of Angela Davis — scholar, abolitionist, and former political prisoner — for young readers. By Daria Peoples.',
        ],
        [
            'name' => 'A Southern Panther: Conversations with Malik Rahim',
            'slug' => 'book-a-southern-panther-conversations-with-malik',
            'price' => 16.00,
            'description' => 'Conversations with Malik Rahim, former Black Panther and lifelong organizer who co-founded the Common Ground Collective after Hurricane Katrina, on hope and persistence in movement work. With James R. Tracy.',
        ],
        [
            'name' => 'Words for My Comrades: A Political History of Tupac Shakur',
            'slug' => 'book-words-for-my-comrades-a-political',
            'price' => 32.00,
            'description' => 'A history of Tupac Shakur as a radical son of the Black Panther Party, tracing his politics through the Panthers and the Black Liberation Army. By Dean Van Nguyen.',
        ],
        [
            'name' => 'A Ministry of Risk: Writings on Peace and Nonviolence',
            'slug' => 'book-a-ministry-of-risk-writings-on',
            'price' => 24.95,
            'description' => 'Selected writings of Philip Berrigan — priest, Plowshares activist, and frequent political prisoner — on a lifetime of nonviolent resistance to war and empire. Edited by Brad Wolf.',
        ],
        [
            'name' => 'Dangerous, Dirty, Violent, and Young: A Fugitive Family in the Revolutionary Underground',
            'slug' => 'book-dangerous-dirty-violent-and-young-a',
            'price' => 32.99,
            'description' => 'A memoir of growing up underground as the child of Weather Underground fugitives, by the son of Bill Ayers and Bernardine Dohrn.',
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
