<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Rounds out the store's catalog to match peer advocacy shops (Vera Institute,
 * Innocence Project, Working Families) by adding an Accessories line — tote
 * bags, enamel mugs, a beanie, a water bottle, a bandana — plus apparel
 * variety (a crewneck sweatshirt and a long-sleeve tee). Each product gets a
 * generated SVG mock image written to the public disk in the same style as the
 * existing store images, so it renders in the grid instead of a placeholder.
 * Dedupes by name; safe to re-run.
 */
class AddStoreAccessories extends Command {
    protected $signature = 'store:add-accessories';
    protected $description = 'Add an Accessories line (totes, mugs, beanie, bottle, bandana) plus apparel variety to the store';

    public function handle(): int {
        $products = [
            // ─── Accessories ───
            [
                'name' => 'NPPC Canvas Tote Bag', 'lines' => ['NPPC Canvas', 'Tote Bag'],
                'description' => "Heavyweight natural-canvas tote with the National Political Prisoner Coalition logo screen-printed on the side. Roomy enough for books, zine drops, and letter-writing supplies.",
                'price' => 30.00, 'category' => 'Accessories', 'sort_order' => 23,
            ],
            [
                'name' => 'Free Them All Tote Bag', 'lines' => ['Free Them All', 'Tote Bag'],
                'description' => "Natural-canvas tote with the rallying call \"FREE THEM ALL\" in bold type. 100% cotton, reinforced handles.",
                'price' => 30.00, 'category' => 'Accessories', 'sort_order' => 24,
            ],
            [
                'name' => 'Letter Writing Saves Lives Mug', 'lines' => ['Letter Writing', 'Saves Lives', 'Mug'],
                'description' => "11oz ceramic mug with \"Letter Writing Saves Lives\" — a reminder over morning coffee that mail to prisoners is one of the most effective acts of solidarity.",
                'price' => 18.00, 'category' => 'Accessories', 'sort_order' => 25,
            ],
            [
                'name' => 'Abolition Enamel Mug', 'lines' => ['Abolition', 'Enamel Mug'],
                'description' => "Classic white enamel camp mug with a black rim and \"ABOLITION\" in bold type. Holds 12oz; good for the kitchen, the campsite, or the picket line.",
                'price' => 18.00, 'category' => 'Accessories', 'sort_order' => 26,
            ],
            [
                'name' => 'NPPC Knit Beanie', 'lines' => ['NPPC Knit', 'Beanie'],
                'description' => "Ribbed cuffed knit beanie with an embroidered NPPC label. One size, warm for winter rallies and visiting-room waiting rooms alike.",
                'price' => 25.00, 'category' => 'Apparel', 'sort_order' => 27,
            ],
            [
                'name' => 'NPPC Insulated Water Bottle', 'lines' => ['NPPC Insulated', 'Water Bottle'],
                'description' => "20oz double-wall insulated stainless-steel bottle with the NPPC logo. Keeps drinks cold 24 hours, hot 12. Stickers sold separately.",
                'price' => 28.00, 'category' => 'Accessories', 'sort_order' => 28,
            ],
            [
                'name' => 'Free Them All Bandana', 'lines' => ['Free Them All', 'Bandana'],
                'description' => "22×22 inch cotton bandana printed with \"FREE THEM ALL\" and a repeating solidarity motif. Wear it, mask up, or fly it.",
                'price' => 15.00, 'category' => 'Apparel', 'sort_order' => 29,
            ],

            // ─── Apparel variety ───
            [
                'name' => 'Free Them All Long-Sleeve Tee', 'lines' => ['Free Them All', 'Long-Sleeve Tee'],
                'description' => "Black long-sleeve t-shirt with \"FREE THEM ALL\" on the chest and a solidarity slogan down one sleeve. 100% cotton, unisex S–3XL.",
                'price' => 35.00, 'category' => 'Apparel', 'sort_order' => 16,
            ],
            [
                'name' => 'NPPC Crewneck Sweatshirt', 'lines' => ['NPPC Crewneck', 'Sweatshirt'],
                'description' => "Midweight crewneck sweatshirt with the NPPC logo embroidered on the chest. Black with white embroidery, unisex S–3XL.",
                'price' => 45.00, 'category' => 'Apparel', 'sort_order' => 17,
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

                // Unique slug (Product's HasSlug reads $model->title, which it lacks,
                // so set it explicitly to avoid empty-slug collisions).
                $base = Str::slug($name);
                $slug = $base;
                $i = 2;
                while (Product::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i++;
                }

                // Generate the SVG mock image on the public disk, matching the
                // existing store-image style.
                $path = "products/{$slug}.svg";
                Storage::disk('public')->put($path, $this->svg($entry['category'], $entry['lines']));

                Product::create([
                    'name'        => $name,
                    'description' => $entry['description'],
                    'price'       => $entry['price'],
                    'category'    => $entry['category'],
                    'sort_order'  => $entry['sort_order'],
                    'published'   => true,
                    'featured'    => false,
                    'image'       => $path,
                    'slug'        => $slug,
                ]);

                $this->info("Added {$name}  (\${$entry['price']})  [{$entry['category']}]");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }

    /**
     * Build a product mock image SVG in the same style as the existing store
     * images: gradient panel, category eyebrow, the product name in big type,
     * and an "NPPC STORE" footer.
     *
     * @param  array<int, string>  $lines
     */
    private function svg(string $category, array $lines): string {
        $n = count($lines);
        $fs = $n >= 3 ? 46 : 56;
        $lh = $n >= 3 ? 64 : 72;

        $tspans = '';
        foreach (array_values($lines) as $idx => $line) {
            $y = (int) round(480 + ($idx - ($n - 1) / 2) * $lh);
            $tspans .= '<tspan x="400" y="'.$y.'">'.htmlspecialchars($line, ENT_XML1).'</tspan>';
        }

        $eyebrow = htmlspecialchars(strtoupper($category), ENT_XML1);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 1000" preserveAspectRatio="xMidYMid slice">
    <defs>
        <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#1a2540"/>
            <stop offset="100%" stop-color="#3b4d8c"/>
        </linearGradient>
    </defs>
    <rect width="800" height="1000" fill="url(#g)"/>
    <rect x="40" y="40" width="720" height="920" fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="2"/>
    <text x="400" y="120" text-anchor="middle" fill="rgba(255,255,255,0.6)" font-family="Helvetica, Arial, sans-serif" font-size="22" letter-spacing="6" font-weight="700">{$eyebrow}</text>
    <text text-anchor="middle" fill="#fff" font-family="Helvetica, Arial, sans-serif" font-size="{$fs}" font-weight="900">{$tspans}</text>
    <text x="400" y="930" text-anchor="middle" fill="rgba(255,255,255,0.55)" font-family="Helvetica, Arial, sans-serif" font-size="20" letter-spacing="6" font-weight="700">NPPC STORE</text>
</svg>
SVG;
    }
}
