<?php

namespace App\Console\Commands;

use App\Support\MediaFields;
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

        // 3. Pull DB-referenced paths from every model field that holds a
        //    file path so we know which copy to keep and which copies still
        //    have pointers we'll need to rewrite.
        $referenced = [];
        foreach (MediaFields::all() as [$model, $field]) {
            $paths = $model::query()->withoutGlobalScopes()
                ->whereNotNull($field)->where($field, '!=', '')
                ->pluck($field);
            foreach ($paths as $p) {
                foreach (MediaFields::dbPathForms((string) $p) as $form) {
                    $referenced[ltrim($form, '/')] = true;
                }
            }
        }

        $publicDir = base_path('public/').'/';

        $sets = 0;
        // Each entry: ['path' => abs path to delete, 'rel' => public-relative, 'keeperDb' => canonical DB form to point rows at]
        $toDelete = [];
        $toDeleteBytes = 0;
        $rewriteCount = 0;
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
            $keepRel = str_replace($publicDir, '', $keep['path']);
            $keepDb = MediaFields::dbPathFromPublicRelative($keepRel);

            $this->info(sprintf('Set #%d (%s, %d copies):', $sets, $this->fmtBytes($keep['size']), count($entries) + 1));
            $this->line('  KEEP   '.$keepRel);
            foreach ($entries as $e) {
                $delRel = str_replace($publicDir, '', $e['path']);
                $this->line('  DELETE '.$delRel);
                $toDelete[] = ['path' => $e['path'], 'rel' => $delRel, 'keeperDb' => $keepDb];
                $toDeleteBytes += $e['size'];

                // Preview any DB rows that would need their path rewritten.
                foreach ($this->findReferencingRows($delRel) as [$model, $field, $row]) {
                    $rewriteCount++;
                    $this->line(sprintf('    REWRITE %s#%s.%s  %s → %s',
                        class_basename($model), $row->getKey(), $field, $row->{$field}, $keepDb
                    ));
                }
            }
        }

        $this->newLine();
        $this->info(sprintf('Total: %d duplicate set(s), %d file(s) to delete, %d DB pointer(s) to rewrite, %s would be reclaimed.',
            $sets, count($toDelete), $rewriteCount, $this->fmtBytes($toDeleteBytes)));

        if (! $this->option('apply')) {
            $this->info('(dry-run; re-run with --apply to delete the redundant copies and rewrite DB pointers)');
            return self::SUCCESS;
        }

        // Rewrite DB pointers FIRST so no row ever points at a missing file.
        $rewritten = 0;
        foreach ($toDelete as $item) {
            foreach ($this->findReferencingRows($item['rel']) as [$model, $field, $row]) {
                $row->{$field} = $item['keeperDb'];
                $row->save();
                $rewritten++;
            }
        }

        $deleted = 0;
        foreach ($toDelete as $item) {
            if (@unlink($item['path'])) $deleted++;
        }
        $this->info(sprintf('Deleted %d duplicate file(s), reclaimed %s; rewrote %d DB pointer(s).',
            $deleted, $this->fmtBytes($toDeleteBytes), $rewritten));
        return self::SUCCESS;
    }

    /**
     * Find every DB row across MediaFields whose stored path resolves to
     * the given public-relative path. Returns tuples of (model class,
     * field name, model instance).
     *
     * @return iterable<array{0: class-string, 1: string, 2: \Illuminate\Database\Eloquent\Model}>
     */
    private function findReferencingRows(string $publicRel): iterable {
        $forms = MediaFields::dbPathForms($publicRel);
        foreach (MediaFields::all() as [$model, $field]) {
            $rows = $model::query()->withoutGlobalScopes()
                ->whereIn($field, $forms)
                ->get();
            foreach ($rows as $row) {
                yield [$model, $field, $row];
            }
        }
    }

    private function fmtBytes(int $bytes): string {
        if ($bytes < 1024) return $bytes.' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1).' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1).' MB';
        return round($bytes / 1073741824, 2).' GB';
    }
}
