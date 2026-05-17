<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Self-host the four remaining archive records that previously linked
 * to external sites — plus delete the now-redundant BPP 1967-1968 IA
 * collection record (its content is fully covered by the 69 individual
 * issue records mirrored in PR #478).
 *
 * Affected records:
 *  - Deletes: the-black-panther-newspaper-1967-1968-ia-collection
 *      (redundant — superseded by per-issue records in PR #478)
 *  - Updates: nyc-abc-illustrated-guide
 *      file repointed to /pdfs/abc/nyc-abc-illustrated-guide-political-prisoners-v19-3-2026.pdf
 *  - Updates: anarchy-live-michael-kimble
 *      file repointed to /pdfs/anarchist-prisoners/anarchy-live-writings-of-michael-kimble.pdf
 *  - Replaces: fbi-cointelpro-twwa-foia-set (single external IA link)
 *      with 68 individual ArchiveRecord rows, one per FBI document,
 *      each mirrored locally at /pdfs/fbi-cointelpro-twwa/
 *
 * Idempotent.
 */
final class SelfHostExternalArchiveRecords extends Command {
    protected $signature = 'archive:self-host-external-records';
    protected $description = 'Self-host the 4 remaining external-link archive records (dokumen.pub x2, BPP IA collection, TWWA FOIA)';

    public function handle(): int {
        // === Delete the redundant BPP 1967-1968 IA collection link ===
        $bpp = ArchiveRecord::where('slug', 'the-black-panther-newspaper-1967-1968-ia-collection')->first();
        if ($bpp) {
            $bpp->delete();
            $this->info('Deleted redundant record: the-black-panther-newspaper-1967-1968-ia-collection (superseded by per-issue records)');
        }

        // === Update the NYC ABC Illustrated Guide record ===
        $guide = ArchiveRecord::where('slug', 'nyc-abc-illustrated-guide')->first();
        if ($guide) {
            $guide->update([
                'file' => '/pdfs/abc/nyc-abc-illustrated-guide-political-prisoners-v19-3-2026.pdf',
                'description' => $guide->description."\n\nMirrored locally from NYC ABC (v19.3, April 2026).",
            ]);
            $this->info('Repointed: nyc-abc-illustrated-guide → /pdfs/abc/');
        } else {
            $this->warn('Record nyc-abc-illustrated-guide not found — creating fresh');
            ArchiveRecord::create([
                'slug' => 'nyc-abc-illustrated-guide',
                'title' => 'NYC Anarchist Black Cross — Illustrated Guide to Political Prisoners and Prisoners of War (v19.3, April 2026)',
                'description' => "The Illustrated Guide to Political Prisoners and Prisoners of War, compiled and maintained by the NYC Anarchist Black Cross. The most actively-updated U.S. political-prisoner roster in continuous publication — released in iterating versions for nearly two decades, with mini-bios, illustrations, current addresses, and birthday dates for every prisoner NYC ABC supports. Optimized for booklet printing and explicitly licensed for redistribution. Mirrored locally from nycabc.wordpress.com (v19.3, April 2026).",
                'record_type' => 'book',
                'source_format' => 'booklet',
                'file' => '/pdfs/abc/nyc-abc-illustrated-guide-political-prisoners-v19-3-2026.pdf',
                'collection' => 'Anarchist Black Cross — NYC',
                'authors' => 'NYC Anarchist Black Cross',
                'publisher' => 'NYC Anarchist Black Cross',
                'year' => 2026,
                'date' => '2026-04-01',
                'subjects' => ['Anarchist Black Cross', 'NYC ABC', 'Political Prisoners', 'Prisoner Listing'],
                'is_digitized' => true,
                'published' => true,
            ]);
        }

        // === Update the Anarchy Live (Michael Kimble) record ===
        $kimble = ArchiveRecord::where('slug', 'anarchy-live-michael-kimble')->first();
        if ($kimble) {
            $kimble->update([
                'file' => '/pdfs/anarchist-prisoners/anarchy-live-writings-of-michael-kimble.pdf',
                'description' => $kimble->description."\n\nMirrored locally from anarchylive.noblogs.org.",
            ]);
            $this->info('Repointed: anarchy-live-michael-kimble → /pdfs/anarchist-prisoners/');
        } else {
            $this->warn('Record anarchy-live-michael-kimble not found — creating fresh');
            ArchiveRecord::create([
                'slug' => 'anarchy-live-michael-kimble',
                'title' => 'Anarchy Live! The Writings of Anarchist Prisoner Michael Kimble',
                'description' => "Collection of Michael Kimble's public writing through Summer 2015, plus a previously-unpublished interview on his life, prison struggle in Alabama, queer prisoner solidarity, the anti-police struggle, civilization, and anarchy. Kimble is a Black, gay, anarchist prisoner from Birmingham, Alabama, imprisoned since 1986 for the killing of a known racist and homophobe in what Kimble has described as self-defense. The publication is one of the longest-running anarchist prisoner writing collections produced from inside U.S. state custody. Mirrored locally from anarchylive.noblogs.org.",
                'record_type' => 'book',
                'source_format' => 'zine',
                'file' => '/pdfs/anarchist-prisoners/anarchy-live-writings-of-michael-kimble.pdf',
                'collection' => 'Anarchist Prisoner Writings',
                'authors' => 'Michael Kimble',
                'publisher' => 'Anarchy Live! / Denver Anarchist Black Cross',
                'year' => 2015,
                'date' => '2015-08-01',
                'subjects' => ['Michael Kimble', 'Anarchist Prisoners', 'Queer Liberation', 'Alabama', 'Prisoner Writings'],
                'is_digitized' => true,
                'published' => true,
            ]);
        }

        // === Delete the old single TWWA FOIA collection record ===
        $twwa = ArchiveRecord::where('slug', 'fbi-cointelpro-twwa-foia-set')->first();
        if ($twwa) {
            $twwa->delete();
            $this->info('Deleted single-record TWWA FOIA collection — replaced with 68 individual file records');
        }

        // === Register each of the 68 TWWA FOIA PDFs as its own record ===
        $files = $this->twwaFiles();
        $added = 0; $updated = 0;
        foreach ($files as $entry) {
            $slug = $entry['slug'];
            $payload = [
                'title' => $entry['title'],
                'description' => "Internal FBI document from the COINTELPRO and post-COINTELPRO surveillance file on the Third World Women's Alliance (TWWA), the multiracial socialist-feminist organization that grew out of SNCC's Black Women's Liberation Committee and was active 1970-1980. Released under FOIA and mirrored from the Internet Archive item twwa_cointelpro. Document date: {$entry['date_pretty']}.",
                'record_type' => 'document',
                'source_format' => 'FBI FOIA',
                'file' => '/pdfs/fbi-cointelpro-twwa/'.$entry['file'],
                'collection' => 'FBI FOIA — COINTELPRO TWWA',
                'authors' => 'Federal Bureau of Investigation (FBI)',
                'publisher' => 'FBI / FOIA release',
                'year' => $entry['year'],
                'date' => $entry['date'],
                'subjects' => ['COINTELPRO', 'FBI', "Third World Women's Alliance", 'TWWA', 'SNCC', 'Black Feminism', 'Triple Jeopardy', 'State Repression'],
                'is_digitized' => true,
                'published' => true,
            ];
            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) { $existing->update($payload); $updated++; }
            else { ArchiveRecord::create(['slug' => $slug] + $payload); $added++; }
        }
        $this->info("TWWA FOIA: added {$added}, updated {$updated}");

        $this->line('Done.');
        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function twwaFiles(): array {
        $dir = public_path('pdfs/fbi-cointelpro-twwa');
        $out = [];
        if (! is_dir($dir)) return $out;
        foreach (scandir($dir) as $f) {
            if (! str_ends_with($f, '.pdf')) continue;
            $base = pathinfo($f, PATHINFO_FILENAME); // e.g. fbi_twwa_memo_19710308-1
            // Find an 8-digit YYYYMMDD or YYYYMM token
            $year = 1970; $date = '1970-01-01'; $datePretty = 'undated';
            if (preg_match('/(\d{4})(\d{2})(\d{2})/', $base, $m)) {
                $y = (int)$m[1]; $mo = (int)$m[2]; $d = (int)$m[3];
                if ($mo >= 1 && $mo <= 12 && $d >= 1 && $d <= 31) {
                    $year = $y;
                    $date = sprintf('%04d-%02d-%02d', $y, $mo, $d);
                    $datePretty = date('F j, Y', strtotime($date));
                } else {
                    // e.g. 197312XX or 1973xxxx
                    $year = $y;
                    $date = sprintf('%04d-01-01', $y);
                    $datePretty = (string)$y;
                }
            } elseif (preg_match('/(\d{4})xx/i', $base, $m)) {
                $year = (int)$m[1];
                $date = sprintf('%04d-01-01', $year);
                $datePretty = (string)$year;
            }

            // Pretty title
            $cat = 'document';
            if (str_contains($base, '_memo_')) $cat = 'memorandum';
            elseif (str_contains($base, '_letter_')) $cat = 'letter';
            elseif (str_contains($base, '_report_')) $cat = 'report';
            elseif (str_contains($base, '_analysis_')) $cat = 'analysis';
            elseif (str_contains($base, '_newspaper_')) $cat = 'Triple Jeopardy newspaper scan';
            elseif (str_contains($base, '_background_')) $cat = 'background';
            elseif (str_contains($base, '_characterization_')) $cat = 'characterization';
            elseif (str_contains($base, '_alsc_')) $cat = 'ALSC conference report';
            elseif (str_contains($base, '_routingslip_')) $cat = 'routing slip';
            elseif (str_contains($base, 'sncc_')) $cat = 'SNCC-period report';
            elseif (str_contains($base, '_Omaha_')) $cat = 'Omaha field-office memo';

            $title = "FBI COINTELPRO — TWWA ".ucfirst($cat)." ({$datePretty})";

            $slug = 'fbi-cointelpro-twwa-'.strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $base));
            $slug = trim($slug, '-');

            $out[] = [
                'slug' => $slug,
                'title' => $title,
                'file' => $f,
                'year' => $year,
                'date' => $date,
                'date_pretty' => $datePretty,
            ];
        }
        return $out;
    }
}
