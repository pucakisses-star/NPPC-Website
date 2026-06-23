<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Clears the "NO IMAGE AVAILABLE" placeholder graphic that was saved into the
 * `photo` column of many prisoner records (so the site treated them as having a
 * real photo). Records are matched by the MD5 of their stored image file, not by
 * a hard-coded name list, so it clears exactly the placeholder copies wherever
 * they are and is safe to re-run (idempotent). Use --dry-run to preview.
 *
 * It only ever NULLs the placeholder photo; it never deletes the underlying
 * file and never touches records whose photo is a genuine portrait.
 */
final class ClearPlaceholderPhotos extends Command
{
    protected $signature = 'prisoners:clear-placeholder-photos {--dry-run : List affected records without changing anything}';

    protected $description = 'Null the "NO IMAGE AVAILABLE" placeholder graphic stored as a real prisoner photo';

    /** MD5(s) of known placeholder images (the "NO IMAGE AVAILABLE" graphic, 41,671 bytes). */
    private const PLACEHOLDER_MD5 = [
        '56f7ee32d16ac711c6768265e1538357',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');

        $cleared = 0;
        $missingFiles = 0;

        $query = Prisoner::withUnderReview()
            ->whereNotNull('photo')
            ->where('photo', '!=', '');

        foreach ($query->cursor() as $prisoner) {
            $path = ltrim(preg_replace('#^.*/storage/#', '', (string) $prisoner->photo), '/');

            if (! $disk->exists($path)) {
                $missingFiles++;

                continue;
            }

            $md5 = md5($disk->get($path));
            if (! in_array($md5, self::PLACEHOLDER_MD5, true)) {
                continue;
            }

            if ($dryRun) {
                $this->line("  would clear: {$prisoner->name}  ({$prisoner->photo})");
            } else {
                $prisoner->photo = null;
                $prisoner->save();
                $this->info("  cleared: {$prisoner->name}");
            }
            $cleared++;
        }

        $verb = $dryRun ? 'would be cleared' : 'cleared';
        $this->info("\nDone. Placeholder photos {$verb}: {$cleared}  (files not found on disk: {$missingFiles})");

        return self::SUCCESS;
    }
}
