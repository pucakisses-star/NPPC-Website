<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Scans storage/app/public/prisoners/ for orphan image files and links
 * them back to prisoners whose photo column is empty (typically because
 * the earlier era-validator wrongly cleared them).
 *
 * Two filename patterns are supported:
 *   - {slug}-{uuid}.{ext}  (admin-uploaded via Filament)
 *   - {slug}.{ext}         (Wikipedia-enriched)
 *
 * Only fills prisoners whose photo is currently empty. Idempotent.
 * Prefers UUID-suffixed (admin-uploaded) filenames over the Wikipedia
 * pattern when both exist.
 */
final class RestoreOrphanPrisonerPhotos extends Command {
    protected $signature = 'archive:restore-orphan-prisoner-photos {--dry-run} {--limit=0}';
    protected $description = 'Re-link orphan photo files in storage/prisoners/ to prisoners whose photo column is empty';

    public function handle(): int {
        $disk = Storage::disk('public');
        if (! $disk->exists('prisoners')) {
            $this->error('storage/app/public/prisoners/ does not exist.');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');

        // Build a slug → file-path lookup. Prefer UUID-suffixed names
        // (admin-uploaded) over plain slug filenames.
        $files = $disk->files('prisoners');
        $bySlug = [];
        $bySlugUuid = [];
        foreach ($files as $file) {
            $base = pathinfo($file, PATHINFO_FILENAME);
            if (preg_match('/^(.+?)-[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $base, $m)) {
                $bySlugUuid[$m[1]] = $file;
            } else {
                $bySlug[$base] = $file;
            }
        }
        $this->info('Indexed '.count($bySlugUuid).' UUID-suffixed + '.count($bySlug).' plain-slug photo files.');

        $query = Prisoner::query()
            ->whereNotNull('slug')
            ->where(function ($q) {
                $q->whereNull('photo')->orWhere('photo', '');
            });
        if ($limit > 0) {
            $query->limit($limit);
        }
        $prisoners = $query->get();
        $this->info("Checking {$prisoners->count()} prisoners with empty photo + a slug…");

        $restored = 0;
        $miss = 0;

        foreach ($prisoners as $p) {
            $slug = (string) $p->slug;
            $match = $bySlugUuid[$slug] ?? $bySlug[$slug] ?? null;
            if (! $match) {
                $miss++;

                continue;
            }
            $this->info("RESTORE {$p->name} ← {$match}");
            if (! $dry) {
                $p->photo = $match;
                $p->save();
            }
            $restored++;
        }

        $verb = $dry ? 'would restore' : 'restored';
        $this->info("\nDone. {$verb}={$restored} no_orphan_found={$miss} ".($dry ? '(DRY RUN)' : ''));

        return self::SUCCESS;
    }
}
