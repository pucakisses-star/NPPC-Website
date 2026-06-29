<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Adds a curated set of political-prisoner zines/booklets to the store (Zines
 * category), each with its cover image and a short description. Covers are
 * committed under public/images/products/zine-*.jpg and copied to the public
 * disk here. Idempotent: a product already present (matched by name) is ensured
 * published in Zines and given the cover if it has none; description/price are
 * left as-is so admin edits are preserved.
 */
final class AddBurningZines extends Command
{
    protected $signature = 'store:add-burning-zines';

    protected $description = 'Add curated political-prisoner zines (Zines category) with covers';

    /** @var array<int, array{name:string, slug:string, price:float, description:string}> */
    private array $zines = [
        [
            'name' => 'Enemies of the State: An Interview with Anti-Imperialist Political Prisoners',
            'slug' => 'zine-enemies-of-the-state-an-interview',
            'price' => 7.00,
            'description' => 'Interviews with anti-imperialist political prisoners Laura Whitehorn, David Gilbert, and Marilyn Buck on armed struggle, solidarity, and decades behind bars.',
        ],
        [
            'name' => 'Words from Assata Shakur',
            'slug' => 'zine-words-from-assata-shakur',
            'price' => 4.50,
            'description' => 'Short writings by Assata Shakur — former Black Panther and Black Liberation Army member who escaped prison and was granted asylum in Cuba.',
        ],
        [
            'name' => 'On the Black Liberation Army',
            'slug' => 'zine-on-the-black-liberation-army',
            'price' => 3.50,
            'description' => "Jalil Muntaqim's inside account and critique of the Black Liberation Army, written from prison in 1979.",
        ],
        [
            'name' => 'Our Commitment is to Our Communities: Mass Incarceration, Political Prisoners and Building a Movement for Community-Based Justice',
            'slug' => 'zine-our-commitment-is-to-our-communities',
            'price' => 5.00,
            'description' => 'David Gilbert, writing from prison, on mass incarceration, political prisoners, and building a movement for community-based justice.',
        ],
        [
            'name' => 'Coming of Age: A New Afrikan Revolutionary',
            'slug' => 'zine-coming-of-age-a-new',
            'price' => 2.50,
            'description' => 'The political coming-of-age of Safiya Bukhari — Black Panther, Black Liberation Army member, and former political prisoner.',
        ],
        [
            'name' => 'Soledad Brothers',
            'slug' => 'zine-soledad-brothers',
            'price' => 2.00,
            'description' => 'A historical reprint of a pamphlet raising awareness for the Soledad Brothers — George Jackson, Fleeta Drumgo, and John Clutchette.',
        ],
        [
            'name' => 'Queer Fire: The George Jackson Brigade, Men Against Sexism, and Gay Struggle Against Prison',
            'slug' => 'zine-queer-fire-the-george-jackson-brigade',
            'price' => 5.00,
            'description' => 'Histories, speeches, and interviews on the George Jackson Brigade, Men Against Sexism, and queer struggle against the prison.',
        ],
        [
            'name' => 'The Trial Statements of Ray Luc Levasseur',
            'slug' => 'zine-the-trial-statements-of-ray-luc',
            'price' => 5.00,
            'description' => 'The courtroom statements of Ray Luc Levasseur — Vietnam veteran and member of the United Freedom Front, a defendant in the 1980s sedition trials.',
        ],
        [
            'name' => 'With Whatever Weapons Come to Hand: The Sentencing Statements of Richard Williams',
            'slug' => 'zine-with-whatever-weapons-come-to-hand',
            'price' => 3.50,
            'description' => 'The sentencing statements of Richard Williams, political prisoner and member of the United Freedom Front.',
        ],
        [
            'name' => 'Striking Back Against Prison Slavery',
            'slug' => 'zine-striking-back-against-prison-slavery',
            'price' => 5.00,
            'description' => "An interview with revolutionary prisoner Kevin 'Rashid' Johnson on prison labor, abolition, and resistance behind the walls.",
        ],
        [
            'name' => 'On the Vanguard Once Again',
            'slug' => 'zine-on-the-vanguard-once-again',
            'price' => 4.00,
            'description' => "Revolutionary prisoner Kevin 'Rashid' Johnson on the role of the vanguard in revolutionary organizing.",
        ],
        [
            'name' => 'Death by Regulation: a Message from a Death Camp',
            'slug' => 'zine-death-by-regulation-a-message-from',
            'price' => 3.00,
            'description' => "Russell 'Maroon' Shoatz on sensory deprivation, isolation, and control units, written from long-term solitary confinement.",
        ],
        [
            'name' => 'Puerto Rico: Indepence is a Necessity',
            'slug' => 'zine-puerto-rico-indepence-is-a-necessity',
            'price' => 5.00,
            'description' => 'Rafael Cancel Miranda — Puerto Rican Nationalist and political prisoner of more than twenty-five years — on independence and colonialism.',
        ],
        [
            'name' => 'BLA Political Dictionary',
            'slug' => 'zine-bla-political-dictionary',
            'price' => 3.50,
            'description' => 'An educational glossary of political terms compiled by the Black Liberation Army Coordinating Committee.',
        ],
        [
            'name' => 'Martin Sostre in Court',
            'slug' => 'zine-martin-sostre-in-court',
            'price' => 5.50,
            'description' => 'A facsimile reprint of a 1969 record of Martin Sostre — Black anarchist, bookstore owner, and political prisoner — in court.',
        ],
        [
            'name' => 'Letters From Prison: Martin Sostre Defense Committee',
            'slug' => 'zine-letters-from-prison-martin-sostre-defense',
            'price' => 5.00,
            'description' => 'Prison letters of political prisoner Martin Sostre, compiled by his defense committee at SUNY Buffalo.',
        ],
        [
            'name' => 'Martin Sostre: Life and Legacy',
            'slug' => 'zine-martin-sostre-life-and-legacy',
            'price' => 8.00,
            'description' => 'A youth-produced zine on the life and legacy of political prisoner and jailhouse lawyer Martin Sostre.',
        ],
        [
            'name' => 'The New Prisoner',
            'slug' => 'zine-the-new-prisoner',
            'price' => 3.00,
            'description' => "A facsimile reprint of Martin Sostre's 1973 writing on the political prisoner and the carceral system.",
        ],
        [
            'name' => 'Martin Luther King Was a Lawbreaker',
            'slug' => 'zine-martin-luther-king-was-a-lawbreaker',
            'price' => 2.00,
            'description' => "Martin Sostre's 1970 essay, first printed as a prison newsletter supplement, on civil disobedience and unjust law.",
        ],
        [
            'name' => 'After Prison: Words from Former Earth and Animal Liberation Prisoners',
            'slug' => 'zine-after-prison-words-from-former-earth',
            'price' => 3.00,
            'description' => 'Reflections from former Earth and animal liberation prisoners — Rod Coronado, Jeff Luers, Jordan Halliday, and Josh Harper — on imprisonment and movement repression.',
        ],
        [
            'name' => 'Under New Management: Stories About Resistance to Prisons in Ontario and Québec',
            'slug' => 'zine-under-new-management-stories-about-resistance',
            'price' => 6.00,
            'description' => 'A history of the Canadian prison system and of resistance to prisons in Ontario and Québec.',
        ],
        [
            'name' => 'Resistance is a Duty! And Other Essays by Comrades from Action Directe',
            'slug' => 'zine-resistance-is-a-duty-and-other',
            'price' => 4.00,
            'description' => 'Essays by imprisoned members of the French armed-struggle group Action Directe.',
        ],
        [
            'name' => 'The Theory and Practice of Armed Struggle in the Northwest: A Historical Analysis',
            'slug' => 'zine-the-theory-and-practice-of-armed',
            'price' => 4.00,
            'description' => 'An essay by Ed Mead of the George Jackson Brigade, written while imprisoned in Washington State.',
        ],
        [
            'name' => 'John Brown',
            'slug' => 'zine-john-brown',
            'price' => 4.00,
            'description' => 'A reprint examining the righteous struggle of abolitionist insurgent John Brown.',
        ],
        [
            'name' => 'Democratic Confederalism: A Proposal for the Liberation of the Kurdish People',
            'slug' => 'zine-democratic-confederalism-a-proposal-for-the',
            'price' => 5.00,
            'description' => "Abdullah Öcalan's proposal for the liberation of the Kurdish people, written from the Turkish prison where he has been held since 1999.",
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
