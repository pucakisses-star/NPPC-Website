<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * One-time cleanup: deletes generated placeholder product images (the SVG
 * mockups) and clears those products' image, leaving every product without a
 * real photo imageless. Detection is by file CONTENT (an SVG document), so it
 * also catches any real photo the old generator overwrote with a mockup, while
 * never clearing a genuine uploaded photo (binary JPEG/PNG/WebP). A dangling
 * reference to a missing file is cleared too. Idempotent; safe to re-run.
 */
final class RemoveStoreMockups extends Command
{
    protected $signature = 'store:remove-mockups {--force : Skip the confirmation prompt}';

    protected $description = "Delete generated placeholder (SVG) product images and clear those products' image";

    public function handle(): int
    {
        $disk = Storage::disk('public');

        $placeholders = [];
        $missing = [];
        foreach (Product::all() as $product) {
            $img = (string) $product->image;
            if ($img === '') {
                continue;
            }
            if (! $disk->exists($img)) {
                $missing[] = $product;

                continue;
            }
            $head = ltrim(substr((string) $disk->get($img), 0, 256));
            $isSvg = stripos($head, '<svg') !== false
                || (str_starts_with($head, '<?xml') && stripos($head, 'svg') !== false);
            if ($isSvg) {
                $placeholders[] = $product;
            }
        }

        if (count($placeholders) + count($missing) === 0) {
            $this->info('No placeholder images found — nothing to clear.');

            return self::SUCCESS;
        }

        $this->warn(count($placeholders).' placeholder image(s) and '.count($missing).' missing-file reference(s) will be cleared. Real uploaded photos are kept.');
        if (! $this->option('force') && ! $this->confirm('Proceed?', true)) {
            $this->info('Aborted.');

            return self::SUCCESS;
        }

        $cleared = 0;
        foreach ($placeholders as $product) {
            $disk->delete($product->image);
            $product->image = null;
            $product->save();
            $this->line("Cleared placeholder: {$product->name}");
            $cleared++;
        }
        foreach ($missing as $product) {
            $product->image = null;
            $product->save();
            $this->line("Cleared missing reference: {$product->name}");
            $cleared++;
        }

        $this->info("\nDone. Cleared {$cleared} image(s). Products without an image can now have a real photo uploaded in the admin.");

        return self::SUCCESS;
    }
}
