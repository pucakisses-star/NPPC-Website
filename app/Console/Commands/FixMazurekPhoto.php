<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Repairs one fuzzy-name-match collision: a photo of Cop City arson defendant
 * John "Jack" Mazurek (uploaded as prisoners/john-mazurek.jpg) was copied by the
 * photo matcher onto 22 other records that merely contain "Jack"/"Jackson" in
 * their name (Gullah Jack, George Jackson, the IWW "Jack"s, Plowshares "Jackie"s,
 * etc.). This keeps the photo on John Mazurek (the original, bare-slug upload) and
 * NULLs the column + DELETES the duplicated file from every other record.
 *
 * Matches the image by hash (md5 be2fdf2b…, 52,738 bytes), so it is exact and
 * idempotent. As a safety check it refuses to run unless the keeper file exists
 * and is that image, so it can never strip every copy. --dry-run previews.
 */
final class FixMazurekPhoto extends Command
{
    protected $signature = 'prisoners:fix-mazurek-photo {--dry-run : List what would change without modifying anything}';

    protected $description = 'Keep John "Jack" Mazurek\'s photo on his record and clear the 22 fuzzy-matched copies';

    private const IMAGE_MD5 = 'be2fdf2b28eddd47d85d22d43a3f1bea';

    private const IMAGE_SIZE = 52738;

    private const KEEP_PATH = 'prisoners/john-mazurek.jpg';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');

        // Safety: only proceed if the keeper file exists and is the target image,
        // so we never delete every copy and leave the right record blank.
        if (! $disk->exists(self::KEEP_PATH) || ! $this->isTarget($disk, self::KEEP_PATH)) {
            $this->error('Keeper file '.self::KEEP_PATH.' is missing or not the expected image — aborting to avoid removing every copy.');

            return self::FAILURE;
        }

        $columnsCleared = 0;

        // Pass 1 — null the column on every OTHER record using this image.
        $query = Prisoner::withUnderReview()
            ->whereNotNull('photo')
            ->where('photo', '!=', '');

        foreach ($query->cursor() as $prisoner) {
            $path = $this->normalizePath($prisoner->photo);
            if ($path === self::KEEP_PATH) {
                continue; // keep John Mazurek's photo
            }
            if (! $disk->exists($path) || ! $this->isTarget($disk, $path)) {
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

        // Pass 2 — delete every copy of this image except the keeper (catches
        // the per-record files just unlinked plus any orphans).
        $filesDeleted = 0;
        foreach ($disk->files('prisoners') as $file) {
            if ($file === self::KEEP_PATH || ! $this->isTarget($disk, $file)) {
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
            .". Kept: ".self::KEEP_PATH." | columns cleared: {$columnsCleared} | duplicate files deleted: {$filesDeleted}");

        return self::SUCCESS;
    }

    private function normalizePath($photo): string
    {
        return ltrim(preg_replace('#^.*/storage/#', '', (string) $photo), '/');
    }

    private function isTarget($disk, string $path): bool
    {
        try {
            if ($disk->size($path) !== self::IMAGE_SIZE) {
                return false;
            }

            return md5($disk->get($path)) === self::IMAGE_MD5;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
