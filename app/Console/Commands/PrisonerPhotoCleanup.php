<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Finds prisoner photo files on disk that no Prisoner row references
 * (e.g. left behind after a prisoner record was deleted) and deletes
 * them. Also reports the reverse — Prisoner rows whose photo path
 * points at a missing file.
 *
 * Storage location: storage/app/public/prisoners/  (and any sub-dirs)
 * Path stored in Prisoner.photo is relative to the public disk root,
 * e.g. "prisoners/leonard-peltier.jpg".
 *
 *   php artisan archive:prisoner-photo-cleanup            # dry-run
 *   php artisan archive:prisoner-photo-cleanup --apply    # delete orphans
 */
final class PrisonerPhotoCleanup extends Command {
    protected $signature = 'archive:prisoner-photo-cleanup
        {--apply : Actually delete orphan photo files}
        {--include-bypass-scope : Also count Prisoner rows hidden by global scopes (under_review)}';
    protected $description = 'Delete prisoner photos on disk that no Prisoner row references';

    public function handle(): int {
        $diskRoot = Storage::disk('public')->path('prisoners');
        if (! is_dir($diskRoot)) {
            $this->error('No storage/app/public/prisoners directory found.');
            return self::FAILURE;
        }

        // 1. Collect every photo path the DB still references.
        $query = Prisoner::query();
        if ($this->option('include-bypass-scope')) {
            $query->withoutGlobalScopes();
        }
        $referenced = $query
            ->whereNotNull('photo')
            ->where('photo', '!=', '')
            ->pluck('photo')
            ->map(fn ($p) => ltrim((string) $p, '/'))
            ->flip()
            ->all();

        // 2. Walk disk for actual files.
        $files = [];
        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($diskRoot, \FilesystemIterator::SKIP_DOTS));
        foreach ($iter as $f) {
            if (! $f->isFile()) continue;
            $abs = $f->getPathname();
            // Photo path in DB is "prisoners/<file>", so strip the storage root prefix.
            $rel = 'prisoners/'.ltrim(str_replace($diskRoot, '', $abs), '/');
            $files[$abs] = $rel;
        }

        // 3. Cross-reference: orphans = on disk, not in DB.
        $orphans = [];
        $orphanSize = 0;
        foreach ($files as $abs => $rel) {
            if (isset($referenced[$rel])) continue;
            $size = filesize($abs) ?: 0;
            $orphans[$abs] = ['rel' => $rel, 'size' => $size];
            $orphanSize += $size;
        }

        // 4. Reverse: photos referenced in DB but missing on disk.
        $missing = [];
        $diskRels = array_flip(array_values($files));
        foreach ($referenced as $rel => $_) {
            if (! isset($diskRels[$rel])) {
                $missing[] = $rel;
            }
        }

        $this->info('=== Prisoner photo cleanup ===');
        $this->line(sprintf('Photos on disk:     %d', count($files)));
        $this->line(sprintf('Photos referenced:  %d', count($referenced)));
        $this->newLine();

        $this->info('Orphan photos (on disk but no Prisoner row): '.count($orphans));
        foreach (array_slice($orphans, 0, 25, true) as $abs => $info) {
            $this->line(sprintf('  %s  (%s)', $info['rel'], $this->fmtBytes($info['size'])));
        }
        if (count($orphans) > 25) {
            $this->line('  ... +'.(count($orphans) - 25).' more');
        }
        $this->info(sprintf('Total orphan size: %s', $this->fmtBytes($orphanSize)));

        $this->newLine();
        $this->warn('Photos referenced by DB but missing on disk: '.count($missing));
        foreach (array_slice($missing, 0, 25) as $rel) {
            $this->line('  '.$rel);
        }
        if (count($missing) > 25) {
            $this->line('  ... +'.(count($missing) - 25).' more');
        }

        $this->newLine();
        if (! $this->option('apply')) {
            $this->info('(dry-run; re-run with --apply to delete the '.count($orphans).' orphan(s))');
            return self::SUCCESS;
        }

        $deleted = 0;
        foreach (array_keys($orphans) as $abs) {
            if (@unlink($abs)) $deleted++;
        }
        $this->info("Deleted {$deleted} orphan photo(s), reclaimed {$this->fmtBytes($orphanSize)}.");
        return self::SUCCESS;
    }

    private function fmtBytes(int $bytes): string {
        if ($bytes < 1024) return $bytes.' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1).' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1).' MB';
        return round($bytes / 1073741824, 2).' GB';
    }
}
