<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Imports PDFs surfaced from the local ABC chapter sites
 * (Austin / Boston / Charm City / Bay Area / Cleveland / KC / Louisville /
 * Portland / Salt Lake City). For each PDF in public/pdfs/abc-chapters/,
 * we infer the chapter from the filename prefix and create an
 * ArchiveRecord with sensible metadata.
 */
final class ImportAbcChapters extends Command {
    protected $signature = 'archive:import-abc-chapters';
    protected $description = 'Import PDFs scraped from ABC chapter sites';

    public function handle(): int {
        $dir = public_path('pdfs/abc-chapters');
        if (! is_dir($dir)) {
            $this->error("Directory not found: {$dir}");

            return self::FAILURE;
        }

        $files = glob($dir.'/*.pdf');
        $created = 0;
        $updated = 0;
        $sort = 600;

        foreach ($files as $f) {
            $fname = basename($f);

            // Format: "<chapter-host>__<original-name>.pdf"
            $chapter = $this->chapterFromFilename($fname);
            $core = preg_replace('/^[^_]+__/', '', $fname);
            $slugBase = pathinfo($core, PATHINFO_FILENAME);
            $slugBase = preg_replace('/[^a-z0-9\-]+/i', '-', $slugBase);
            $slugBase = trim(strtolower($slugBase), '-');
            $archiveSlug = 'abc-'.$chapter['key'].'-'.$slugBase;

            $title = $this->titleFromSlug($slugBase);
            $thumb = is_file(public_path('images/archive/abc-chapters/'.pathinfo($fname, PATHINFO_FILENAME).'-cover.jpg'))
                ? '/images/archive/abc-chapters/'.pathinfo($fname, PATHINFO_FILENAME).'-cover.jpg'
                : null;

            $payload = [
                'title' => $title,
                'description' => "Zine sourced from {$chapter['name']}'s online library ({$chapter['site']}).",
                'record_type' => 'document',
                'source_format' => 'pamphlet',
                'file' => '/pdfs/abc-chapters/'.$fname,
                'thumbnail' => $thumb,
                'collection' => $chapter['name'],
                'subjects' => ['Anarchist Black Cross', 'Prisoner Support', 'Political Prisoners'],
                'is_digitized' => true,
                'published' => true,
                'sort_order' => $sort++,
            ];

            $existing = ArchiveRecord::where('slug', $archiveSlug)->first();
            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                ArchiveRecord::create(['slug' => $archiveSlug] + $payload);
                $created++;
            }
        }

        $this->info("Done. Created={$created} Updated={$updated}");

        return self::SUCCESS;
    }

    /**
     * @return array{key:string,name:string,site:string}
     */
    private function chapterFromFilename(string $fname): array {
        $host = explode('__', $fname, 2)[0] ?? '';
        $host = preg_replace('/\.$/', '', $host);

        return match (true) {
            str_contains($host, 'atxanarchistblackcross') => ['key' => 'atx', 'name' => 'Austin ABC', 'site' => 'atxanarchistblackcross.wordpress.com'],
            str_contains($host, 'bostonanarchistblackcross') => ['key' => 'boston', 'name' => 'Boston ABC', 'site' => 'bostonanarchistblackcross.wordpress.com'],
            str_contains($host, 'bayareaabc') => ['key' => 'bay', 'name' => 'Bay Area ABC', 'site' => 'bayareaabc.wordpress.com'],
            str_contains($host, 'charmcityabc') => ['key' => 'charm', 'name' => 'Charm City ABC', 'site' => 'charmcityabc.noblogs.org'],
            str_contains($host, 'clevelandabc') => ['key' => 'cle', 'name' => 'Cleveland ABC', 'site' => 'clevelandabc.blogspot.com'],
            str_contains($host, 'kansascityabc') => ['key' => 'kc', 'name' => 'Kansas City ABC', 'site' => 'kansascityabc.wordpress.com'],
            str_contains($host, 'lvilleabc') => ['key' => 'lville', 'name' => 'Louisville ABC', 'site' => 'lvilleabc.wordpress.com'],
            str_contains($host, 'pdxabc') => ['key' => 'pdx', 'name' => 'Portland ABC', 'site' => 'pdxabc.com'],
            str_contains($host, 'slabc') => ['key' => 'slc', 'name' => 'Salt Lake City ABC', 'site' => 'slabc.wordpress.com'],
            str_contains($host, 'whichsidepodcast') => ['key' => 'slc', 'name' => 'Salt Lake City ABC', 'site' => 'slabc.wordpress.com'],
            default => ['key' => 'misc', 'name' => 'ABC Chapter (misc)', 'site' => $host],
        };
    }

    private function titleFromSlug(string $slug): string {
        $s = str_replace(['_', '-'], ' ', $slug);
        // Drop common noise suffixes
        $s = preg_replace('/\b(final|print|internet|screen|version|web|imposed|read|comp|small|for download)\b/i', '', $s);
        $s = preg_replace('/\s+/', ' ', $s);

        return trim(ucwords($s));
    }
}
