<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Hunts for junk inside public/pdfs/ — orphan tmp files from
 * interrupted OCR runs, OS metadata droppings (.DS_Store, Thumbs.db),
 * editor backups, zero-byte PDFs from failed downloads, and tiny
 * "broken" files. Also lists files on disk that no ArchiveRecord
 * row references (orphans).
 *
 * Dry-run by default. --apply deletes the JUNK category. Orphans are
 * only listed (deleting them is a destructive decision and can break
 * external links).
 *
 *   php artisan archive:cleanup-sweep
 *   php artisan archive:cleanup-sweep --apply
 *   php artisan archive:cleanup-sweep --include-orphans --apply  # also delete orphans
 */
final class ArchiveCleanupSweep extends Command {
    protected $signature = 'archive:cleanup-sweep
        {--apply : Actually delete the JUNK files}
        {--include-orphans : Also delete files on disk that have no DB record (dangerous)}
        {--min-pdf-bytes=2048 : PDFs smaller than this are flagged broken}';
    protected $description = 'Find + clean junk in public/pdfs (tmp files, OS metadata, zero-byte PDFs)';

    public function handle(): int {
        $root = base_path('public/pdfs');
        if (! is_dir($root)) {
            $this->error('No public/pdfs directory found.');
            return self::FAILURE;
        }

        $junkPatterns = [
            '*.ocr.*tmp.pdf'  => 'OCR tmp file',
            '.DS_Store'       => 'macOS metadata',
            'Thumbs.db'       => 'Windows thumbnail cache',
            'desktop.ini'     => 'Windows desktop',
            '._*'             => 'macOS AppleDouble',
            '.#*'             => 'emacs lock file',
            '*.swp'           => 'vim swap',
            '*.swo'           => 'vim swap',
            '*.bak'           => 'backup file',
            '*.orig'          => 'merge leftover',
            '*~'              => 'editor backup',
        ];

        $junk = [];
        foreach ($junkPatterns as $pattern => $label) {
            $cmd = sprintf('find %s -type f -name %s 2>/dev/null', escapeshellarg($root), escapeshellarg($pattern));
            $output = shell_exec($cmd);
            foreach (preg_split('/\r?\n/', trim((string) $output)) as $path) {
                if ($path === '') continue;
                $junk[$path] = ['label' => $label, 'size' => filesize($path) ?: 0];
            }
        }

        // Zero-byte / tiny PDFs from failed downloads.
        $minBytes = (int) $this->option('min-pdf-bytes');
        $cmd = sprintf('find %s -type f -name "*.pdf" -size -%dc 2>/dev/null', escapeshellarg($root), $minBytes);
        $output = shell_exec($cmd);
        foreach (preg_split('/\r?\n/', trim((string) $output)) as $path) {
            if ($path === '') continue;
            $junk[$path] = ['label' => 'broken PDF (<'.$minBytes.'b)', 'size' => filesize($path) ?: 0];
        }

        $this->info('=== Junk files in public/pdfs ===');
        if ($junk) {
            $byLabel = [];
            $totalSize = 0;
            foreach ($junk as $p => $info) {
                $byLabel[$info['label']][] = $p;
                $totalSize += $info['size'];
            }
            foreach ($byLabel as $label => $paths) {
                $this->warn(sprintf('  %s — %d file(s):', $label, count($paths)));
                foreach (array_slice($paths, 0, 12) as $p) {
                    $rel = str_replace($root.'/', '', $p);
                    $size = $junk[$p]['size'];
                    $this->line(sprintf('    %s  (%s)', $rel, $this->fmtBytes($size)));
                }
                if (count($paths) > 12) {
                    $this->line('    ... +'.(count($paths) - 12).' more');
                }
            }
            $this->info(sprintf('Total junk: %d file(s), %s.', count($junk), $this->fmtBytes($totalSize)));
        } else {
            $this->line('  (none found)');
        }

        // Orphan files — on disk but not referenced by any ArchiveRecord.
        $this->newLine();
        $this->info('=== Orphan files (on disk but no ArchiveRecord row) ===');

        $cmd = sprintf('find %s -type f \( -name "*.pdf" -o -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.webp" \) 2>/dev/null', escapeshellarg($root));
        $allFiles = preg_split('/\r?\n/', trim((string) shell_exec($cmd))) ?: [];

        $dbRefs = ArchiveRecord::query()
            ->select('file', 'thumbnail')
            ->get()
            ->flatMap(fn ($r) => array_filter([$r->file, $r->thumbnail]))
            ->map(fn ($p) => ltrim((string) $p, '/'))
            ->toArray();
        $dbRefs = array_flip($dbRefs);

        $orphans = [];
        $orphanSize = 0;
        foreach ($allFiles as $abs) {
            if ($abs === '') continue;
            // Skip junk we already flagged.
            if (isset($junk[$abs])) continue;
            $rel = str_replace(base_path('public/'), '', $abs);
            // Match either with or without a leading slash.
            if (isset($dbRefs[$rel]) || isset($dbRefs['/'.$rel])) continue;
            $orphans[$abs] = filesize($abs) ?: 0;
            $orphanSize += $orphans[$abs];
        }

        if ($orphans) {
            foreach (array_slice($orphans, 0, 25, true) as $abs => $size) {
                $rel = str_replace($root.'/', '', $abs);
                $this->line(sprintf('  %s  (%s)', $rel, $this->fmtBytes($size)));
            }
            if (count($orphans) > 25) {
                $this->line('  ... +'.(count($orphans) - 25).' more');
            }
            $this->info(sprintf('Total orphans: %d file(s), %s.', count($orphans), $this->fmtBytes($orphanSize)));
        } else {
            $this->line('  (none found)');
        }

        // Apply phase.
        $this->newLine();
        if (! $this->option('apply')) {
            $this->info('(dry-run; re-run with --apply to delete the JUNK category)');
            $this->line('Orphans listed above are LISTED only — pass --include-orphans --apply to delete them.');
            return self::SUCCESS;
        }

        $deleted = 0;
        foreach (array_keys($junk) as $path) {
            if (@unlink($path)) $deleted++;
        }
        $this->info("Deleted {$deleted} junk file(s).");

        if ($this->option('include-orphans')) {
            $orphDeleted = 0;
            foreach (array_keys($orphans) as $path) {
                if (@unlink($path)) $orphDeleted++;
            }
            $this->info("Deleted {$orphDeleted} orphan file(s).");
        }

        return self::SUCCESS;
    }

    private function fmtBytes(int $bytes): string {
        if ($bytes < 1024) return $bytes.' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1).' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1).' MB';
        return round($bytes / 1073741824, 2).' GB';
    }
}
