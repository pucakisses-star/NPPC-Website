<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Finds duplicate files on disk (same content, different paths) and
 * reports / deletes the redundant copies.
 *
 * Strategy — cheap-then-expensive:
 *   1. Walk a directory (default: public/).
 *   2. Group files by size — files of different size can't be duplicates.
 *   3. For each size-bucket with >1 file, hash each file with MD5.
 *   4. For each hash-bucket with >1 file, that's a duplicate set.
 *   5. Pick a "canonical" file to KEEP — prefer one referenced by an
 *      ArchiveRecord.file/.thumbnail or Prisoner.photo, then oldest
 *      mtime, then alphabetical. Other copies get reported / deleted.
 *
 * Dry-run by default. --apply deletes the redundant copies.
 *
 *   php artisan archive:dedupe-files
 *   php artisan archive:dedupe-files --root=public/pdfs
 *   php artisan archive:dedupe-files --min-bytes=10240 --apply
 */
final class DedupeFiles extends Command {
    protected $signature = 'archive:dedupe-files
        {--root=public : Directory to walk (relative to base_path or absolute)}
        {--min-bytes=1024 : Skip files smaller than this (default 1KB)}
        {--apply : Actually delete the redundant copies}
        {--ext=pdf,jpg,jpeg,png,webp,gif,mp4,mp3,m4a,zip : Comma-separated extension allow-list}';
    protected $description = 'Find + delete duplicate files (same content, different paths)';

    public function handle(): int {
        $rootOpt = (string) $this->option('root');
        $root = realpath($rootOpt) ?: realpath(base_path($rootOpt));
        if (! $root || ! is_dir($root)) {
            $this->error("Root directory not found: {$rootOpt}");
            return self::FAILURE;
        }
        $minBytes = (int) $this->option('min-bytes');
        $exts = array_filter(array_map('trim', explode(',', strtolower((string) $this->option('ext')))));

        // 1. Walk & bucket by size.
        $this->info('Walking '.$root.' ...');
        $bySize = [];
        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS));
        $total = 0; $totalBytes = 0;
        foreach ($iter as $f) {
            if (! $f->isFile() || $f->isLink()) continue;
            $size = $f->getSize();
            if ($size < $minBytes) continue;
            $ext = strtolower(pathinfo($f->getFilename(), PATHINFO_EXTENSION));
            if ($exts && ! in_array($ext, $exts, true)) continue;
            $bySize[$size][] = $f->getPathname();
            $total++;
            $totalBytes += $size;
        }
        $this->line(sprintf('Scanned %d file(s), %s total.', $total, $this->fmtBytes($totalBytes)));

        // Only size-buckets with 2+ entries can have duplicates.
        $candidates = array_filter($bySize, fn ($v) => count($v) > 1);
        $candidateCount = array_sum(array_map('count', $candidates));
        $this->line(sprintf('Size-collision candidates: %d file(s) across %d size-bucket(s).', $candidateCount, count($candidates)));

        // 2. Hash candidates.
        $byHash = [];
        $bar = $this->output->createProgressBar($candidateCount);
        $bar->start();
        foreach ($candidates as $size => $paths) {
            foreach ($paths as $p) {
                $bar->advance();
                $hash = @md5_file($p);
                if (! $hash) continue;
                $byHash[$hash][] = ['path' => $p, 'size' => $size];
            }
        }
        $bar->finish();
        $this->newLine(2);

        $duplicates = array_filter($byHash, fn ($v) => count($v) > 1);
        if (! $duplicates) {
            $this->info('No duplicate files found.');
            return self::SUCCESS;
        }

        // 3. Pull DB-referenced paths so we know which copy to keep.
        $referenced = [];
        foreach (ArchiveRecord::query()->select('file', 'thumbnail')->get() as $r) {
            foreach ([$r->file, $r->thumbnail] as $p) {
                if ($p) $referenced[ltrim((string) $p, '/')] = true;
            }
        }
        foreach (Prisoner::query()->whereNotNull('photo')->where('photo', '!=', '')->pluck('photo') as $p) {
            $referenced[ltrim((string) $p, '/')] = true;
        }

        $publicDir = base_path('public/').'/';

        $sets = 0; $toDelete = []; $toDeleteBytes = 0;
        foreach ($duplicates as $hash => $entries) {
            $sets++;

            // Pick a canonical "keeper":
            //   1. file path matches a referenced DB row (strip public/ prefix)
            //   2. oldest mtime
            //   3. alphabetically first
            usort($entries, function ($a, $b) use ($referenced, $publicDir) {
                $aRel = str_replace($publicDir, '', $a['path']);
                $bRel = str_replace($publicDir, '', $b['path']);
                $aRef = isset($referenced[$aRel]) ? 1 : 0;
                $bRef = isset($referenced[$bRel]) ? 1 : 0;
                if ($aRef !== $bRef) return $bRef <=> $aRef;
                $aMtime = filemtime($a['path']) ?: PHP_INT_MAX;
                $bMtime = filemtime($b['path']) ?: PHP_INT_MAX;
                if ($aMtime !== $bMtime) return $aMtime <=> $bMtime;
                return strcmp($a['path'], $b['path']);
            });
            $keep = array_shift($entries);

            $this->info(sprintf('Set #%d (%s, %d copies):', $sets, $this->fmtBytes($keep['size']), count($entries) + 1));
            $this->line('  KEEP   '.str_replace($publicDir, '', $keep['path']));
            foreach ($entries as $e) {
                $this->line('  DELETE '.str_replace($publicDir, '', $e['path']));
                $toDelete[] = $e['path'];
                $toDeleteBytes += $e['size'];
            }
        }

        $this->newLine();
        $this->info(sprintf('Total: %d duplicate set(s), %d file(s) to delete, %s would be reclaimed.',
            $sets, count($toDelete), $this->fmtBytes($toDeleteBytes)));

        if (! $this->option('apply')) {
            $this->info('(dry-run; re-run with --apply to delete the redundant copies)');
            return self::SUCCESS;
        }

        $deleted = 0;
        foreach ($toDelete as $p) {
            if (@unlink($p)) $deleted++;
        }
        $this->info(sprintf('Deleted %d duplicate file(s), reclaimed %s.', $deleted, $this->fmtBytes($toDeleteBytes)));
        return self::SUCCESS;
    }

    private function fmtBytes(int $bytes): string {
        if ($bytes < 1024) return $bytes.' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1).' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1).' MB';
        return round($bytes / 1073741824, 2).' GB';
    }
}
