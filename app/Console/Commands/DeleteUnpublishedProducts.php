<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Permanently deletes every product that is not published, along with its own
 * image and gallery files. Order history is unaffected: order_items snapshot the
 * product name and price and hold only a nullable product_id (no foreign key).
 * A file is removed only if no other product still references it. Prompts before
 * deleting (pass --force to skip). Idempotent; safe to re-run.
 */
final class DeleteUnpublishedProducts extends Command
{
    protected $signature = 'store:delete-unpublished {--force : Skip the confirmation prompt}';

    protected $description = 'Permanently delete all unpublished products (and their image/gallery files)';

    public function handle(): int
    {
        $products = Product::where('published', false)->get();

        if ($products->isEmpty()) {
            $this->info('No unpublished products to delete.');

            return self::SUCCESS;
        }

        $this->warn($products->count().' unpublished product(s) will be permanently deleted:');
        foreach ($products as $p) {
            $this->line("  • {$p->name}");
        }
        if (! $this->option('force') && ! $this->confirm('Permanently delete these products?', false)) {
            $this->info('Aborted.');

            return self::SUCCESS;
        }

        $disk = Storage::disk('public');
        $deleted = 0;
        foreach ($products as $product) {
            $files = array_filter(array_merge(
                [(string) $product->image],
                array_map('strval', (array) ($product->gallery ?? []))
            ));
            foreach ($files as $file) {
                if (! $disk->exists($file) || $this->referencedElsewhere($file, $product->id)) {
                    continue;
                }
                $disk->delete($file);
            }
            $name = $product->name;
            $product->delete();
            $this->line("Deleted: {$name}");
            $deleted++;
        }

        $this->info("\nDone. Deleted {$deleted} unpublished product(s).");

        return self::SUCCESS;
    }

    /** True if any product other than $exceptId still references $file (image or gallery). */
    private function referencedElsewhere(string $file, string $exceptId): bool
    {
        return Product::where('id', '!=', $exceptId)
            ->where(function ($q) use ($file) {
                $q->where('image', $file)->orWhereJsonContains('gallery', $file);
            })
            ->exists();
    }
}
