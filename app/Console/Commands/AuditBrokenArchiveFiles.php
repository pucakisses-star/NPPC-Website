<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Walks every ArchiveRecord and reports any whose `file` column
 * points at a path that doesn't exist on disk. Optional --delete
 * removes the broken records from the DB so they stop showing
 * "Failed to load" on /archive-records.
 */
final class AuditBrokenArchiveFiles extends Command {
    protected $signature = 'archive:audit-broken-files {--full : Print every broken row, not just the first 50} {--delete : Delete the broken ArchiveRecord rows after listing}';
    protected $description = 'List ArchiveRecord rows whose file column 404s, and optionally delete them';

    public function handle(): int {
        $publicRoot = public_path();
        $storageRoot = storage_path('app/public');

        $broken = [];
        $missingHttp = [];
        $checked = 0;

        ArchiveRecord::query()
            ->whereNotNull('file')
            ->where('file', '!=', '')
            ->orderBy('id')
            ->chunk(200, function ($chunk) use (&$broken, &$missingHttp, &$checked, $publicRoot, $storageRoot) {
                foreach ($chunk as $r) {
                    $checked++;
                    $f = $r->file;
                    if (str_starts_with($f, 'http://') || str_starts_with($f, 'https://')) {
                        $missingHttp[] = ['id' => $r->id, 'slug' => $r->slug, 'file' => $f];

                        continue;
                    }
                    $rel = ltrim($f, '/');
                    $candidates = [
                        $publicRoot.DIRECTORY_SEPARATOR.$rel,
                        $storageRoot.DIRECTORY_SEPARATOR.$rel,
                    ];
                    if (str_starts_with($rel, 'storage/')) {
                        $candidates[] = $storageRoot.DIRECTORY_SEPARATOR.substr($rel, strlen('storage/'));
                    }
                    $ok = false;
                    foreach ($candidates as $abs) {
                        if (is_file($abs) && filesize($abs) > 0) {
                            $ok = true;
                            break;
                        }
                    }
                    if (! $ok) {
                        $broken[] = ['id' => $r->id, 'slug' => $r->slug, 'title' => $r->title, 'file' => $f];
                    }
                }
            });

        $this->info('Checked '.$checked.' ArchiveRecord rows with non-empty file column.');
        $this->info('Broken (file missing on disk): '.count($broken));
        if (count($missingHttp)) {
            $this->info('Stored as external URL (skipped from disk check): '.count($missingHttp));
        }

        $rows = $this->option('full') ? $broken : array_slice($broken, 0, 50);
        foreach ($rows as $row) {
            $this->line('  - '.$row['slug'].'  ('.$row['title'].')  file='.$row['file']);
        }
        if (! $this->option('full') && count($broken) > 50) {
            $this->line('  ... '.(count($broken) - 50).' more (re-run with --full)');
        }

        if ($this->option('delete') && count($broken) > 0) {
            $this->warn("\nDeleting ".count($broken).' broken ArchiveRecord rows...');
            $ids = array_column($broken, 'id');
            ArchiveRecord::whereIn('id', $ids)->delete();
            $this->info('Done.');
        }

        return self::SUCCESS;
    }
}
