<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Regenerates the store product images as recognizable vector product mockups —
 * an actual t-shirt / hoodie / crewneck / long-sleeve / cap / beanie / tote /
 * mug / bottle / bandana / sticker / pin / bundle silhouette with the item's
 * slogan rendered on it, on a light "studio" background — replacing the older
 * flat text cards so the store reads like the peer advocacy shops. Overwrites
 * each product's existing products/{slug}.svg on the public disk (the DB image
 * path is unchanged). Idempotent; safe to re-run.
 */
class GenerateStoreMockups extends Command {
    protected $signature = 'store:generate-mockups {name? : Only (re)generate the mockup for this exact product name}';
    protected $description = 'Regenerate store product images as vector product mockups (shirts, totes, mugs, pins, etc.)';

    /** @var array<string, array{shape:string, design:array<int,string>}> */
    private array $map = [
        // Apparel
        'NPPC Logo T-Shirt'                    => ['shape' => 'tshirt',    'design' => ['NPPC']],
        'Free Them All T-Shirt'                => ['shape' => 'tshirt',    'design' => ['FREE', 'THEM ALL']],
        'Free Leonard Peltier T-Shirt'         => ['shape' => 'tshirt',    'design' => ['FREE', 'LEONARD', 'PELTIER']],
        'Free Mumia Abu-Jamal T-Shirt'         => ['shape' => 'tshirt',    'design' => ['FREE', 'MUMIA']],
        'Letter Writing Saves Lives T-Shirt'   => ['shape' => 'tshirt',    'design' => ['LETTER', 'WRITING', 'SAVES LIVES']],
        'Abolition T-Shirt'                    => ['shape' => 'tshirt',    'design' => ['ABOLITION']],
        'NPPC Heavyweight Hoodie'              => ['shape' => 'hoodie',    'design' => ['NPPC']],
        'Free Them All Hoodie'                 => ['shape' => 'hoodie',    'design' => ['FREE', 'THEM ALL']],
        'NPPC Embroidered Dad Cap'             => ['shape' => 'cap',       'design' => ['NPPC']],
        'Free Them All Long-Sleeve Tee'        => ['shape' => 'longsleeve','design' => ['FREE', 'THEM ALL']],
        'NPPC Crewneck Sweatshirt'             => ['shape' => 'crewneck',  'design' => ['NPPC']],
        // Accessories
        'NPPC Canvas Tote Bag'                 => ['shape' => 'tote',      'design' => ['NPPC']],
        'Free Them All Tote Bag'               => ['shape' => 'tote',      'design' => ['FREE', 'THEM ALL']],
        'Letter Writing Saves Lives Mug'       => ['shape' => 'mug',       'design' => ['LETTER', 'WRITING', 'SAVES LIVES']],
        'Abolition Enamel Mug'                 => ['shape' => 'mug',       'design' => ['ABOLITION']],
        'NPPC Knit Beanie'                     => ['shape' => 'beanie',    'design' => ['NPPC']],
        'NPPC Insulated Water Bottle'          => ['shape' => 'bottle',    'design' => ['NPPC']],
        'Free Them All Bandana'                => ['shape' => 'bandana',   'design' => ['FREE', 'THEM ALL']],
        // Stickers
        'NPPC Sticker Pack (10)'               => ['shape' => 'sticker',   'design' => ['NPPC']],
        'Free Them All Sticker'                => ['shape' => 'sticker',   'design' => ['FREE', 'THEM ALL']],
        'Black Liberation Slogan Sticker Set (5)' => ['shape' => 'sticker','design' => ['BLACK', 'LIBERATION']],
        'Free Them All Bumper Sticker'         => ['shape' => 'bumpersticker', 'design' => ['FREE THEM ALL']],
        // Magnets
        'Free Them All Car Magnet'             => ['shape' => 'carmagnet',     'design' => ['FREE', 'THEM ALL']],
        'NPPC Fridge Magnet'                   => ['shape' => 'fridgemagnet',  'design' => ['NPPC']],
        // Pins
        'NPPC Enamel Pin'                      => ['shape' => 'pin',       'design' => ['NPPC']],
        'Free Mumia Enamel Pin'                => ['shape' => 'pin',       'design' => ['FREE', 'MUMIA']],
        'Free Leonard Peltier Enamel Pin'      => ['shape' => 'pin',       'design' => ['FREE', 'LEONARD']],
        'NPPC Button'                          => ['shape' => 'button',    'design' => ['NPPC']],
        // Bundle
        'NPPC Solidarity Bundle'               => ['shape' => 'bundle',    'design' => ['NPPC', 'SOLIDARITY']],
    ];

    public function handle(): int {
        $only = $this->argument('name');
        $count = 0;
        $missing = 0;
        $skipped = 0;
        foreach ($this->map as $name => $cfg) {
            if ($only !== null && $name !== $only) {
                continue;
            }
            $product = Product::where('name', $name)->first();
            if (! $product) {
                $this->warn("Not found: {$name}");
                $missing++;

                continue;
            }

            // Generated mockups always live at this one canonical path. Only
            // write when the product is still using that mockup (or has no
            // image yet). NEVER touch a photo an admin uploaded through the
            // panel — Filament stores those under a different filename, so any
            // other path means a real upload we must leave alone.
            $canonical = 'products/'.$product->slug.'.svg';
            $current = (string) $product->image;
            if ($current !== '' && $current !== $canonical) {
                $this->line("Keeping uploaded image for {$name} ({$current}) — not regenerating.");
                $skipped++;

                continue;
            }

            $inner = $this->shape($cfg['shape'], $cfg['design']);
            $svg = $this->wrap($product->category ?: 'NPPC', $inner);
            Storage::disk('public')->put($canonical, $svg);
            if ($current !== $canonical) {
                $product->image = $canonical;
                $product->save();
            }
            $this->info("Mockup: {$name}  [{$cfg['shape']}]");
            $count++;
        }
        $this->info("\nDone. Regenerated {$count} mockup(s), skipped {$skipped}".($missing ? ", {$missing} not found." : '.'));

        return self::SUCCESS;
    }

    private function shape(string $type, array $design): string {
        return match ($type) {
            'tshirt'     => $this->tshirt($design),
            'longsleeve' => $this->longsleeve($design),
            'hoodie'     => $this->hoodie($design),
            'crewneck'   => $this->crewneck($design),
            'cap'        => $this->cap($design),
            'beanie'     => $this->beanie($design),
            'tote'       => $this->tote($design),
            'mug'        => $this->mug($design),
            'bottle'     => $this->bottle($design),
            'bandana'    => $this->bandana($design),
            'sticker'    => $this->sticker($design),
            'bumpersticker' => $this->bumpersticker($design),
            'carmagnet'  => $this->carmagnet($design),
            'fridgemagnet' => $this->fridgemagnet($design),
            'pin'        => $this->pin($design),
            'button'     => $this->button($design),
            'bundle'     => $this->bundle($design),
            default      => $this->centeredText($design, 400, 500, 56, '#1f2a44'),
        };
    }

    // ── helpers ──

    private function fs(array $lines, int $maxW = 300, int $base = 58): int {
        $max = 1;
        foreach ($lines as $l) {
            $max = max($max, strlen($l));
        }
        $byWidth = (int) floor($maxW / (0.62 * $max));
        $fs = min($base, $byWidth);
        if (count($lines) >= 3) {
            $fs = min($fs, (int) round($base * 0.7));
        }

        return max(18, $fs);
    }

    private function centeredText(array $lines, int $cx, int $cy, int $fs, string $color): string {
        $n = count($lines);
        $lh = (int) round($fs * 1.14);
        $spans = '';
        foreach (array_values($lines) as $i => $line) {
            $y = (int) round($cy + ($i - ($n - 1) / 2) * $lh + $fs * 0.34);
            $spans .= '<tspan x="'.$cx.'" y="'.$y.'">'.htmlspecialchars($line, ENT_XML1).'</tspan>';
        }

        return '<text text-anchor="middle" fill="'.$color.'" font-family="Helvetica, Arial, sans-serif" font-size="'.$fs.'" font-weight="800" letter-spacing="1">'.$spans.'</text>';
    }

    private function shadow(int $cy = 812, int $rx = 170): string {
        return '<ellipse cx="400" cy="'.$cy.'" rx="'.$rx.'" ry="22" fill="rgba(0,0,0,0.10)"/>';
    }

    // ── shapes ──

    private function tshirt(array $d): string {
        $body = '<path d="M305 302 C342 272 362 262 400 262 C438 262 458 272 495 302 L598 352 L556 452 L520 432 L520 786 L280 786 L280 432 L244 452 L202 352 Z" fill="#23262e"/>';
        $collar = '<path d="M360 276 Q400 322 440 276" fill="none" stroke="#383d48" stroke-width="9" stroke-linecap="round"/>';

        return $this->shadow().$body.$collar.$this->centeredText($d, 400, 500, $this->fs($d, 300), '#ffffff');
    }

    private function longsleeve(array $d): string {
        $body = '<path d="M305 302 C342 272 362 262 400 262 C438 262 458 272 495 302 L590 346 L642 642 L584 660 L548 432 L520 452 L520 786 L280 786 L280 452 L252 432 L216 660 L158 642 L210 346 Z" fill="#23262e"/>';
        $collar = '<path d="M360 276 Q400 322 440 276" fill="none" stroke="#383d48" stroke-width="9" stroke-linecap="round"/>';

        return $this->shadow().$body.$collar.$this->centeredText($d, 400, 500, $this->fs($d, 280), '#ffffff');
    }

    private function hoodie(array $d): string {
        $hood = '<path d="M322 312 Q400 214 478 312 Q452 348 400 348 Q348 348 322 312 Z" fill="#1f232a"/>';
        $body = '<path d="M312 318 C346 296 360 300 400 300 C440 300 454 296 488 318 L598 360 L556 458 L520 438 L520 790 L280 790 L280 438 L244 458 L202 360 Z" fill="#262a32"/>';
        $pocket = '<path d="M322 612 L478 612 L468 692 L332 692 Z" fill="#1f232a"/>';
        $strings = '<line x1="384" y1="330" x2="384" y2="430" stroke="#cfd3da" stroke-width="7" stroke-linecap="round"/><line x1="416" y1="330" x2="416" y2="430" stroke="#cfd3da" stroke-width="7" stroke-linecap="round"/>';

        return $this->shadow().$hood.$body.$pocket.$strings.$this->centeredText($d, 400, 500, $this->fs($d, 280), '#ffffff');
    }

    private function crewneck(array $d): string {
        $body = '<path d="M305 312 C342 286 362 286 400 286 C438 286 458 286 495 312 L598 356 L556 456 L520 436 L520 770 Q520 790 500 790 L300 790 Q280 790 280 770 L280 436 L244 456 L202 356 Z" fill="#2a2e36"/>';
        $collar = '<path d="M352 300 Q400 338 448 300" fill="none" stroke="#3b4049" stroke-width="16" stroke-linecap="round"/>';
        $hem = '<rect x="280" y="762" width="240" height="22" fill="#222630"/>';

        return $this->shadow().$body.$collar.$hem.$this->centeredText($d, 400, 512, $this->fs($d, 290), '#ffffff');
    }

    private function cap(array $d): string {
        $dome = '<path d="M252 474 Q252 330 400 330 Q548 330 548 474 Z" fill="#23262e"/>';
        $brim = '<path d="M252 474 Q470 540 612 492 Q628 488 618 466 Q470 500 252 474 Z" fill="#1c1f26"/>';
        $btn = '<circle cx="400" cy="332" r="11" fill="#1c1f26"/>';

        return $this->shadow(560, 150).$brim.$dome.$btn.$this->centeredText($d, 400, 432, $this->fs($d, 210, 48), '#ffffff');
    }

    private function beanie(array $d): string {
        $dome = '<path d="M286 540 Q286 356 400 356 Q514 356 514 540 Z" fill="#262a32"/>';
        $cuff = '<rect x="276" y="520" width="248" height="78" rx="14" fill="#30353f"/>';
        $rib = '<g stroke="#262a32" stroke-width="4">'.implode('', array_map(fn ($x) => '<line x1="'.$x.'" y1="528" x2="'.$x.'" y2="590"/>', range(300, 500, 24))).'</g>';

        return $this->shadow(620, 140).$dome.$cuff.$rib.$this->centeredText($d, 400, 462, $this->fs($d, 200, 46), '#ffffff');
    }

    private function tote(array $d): string {
        $bag = '<rect x="272" y="362" width="256" height="392" rx="10" fill="#d8cdb2"/>';
        $seam = '<line x1="272" y1="404" x2="528" y2="404" stroke="#c4b896" stroke-width="3"/>';
        $handles = '<path d="M318 366 C318 256 364 256 364 366" fill="none" stroke="#cabe9d" stroke-width="15"/><path d="M436 366 C436 256 482 256 482 366" fill="none" stroke="#cabe9d" stroke-width="15"/>';

        return $this->shadow(764, 150).$handles.$bag.$seam.$this->centeredText($d, 400, 560, $this->fs($d, 220), '#1f2a44');
    }

    private function mug(array $d): string {
        $handle = '<path d="M512 432 C600 432 600 568 512 568" fill="none" stroke="#e9e8e2" stroke-width="30"/>';
        $body = '<rect x="298" y="392" width="220" height="248" rx="14" fill="#f8f8f4"/>';
        $rim = '<ellipse cx="408" cy="392" rx="110" ry="20" fill="#ffffff" stroke="#e2e1da" stroke-width="3"/>';

        return $this->shadow(660, 140).$handle.$body.$rim.$this->centeredText($d, 405, 520, $this->fs($d, 168, 46), '#1f2a44');
    }

    private function bottle(array $d): string {
        $body = '<rect x="338" y="346" width="128" height="404" rx="34" fill="url(#steel)"/>';
        $cap = '<rect x="360" y="300" width="84" height="50" rx="10" fill="#23262e"/>';
        $band = '<rect x="338" y="470" width="128" height="3" fill="rgba(0,0,0,0.12)"/>';

        return $this->shadow(760, 90).$cap.$body.$band.$this->centeredText($d, 402, 540, $this->fs($d, 104, 40), '#1f2a44');
    }

    private function bandana(array $d): string {
        $diamond = '<polygon points="400,300 620,520 400,740 180,520" fill="#93302f"/>';
        $border = '<polygon points="400,344 576,520 400,696 224,520" fill="none" stroke="#f3e9e0" stroke-width="4" stroke-dasharray="10 10"/>';
        $dots = '<g fill="#f3e9e0">'.implode('', array_map(
            fn ($p) => '<circle cx="'.$p[0].'" cy="'.$p[1].'" r="7"/>',
            [[400, 388], [400, 652], [268, 520], [532, 520]]
        )).'</g>';

        return $this->shadow(744, 170).$diamond.$border.$dots.$this->centeredText($d, 400, 520, $this->fs($d, 240, 50), '#ffffff');
    }

    private function sticker(array $d): string {
        $cut = '<rect x="252" y="372" width="296" height="256" rx="26" fill="#ffffff" stroke="#ffffff" stroke-width="20" transform="rotate(-5 400 500)"/>';
        $inner = '<rect x="270" y="390" width="260" height="220" rx="18" fill="#1f2a44" transform="rotate(-5 400 500)"/>';

        return $this->shadow(648, 150).$cut.$inner.$this->centeredText($d, 400, 500, $this->fs($d, 220), '#ffffff');
    }

    private function bumpersticker(array $d): string {
        // Wide rounded rectangle in bumper proportions, slight tilt like a sticker.
        $cut = '<rect x="132" y="430" width="536" height="150" rx="16" fill="#ffffff" stroke="#ffffff" stroke-width="18" transform="rotate(-4 400 505)"/>';
        $inner = '<rect x="150" y="448" width="500" height="114" rx="9" fill="#1f2a44" transform="rotate(-4 400 505)"/>';

        return $this->shadow(648, 210).$cut.$inner.$this->centeredText($d, 400, 505, $this->fs($d, 440, 50), '#ffffff');
    }

    private function carmagnet(array $d): string {
        // Classic oval ("euro-oval") car magnet: dark rim, light face.
        $rim = '<ellipse cx="400" cy="500" rx="200" ry="128" fill="#1f2a44"/>';
        $face = '<ellipse cx="400" cy="500" rx="178" ry="106" fill="#f5f2ec"/>';

        return $this->shadow(648, 200).$rim.$face.$this->centeredText($d, 400, 500, $this->fs($d, 270, 52), '#1f2a44');
    }

    private function fridgemagnet(array $d): string {
        // Small rounded-square magnet; offset backing edge suggests its depth.
        $back = '<rect x="298" y="404" width="212" height="212" rx="20" fill="#c7cdd3"/>';
        $face = '<rect x="290" y="392" width="212" height="212" rx="20" fill="#1f2a44"/>';

        return $this->shadow(648, 150).$back.$face.$this->centeredText($d, 396, 498, $this->fs($d, 168, 50), '#ffffff');
    }

    private function pin(array $d): string {
        $disc = '<circle cx="400" cy="500" r="160" fill="#1a2540" stroke="#c9a84a" stroke-width="14"/>';
        $sheen = '<path d="M300 430 A160 160 0 0 1 500 400" fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="18" stroke-linecap="round"/>';

        return $this->shadow(672, 150).$disc.$sheen.$this->centeredText($d, 400, 500, $this->fs($d, 230), '#f3e7c4');
    }

    private function button(array $d): string {
        // Round pin-back button: a two-tone metal rim crimped around a printed
        // face (distinct from the enamel pin's gold rim).
        $rim = '<circle cx="400" cy="500" r="172" fill="#d4d8de"/>';
        $bezel = '<circle cx="400" cy="500" r="162" fill="#aeb4bd"/>';
        $face = '<circle cx="400" cy="500" r="150" fill="#1f2a44"/>';
        $sheen = '<path d="M300 436 A156 156 0 0 1 512 404" fill="none" stroke="rgba(255,255,255,0.16)" stroke-width="16" stroke-linecap="round"/>';

        return $this->shadow(672, 150).$rim.$bezel.$face.$sheen.$this->centeredText($d, 400, 500, $this->fs($d, 220), '#ffffff');
    }

    private function bundle(array $d): string {
        $box = '<rect x="298" y="436" width="204" height="196" rx="8" fill="#cdab7c"/>';
        $lid = '<rect x="286" y="420" width="228" height="36" rx="8" fill="#bd9866"/>';
        $ribV = '<rect x="386" y="420" width="28" height="212" fill="#1f2a44"/>';
        $ribH = '<rect x="298" y="516" width="204" height="26" fill="#1f2a44"/>';
        $bow = '<path d="M400 420 Q360 392 352 420 Q360 440 400 426 Q440 440 448 420 Q440 392 400 420 Z" fill="#28385c"/>';

        return $this->shadow(648, 150).$box.$lid.$ribH.$ribV.$bow.$this->centeredText($d, 400, 716, $this->fs($d, 300, 50), '#1f2a44');
    }

    private function wrap(string $category, string $inner): string {
        $eyebrow = htmlspecialchars(strtoupper($category), ENT_XML1);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 1000" preserveAspectRatio="xMidYMid slice">
    <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#f5f2ec"/>
            <stop offset="100%" stop-color="#e4dfd4"/>
        </linearGradient>
        <linearGradient id="steel" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0%" stop-color="#929ba6"/>
            <stop offset="48%" stop-color="#c6ccd2"/>
            <stop offset="100%" stop-color="#7c8690"/>
        </linearGradient>
    </defs>
    <rect width="800" height="1000" fill="url(#bg)"/>
    <text x="400" y="120" text-anchor="middle" fill="#7a7264" font-family="Helvetica, Arial, sans-serif" font-size="22" letter-spacing="7" font-weight="700">{$eyebrow}</text>
    {$inner}
    <text x="400" y="930" text-anchor="middle" fill="#9a9285" font-family="Helvetica, Arial, sans-serif" font-size="20" letter-spacing="6" font-weight="700">NPPC STORE</text>
</svg>
SVG;
    }
}
