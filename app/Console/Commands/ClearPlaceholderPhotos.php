<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Removes the "NO IMAGE AVAILABLE" placeholder graphic that was saved as a real
 * prisoner photo. It does two things, matching the placeholder by file hash (not
 * a name list) so it is exact and safe to re-run (idempotent):
 *
 *   1. NULLs the `photo` column on any prisoner whose stored image is the
 *      placeholder, so the site no longer treats them as having a portrait.
 *   2. DELETES the placeholder image files from the public disk (the per-record
 *      copies under storage/app/public/prisoners/). The file sweep runs over the
 *      whole prisoners/ directory, so it also cleans up orphaned placeholder
 *      files whose column was already nulled by an earlier run.
 *
 * It only ever touches files that ARE the placeholder; genuine portraits are
 * left untouched. Use --dry-run to preview.
 */
final class ClearPlaceholderPhotos extends Command
{
    protected $signature = 'prisoners:clear-placeholder-photos {--dry-run : List what would change without modifying anything}';

    protected $description = 'Null and delete the "NO IMAGE AVAILABLE" placeholder graphic stored as a real prisoner photo';

    /** MD5(s) of known placeholder images (the "NO IMAGE AVAILABLE" graphic). */
    private const PLACEHOLDER_MD5 = [
        '56f7ee32d16ac711c6768265e1538357',
    ];

    /** Byte size(s) of those placeholders — a cheap pre-filter before hashing. */
    private const PLACEHOLDER_SIZE = [
        41671,
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');

        $columnsCleared = 0;
        $missingFiles = 0;

        // Pass 1 — null the photo column on records pointing at the placeholder.
        $query = Prisoner::withUnderReview()
            ->whereNotNull('photo')
            ->where('photo', '!=', '');

        foreach ($query->cursor() as $prisoner) {
            $path = $this->normalizePath($prisoner->photo);

            if (! $disk->exists($path)) {
                $missingFiles++;

                continue;
            }
            if (! $this->isPlaceholder($disk, $path)) {
                continue;
            }

            if ($dryRun) {
                $this->line("  would clear column: {$prisoner->name}  ({$prisoner->photo})");
            } else {
                $prisoner->photo = null;
                $prisoner->save();
                $this->info("  cleared column: {$prisoner->name}");
            }
            $columnsCleared++;
        }

        // Pass 2 — delete every placeholder file under prisoners/ (covers the
        // copies just unlinked above plus any orphaned by a prior null-only run).
        $filesDeleted = 0;

        foreach ($disk->files('prisoners') as $file) {
            if (! $this->isPlaceholder($disk, $file)) {
                continue;
            }

            if ($dryRun) {
                $this->line("  would delete file: {$file}");
            } else {
                $disk->delete($file);
            }
            $filesDeleted++;
        }

        $this->info("\nDone".($dryRun ? ' (dry run)' : '')
            .". Columns cleared: {$columnsCleared} | placeholder files deleted: {$filesDeleted}"
            ." (column paths already missing on disk: {$missingFiles})");

        return self::SUCCESS;
    }

    private function normalizePath($photo): string
    {
        return ltrim(preg_replace('#^.*/storage/#', '', (string) $photo), '/');
    }

    private function isPlaceholder($disk, string $path): bool
    {
        try {
            if (! in_array($disk->size($path), self::PLACEHOLDER_SIZE, true)) {
                return false;
            }

            return in_array(md5($disk->get($path)), self::PLACEHOLDER_MD5, true);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
