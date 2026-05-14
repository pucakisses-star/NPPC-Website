<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Reports likely duplicate ArchiveRecord entries by two passes:
 *
 *   1. URL-exact: any two records whose normalized `file` matches
 *      (strip protocol, www, trailing slash, lowercase).
 *   2. Title-similar: groups of records whose titles match after
 *      stripping common version/edition suffixes. Intentionally
 *      preserves legitimate version chains (NYC ABC Illustrated
 *      Guide monthly editions, Black Flag vol/issue numbering,
 *      multi-part FBI Vault files, etc.) — those are NOT flagged.
 *
 * Pass --delete-url-dupes to remove the URL-exact duplicates
 * automatically (keeps the lowest-id row). Title-similar matches
 * are always report-only — they need human review.
 */
final class FindArchiveDuplicates extends Command {
    protected $signature = 'archive:find-duplicates {--delete-url-dupes : Auto-delete URL-exact dupes, keeping the lowest-id row}';
    protected $description = 'Find duplicate ArchiveRecord entries (URL-exact + title-similar)';

    public function handle(): int {
        $delete = (bool) $this->option('delete-url-dupes');

        $records = ArchiveRecord::orderBy('id')->get(['id', 'slug', 'title', 'file', 'collection']);
        $this->info('Scanning '.$records->count().' ArchiveRecord rows.');

        // -- Pass 1: URL-exact duplicates --
        $byUrl = [];
        foreach ($records as $r) {
            if (empty($r->file)) {
                continue;
            }
            $key = $this->normalizeUrl($r->file);
            $byUrl[$key][] = $r;
        }

        $this->line("\n— URL-EXACT DUPLICATES —");
        $urlDupeGroups = 0;
        $urlDupeRows = 0;
        $toDelete = [];
        foreach ($byUrl as $key => $group) {
            if (count($group) < 2) {
                continue;
            }
            $urlDupeGroups++;
            $urlDupeRows += count($group) - 1;
            $this->warn('URL: '.$key);
            foreach ($group as $i => $r) {
                $marker = $i === 0 ? '  [KEEP]' : '  [DUPE]';
                $this->line($marker.' #'.$r->id.'  '.$r->slug.'  ('.$r->collection.')  '.$r->title);
                if ($i > 0) {
                    $toDelete[] = $r;
                }
            }
            $this->line('');
        }
        $this->info("URL-exact duplicate groups: {$urlDupeGroups}    Extra rows: {$urlDupeRows}");

        if ($delete && ! empty($toDelete)) {
            foreach ($toDelete as $r) {
                $r->delete();
            }
            $this->info('Deleted '.count($toDelete).' URL-exact duplicate rows.');
        } elseif (! empty($toDelete)) {
            $this->info('(re-run with --delete-url-dupes to remove the '.count($toDelete).' duplicate rows)');
        }

        // -- Pass 2: Title-similar (excluding version chains) --
        $byTitle = [];
        foreach ($records as $r) {
            // Skip rows we just marked for deletion
            if (in_array($r->id, array_map(fn ($x) => $x->id, $toDelete), true)) {
                continue;
            }
            $stripped = $this->stripVersionSuffixes((string) $r->title);
            $key = $this->normalizeTitle($stripped);
            if ($key === '') {
                continue;
            }
            $byTitle[$key][] = $r;
        }

        $this->line("\n— TITLE-SIMILAR (NEEDS HUMAN REVIEW) —");
        $titleGroups = 0;
        foreach ($byTitle as $key => $group) {
            if (count($group) < 2) {
                continue;
            }
            // If all titles share a version/issue suffix marker, treat
            // as legitimate version chain and skip.
            if ($this->looksLikeVersionChain(array_map(fn ($r) => (string) $r->title, $group))) {
                continue;
            }
            $titleGroups++;
            $this->warn('Normalized title: "'.$key.'"');
            foreach ($group as $r) {
                $this->line('  #'.$r->id.'  '.$r->slug.'  ('.$r->collection.')  '.$r->title);
            }
            $this->line('');
        }
        $this->info("Title-similar groups (likely dupes, review): {$titleGroups}");

        return self::SUCCESS;
    }

    private function normalizeUrl(string $url): string {
        $u = strtolower(trim($url));
        $u = preg_replace('#^https?://#', '', $u);
        $u = preg_replace('#^www\.#', '', $u);
        $u = rtrim($u, '/');

        return $u;
    }

    private function normalizeTitle(string $title): string {
        $t = mb_strtolower(trim($title));
        $t = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $t);
        $t = preg_replace('/\s+/', ' ', $t);

        return trim($t);
    }

    /** Strip version/issue/edition suffixes that distinguish legitimate variants. */
    private function stripVersionSuffixes(string $title): string {
        $patterns = [
            '/\s*[\-–—]\s*part\s*\d+\s*(of\s*\d+)?\s*(\(final\))?\s*$/i',
            '/\s*\(\s*part\s*\d+\s*(of\s*\d+)?\s*(\(final\))?\s*\)\s*$/i',
            '/\s*[\-–—]?\s*v\d+(\.\d+)?\s*$/i',
            '/\s*\(\s*v\d+(\.\d+)?\s*\)\s*$/i',
            '/\s*[\-–—]?\s*vol\.?\s*\d+(\s*[\-–—]?\s*\d+)?\s*$/i',
            '/\s*\(\s*vol\.?\s*\d+([\s\-–—]+no\.?\s*\d+)?\s*\)\s*$/i',
            '/\s*no\.?\s*\d+\s*\(.+\d{4}\)\s*$/i',
            '/\s*\(?\s*no\.?\s*\d+\s*\)?\s*$/i',
            '/\s*#\d+\s*$/i',
            '/\s*\(\s*\d{4}\s*\)\s*$/',
            '/\s*\((january|february|march|april|may|june|july|august|september|october|november|december)\s+\d{4}\)\s*$/i',
            '/\s*[\-–—]\s*(january|february|march|april|may|june|july|august|september|october|november|december)\s+\d{4}\s*$/i',
            '/\s*\(\s*\d{4}[\-–—]\d{2}[\-–—]\d{2}\s*\)\s*$/',
            '/\s*[\-–—]\s*\d{4}[\-–—]\d{2}[\-–—]\d{2}\s*$/',
        ];
        $prev = '';
        $cur = $title;
        while ($cur !== $prev) {
            $prev = $cur;
            foreach ($patterns as $p) {
                $cur = preg_replace($p, '', $cur);
            }
            $cur = trim($cur);
        }

        return $cur;
    }

    /** Heuristic — if titles in a group all contain a numeric or month/year suffix, treat as version chain. */
    private function looksLikeVersionChain(array $titles): bool {
        if (count($titles) < 2) {
            return false;
        }
        $versionMarker = '/(v\d+(\.\d+)?|part\s*\d+|vol\.?\s*\d+|no\.?\s*\d+|#\d+|\b(january|february|march|april|may|june|july|august|september|october|november|december)\s+\d{4}\b|\b\d{4}[\-–—]\d{2}[\-–—]\d{2}\b)/i';
        foreach ($titles as $t) {
            if (! preg_match($versionMarker, $t)) {
                return false;
            }
        }

        return true;
    }
}
