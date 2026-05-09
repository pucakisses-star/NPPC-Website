<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * For every published Product whose `image` is null, pick a cover for
 * it. Books are looked up against the Open Library search API and
 * their real cover JPEG is downloaded. Anything else (apparel,
 * posters/prints, accessories) gets a generated SVG placeholder with
 * the product title set on a category-colored gradient — those are
 * good enough for the store grid and can be replaced from the admin
 * panel later.
 *
 * Idempotent — only fills empty images.
 */
class FillProductImages extends Command
{
    protected $signature   = 'products:fill-images {--dry-run : Print plan without writing files or updating products} {--force : Overwrite even when image is already set}';
    protected $description = 'Fill missing product images: real Open Library book covers where applicable, generated SVG placeholders otherwise.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force  = (bool) $this->option('force');

        $disk = Storage::disk('public');
        if (! $disk->exists('products')) $disk->makeDirectory('products');

        $query = Product::query();
        if (! $force) $query->where(function ($q) {
            $q->whereNull('image')->orWhere('image', '');
        });

        $products = $query->orderBy('sort_order')->orderBy('name')->get();
        $this->info("Considering {$products->count()} products.");

        $bookHits = 0; $svgs = 0; $skipped = 0;

        foreach ($products as $p) {
            $kind = $this->classify($p);

            if ($kind === 'book') {
                $cover = $this->fetchBookCover($p);
                if ($cover) {
                    $path = "products/{$p->slug}.jpg";
                    if (! $dryRun) {
                        $disk->put($path, $cover);
                        $p->image = $path;
                        $p->save();
                    }
                    $this->info("  [book cover] {$p->name} -> {$path}");
                    $bookHits++;
                    continue;
                }
                $this->line("  [no Open Library match] {$p->name} — falling through to SVG placeholder");
            }

            $svg = $this->buildPlaceholderSvg($p, $kind);
            $path = "products/{$p->slug}.svg";
            if (! $dryRun) {
                $disk->put($path, $svg);
                $p->image = $path;
                $p->save();
            }
            $this->info("  [svg placeholder] {$p->name} -> {$path}  ({$kind})");
            $svgs++;
        }

        $this->info("\nDone. book covers: {$bookHits}, svg placeholders: {$svgs}" . ($dryRun ? ' (dry-run)' : ''));
        return self::SUCCESS;
    }

    private function classify(Product $p): string
    {
        $name = strtolower($p->name);
        $cat  = strtolower((string) $p->category);

        if ($cat === 'books' || str_contains($name, '—') && preg_match('/—\s*\w/', $name)) {
            // Book titles are formatted "Title — Author"
            return 'book';
        }
        if ($cat === 'apparel' || preg_match('/t-shirt|hoodie|cap|tee\b|sweatshirt|tote|pin\b/i', $name)) return 'apparel';
        if ($cat === 'prints' || preg_match('/poster|print\b/i', $name)) return 'print';
        return 'other';
    }

    /**
     * Search Open Library for the book by title (and author if the
     * title contains an em-dash splitting them) and return the JPEG
     * bytes of the largest available cover, or null if no match.
     */
    private function fetchBookCover(Product $p): ?string
    {
        $title  = $p->name;
        $author = null;
        if (str_contains($title, '—')) {
            [$title, $author] = array_map('trim', explode('—', $title, 2));
        }

        try {
            $params = ['title' => $title, 'limit' => 5];
            if ($author) $params['author'] = $author;
            $resp = Http::timeout(15)->get('https://openlibrary.org/search.json', $params);
            if (! $resp->successful()) return null;

            $docs = $resp->json('docs') ?? [];
            $coverId = null;
            foreach ($docs as $doc) {
                if (! empty($doc['cover_i'])) { $coverId = $doc['cover_i']; break; }
            }
            if (! $coverId) return null;

            $img = Http::timeout(20)->get("https://covers.openlibrary.org/b/id/{$coverId}-L.jpg");
            if (! $img->successful() || strlen($img->body()) < 1024) return null;
            return $img->body();
        } catch (\Throwable $e) {
            $this->warn("  Open Library error for {$p->name}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Generate a 800x1000 SVG placeholder with the product name over
     * a category-colored gradient and a hint of poster-like
     * typography.
     */
    private function buildPlaceholderSvg(Product $p, string $kind): string
    {
        $palettes = [
            'book'    => ['#3b1d1d', '#7a2c2c'],
            'apparel' => ['#1a2540', '#3b4d8c'],
            'print'   => ['#2d1740', '#6b3aa0'],
            'other'   => ['#11263a', '#2a4a6a'],
        ];
        [$c1, $c2] = $palettes[$kind] ?? $palettes['other'];

        $title = htmlspecialchars($p->name, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $kindLabel = htmlspecialchars(strtoupper($kind === 'other' ? ($p->category ?? 'NPPC') : $kind), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        // Wrap the title across multiple <tspan> lines (~16 chars each).
        $words = preg_split('/\s+/', $p->name);
        $lines = [];
        $cur = '';
        foreach ($words as $w) {
            if (mb_strlen(trim($cur . ' ' . $w)) > 16 && $cur !== '') {
                $lines[] = trim($cur);
                $cur = $w;
            } else {
                $cur = trim($cur . ' ' . $w);
            }
        }
        if ($cur !== '') $lines[] = $cur;

        $startY = 480 - (count($lines) - 1) * 36;
        $tspans = '';
        foreach ($lines as $i => $ln) {
            $y = $startY + $i * 72;
            $safe = htmlspecialchars($ln, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $tspans .= "<tspan x=\"400\" y=\"{$y}\">{$safe}</tspan>";
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 1000" preserveAspectRatio="xMidYMid slice">
    <defs>
        <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="{$c1}"/>
            <stop offset="100%" stop-color="{$c2}"/>
        </linearGradient>
    </defs>
    <rect width="800" height="1000" fill="url(#g)"/>
    <rect x="40" y="40" width="720" height="920" fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="2"/>
    <text x="400" y="120" text-anchor="middle" fill="rgba(255,255,255,0.6)" font-family="Helvetica, Arial, sans-serif" font-size="22" letter-spacing="6" font-weight="700">{$kindLabel}</text>
    <text text-anchor="middle" fill="#fff" font-family="Helvetica, Arial, sans-serif" font-size="56" font-weight="900">{$tspans}</text>
    <text x="400" y="930" text-anchor="middle" fill="rgba(255,255,255,0.55)" font-family="Helvetica, Arial, sans-serif" font-size="20" letter-spacing="6" font-weight="700">NPPC STORE</text>
</svg>
SVG;
    }
}
