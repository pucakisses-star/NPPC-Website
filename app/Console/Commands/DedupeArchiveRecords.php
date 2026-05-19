<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Collapse duplicate ArchiveRecord rows that point at byte-identical
 * PDFs. Hashes every locally-hosted PDF, groups by SHA-256, picks a
 * winner per cluster, and deletes the losers (and their now-orphaned
 * PDF files).
 *
 * Keep-ranking (best first):
 *   1.  slug NOT starting with abc-boston-          (boston-abc-* is newer / cleaner)
 *   2.  slug NOT starting with abcf-library-        (abcf-* is newer / cleaner)
 *   3.  slug NOT starting with fa1057-              (freedom-archives-c167-* is the better revision)
 *   4.  slug NOT starting with azine-               (richer publisher slugs preferred)
 *   5.  slug NOT starting with abc-                 (more specific scoped slugs preferred)
 *   6.  has a thumbnail set                         (don't throw away an already-generated cover)
 *   7.  shorter slug                                (heuristic — cleaner naming)
 *
 * Dry-run by default. Pass --apply to actually delete.
 */
final class DedupeArchiveRecords extends Command {
    protected $signature = 'archive:dedupe {--apply : Actually delete losing records and orphaned files} {--keep-files : Even with --apply, don\'t delete the orphaned PDF files}';
    protected $description = 'Collapse ArchiveRecord rows that point at byte-identical PDFs';

    public function handle(): int {
        $records = ArchiveRecord::query()
            ->where('file', 'like', '/pdfs/%.pdf')
            ->orderBy('slug')
            ->get(['id', 'slug', 'title', 'file', 'thumbnail']);

        $this->info("Hashing {$records->count()} locally-hosted PDF(s)...");

        $groups = [];
        foreach ($records as $r) {
            $path = public_path(ltrim($r->file, '/'));
            if (!file_exists($path)) continue;
            $hash = @hash_file('sha256', $path);
            if ($hash === false) continue;
            $groups[$hash][] = $r;
        }

        // Filter to clusters of 2+
        $dupes = array_filter($groups, fn ($g) => count($g) > 1);

        if (empty($dupes)) {
            $this->info('No duplicate clusters.');
            return self::SUCCESS;
        }

        $apply = (bool) $this->option('apply');
        $keepFiles = (bool) $this->option('keep-files');
        $clusters = 0; $deletedRows = 0; $deletedFiles = 0; $keptFiles = 0; $sameFile = 0;

        foreach ($dupes as $hash => $rows) {
            $clusters++;
            usort($rows, fn ($a, $b) => $this->rank($a) <=> $this->rank($b));
            $winner = $rows[0];
            $losers = array_slice($rows, 1);

            $this->line('');
            $this->line(sprintf('--- cluster %d (%d copies, hash %s) ---', $clusters, count($rows), substr($hash, 0, 12)));
            $this->info(sprintf('  KEEP   %-60s  %s', $winner->slug, $winner->file));
            foreach ($losers as $l) {
                $this->warn(sprintf('  DELETE %-60s  %s', $l->slug, $l->file));
            }

            if (!$apply) continue;

            $winnerFile = $winner->file;
            foreach ($losers as $l) {
                $loserFile = $l->file;
                $l->delete();
                $deletedRows++;
                if ($loserFile === $winnerFile) {
                    $sameFile++;
                    continue; // never delete a file the winner still references
                }
                if ($keepFiles) { $keptFiles++; continue; }
                // Make sure no other surviving record references this file.
                $stillUsed = ArchiveRecord::where('file', $loserFile)->exists();
                if ($stillUsed) { $keptFiles++; continue; }
                $localPath = public_path(ltrim($loserFile, '/'));
                if (file_exists($localPath)) {
                    unlink($localPath);
                    $deletedFiles++;
                }
            }
        }

        $this->line('');
        if (!$apply) {
            $this->info("DRY RUN — would delete {$deletedRows} rows. Re-run with --apply to commit.");
            $this->info(sprintf('Found %d duplicate cluster(s).', $clusters));
        } else {
            $this->info(sprintf(
                'Done — %d clusters processed; deleted %d row(s), %d orphan PDF file(s); kept %d file(s) referenced by survivors; %d loser(s) shared a file with the winner.',
                $clusters, $deletedRows, $deletedFiles, $keptFiles, $sameFile
            ));
        }
        return self::SUCCESS;
    }

    /**
     * Lower = better. The first record after a stable usort() of a
     * cluster by this rank is the one we keep.
     */
    private function rank(ArchiveRecord $r): array {
        $s = $r->slug;
        return [
            str_starts_with($s, 'abc-boston-')      ? 1 : 0,
            str_starts_with($s, 'abcf-library-')    ? 1 : 0,
            str_starts_with($s, 'fa1057-')          ? 1 : 0,
            str_starts_with($s, 'azine-')           ? 1 : 0,
            str_starts_with($s, 'abc-')             ? 1 : 0,
            empty($r->thumbnail)                    ? 1 : 0,
            strlen($s),
        ];
    }
}
