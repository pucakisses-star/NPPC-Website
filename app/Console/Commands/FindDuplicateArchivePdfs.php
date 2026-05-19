<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Find ArchiveRecord rows whose `file` points at byte-identical PDFs.
 *
 * Hashes every locally-hosted PDF referenced by an ArchiveRecord (any
 * `file` starting with `/pdfs/`) with SHA-256, groups by hash, and
 * prints each duplicate cluster with the slug + title + size of every
 * record in it.
 *
 * Read-only — does not delete or merge anything. Use the output to
 * decide which dupes to keep and which to remove with `archive:delete`
 * or a follow-up cleanup command.
 *
 * Options:
 *   --json    Machine-readable output (object of hash => list of records)
 *   --hash    Filter to a single hash (full or 12-char prefix)
 *   --min=N   Only show clusters with at least N copies (default 2)
 */
final class FindDuplicateArchivePdfs extends Command {
    protected $signature = 'archive:find-duplicate-pdfs {--json} {--hash=} {--min=2}';
    protected $description = 'Find ArchiveRecord rows whose self-hosted PDFs are byte-identical';

    public function handle(): int {
        $records = ArchiveRecord::query()
            ->where('file', 'like', '/pdfs/%.pdf')
            ->orderBy('slug')
            ->get(['id', 'slug', 'title', 'file']);

        $this->info("Hashing {$records->count()} locally-hosted PDF(s)...");

        $groups = [];   // hash => list of [slug, title, file, size]
        $missing = 0; $hashed = 0;
        foreach ($records as $r) {
            $path = public_path(ltrim($r->file, '/'));
            if (!file_exists($path)) {
                $missing++;
                continue;
            }
            $hash = @hash_file('sha256', $path);
            if ($hash === false) continue;
            $size = filesize($path) ?: 0;
            $groups[$hash][] = [
                'slug' => $r->slug,
                'title' => $r->title,
                'file' => $r->file,
                'size' => $size,
            ];
            $hashed++;
        }

        $filterHash = $this->option('hash');
        $min = max(1, (int) $this->option('min'));

        $dupes = [];
        foreach ($groups as $hash => $rows) {
            if (count($rows) < $min) continue;
            if ($filterHash !== null && !str_starts_with($hash, $filterHash)) continue;
            $dupes[$hash] = $rows;
        }
        // Largest cluster first
        uksort($dupes, fn ($a, $b) => count($groups[$b]) <=> count($groups[$a]));

        if ($this->option('json')) {
            $this->line(json_encode($dupes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        if (empty($dupes)) {
            $this->info("Hashed {$hashed}, missing on disk {$missing}. No duplicate clusters at min={$min}.");
            return self::SUCCESS;
        }

        $totalWaste = 0;
        $clusters = 0;
        foreach ($dupes as $hash => $rows) {
            $clusters++;
            $size = $rows[0]['size'];
            $waste = $size * (count($rows) - 1);
            $totalWaste += $waste;
            $this->line('');
            $this->line(sprintf(
                "=== cluster %d : %d copies, %.1f MB each, %.1f MB wasted ===",
                $clusters,
                count($rows),
                $size / 1048576,
                $waste / 1048576,
            ));
            $this->line("    hash: ".substr($hash, 0, 16)."...");
            foreach ($rows as $row) {
                $this->line(sprintf("    %-60s  %s", $row['slug'], $row['title']));
                $this->line(sprintf("      file: %s", $row['file']));
            }
        }

        $this->line('');
        $this->info(sprintf(
            "Hashed %d, missing on disk %d. Found %d duplicate cluster(s), %.1f MB wasted total.",
            $hashed, $missing, $clusters, $totalWaste / 1048576
        ));
        return self::SUCCESS;
    }
}
