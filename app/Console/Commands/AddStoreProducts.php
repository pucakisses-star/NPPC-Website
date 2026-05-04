<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddStoreProducts extends Command
{
    protected $signature = 'store:seed-products';
    protected $description = 'Seed the NPPC store with a default merch line: apparel, prints, books, stickers, pins, letter-writing kits, and bundles.';

    public function handle(): int
    {
        $products = [
            // ─── Apparel: T-shirts ───
            [
                'name' => 'NPPC Logo T-Shirt',
                'description' => "Black short-sleeve t-shirt with the National Political Prisoner Coalition logo printed on the chest. 100% cotton. Unisex sizing S–3XL. All proceeds support our prisoner-support and education work.",
                'price' => 25.00, 'category' => 'Apparel', 'featured' => true, 'sort_order' => 10,
            ],
            [
                'name' => 'Free Them All T-Shirt',
                'description' => "Black t-shirt with the rallying call \"FREE THEM ALL\" in bold white type. Unisex sizing S–3XL.",
                'price' => 25.00, 'category' => 'Apparel', 'featured' => true, 'sort_order' => 11,
            ],
            [
                'name' => 'Free Leonard Peltier T-Shirt',
                'description' => "Portrait of Leonard Peltier with the words FREE LEONARD PELTIER. Released to home confinement on February 18, 2025 after 49 years in federal prison.",
                'price' => 25.00, 'category' => 'Apparel', 'sort_order' => 12,
            ],
            [
                'name' => 'Free Mumia Abu-Jamal T-Shirt',
                'description' => "Portrait of Mumia Abu-Jamal — Black Panther, journalist, and political prisoner since 1981. Black t-shirt, white print.",
                'price' => 25.00, 'category' => 'Apparel', 'sort_order' => 13,
            ],
            [
                'name' => 'Letter Writing Saves Lives T-Shirt',
                'description' => "Cream t-shirt with the words \"Letter Writing Saves Lives\" — a reminder that mail to prisoners is one of the most effective acts of solidarity.",
                'price' => 25.00, 'category' => 'Apparel', 'sort_order' => 14,
            ],
            [
                'name' => 'Abolition T-Shirt',
                'description' => "Single-word \"ABOLITION\" type on a black t-shirt. Unisex sizing S–3XL.",
                'price' => 25.00, 'category' => 'Apparel', 'sort_order' => 15,
            ],

            // ─── Apparel: Hoodies & Hats ───
            [
                'name' => 'NPPC Heavyweight Hoodie',
                'description' => "Heavyweight pullover hoodie with the NPPC logo embroidered on the chest. Black with white embroidery. Sizes S–3XL.",
                'price' => 50.00, 'category' => 'Apparel', 'featured' => true, 'sort_order' => 20,
            ],
            [
                'name' => 'Free Them All Hoodie',
                'description' => "Heavyweight pullover hoodie with \"FREE THEM ALL\" printed across the back. Black, sizes S–3XL.",
                'price' => 50.00, 'category' => 'Apparel', 'sort_order' => 21,
            ],
            [
                'name' => 'NPPC Embroidered Dad Cap',
                'description' => "Unstructured low-profile cotton cap with NPPC embroidered on the front panel. Adjustable strap.",
                'price' => 25.00, 'category' => 'Apparel', 'sort_order' => 22,
            ],

            // ─── Books ───
            [
                'name' => 'Live From Death Row — Mumia Abu-Jamal',
                'description' => "Mumia Abu-Jamal's collection of essays written from death row, where he was held from 1982 until his sentence was commuted to life in 2011. A foundational text of contemporary U.S. abolitionist writing.",
                'price' => 20.00, 'category' => 'Books', 'sort_order' => 30,
            ],
            [
                'name' => 'Prison Writings: My Life Is My Sun Dance — Leonard Peltier',
                'description' => "Leonard Peltier's memoir, written from federal prison, weaving Indigenous spirituality with the political history of the American Indian Movement.",
                'price' => 25.00, 'category' => 'Books', 'sort_order' => 31,
            ],
            [
                'name' => 'Assata: An Autobiography — Assata Shakur',
                'description' => "Assata Shakur's autobiography of her life in the Black Panther Party, the Black Liberation Army, the 1973 New Jersey Turnpike shootout, her conviction, escape from prison, and exile in Cuba.",
                'price' => 20.00, 'category' => 'Books', 'sort_order' => 32,
            ],
            [
                'name' => 'Prison Memoirs of an Anarchist — Alexander Berkman',
                'description' => "Alexander Berkman's 1912 account of fourteen years in the Western Penitentiary of Pennsylvania after his attempted assassination of Henry Clay Frick during the Homestead Strike. One of the foundational texts of American prison literature.",
                'price' => 20.00, 'category' => 'Books', 'sort_order' => 33,
            ],
            [
                'name' => 'Soledad Brother — George Jackson',
                'description' => "George Jackson's prison letters, written from 1964 to 1970, foundational to the Black liberation prison movement.",
                'price' => 20.00, 'category' => 'Books', 'sort_order' => 34,
            ],

            // ─── Prints & Posters ───
            [
                'name' => 'Political Prisoners of the United States Poster (18×24)',
                'description' => "Large 18×24 poster listing the names of every U.S. political prisoner currently incarcerated, organized by era. Printed on heavy matte stock.",
                'price' => 20.00, 'category' => 'Prints', 'featured' => true, 'sort_order' => 40,
            ],
            [
                'name' => 'Mumia Abu-Jamal Portrait Print (11×17)',
                'description' => "11×17 inch portrait print of Mumia Abu-Jamal. Printed on archival matte paper.",
                'price' => 15.00, 'category' => 'Prints', 'sort_order' => 41,
            ],
            [
                'name' => 'Leonard Peltier Portrait Print (11×17)',
                'description' => "11×17 inch portrait print of Leonard Peltier with the words \"FREE LEONARD\". Archival matte paper.",
                'price' => 15.00, 'category' => 'Prints', 'sort_order' => 42,
            ],
            [
                'name' => 'Assata Shakur Portrait Print (11×17)',
                'description' => "11×17 inch portrait print of Assata Shakur. Archival matte paper.",
                'price' => 15.00, 'category' => 'Prints', 'sort_order' => 43,
            ],
            [
                'name' => 'MOVE 9 Commemorative Print (11×17)',
                'description' => "Commemorative print of the MOVE 9 — the nine MOVE members imprisoned after the 1978 Philadelphia police siege. All have now been released; two died in prison. 11×17 inch archival matte paper.",
                'price' => 20.00, 'category' => 'Prints', 'sort_order' => 44,
            ],
            [
                'name' => 'Plowshares Eight Historical Print (11×17)',
                'description' => "Commemorative print of the Plowshares Eight — Daniel Berrigan, Philip Berrigan, Carl Kabat, Anne Montgomery, Molly Rush, Elmer Maas, John Schuchardt, and Dean Hammer — at the September 9, 1980 GE King of Prussia action that launched the Plowshares movement.",
                'price' => 20.00, 'category' => 'Prints', 'sort_order' => 45,
            ],

            // ─── Stickers ───
            [
                'name' => 'NPPC Sticker Pack (10)',
                'description' => "Pack of ten 3-inch vinyl stickers with NPPC logo and slogans (\"Free Them All\", \"Letter Writing Saves Lives\", \"Abolition\", and others). Weatherproof for laptops, water bottles, and bumpers.",
                'price' => 10.00, 'category' => 'Stickers', 'sort_order' => 50,
            ],
            [
                'name' => 'Free Them All Sticker',
                'description' => "Single 3-inch vinyl sticker with the \"FREE THEM ALL\" slogan in bold type.",
                'price' => 3.00, 'category' => 'Stickers', 'sort_order' => 51,
            ],
            [
                'name' => 'Black Liberation Slogan Sticker Set (5)',
                'description' => "Five 3-inch vinyl stickers featuring slogans from the Black liberation tradition: \"Free Mumia\", \"Free Sundiata\", \"Free Leonard\", \"All Power to the People\", and \"Free Them All\".",
                'price' => 8.00, 'category' => 'Stickers', 'sort_order' => 52,
            ],

            // ─── Pins ───
            [
                'name' => 'NPPC Enamel Pin',
                'description' => "Hard enamel lapel pin with the NPPC logo. 1.25 inches.",
                'price' => 10.00, 'category' => 'Pins', 'sort_order' => 60,
            ],
            [
                'name' => 'Free Mumia Enamel Pin',
                'description' => "Hard enamel pin with the \"FREE MUMIA\" slogan. 1 inch.",
                'price' => 8.00, 'category' => 'Pins', 'sort_order' => 61,
            ],
            [
                'name' => 'Free Leonard Peltier Enamel Pin',
                'description' => "Hard enamel pin with the \"FREE LEONARD\" slogan. 1 inch.",
                'price' => 8.00, 'category' => 'Pins', 'sort_order' => 62,
            ],

            // ─── Letter-Writing Kits ───
            [
                'name' => 'Political Prisoner Letter-Writing Kit',
                'description' => "Everything you need to write to a political prisoner: 20 sheets of letter paper, 20 envelopes, 10 stamps, a current prisoner-address list, and a one-page guide to navigating prison mail rules. The single most effective form of solidarity.",
                'price' => 15.00, 'category' => 'Letter Writing', 'featured' => true, 'sort_order' => 70,
            ],
            [
                'name' => 'Holiday Card Pack for Political Prisoners',
                'description' => "Pack of 10 holiday cards designed to comply with prison mail rules (no glitter, no foil, no cardstock thicker than 1/16\"), with envelopes and a current address list of incarcerated political prisoners. Send during the lonely December weeks.",
                'price' => 12.00, 'category' => 'Letter Writing', 'sort_order' => 71,
            ],

            // ─── Bundles ───
            [
                'name' => 'NPPC Solidarity Bundle',
                'description' => "Bundle: NPPC logo t-shirt + 10-pack sticker set + NPPC enamel pin. Save \$5 vs. ordering separately.",
                'price' => 40.00, 'category' => 'Bundles', 'featured' => true, 'sort_order' => 80,
            ],
            [
                'name' => 'Prisoner Memoir Reading Bundle',
                'description' => "Three foundational political-prisoner memoirs: Live From Death Row (Mumia Abu-Jamal), Prison Writings (Leonard Peltier), and Assata: An Autobiography (Assata Shakur). Save \$10 vs. ordering separately.",
                'price' => 55.00, 'category' => 'Bundles', 'sort_order' => 81,
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($products as $entry) {
            DB::transaction(function () use ($entry, &$created, &$skipped) {
                $name = $entry['name'];
                if (Product::where('name', $name)->exists()) {
                    $this->warn("Skipping {$name} — already exists.");
                    $skipped++;
                    return;
                }

                // Defaults
                $entry['published'] = true;
                $entry['featured'] = $entry['featured'] ?? false;

                // Generate slug explicitly (HasSlug trait reads $model->title which Product
                // doesn't have, so we set it ourselves to avoid empty-slug unique conflicts)
                $base = Str::slug($entry['name']);
                $slug = $base;
                $i = 2;
                while (Product::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $entry['slug'] = $slug;

                $product = Product::create($entry);
                $this->info("Added {$product->name}  (\${$product->price})");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
