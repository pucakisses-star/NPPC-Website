<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Second batch of curated political-prisoner / movement books for the store
 * (Books category), each with its cover image and a short original description.
 * Covers are committed under public/images/products/book-*.jpg and copied to the
 * public disk here. Idempotent: a product already present (matched by name) is
 * ensured published in Books and given the cover if it has none; its
 * description/price are left as-is so admin edits are preserved.
 */
final class AddBurningBooks2 extends Command
{
    protected $signature = 'store:add-burning-books-2';

    protected $description = 'Add a second batch of political-prisoner books (Books category) with covers';

    /** @var array<int, array{name:string, slug:string, price:float, description:string}> */
    private array $books = [
        [
            'name' => 'Fires in the Night: The Earth Liberation Front, the Fbi, and a Secret History of Eco-Sabotage',
            'slug' => 'book-fires-in-the-night-the-earth-liberation-front-the-fbi',
            'price' => 32.00,
            'description' => 'A history of the Earth Liberation Front, the FBI\'s pursuit of it, and the underground campaign of eco-sabotage.',
        ],
        [
            'name' => 'Levitating the Pentagon and Other Uplifting Stories: A Life of Activism',
            'slug' => 'book-levitating-the-pentagon-and-other-uplifting-stories-a',
            'price' => 22.00,
            'description' => 'A memoir of seven decades on the front lines of American protest by longtime activist Nancy Kurshan.',
        ],
        [
            'name' => 'The Fort Bragg Cartel: Drug Trafficking and Murder in the Special Forces',
            'slug' => 'book-the-fort-bragg-cartel-drug-trafficking-and-murder-in-t',
            'price' => 20.00,
            'description' => 'An investigation into drug trafficking and a string of murders within the Army\'s Special Forces at Fort Bragg.',
        ],
        [
            'name' => 'Days of Dissent: Revolts, Strikes, and Rebel Histories',
            'slug' => 'book-days-of-dissent-revolts-strikes-and-rebel-histories',
            'price' => 24.95,
            'description' => 'A people\'s history of strikes, revolts, and forgotten martyrs of the U.S. labor and protest movements.',
        ],
        [
            'name' => 'A Resistance History of the United States',
            'slug' => 'book-a-resistance-history-of-the-united-states',
            'price' => 19.95,
            'description' => 'A history of the United States told through its movements of resistance, from the Underground Railroad onward.',
        ],
        [
            'name' => 'Radical Justice: Building the World We Need',
            'slug' => 'book-radical-justice-building-the-world-we-need',
            'price' => 19.95,
            'description' => 'Human-rights lawyer Nani Jansen Reventlow on using the law to help build a more just world.',
        ],
        [
            'name' => 'Chain of Ideas: The Origins of Our Authoritarian Age',
            'slug' => 'book-chain-of-ideas-the-origins-of-our-authoritarian-age',
            'price' => 35.00,
            'description' => 'A history tracing the intellectual origins of the present authoritarian age.',
        ],
        [
            'name' => 'The Young Lords Speak: Building Revolution on the Streets of Chicago',
            'slug' => 'book-the-young-lords-speak-building-revolution-on-the-stree',
            'price' => 24.95,
            'description' => 'Documents and voices of the Young Lords, the Puerto Rican revolutionary organization rooted in 1960s Chicago.',
        ],
        [
            'name' => 'We Refuse: A Forceful History of Black Resistance',
            'slug' => 'book-we-refuse-a-forceful-history-of-black-resistance',
            'price' => 18.99,
            'description' => 'Kellie Carter Jackson\'s history of Black resistance in America and the many forms it has taken.',
        ],
        [
            'name' => 'A Protest History of the United States',
            'slug' => 'book-a-protest-history-of-the-united-states',
            'price' => 21.95,
            'description' => 'A history of the United States told through its movements of protest.',
        ],
        [
            'name' => 'Policing the Progressive City: Portland, Oregon, from Settlement to Uprising',
            'slug' => 'book-policing-the-progressive-city-portland-oregon-from-set',
            'price' => 24.00,
            'description' => 'A history of policing, and resistance to it, in Portland, Oregon, from settlement to the 2020 uprisings.',
        ],
        [
            'name' => 'The Memoirs of Robert and Mabel Williams: African American Freedom, Armed Resistance, and International Solidarity',
            'slug' => 'book-the-memoirs-of-robert-and-mabel-williams-african-ameri',
            'price' => 34.95,
            'description' => 'The memoirs of Robert F. and Mabel Williams, advocates of armed Black self-defense who lived in exile in Cuba and China.',
        ],
        [
            'name' => 'If We Burn: The Mass Protest Decade and the Missing Revolution',
            'slug' => 'book-if-we-burn-the-mass-protest-decade-and-the-missing-rev',
            'price' => 21.99,
            'description' => 'Vincent Bevins on the mass-protest decade of the 2010s and why so many uprisings fell short of lasting change.',
        ],
        [
            'name' => 'Victor Serge: Unruly Revolutionary',
            'slug' => 'book-victor-serge-unruly-revolutionary',
            'price' => 26.95,
            'description' => 'A biography of Victor Serge, the revolutionary writer who survived both Tsarist and Stalinist prisons.',
        ],
        [
            'name' => 'Who Do We Trust?: Power, Solidarity and Anti-Authoritarianism',
            'slug' => 'book-who-do-we-trust-power-solidarity-and-anti-authoritaria',
            'price' => 26.95,
            'description' => 'Essays on power, solidarity, and anti-authoritarian organizing.',
        ],
        [
            'name' => 'The Famous Speeches of the Eight Chicago Anarchists',
            'slug' => 'book-the-famous-speeches-of-the-eight-chicago-anarchists',
            'price' => 21.00,
            'description' => 'The courtroom speeches of the eight anarchists tried after the 1886 Haymarket affair.',
        ],
        [
            'name' => 'Police Against the Movement: The Sabotage of the Civil Rights Struggle and the Activists Who Fought Back',
            'slug' => 'book-police-against-the-movement-the-sabotage-of-the-civil',
            'price' => 27.95,
            'description' => 'A history of police sabotage of the civil rights struggle, and the activists who fought back.',
        ],
        [
            'name' => 'On a Move: Philadelphia\'s Notorious Bombing and a Native Son\'s Lifelong Battle for Justice',
            'slug' => 'book-on-a-move-philadelphia-s-notorious-bombing-and-a-nativ',
            'price' => 18.99,
            'description' => 'The story of the 1985 MOVE bombing in Philadelphia and a native son\'s lifelong battle for justice.',
        ],
        [
            'name' => 'Ending Isolation: The Case Against Solitary Confinement',
            'slug' => 'book-ending-isolation-the-case-against-solitary-confinement',
            'price' => 22.95,
            'description' => 'The case against solitary confinement and the movement working to end it.',
        ],
        [
            'name' => 'My Glorious Defeats: Hacktivist, Narcissist, Anonymous: A Memoir',
            'slug' => 'book-my-glorious-defeats-hacktivist-narcissist-anonymous-a',
            'price' => 20.00,
            'description' => 'Barrett Brown\'s memoir of Anonymous, hacktivism, and his years in federal prison.',
        ],
        [
            'name' => 'Media Bias, Perspective & State Repression: The Black Panther Party',
            'slug' => 'book-media-bias-perspective-state-repression-the-black-pant',
            'price' => 29.99,
            'description' => 'A study of media bias and state repression targeting the Black Panther Party.',
        ],
        [
            'name' => 'Are Prisons Obsolete?',
            'slug' => 'book-are-prisons-obsolete',
            'price' => 15.95,
            'description' => 'Angela Davis\'s foundational case for prison abolition.',
        ],
        [
            'name' => 'Challenging the Myths of US History: Seven Short Essays on the Past and Present',
            'slug' => 'book-challenging-the-myths-of-us-history-seven-short-essays',
            'price' => 24.95,
            'description' => 'Seven short essays challenging common myths about the American past and present.',
        ],
        [
            'name' => 'Blood In My Eye',
            'slug' => 'book-blood-in-my-eye',
            'price' => 22.95,
            'description' => 'George Jackson\'s final book, completed shortly before his 1971 killing at San Quentin.',
        ],
        [
            'name' => 'Writing on the Wall: Selected Prison Writings of Mumia Abu-Jamal',
            'slug' => 'book-writing-on-the-wall-selected-prison-writings-of-mumia',
            'price' => 17.95,
            'description' => 'Selected prison writings of journalist and political prisoner Mumia Abu-Jamal.',
        ],
        [
            'name' => 'Lucasville: The Untold Story of a Prison Uprising',
            'slug' => 'book-lucasville-the-untold-story-of-a-prison-uprising',
            'price' => 20.00,
            'description' => 'Staughton Lynd\'s account of the 1993 Lucasville prison uprising in Ohio.',
        ],
        [
            'name' => 'Resistance Behind Bars: The Struggles of Incarcerated Women, 2nd Ed.',
            'slug' => 'book-resistance-behind-bars-the-struggles-of-incarcerated-w',
            'price' => 20.00,
            'description' => 'Victoria Law\'s history of organizing and resistance by incarcerated women.',
        ],
        [
            'name' => 'Can\'t Jail the Spirit: Political Prisoners in the U.S.',
            'slug' => 'book-can-t-jail-the-spirit-political-prisoners-in-the-u-s',
            'price' => 20.00,
            'description' => 'A reference profiling political prisoners and prisoners of war held in the United States.',
        ],
        [
            'name' => 'They Never Crushed His Spirit',
            'slug' => 'book-they-never-crushed-his-spirit',
            'price' => 9.00,
            'description' => 'The story of a political prisoner whose spirit endured decades of imprisonment.',
        ],
        [
            'name' => 'Muzzling A Movement: The Effects of Anti-Terrorism Law, Money, and Politics on Animal Activism',
            'slug' => 'book-muzzling-a-movement-the-effects-of-anti-terrorism-law',
            'price' => 20.00,
            'description' => 'How anti-terrorism law, money, and politics have been used to suppress animal-rights activism.',
        ],
        [
            'name' => 'Jailhouse Lawyers: Prisoners Defending Prisoners V. The U.S.A.',
            'slug' => 'book-jailhouse-lawyers-prisoners-defending-prisoners-v-the',
            'price' => 16.95,
            'description' => 'Mumia Abu-Jamal on the prisoners who teach themselves the law to defend themselves and one another.',
        ],
        [
            'name' => 'Death Blossoms',
            'slug' => 'book-death-blossoms',
            'price' => 16.95,
            'description' => 'Mumia Abu-Jamal\'s reflections on faith, spirit, and life on death row.',
        ],
        [
            'name' => 'We Want Freedom: A Life in the Black Panther Party',
            'slug' => 'book-we-want-freedom-a-life-in-the-black-panther-party',
            'price' => 20.00,
            'description' => 'Mumia Abu-Jamal\'s history of, and memoir within, the Black Panther Party.',
        ],
        [
            'name' => 'The Classroom and the Cell: Conversations on Black Life in America',
            'slug' => 'book-the-classroom-and-the-cell-conversations-on-black-life',
            'price' => 14.95,
            'description' => 'Conversations on Black life in America between Mumia Abu-Jamal and Marc Lamont Hill.',
        ],
        [
            'name' => 'The Black Panther Party',
            'slug' => 'book-the-black-panther-party',
            'price' => 21.95,
            'description' => 'A history of the Black Panther Party.',
        ],
        [
            'name' => 'Revolutionary Suicide',
            'slug' => 'book-revolutionary-suicide',
            'price' => 19.00,
            'description' => 'The autobiography of Huey P. Newton, co-founder of the Black Panther Party.',
        ],
        [
            'name' => 'The Failure of Nonviolence, 3rd Edition',
            'slug' => 'book-the-failure-of-nonviolence-3rd-edition',
            'price' => 17.00,
            'description' => 'Peter Gelderloos\'s critique of nonviolence as a strategy for social change.',
        ],
        [
            'name' => 'Acts of Rebellion: The Ward Churchill Reader',
            'slug' => 'book-acts-of-rebellion-the-ward-churchill-reader',
            'price' => 47.95,
            'description' => 'A reader of Ward Churchill\'s writings on Indigenous resistance, COINTELPRO, and U.S. repression.',
        ],
        [
            'name' => 'The Monkey Wrench Gang',
            'slug' => 'book-the-monkey-wrench-gang',
            'price' => 18.99,
            'description' => 'Edward Abbey\'s classic novel of eco-sabotage in the American Southwest.',
        ],
        [
            'name' => 'No Surrender: Writings From an Anti-Imperialist Political Prisoner',
            'slug' => 'book-no-surrender-writings-from-an-anti-imperialist-politic',
            'price' => 15.00,
            'description' => 'Prison writings from an anti-imperialist political prisoner.',
        ],
        [
            'name' => 'Stitching Freedom: A True Story of Injustice, Defiance, and Hope in Angola Prison',
            'slug' => 'book-stitching-freedom-a-true-story-of-injustice-defiance-a',
            'price' => 29.00,
            'description' => 'A true story of injustice, defiance, and hope inside Louisiana\'s Angola prison.',
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
