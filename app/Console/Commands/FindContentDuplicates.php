<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * For every group of ArchiveRecord rows sharing a normalized title,
 * compute a content fingerprint for each row's underlying file and
 * report which groups contain BIT-IDENTICAL files vs ones where the
 * files differ (legitimately different artifacts).
 *
 * Local files (file column begins with '/'): SHA-256 hash + filesize.
 * Remote URLs (http://...): URL string compared verbatim; can't
 * cheaply hash without downloading. If two rows share the same
 * remote URL they're already flagged by URL-exact dedup.
 *
 * Pass --delete-content-dupes to remove rows whose file is a
 * bit-identical copy of another row's, keeping the lowest-id row.
 *
 * Pass --group=<slug> to limit to one normalized title's group, e.g.
 *   --group="off the hook"
 */
final class FindContentDuplicates extends Command {
    protected $signature = 'archive:find-content-duplicates {--delete-content-dupes : Auto-delete bit-identical extras, keeping lowest-id} {--group= : Limit to one normalized title}';
    protected $description = 'Group ArchiveRecord rows by title and check whether underlying PDFs are bit-identical';

    public function handle(): int {
        $delete = (bool) $this->option('delete-content-dupes');
        $onlyGroup = $this->option('group');

        $records = ArchiveRecord::orderBy('id')->get(['id', 'slug', 'title', 'file', 'collection']);
        $this->info('Scanning '.$records->count().' ArchiveRecord rows.');

        // Group by normalized title (case-insensitive, strip punctuation).
        $byTitle = [];
        foreach ($records as $r) {
            $key = $this->normalize((string) $r->title);
            if ($key === '') {
                continue;
            }
            $byTitle[$key][] = $r;
        }

        $contentDupeGroups = 0;
        $distinctGroups = 0;
        $unverifiableGroups = 0;
        $toDelete = [];

        foreach ($byTitle as $key => $group) {
            if (count($group) < 2) {
                continue;
            }
            if ($onlyGroup !== null && $onlyGroup !== '' && stripos($key, (string) $onlyGroup) === false) {
                continue;
            }

            $fingerprints = [];
            foreach ($group as $r) {
                $fp = $this->fingerprint($r->file);
                $fingerprints[] = ['row' => $r, 'fp' => $fp];
            }

            // Bucket by fingerprint
            $buckets = [];
            foreach ($fingerprints as $f) {
                $buckets[$f['fp']['key']][] = $f;
            }

            $hasDupe = false;
            $hasUnverifiable = false;
            foreach ($buckets as $bucket) {
                if (count($bucket) > 1) {
                    $hasDupe = true;
                }
            }
            foreach ($fingerprints as $f) {
                if ($f['fp']['kind'] === 'remote') {
                    $hasUnverifiable = true;
                }
            }

            $label = $hasDupe ? 'CONTENT-DUPE' : ($hasUnverifiable ? 'UNVERIFIABLE (remote)' : 'DISTINCT');
            if ($hasDupe) {
                $contentDupeGroups++;
            } elseif ($hasUnverifiable) {
                $unverifiableGroups++;
            } else {
                $distinctGroups++;
            }
            $this->line('');
            $this->line('— ['.$label.']  "'.$key.'" —');
            foreach ($fingerprints as $f) {
                $r = $f['row'];
                $fp = $f['fp'];
                $this->line('  #'.$r->id.'  '.$r->slug.'  ['.$fp['kind'].' '.$fp['display'].']  ('.$r->collection.')');
            }

            if ($hasDupe) {
                // Build delete list: for each bucket with >1 entry, keep lowest-id row, queue others.
                foreach ($buckets as $bucket) {
                    if (count($bucket) < 2) {
                        continue;
                    }
                    usort($bucket, fn ($a, $b) => strcmp($a['row']->id, $b['row']->id));
                    foreach (array_slice($bucket, 1) as $f) {
                        $toDelete[] = $f['row'];
                    }
                }
            }
        }

        $this->line('');
        $this->info('Title groups with confirmed CONTENT-DUPE rows: '.$contentDupeGroups);
        $this->info('Title groups with all DISTINCT files:          '.$distinctGroups);
        $this->info('Title groups containing unverifiable remote refs: '.$unverifiableGroups);
        $this->info('Total rows to delete: '.count($toDelete));

        if ($delete && ! empty($toDelete)) {
            foreach ($toDelete as $r) {
                $this->warn('  deleting #'.$r->id.'  '.$r->slug);
                $r->delete();
            }
            $this->info('Deleted '.count($toDelete).' bit-identical duplicate rows.');
        } elseif (! empty($toDelete)) {
            $this->info('(re-run with --delete-content-dupes to actually delete)');
        }

        return self::SUCCESS;
    }

    private function normalize(string $title): string {
        $t = mb_strtolower(trim($title));
        // Strip trailing version markers our renamer just added so previously
        // disambiguated rows DO group again here (we want to compare content).
        $t = preg_replace('/\s*\([^)]*\)\s*$/', '', $t);
        $t = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $t);
        $t = preg_replace('/\s+/', ' ', $t);

        return trim($t);
    }

    private function fingerprint(?string $file): array {
        if ($file === null || $file === '') {
            return ['key' => 'EMPTY', 'kind' => 'empty', 'display' => '(no file)'];
        }
        if (str_starts_with($file, 'http://') || str_starts_with($file, 'https://')) {
            $norm = strtolower(preg_replace('#^https?://(www\.)?#', '', rtrim($file, '/')));

            return ['key' => 'remote:'.$norm, 'kind' => 'remote', 'display' => parse_url($file, PHP_URL_HOST).parse_url($file, PHP_URL_PATH)];
        }
        $local = public_path(ltrim($file, '/'));
        if (! is_file($local)) {
            return ['key' => 'missing:'.$file, 'kind' => 'missing', 'display' => $file.' (NOT ON DISK)'];
        }
        $size = filesize($local);
        // Fast hash: file size + sha256 of first 256KB. For PDFs this catches identical scans cheaply.
        $fh = fopen($local, 'rb');
        $chunk = fread($fh, 262144);
        fclose($fh);
        $hash = hash('sha256', $chunk);

        return [
            'key' => 'local:'.$size.':'.$hash,
            'kind' => 'local',
            'display' => number_format($size / 1024, 1).'KB '.substr($hash, 0, 10),
        ];
    }
}
